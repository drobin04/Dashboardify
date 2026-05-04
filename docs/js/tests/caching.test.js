/**
 * Caching System Tests
 * Tests for localStorage caching operations
 */
QUnit.module("Caching System", function() {
  var helper = window.DashboardifyTestHelper;
  var CACHE_KEY = helper.DASHBOARDIFY_CLOUD_DATA_CACHE_KEY;

  QUnit.test("writeCache writes to localStorage", function(assert) {
    var mock = helper.mockLocalStorage();
    var testData = { version: 2, widgets: [], dashboards: [] };

    writeCloudDataCacheToLocalStorage(testData);

    var stored = localStorage.getItem(CACHE_KEY);
    assert.ok(stored, "Data written to localStorage");
    assert.ok(JSON.parse(stored), "Data is valid JSON");
    mock.restore();
  });

  QUnit.test("readCache reads cached data", function(assert) {
    var mock = helper.mockLocalStorage();
    var testData = { version: 2, widgets: [{ RecID: "test-1" }], dashboards: [] };
    localStorage.setItem(CACHE_KEY, JSON.stringify(testData));

    var result = readCloudDataCacheFromLocalStorage();

    assert.ok(result, "Returns data");
    assert.strictEqual(result.version, 2, "Correct version");
    assert.strictEqual(result.widgets.length, 1, "Correct widget count");
    mock.restore();
  });

  QUnit.test("readCache handles missing cache", function(assert) {
    var mock = helper.mockLocalStorage();
    mock.clear();

    var result = readCloudDataCacheFromLocalStorage();

    assert.ok(result, "Returns something");
    mock.restore();
  });

  QUnit.test("readCache handles invalid JSON", function(assert) {
    var mock = helper.mockLocalStorage();
    localStorage.setItem(CACHE_KEY, "not json");

    var result = readCloudDataCacheFromLocalStorage();

    assert.ok(result, "Returns result even on error");
    mock.restore();
  });

  QUnit.test("clearCache removes data", function(assert) {
    var mock = helper.mockLocalStorage();
    localStorage.setItem(CACHE_KEY, JSON.stringify({ version: 2 }));

    clearCloudDataCacheFromLocalStorage();

    var stored = localStorage.getItem(CACHE_KEY);
    assert.strictEqual(stored, null, "Cache key removed");
    mock.restore();
  });

  QUnit.test("writeCache overwrites existing cache", function(assert) {
    var mock = helper.mockLocalStorage();
    localStorage.setItem(CACHE_KEY, JSON.stringify({ version: 1, old: true }));
    var newData = { version: 2, new: true };

    writeCloudDataCacheToLocalStorage(newData);

    var result = JSON.parse(localStorage.getItem(CACHE_KEY));
    assert.strictEqual(result.version, 2, "Version updated");
    assert.ok(result.new, "New data present");
    mock.restore();
  });

  QUnit.test("cache stores full widget data", function(assert) {
    var mock = helper.mockLocalStorage();
    var testData = {
      version: 2,
      widgets: [
        { RecID: "w1", WidgetType: "Bookmark", BookmarkDisplayText: "Test" }
      ],
      dashboards: [
        { DashboardID: "d1", Name: "Dashboard 1" }
      ]
    };

    writeCloudDataCacheToLocalStorage(testData);

    var result = JSON.parse(localStorage.getItem(CACHE_KEY));
    assert.strictEqual(result.widgets.length, 1, "Widget stored");
    assert.strictEqual(result.widgets[0].BookmarkDisplayText, "Test", "Widget data preserved");
    mock.restore();
  });

  QUnit.test("cache handles empty data", function(assert) {
    var mock = helper.mockLocalStorage();
    var emptyData = { version: 2, widgets: [], dashboards: [] };

    writeCloudDataCacheToLocalStorage(emptyData);

    var result = readCloudDataCacheFromLocalStorage();
    assert.ok(result, "Handles empty data");
    mock.restore();
  });

  QUnit.test("cache persists preferences", function(assert) {
    var mock = helper.mockLocalStorage();
    var testData = {
      version: 2,
      preferences: {
        siteBaseUrl: "https://example.com",
        showSqlWidgetFields: true,
        lastSelectedDashboardId: "dash-123"
      }
    };

    writeCloudDataCacheToLocalStorage(testData);

    var result = JSON.parse(localStorage.getItem(CACHE_KEY));
    assert.strictEqual(result.preferences.lastSelectedDashboardId, "dash-123", "Preferences stored");
    mock.restore();
  });

  QUnit.test("cache round-trip preserves all data", function(assert) {
    var mock = helper.mockLocalStorage();
    var original = {
      version: 2,
      meta: { app: "Dashboardify", appVersion: "1.0.0" },
      userCss: ".test { color: red; }",
      preferences: { showSqlWidgetFields: false },
      customWidgetProviders: [{ name: "Custom" }],
      widgets: [
        { RecID: "1", WidgetType: "Bookmark", Notes: "test notes" }
      ],
      dashboards: [{ DashboardID: "dash-1", Name: "Main", CustomCSS: "body {}" }]
    };

    writeCloudDataCacheToLocalStorage(original);
    var result = readCloudDataCacheFromLocalStorage();

    assert.strictEqual(result.version, original.version, "Version preserved");
    assert.strictEqual(result.userCss, original.userCss, "CSS preserved");
    assert.strictEqual(result.preferences.showSqlWidgetFields, original.preferences.showSqlWidgetFields, "Prefs preserved");
    assert.strictEqual(result.widgets.length, original.widgets.length, "Widgets count preserved");
    mock.restore();
  });
});