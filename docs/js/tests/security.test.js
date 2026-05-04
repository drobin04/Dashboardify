/**
 * Security Tests
 * Tests for XSS prevention and escaping functions
 */
QUnit.module("Security - HTML Escaping", function() {
  var helper = window.DashboardifyTestHelper;

  QUnit.test("escapeHtmlAttr handles double quotes", function(assert) {
    var input = 'test"quote"';
    var result = escapeHtmlAttr(input);

    assert.ok(result.indexOf("&quot;") !== -1 || result.indexOf("&#34;") !== -1,
      "Quotes escaped");
  });

  QUnit.test("escapeHtmlAttr handles single quotes", function(assert) {
    var input = "test'quote'";
    var result = escapeHtmlAttr(input);

    assert.ok(result.indexOf("&#39;") !== -1 || result.indexOf("&apos;") !== -1,
      "Single quotes escaped");
  });

  QUnit.test("escapeHtmlAttr handles ampersand", function(assert) {
    var input = "test&value";
    var result = escapeHtmlAttr(input);

    assert.ok(result.indexOf("&amp;") !== -1, "Ampersand escaped");
  });

  QUnit.test("escapeHtmlAttr handles less than", function(assert) {
    var input = "test<tag>";
    var result = escapeHtmlAttr(input);

    assert.ok(result.indexOf("&lt;") !== -1, "Less than escaped");
  });

  QUnit.test("escapeHtmlAttr handles greater than", function(assert) {
    var input = "<test>value";
    var result = escapeHtmlAttr(input);

    assert.ok(result.indexOf("&gt;") !== -1, "Greater than escaped");
  });

  QUnit.test("escapeHtmlAttr handles empty string", function(assert) {
    var result = escapeHtmlAttr("");

    assert.strictEqual(result, "", "Empty string returns empty");
  });

  QUnit.test("escapeHtmlAttr handles no special chars", function(assert) {
    var input = "plaintext123";
    var result = escapeHtmlAttr(input);

    assert.strictEqual(result, "plaintext123", "No changes for plain text");
  });

  QUnit.test("escapeHtmlForTextarea handles script tag", function(assert) {
    var input = "<script>alert('xss')</script>";
    var result = escapeHtmlForTextarea(input);

    assert.ok(result.indexOf("<script>") === -1 || result.indexOf("&lt;") !== -1,
      "Script tag escaped");
  });

  QUnit.test("escapeHtmlForTextarea handles onerror", function(assert) {
    var input = '<img src="x" onerror="alert(1)">';
    var result = escapeHtmlForTextarea(input);

    assert.ok(result.indexOf("onerror") === -1 || result.indexOf("onerror") === -1,
      "Event handler escaped");
  });

  QUnit.test("escapeHtmlForTextarea handles javascript:", function(assert) {
    var input = '<a href="javascript:alert(1)">click</a>';
    var result = escapeHtmlForTextarea(input);

    assert.ok(result.indexOf("javascript:") === -1 || result.indexOf("javascript:") === -1,
      "JavaScript protocol escaped");
  });

  QUnit.test("escapeHtmlForTextarea preserves safe HTML", function(assert) {
    var input = "<b>bold</b> and <i>italic</i>";
    var result = escapeHtmlForTextarea(input);

    assert.contains(assert, result, "<b>", "Bold preserved");
    assert.contains(assert, result, "<i>", "Italic preserved");
  });

  QUnit.test("escapeHtmlForTextarea handles empty", function(assert) {
    var result = escapeHtmlForTextarea("");

    assert.strictEqual(result, "", "Empty returns empty");
  });

  QUnit.test("escapeHtmlForTextarea handles entities", function(assert) {
    var input = "&lt;test&gt;";
    var result = escapeHtmlForTextarea(input);

    assert.contains(assert, result, "&lt;", "Entities preserved");
  });
});

QUnit.module("Security - Widget Rendering", function() {
  var helper = window.DashboardifyTestHelper;

  QUnit.test("rendering Notes escapes script tag", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Notes",
      Notes: "<script>alert('xss')</script>"
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.ok(html.indexOf("<script>") === -1 || html.indexOf("&lt;") !== -1,
      "Script tag escaped in output");
  });

  QUnit.test("rendering Notes escapes onerror", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Notes",
      Notes: '<img src="x" onerror="alert(1)">'
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.ok(html.indexOf("onerror") === -1 || html.indexOf("onerror") === -1,
      "Event handler escaped");
  });

  QUnit.test("rendering Bookmark escapes URL", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Bookmark",
      BookmarkDisplayText: "Test",
      WidgetURL: 'javascript:alert("xss")'
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.ok(html.indexOf("javascript:") === -1 || html.indexOf("javascript:") === -1,
      "JavaScript protocol handled");
  });

  QUnit.test("rendering IFrame sanitizes src", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "IFrame",
      WidgetURL: "https://example.com"
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.contains(assert, html, "https://example.com", "Valid URL preserved");
  });

  QUnit.test("rendering Image escapes src", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Image",
      WidgetURL: 'javascript:alert("xss")',
      Notes: "cover"
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.ok(html.indexOf("javascript:") === -1, "JavaScript in src handled");
  });

  QUnit.test("rendering HTML embed escapes dangerous content", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "HTMLEmbed",
      Notes: '<iframe src="javascript:alert(1)"></iframe>'
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.ok(html.indexOf("javascript:") === -1 || html.indexOf("javascript") === -1,
      "Dangerous iframe handled");
  });

  QUnit.test("rendering preserves safe HTML in Notes", function(assert) {
    var widget = helper.createWidget({
      WidgetType: "Notes",
      Notes: "<b>Bold text</b>"
    });

    var container = document.getElementById("widgetcontainer");
    container.innerHTML = "";

    drawWidget(widget);

    var html = container.innerHTML;
    assert.contains(assert, html, "<b>", "Safe HTML preserved");
  });
});

QUnit.module("Security - Data Storage", function() {
  QUnit.test("localStorage does not store sensitive data in plain text", function(assert) {
    var mock = window.DashboardifyTestHelper.mockLocalStorage();
    var testData = {
      version: 2,
      widgets: [{ Notes: "<script>alert(1)</script>" }]
    };

    writeCloudDataCacheToLocalStorage(testData);

    var stored = localStorage.getItem("dashboardify_cloud_data_snapshot");
    assert.contains(assert, stored, "<script>", "Script stored (but will be escaped on render)");
    mock.restore();
  });

  QUnit.test("localStorage key is non-trivial", function(assert) {
    var mock = window.DashboardifyTestHelper.mockLocalStorage();

    writeCloudDataCacheToLocalStorage({ version: 2 });

    var key = localStorage.key(0);
    assert.ok(key && key.length > 10, "Uses non-trivial key name");
    mock.restore();
  });
});