# Dashboardify

A personal dashboard builder — arrange widgets (bookmarks, notes, iframes, images, countdowns, flash cards, and more) on a canvas, switch between dashboards, and keep everything organized in one place.

**Two versions:**

| Version | Status | Docs |
|---|---|---|
| **Serverless Cloud App** (HTML/JS) | Current | [`DOCS.md`](DOCS.md) |
| **Android TV Wrapper** | In development | [`ANDROID_TV.md`](ANDROID_TV.md) |
| PHP App | Deprecated (not used) | — |

---

## Quick Links

- **Live site:** https://drobin04.github.io/Dashboardify/
- **Serverless app docs:** [`DOCS.md`](DOCS.md) — architecture, auth flow, widget system, data model
- **Android TV app docs:** [`ANDROID_TV.md`](ANDROID_TV.md) — setup, build, testing, troubleshooting

---

## Cloud App (serverless)

The main Dashboardify app runs entirely in the browser as a static site hosted on GitHub Pages. Data syncs with Google Drive's app data folder using OAuth authentication.

No backend, no server, no build step. Open the live site and sign in with Google to get started.

---

## Android TV App

A thin wrapper that loads the Dashboardify web app in a fullscreen WebView with native Google Sign-In. Designed for always-on dashboard displays on Android TV hardware.

---

## Repository Structure

```
/
├── docs/           Serverless HTML/JS app (deployed to GitHub Pages)
├── androidTv/      Android TV wrapper app
├── src/html/       Deprecated PHP version (not used)
├── DOCS.md         Serverless app documentation
├── ANDROID_TV.md   Android TV app documentation
└── README.md       This file
```
