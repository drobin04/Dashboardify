/**
 * Unit Tests - Pure Functions
 * Tests for standalone functions that don't require DOM or external mocking.
 * These tests verify core logic behavior.
 */
QUnit.module("Unit - Escaping", function() {
  QUnit.test("escapeHtmlAttr handles double quotes", function(assert) {
    var result = window.escapeHtmlAttr('test"quote"');
    assert.ok(result.indexOf("&quot;") !== -1 || result.indexOf("&#34;") !== -1, "Quotes escaped");
  });

  QUnit.test("escapeHtmlAttr handles single quotes", function(assert) {
    var result = window.escapeHtmlAttr("test'quote'");
    assert.ok(result.indexOf("&#39;") !== -1 || result.indexOf("&apos;") !== -1, "Single quotes escaped");
  });

  QUnit.test("escapeHtmlAttr handles ampersand", function(assert) {
    var result = window.escapeHtmlAttr("test&value");
    assert.ok(result.indexOf("&amp;") !== -1, "Ampersand escaped");
  });

  QUnit.test("escapeHtmlAttr handles less than", function(assert) {
    var result = window.escapeHtmlAttr("test<tag>");
    assert.ok(result.indexOf("&lt;") !== -1, "Less than escaped");
  });

  QUnit.test("escapeHtmlAttr handles greater than", function(assert) {
    var result = window.escapeHtmlAttr("<test>value");
    assert.ok(result.indexOf("&gt;") !== -1, "Greater than escaped");
  });

  QUnit.test("escapeHtmlAttr handles empty string", function(assert) {
    var result = window.escapeHtmlAttr("");
    assert.strictEqual(result, "", "Empty string returns empty");
  });

  QUnit.test("escapeHtmlAttr handles no special chars", function(assert) {
    var result = window.escapeHtmlAttr("plaintext123");
    assert.strictEqual(result, "plaintext123", "No changes for plain text");
  });

  QUnit.test("escapeHtmlForTextarea handles empty", function(assert) {
    var result = window.escapeHtmlForTextarea("");
    assert.strictEqual(result, "", "Empty returns empty");
  });

  QUnit.test("escapeHtmlForTextarea handles entities", function(assert) {
    var input = "&lt;test&gt;";
    var result = window.escapeHtmlForTextarea(input);
    // When input contains &, it becomes &amp; first, so &lt; becomes &amp;lt;
    assert.ok(result.indexOf("&amp;") !== -1, "Original ampersand escaped to &amp;");
  });

  QUnit.test("dashboardifyEscapeHtmlText handles script tag", function(assert) {
    var result = window.dashboardifyEscapeHtmlText("<script>alert('xss')</script>");
    assert.ok(result.indexOf("<script>") === -1, "Script tag escaped");
  });

  QUnit.test("dashboardifyEscapeHtmlText preserves newlines", function(assert) {
    var result = window.dashboardifyEscapeHtmlText("line1\nline2");
    assert.ok(result.indexOf("\n") !== -1 || result.indexOf("&#10;") !== -1, "Newlines preserved");
  });
});

