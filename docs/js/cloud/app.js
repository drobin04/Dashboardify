import { dashboardifyCloudConfig } from "./config.js";
import {
  GoogleAuthProvider,
  hasValidPersistedOAuthSession
} from "./google-auth.js";
import { GoogleDriveAppDataClient } from "./google-drive-client.js";
import {
  CloudStorageAdapter,
  readCloudDataCacheFromLocalStorage,
  writeCloudDataCacheToLocalStorage
} from "./storage-adapter.js";
import { defaultPreferences, ensureDataModelShape } from "./schema.js";

let adapter = null;
let currentDashboardId = "";
let googleAuth = null;
let writeAccessInFlight = null;

function tryApplyOAuthHandoffFromUrl(auth) {
  if (!auth || !window.location || !window.location.hash) return false;
  const hash = String(window.location.hash || "");
  const marker = "oauth_handoff=";
  const idx = hash.indexOf(marker);
  if (idx < 0) return false;
  const tokenPart = hash.slice(idx + marker.length).split("&")[0];
  if (!tokenPart) return false;
  try {
    const decodedJson = decodeURIComponent(escape(atob(decodeURIComponent(tokenPart))));
    const payload = JSON.parse(decodedJson);
    const accessToken = String(payload?.accessToken || "").trim();
    const expiresAtMs = Number(payload?.expiresAtMs || 0);
    if (!accessToken || !Number.isFinite(expiresAtMs) || Date.now() >= expiresAtMs) {
      return false;
    }
    auth.accessToken = accessToken;
    auth.tokenExpiresAtMs = expiresAtMs;
    if (window.history && typeof window.history.replaceState === "function") {
      window.history.replaceState(
        null,
        document.title,
        window.location.pathname + window.location.search
      );
    } else {
      window.location.hash = "";
    }
    return true;
  } catch {
    return false;
  }
}

const DEFAULT_WIDGET_TYPES = [
  // NOTE: When adding a new widget type, you MUST add it here AND in cloud.html dropdown AND in index.js switch cases
  // See drawNewWidgetBasedOnType(), fillEditWidgetFormFromRecord(), and drawWidget() in js/index.js
  "Bookmark",
  "IFrame",
  "Collapseable IFrame",
  "Notes",
  "HTMLEmbed",
  "Image",
  "Flash Cards",
  "Countdown",
  "Clock",
  "CountUp_Hours",
  "CountUp_Days"
];

const SQL_WIDGET_TYPES = [
  "SQLServerScalarQuery",
  "SQLiteResultsList",
  "SQLite Chart (PHPGD)"
];

function byId(id) {
  return document.getElementById(id);
}

function downloadJson(filename, obj) {
  const blob = new Blob([JSON.stringify(obj, null, 2)], {
    type: "application/json"
  });
  const url = URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = filename;
  document.body.appendChild(a);
  a.click();
  a.remove();
  URL.revokeObjectURL(url);
}

let backupImportMode = "merge";

function setStatus(message, isError = false) {
  const el = byId("statusText");
  if (!el) return;
  el.innerHTML = message;
  el.title = message.replace(/<[^>]*>/g, "");
  el.style.color = isError ? "#a00" : "#333";
}

function describeError(err) {
  if (!err) return "Unknown error";
  if (typeof err === "string") return err;
  if (err.message) return String(err.message);
  try {
    return JSON.stringify(err);
  } catch {
    return String(err);
  }
}

function applyDashboardPresentation() {
  if (!adapter || !adapter.dataCache) return;

  const data = adapter.dataCache;
  const userEl = byId("dashboard-user-css");
  if (userEl) {
    userEl.textContent = data.userCss || "";
  }

  const dashEl = byId("dashboard-per-dashboard-css");
  if (!dashEl || !currentDashboardId) {
    return;
  }

  const dash = data.dashboards.find(
    (d) => String(d.DashboardID) === String(currentDashboardId)
  );
  const bg =
    dash && dash.BackgroundPhotoURL
      ? String(dash.BackgroundPhotoURL).trim()
      : "";
  const custom = dash && dash.CustomCSS ? String(dash.CustomCSS) : "";

  let block = "";
  if (bg) {
    block += `body#dashboardcontent { background-image: url(${JSON.stringify(
      bg
    )}); background-size: cover; }\n`;
  } else {
    block += `body#dashboardcontent { background-image: none; }\n`;
  }
  block += custom;
  dashEl.textContent = block;
}

