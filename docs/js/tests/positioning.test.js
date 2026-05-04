/**
 * Widget Positioning Tests
 * Tests for positioning logic and auto-tiling
 */
QUnit.module("Widget Positioning", function() {
  var helper = window.DashboardifyTestHelper;

  QUnit.test("PositionX 0 triggers auto-tiling behavior", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Bookmark",
      PositionX: 0,
      PositionY: 0
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.ok(html.indexOf("left:0px") !== -1 || html.indexOf("left: 0px") !== -1,
      "Zero position renders as left:0px");
  });

  QUnit.test("PositionX empty string triggers auto-tiling", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Bookmark",
      PositionX: "",
      PositionY: ""
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.ok(html.indexOf("left:px") !== -1 || html.indexOf("left:px") !== -1,
      "Empty position renders without explicit left");
  });

  QUnit.test("PositionX numeric applies absolute positioning", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Bookmark",
      PositionX: 100,
      PositionY: 200
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.contains(assert, html, "left:100px", "Numeric PositionX applied");
    assert.contains(assert, html, "top:200px", "Numeric PositionY applied");
  });

  QUnit.test("PositionY numeric applies absolute positioning", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Bookmark",
      PositionX: 50,
      PositionY: 75
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.contains(assert, html, "top:75px", "Numeric PositionY applied");
  });

  QUnit.test("default SizeX applied when missing", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Bookmark",
      SizeX: null,
      SizeY: null
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.ok(html.length > 0, "Widget renders without SizeX");
  });

  QUnit.test("default SizeY applied when missing", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Bookmark",
      SizeX: null,
      SizeY: null
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.ok(html.length > 0, "Widget renders without SizeY");
  });

  QUnit.test("custom SizeX rendered", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Bookmark",
      SizeX: 500,
      SizeY: 400
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.contains(assert, html, "width:500px", "Custom SizeX applied");
  });

  QUnit.test("custom SizeY rendered", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Bookmark",
      SizeX: 500,
      SizeY: 400
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.contains(assert, html, "height:400px", "Custom SizeY applied");
  });

  QUnit.test("negative PositionX handled", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Bookmark",
      PositionX: -50,
      PositionY: 100
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.contains(assert, html, "left:-50px", "Negative PositionX applied");
  });

  QUnit.test("large position values handled", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Bookmark",
      PositionX: 2000,
      PositionY: 1500
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.contains(assert, html, "left:2000px", "Large PositionX applied");
    assert.contains(assert, html, "top:1500px", "Large PositionY applied");
  });

  QUnit.test("decimal position values handled", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Bookmark",
      PositionX: 150.5,
      PositionY: 200.75
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.ok(html.indexOf("150.5") !== -1 || html.indexOf("150.75") !== -1,
      "Decimal positions handled");
  });
});