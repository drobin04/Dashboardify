/**
 * Data Model Tests
 * Tests for data validation and normalization
 */
QUnit.module("Data Model", function() {
  var helper = window.DashboardifyTestHelper;

  QUnit.test("ensureDataModelShape adds version", function(assert) {
    var input = {};

    var result = ensureDataModelShape(input);

    assert.ok(result.version, "Version field added");
    assert.strictEqual(typeof result.version, "number", "Version is number");
  });

  QUnit.test("ensureDataModelShape has required arrays", function(assert) {
    var input = {};

    var result = ensureDataModelShape(input);

    assert.ok(Array.isArray(result.dashboards), "dashboards array exists");
    assert.ok(Array.isArray(result.widgets), "widgets array exists");
  });

  QUnit.test("ensureDataModelShape preserves existing data", function(assert) {
    var input = {
      version: 2,
      widgets: [{ RecID: "test-1" }],
      dashboards: [{ DashboardID: "dash-1" }]
    };

    var result = ensureDataModelShape(input);

    assert.strictEqual(result.version, 2, "Version preserved");
    assert.strictEqual(result.widgets.length, 1, "Widgets preserved");
  });

  QUnit.test("ensureDataModelShape adds meta if missing", function(assert) {
    var input = {};

    var result = ensureDataModelShape(input);

    assert.ok(result.meta, "Meta object added");
    assert.ok(result.meta.app, "Meta app field added");
  });

  QUnit.test("ensureDataModelShape adds preferences if missing", function(assert) {
    var input = {};

    var result = ensureDataModelShape(input);

    assert.ok(result.preferences, "Preferences object added");
    assert.ok("showSqlWidgetFields" in result.preferences, "showSqlWidgetFields field added");
  });

  QUnit.test("widget has required fields", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Bookmark"
    });

    assert.ok(widget.RecID, "Widget has RecID");
    assert.ok(widget.DashboardRecID, "Widget has DashboardRecID");
    assert.ok(widget.WidgetType, "Widget has WidgetType");
  });

  QUnit.test("widget has positioning fields", function(assert) {
    var widget = helper.createWidget({});

    assert.ok("PositionX" in widget, "PositionX exists");
    assert.ok("PositionY" in widget, "PositionY exists");
    assert.ok("SizeX" in widget, "SizeX exists");
    assert.ok("SizeY" in widget, "SizeY exists");
  });

  QUnit.test("dashboard has required fields", function(assert) {
    var dashboard = helper.createDashboard({});

    assert.ok(dashboard.DashboardID, "Dashboard has ID");
    assert.ok(dashboard.Name, "Dashboard has Name");
  });

  QUnit.test("dashboard optional fields", function(assert) {
    var dashboard = helper.createDashboard({
      CustomCSS: ".custom { color: blue; }",
      BackgroundPhotoURL: "https://example.com/bg.jpg"
    });

    assert.strictEqual(dashboard.CustomCSS, ".custom { color: blue; }", "CustomCSS stored");
    assert.strictEqual(dashboard.BackgroundPhotoURL, "https://example.com/bg.jpg", "Background stored");
  });

  QUnit.test("widget supports all content types", function(assert) {
    var types = ["Bookmark", "IFrame", "Notes", "Image", "Flash Cards", "Countdown", "Clock"];

    types.forEach(function(type) {
      var widget = helper.createWidget({ WidgetType: type });
      assert.strictEqual(widget.WidgetType, type, "Widget type: " + type);
    });
  });

  QUnit.test("app data structure validates", function(assert) {
    var appData = helper.createAppData({});

    assert.ok(appData.version, "Has version");
    assert.ok(Array.isArray(appData.dashboards), "Has dashboards array");
    assert.ok(Array.isArray(appData.widgets), "Has widgets array");
    assert.ok(appData.preferences, "Has preferences");
  });

  QUnit.test("widget RecID uniqueness", function(assert) {
    var ids = {};
    var unique = true;
    for (var i = 0; i < 100; i++) {
      var id = helper.generateId("widget");
      if (ids[id]) {
        unique = false;
        break;
      }
      ids[id] = true;
    }
    assert.ok(unique, "Generated IDs are unique");
  });

  QUnit.test("widget can store Notes data", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Clock",
      Notes: "America/New_York"
    });

    assert.strictEqual(widget.Notes, "America/New_York", "Notes stored");
  });

  QUnit.test("widget can store HTML content", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "HTMLEmbed",
      Notes: "<div>Custom</div>"
    });

    assert.strictEqual(widget.Notes, "<div>Custom</div>", "HTML Notes stored");
  });

  QUnit.test("widget can store Flash Cards model", function(assert) {
    var model = { sortMethod: "random", cards: [{ q: "Q", a: "A" }] };
    var widget = helper.createWidget({
      WidgetType: "Flash Cards",
      Notes: JSON.stringify(model)
    });

    assert.ok(widget.Notes, "Flash Cards Notes stored");
    var parsed = JSON.parse(widget.Notes);
    assert.strictEqual(parsed.sortMethod, "random", "Model data preserved");
  });
});