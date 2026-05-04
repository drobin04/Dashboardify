/**
 * Form Collection Tests
 * Tests for collectWidgetPayloadFromForm function
 */
QUnit.module("Form Collection", function() {
  var helper = window.DashboardifyTestHelper;

  QUnit.test("collectWidgetPayload collects Bookmark fields", function(assert) {
    var created = helper.setupFormFields({
      ddlWidgetType2: "Bookmark",
      txtWidgetDisplayText: "My Link",
      txtWidgetURL: "https://example.com"
    });

    var result = collectWidgetPayloadFromForm();

    assert.strictEqual(result.WidgetType, "Bookmark", "Widget type collected");
    assert.strictEqual(result.BookmarkDisplayText, "My Link", "Display text collected");
    assert.strictEqual(result.WidgetURL, "https://example.com", "URL collected");
    helper.cleanupFormFields(created);
  });

  QUnit.test("collectWidgetPayload collects Notes content", function(assert) {
    var created = helper.setupFormFields({
      ddlWidgetType2: "Notes",
      txtNotes: "# Test Notes\n\nSome content"
    });

    var result = collectWidgetPayloadFromForm();

    assert.strictEqual(result.WidgetType, "Notes", "Widget type correct");
    assert.contains(assert, result.Notes, "Test Notes", "Notes content collected");
    helper.cleanupFormFields(created);
  });

  QUnit.test("collectWidgetPayload collects Image object-fit", function(assert) {
    var created = helper.setupFormFields({
      ddlWidgetType2: "Image",
      txtWidgetURL: "https://example.com/img.png",
      ddlImageObjectFit: "cover"
    });

    var result = collectWidgetPayloadFromForm();

    assert.strictEqual(result.WidgetType, "Image", "Widget type correct");
    assert.strictEqual(result.WidgetURL, "https://example.com/img.png", "URL collected");
    assert.strictEqual(result.Notes, "cover", "object-fit in Notes");
    helper.cleanupFormFields(created);
  });

  QUnit.test("collectWidgetPayload collects Clock timezone", function(assert) {
    var created = helper.setupFormFields({
      ddlWidgetType2: "Clock",
      txtWidgetDisplayText: "My Clock",
      ddlClockTimezone: "Europe/London"
    });

    var result = collectWidgetPayloadFromForm();

    assert.strictEqual(result.WidgetType, "Clock", "Widget type correct");
    assert.strictEqual(result.BookmarkDisplayText, "My Clock", "Display text collected");
    assert.strictEqual(result.Notes, "Europe/London", "Timezone in Notes");
    helper.cleanupFormFields(created);
  });

  QUnit.test("collectWidgetPayload collects IFrame fields", function(assert) {
    var created = helper.setupFormFields({
      ddlWidgetType2: "IFrame",
      txtWidgetDisplayText: "Embedded",
      txtWidgetURL: "https://example.com/embed"
    });

    var result = collectWidgetPayloadFromForm();

    assert.strictEqual(result.WidgetType, "IFrame", "Widget type correct");
    assert.strictEqual(result.BookmarkDisplayText, "Embedded", "Title collected");
    assert.strictEqual(result.WidgetURL, "https://example.com/embed", "URL collected");
    helper.cleanupFormFields(created);
  });

  QUnit.test("collectWidgetPayload collects Countdown date", function(assert) {
    var created = helper.setupFormFields({
      ddlWidgetType2: "Countdown",
      txtWidgetDisplayText: "Event",
      datepicker: "2025-06-15"
    });

    var result = collectWidgetPayloadFromForm();

    assert.strictEqual(result.WidgetType, "Countdown", "Widget type correct");
    assert.contains(assert, result.Notes, "2025", "Date in Notes");
    helper.cleanupFormFields(created);
  });

  QUnit.test("collectWidgetPayload uses Notes for CountUp_Hours", function(assert) {
    var created = helper.setupFormFields({
      ddlWidgetType2: "CountUp_Hours",
      txtWidgetDisplayText: "Since Event",
      datepicker: "2024-01-01T10:00"
    });

    var result = collectWidgetPayloadFromForm();

    assert.strictEqual(result.WidgetType, "CountUp_Hours", "Widget type correct");
    helper.cleanupFormFields(created);
  });

  QUnit.test("collectWidgetPayload handles missing optional fields", function(assert) {
    var created = helper.setupFormFields({
      ddlWidgetType2: "Bookmark",
      txtWidgetDisplayText: "",
      txtWidgetURL: ""
    });

    var result = collectWidgetPayloadFromForm();

    assert.ok(result, "Returns result");
    assert.ok(result.WidgetType, "Has widget type");
    helper.cleanupFormFields(created);
  });

  QUnit.test("collectWidgetPayload normalizes empty position for Bookmark", function(assert) {
    var created = helper.setupFormFields({
      ddlWidgetType2: "Bookmark",
      txtWidgetDisplayText: "Test",
      txtWidgetURL: "https://example.com",
      txtpositionx2: "",
      txtpositiony2: "",
      txtsizeX2: "",
      txtsizeY2: ""
    });

    var result = collectWidgetPayloadFromForm();

    assert.strictEqual(result.PositionX, "", "Empty position normalized");
    assert.strictEqual(result.PositionY, "", "Empty position normalized");
    helper.cleanupFormFields(created);
  });

  QUnit.test("collectWidgetPayload preserves numeric position", function(assert) {
    var created = helper.setupFormFields({
      ddlWidgetType2: "Bookmark",
      txtWidgetDisplayText: "Test",
      txtWidgetURL: "https://example.com",
      txtpositionx2: "100",
      txtpositiony2: "200",
      txtsizeX2: "300",
      txtsizeY2: "400"
    });

    var result = collectWidgetPayloadFromForm();

    assert.strictEqual(result.PositionX, "100", "PositionX preserved");
    assert.strictEqual(result.PositionY, "200", "PositionY preserved");
    assert.strictEqual(result.SizeX, "300", "SizeX preserved");
    assert.strictEqual(result.SizeY, "400", "SizeY preserved");
    helper.cleanupFormFields(created);
  });

  QUnit.test("collectWidgetPayload collects CSS class", function(assert) {
    var created = helper.setupFormFields({
      ddlWidgetType2: "Bookmark",
      txtWidgetDisplayText: "Test",
      txtCSSClass: "custom-class"
    });

    var result = collectWidgetPayloadFromForm();

    assert.strictEqual(result.WidgetCSSClass, "custom-class", "CSS class collected");
    helper.cleanupFormFields(created);
  });

  QUnit.test("collectWidgetPayload default to Bookmark type", function(assert) {
    var created = helper.setupFormFields({
      ddlWidgetType2: "",
      txtWidgetDisplayText: "No Type"
    });

    var result = collectWidgetPayloadFromForm();

    assert.strictEqual(result.WidgetType, "", "Empty type returned");
    helper.cleanupFormFields(created);
  });
});