function populateCssEditor() {
  if (!adapter || !adapter.dataCache) return;
  const dash = adapter.dataCache.dashboards.find(
    (d) => String(d.DashboardID) === String(currentDashboardId)
  );
  if (!dash) return;
  byId("txtCSS").value = dash.CustomCSS || "";
  byId("cssEditorBackgroundUrl").value = dash.BackgroundPhotoURL || "";
}

function populateEditDashboardDialog() {
  if (!adapter || !adapter.dataCache) return;
  const dash = adapter.dataCache.dashboards.find(
    (d) => String(d.DashboardID) === String(currentDashboardId)
  );
  if (!dash) return;
  byId("editDashboardName").value = dash.Name || "";
  byId("editDashboardBackground").value = dash.BackgroundPhotoURL || "";
  byId("editDashboardEmbeddable").checked =
    String(dash.Embeddable) === "1" || dash.Embeddable === 1;
  const base = (adapter.dataCache.preferences?.siteBaseUrl || "").trim();
  const note = byId("editEmbedNote");
  if (note) {
    note.textContent = base
      ? `If you add a public embed page, it might live under: ${base.replace(
          /\/$/,
          ""
        )}/…`
      : "Set “Public site base URL” in User data & preferences if you use fixed links to this app.";
  }
}

function injectUserWidgetStyles() {
  if (!adapter?.dataCache) return;
  const styles = adapter.dataCache.widgetStyles || [];
  let el = document.getElementById("dashboardify-user-widget-styles");
  if (!el) {
    el = document.createElement("style");
    el.id = "dashboardify-user-widget-styles";
    document.head.appendChild(el);
  }
  if (!styles.length) {
    el.textContent = "";
    return;
  }
  const parts = [];
  for (const s of styles) {
    const css = (s.CSS || "").trim();
    const cn = (s.ClassName || "").trim();
    if (cn && /^[a-zA-Z0-9_-]+$/.test(cn) && css) {
      parts.push(`.${cn} { ${css} }`);
    } else if (css) {
      parts.push(css);
    }
  }
  el.textContent = parts.join("\n");
}

function rebuildWidgetTypeDropdown() {
  const sel = byId("ddlWidgetType2");
  if (!sel || !adapter?.dataCache) return;
  const preserve = sel.value;
  sel.innerHTML = "";
  const appendOpt = (value) => {
    const o = document.createElement("option");
    o.value = value;
    o.textContent = value;
    sel.appendChild(o);
  };
  DEFAULT_WIDGET_TYPES.forEach(appendOpt);
  const seen = new Set(DEFAULT_WIDGET_TYPES);
  for (const p of adapter.dataCache.customWidgetProviders || []) {
    const n = p && p.WidgetProviderName;
    if (n && !seen.has(String(n))) {
      seen.add(String(n));
      appendOpt(String(n));
    }
  }
  if (window.DashboardifyShowSqlWidgets) {
    for (const t of SQL_WIDGET_TYPES) {
      if (!seen.has(t)) {
        seen.add(t);
        appendOpt(t);
      }
    }
  }
  if ([...sel.options].some((o) => o.value === preserve)) {
    sel.value = preserve;
  }
}

function syncUserDataChrome() {
  if (!adapter?.dataCache) return;
  const pref = adapter.dataCache.preferences || {};
  window.DashboardifySiteBaseUrl = String(pref.siteBaseUrl || "").trim();
  window.DashboardifyShowSqlWidgets = !!pref.showSqlWidgetFields;
  window.DashboardifyWidgetTypesManagedByCloud = true;
  window.findCustomWidgetProvider = (name) =>
    (adapter.dataCache.customWidgetProviders || []).find(
      (x) => String(x.WidgetProviderName) === String(name)
    );
  injectUserWidgetStyles();
  rebuildWidgetTypeDropdown();
}

