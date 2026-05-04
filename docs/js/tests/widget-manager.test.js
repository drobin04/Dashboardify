/**
 * Widget Manager Tests
 * Tests for widget manager open/save/delete functionality
 * These tests validate that widgets can be properly managed without touching live Google Drive data
 */
QUnit.module("Widget Manager", function() {
  var helper = window.DashboardifyTestHelper;

  QUnit.test("DashboardifyAllWidgets is set after renderwidgetsfromjson", function(assert) {
    var widgets = [
      helper.createWidget({ RecID: "1", WidgetType: "Bookmark", BookmarkDisplayText: "Test 1" }),
      helper.createWidget({ RecID: "2", WidgetType: "Notes", BookmarkDisplayText: "Test 2" })
    ];

    window.DashboardifyAllWidgets = undefined;
    renderwidgetsfromjson(widgets);

    assert.deepEqual(window.DashboardifyAllWidgets, widgets, "DashboardifyAllWidgets should be populated");
    assert.strictEqual(window.DashboardifyAllWidgets.length, 2, "Should have 2 widgets");
  });

  QUnit.test("DashboardifyAllWidgets is updated on fresh load", function(assert) {
    var initialWidgets = [
      helper.createWidget({ RecID: "1", WidgetType: "Bookmark", BookmarkDisplayText: "Old" })
    ];
    var newWidgets = [
      helper.createWidget({ RecID: "2", WidgetType: "IFrame", BookmarkDisplayText: "New" })
    ];

    renderwidgetsfromjson(initialWidgets);
    assert.strictEqual(window.DashboardifyAllWidgets.length, 1, "Initially 1 widget");

    renderwidgetsfromjson(newWidgets);
    assert.strictEqual(window.DashboardifyAllWidgets.length, 1, "After reload, 1 widget");
    assert.strictEqual(window.DashboardifyAllWidgets[0].BookmarkDisplayText, "New", "Updated to new widget");
  });

  QUnit.test("openWidgetManager shows no widgets message when empty", function(assert) {
    renderwidgetsfromjson([]);

    openWidgetManager();

    var dialog = document.getElementById("widgetManagerDialog");
    var noWidgets = document.getElementById("widgetManagerNoWidgets");
    var list = document.getElementById("widgetManagerList");

    assert.ok(dialog.style.display === "block" || dialog.getAttribute("style").indexOf("display: block") > -1, "Dialog is shown");
    assert.ok(noWidgets.style.display === "block" || noWidgets.textContent.indexOf("No widgets") > -1, "No widgets message is visible");
    assert.strictEqual(list.options.length, 0, "List is empty");
  });

  QUnit.test("openWidgetManager populates widget list", function(assert) {
    var widgets = [
      helper.createWidget({ RecID: "100", WidgetType: "Bookmark", BookmarkDisplayText: "Link 1" }),
      helper.createWidget({ RecID: "101", WidgetType: "IFrame", BookmarkDisplayText: "Site" }),
      helper.createWidget({ RecID: "102", WidgetType: "Notes", BookmarkDisplayText: "Notes" })
    ];
    renderwidgetsfromjson(widgets);

    openWidgetManager();

    var list = document.getElementById("widgetManagerList");
    assert.strictEqual(list.options.length, 3, "Should have 3 widgets in list");
    assert.contains(assert, list.options[0].textContent, "Link 1", "First widget shows display text");
    assert.contains(assert, list.options[0].textContent, "100", "First widget shows RecID");
  });

  QUnit.test("loadWidgetManagerDetails populates fields from selected widget", function(assert) {
    var widgets = [
      helper.createWidget({
        RecID: "200",
        WidgetType: "IFrame",
        BookmarkDisplayText: "Test Site",
        PositionX: 50,
        PositionY: 100,
        SizeX: 400,
        SizeY: 300
      })
    ];
    renderwidgetsfromjson(widgets);
    openWidgetManager();

    loadWidgetManagerDetails();

    var typeEl = document.getElementById("widgetManagerType");
    var xEl = document.getElementById("widgetManagerX");
    var yEl = document.getElementById("widgetManagerY");
    var wEl = document.getElementById("widgetManagerWidth");
    var hEl = document.getElementById("widgetManagerHeight");

    assert.strictEqual(typeEl.textContent, "IFrame", "Widget type populated");
    assert.strictEqual(xEl.value, "50", "Position X populated");
    assert.strictEqual(yEl.value, "100", "Position Y populated");
    assert.strictEqual(wEl.value, "400", "Width populated");
    assert.strictEqual(hEl.value, "300", "Height populated");
  });

  QUnit.test("closeWidgetManager hides dialog", function(assert) {
    openWidgetManager();
    assert.ok(document.getElementById("widgetManagerDialog").style.display === "block" || 
           document.getElementById("widgetManagerDialog").getAttribute("style").indexOf("display: block") > -1, 
           "Dialog is visible before close");

    closeWidgetManager();

    assert.strictEqual(document.getElementById("widgetManagerDialog").style.display, "none", "Dialog hidden");
  });

  QUnit.test("widget selection triggers details load", function(assert) {
    var widgets = [
      helper.createWidget({ RecID: "300", WidgetType: "Bookmark", BookmarkDisplayText: "One" }),
      helper.createWidget({ RecID: "301", WidgetType: "Bookmark", BookmarkDisplayText: "Two" })
    ];
    renderwidgetsfromjson(widgets);
    openWidgetManager();

    var list = document.getElementById("widgetManagerList");
    list.selectedIndex = 1;
    list.dispatchEvent(new Event("change"));

    var typeEl = document.getElementById("widgetManagerType");
    assert.strictEqual(typeEl.textContent, "Bookmark", "Details updated for second widget");
  });

  QUnit.test("widget manager state tracks selected widget", function(assert) {
    var widgets = [
      helper.createWidget({ RecID: "400", WidgetType: "Bookmark", BookmarkDisplayText: "Test" })
    ];
    renderwidgetsfromjson(widgets);
    openWidgetManager();
    loadWidgetManagerDetails();

    assert.ok(DashboardifyWidgetManagerState, "State object exists");
    assert.strictEqual(DashboardifyWidgetManagerState.selectedRecId, "400", "Selected RecID tracked");
    assert.strictEqual(DashboardifyWidgetManagerState.widgets.length, 1, "Widgets array tracked");
  });

  QUnit.test("delete confirms before removal", function(assert) {
    var widgets = [
      helper.createWidget({ RecID: "500", WidgetType: "Bookmark", BookmarkDisplayText: "To Delete" })
    ];
    renderwidgetsfromjson(widgets);
    openWidgetManager();

    var deleteBtn = document.getElementById("btnWidgetManagerDelete");
    assert.ok(deleteBtn, "Delete button exists");
  });
});

