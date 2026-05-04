/**
 * Test stub for Dashboardify unit tests
 * Matches exact implementation from js/index.js
 */

window.getrooturlpath = function() {
  return "";
};

window.escapeHtmlAttr = function(s) {
  return String(s)
    .replace(/&/g, "&amp;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#39;");
};

window.dashboardifyEscapeHtmlText = function(s) {
  return String(s || "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;");
};

window.escapeHtmlForTextarea = function(c) {
  if (c == null) return "";
  return String(c)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;");
};

window.dashboardifyImageObjectFitFromNotes = function(notes) {
  var fit = String(notes || "").trim().toLowerCase();
  if (fit === "cover" || fit === "contain" || fit === "fill" || fit === "none" || fit === "scale-down") {
    return fit;
  }
  return "contain";
};

window.dashboardifyParseFlashCardsNotes = function(notes) {
  if (!notes) {
    return { sortMethod: "random", displayStyle: "full", autoAdvanceEnabled: false, autoAdvanceMs: 5000, cards: [] };
  }
  try {
    var parsed = JSON.parse(notes);
    return {
      sortMethod: parsed.sortMethod || "random",
      displayStyle: parsed.displayStyle || "full",
      autoAdvanceEnabled: parsed.autoAdvanceEnabled || false,
      autoAdvanceMs: parsed.autoAdvanceMs || 5000,
      cards: parsed.cards || []
    };
  } catch (e) {
    return { sortMethod: "random", displayStyle: "full", autoAdvanceEnabled: false, autoAdvanceMs: 5000, cards: [] };
  }
};

window.dashboardifySerializeFlashCardsModel = function(inputModel) {
  return JSON.stringify({
    sortMethod: inputModel.sortMethod || "random",
    displayStyle: inputModel.displayStyle || "full",
    autoAdvanceEnabled: inputModel.autoAdvanceEnabled || false,
    autoAdvanceMs: inputModel.autoAdvanceMs || 5000,
    cards: inputModel.cards || []
  });
};

window.dashboardifyBuildFlashCardOrder = function(count, model) {
  var arr = [];
  for (var i = 0; i < count; i++) {
    arr.push(i);
  }
  if (model && model.sortMethod === "random") {
    arr.sort(function() { return Math.random() - 0.5; });
  }
  return arr;
};

window.calculateHoursSinceEvent = function(eventDate) {
  if (!eventDate) return 0;
  var event = new Date(eventDate);
  var now = new Date();
  var diff = now - event;
  return Math.floor(diff / (1000 * 60 * 60));
};

window.readCloudDataCacheFromLocalStorage = function() {
  var key = "dashboardify_cloud_data_snapshot";
  try {
    var item = localStorage.getItem(key);
    if (!item) return {};
    return JSON.parse(item);
  } catch (e) {
    return {};
  }
};

window.writeCloudDataCacheToLocalStorage = function(data) {
  var key = "dashboardify_cloud_data_snapshot";
  localStorage.setItem(key, JSON.stringify(data));
};

window.clearCloudDataCacheFromLocalStorage = function() {
  var key = "dashboardify_cloud_data_snapshot";
  localStorage.removeItem(key);
};

window.ensureDataModelShape = function(input) {
  var data = input || {};
  return {
    version: data.version || 2,
    meta: data.meta || { app: "Dashboardify", appVersion: "1.0.0", lastUpdatedUtc: new Date().toISOString(), importedFrom: null },
    userCss: data.userCss || "",
    preferences: data.preferences || { siteBaseUrl: "", showSqlWidgetFields: false, lastSelectedDashboardId: "" },
    customWidgetProviders: data.customWidgetProviders || [],
    widgetStyles: data.widgetStyles || [],
    storedWidgets: data.storedWidgets || [],
    dashboards: data.dashboards || [],
    widgets: data.widgets || []
  };
};

window.collectWidgetPayloadFromForm = function() {
  var widgetType = (document.getElementById("ddlWidgetType2") || {}).value || "";
  var payload = {
    WidgetType: widgetType,
    BookmarkDisplayText: (document.getElementById("txtWidgetDisplayText") || {}).value || "",
    WidgetURL: (document.getElementById("txtWidgetURL") || {}).value || "",
    WidgetCSSClass: (document.getElementById("txtCSSClass") || {}).value || "",
    Notes: (document.getElementById("txtNotes") || {}).value || "",
    PositionX: (document.getElementById("txtpositionx2") || {}).value || "",
    PositionY: (document.getElementById("txtpositiony2") || {}).value || "",
    SizeX: (document.getElementById("txtsizeX2") || {}).value || "",
    SizeY: (document.getElementById("txtsizeY2") || {}).value || ""
  };
  
  var dateEl = document.getElementById("datepicker");
  if (dateEl && (widgetType === "Countdown" || widgetType === "CountUp_Hours")) {
    payload.Notes = dateEl.value || payload.Notes;
  }
  if (widgetType === "Image") {
    var fitEl = document.getElementById("ddlImageObjectFit");
    payload.Notes = fitEl ? (fitEl.value || "").trim() : "";
  }
  if (widgetType === "Clock") {
    var tzEl = document.getElementById("ddlClockTimezone");
    payload.Notes = tzEl ? (tzEl.value || "").trim() : "";
  }
  
  return payload;
};

window.echo = function(stringData) {
  var container = document.getElementById("widgetcontainer");
  if (container) {
    container.innerHTML += stringData;
  }
};

window.getrooturlpath = window.getrooturlpath || function() { return ""; };