async function persistLastDashboardPreferenceIfNeeded() {
  if (!adapter || !currentDashboardId) return;
  const cur = adapter.dataCache?.preferences?.lastSelectedDashboardId;
  if (String(cur || "") === String(currentDashboardId)) return;
  if (adapter.driveClient) {
    await adapter.updatePreferences({ lastSelectedDashboardId: currentDashboardId });
    return;
  }
  if (adapter.dataCache) {
    adapter.dataCache.preferences = {
      ...defaultPreferences(),
      ...(adapter.dataCache.preferences || {}),
      lastSelectedDashboardId: currentDashboardId
    };
    writeCloudDataCacheToLocalStorage(adapter.dataCache);
  }
}

function scheduleBackgroundOnlineIfTokenValid() {
  const run = () => void tryBackgroundReattachDriveWhenTokenValid();
  if (typeof requestIdleCallback === "function") {
    requestIdleCallback(run, { timeout: 5000 });
  } else {
    setTimeout(run, 2000);
  }
}

/**
 * If a non-expired token is already in localStorage, attach Drive and refresh data — no
 * requestAccessToken call, so no account popup.
 */
async function tryBackgroundReattachDriveWhenTokenValid() {
  if (!adapter || adapter.driveClient) return;
  if (!hasValidPersistedOAuthSession()) return;
  const backup = adapter.dataCache;
  try {
    await googleAuth.ensureInitialized();
    if (!googleAuth.restoreSessionIfValid()) return;
    adapter.driveClient = new GoogleDriveAppDataClient(googleAuth);
    await adapter.loadAppData();
    syncUserDataChrome();
    await refreshDashboards();
    setStatus("Signed in");
  } catch (e) {
    console.warn("Background Drive sync skipped:", e);
    adapter.driveClient = null;
    if (backup) {
      adapter.hydrateFromLocalCacheModel(backup);
    }
  }
}

async function ensureCloudWriteAccess() {
  if (!adapter) return false;
  if (adapter.driveClient) return true;
  if (writeAccessInFlight) return writeAccessInFlight;
  writeAccessInFlight = (async () => {
    try {
      if (!(await googleAuth.restoreSessionOrSilentRefresh())) {
        setStatus("Sign in required to save changes. Use the sign-in link.", true);
        return false;
      }
      adapter.driveClient = new GoogleDriveAppDataClient(googleAuth);
      await adapter.loadAppData();
      syncUserDataChrome();
      await refreshDashboards();
      setStatus("Signed in");
      return true;
    } catch (e) {
      console.error(e);
      setStatus(`Could not enable cloud write access: ${describeError(e)}`, true);
      return false;
    } finally {
      writeAccessInFlight = null;
    }
  })();
  return writeAccessInFlight;
}

function populateUserPreferencesForm() {
  if (!adapter?.dataCache) return;
  const p = adapter.dataCache.preferences || {};
  byId("prefSiteBaseUrl").value = p.siteBaseUrl || "";
  byId("prefShowSqlWidgets").checked = !!p.showSqlWidgetFields;
  byId("prefCustomProvidersJson").value = JSON.stringify(
    adapter.dataCache.customWidgetProviders || [],
    null,
    2
  );
  byId("prefWidgetStylesJson").value = JSON.stringify(
    adapter.dataCache.widgetStyles || [],
    null,
    2
  );
}

