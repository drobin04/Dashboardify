package com.dashboardify.tv

import android.annotation.SuppressLint
import android.content.Intent
import android.os.Bundle
import android.util.Base64
import android.util.Log
import android.view.KeyEvent
import android.view.View
import android.webkit.*
import android.widget.Button
import android.widget.FrameLayout
import android.widget.TextView
import androidx.activity.result.contract.ActivityResultContracts
import androidx.appcompat.app.AppCompatActivity
import com.google.android.gms.auth.GoogleAuthException
import com.google.android.gms.auth.GoogleAuthUtil
import com.google.android.gms.auth.UserRecoverableAuthException
import com.google.android.gms.auth.api.signin.GoogleSignIn
import com.google.android.gms.auth.api.signin.GoogleSignInAccount
import com.google.android.gms.auth.api.signin.GoogleSignInClient
import com.google.android.gms.auth.api.signin.GoogleSignInOptions
import com.google.android.gms.common.api.Scope
import java.io.ByteArrayInputStream
import java.io.IOException
import java.net.URLEncoder
import java.nio.charset.StandardCharsets

private const val TAG = "DashboardifyTV"
private const val DASHBOARD_BASE = "https://drobin04.github.io/Dashboardify/"
private const val CLOUD_URL = "https://drobin04.github.io/Dashboardify/cloud.html"
private const val DRIVE_SCOPE = "https://www.googleapis.com/auth/drive.appdata"
private const val RC_RECOVER = 9002

class MainActivity : AppCompatActivity() {

    private lateinit var googleSignInClient: GoogleSignInClient
    private lateinit var rootView: FrameLayout
    private var webView: WebView? = null
    private var statusLabel: TextView? = null
    private var actionButton: Button? = null
    var currentAccessToken: String? = null