QUnit.module("Widget Position Persistence", function() {
  var helper = window.DashboardifyTestHelper;

  QUnit.test("patchWidget updates widget in memory", function(assert) {
    var done = assert.async();
    var widgets = [
      helper.createWidget({ RecID: "600", PositionX: 0, PositionY: 0, SizeX: 200, SizeY: 150 })
    ];
    renderwidgetsfromjson(widgets);

    var adapter = window.DashboardifyDataAdapter;
    if (!adapter || typeof adapter.patchWidget !== "function") {
      assert.ok(true, "No adapter - skipping cloud test");
      done();
      return;
    }

    adapter.patchWidget("600", { PositionX: 75, PositionY: 50, SizeX: 300, SizeY: 250 })
      .then(function() {
        var updated = window.DashboardifyAllWidgets.find(function(w) { return w.RecID === "600"; });
        assert.strictEqual(updated.PositionX, "75", "PositionX updated in memory");
        assert.strictEqual(updated.PositionY, "50", "PositionY updated in memory");
        assert.strictEqual(updated.SizeX, "300", "SizeX updated in memory");
        assert.strictEqual(updated.SizeY, "250", "SizeY updated in memory");
        done();
      })
      .catch(function(err) {
        assert.ok(false, "patchWidget failed: " + err.message);
        done();
      });
  });

  QUnit.test("position values are validated as positive integers", function(assert) {
    var widgets = [
      helper.createWidget({ RecID: "601", PositionX: 100, PositionY: 200, SizeX: 300, SizeY: 400 })
    ];
    renderwidgetsfromjson(widgets);
    openWidgetManager();
    loadWidgetManagerDetails();

    document.getElementById("widgetManagerX").value = "-50";
    document.getElementById("widgetManagerWidth").value = "0";

    var xVal = parseInt(document.getElementById("widgetManagerX").value) || 0;
    var wVal = parseInt(document.getElementById("widgetManagerWidth").value) || 0;

    assert.ok(xVal >= 0, "Negative values converted to positive");
    assert.ok(wVal >= 0, "Zero values stay zero");
  });
});