function collectWidgetPayloadFromForm() {
  const get = (id) => {
    const el = document.getElementById(id);
    return el ? el.value : "";
  };
  const widgetType = (get("ddlWidgetType2") || "").trim();
  let notes = get("txtNotes");
  const dateEl = document.getElementById("datepicker");
  if (
    dateEl &&
    (widgetType === "Countdown" || widgetType === "CountUp_Hours")
  ) {
    notes = dateEl.value || notes;
  }
  if (widgetType === "Image") {
    const fitEl = document.getElementById("ddlImageObjectFit");
    notes = fitEl ? String(fitEl.value || "").trim() : "";
  }
  if (widgetType === "Clock") {
    const tzEl = document.getElementById("ddlClockTimezone");
    notes = tzEl ? String(tzEl.value || "").trim() : "";
  }
  if (widgetType === "Flash Cards") {
    const sortMethod = get("flashSortMethod") || "random";
    const displayStyleRaw = get("flashDisplayStyle");
    const displayStyle =
      String(displayStyleRaw || "").trim().toLowerCase() === "guess"
        ? "guess"
        : String(displayStyleRaw || "").trim().toLowerCase() === "multiplechoice"
          ? "multiplechoice"
          : "full";
    const autoAdvanceEnabled = !!document.getElementById("flashAutoAdvanceEnabled")?.checked;
    const autoAdvanceMsRaw = Number(get("flashAutoAdvanceMs"));
    const autoAdvanceMs = Number.isFinite(autoAdvanceMsRaw) && autoAdvanceMsRaw > 0
      ? Math.round(autoAdvanceMsRaw)
      : 5000;
    let cards = [];
    try {
      cards = JSON.parse(get("txtFlashCardsData") || "[]");
      if (!Array.isArray(cards)) cards = [];
    } catch {
      cards = [];
    }
    if (typeof window.dashboardifySerializeFlashCardsModel === "function") {
      notes = window.dashboardifySerializeFlashCardsModel({
        sortMethod,
        displayStyle,
        autoAdvanceEnabled,
        autoAdvanceMs,
        cards
      });
    }
  }

  let positionX = get("txtpositionx2") || "0";
  let positionY = get("txtpositiony2") || "0";
  if (widgetType === "Bookmark") {
    const sx = String(get("txtpositionx2") || "").trim();
    const sy = String(get("txtpositiony2") || "").trim();
    const x = parseFloat(sx);
    const y = parseFloat(sy);
    const autoTile =
      (sx === "" && sy === "") ||
      (Number.isFinite(x) && Number.isFinite(y) && x === 0 && y === 0);
    if (autoTile) {
      positionX = "";
      positionY = "";
    } else {
      positionX = sx || "0";
      positionY = sy || "0";
    }
  }

  return {
    RecID: get("txtWidgetID") || undefined,
    DashboardRecID: currentDashboardId,
    WidgetType: widgetType,
    BookmarkDisplayText: get("txtWidgetDisplayText"),
    WidgetURL: get("txtWidgetURL"),
    WidgetCSSClass: get("txtCSSClass"),
    Notes: notes,
    PositionX: positionX,
    PositionY: positionY,
    SizeX: get("txtsizeX2") || "300",
    SizeY: get("txtsizeY2") || "200",
    sqlserveraddress: get("SQLServerAddressName"),
    sqldbname: get("SQLDBName"),
    sqluser: get("sqluser"),
    sqlpass: get("sqlpass"),
    sqlquery: get("sqlquery")
  };
}