    private val signInLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        val data = result.data
        val googleResult = GoogleSignIn.getSignedInAccountFromIntent(data)
        if (googleResult.isSuccessful) {
            getTokenAndLoad(googleResult.getResult())
        } else {
            Log.w(TAG, "Sign-in failed or cancelled: ${googleResult.exception?.message}")
            showRetryUI("Sign-in was cancelled. Please try again.")
        }
    }

    private val recoverLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { _ ->
        val account = GoogleSignIn.getLastSignedInAccount(this)
        if (account != null) {
            getTokenAndLoad(account)
        } else {
            showRetryUI("Permission was not granted. Please try again.")
        }
    }

    @SuppressLint("SetJavaScriptEnabled")
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        supportActionBar?.hide()

        rootView = FrameLayout(this).apply {
            setBackgroundColor(0xFF121212.toInt())
        }

        statusLabel = TextView(this).apply {
            text = "Loading..."
            setTextColor(0xFFFFFFFF.toInt())
            textSize = 18f
        }
        rootView.addView(statusLabel, FrameLayout.LayoutParams(
            FrameLayout.LayoutParams.WRAP_CONTENT,
            FrameLayout.LayoutParams.WRAP_CONTENT
        ).also { it.gravity = android.view.Gravity.CENTER })

        actionButton = Button(this).apply {
            visibility = View.GONE
        }
        rootView.addView(actionButton, FrameLayout.LayoutParams(
            FrameLayout.LayoutParams.WRAP_CONTENT,
            FrameLayout.LayoutParams.WRAP_CONTENT
        ).also {
            it.gravity = android.view.Gravity.CENTER
            it.topMargin = 120
        })

        setContentView(rootView)

        val gso = GoogleSignInOptions.Builder(GoogleSignInOptions.DEFAULT_SIGN_IN)
            .requestScopes(Scope(DRIVE_SCOPE))
            .requestEmail()
            .build()
        googleSignInClient = GoogleSignIn.getClient(this, gso)

        val account = GoogleSignIn.getLastSignedInAccount(this)
        val hasDriveScope = account?.grantedScopes?.any {
            it.scopeUri == DRIVE_SCOPE
        } == true

        if (account != null && hasDriveScope) {
            getTokenAndLoad(account)
        } else {
            showRetryUI("Please sign in with your Google account to access dashboards.")
        }
    }

    private fun showRetryUI(message: String) {
        statusLabel?.text = message
        actionButton?.apply {
            text = "Sign in with Google"
            visibility = View.VISIBLE
            setOnClickListener {
                visibility = View.GONE
                statusLabel?.text = "Signing in..."
                signInLauncher.launch(googleSignInClient.signInIntent)
            }
        }
    }

    private fun getTokenAndLoad(account: GoogleSignInAccount) {
        statusLabel?.text = "Getting access token..."
        val email = account.email
        if (email.isNullOrBlank()) {
            showRetryUI("Account email not available. Please sign in again.")
            return
        }

        Thread {
            try {
                val token = GoogleAuthUtil.getToken(
                    this,
                    email,
                    "oauth2:$DRIVE_SCOPE"
                )
                currentAccessToken = token
                val expiresAtMs = System.currentTimeMillis() + 3480_000
                runOnUiThread { setUpWebView(token, expiresAtMs) }
            } catch (e: UserRecoverableAuthException) {
                Log.w(TAG, "Need to recover auth permission", e)
                runOnUiThread {
                    statusLabel?.text = "Granting permission..."
                    recoverLauncher.launch(e.getIntent())
                }
            } catch (e: GoogleAuthException) {
                Log.e(TAG, "Google auth error", e)
                runOnUiThread { showRetryUI("Google sign-in error: ${e.message}") }
            } catch (e: IOException) {
                Log.e(TAG, "Network error getting token", e)
                runOnUiThread { showRetryUI("Network error. Please check your connection.") }
            }
        }.start()
    }

    @SuppressLint("SetJavaScriptEnabled", "AddJavascriptInterface")
    private fun setUpWebView(accessToken: String, expiresAtMs: Long) {
        statusLabel?.visibility = View.GONE
        actionButton?.visibility = View.GONE

        val wv = WebView(this)
        wv.layoutParams = FrameLayout.LayoutParams(
            FrameLayout.LayoutParams.MATCH_PARENT,
            FrameLayout.LayoutParams.MATCH_PARENT
        )
        webView = wv
        rootView.addView(wv)

        wv.apply {
            settings.javaScriptEnabled = true
            settings.domStorageEnabled = true
            settings.javaScriptCanOpenWindowsAutomatically = true
            settings.setSupportMultipleWindows(true)
            settings.loadWithOverviewMode = true
            settings.useWideViewPort = true
            settings.builtInZoomControls = false
            settings.setSupportZoom(false)
            settings.mediaPlaybackRequiresUserGesture = false
            settings.databaseEnabled = true
            settings.cacheMode = WebSettings.LOAD_DEFAULT
            settings.mixedContentMode = WebSettings.MIXED_CONTENT_ALWAYS_ALLOW

            val defaultUA = settings.userAgentString
            settings.userAgentString = "$defaultUA Chrome/130.0.6723.58"

            setOnKeyListener { _, keyCode, event ->
                if (event.action == KeyEvent.ACTION_DOWN) {
                    when (keyCode) {
                        KeyEvent.KEYCODE_DPAD_UP,
                        KeyEvent.KEYCODE_DPAD_DOWN,
                        KeyEvent.KEYCODE_DPAD_LEFT,
                        KeyEvent.KEYCODE_DPAD_RIGHT,
                        KeyEvent.KEYCODE_DPAD_CENTER,
                        KeyEvent.KEYCODE_ENTER -> {
                            dispatchKeyEvent(event); true
                        }
                        else -> false
                    }
                } else false
            }

            addJavascriptInterface(
                DashboardifyJsBridge(this@MainActivity),
                "AndroidDashboardifyBridge"
            )
        }

        CookieManager.getInstance().apply {
            setAcceptCookie(true)
            setAcceptThirdPartyCookies(wv, true)
        }

        wv.webChromeClient = object : WebChromeClient() {
            override fun onCreateWindow(
                view: WebView,
                isDialog: Boolean,
                isUserGesture: Boolean,
                resultMsg: android.os.Message
            ): Boolean {
                val newView = WebView(view.context).apply {
                    settings.javaScriptEnabled = true
                    settings.domStorageEnabled = true
                }
                view.addView(newView)
                (resultMsg.obj as WebView.WebViewTransport).webView = newView
                resultMsg.sendToTarget()
                return true
            }
        }

        wv.webViewClient = object : WebViewClient() {
            override fun shouldOverrideUrlLoading(
                view: WebView,
                request: WebResourceRequest
            ): Boolean = false

            override fun shouldInterceptRequest(
                view: WebView,
                request: WebResourceRequest
            ): WebResourceResponse? {
                val url = request.url?.toString() ?: return null
                if (url.startsWith("https://accounts.google.com/gsi/client")) {
                    Log.d(TAG, "Intercepting GIS script, returning native bridge stub")
                    val script = buildGisStub()
                    return WebResourceResponse(
                        "application/javascript",
                        "UTF-8",
                        ByteArrayInputStream(script.toByteArray(StandardCharsets.UTF_8))
                    )
                }
                return null
            }
        }

        val handoffUrl = buildHandoffUrl(accessToken, expiresAtMs)
        wv.loadUrl(handoffUrl)
    }

    private fun buildGisStub(): String {
        return """
            (function() {
                window.google = window.google || {};
                google.accounts = google.accounts || {};
                google.accounts.oauth2 = google.accounts.oauth2 || {};
                
                google.accounts.oauth2.initTokenClient = function(config) {
                    var client = {
                        callback: function() {},
                        requestAccessToken: function(options) {
                            try {
                                var token = AndroidDashboardifyBridge.getAccessToken();
                                if (token && token.length > 0) {
                                    client.callback({ access_token: token, expires_in: 3600 });
                                } else {
                                    client.callback({ access_token: '', expires_in: 0, error: 'no_token' });
                                }
                            } catch (e) {
                                client.callback({ access_token: '', expires_in: 0, error: e.message || 'unknown' });
                            }
                        }
                    };
                    return client;
                };
            })();
        """.trimIndent()
    }

    private fun buildHandoffUrl(accessToken: String, expiresAtMs: Long): String {
        val json = """{"accessToken":"$accessToken","expiresAtMs":$expiresAtMs}"""
        val utf8 = json.toByteArray(StandardCharsets.UTF_8)
        val iso8859 = String(utf8, StandardCharsets.ISO_8859_1)
        val b64 = Base64.encodeToString(
            iso8859.toByteArray(StandardCharsets.ISO_8859_1),
            Base64.NO_WRAP
        )
        val handoff = URLEncoder.encode(b64, "UTF-8")
        return "$CLOUD_URL#oauth_handoff=$handoff"
    }

    override fun onSaveInstanceState(outState: Bundle) {
        super.onSaveInstanceState(outState)
        webView?.saveState(outState)
    }

    override fun onRestoreInstanceState(savedInstanceState: Bundle) {
        super.onRestoreInstanceState(savedInstanceState)
        webView?.restoreState(savedInstanceState)
    }

    override fun onKeyDown(keyCode: Int, event: KeyEvent): Boolean {
        if (keyCode == KeyEvent.KEYCODE_BACK) {
            if (webView?.canGoBack() == true) {
                webView?.goBack()
                return true
            }
        }
        return super.onKeyDown(keyCode, event)
    }

    @Deprecated("Deprecated in Java")
    override fun onBackPressed() {
        if (webView?.canGoBack() == true) {
            webView?.goBack()
        } else {
            super.onBackPressed()
        }
    }
}

class DashboardifyJsBridge(private val activity: MainActivity) {
    @JavascriptInterface
    fun getAccessToken(): String {
        return activity.currentAccessToken ?: ""
    }
}
