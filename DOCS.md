# Dashboardify — HTML/JS Architecture

## Overview

Dashboardify is a **100% client-side serverless dashboard builder**. It runs entirely in the browser as a static site hosted on GitHub Pages. There is no backend server, no database, and no build step required.

Data is stored in **Google Drive's hidden app data folder** (`appDataFolder`) using the Drive v3 REST API. Authentication is handled by **Google Identity Services (GIS)**.

---

## File Structure

```
docs/
  index.html                  Landing / splash page
  cloud-login.html            Google sign-in page
  cloud.html                  Main single-page dashboard app
  tests.html                  QUnit test runner

  config/
    globalcss.css              Base global styles

  css/
    home.css                  Splash page styling
    start-login.css            Login card layout
    cloud-login-extra.css      Login page overrides
    index.css                  Widget canvas, dialogs, flash cards
    cloud-shell.css            Cloud app toolbar
    settings_flyout.css        Settings dropdown
    settings_page.css          Legacy settings page

  js/
    index.js                  Widget rendering pipeline (~2290 lines)
    settings_flyout.js         Settings menu positioning/hide
    md-block.js                Custom element for rendering markdown

    cloud/
      config.js                OAuth client ID and scope
      google-auth.js           OAuth token management (GIS)
      google-drive-client.js   Low-level Drive v3 REST client
      storage-adapter.js       CRUD for dashboards, widgets, preferences
      schema.js                Data model shape and normalization
      cloud-login.js           Login page controller
      app.js                   Cloud app boot and event wiring
      welcome-pack.js          Starter widgets for new users

    tests/
      (QUnit test files)
```

---

## Page Navigation

| Page | URL | Purpose |
|---|---|---|
| `index.html` | `/Dashboardify/` | Splash page with links to sign in or go to app |
| `cloud-login.html` | `/Dashboardify/cloud-login.html` | Google OAuth sign-in |
| `cloud.html` | `/Dashboardify/cloud.html` | Dashboard management and widget canvas |
| `tests.html` | `/Dashboardify/tests.html` | QUnit test runner |

Navigation flow:
- `index.html` → "Open Dashboardify" → `cloud-login.html`
- `index.html` → "Go to app (signed in)" → `cloud.html`
- `cloud-login.html` → sign in → `cloud.html#oauth_handoff=...`
- `cloud.html` → "Sign out" → `cloud-login.html`

---

## Authentication

### Sign-in flow (`cloud-login.html`)

1. User clicks "Continue with Google"
2. `GoogleAuthProvider.init()` loads the GIS script (`https://accounts.google.com/gsi/client`) and initializes a token client
3. `GoogleAuthProvider.signIn()` calls `requestAccessToken({ prompt: "consent" })`, showing Google's OAuth consent popup
4. On success, the access token and expiry are persisted to `localStorage` under key `dashboardify_google_oauth`
5. The page redirects to `cloud.html#oauth_handoff=<encoded>` where the hash contains a base64-encoded JSON payload `{ accessToken, expiresAtMs }`

### App boot (`cloud.html`)

The `boot()` function in `app.js` runs on page load:

1. **Checks local cache** — if a cached data snapshot exists in `localStorage` (`dashboardify_cloud_data_snapshot`), the app enters view-only mode (shows cached dashboards) and schedules a silent background attempt to reattach Drive access if a valid token is found
2. **Checks OAuth handoff** — decodes the `#oauth_handoff=...` URL hash and applies the token
3. **Silent session restore** — reads a persisted token from `localStorage`, or requests a new one silently (no prompt) via GIS
4. On success, creates a `CloudStorageAdapter` with a `GoogleDriveAppDataClient` and loads user data from Drive

### Token persistence

- **Key:** `dashboardify_google_oauth` in `localStorage` (falls back to `sessionStorage` in private browsing)
- **Format:** `{ accessToken: string, expiresAtMs: number }`
- **Expiry buffer:** Tokens are considered expired 120 seconds before the server-reported `expires_in`

---

## Data Storage

### Location

Google Drive **`appDataFolder`** — a hidden per-app folder invisible in the user's normal Drive UI. All data is stored in a single file named `dashboardify-data.json`.

### Drive API client (`google-drive-client.js`)

Wraps the Drive v3 REST API using `fetch()` with Bearer token auth:

- `findDataFile()` — Queries files in `appDataFolder` by name
- `createDataFile(json)` — Multipart upload to create the file
- `readFileContent(id)` — Downloads file content
- `updateFileContent(id, json)` — Uploads updated content

### Cloud Storage Adapter (`storage-adapter.js`)

Central data access layer with two modes:

- **Online (Drive client present):** Loads from Drive, writes back on every mutation, caches locally for offline access
- **Offline (no Drive client):** Uses local cache only; mutations throw "Sign in to save changes"

Every read from Drive writes to the local cache (`dashboardify_cloud_data_snapshot` in `localStorage`).

---

## Data Model

```json
{
  "version": 2,
  "meta": {
    "app": "Dashboardify",
    "appVersion": "cloud-v1",
    "lastUpdatedUtc": "<ISO string>",
    "nuxWelcomePackApplied": false
  },
  "userCss": "",
  "preferences": {
    "siteBaseUrl": "",
    "showSqlWidgetFields": false,
    "lastSelectedDashboardId": ""
  },
  "customWidgetProviders": [],
  "widgetStyles": [],
  "dashboards": [
    {
      "DashboardID": "<UUID>",
      "Name": "Dashboard Name",
      "CustomCSS": "",
      "BackgroundPhotoURL": "",
      "Embeddable": "0"
    }
  ],
  "widgets": [
    {
      "RecID": "<UUID>",
      "DashboardRecID": "<UUID>",
      "WidgetType": "Bookmark | Notes | IFrame | ...",
      "BookmarkDisplayText": "",
      "WidgetURL": "",
      "WidgetCSSClass": "",
      "Notes": "",
      "PositionX": "0",
      "PositionY": "0",
      "SizeX": "300",
      "SizeY": "200"
    }
  ]
}
```

---

## Widget Types

| Type | Description |
|---|---|
| **Bookmark** | Clickable link tile; auto-flows when position is empty or 0,0 |
| **IFrame** | Full-size iframe at given position/size |
| **Collapseable IFrame** | Collapsible header bar, lazy-loads iframe on expand |
| **Notes** | Markdown-rendered text block; Shift+click for inline editing |
| **HTMLEmbed** | Raw HTML injection; rendered last |
| **Image** | Image with CSS `object-fit` control |
| **Flash Cards** | Question/answer cards with various display modes and auto-advance |
| **Countdown** | "X days remaining" countdown widget |
| **Clock** | Formatted time display with timezone support |
| **CountUp_Hours** | Hours since a reference event |
| **CountUp_Days** | Days since a reference event |
| **Website Down Detector** | HEAD request health check with green/red indicator |

---

## CRUD Operations

| Operation | Method | Behavior |
|---|---|---|
| Load | `adapter.loadAppData()` | Find or create file on Drive, parse, normalize, cache locally |
| Get dashboards | `adapter.getDashboards()` | Returns dashboard list |
| Get widgets | `adapter.getWidgetsForDashboard(id)` | Filters widgets by dashboard ID |
| Create dashboard | `adapter.createDashboard(name)` | Generates UUID, pushes to data model, saves |
| Update dashboard | `adapter.updateDashboard(id, patch)` | Merges patch, saves |
| Delete dashboard | `adapter.deleteDashboard(id)` | Removes dashboard + all its widgets, saves |
| Upsert widget | `adapter.upsertWidget(widget)` | Creates or updates a widget, saves |
| Delete widget | `adapter.deleteWidget(recId)` | Removes widget by ID, saves |

---

## Security

- **OAuth scope:** `https://www.googleapis.com/auth/drive.appdata` — only the hidden app data folder, not the user's visible Drive files
- **Token storage:** `localStorage` (same-origin only, cleared on sign-out)
- **CORS:** Drive API calls use CORS; no proxy needed
- **Website Down Detector:** Uses `mode: "no-cors"` — can only detect load success/failure, cannot read response
- **DOMPurify:** Applied by `md-block.js` to sanitize rendered markdown

---

## Key Configuration

**`docs/js/cloud/config.js`:**
```javascript
{
  clientId: "33427175670-vbe7elvf7um034tb3kqbnh2dvpu69dn0.apps.googleusercontent.com",
  scope: "https://www.googleapis.com/auth/drive.appdata"
}
```

**LocalStorage keys:**
| Key | Content |
|---|---|
| `dashboardify_google_oauth` | `{ accessToken, expiresAtMs }` |
| `dashboardify_cloud_data_snapshot` | Full cached data model |

---

## Dependencies (loaded from CDN)

- jQuery 3.6 / jQuery UI 1.13 (draggable widgets)
- Moment.js 2.29
- Font Awesome 6.5 (Google icon)
- Google Identity Services (loaded dynamically)
- marked (Markdown parser, loaded dynamically by `md-block.js`)
- DOMPurify (HTML sanitizer, loaded dynamically by `md-block.js`)

---

## Testing

Open `docs/tests.html` in a browser to run the QUnit test suite. Test files are in `docs/js/tests/`.