QUnit.module("Unit - Flash Cards", function() {
  QUnit.test("parse valid JSON model", function(assert) {
    var notes = JSON.stringify({
      sortMethod: "random",
      displayStyle: "full",
      autoAdvanceEnabled: false,
      autoAdvanceMs: 5000,
      cards: [{ q: "Question 1", a: "Answer 1" }, { q: "Q2", a: "A2" }]
    });

    var result = window.dashboardifyParseFlashCardsNotes(notes);

    assert.strictEqual(result.sortMethod, "random", "Sort method parsed");
    assert.strictEqual(result.displayStyle, "full", "Display style parsed");
    assert.strictEqual(result.autoAdvanceEnabled, false, "Auto advance parsed");
    assert.strictEqual(result.autoAdvanceMs, 5000, "Auto advance ms parsed");
    assert.strictEqual(result.cards.length, 2, "Cards array parsed");
    assert.strictEqual(result.cards[0].q, "Question 1", "First question parsed");
  });

  QUnit.test("parse handles missing notes", function(assert) {
    var result = window.dashboardifyParseFlashCardsNotes("");
    assert.ok(result, "Returns default model");
    assert.strictEqual(result.sortMethod, "random", "Default sort method");
  });

  QUnit.test("parse handles malformed JSON", function(assert) {
    var result = window.dashboardifyParseFlashCardsNotes("not valid json");
    assert.ok(result, "Returns default model on error");
    assert.ok(result.cards, "Has cards array");
  });

  QUnit.test("parse handles null input", function(assert) {
    var result = window.dashboardifyParseFlashCardsNotes(null);
    assert.ok(result, "Returns default model");
  });

  QUnit.test("parse handles partial model", function(assert) {
    var notes = JSON.stringify({ cards: [{ q: "Q1", a: "A1" }] });
    var result = window.dashboardifyParseFlashCardsNotes(notes);

    assert.strictEqual(result.cards.length, 1, "Partial model parsed");
    assert.strictEqual(result.sortMethod, "random", "Default sort method");
  });

  QUnit.test("serialize model to JSON", function(assert) {
    var model = {
      sortMethod: "sequential",
      displayStyle: "guess",
      autoAdvanceEnabled: true,
      autoAdvanceMs: 3000,
      cards: [{ q: "Test Q", a: "Test A" }]
    };

    var result = window.dashboardifySerializeFlashCardsModel(model);

    assert.ok(result, "Returns string");
    var parsed = JSON.parse(result);
    assert.strictEqual(parsed.sortMethod, "sequential", "Sort method serialized");
    assert.strictEqual(parsed.displayStyle, "guess", "Display style serialized");
  });

  QUnit.test("serialize handles missing fields", function(assert) {
    var result = window.dashboardifySerializeFlashCardsModel({ cards: [{ q: "Q", a: "A" }] });
    var parsed = JSON.parse(result);
    assert.strictEqual(parsed.sortMethod, "random", "Default sort method");
  });

  QUnit.test("serialize round-trip preservation", function(assert) {
    var original = {
      sortMethod: "random",
      displayStyle: "full",
      autoAdvanceEnabled: true,
      autoAdvanceMs: 7000,
      cards: [{ q: "Q1", a: "A1" }, { q: "Q2", a: "A2" }, { q: "Q3", a: "A3" }]
    };

    var serialized = window.dashboardifySerializeFlashCardsModel(original);
    var parsed = window.dashboardifyParseFlashCardsNotes(serialized);

    assert.strictEqual(parsed.sortMethod, original.sortMethod, "Sort method preserved");
    assert.strictEqual(parsed.displayStyle, original.displayStyle, "Display style preserved");
    assert.strictEqual(parsed.cards.length, original.cards.length, "Card count preserved");
  });

  QUnit.test("buildFlashCardOrder returns array", function(assert) {
    var result = window.dashboardifyBuildFlashCardOrder(5, {});
    assert.ok(Array.isArray(result), "Returns array");
    assert.strictEqual(result.length, 5, "Correct length");
  });

  QUnit.test("buildFlashCardOrder sequential produces sequential", function(assert) {
    var result = window.dashboardifyBuildFlashCardOrder(5, { sortMethod: "sequential" });
    assert.strictEqual(result[0], 0, "First index is 0");
    assert.strictEqual(result[1], 1, "Second index is 1");
    assert.strictEqual(result[2], 2, "Third index is 2");
  });

  QUnit.test("buildFlashCardOrder handles zero count", function(assert) {
    var result = window.dashboardifyBuildFlashCardOrder(0, {});
    assert.strictEqual(result.length, 0, "Empty array for zero count");
  });

  QUnit.test("buildFlashCardOrder handles single card", function(assert) {
    var result = window.dashboardifyBuildFlashCardOrder(1, {});
    assert.strictEqual(result.length, 1, "Single card returns array of 1");
  });
});

