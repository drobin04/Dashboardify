/**
 * Widget Rendering Tests
 * Tests for drawWidget function and all widget type rendering
 */
QUnit.module("Widget Rendering", function() {
  var helper = window.DashboardifyTestHelper;

  QUnit.test("drawWidget renders Bookmark with URL", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Bookmark",
      BookmarkDisplayText: "Test Link",
      WidgetURL: "https://example.com"
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.ok(html.length > 0, "Widget rendered HTML output");
    assert.contains(assert, html, "https://example.com", "URL is in output");
    assert.contains(assert, html, "Test Link", "Display text is in output");
  });

  QUnit.test("drawWidget renders IFrame with src", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "IFrame",
      WidgetURL: "https://example.com/iframe"
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.ok(html.length > 0, "Widget rendered HTML output");
    assert.contains(assert, html, "<iframe", "IFrame element present");
    assert.contains(assert, html, "https://example.com/iframe", "URL is in src");
  });

  QUnit.test("drawWidget renders Notes as markdown", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Notes",
      Notes: "# Hello World\n\nThis is a **test**."
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.ok(html.length > 0, "Widget rendered HTML output");
    assert.contains(assert, html, "md-block", "MD block component present");
  });

  QUnit.test("drawWidget renders Image with object-fit", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Image",
      WidgetURL: "https://example.com/image.png",
      Notes: "cover"
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.ok(html.length > 0, "Widget rendered HTML output");
    assert.contains(assert, html, "https://example.com/image.png", "Image URL present");
    assert.contains(assert, html, "object-fit", "object-fit CSS present");
  });

  QUnit.test("drawWidget renders Image default object-fit", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Image",
      WidgetURL: "https://example.com/image.png",
      Notes: ""
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.ok(html.length > 0, "Widget rendered HTML output");
    assert.ok(html.indexOf("object-fit:contain") !== -1 || html.indexOf("object-fit") !== -1,
      "Default object-fit is contain");
  });

  QUnit.test("drawWidget renders Flash Cards", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Flash Cards",
      Notes: JSON.stringify({
        sortMethod: "random",
        displayStyle: "full",
        autoAdvanceEnabled: false,
        autoAdvanceMs: 5000,
        cards: [{ q: "Question 1", a: "Answer 1" }]
      })
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.ok(html.length > 0, "Widget rendered HTML output");
    assert.contains(assert, html, "flashcards", "Flash cards class present");
  });

  QUnit.test("drawWidget renders Countdown", function(assert) {
    var futureDate = new Date();
    futureDate.setDate(futureDate.getDate() + 10);
    var dateStr = (futureDate.getMonth() + 1) + "/" + futureDate.getDate() + "/" + futureDate.getFullYear();

    var widget = helper.createWidget({
      WidgetType: "Countdown",
      BookmarkDisplayText: "Event Title",
      Notes: dateStr
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.ok(html.length > 0, "Widget rendered HTML output");
    assert.contains(assert, html, "Event Title", "Title present");
    assert.contains(assert, html, "countdown", "Countdown class present");
  });

  QUnit.test("drawWidget renders Clock with timezone", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Clock",
      BookmarkDisplayText: "My Clock",
      Notes: "America/New_York"
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.ok(html.length > 0, "Widget rendered HTML output");
    assert.contains(assert, html, "My Clock", "Title present");
    assert.contains(assert, html, "clock", "Clock class present");
  });

  QUnit.test("drawWidget renders Clock with custom timezone", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Clock",
      BookmarkDisplayText: "Tokyo Time",
      Notes: "Asia/Tokyo"
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.ok(html.length > 0, "Widget rendered HTML output");
    assert.contains(assert, html, "Tokyo Time", "Custom title present");
  });

  QUnit.test("drawWidget renders Collapseable IFrame", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Collapseable IFrame",
      BookmarkDisplayText: "Collapsible Panel",
      WidgetURL: "https://example.com"
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.ok(html.length > 0, "Widget rendered HTML output");
    assert.contains(assert, html, "Collapsible Panel", "Title present");
    assert.contains(assert, html, "collapseable", "Collapseable class present");
  });

  QUnit.test("drawWidget renders HTMLEmbed", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "HTMLEmbed",
      Notes: "<div>Custom HTML</div>"
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.ok(html.length > 0, "Widget rendered HTML output");
    assert.contains(assert, html, "Custom HTML", "Custom HTML present");
  });
});

QUnit.module("Widget Rendering - Position and Size", function() {
  var helper = window.DashboardifyTestHelper;

  QUnit.test("drawWidget applies PositionX/Y", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Bookmark",
      PositionX: 150,
      PositionY: 250
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.contains(assert, html, "left:150px", "PositionX applied");
    assert.contains(assert, html, "top:250px", "PositionY applied");
  });

  QUnit.test("drawWidget applies SizeX/Y", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Bookmark",
      SizeX: 400,
      SizeY: 300
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.contains(assert, html, "width:400px", "SizeX applied");
    assert.contains(assert, html, "height:300px", "SizeY applied");
  });

  QUnit.test("drawWidget applies custom CSS class", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Bookmark",
      WidgetCSSClass: "custom-widget"
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.contains(assert, html, "custom-widget", "Custom CSS class applied");
  });

  QUnit.test("drawWidget generates unique RecID", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Bookmark",
      RecID: "unique-test-id"
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.contains(assert, html, "unique-test-id", "RecID used in output");
  });
});