async function refreshDashboards() {
  const dashboards = await adapter.getDashboards();
  const ddl = byId("dashboardSelect");
  ddl.innerHTML = "";
  dashboards.forEach((d) => {
    const opt = document.createElement("option");
    opt.value = d.DashboardID;
    opt.textContent = d.Name || d.DashboardID;
    ddl.appendChild(opt);
  });

  if (!dashboards.length) {
    if (!adapter.driveClient) {
      setStatus("No dashboards in saved copy.", true);
      return;
    }
    currentDashboardId = await adapter.createDashboard("Default Dashboard");
    return refreshDashboards();
  }

  const lastPref = adapter.dataCache?.preferences?.lastSelectedDashboardId;
  let resolved = currentDashboardId;
  if (
    lastPref &&
    dashboards.some((d) => String(d.DashboardID) === String(lastPref))
  ) {
    resolved = lastPref;
  } else if (
    !resolved ||
    !dashboards.some((d) => String(d.DashboardID) === String(resolved))
  ) {
    resolved = dashboards[0].DashboardID;
  }
  currentDashboardId = resolved;

  ddl.value = currentDashboardId;

  const hid = byId("txtDashboardID");
  if (hid) hid.value = currentDashboardId;

  await persistLastDashboardPreferenceIfNeeded();
  if (
    adapter.driveClient &&
    typeof adapter.maybeSeedWelcomePack === "function"
  ) {
    const seeded = await adapter.maybeSeedWelcomePack(currentDashboardId);
    if (seeded) {
      setStatus("Added a few starter tips — edit or delete them anytime.");
    }
  }
  await refreshWidgets();
}

async function refreshWidgets() {
  applyDashboardPresentation();
  const widgets = await adapter.getWidgetsForDashboard(currentDashboardId);
  if (typeof window.clearWidgetContainer === "function") {
    window.clearWidgetContainer();
  } else {
    const c = byId("widgetcontainer");
    if (c) c.innerHTML = "";
  }
  if (typeof window.renderwidgetsfromjson === "function") {
    window.renderwidgetsfromjson(widgets || []);
  }
}

async function boot() {
  try {
    googleAuth = new GoogleAuthProvider(dashboardifyCloudConfig);
    const cached = readCloudDataCacheFromLocalStorage();

    if (cached) {
      adapter = new CloudStorageAdapter(null);
      adapter.hydrateFromLocalCacheModel(cached);
      setStatus(
        "Viewing saved dashboards — <a href=\"cloud-login.html\">sign in</a> when you add or change anything."
      );
      scheduleBackgroundOnlineIfTokenValid();
    } else {
      const signedInFromHandoff = tryApplyOAuthHandoffFromUrl(googleAuth);
      const signedIn = signedInFromHandoff || (await googleAuth.restoreSessionOrSilentRefresh());
      if (!signedIn) {
        setStatus("Sign-in required. Open cloud-login.html to continue.", true);
        return false;
      }
      adapter = new CloudStorageAdapter(
        new GoogleDriveAppDataClient(googleAuth)
      );
      await adapter.loadAppData();
      setStatus("Signed in");
    }

    window.DashboardifyDataAdapter = adapter;
    window.DashboardifyEnsureCloudWriteAccess = ensureCloudWriteAccess;

    window.editwidget = async function (recID) {
      if (!adapter) return;
      const list = await adapter.getWidgetsForDashboard(currentDashboardId);
      const w = list.find((x) => String(x.RecID) === String(recID));
      if (!w) return;
      const hid = byId("txtDashboardID");
      if (hid) hid.value = currentDashboardId;
      if (typeof window.fillEditWidgetFormFromRecord === "function") {
        fillEditWidgetFormFromRecord(w, recID);
      }
      if (typeof window.toggleDisplay === "function") {
        toggleDisplay("NewWidgetDialog2");
      } else {
        byId("NewWidgetDialog2").style.display = "block";
      }
    };

    syncUserDataChrome();
    await refreshDashboards();
    return true;
  } catch (err) {
    console.error(err);
    if (adapter?.driveClient) {
      googleAuth?.clearPersistedSession();
    }
    setStatus(`Cloud boot failed: ${describeError(err)}`, true);
    return false;
  }
}

