/**
 * Test helper utilities for Dashboardify unit tests
 * Provides DOM simulation and common test utilities
 */

window.DashboardifyTestHelper = {
  /**
   * Test widget data factory - creates widget objects with sensible defaults
   */
  createWidget: function(overrides) {
    return {
      RecID: "test-" + Date.now() + "-" + Math.random().toString(36).substr(2, 9),
      DashboardRecID: "test-dash-1",
      WidgetType: "Bookmark",
      BookmarkDisplayText: "Test Widget",
      WidgetURL: "https://example.com",
      WidgetCSSClass: "",
      Notes: "",
      PositionX: 100,
      PositionY: 100,
      SizeX: 300,
      SizeY: 200,
      ...overrides
    };
  },

  /**
   * Create widget list for dashboard testing
   */
  createWidgetList: function(count, typeOverrides) {
    var widgets = [];
    for (var i = 0; i < count; i++) {
      widgets.push(this.createWidget(typeOverrides));
    }
    return widgets;
  },

  /**
   * Dashboard data factory
   */
  createDashboard: function(overrides) {
    return {
      DashboardID: "dash-" + Date.now(),
      Name: "Test Dashboard",
      CustomCSS: "",
      BackgroundPhotoURL: "",
      Embeddable: false,
      ...overrides
    };
  },

  /**
   * App data structure factory
   */
  createAppData: function(overrides) {
    return {
      version: 2,
      meta: {
        app: "Dashboardify",
        appVersion: "1.0.0",
        lastUpdatedUtc: new Date().toISOString(),
        importedFrom: null
      },
      userCss: "",
      preferences: {
        siteBaseUrl: "",
        showSqlWidgetFields: false,
        lastSelectedDashboardId: ""
      },
      customWidgetProviders: [],
      widgetStyles: [],
      storedWidgets: [],
      dashboards: [],
      widgets: [],
      ...overrides
    };
  },

  /**
   * Mock document.getElementById for testing without DOM
   */
  mockDocument: function() {
    var elements = {};
    var container = document.createElement("div");
    container.id = "widgetcontainer";
    document.body.appendChild(container);

    return {
      container: container,
      elements: elements,

      createElement: function(id, tagName) {
        var el = document.createElement(tagName || "div");
        el.id = id;
        elements[id] = el;
        document.body.appendChild(el);
        return el;
      },

      getElement: function(id) {
        return elements[id] || document.getElementById(id);
      },

      reset: function() {
        container.innerHTML = "";
        elements = {};
      },

      cleanup: function() {
        container.remove();
        elements = {};
      }
    };
  },

  /**
   * Simulate form input values
   */
  setupFormFields: function(fields) {
    var created = [];
    for (var id in fields) {
      var el = document.getElementById(id);
      if (!el) {
        el = document.createElement("input");
        el.id = id;
        document.body.appendChild(el);
        created.push(el);
      }
      el.value = fields[id];
    }
    return created;
  },

  /**
   * Clean up form fields created for test
   */
  cleanupFormFields: function(elements) {
    elements.forEach(function(el) {
      if (el && el.parentNode) {
        el.parentNode.removeChild(el);
      }
    });
  },

  /**
   * Mock localStorage for testing
   */
  mockLocalStorage: function() {
    var store = {};
    var original = window.localStorage;

    window.localStorage = {
      getItem: function(key) {
        return store[key] || null;
      },
      setItem: function(key, value) {
        store[key] = value;
      },
      removeItem: function(key) {
        delete store[key];
      },
      clear: function() {
        store = {};
      },
      get length() {
        return Object.keys(store).length;
      },
      key: function(index) {
        return Object.keys(store)[index] || null;
      }
    };

    return {
      store: store,
      restore: function() {
        window.localStorage = original;
      },
      clear: function() {
        store = {};
      }
    };
  },

  /**
   * Generate unique ID for test isolation
   */
  generateId: function(prefix) {
    return (prefix || "test") + "-" + Date.now() + "-" + Math.random().toString(36).substr(2, 9);
  },

  /**
   * Wait for async operation (for future async testing)
   */
  wait: function(ms) {
    return new Promise(function(resolve) {
      setTimeout(resolve, ms);
    });
  },

  /**
   * Assert helper - deep equality for objects
   */
  assertDeepEqual: function(assert, actual, expected, message) {
    var actualJson = JSON.stringify(actual);
    var expectedJson = JSON.stringify(expected);
    assert.strictEqual(actualJson, expectedJson, message);
  },

  /**
   * Assert helper - contains substring
   */
  assertContains: function(assert, str, substring, message) {
    assert.ok(str.indexOf(substring) !== -1, message);
  },

  /**
   * Clock timezone test data
   */
  timezones: [
    "America/New_York",
    "America/Chicago",
    "America/Denver",
    "America/Los_Angeles",
    "America/Anchorage",
    "Pacific/Honolulu",
    "Europe/London",
    "Europe/Paris",
    "Europe/Berlin",
    "Europe/Moscow",
    "Asia/Dubai",
    "Asia/Kolkata",
    "Asia/Singapore",
    "Asia/Tokyo",
    "Asia/Shanghai",
    "Australia/Sydney",
    "Pacific/Auckland"
  ],

  /**
   * Widget type test data
   */
  widgetTypes: [
    "Bookmark",
    "IFrame",
    "Collapseable IFrame",
    "Notes",
    "HTMLEmbed",
    "Image",
    "Flash Cards",
    "Countdown",
    "Clock",
    "CountUp_Hours",
    "CountUp_Days"
  ]
};

window.DashboardifyTestHelper.DASHBOARDIFY_CLOUD_DATA_CACHE_KEY = "dashboardify_cloud_data_snapshot";
window.DashboardifyTestHelper.GOOGLE_OAUTH_SESSION_KEY = "google_oauth_session";

window.DashboardifyTestLoader = {
  createWidget: function(overrides) {
    return window.DashboardifyTestHelper.createWidget(overrides);
  },
  createDashboard: function(overrides) {
    return window.DashboardifyTestHelper.createDashboard(overrides);
  },
  createAppData: function(overrides) {
    return window.DashboardifyTestHelper.createAppData(overrides);
  }
};