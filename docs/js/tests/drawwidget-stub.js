/**
 * drawWidget stub for testing
 * Provides widget rendering without full app dependencies
 */

function drawWidget(widget) {
  var container = document.getElementById("widgetcontainer");
  if (!container) return;
  
  var BookmarkDisplayText = widget.BookmarkDisplayText || "";
  var WidgetURL = widget.WidgetURL || "";
  var WidgetCSSClass = widget.WidgetCSSClass || "";
  var Notes = widget.Notes || "";
  var PositionX = widget.PositionX || 0;
  var PositionY = widget.PositionY || 0;
  var SizeX = widget.SizeX || 300;
  var SizeY = widget.SizeY || 200;
  var RecID = widget.RecID || "widget-" + Date.now();
  var WidgetType = widget.WidgetType || "Bookmark";
  
  var widgetTypeNorm = String(WidgetType || "").trim();
  
  var siteurl = "";
  var ridJs = String(RecID).replace(/\\/g, "\\\\").replace(/"/g, '\\"');
  
  var PositionAndSize = "left: " + PositionX + "px; top: " + PositionY + "px; width: " + SizeX + "px; height: " + SizeY + "px;";
  var PositionAndCSSClass = PositionAndSize + "class='widget resize " + WidgetCSSClass + "'";
  
  var combined = "<div id='" + RecID + "' style='margin:15px; position:absolute; background-color: white; border: 1px solid black;" + PositionAndCSSClass + "'>";
  
  switch (widgetTypeNorm) {
    case "Bookmark":
      var displayText = BookmarkDisplayText || WidgetURL || "Link";
      var linkUrl = escapeHtmlAttr(WidgetURL);
      var escapedDisplay = escapeHtmlAttr(displayText);
      echo(combined + "<div style='padding:15px;'><a href='" + linkUrl + "' target='_blank'>" + escapedDisplay + "</a></div></div>");
      break;
      
    case "IFrame":
      var iframeSrc = escapeHtmlAttr(WidgetURL);
      echo(combined + "<iframe style='height:100%;width:100%' src='" + iframeSrc + "'></iframe></div>");
      break;
      
    case "Notes":
      var notesvalue = escapeHtmlForTextarea(Notes);
      echo(combined + "<div style='height:100%;' id='" + RecID + "_note'><p style='padding:15px;'><md-block>" + notesvalue + "</md-block></p></div></div>");
      break;
      
    case "Image":
      var objectFit = dashboardifyImageObjectFitFromNotes(Notes);
      var srcAttr = escapeHtmlAttr(WidgetURL || "");
      echo(combined + "<div style='width:100%;height:100%;overflow:hidden;box-sizing:border-box;background:transparent;'>" +
        "<img alt='' loading='lazy' style='width:100%;height:100%;display:block;object-fit:" + objectFit + ";' src='" + srcAttr + "'>" +
        "</div></div>");
      break;
      
    case "Flash Cards":
      var model = dashboardifyParseFlashCardsNotes(Notes);
      echo(combined + "<div class='flashcards-body'><p>Flash Cards: " + model.cards.length + " cards</p></div></div>");
      break;
      
    case "Countdown":
      var title = BookmarkDisplayText;
      var days = 0;
      if (Notes) {
        var dateValue = new Date(Notes);
        var now = new Date();
        var diff = dateValue - now;
        days = Math.floor(diff / (1000 * 60 * 60 * 24));
      }
      echo(combined + "<div style='text-align:center;'><div><b>" + title + "</b></div><br /><div>" + days + " Days Remaining.</div></div></div>");
      break;
      
    case "Clock":
      var clockTitle = BookmarkDisplayText;
      var timezone = Notes || "America/New_York";
      var formattedTime = "";
      try {
        var clockNow = new Date();
        var clockOptions = { timeZone: timezone, year: "numeric", month: "2-digit", day: "2-digit", hour: "2-digit", minute: "2-digit", hour12: true };
        formattedTime = clockNow.toLocaleString("en-US", clockOptions);
      } catch (e) {
        formattedTime = "Invalid timezone";
      }
      echo(combined + "<div style='padding:15px;height:calc(100%-30px);box-sizing:border-box;display:flex;flex-direction:column;justify-content:center;align-items:center;text-align:center;'>" +
        "<div style='font-weight:bold;margin-bottom:8px;'>" + clockTitle + "</div>" +
        "<div style='font-size:1.5em;'>" + formattedTime + "</div></div></div>");
      break;
      
    case "Collapseable IFrame":
      var collapseTitle = String(BookmarkDisplayText || "").replace(/&/g, "&amp;").replace(/</g, "&lt;");
      var collapseSrc = escapeHtmlAttr(WidgetURL);
      echo(combined + "<div class='collapseable-iframe'><div class='collapseable-header'>" + collapseTitle + "</div>" +
        "<iframe style='height:100%;width:100%' src='" + collapseSrc + "'></iframe></div></div>");
      break;
      
    case "HTMLEmbed":
      echo(combined + "<div style='padding:8px;overflow:auto;height:calc(100% - 40px);'>" + Notes + "</div></div>");
      break;
      
    default:
      echo(combined + "<div style='padding:15px;'>Widget type: " + widgetTypeNorm + "</div></div>");
  }
}