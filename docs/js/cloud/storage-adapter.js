import {
  createEmptyDataModel,
  defaultPreferences,
  ensureDataModelShape
} from "./schema.js";

export const DASHBOARDIFY_CLOUD_DATA_CACHE_KEY = "dashboardify_cloud_data_snapshot";

export function readCloudDataCacheFromLocalStorage() {
  try {
    const raw = localStorage.getItem(DASHBOARDIFY_CLOUD_DATA_CACHE_KEY);
    if (!raw) return null;
    return ensureDataModelShape(JSON.parse(raw));
  } catch {
    return null;
  }
}

export function writeCloudDataCacheToLocalStorage(data) {
  try {
    const normalized = ensureDataModelShape(data);
    localStorage.setItem(
      DASHBOARDIFY_CLOUD_DATA_CACHE_KEY,
      JSON.stringify(normalized)
    );
  } catch {
    /* quota / private mode */
  }
}

export function clearCloudDataCacheFromLocalStorage() {
  try {
    localStorage.removeItem(DASHBOARDIFY_CLOUD_DATA_CACHE_KEY);
  } catch {
    /* ignore */
  }
}

export class CloudStorageAdapter {
  /** @param {object | null} driveClient GoogleDriveAppDataClient instance, or null for view-only cached data. */
  constructor(driveClient) {
    this.driveClient = driveClient;
    this.fileMeta = null;
    this.dataCache = null;
  }

  /** Load dashboards/widgets from a previously saved local snapshot (no network). */
  hydrateFromLocalCacheModel(model) {
    this.dataCache = ensureDataModelShape(model);
    this.fileMeta = null;
  }

  async loadAppData() {
    if (!this.driveClient) {
      if (!this.dataCache) {
        throw new Error("No cached app data.");
      }
      return this.dataCache;
    }

    const existing = await this.driveClient.findDataFile();
    if (!existing) {
      const initial = createEmptyDataModel();
      const created = await this.driveClient.createDataFile(
        JSON.stringify(initial, null, 2)
      );
      this.fileMeta = created;
      this.dataCache = initial;
      writeCloudDataCacheToLocalStorage(this.dataCache);
      return initial;
    }

    this.fileMeta = existing;
    const raw = await this.driveClient.readFileContent(existing.id);
    const parsed = raw ? JSON.parse(raw) : createEmptyDataModel();
    this.dataCache = ensureDataModelShape(parsed);
    writeCloudDataCacheToLocalStorage(this.dataCache);
    return this.dataCache;
  }

  async saveAppData(data) {
    if (!this.driveClient) {
      throw new Error("Sign in to save changes to Google Drive.");
    }
    if (!this.fileMeta || !this.fileMeta.id) {
      await this.loadAppData();
    }
    const normalized = ensureDataModelShape(data);
    const updated = await this.driveClient.updateFileContent(
      this.fileMeta.id,
      JSON.stringify(normalized, null, 2)
    );
    this.fileMeta = updated;
    this.dataCache = normalized;
    writeCloudDataCacheToLocalStorage(this.dataCache);
    return normalized;
  }

  async getDashboards() {
    const data = this.dataCache || (await this.loadAppData());
    return data.dashboards;
  }

  async getWidgetsForDashboard(dashboardId) {
    const data = this.dataCache || (await this.loadAppData());
    return data.widgets.filter((w) => String(w.DashboardRecID) === String(dashboardId));
  }

  async createDashboard(name) {
    const data = this.dataCache || (await this.loadAppData());
    const dashboardId = crypto.randomUUID();
    data.dashboards.push({
      DashboardID: dashboardId,
      Name: name || "Dashboard",
      CustomCSS: "",
      BackgroundPhotoURL: "",
      Embeddable: "0"
    });
    await this.saveAppData(data);
    return dashboardId;
  }

  async updateDashboard(dashboardId, patch) {
    const data = this.dataCache || (await this.loadAppData());
    const idx = data.dashboards.findIndex(
      (d) => String(d.DashboardID) === String(dashboardId)
    );
    if (idx < 0) return null;
    data.dashboards[idx] = { ...data.dashboards[idx], ...patch };
    await this.saveAppData(data);
    return data.dashboards[idx];
  }

  async deleteDashboard(dashboardId) {
    const data = this.dataCache || (await this.loadAppData());
    data.dashboards = data.dashboards.filter(
      (d) => String(d.DashboardID) !== String(dashboardId)
    );
    data.widgets = data.widgets.filter(
      (w) => String(w.DashboardRecID) !== String(dashboardId)
    );
    await this.saveAppData(data);
  }

  async updatePreferences(patch) {
    const data = this.dataCache || (await this.loadAppData());
    data.preferences = {
      ...defaultPreferences(),
      ...(data.preferences || {}),
      ...(patch && typeof patch === "object" ? patch : {})
    };
    await this.saveAppData(data);
    return data.preferences;
  }

  async setCustomWidgetProviders(list) {
    const data = this.dataCache || (await this.loadAppData());
    data.customWidgetProviders = Array.isArray(list) ? list : [];
    await this.saveAppData(data);
  }

  async setWidgetStyles(list) {
    const data = this.dataCache || (await this.loadAppData());
    data.widgetStyles = Array.isArray(list) ? list : [];
    await this.saveAppData(data);
  }

