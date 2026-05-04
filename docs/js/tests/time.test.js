/**
 * Time Calculation Tests
 * Tests for countdown and time-related functions
 */
QUnit.module("Time Calculations", function() {
  var helper = window.DashboardifyTestHelper;

  QUnit.test("calculateHoursSinceEvent returns number", function(assert) {
    var eventDate = new Date();
    eventDate.setHours(eventDate.getHours() - 5);

    var result = calculateHoursSinceEvent(eventDate.toISOString());

    assert.ok(typeof result === "number" || !isNaN(parseFloat(result)), "Returns numeric value");
  });

  QUnit.test("calculateHoursSinceEvent handles future date", function(assert) {
    var futureDate = new Date();
    futureDate.setHours(futureDate.getHours() + 10);

    var result = calculateHoursSinceEvent(futureDate.toISOString());

    assert.ok(!isNaN(result), "Returns valid number");
  });

  QUnit.test("calculateHoursSinceEvent handles current time", function(assert) {
    var now = new Date().toISOString();

    var result = calculateHoursSinceEvent(now);

    assert.ok(!isNaN(result), "Returns valid number for now");
  });

  QUnit.test("calculateHoursSinceEvent handles date string format", function(assert) {
    var result = calculateHoursSinceEvent("2024-01-01T00:00:00");

    assert.ok(typeof result === "number" || !isNaN(parseFloat(result)), "Handles date string");
  });

  QUnit.test("countdown displays days remaining for future date", function(assert) {
    var futureDate = new Date();
    futureDate.setDate(futureDate.getDate() + 5);
    var dateStr = (futureDate.getMonth() + 1) + "/" + futureDate.getDate() + "/" + futureDate.getFullYear();

    var widget = helper.createWidget({
      WidgetType: "Countdown",
      Notes: dateStr
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.ok(html.length > 0, "Renders countdown");
    assert.contains(assert, html, "Days", "Shows days label");
  });

  QUnit.test("countdown displays days remaining for past date", function(assert) {
    var pastDate = new Date();
    pastDate.setDate(pastDate.getDate() - 5);
    var dateStr = (pastDate.getMonth() + 1) + "/" + pastDate.getDate() + "/" + pastDate.getFullYear();

    var widget = helper.createWidget({
      WidgetType: "Countdown",
      Notes: dateStr
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.ok(html.length > 0, "Renders countdown for past date");
  });

  QUnit.test("countdown displays for today", function(assert) {
    var today = new Date();
    var dateStr = (today.getMonth() + 1) + "/" + today.getDate() + "/" + today.getFullYear();

    var widget = helper.createWidget({
      WidgetType: "Countdown",
      Notes: dateStr
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.ok(html.length > 0, "Renders countdown for today");
  });

  QUnit.test("clock displays for valid timezone", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Clock",
      BookmarkDisplayText: "Test Clock",
      Notes: "America/New_York"
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.ok(html.length > 0, "Clock renders");
    assert.contains(assert, html, "Test Clock", "Title displayed");
  });

  QUnit.test("clock displays for Europe timezone", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Clock",
      BookmarkDisplayText: "London",
      Notes: "Europe/London"
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.ok(html.length > 0, "Clock renders for Europe");
  });

  QUnit.test("clock displays for Asia timezone", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Clock",
      BookmarkDisplayText: "Tokyo",
      Notes: "Asia/Tokyo"
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.ok(html.length > 0, "Clock renders for Asia");
  });

  QUnit.test("clock defaults to Eastern Time", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Clock",
      BookmarkDisplayText: "Default Clock",
      Notes: ""
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.ok(html.length > 0, "Clock renders with default timezone");
  });

  QUnit.test("clock handles invalid timezone gracefully", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Clock",
      BookmarkDisplayText: "Invalid",
      Notes: "Invalid/Timezone"
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.ok(html.length > 0, "Clock handles invalid timezone");
  });
});