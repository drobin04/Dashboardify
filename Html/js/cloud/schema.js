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
      sourceVersion: null
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

export function ensureDataModelShape(input) {
  const base = createEmptyDataModel();
  const data = input && typeof input === "object" ? input : {};

  const normalized = {
    version: Number.isInteger(data.version) ? data.version : base.version,
    meta: {
      ...base.meta,
      ...(data.meta && typeof data.meta === "object" ? data.meta : {})
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
    widgets: Array.isArray(data.widgets) ? data.widgets : []
  };

  normalized.meta.lastUpdatedUtc = new Date().toISOString();
  return normalized;
}