QUnit.module("Unit - Data Model", function() {
  QUnit.test("ensureDataModelShape adds version", function(assert) {
    var result = window.ensureDataModelShape({});
    assert.ok(result.version, "Version field added");
    assert.strictEqual(typeof result.version, "number", "Version is number");
  });

  QUnit.test("ensureDataModelShape has required arrays", function(assert) {
    var result = window.ensureDataModelShape({});
    assert.ok(Array.isArray(result.dashboards), "dashboards array exists");
    assert.ok(Array.isArray(result.widgets), "widgets array exists");
  });

  QUnit.test("ensureDataModelShape preserves existing data", function(assert) {
    var input = { version: 2, widgets: [{ RecID: "test-1" }], dashboards: [{ DashboardID: "dash-1" }] };
    var result = window.ensureDataModelShape(input);
    assert.strictEqual(result.version, 2, "Version preserved");
    assert.strictEqual(result.widgets.length, 1, "Widgets preserved");
  });

  QUnit.test("ensureDataModelShape adds meta if missing", function(assert) {
    var result = window.ensureDataModelShape({});
    assert.ok(result.meta, "Meta object added");
    assert.ok(result.meta.app, "Meta app field added");
  });

  QUnit.test("ensureDataModelShape adds preferences if missing", function(assert) {
    var result = window.ensureDataModelShape({});
    assert.ok(result.preferences, "Preferences object added");
    assert.ok("showSqlWidgetFields" in result.preferences, "showSqlWidgetFields field added");
  });

  QUnit.test("imageObjectFitFromNotes returns valid fit", function(assert) {
    var result = window.dashboardifyImageObjectFitFromNotes("cover");
    assert.strictEqual(result, "cover", "cover returned");
  });

  QUnit.test("imageObjectFitFromNotes handles invalid", function(assert) {
    var result = window.dashboardifyImageObjectFitFromNotes("invalid");
    assert.strictEqual(result, "contain", "defaults to contain");
  });

  QUnit.test("imageObjectFitFromNotes handles empty", function(assert) {
    var result = window.dashboardifyImageObjectFitFromNotes("");
    assert.strictEqual(result, "contain", "defaults to contain");
  });
});

QUnit.module("Unit - Caching", function() {
  var helper = window.DashboardifyTestHelper;
  var CACHE_KEY = helper.DASHBOARDIFY_CLOUD_DATA_CACHE_KEY;

  QUnit.test("writeCache writes to localStorage", function(assert) {
    var mock = helper.mockLocalStorage();
    var testData = { version: 2, widgets: [], dashboards: [] };

    window.writeCloudDataCacheToLocalStorage(testData);

    var stored = localStorage.getItem(CACHE_KEY);
    assert.ok(stored, "Data written to localStorage");
    assert.ok(JSON.parse(stored), "Data is valid JSON");
    mock.restore();
  });

  QUnit.test("readCache reads cached data", function(assert) {
    var mock = helper.mockLocalStorage();
    var testData = { version: 2, widgets: [{ RecID: "test-1" }], dashboards: [] };
    localStorage.setItem(CACHE_KEY, JSON.stringify(testData));

    var result = window.readCloudDataCacheFromLocalStorage();

    assert.ok(result, "Returns data");
    assert.strictEqual(result.version, 2, "Correct version");
    assert.strictEqual(result.widgets.length, 1, "Correct widget count");
    mock.restore();
  });

  QUnit.test("readCache handles missing cache", function(assert) {
    var mock = helper.mockLocalStorage();
    mock.clear();

    var result = window.readCloudDataCacheFromLocalStorage();

    assert.ok(result, "Returns something");
    mock.restore();
  });

  QUnit.test("readCache handles invalid JSON", function(assert) {
    var mock = helper.mockLocalStorage();
    localStorage.setItem(CACHE_KEY, "not json");

    var result = window.readCloudDataCacheFromLocalStorage();

    assert.ok(result, "Returns result even on error");
    mock.restore();
  });

  QUnit.test("clearCache removes data", function(assert) {
    var mock = helper.mockLocalStorage();
    localStorage.setItem(CACHE_KEY, JSON.stringify({ version: 2 }));

    window.clearCloudDataCacheFromLocalStorage();

    var stored = localStorage.getItem(CACHE_KEY);
    assert.strictEqual(stored, null, "Cache key removed");
    mock.restore();
  });

  QUnit.test("cache round-trip preserves all data", function(assert) {
    var mock = helper.mockLocalStorage();
    var original = {
      version: 2,
      meta: { app: "Dashboardify", appVersion: "1.0.0" },
      userCss: ".test { color: red; }",
      preferences: { showSqlWidgetFields: false },
      widgets: [{ RecID: "1", WidgetType: "Bookmark", Notes: "test notes" }],
      dashboards: [{ DashboardID: "dash-1", Name: "Main", CustomCSS: "body {}" }]
    };

    window.writeCloudDataCacheToLocalStorage(original);
    var result = window.readCloudDataCacheFromLocalStorage();

    assert.strictEqual(result.version, original.version, "Version preserved");
    assert.strictEqual(result.widgets.length, original.widgets.length, "Widgets count preserved");
    mock.restore();
  });
});

