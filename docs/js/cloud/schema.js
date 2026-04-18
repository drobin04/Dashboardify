export const DASHBOARDIFY_DATA_VERSION = 2;

export function defaultPreferences() {
  return {
    siteBaseUrl: "",
    showSqlWidgetFields: false,
    lastSelectedDashboardId: ""
  };
}

export function createEmptyDataModel() {
  return {
    version: DASHBOARDIFY_DATA_VERSION,
    meta: {
      app: "Dashboardify",
      appVersion: "cloud-v1",
      lastUpdatedUtc: new Date().toISOString(),
      importedFrom: null,
      importTimestampUtc: null,
      sourceVersion: null,
      /** When false, cloud boot may seed starter Notes widgets once. Omitted in older files = skip NUX. */
      nuxWelcomePackApplied: false
    },
    userCss: "",
    preferences: defaultPreferences(),
    customWidgetProviders: [],
    widgetStyles: [],
    storedWidgets: [],
    dashboards: [],
    widgets: []
  };
}

/** Bookmark: empty or 0,0 means flow layout (auto column), not absolute top-left. */
export function normalizeBookmarkWidgetPosition(w) {
  if (!w || typeof w !== "object") {
    return w;
  }
  if (String(w.WidgetType || "").trim() !== "Bookmark") {
    return w;
  }
  const sx = w.PositionX == null ? "" : String(w.PositionX).trim();
  const sy = w.PositionY == null ? "" : String(w.PositionY).trim();
  const x = parseFloat(sx);
  const y = parseFloat(sy);
  const autoTile =
    (sx === "" && sy === "") ||
    (Number.isFinite(x) && Number.isFinite(y) && x === 0 && y === 0);
  if (!autoTile) {
    return w;
  }
  return { ...w, PositionX: "", PositionY: "" };
}

export function ensureDataModelShape(input) {
  const base = createEmptyDataModel();
  const data = input && typeof input === "object" ? input : {};

  const rawWidgets = Array.isArray(data.widgets) ? data.widgets : [];

  const incomingMeta =
    data.meta && typeof data.meta === "object" ? data.meta : {};
  const hasNuxKey = Object.prototype.hasOwnProperty.call(
    incomingMeta,
    "nuxWelcomePackApplied"
  );
  const nuxWelcomePackApplied = hasNuxKey
    ? Boolean(incomingMeta.nuxWelcomePackApplied)
    : true;

  const normalized = {
    version: Number.isInteger(data.version) ? data.version : base.version,
    meta: {
      ...base.meta,
      ...incomingMeta,
      nuxWelcomePackApplied
    },
    userCss: typeof data.userCss === "string" ? data.userCss : base.userCss,
    preferences: {
      ...defaultPreferences(),
      ...(data.preferences && typeof data.preferences === "object"
        ? data.preferences
        : {})
    },
    customWidgetProviders: Array.isArray(data.customWidgetProviders)
      ? data.customWidgetProviders
      : [],
    widgetStyles: Array.isArray(data.widgetStyles) ? data.widgetStyles : [],
    storedWidgets: Array.isArray(data.storedWidgets) ? data.storedWidgets : [],
    dashboards: Array.isArray(data.dashboards) ? data.dashboards : [],
    widgets: rawWidgets.map((w) => normalizeBookmarkWidgetPosition(w))
  };

  normalized.meta.lastUpdatedUtc = new Date().toISOString();
  return normalized;
}