  async setStoredWidgets(list) {
    const data = this.dataCache || (await this.loadAppData());
    data.storedWidgets = Array.isArray(list) ? list : [];
    await this.saveAppData(data);
  }

  async upsertWidget(widget) {
    const data = this.dataCache || (await this.loadAppData());
    const recId = widget.RecID || `cloud-${crypto.randomUUID()}`;
    const idx = data.widgets.findIndex((w) => String(w.RecID) === String(recId));
    const prior = idx >= 0 ? data.widgets[idx] : null;
    const defaults = {
      BookmarkDisplayText: "",
      WidgetURL: "",
      WidgetCSSClass: "",
      Notes: "",
      PositionX: "0",
      PositionY: "0",
      SizeX: "300",
      SizeY: "200",
      RecID: recId,
      DashboardRecID: widget.DashboardRecID,
      WidgetType: "Bookmark"
    };
    const merged = prior
      ? { ...prior, ...widget, RecID: recId }
      : { ...defaults, ...widget, RecID: recId };
    if (idx >= 0) {
      data.widgets[idx] = merged;
    } else {
      data.widgets.push(merged);
    }
    await this.saveAppData(data);
    return merged;
  }

  /**
   * Partial update (e.g. drag position or resize) without clobbering other fields.
   */
  async patchWidget(recId, patch) {
    const data = this.dataCache || (await this.loadAppData());
    const idx = data.widgets.findIndex((w) => String(w.RecID) === String(recId));
    if (idx < 0) {
      return null;
    }
    const cur = data.widgets[idx];
    const next = { ...cur };
    for (const [k, v] of Object.entries(patch || {})) {
      if (v !== undefined && v !== null) {
        next[k] = v;
      }
    }
    data.widgets[idx] = next;
    await this.saveAppData(data);
    return next;
  }

  async deleteWidget(recId) {
    const data = this.dataCache || (await this.loadAppData());
    data.widgets = data.widgets.filter((w) => String(w.RecID) !== String(recId));
    await this.saveAppData(data);
  }

  async importLegacyData(legacyData, options = {}) {
    const replaceAll = options.replaceAll === true;
    const incomingDashboards = Array.isArray(legacyData.dashboards) ? legacyData.dashboards : [];
    const incomingWidgets = Array.isArray(legacyData.widgets) ? legacyData.widgets : [];
    const incomingUserCss =
      typeof legacyData.userCss === "string" ? legacyData.userCss : "";

    let current;
    if (replaceAll) {
      current = createEmptyDataModel();
      current.dashboards = incomingDashboards.slice();
      current.widgets = incomingWidgets.slice();
      current.userCss = incomingUserCss;
      if (Array.isArray(legacyData.customWidgetProviders)) {
        current.customWidgetProviders = legacyData.customWidgetProviders.slice();
      }
      if (Array.isArray(legacyData.widgetStyles)) {
        current.widgetStyles = legacyData.widgetStyles.slice();
      }
      if (Array.isArray(legacyData.storedWidgets)) {
        current.storedWidgets = legacyData.storedWidgets.slice();
      }
      if (legacyData.preferences && typeof legacyData.preferences === "object") {
        current.preferences = {
          ...defaultPreferences(),
          ...legacyData.preferences
        };
      }
    } else {
      current = this.dataCache || (await this.loadAppData());
      const dashboardIds = new Set(current.dashboards.map((d) => String(d.DashboardID)));
      const widgetIds = new Set(current.widgets.map((w) => String(w.RecID)));

      for (const d of incomingDashboards) {
        if (!dashboardIds.has(String(d.DashboardID))) {
          current.dashboards.push(d);
        }
      }
      for (const w of incomingWidgets) {
        if (!widgetIds.has(String(w.RecID))) {
          current.widgets.push(w);
        }
      }
      if (incomingUserCss !== "") {
        current.userCss = incomingUserCss;
      }
      if (Array.isArray(legacyData.customWidgetProviders)) {
        const names = new Set(
          current.customWidgetProviders.map((p) => String(p.WidgetProviderName))
        );
        for (const p of legacyData.customWidgetProviders) {
          const n = p && p.WidgetProviderName;
          if (n && !names.has(String(n))) {
            current.customWidgetProviders.push(p);
            names.add(String(n));
          }
        }
      }
      if (Array.isArray(legacyData.widgetStyles)) {
        current.widgetStyles = current.widgetStyles.concat(legacyData.widgetStyles);
      }
      if (Array.isArray(legacyData.storedWidgets)) {
        const rowSig = (w) => JSON.stringify(w);
        const seen = new Set((current.storedWidgets || []).map(rowSig));
        for (const sw of legacyData.storedWidgets) {
          const s = rowSig(sw);
          if (!seen.has(s)) {
            current.storedWidgets.push(sw);
            seen.add(s);
          }
        }
      }
      if (legacyData.preferences && typeof legacyData.preferences === "object") {
        current.preferences = {
          ...defaultPreferences(),
          ...(current.preferences || {}),
          ...legacyData.preferences
        };
      }
    }

    current.meta.importedFrom = "sqlite";
    current.meta.importTimestampUtc = new Date().toISOString();
    current.meta.sourceVersion =
      legacyData.meta?.sourceVersion ||
      legacyData.sourceVersion ||
      "legacy-sqlite";

    await this.saveAppData(current);
    return {
      importedDashboards: incomingDashboards.length,
      importedWidgets: incomingWidgets.length,
      replacedAll: replaceAll
    };
  }
}
