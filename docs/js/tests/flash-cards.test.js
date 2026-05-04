/**
 * Flash Cards Tests
 * Tests for flash card parsing, serialization, and ordering
 */
QUnit.module("Flash Cards", function() {
  var helper = window.DashboardifyTestHelper;

  QUnit.test("parse valid JSON model", function(assert) {
    var notes = JSON.stringify({
      sortMethod: "random",
      displayStyle: "full",
      autoAdvanceEnabled: false,
      autoAdvanceMs: 5000,
      cards: [{ q: "Question 1", a: "Answer 1" }, { q: "Q2", a: "A2" }]
    });

    var result = dashboardifyParseFlashCardsNotes(notes);

    assert.ok(result, "Parsed result returned");
    assert.strictEqual(result.sortMethod, "random", "Sort method parsed");
    assert.strictEqual(result.displayStyle, "full", "Display style parsed");
    assert.strictEqual(result.autoAdvanceEnabled, false, "Auto advance parsed");
    assert.strictEqual(result.autoAdvanceMs, 5000, "Auto advance ms parsed");
    assert.strictEqual(result.cards.length, 2, "Cards array parsed");
    assert.strictEqual(result.cards[0].q, "Question 1", "First question parsed");
    assert.strictEqual(result.cards[0].a, "Answer 1", "First answer parsed");
  });

  QUnit.test("parse handles missing notes", function(assert) {
    var result = dashboardifyParseFlashCardsNotes("");
    assert.ok(result, "Returns default model");
  });

  QUnit.test("parse handles malformed JSON", function(assert) {
    var result = dashboardifyParseFlashCardsNotes("not valid json");
    assert.ok(result, "Returns default model on error");
    assert.ok(result.cards, "Has cards array");
  });

  QUnit.test("parse handles null input", function(assert) {
    var result = dashboardifyParseFlashCardsNotes(null);
    assert.ok(result, "Returns default model");
  });

  QUnit.test("parse handles partial model", function(assert) {
    var notes = JSON.stringify({
      cards: [{ q: "Q1", a: "A1" }]
    });

    var result = dashboardifyParseFlashCardsNotes(notes);

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

    var result = dashboardifySerializeFlashCardsModel(model);

    assert.ok(result, "Returns string");
    var parsed = JSON.parse(result);
    assert.strictEqual(parsed.sortMethod, "sequential", "Sort method serialized");
    assert.strictEqual(parsed.displayStyle, "guess", "Display style serialized");
    assert.strictEqual(parsed.autoAdvanceEnabled, true, "Auto advance serialized");
    assert.strictEqual(parsed.autoAdvanceMs, 3000, "Auto advance ms serialized");
    assert.strictEqual(parsed.cards.length, 1, "Cards serialized");
  });

  QUnit.test("serialize handles missing fields", function(assert) {
    var model = { cards: [{ q: "Q", a: "A" }] };

    var result = dashboardifySerializeFlashCardsModel(model);

    assert.ok(result, "Returns string with defaults");
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

    var serialized = dashboardifySerializeFlashCardsModel(original);
    var parsed = dashboardifyParseFlashCardsNotes(serialized);

    assert.strictEqual(parsed.sortMethod, original.sortMethod, "Sort method preserved");
    assert.strictEqual(parsed.displayStyle, original.displayStyle, "Display style preserved");
    assert.strictEqual(parsed.autoAdvanceEnabled, original.autoAdvanceEnabled, "Auto enable preserved");
    assert.strictEqual(parsed.autoAdvanceMs, original.autoAdvanceMs, "Auto ms preserved");
    assert.strictEqual(parsed.cards.length, original.cards.length, "Card count preserved");
  });

  QUnit.test("buildFlashCardOrder returns array", function(assert) {
    var result = dashboardifyBuildFlashCardOrder(5, {});

    assert.ok(Array.isArray(result), "Returns array");
    assert.strictEqual(result.length, 5, "Correct length");
  });

  QUnit.test("buildFlashCardOrder random produces shuffle", function() {
    var orders = [];
    for (var i = 0; i < 10; i++) {
      orders.push(dashboardifyBuildFlashCardOrder(10, { sortMethod: "random" }).join(","));
    }

    var allSame = orders.every(function(o) { return o === orders[0]; });
    assert.notOk(allSame, "Random order produces different results");
  });

  QUnit.test("buildFlashCardOrder sequential produces sequential", function(assert) {
    var result = dashboardifyBuildFlashCardOrder(5, { sortMethod: "sequential" });

    assert.strictEqual(result[0], 0, "First index is 0");
    assert.strictEqual(result[1], 1, "Second index is 1");
    assert.strictEqual(result[2], 2, "Third index is 2");
    assert.strictEqual(result[3], 3, "Fourth index is 3");
    assert.strictEqual(result[4], 4, "Fifth index is 4");
  });

  QUnit.test("buildFlashCardOrder handles zero count", function(assert) {
    var result = dashboardifyBuildFlashCardOrder(0, {});
    assert.strictEqual(result.length, 0, "Empty array for zero count");
  });

  QUnit.test("buildFlashCardOrder handles single card", function(assert) {
    var result = dashboardifyBuildFlashCardOrder(1, {});
    assert.strictEqual(result.length, 1, "Single card returns array of 1");
    assert.strictEqual(result[0], 0, "First index is 0");
  });
});