QUnit.module("Unit - Time", function() {
  QUnit.test("calculateHoursSinceEvent returns number", function(assert) {
    var eventDate = new Date();
    eventDate.setHours(eventDate.getHours() - 5);

    var result = window.calculateHoursSinceEvent(eventDate.toISOString());

    assert.ok(typeof result === "number" || !isNaN(parseFloat(result)), "Returns numeric value");
  });

  QUnit.test("calculateHoursSinceEvent handles future date", function(assert) {
    var futureDate = new Date();
    futureDate.setHours(futureDate.getHours() + 10);

    var result = window.calculateHoursSinceEvent(futureDate.toISOString());

    assert.ok(!isNaN(result), "Returns valid number");
  });

  QUnit.test("calculateHoursSinceEvent handles empty", function(assert) {
    var result = window.calculateHoursSinceEvent("");
    assert.strictEqual(result, 0, "Returns 0 for empty");
  });

  QUnit.test("calculateHoursSinceEvent handles null", function(assert) {
    var result = window.calculateHoursSinceEvent(null);
    assert.strictEqual(result, 0, "Returns 0 for null");
  });
});

QUnit.module("Unit - Form Collection", function() {
  var helper = window.DashboardifyTestHelper;

  QUnit.test("collectWidgetPayload collects Bookmark fields", function(assert) {
    var created = helper.setupFormFields({
      ddlWidgetType2: "Bookmark",
      txtWidgetDisplayText: "My Link",
      txtWidgetURL: "https://example.com"
    });

    var result = window.collectWidgetPayloadFromForm();

    assert.strictEqual(result.WidgetType, "Bookmark", "Widget type collected");
    assert.strictEqual(result.BookmarkDisplayText, "My Link", "Display text collected");
    assert.strictEqual(result.WidgetURL, "https://example.com", "URL collected");
    helper.cleanupFormFields(created);
  });

  QUnit.test("collectWidgetPayload collects IFrame fields", function(assert) {
    var created = helper.setupFormFields({
      ddlWidgetType2: "IFrame",
      txtWidgetDisplayText: "Embedded",
      txtWidgetURL: "https://example.com/embed"
    });

    var result = window.collectWidgetPayloadFromForm();

    assert.strictEqual(result.WidgetType, "IFrame", "Widget type correct");
    assert.strictEqual(result.BookmarkDisplayText, "Embedded", "Title collected");
    helper.cleanupFormFields(created);
  });

  QUnit.test("collectWidgetPayload collects Image object-fit", function(assert) {
    var created = helper.setupFormFields({
      ddlWidgetType2: "Image",
      txtWidgetURL: "https://example.com/img.png",
      ddlImageObjectFit: "cover"
    });

    var result = window.collectWidgetPayloadFromForm();

    assert.strictEqual(result.WidgetType, "Image", "Widget type correct");
    assert.strictEqual(result.Notes, "cover", "object-fit in Notes");
    helper.cleanupFormFields(created);
  });

  QUnit.test("collectWidgetPayload collects Clock timezone", function(assert) {
    var created = helper.setupFormFields({
      ddlWidgetType2: "Clock",
      txtWidgetDisplayText: "My Clock",
      ddlClockTimezone: "Europe/London"
    });

    var result = window.collectWidgetPayloadFromForm();

    assert.strictEqual(result.WidgetType, "Clock", "Widget type correct");
    assert.strictEqual(result.Notes, "Europe/London", "Timezone in Notes");
    helper.cleanupFormFields(created);
  });

  QUnit.test("collectWidgetPayload collects position fields", function(assert) {
    var created = helper.setupFormFields({
      ddlWidgetType2: "Bookmark",
      txtWidgetDisplayText: "Test",
      txtpositionx2: "100",
      txtpositiony2: "200",
      txtsizeX2: "300",
      txtsizeY2: "400"
    });

    var result = window.collectWidgetPayloadFromForm();

    assert.strictEqual(result.PositionX, "100", "PositionX collected");
    assert.strictEqual(result.PositionY, "200", "PositionY collected");
    assert.strictEqual(result.SizeX, "300", "SizeX collected");
    assert.strictEqual(result.SizeY, "400", "SizeY collected");
    helper.cleanupFormFields(created);
  });

  QUnit.test("collectWidgetPayload handles missing optional fields", function(assert) {
    var created = helper.setupFormFields({
      ddlWidgetType2: "Bookmark"
    });

    var result = window.collectWidgetPayloadFromForm();

    assert.ok(result, "Returns result");
    assert.strictEqual(result.WidgetType, "Bookmark", "Has widget type");
    helper.cleanupFormFields(created);
  });
});