window.addEventListener("DOMContentLoaded", async () => {
  const ready = await boot();
  if (!ready) return;

  byId("dashboardSelect").addEventListener("change", async (e) => {
    currentDashboardId = e.target.value;
    const hid = byId("txtDashboardID");
    if (hid) hid.value = currentDashboardId;
    await persistLastDashboardPreferenceIfNeeded();
    await refreshWidgets();
  });

  byId("btnOpenNewWidget").addEventListener("click", () => {
    byId("txtDashboardID").value = currentDashboardId;
    byId("txtWidgetID").value = "";
    if (typeof drawNewWidgetBasedOnType === "function") {
      drawNewWidgetBasedOnType();
    }
    toggleDisplay("NewWidgetDialog2");
  });

  byId("btnSubmitNewWidget").addEventListener("click", async () => {
    if (!adapter) return;
    if (!(await ensureCloudWriteAccess())) return;
    const payload = collectWidgetPayloadFromForm();
    await adapter.upsertWidget(payload);
    byId("NewWidgetDialog2").style.display = "none";
    await refreshWidgets();
  });

  byId("btnSaveCloudCss").addEventListener("click", async () => {
    if (!adapter) return;
    if (!(await ensureCloudWriteAccess())) return;
    await adapter.updateDashboard(currentDashboardId, {
      CustomCSS: byId("txtCSS").value,
      BackgroundPhotoURL: byId("cssEditorBackgroundUrl").value
    });
    byId("cssEditorBox").style.display = "none";
    await refreshWidgets();
  });

  byId("btnCloudSaveNewDashboard").addEventListener("click", async () => {
    if (!adapter) return;
    if (!(await ensureCloudWriteAccess())) return;
    const name = (byId("newDashboardName").value || "").trim();
    if (!name) {
      alert("Enter a dashboard name.");
      return;
    }
    currentDashboardId = await adapter.createDashboard(name);
    byId("newDashboardName").value = "";
    byId("NewDashboardDialog").style.display = "none";
    await refreshDashboards();
  });

  byId("btnCloudSaveEditDashboard").addEventListener("click", async () => {
    if (!adapter) return;
    if (!(await ensureCloudWriteAccess())) return;
    await adapter.updateDashboard(currentDashboardId, {
      Name: byId("editDashboardName").value,
      BackgroundPhotoURL: byId("editDashboardBackground").value,
      Embeddable: byId("editDashboardEmbeddable").checked ? "1" : "0"
    });
    byId("EditDashboardDialog").style.display = "none";
    await refreshDashboards();
  });

  byId("btnCloudDeleteDashboard").addEventListener("click", async () => {
    if (!adapter) return;
    if (!(await ensureCloudWriteAccess())) return;
    const dashboards = await adapter.getDashboards();
    if (dashboards.length <= 1) {
      alert("Cannot delete the last dashboard.");
      return;
    }
    if (!confirm("Delete this dashboard and all its widgets?")) return;
    await adapter.deleteDashboard(currentDashboardId);
    currentDashboardId = "";
    byId("EditDashboardDialog").style.display = "none";
    await refreshDashboards();
  });

  byId("btnSettingsEditCss").addEventListener("click", () => {
    populateCssEditor();
    toggleDisplay("cssEditorBox");
  });

  byId("btnSettingsEditDashboard").addEventListener("click", () => {
    populateEditDashboardDialog();
    toggleDisplay("EditDashboardDialog");
  });

  byId("btnSettingsWidgetManager").addEventListener("click", () => {
    openWidgetManager();
  });

  byId("btnSettingsUserData").addEventListener("click", () => {
    populateUserPreferencesForm();
    toggleDisplay("UserPreferencesDialog");
  });

  byId("btnSaveUserPreferences").addEventListener("click", async () => {
    if (!adapter) return;
    if (!(await ensureCloudWriteAccess())) return;
    let providers;
    let styles;
    try {
      providers = JSON.parse(byId("prefCustomProvidersJson").value || "[]");
      styles = JSON.parse(byId("prefWidgetStylesJson").value || "[]");
    } catch (e) {
      alert("Invalid JSON: " + (e.message || e));
      return;
    }
    if (!Array.isArray(providers) || !Array.isArray(styles)) {
      alert("Custom providers and widget styles must be JSON arrays.");
      return;
    }
    await adapter.updatePreferences({
      siteBaseUrl: byId("prefSiteBaseUrl").value.trim(),
      showSqlWidgetFields: byId("prefShowSqlWidgets").checked
    });
    await adapter.setCustomWidgetProviders(providers);
    await adapter.setWidgetStyles(styles);
    syncUserDataChrome();
    byId("UserPreferencesDialog").style.display = "none";
    await refreshWidgets();
    setStatus("Saved user preferences to Google.");
  });

  byId("btnExportDashboardifyData").addEventListener("click", () => {
    if (!adapter?.dataCache) {
      setStatus("Nothing to export.", true);
      return;
    }
    const snap = ensureDataModelShape(adapter.dataCache);
    const stamp = new Date()
      .toISOString()
      .slice(0, 19)
      .replace("T", "_")
      .replace(/:/g, "-");
    downloadJson(`dashboardify-backup-${stamp}.json`, snap);
    setStatus("Backup file downloaded.");
  });

  byId("btnImportDashboardifyMerge").addEventListener("click", () => {
    backupImportMode = "merge";
    byId("prefImportFile").click();
  });

  byId("btnImportDashboardifyReplace").addEventListener("click", () => {
    backupImportMode = "replace";
    byId("prefImportFile").click();
  });

  byId("prefImportFile").addEventListener("change", async (ev) => {
    const input = ev.target;
    const file = input.files && input.files[0];
    input.value = "";
    if (!file) return;
    if (!adapter) return;
    if (!(await ensureCloudWriteAccess())) return;
    let text;
    try {
      text = await file.text();
    } catch {
      alert("Could not read that file.");
      return;
    }
    let parsed;
    try {
      parsed = JSON.parse(text);
    } catch (e) {
      alert("Invalid JSON: " + (e.message || e));
      return;
    }
    const replaceAll = backupImportMode === "replace";
    if (
      replaceAll &&
      !confirm(
        "Replace ALL data on Google Drive with this backup? This cannot be undone."
      )
    ) {
      return;
    }
    if (
      !replaceAll &&
      !confirm(
        "Merge this backup into your current Drive data? Items that already exist (same dashboard or widget ID) are left unchanged."
      )
    ) {
      return;
    }
    try {
      const normalized = ensureDataModelShape(parsed);
      if (
        !Array.isArray(normalized.dashboards) ||
        !Array.isArray(normalized.widgets)
      ) {
        alert("Backup must include dashboards and widgets arrays.");
        return;
      }
      await adapter.importLegacyData(normalized, { replaceAll });
      syncUserDataChrome();
      populateUserPreferencesForm();
      rebuildWidgetTypeDropdown();
      injectUserWidgetStyles();
      await refreshDashboards();
      byId("UserPreferencesDialog").style.display = "none";
      setStatus(
        replaceAll
          ? "Imported backup (replaced all data on Drive)."
          : "Imported backup (merged with your Drive data)."
      );
    } catch (err) {
      console.error(err);
      alert("Import failed: " + (err.message || err));
    }
  });

  byId("btnClearAllDriveData").addEventListener("click", async () => {
    if (!adapter) return;
    if (!(await ensureCloudWriteAccess())) return;
    if (
      !confirm(
        "Erase ALL Dashboardify data from Google Drive? Every dashboard, widget, and preference in your app data file will be removed. This cannot be undone."
      )
    ) {
      return;
    }
    if (
      !confirm(
        "Final confirmation: your Drive app data will be reset to empty. Continue?"
      )
    ) {
      return;
    }
    try {
      await adapter.resetAppDataToEmpty();
      syncUserDataChrome();
      populateUserPreferencesForm();
      rebuildWidgetTypeDropdown();
      injectUserWidgetStyles();
      await refreshDashboards();
      byId("UserPreferencesDialog").style.display = "none";
      setStatus("All Dashboardify data erased on Google Drive.");
    } catch (err) {
      console.error(err);
      alert("Could not clear data: " + (err.message || err));
    }
  });

  byId("cloudSignOutBtn").addEventListener("click", () => {
    googleAuth?.clearPersistedSession();
    window.location.href = "cloud-login.html";
  });

});
