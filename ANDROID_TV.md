# Dashboardify Android TV App

A thin Android TV (and phone-compatible) wrapper that loads the Dashboardify web app in a fullscreen WebView with native Google Sign-In for reliable authentication.

---

## Project Structure

```
androidTv/
├── build.gradle.kts              Root Gradle config
├── settings.gradle.kts           Project settings
├── gradle.properties             Build properties
├── gradle/wrapper/
│   └── gradle-wrapper.properties Gradle version
└── app/
    ├── build.gradle.kts          App module config
    ├── proguard-rules.pro        ProGuard rules
    └── src/main/
        ├── AndroidManifest.xml
        ├── java/com/dashboardify/tv/
        │   └── MainActivity.kt   Main activity with WebView + native auth
        └── res/
            ├── drawable/ic_launcher.xml
            ├── values/{strings,colors,themes}.xml
            └── xml/network_security_config.xml
```

---

## How It Works

### Authentication strategy

Google blocks the Google Identity Services (GIS) script (`https://accounts.google.com/gsi/client`) inside embedded WebViews for security reasons. This app solves that with a two-layer approach:

1. **Native Google Sign-In** — Uses Android's `GoogleSignInClient` to obtain an OAuth token with the `drive.appdata` scope directly from the device. No WebView involvement.

2. **Auth token injection** — The native token is passed to the WebView via:
   - **URL hash handoff** — Navigates to `cloud.html#oauth_handoff=<encoded>` where the web app's existing `tryApplyOAuthHandoffFromUrl()` function reads the token from the URL
   - **GIS script interception** — When the web app tries to load GIS, `WebViewClient.shouldInterceptRequest()` returns a stub script that replaces `google.accounts.oauth2` with a native bridge implementation
   - **JavaScript bridge** — A `@JavascriptInterface` bridge (`AndroidDashboardifyBridge.getAccessToken()`) provides the token to the GIS stub

3. **Loading UI** — Instead of falling back to a broken WebView on sign-in failure, the app shows a native "Sign in with Google" button. The WebView is only created after successful authentication.

### WebView configuration

Key settings applied to the WebView:
- JavaScript enabled
- DOM storage enabled
- Multiple window / popup support enabled
- Third-party cookies enabled
- User-Agent spoofed with `Chrome/130.0.6723.58` suffix
- D-pad remote navigation support (up, down, left, right, center, enter)

---

## Prerequisites

- [Android Studio](https://developer.android.com/studio) installed
- A Google Cloud project with the Drive API enabled
- The debug keystore SHA-1 fingerprint registered in Google Cloud Console (see Setup)

---

## Setup

### 1. Register the Android app in Google Cloud Console

The native Google Sign-In requires your Android app to be registered in the same Google Cloud project as the web app's OAuth client ID.

a) Get your debug SHA-1 fingerprint:

```bash
# Windows
"D:\Program Files\Android\Android Studio\jbr\bin\keytool.exe" -list -v -keystore "%USERPROFILE%\.android\debug.keystore" -alias androiddebugkey -storepass android -keypass android
```

b) Go to [Google Cloud Console](https://console.cloud.google.com/) → APIs & Services → Credentials

c) Click **Create Credentials** → **OAuth client ID** → **Android**

d) Enter:
   - **Package name:** `com.dashboardify.tv`
   - **SHA-1 certificate fingerprint:** (paste the value from step a)

e) Click **Create**

### 2. Open in Android Studio

- Open Android Studio
- File → Open → select the `androidTv/` directory
- Wait for Gradle sync to complete

### 3. Build and run

**On a device or emulator:**

- Select a run configuration (app) and target device
- Click Run (Shift+F10)

**Build APK only:**

```bash
cd androidTv
./gradlew assembleDebug
```

The APK will be at `app/build/outputs/apk/debug/app-debug.apk`.

---

## Testing

### Android TV emulator

1. In Android Studio: Tools → Device Manager → Create Device
2. Select **TV** category → choose "Android TV (1080p)" or similar
3. Run the app on the TV emulator

### Phone testing

The app works on phones too (the `android.hardware.type.television` requirement is set to `required="false"`). Install the APK directly:

```bash
adb install app/build/outputs/apk/debug/app-debug.apk
```

### Sideloading to Android TV

1. Enable Developer Options on your TV: Settings → Device Preferences → About → Build (click 7 times)
2. Enable: Settings → Developer Options → Debug over ADB
3. Find the TV's IP address (Settings → Network)
4. Connect via ADB:

```bash
adb connect <TV_IP_ADDRESS>
adb install app/build/outputs/apk/debug/app-debug.apk
```

---

## Troubleshooting

### Sign-in cancelled

The app shows "Sign in was cancelled" after selecting an account. This usually means:
- The Android app is not registered in Google Cloud Console (see Setup step 1)
- The package name or SHA-1 fingerprint doesn't match

Check Logcat in Android Studio for messages tagged `DashboardifyTV` to see the exact error.

### GIS script still fails

Even with native sign-in, if the web app navigates to pages that try to load GIS directly (e.g., `cloud-login.html`), the `shouldInterceptRequest` handler returns a stub. If the stub isn't working:

- Verify the `shouldInterceptRequest` method is intercepting `https://accounts.google.com/gsi/client` 
- Check Logcat for `DashboardifyTV: Intercepting GIS script` messages

### WebView displays "Cloud boot failed"

This means the web app's `boot()` function encountered an error. Common causes:
- No valid token in the handoff URL or localStorage
- The token expired between sign-in and page load
- GIS script interception failed

---

## Architecture Notes

### Token flow

```
[App Launch]
     │
     ▼
[GoogleSignInClient.signInIntent]
     │ (user selects account)
     ▼
[GoogleAuthUtil.getToken()] ──▶ OAuth access token
     │
     ▼
[buildHandoffUrl()] ──▶ cloud.html#oauth_handoff=<encoded>
     │
     ├── WebView loads cloud.html ──▶ tryApplyOAuthHandoffFromUrl()
     │                                   reads token from URL hash
     │
     └── WebView requests gsi/client ──▶ shouldInterceptRequest()
                                             returns stub script
                                             │
                                             ▼
                                        AndroidDashboardifyBridge
                                             .getAccessToken()
```

### GIS stub

When the web app dynamically loads `https://accounts.google.com/gsi/client`, the Android app intercepts the request and returns:

```javascript
google.accounts.oauth2.initTokenClient = function(config) {
    return {
        callback: function() {},
        requestAccessToken: function() {
            var token = AndroidDashboardifyBridge.getAccessToken();
            this.callback({ access_token: token, expires_in: 3600 });
        }
    };
};
```

This allows any page in the WebView that uses Google Identity Services to work without modification.

---

## Key Files

| File | Purpose |
|---|---|
| `app/src/main/java/com/dashboardify/tv/MainActivity.kt` | Main activity: native auth, WebView setup, GIS interception, JS bridge |
| `app/src/main/AndroidManifest.xml` | Leanback launcher, TV features, network security |
| `app/build.gradle.kts` | Dependencies including `play-services-auth` |
| `app/src/main/res/xml/network_security_config.xml` | Domain allowlist for dashboardify |