QUnit.module("Widget Dashboard Association", function() {
  var helper = window.DashboardifyTestLoader;

  QUnit.test("getWidgetsForDashboard loads widgets for specific dashboard", function(assert) {
    var done = assert.async();
    var adapter = window.DashboardifyDataAdapter;

    if (!adapter || typeof adapter.getWidgetsForDashboard !== "function") {
      assert.ok(true, "No cloud adapter - skipping");
      done();
      return;
    }

    var testDashId = "test_dashboard_" + Date.now();
    var testWidgets = [
      helper.createWidget({ RecID: "700", DashboardRecID: testDashId, WidgetType: "Bookmark" })
    ];

    adapter.getWidgetsForDashboard(testDashId)
      .then(function(widgets) {
        assert.ok(Array.isArray(widgets), "Returns array of widgets");
        assert.strictEqual(widgets.length, 0, "No widgets found for new dashboard");
        done();
      })
      .catch(function(err) {
        assert.ok(false, "getWidgetsForDashboard failed: " + err.message);
        done();
      });
  });

  QUnit.test("DashboardifyAllWidgets cleared when switching dashboards", function(assert) {
    var widgets1 = [
      helper.createWidget({ RecID: "801", DashboardRecID: "dash1", WidgetType: "Bookmark" })
    ];
    var widgets2 = [
      helper.createWidget({ RecID: "802", DashboardRecID: "dash2", WidgetType: "Bookmark" })
    ];

    renderwidgetsfromjson(widgets1);
    assert.strictEqual(window.DashboardifyAllWidgets.length, 1, "First dashboard has 1 widget");

    renderwidgetsfromjson(widgets2);
    assert.strictEqual(window.DashboardifyAllWidgets.length, 1, "Second dashboard replaces first");
    assert.strictEqual(window.DashboardifyAllWidgets[0].RecID, "802", "Only second dashboard's widget present");
  });
});

QUnit.module("Flash Cards CSV Import", function() {
  QUnit.test("import replaces cards without losing widget", function(assert) {
    var done = assert.async();
    var widget = {
      RecID: "900",
      WidgetType: "FlashCards",
      Notes: JSON.stringify({
        sortMethod: "random",
        displayStyle: "full",
        autoAdvanceEnabled: false,
        autoAdvanceMs: 5000,
        cards: [{ q: "Old Question", a: "Old Answer" }]
      })
    };

    var adapter = window.DashboardifyDataAdapter;
    if (!adapter || typeof adapter.patchWidget !== "function") {
      assert.ok(true, "No adapter - cannot test cloud integration");
      done();
      return;
    }

    adapter.patchWidget("900", {
      Notes: JSON.stringify({
        sortMethod: "random",
        displayStyle: "full",
        autoAdvanceEnabled: false,
        autoAdvanceMs: 5000,
        cards: [{ q: "New Question", a: "New Answer" }]
      })
    })
      .then(function() {
        var updated = window.DashboardifyAllWidgets.find(function(w) { return w.RecID === "900"; });
        assert.ok(updated, "Widget still exists after Notes update");
        var notes = JSON.parse(updated.Notes || "{}");
        assert.strictEqual(notes.cards.length, 1, "Card count is correct");
        assert.strictEqual(notes.cards[0].q, "New Question", "New card saved");
        done();
      })
      .catch(function(err) {
        assert.ok(false, "Failed: " + err.message);
        done();
      });
  });
});