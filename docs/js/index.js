// Function to add options to the select element
function addOptionsToSelect() {
    const selectElement = document.getElementById('ddlWidgetType2');

    if (selectElement) {
        const optionValues = [
            { value: 'SQLServerScalarQuery', text: 'SQLServerScalarQuery' },
            { value: 'SQLiteResultsList', text: 'SQLiteResultsList' },
            { value: 'SQLite Chart (PHPGD)', text: 'SQLite Chart (PHPGD)' },
            { value: 'Flash Cards', text: 'Flash Cards' }
        ];

        optionValues.forEach(option => {
            const newOption = document.createElement('option');
            newOption.value = option.value;
            newOption.textContent = option.text;
            selectElement.appendChild(newOption);
        });
    } else {
        console.error('Select element with id "ddlWidgetType2" not found.');
    }
}

function isAdmin() {
	const cookies = document.cookie.split('; ');
    const isAdminCookie = cookies.find(cookie => cookie.startsWith('isAdmin='));
    if (isAdminCookie && isAdminCookie.split('=')[1] === 'true') {
        return true;
    } else {
		return false;
	}

}

// this function runs when the DOM has fully loaded:
function main() {
    if (window.DashboardifyWidgetTypesManagedByCloud) {
        return;
    }
    if (isAdmin() || window.DashboardifyShowSqlWidgets) {
        addOptionsToSelect();
    }


}

function calculateHoursSinceEvent(eventDate) {
    // Create a Date object for the event time (assuming it's in local time)
    const eventTime = new Date(eventDate);

    // Get the current time
    const now = new Date();

    // Calculate the difference in milliseconds
    const diffMs = now.getTime() - eventTime.getTime();

    // Get the local timezone offset in minutes (positive if behind UTC, negative if ahead of UTC)
    const localOffsetMinutes = now.getTimezoneOffset(); // Local offset from UTC in minutes

    // Convert local offset to milliseconds
    const localOffsetMs = localOffsetMinutes * 60 * 1000;

    // Adjust the difference by subtracting the local timezone offset
    const adjustedDiffMs = diffMs - localOffsetMs; // Subtract the offset to account for local time

    // Convert milliseconds to hours
    const hoursDiff = Math.floor(adjustedDiffMs / (1000 * 60 * 60));

    // Debug output
    console.log("Event Time (Local):", eventTime.toString());
    console.log("Current Time (Local):", now.toString());
    console.log("Local Offset (minutes):", localOffsetMinutes);
    console.log("Local Offset (milliseconds):", localOffsetMs);
    console.log("Adjusted Difference (milliseconds):", adjustedDiffMs);
    console.log("Hours Difference:", hoursDiff);

    return hoursDiff;
}

async function populateEditRecID(recid) {
  var dlg = document.getElementById("NewWidgetDialog2");
  if (dlg) dlg.style.display = "block";
  var ad = window.DashboardifyDataAdapter;
  if (!ad || !ad.dataCache) {
    console.error("Dashboardify: sign in via the cloud app to edit widgets.");
    return;
  }
  var widget = (ad.dataCache.widgets || []).find(function (w) {
    return String(w.RecID) === String(recid);
  });
  if (!widget) {
    console.warn("Widget not found:", recid);
    return;
  }
  fillEditWidgetFormFromRecord(widget, recid);
}

async function getWidgetsForDashboard(dashboardID) {
	if (window.DashboardifyDataAdapter && typeof window.DashboardifyDataAdapter.getWidgetsForDashboard === "function") {
		document.cookie = "dashboardid=" + dashboardID;
		var cloudWidgets = await window.DashboardifyDataAdapter.getWidgetsForDashboard(dashboardID);
		renderwidgetsfromjson(cloudWidgets || []);
		return;
	}
	console.warn(
		"Dashboardify: no cloud storage adapter. Open cloud-login.html and sign in."
	);
	if (typeof clearWidgetContainer === "function") {
		clearWidgetContainer();
	}
}

function renderwidgetsfromjson(widgets) {
	window.DashboardifyAllWidgets = widgets;
	// Create a new array for HTMLEmbed widgets
	const htmlEmbedWidgets = [];
	
	// Iterate through the array of objects
	for (const widget of widgets) {
	  // Destructure the object to create variables for each field
	  const {
		BookmarkDisplayText,
		WidgetURL,
		WidgetCSSClass,
		Notes,
		PositionX,
		PositionY,
		SizeX,
		SizeY,
		RecID,
		DashboardRecID,
		WidgetType
	  } = widget;
	
	  // Check if WidgetType is 'HTMLEmbed'
	  if (WidgetType == "HTMLEmbed") {
		// Add the widget to the htmlEmbedWidgets array
		htmlEmbedWidgets.push(widget);
	  } else {	  
	  	  drawWidget(widget);
	  }
	}
	
	// Manually process the list of HTML Embeds, after everything else
	
	// Loop through the htmlEmbedWidgets array and call drawWidget for each widget
	for (const widget of htmlEmbedWidgets) {
	  drawWidget(widget);
	}
	
	// Rendering is now finished.
	// Hash value should have been sent and set into a cookie now automatically, using PHP's 'setcookie' value. So next time, we should check if the hash for this dashboard has been found. 
	
}

function fillEditWidgetFormFromRecord(result2, RecID) {
	var x = document.getElementById("ddlWidgetType2");
	if (x) x.value = result2.WidgetType;
	drawNewWidgetBasedOnType();
	var y = document.getElementById("txtWidgetID");
	if (y) y.value = result2.RecID != null ? result2.RecID : RecID;
	var CSSClass = document.getElementById("txtCSSClass");
	if (CSSClass) CSSClass.value = result2.WidgetCSSClass || "";
	var elPX = document.getElementById("txtpositionx2");
	if (elPX) {
		var px = result2.PositionX;
		elPX.value =
			px != null && String(px).trim() !== "" ? px : "";
	}
	var elPY = document.getElementById("txtpositiony2");
	if (elPY) {
		var py = result2.PositionY;
		elPY.value =
			py != null && String(py).trim() !== "" ? py : "";
	}
	var elSX = document.getElementById("txtsizeX2");
	if (elSX) elSX.value = result2.SizeX;
	var elSY = document.getElementById("txtsizeY2");
	if (elSY) elSY.value = result2.SizeY;

	switch (result2.WidgetType) {
		case "Bookmark":
			var z = document.getElementById("txtWidgetURL");
			if (z) z.value = result2.WidgetURL || "";
			var BookmarkDisplayText = document.getElementById("txtWidgetDisplayText");
			if (BookmarkDisplayText) BookmarkDisplayText.value = result2.BookmarkDisplayText || "";
			break;
		case "Notes":
		case "HTMLEmbed":
			var NotesEl = document.getElementById("txtNotes");
			if (NotesEl) NotesEl.value = result2.Notes || "";
			break;
		case "Clock": {
			var ClockTitle = document.getElementById("txtWidgetDisplayText");
			if (ClockTitle) ClockTitle.value = result2.BookmarkDisplayText || "";
			var ClockTimezone = document.getElementById("ddlClockTimezone");
			if (ClockTimezone && result2.Notes) {
				ClockTimezone.value = result2.Notes;
			}
			break;
		}
		case "Countdown":
		case "CountUp_Hours":
			var CountdownTitle = document.getElementById("txtWidgetDisplayText");
			if (CountdownTitle) CountdownTitle.value = result2.BookmarkDisplayText || "";
			var CountdownDate = document.getElementById("datepicker");
			if (CountdownDate && result2.Notes) {
				var dateValue = result2.Notes;
				var parts = String(dateValue).split("/");
				if (parts.length === 3) {
					var month = parts[0];
					var day = parts[1];
					var year = parts[2];
					CountdownDate.value =
						year +
						"-" +
						String(month).padStart(2, "0") +
						"-" +
						String(day).padStart(2, "0");
				} else {
					CountdownDate.value = dateValue;
				}
			}
			break;
		case "IFrame":
		case "Collapseable IFrame":
			var displayt = document.getElementById("txtWidgetDisplayText");
			if (displayt) displayt.value = result2.BookmarkDisplayText || "";
			var URL2 = document.getElementById("txtWidgetURL");
			if (URL2) URL2.value = result2.WidgetURL || "";
			break;
		case "Image":
			var imgUrl = document.getElementById("txtWidgetURL");
			if (imgUrl) imgUrl.value = result2.WidgetURL || "";
			var fitSel = document.getElementById("ddlImageObjectFit");
			if (fitSel) {
				var v = dashboardifyImageObjectFitFromNotes(result2.Notes);
				fitSel.value = v;
			}
			break;
		case "SQLiteResultsList":
		case "SQLServerScalarQuery":
		case "SQLite Chart (PHPGD)":
			var dbname = document.getElementById("SQLDBName");
			if (dbname) dbname.value = result2.sqldbname || "";
			var sqlquery = document.getElementById("sqlquery");
			if (sqlquery) sqlquery.value = result2.sqlquery || "";
			break;
		case "Flash Cards":
			var flashModel = dashboardifyParseFlashCardsNotes(result2.Notes);
			var flashSort = document.getElementById("flashSortMethod");
			if (flashSort) flashSort.value = flashModel.sortMethod || "random";
			var flashStyle = document.getElementById("flashDisplayStyle");
			if (flashStyle) flashStyle.value = flashModel.displayStyle === "guess" ? "guess" : (flashModel.displayStyle === "multiplechoice" ? "multiplechoice" : "full");
			var flashAuto = document.getElementById("flashAutoAdvanceEnabled");
			if (flashAuto) flashAuto.checked = !!flashModel.autoAdvanceEnabled;
			var flashAutoMs = document.getElementById("flashAutoAdvanceMs");
			if (flashAutoMs) flashAutoMs.value = flashModel.autoAdvanceMs || 5000;
			var flashData = document.getElementById("txtFlashCardsData");
			if (flashData) flashData.value = JSON.stringify(flashModel.cards || []);
			var flashHint = document.getElementById("flashCardsCountHint");
			if (flashHint) flashHint.textContent = (flashModel.cards || []).length + " questions saved.";
			break;
		default:
			break;
	}
	var sAddr = document.getElementById("SQLServerAddressName");
	if (sAddr && result2.sqlserveraddress != null) sAddr.value = result2.sqlserveraddress;
	var sDb = document.getElementById("SQLDBName");
	if (sDb && result2.sqldbname != null) sDb.value = result2.sqldbname;
	var sUser = document.getElementById("sqluser");
	if (sUser && result2.sqluser != null) sUser.value = result2.sqluser;
	var sPass = document.getElementById("sqlpass");
	if (sPass && result2.sqlpass != null) sPass.value = result2.sqlpass;
	var sQ = document.getElementById("sqlquery");
	if (sQ && result2.sqlquery != null) sQ.value = result2.sqlquery;
}

function editwidget(RecID) {
	var dlg = document.getElementById("NewWidgetDialog2");
	if (dlg) dlg.style.display = "block";
	var ad = window.DashboardifyDataAdapter;
	if (!ad || !ad.dataCache) {
		console.error("Dashboardify: sign in via the cloud app to edit widgets.");
		return;
	}
	var result2 = (ad.dataCache.widgets || []).find(function (w) {
		return String(w.RecID) === String(RecID);
	});
	if (!result2) {
		console.warn("Widget not found:", RecID);
		return;
	}
	fillEditWidgetFormFromRecord(result2, RecID);
}

/** Stored in widget Notes; maps to CSS object-fit. */
function dashboardifyImageObjectFitFromNotes(notes) {
	var allowed = ["contain", "cover", "fill", "none", "scale-down"];
	var s = String(notes || "").trim();
	if (allowed.indexOf(s) !== -1) {
		return s;
	}
	return "contain";
}

function escapeHtmlAttr(s) {
	return String(s)
		.replace(/&/g, "&amp;")
		.replace(/"/g, "&quot;")
		.replace(/'/g, "&#39;");
}

function dashboardifyEscapeHtmlText(s) {
	return String(s || "")
		.replace(/&/g, "&amp;")
		.replace(/</g, "&lt;")
		.replace(/>/g, "&gt;");
}

function dashboardifyDefaultFlashCardsModel() {
	return {
		sortMethod: "random",
		displayStyle: "full",
		autoAdvanceEnabled: false,
		autoAdvanceMs: 5000,
		cards: []
	};
}

/** Empty or unknown → "full". */
function dashboardifyNormalizeFlashCardDisplayStyle(v) {
	var s = String(v == null ? "" : v).trim().toLowerCase();
	if (s === "guess") return "guess";
	if (s === "multiplechoice") return "multiplechoice";
	return "full";
}

function dashboardifyNormalizeFlashCard(card) {
	if (!card || typeof card !== "object") return null;
	var q = String(card.q || card.question || "").trim();
	var a = String(card.a || card.answer || "").trim();
	var note = String(card.note || "").trim();
	var mcq1 = String(card.mcq1 || "").trim();
	var mcq2 = String(card.mcq2 || "").trim();
	var mcq3 = String(card.mcq3 || "").trim();
	var mcq4 = String(card.mcq4 || "").trim();
	if (!q && !a && !note && !mcq1 && !mcq2 && !mcq3 && !mcq4) return null;
	return { q: q, a: a, note: note, mcq1: mcq1, mcq2: mcq2, mcq3: mcq3, mcq4: mcq4 };
}

function dashboardifyParseFlashCardsNotes(notes) {
	var model = dashboardifyDefaultFlashCardsModel();
	if (!notes) return model;
	try {
		var parsed = typeof notes === "string" ? JSON.parse(notes) : notes;
		model.sortMethod = parsed.sortMethod === "forward" || parsed.sortMethod === "reverse" ? parsed.sortMethod : "random";
		model.displayStyle = dashboardifyNormalizeFlashCardDisplayStyle(parsed.displayStyle);
		model.autoAdvanceEnabled = !!parsed.autoAdvanceEnabled;
		var ms = Number(parsed.autoAdvanceMs);
		model.autoAdvanceMs = Number.isFinite(ms) && ms > 0 ? Math.round(ms) : 5000;
		model.cards = Array.isArray(parsed.cards)
			? parsed.cards.map(dashboardifyNormalizeFlashCard).filter(function (x) { return !!x; })
			: [];
	} catch (e) {
		model.cards = [];
	}
	return model;
}

function dashboardifySerializeFlashCardsModel(inputModel) {
	var model = inputModel && typeof inputModel === "object" ? inputModel : {};
	return JSON.stringify({
		sortMethod: model.sortMethod === "forward" || model.sortMethod === "reverse" ? model.sortMethod : "random",
		displayStyle: dashboardifyNormalizeFlashCardDisplayStyle(model.displayStyle),
		autoAdvanceEnabled: !!model.autoAdvanceEnabled,
		autoAdvanceMs: Number.isFinite(Number(model.autoAdvanceMs)) && Number(model.autoAdvanceMs) > 0 ? Math.round(Number(model.autoAdvanceMs)) : 5000,
		cards: Array.isArray(model.cards) ? model.cards.map(dashboardifyNormalizeFlashCard).filter(function (x) { return !!x; }) : []
	});
}

window.dashboardifySerializeFlashCardsModel = dashboardifySerializeFlashCardsModel;

function dashboardifyBuildFlashCardOrder(count, sortMethod) {
	var order = [];
	for (var i = 0; i < count; i++) order.push(i);
	if (sortMethod === "reverse") return order.reverse();
	if (sortMethod === "forward") return order;
	for (var j = order.length - 1; j > 0; j--) {
		var k = Math.floor(Math.random() * (j + 1));
		var t = order[j];
		order[j] = order[k];
		order[k] = t;
	}
	return order;
}

/** Larger type for short prompts; smaller for long text (score = chars + words*8). */
function dashboardifyFlashCardScaleClass(text) {
	var t = String(text || "").trim();
	var n = t.length;
	var wc = t ? t.split(/\s+/).filter(Boolean).length : 0;
	var score = n + wc * 8;
	if (score <= 42) return "flashcards-scale--xl";
	if (score <= 100) return "flashcards-scale--lg";
	if (score <= 240) return "flashcards-scale--md";
	return "flashcards-scale--sm";
}

function dashboardifyApplyFlashCardTextScale(el, text) {
	if (!el) return;
	var parts = String(el.className || "")
		.split(/\s+/)
		.filter(Boolean)
		.filter(function (c) {
			return c.indexOf("flashcards-scale--") !== 0;
		});
	parts.push(dashboardifyFlashCardScaleClass(text));
	el.className = parts.join(" ");
}

function drawWidget(widget) {
	//Variables
	// Deserialize JSON data in JavaScript
	const {
		BookmarkDisplayText,
		WidgetURL,
		WidgetCSSClass,
		Notes,
		PositionX,
		PositionY,
		SizeX,
		SizeY,
		RecID,
		DashboardRecID,
		WidgetType
	  } = widget;

	  const widgetTypeNorm = String(WidgetType || "").trim();
	  
		// Variable Declarations to be shared between widget types: 
		let siteurl = getrooturlpath();
		var ridJs = String(RecID).replace(/\\/g, "\\\\").replace(/"/g, '\\"');
		let editbuttonscss =
			"<a class='editbuttons' onclick='editwidget(\"" +
			ridJs +
			"\")' style='display:none;height:24px; width:24px;'";
		const imgstylecss = "<img style='height:24px; width:24px;' src='";
		deletebuttonOnClick = "deleteWidget(\"" + ridJs + "\")";
		deletebuttoncss =
			"<a class='editbuttons' style='display:none;height:24px; width:24px;' onclick='" +
			deletebuttonOnClick +
			"'";

		PositionAndSize = "left: " + PositionX + "px; top: " + PositionY + "px; width: " + SizeX + "px; height: " + SizeY + "px; width: " + SizeX + "px;' ";
		
		PositionAndCSSClass = PositionAndSize + "class='widget resize " + WidgetCSSClass + " Countdown'>";
		
		combined = "<div id='" + RecID + "' style='margin:15px; position:absolute; background-color: white;  border: 1px solid black;" + PositionAndCSSClass + editbuttonscss + ">" + imgstylecss + siteurl + "icons/edit.png'></img></a>" + deletebuttoncss + ">" + imgstylecss + siteurl + "icons/cancel.png'></img></a>";
	  
	  
	  switch (widgetTypeNorm) {
	  	case "Bookmark":
	  		{
	  			var sx = PositionX == null ? "" : String(PositionX).trim();
	  			var sy = PositionY == null ? "" : String(PositionY).trim();
	  			var bx = parseFloat(sx);
	  			var by = parseFloat(sy);
	  			var bookmarkAutoTile =
	  				(sx === "" && sy === "") ||
	  				(Number.isFinite(bx) &&
	  					Number.isFinite(by) &&
	  					bx === 0 &&
	  					by === 0);
	  			if (!bookmarkAutoTile) {
	  			var floatingbookmark =
	  				"<div id='" +
	  				RecID +
	  				"' class='widget bookmark " +
	  				WidgetCSSClass +
	  				"' style='position:absolute;width:100px;background-color:lightgrey;border:1px solid black;left:" +
	  				PositionX +
	  				"px;top:" +
	  				PositionY +
	  				"px;'>" +
	  				editbuttonscss +
	  				">" +
	  				imgstylecss +
	  				siteurl +
	  				"icons/edit.png'></img></a>" +
	  				deletebuttoncss +
	  				">" +
	  				imgstylecss +
	  				siteurl +
	  				"icons/cancel.png'></img></a>" +
	  				"<div style='padding:5px;width:100%;'><a target='_blank' href='" +
	  				String(WidgetURL).replace(/'/g, "%27") +
	  				"'>" +
	  				String(BookmarkDisplayText).replace(/</g, "&lt;").replace(/>/g, "&gt;") +
	  				"</a></div></div>";
				var y = document.getElementById("widgetcontainer");
				y.innerHTML += floatingbookmark;
			} else {
	  			let x =
	  				"<div id='" +
	  				RecID +
	  				"' style='padding: 5px; margin: 5px; width:100px; background-color: lightgrey; border: 1px solid black;' class='widget bookmark " +
	  				WidgetCSSClass +
	  				"'><a target='_blank' href='" +
	  				String(WidgetURL).replace(/'/g, "%27") +
	  				"'>" +
	  				String(BookmarkDisplayText).replace(/</g, "&lt;").replace(/>/g, "&gt;") +
	  				"</a>" +
	  				editbuttonscss +
	  				">" +
	  				imgstylecss +
	  				siteurl +
	  				"icons/edit.png'></img></a>" +
	  				deletebuttoncss +
	  				">" +
	  				imgstylecss +
	  				siteurl +
	  				"icons/cancel.png'></img></a></div>";
	  			let yb = document.getElementById("widgetcontainer");
				yb.innerHTML += x;
	  		}
	  		}
	  		break;
	  	case "Notes":
	  		// Escape the Notes value to replace '' with '
			let notesvalue = (Notes || "").replace(/''/g, "'");
			let left = PositionX + "px";
			let top = PositionY + "px";
			let width = SizeX + "px";
			let height = SizeY + "px";
			let customclass = WidgetCSSClass;
			let combined_notes =
				"<div class='notes resize widget " +
				customclass +
				" ' id='" +
				RecID +
				"' style='margin:15px; position:absolute; background-color: white;  border: 1px solid black;left: " +
				left +
				"; top: " +
				top +
				"; width: " +
				width +
				"; height: " +
				height +
				";'>" +
				"<a class='editbuttons' style='display:none;height:24px; width:24px;' onclick='editwidget(\"" +
				ridJs +
				"\")'>" +
				imgstylecss +
				siteurl +
				"icons/edit.png'></img></a>" +
				deletebuttoncss +
				">" +
				imgstylecss +
				siteurl +
				"icons/cancel.png'></img></a>";
		
			echo(combined_notes + "<div style='height: 100%;' id='" + RecID + "_note'><p class='note' style='padding-left: 15px; padding-right: 15px;'><md-block>" + notesvalue +"</md-block></p></div></div>");
	  		
	  		break;
		case "Countdown":
			
			stringValue = Notes;
			
			// Get title
			title = BookmarkDisplayText;
			
			
			// Calculate time from now until date value, in days
			let now = new Date();
			// Convert to date format
			let dateValue = new Date(stringValue);
			let interval = Math.floor((dateValue - now) / (1000 * 60 * 60 * 24));
			let days = interval;
			
			//Redraw widget details, to add in custom widget class for styling later on...
			
			echo(combined + "<p style='padding-left: 15px; padding-right: 15px;'><div style='text-align: center;'><div id='countdowntitle'><b>" + title + "</b></div><br /><div id='countdownvalue'>" + days + " Days Remaining.</div></div></p></div>");
				
			break;
		case "Clock": {
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
			echo(combined + "<div style='padding: 15px; height: calc(100% - 30px); box-sizing: border-box; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;'><div style='font-weight: bold; margin-bottom: 8px;'>" + clockTitle + "</div><div style='font-size: 1.5em;'>" + formattedTime + "</div></div>");
			break;
		}
		case "CountUp_Hours":
		
			//stringValue = Notes; // Example: "2024-09-08T15:00:00"
			stringValue = "2024-09-08T14:00";
			// Get title
			title = BookmarkDisplayText;
			// Example usage
			const hoursPassed = calculateHoursSinceEvent(stringValue);
			console.log(`Hours passed since ${stringValue}:`, hoursPassed);
			
			//Redraw widget details, to add in custom widget class for styling later on...
					
			echo(combined + "<p style='padding-left: 15px; padding-right: 15px;'><div style='text-align: center;'><div id='countdowntitle'><b>" + title + "</b></div><br /><div id='countdownvalue'>" + hoursPassed + " Hours." + stringValue + "</div></div></p></div>");
				
			break;
		case "IFrame":
			echo(combined + "<iframe style='height:100%;width:100%' src='" + WidgetURL + "'></iframe></a></div>");
			break;
		case "Image": {
			var objectFit = dashboardifyImageObjectFitFromNotes(Notes);
			var srcAttr = escapeHtmlAttr(WidgetURL || "");
			var imgClass = String(WidgetCSSClass || "").replace(/"/g, "");
			var imageOuter =
				"<div id='" +
				RecID +
				"' style='margin:0;position:absolute;background:transparent;border:none;outline:none;box-shadow:none;" +
				PositionAndSize +
				"class='widget resize " +
				imgClass +
				" dashboardify-image-widget'>" +
				editbuttonscss +
				">" +
				imgstylecss +
				siteurl +
				"icons/edit.png'></img></a>" +
				deletebuttoncss +
				">" +
				imgstylecss +
				siteurl +
				"icons/cancel.png'></img></a>";
			echo(
				imageOuter +
					"<div class='image-widget-body' style='width:100%;height:100%;overflow:hidden;box-sizing:border-box;background:transparent;'>" +
					"<img alt='' loading='lazy' decoding='async' referrerpolicy='no-referrer' style='width:100%;height:100%;display:block;object-fit:" +
					objectFit +
					";background:transparent;' src='" +
					srcAttr +
					"'>" +
					"</div></div>"
			);
			break;
		}
		case "Collapseable IFrame": {
			var collapseTitle = String(BookmarkDisplayText || "")
				.replace(/&/g, "&amp;")
				.replace(/</g, "&lt;")
				.replace(/>/g, "&gt;");
			var collapseOuter =
				"<div id='" +
				RecID +
				"' class='collapse collapseable-iframe-wrap widget resize " +
				WidgetCSSClass +
				"' style='position:absolute;left:" +
				PositionX +
				"px;top:" +
				PositionY +
				"px;width:" +
				SizeX +
				"px;box-sizing:border-box;'>" +
				"<div class='collapse-iframe-header' style='display:flex;align-items:center;gap:4px;min-height:24px;width:100%;box-sizing:border-box;border:1px solid black;background-color:white;padding:2px 4px;'>" +
				editbuttonscss +
				">" +
				imgstylecss +
				siteurl +
				"icons/edit.png'></img></a>" +
				deletebuttoncss +
				">" +
				imgstylecss +
				siteurl +
				"icons/cancel.png'></img></a>" +
				"<a class='collapse-iframe-title' onclick='opencollapsediframe(&quot;" +
				RecID +
				"&quot;)'>" +
				collapseTitle +
				"</a></div>" +
				"<div class='collapse-iframe-panel' style='display:none;box-sizing:border-box;width:100%;height:" +
				SizeY +
				"px;background-color:white;border:1px solid black;border-top:none;'>" +
				"<iframe style='height:100%;width:100%;border:0;display:block;' id='" +
				RecID +
				"/iframe' src2=\"" +
				escapeHtmlAttr(WidgetURL || "") +
				"\"></iframe></div></div>";
			echo(collapseOuter);
			break;
		}
		case "HTMLEmbed":
			//echo(combined + Notes+"</div>");
			break;
		case "Flash Cards": {
			var flashModel = dashboardifyParseFlashCardsNotes(Notes);
			var cards = flashModel.cards || [];
			var firstQ = cards.length ? cards[0].q : "No questions yet.";
			var firstA = cards.length ? cards[0].a : "Use edit mode to add your first card.";
			var isGuess = flashModel.displayStyle === "guess";
			var isMcq = flashModel.displayStyle === "multiplechoice";
			var flashTitle = BookmarkDisplayText;
			var gearButton =
				"<a class='editbuttons flashcards-edit-extra' style='display:none;height:24px; width:24px;' onclick='dashboardifyOpenFlashCardsManager(\"" +
				ridJs +
				"\")' title='Manage questions'><span class='flashcards-edit-icon' aria-hidden='true'>&#9881;</span></a>";
			var quickAddButton =
				"<a class='editbuttons flashcards-edit-extra' style='display:none;height:24px; width:24px;' onclick='dashboardifyOpenFlashCardsQuickAdd(\"" +
				ridJs +
				"\")' title='Add question'><span class='flashcards-edit-icon' aria-hidden='true'>+</span></a>";
			var answerSection;
			if (isMcq) {
				answerSection = "<div class='flashcards-mcq' id='flashcards-mcq-" + ridJs + "'></div>";
			} else if (isGuess) {
				answerSection = "<div class='flashcards-answer-stack' id='flashcards-answer-stack-" +
					ridJs +
					"'>" +
					"<div class='flashcards-answer' id='flashcards-answer-" +
					ridJs +
					"'></div>" +
					"<button type='button' class='menubar flashcards-reveal-btn' id='flashcards-reveal-" +
					ridJs +
					"' onclick='dashboardifyFlashCardRevealAnswer(\"" +
					ridJs +
					"\")'>Show answer</button>" +
					"</div>";
			} else {
				answerSection = "<div class='flashcards-answer' id='flashcards-answer-" + ridJs + "'></div>";
			}
			var bodyClass = isMcq ? " flashcards-body--mcq" : (isGuess ? " flashcards-body--guess" : " flashcards-body--full");
			var markup =
				"<div id='" +
				RecID +
				"' style='margin:15px; position:absolute; background-color: white; border: 1px solid black;" +
				PositionAndCSSClass +
				editbuttonscss +
				">" +
				imgstylecss +
				siteurl +
				"icons/edit.png'></img></a>" +
				gearButton +
				quickAddButton +
				deletebuttoncss +
				">" +
				imgstylecss +
				siteurl +
				"icons/cancel.png'></img></a>" +
				"<div class='flashcards-body" + bodyClass + "'>" +
				(flashTitle ? "<div class='flashcards-title'>" + dashboardifyEscapeHtmlText(flashTitle) + "</div>" : "") +
				"<div class='flashcards-question' id='flashcards-question-" +
				ridJs +
				"'>" +
				dashboardifyEscapeHtmlText(firstQ) +
				"</div>" +
				answerSection +
				"<div class='flashcards-nav-row'>" +
				"<button type='button' class='flashcards-nav-btn' onclick='dashboardifyFlashCardPrev(\"" +
				ridJs +
				"\")' aria-label='Previous question'>&uarr;</button>" +
				"<button type='button' class='flashcards-nav-btn' onclick='dashboardifyFlashCardNext(\"" +
				ridJs +
				"\")' aria-label='Next question'>&darr;</button>" +
				"</div></div></div>";
			echo(markup);
			window.DashboardifyFlashCardRuntime = window.DashboardifyFlashCardRuntime || {};
			window.DashboardifyFlashCardRuntime[String(RecID)] = {
				model: flashModel,
				order: dashboardifyBuildFlashCardOrder(cards.length, flashModel.sortMethod),
				index: 0,
				timerId: null,
				revealed: false,
				mcqAnswered: false
			};
			dashboardifyRenderFlashCardState(String(RecID));
			break;
		}
		default: 
			if (typeof window.findCustomWidgetProvider === "function") {
				var prov = window.findCustomWidgetProvider(WidgetType);
				if (prov) {
					var inner = "";
					if (prov.HTML_Content && String(prov.HTML_Content).trim() !== "") {
						inner = String(prov.HTML_Content);
						if (Notes && inner.indexOf("{{Notes}}") !== -1) {
							inner = inner.split("{{Notes}}").join(String(Notes));
						}
					} else if (prov.PHP_To_Run && String(prov.PHP_To_Run).trim() !== "") {
						inner = "<p style='padding:12px;font-size:12px'>This widget type had server-side code in an older version. Add <b>HTML_Content</b> for this custom provider in your cloud data (Settings → User data) to render HTML here. You can use <code>{{Notes}}</code> as a placeholder for the widget Notes field.</p>";
					}
					if (inner !== "") {
						echo(combined + "<div class='custom-widget-provider-inner' style='padding:8px;overflow:auto;height:calc(100% - 40px);'>" + inner + "</div></div>");
						break;
					}
				}
			}
			console.log("Not yet handling widget: " + RecID + ", " + WidgetType);
	  	  
	  }
	
	
}

function echo(stringData) {
	var container = document.getElementById('widgetcontainer');
	container.innerHTML += stringData;
}

function clearWidgetContainer() {
	var container = document.getElementById('widgetcontainer');
	container.innerHTML = "";
	if (window.DashboardifyFlashCardRuntime) {
		Object.keys(window.DashboardifyFlashCardRuntime).forEach(function (k) {
			var st = window.DashboardifyFlashCardRuntime[k];
			if (st && st.timerId) clearTimeout(st.timerId);
		});
	}
}

function dashboardifyRenderFlashCardState(recId) {
	var runtime = window.DashboardifyFlashCardRuntime || {};
	var st = runtime[String(recId)];
	if (!st) return;
	if (st.timerId) {
		clearTimeout(st.timerId);
		st.timerId = null;
	}
	var qEl = document.getElementById("flashcards-question-" + recId);
	var aEl = document.getElementById("flashcards-answer-" + recId);
	var revealBtn = document.getElementById("flashcards-reveal-" + recId);
	var mcqEl = document.getElementById("flashcards-mcq-" + recId);
	if (!qEl || !aEl) return;
	var cards = (st.model && st.model.cards) || [];
	var isGuess = st.model && st.model.displayStyle === "guess";
	var isMcq = st.model && st.model.displayStyle === "multiplechoice";
	if (!cards.length || !st.order.length) {
		qEl.textContent = "No questions yet.";
		aEl.textContent = "Use edit mode to add your first card.";
		aEl.classList.remove("flashcards-answer--placeholder");
		dashboardifyApplyFlashCardTextScale(qEl, qEl.textContent);
		dashboardifyApplyFlashCardTextScale(aEl, aEl.textContent);
		if (revealBtn) revealBtn.style.display = "none";
		if (mcqEl) mcqEl.innerHTML = "";
		return;
	}
	if (st.index < 0) st.index = st.order.length - 1;
	if (st.index >= st.order.length) st.index = 0;
	var card = cards[st.order[st.index]] || { q: "", a: "" };
	var qText = card.q || "";
	var aText = card.a || "";
	var noteText = card.note || "";
	qEl.textContent = qText;
	dashboardifyApplyFlashCardTextScale(qEl, qText);
	if (isMcq) {
		var mcqOptions = [];
		if (card.mcq1) mcqOptions.push({ label: card.mcq1, id: 1 });
		if (card.mcq2) mcqOptions.push({ label: card.mcq2, id: 2 });
		if (card.mcq3) mcqOptions.push({ label: card.mcq3, id: 3 });
		if (card.mcq4) mcqOptions.push({ label: card.mcq4, id: 4 });
		var noteHtml = noteText ? "<div class='flashcards-note'>" + dashboardifyEscapeHtmlText(noteText) + "</div>" : "";
		if (mcqOptions.length > 0) {
			var optionsHtml = "";
			mcqOptions.forEach(function (opt) {
				var selectedClass = st.mcqAnswered === opt.id ? " flashcards-mcq-option--selected" : "";
				var correctClass = st.mcqAnswered && st.mcqAnswered === opt.id && opt.label.toLowerCase() === aText.toLowerCase() ? " flashcards-mcq-option--correct" : (st.mcqAnswered && st.mcqAnswered === opt.id ? " flashcards-mcq-option--wrong" : "");
				optionsHtml += "<label class='flashcards-mcq-option" + selectedClass + correctClass + "'>" +
					"<input type='radio' name='flashcards-mcq-" + recId + "' value='" + opt.id + "'" +
					(st.mcqAnswered === opt.id ? " checked" : "") +
					(st.mcqAnswered ? " disabled" : "") +
					" onchange='dashboardifyFlashCardSelectMcq(\"" + recId + "\", " + opt.id + ", \"" + dashboardifyEscapeHtmlAttr(aText) + "\")'>" +
					"<span class='flashcards-mcq-option-text'>" + dashboardifyEscapeHtmlText(opt.label) + "</span></label>";
			});
			mcqEl.innerHTML = noteHtml + "<div class='flashcards-mcq-options'>" + optionsHtml + "</div>";
		} else {
			mcqEl.innerHTML = noteHtml + "<div class='flashcards-mcq-empty'>No multiple choice options defined.</div>";
		}
		aEl.textContent = "";
		if (revealBtn) revealBtn.style.display = "none";
	} else if (isGuess) {
		if (revealBtn) revealBtn.style.display = "";
		if (!st.revealed) {
			aEl.textContent =
				'Try the answer in your head, then tap "Show answer" to check.';
			aEl.classList.add("flashcards-answer--placeholder");
			dashboardifyApplyFlashCardTextScale(aEl, aEl.textContent);
			if (revealBtn) {
				revealBtn.textContent = "Show answer";
				revealBtn.setAttribute("aria-expanded", "false");
			}
		} else {
			aEl.textContent = noteText ? noteText + "<br><br>" + aText : aText;
			aEl.classList.remove("flashcards-answer--placeholder");
			dashboardifyApplyFlashCardTextScale(aEl, aText);
			if (revealBtn) {
				revealBtn.style.display = "none";
				revealBtn.setAttribute("aria-expanded", "true");
			}
		}
		if (mcqEl) mcqEl.innerHTML = "";
	} else {
		aEl.textContent = noteText ? noteText + "<br><br>" + aText : aText;
		aEl.classList.remove("flashcards-answer--placeholder");
		dashboardifyApplyFlashCardTextScale(aEl, aText);
		if (revealBtn) revealBtn.style.display = "none";
		if (mcqEl) mcqEl.innerHTML = "";
	}
	if (st.model.autoAdvanceEnabled && st.order.length > 1) {
		var delay = Number(st.model.autoAdvanceMs);
		var ms = Number.isFinite(delay) && delay > 0 ? delay : 5000;
		st.timerId = setTimeout(function () {
			dashboardifyFlashCardNext(recId);
		}, ms);
	}
}

function dashboardifyFlashCardNext(recId) {
	var st = window.DashboardifyFlashCardRuntime && window.DashboardifyFlashCardRuntime[String(recId)];
	if (!st || !st.order.length) return;
	st.revealed = false;
	st.mcqAnswered = false;
	st.index = (st.index + 1) % st.order.length;
	dashboardifyRenderFlashCardState(recId);
}

function dashboardifyFlashCardPrev(recId) {
	var st = window.DashboardifyFlashCardRuntime && window.DashboardifyFlashCardRuntime[String(recId)];
	if (!st || !st.order.length) return;
	st.revealed = false;
	st.mcqAnswered = false;
	st.index = (st.index - 1 + st.order.length) % st.order.length;
	dashboardifyRenderFlashCardState(recId);
}

function dashboardifyFlashCardRevealAnswer(recId) {
	var st = window.DashboardifyFlashCardRuntime && window.DashboardifyFlashCardRuntime[String(recId)];
	if (!st || st.model.displayStyle !== "guess") return;
	st.revealed = true;
	dashboardifyRenderFlashCardState(recId);
}

function dashboardifyFlashCardSelectMcq(recId, selectedId, correctAnswer) {
	var st = window.DashboardifyFlashCardRuntime && window.DashboardifyFlashCardRuntime[String(recId)];
	if (!st || st.mcqAnswered) return;
	st.mcqAnswered = selectedId;
	dashboardifyRenderFlashCardState(recId);
}

window.dashboardifyFlashCardSelectMcq = dashboardifyFlashCardSelectMcq;

window.dashboardifyFlashCardNext = dashboardifyFlashCardNext;
window.dashboardifyFlashCardPrev = dashboardifyFlashCardPrev;
window.dashboardifyFlashCardRevealAnswer = dashboardifyFlashCardRevealAnswer;

function escapeHtmlForTextarea(s) {
	return String(s)
		.replace(/&/g, "&amp;")
		.replace(/</g, "&lt;")
		.replace(/>/g, "&gt;");
}

function editExistingNote(RecID) {
	var adapter = window.DashboardifyDataAdapter;
	var y = document.getElementById(RecID + "_note");
	if (!y) return;
	if (!adapter || typeof adapter.patchWidget !== "function") {
		console.warn("Notes editing requires the cloud app (sign in on cloud-login.html).");
		return;
	}
	var w = (adapter.dataCache && adapter.dataCache.widgets) || [];
	var row = w.find(function (x) {
		return String(x.RecID) === String(RecID);
	});
	var NotesData = row ? row.Notes || "" : "";
	y.setAttribute("data-original-html", y.innerHTML);
	y.innerHTML =
		"<form style='height: 100%; width:100%;' method='POST' id='notes_submission' onsubmit='return false;'>" +
		"<textarea name='Notes' style='width: 95%; height: 90%;'>" +
		escapeHtmlForTextarea(NotesData) +
		"</textarea><br />" +
		"<button type='submit'>Save</button> " +
		"<button type='button' id='cancelButton'>Cancel</button>" +
		"</form>";
	document.getElementById("cancelButton").addEventListener("click", function () {
		y.innerHTML = y.getAttribute("data-original-html");
	});
	var form = document.getElementById("notes_submission");
	form.onsubmit = async function (ev) {
		ev.preventDefault();
		if (
			typeof window.DashboardifyEnsureCloudWriteAccess === "function" &&
			!(await window.DashboardifyEnsureCloudWriteAccess())
		) {
			return;
		}
		var ta = form.querySelector('textarea[name="Notes"]');
		var text = ta ? ta.value : "";
		void adapter.patchWidget(RecID, { Notes: text }).then(function () {
			var notesvalue = (text || "").replace(/''/g, "'");
			y.innerHTML =
				"<div style='height: 100%;' id='" +
				RecID +
				"_note'><p class='note' style='padding-left: 15px; padding-right: 15px;'><md-block>" +
				notesvalue +
				"</md-block></p></div>";
		});
	};
}


// Listen for shift-click on any notes widgets.
document.addEventListener('DOMContentLoaded', function() {
  document.addEventListener('click', function(event) {
    if (event.shiftKey) {
      let targetElement = event.target.closest('.notes');
      if (targetElement) {
        let clickedElementId = targetElement.id;
        editExistingNote(clickedElementId);
      }
    }
  });
});


function getrooturlpath() {
  const url9 = new URL(window.location.href);
  const pathWithoutFile = url9.origin + url9.pathname.substring(0, url9.pathname.lastIndexOf('/') + 1);
  const apiURL = pathWithoutFile
  return apiURL;
}

async function deleteWidget(recID) {
  if (window.DashboardifyDataAdapter && typeof window.DashboardifyDataAdapter.deleteWidget === "function") {
	if (
	  typeof window.DashboardifyEnsureCloudWriteAccess === "function" &&
	  !(await window.DashboardifyEnsureCloudWriteAccess())
	) {
	  return;
	}
	const x2 = document.getElementById(recID);
	if (x2) {
	  x2.remove();
	}
	window.DashboardifyDataAdapter.deleteWidget(recID).catch((error) => {
	  console.error('Cloud delete error:', error);
	});
	return;
  }
  console.warn("Dashboardify: sign in via the cloud app to delete widgets.");
}

// Make Dialogs Draggable
$( function() {
    $( ".white_content" ).draggable({ containment: "window", scroll: false });
  } );


  $(document).ready(function() {
  
  	
	// Add event handler to the button with id "editmode"
	$("#editmode").click(function() {
	  // Call the toggleEditMode function
	  toggleEditMode();
	});

	main();
});
	// Function to toggle edit mode
var isEditMode = false; // This flag will help track the state
function toggleEditMode() {
    isEditMode = !isEditMode; // Toggle the state

    if (isEditMode) {
        // Enable draggable functionality
        $(".widget").draggable({
            stop: async function(event, ui) {
                // Get the current position of the widget element
                var x = ui.position.left;
                var y = ui.position.top;

                // Get the ID of the moved element
                var widgetId = $(this).attr("id");
                var adapter = window.DashboardifyDataAdapter;
                if (adapter && typeof adapter.patchWidget === "function") {
                    if (
                      typeof window.DashboardifyEnsureCloudWriteAccess === "function" &&
                      !(await window.DashboardifyEnsureCloudWriteAccess())
                    ) {
                      return;
                    }
                    void adapter
                        .patchWidget(widgetId, {
                            PositionX: String(Math.round(x)),
                            PositionY: String(Math.round(y))
                        })
                        .catch(function (err) {
                            console.error("Save widget position failed:", err);
                        });
                    return;
                }
                console.warn("Move widget: sign in on the cloud app to save position.");
            }
        });
    } else {
        // Disable draggable functionality
        $(".widget").draggable("destroy");
    }
}



  $(document).ready(function() {
	// Add event handler to the button with id "resizewidgets"
	$("#resizewidgets").click(function() {
	  console.log("resize mode active");
	  toggleresize();
	});
  
	// Function to toggle edit mode
	function toggleresize() {
		$(".resize").each(function() {
		  var $this = $(this);
		  var widget = $this[0];
		  var hasResizeStyles = $this.data("resize-enabled");
	  
		  if (hasResizeStyles) {
			// Remove resizing styles and functionality
			$this.css({
			  'resize': '',
			  'overflow': ''
			});
	  
			// Stop observing the widget
			if (widget._mutationObserver) {
			  widget._mutationObserver.disconnect();
			  widget._mutationObserver = null;
			}
	  
			// Remove the flag indicating resizing is enabled
			$this.removeData("resize-enabled");
		  } else {
			// Add resizing styles and functionality
			$this.css({
			  'resize': 'both',
			  'overflow': 'auto'
			});
	  
			// Create a new MutationObserver
			var observer = new MutationObserver(function(mutationsList) {
			  for (var mutation of mutationsList) {
				if (
				  mutation.type === "attributes" &&
				  (mutation.attributeName === "style" || mutation.attributeName === "class")
				) {
				  // Clear any previously set timeout
				  clearTimeout(widget.resizeTimeout);
	  
				  // Set a new timeout to send the API request after 500ms of inactivity
				  widget.resizeTimeout = setTimeout(async function() {
					// Get the width and height of the resized widget
					const width = widget.offsetWidth;
					const height = widget.offsetHeight;
	  
					// Get the ID of the element being resized
					const id = widget.id;
					const adapter = window.DashboardifyDataAdapter;
					if (adapter && typeof adapter.patchWidget === "function") {
					  if (
						typeof window.DashboardifyEnsureCloudWriteAccess === "function" &&
						!(await window.DashboardifyEnsureCloudWriteAccess())
					  ) {
						return;
					  }
					  void adapter
						.patchWidget(id, {
						  SizeX: String(Math.round(width)),
						  SizeY: String(Math.round(height))
						})
						.catch(function (err) {
						  console.error("Save widget size failed:", err);
						});
					  return;
					}
					console.warn("Resize widget: sign in on the cloud app to save size.");
				  }, 500); // Adjust the delay as needed
				}
			  }
			});
	  
			// Start observing the widget for attribute changes
			observer.observe(widget, { attributes: true });
	  
			// Store the observer in the widget element
			widget._mutationObserver = observer;
	  
			// Set a flag indicating resizing is enabled
			$this.data("resize-enabled", true);
		  }
		});
	  }	  
  });

function openeditdialog() {
	if (window.location.href.indexOf("EditRecID") != -1) {
		document.getElementById('NewWidgetDialog2').style.display='block';
	}
}

function renderNewWidgetOptionsByDropdown() {

	var dropdownvalue = document.getElementById("ddlWidgetType").value;

	switch(dropdownvalue) {
		case "SQLServerScalarQuery":
			document.getElementById("SQL").style.display = 'block';
		  break;
		
		case "SQLiteResultsList":
			document.getElementById("SQL").style.display = 'block';
			break;
		case "SQLite Chart (PHPGD)":
			document.getElementById("SQL").style.display = 'block';
			break;
		}


}
function toggleDisplay(elementId) {
	var element = document.getElementById(elementId);
	var computedStyle = window.getComputedStyle(element);
	var displayValue = computedStyle.getPropertyValue("display");

if (displayValue === "none") {
  element.style.display = "block";
} else {
	element.style.display = "none";
}
	
}
  function toggleDisplayByClass(className) {
	var elements = document.getElementsByClassName(className);
	for (var i = 0; i < elements.length; i++) {
	  var element = elements[i];
	  if (element.style.display === "none") {
		element.style.display = "initial";
	  } else {
		element.style.display = "none";
	  }
	}
}

function opencollapsediframe(recid) {
	var root = document.getElementById(recid);
	if (!root) return;
	var panel = root.querySelector(".collapse-iframe-panel");
	if (panel) panel.style.display = "block";
	var iframeEl = root.querySelector("iframe");
	if (!iframeEl) return;
	var src2 = iframeEl.getAttribute("src2");
	if (src2) iframeEl.setAttribute("src", src2);
}

		function loadselecteddashboard() {
			var e = document.getElementById("ddlSelectedDashboard");
			if (!e) return;
			var value = e.value;
			var cloudSel = document.getElementById("dashboardSelect");
			if (cloudSel) {
				cloudSel.value = value;
				cloudSel.dispatchEvent(new Event("change", { bubbles: true }));
				return;
			}
			console.warn("Dashboardify: use the Dashboard dropdown in the cloud app.");
		}
		
		var canvas = null,

		    ctx = null,

		    rect = {},

		    drag = false;

		function init() {

			if(canvas || ctx) return;

    		canvas = document.createElement('canvas');

    		canvas.width = document.body.clientWidth;

    		canvas.height = document.body.clientHeight;

    		canvas.style.position = 'absolute';

    		canvas.style.top = '0px';

    		canvas.style.left = '0px';

    		canvas.style.cursor = 'crosshair';

    		// canvas.style.background = "#f2f2f2";

    		canvas.style.zIndex = 9999;

    		document.body.appendChild(canvas);

    		ctx = canvas.getContext('2d');

		   	document.getElementById('txtpositionx2').value = 0;

		    document.getElementById('txtpositiony2').value = 0;

		    document.getElementById('txtsizeX2').value = 0;

		    document.getElementById('txtsizeY2').value = 0;

			canvas.addEventListener('mousedown', mouseDown, false);

			canvas.addEventListener('mouseup', mouseUp, false);

			canvas.addEventListener('mousemove', mouseMove, false);

		}

		function mouseDown(e) {

		  rect.startX = e.pageX - this.offsetLeft;

		  rect.startY = e.pageY - this.offsetTop;

		  drag = true;

		}

		function mouseUp(e) {

			drag = false;

			rect.endX = e.pageX - this.offsetLeft;

			rect.endY = e.pageY - this.offsetTop;

			if(rect.endX < rect.startX || rect.endY < rect.startY) {

			   	document.getElementById('txtpositionx2').value = rect.endX;

			    document.getElementById('txtpositiony2').value = rect.endY;
			    

			}

		    ctx.clearRect(0,0,canvas.width,canvas.height);

		    destroy_canvas();
		    UpdateSizeCalcs();
		}

		function mouseMove(e) {

		  if (drag) {

		    rect.w = (e.pageX - this.offsetLeft) - rect.startX;

		    rect.h = (e.pageY - this.offsetTop) - rect.startY ;

		    ctx.clearRect(0,0,canvas.width,canvas.height);

		    draw();

		   	document.getElementById('txtpositionx2').value = rect.startX;

		    document.getElementById('txtpositiony2').value = rect.startY;

		    document.getElementById('txtsizeX2').value = Math.abs(rect.w);

		    document.getElementById('txtsizeY2').value = Math.abs(rect.h);
		    //UpdateSizeCalcs();

		  }

		}

		function draw() {

		    ctx.setLineDash([6]);

			ctx.strokeRect(rect.startX, rect.startY, rect.w, rect.h);

		}

		function destroy_canvas(){

			canvas.removeEventListener('mousedown', mouseDown);

			canvas.removeEventListener('mouseup', mouseUp);

			canvas.removeEventListener('mousemove', mouseMove);

			document.body.removeChild(canvas);

			canvas = null;

			ctx = null;

			rect = {};
		}
		
			  
// Need, at the beginning of page load, to identify list of widget types.
// Need to check these to see if we have them in localstorage.
// If we don't, then we need to query the server for them.
// Need some security around this - 
// When querying the server, it should check which widget types to send to the user.
// There's some that shouldn't go to the user if they're not an admin
// Regardless, should check when they submit a new widget to their dashboard,
// That it's a widget that they have the right to submit, to prevent abuse & request forgery



function drawNewWidgetBasedOnType() {
	// THIS IS FOR THE DROPDOWN ON THE NEW WIDGET DIALOG / FORM
	
	// Need to get value of currently selected widget type (get element by id, check value)	
	
	var ddl = document.getElementById('ddlWidgetType2');
	var ddlValue = ddl.value;
	var SizeAndCSSClassMarkup = "<hr class=\"dify-form-divider\" /><p class=\"new-widget-sizer-hint\">Place on the canvas: click <strong>Set position &amp; size</strong>, then click and drag to draw the widget box.</p><button type=\"button\" class=\"menubar new-widget-sizer-btn\" onclick=\"Experimental_New_Widget_Form_Sizer_init()\">Set position &amp; size</button><br /><label id='sizelabel'></label><div class=\"new-widget-hidden-fields\"><label>PositionX: </label><input id=\"txtpositionx2\" value=\"\" autocomplete=\"off\" name=\"PositionX\"><br /><label>PositionY: </label><input id=\"txtpositiony2\" value=\"\" autocomplete=\"off\" name=\"PositionY\"><br /><label>SizeX: </label><input id=\"txtsizeX2\" value=\"\" autocomplete=\"off\" name=\"SizeX\"><br /><label>SizeY: </label><input onchange='UpdateSizeCalcs();' id=\"txtsizeY2\" value=\"\" autocomplete=\"off\" name=\"SizeY\"></div><label class=\"new-widget-field-label\" for=\"txtCSSClass\">CSS class</label><input id=\"txtCSSClass\" name=\"CSSClass\"><br />";
	
	switch (ddlValue) {
		
	case "Bookmark":
		//URL and Display Text needed,
		//As well as position / size elements
		var x = "<hr><span id='widgetURL'><label>Widget URL: </label><input ID='txtWidgetURL' name='URL'></input><br /></span><label>Display Text: </label><input ID=\"txtWidgetDisplayText\" name=\"DisplayText\"></input><br />";
		//Fill content to the dialog
		document.getElementById('NewWidget_Form').innerHTML = SizeAndCSSClassMarkup + x;
		break;
		
case "Countdown":
		var x = '<hr><label>Countdown Title: </label><input id="txtWidgetDisplayText" name="DisplayText"><br />';
		var y = '<input type="date" id="datepicker" name="Notes"> <br/>';
		document.getElementById('NewWidget_Form').innerHTML = SizeAndCSSClassMarkup + x + y;
		break;

	case "Clock":
		var clockFields = 
			'<hr><label>Clock Title: </label><input id="txtWidgetDisplayText" name="DisplayText"><br />' +
			'<label>Timezone: </label>' +
			'<select id="ddlClockTimezone" name="Notes">' +
			'<option value="America/New_York">Eastern Time (ET)</option>' +
			'<option value="America/Chicago">Central Time (CT)</option>' +
			'<option value="America/Denver">Mountain Time (MT)</option>' +
			'<option value="America/Los_Angeles">Pacific Time (PT)</option>' +
			'<option value="America/Anchorage">Alaska Time (AKT)</option>' +
			'<option value="Pacific/Honolulu">Hawaii Time (HT)</option>' +
			'<option value="Europe/London">London (GMT/BST)</option>' +
			'<option value="Europe/Paris">Paris (CET/CEST)</option>' +
			'<option value="Europe/Berlin">Berlin (CET/CEST)</option>' +
			'<option value="Europe/Moscow">Moscow (MSK)</option>' +
			'<option value="Asia/Dubai">Dubai (GST)</option>' +
			'<option value="Asia/Kolkata">India (IST)</option>' +
			'<option value="Asia/Singapore">Singapore (SGT)</option>' +
			'<option value="Asia/Tokyo">Tokyo (JST)</option>' +
			'<option value="Asia/Shanghai">Shanghai (CST)</option>' +
			'<option value="Australia/Sydney">Sydney (AEST/AEDT)</option>' +
			'<option value="Pacific/Auckland">Auckland (NZST/NZDT)</option>' +
			'</select><br />';
		document.getElementById('NewWidget_Form').innerHTML = SizeAndCSSClassMarkup + clockFields;
		break;
		
	case "CountUp_Hours":
		var x = "<hr></span><label>Title: </label><input ID=\"txtWidgetDisplayText\" name=\"DisplayText\"></input><br />";
		var y = "<input type=\"datetime-local\" id=\"datepicker\" name=\"Notes\"> <br/>";
		//Fill content to the dialog
		document.getElementById('NewWidget_Form').innerHTML = SizeAndCSSClassMarkup + x + y;
		
		break;
		
	case "IFrame":
		//URL and Display Text needed,
		//As well as position / size elements
		var x = "<hr><span id='widgetURL'><label>IFrame URL: </label><input ID='txtWidgetURL' name='URL'></input><br /></span><label>Header / Display Text: </label><input ID=\"txtWidgetDisplayText\" name=\"DisplayText\"></input><br />";
		//Fill content to the dialog
		document.getElementById('NewWidget_Form').innerHTML = SizeAndCSSClassMarkup + x;
		break;
	case "Collapseable IFrame":
		//URL and Display Text needed,
		//As well as position / size elements
		var x = "<hr><span id='widgetURL'><label>IFrame URL: </label><input ID='txtWidgetURL' name='URL'></input><br /></span><label>Header / Display Text: </label><input ID=\"txtWidgetDisplayText\" name=\"DisplayText\"></input><br />";
		//Fill content to the dialog
		document.getElementById('NewWidget_Form').innerHTML = SizeAndCSSClassMarkup + x;
		break;
	case "Notes":
		//Position, Sizine, Notes fields needed
		//As well as position / size elements
		var x = "<div class=\"dify-field\"><label class=\"new-widget-field-label\" for=\"txtNotes\">Notes</label><textarea id=\"txtNotes\" rows=\"7\" name=\"Notes\"></textarea></div>";
		//Fill content to the dialog
		document.getElementById('NewWidget_Form').innerHTML = SizeAndCSSClassMarkup + x;
		break;

	case "HTMLEmbed":
		//Position, Sizine, HTML fields needed
		//As well as position / size elements
		var x = "<div class=\"dify-field\"><label class=\"new-widget-field-label\" for=\"txtNotes\">HTML</label><textarea id=\"txtNotes\" rows=\"7\" name=\"Notes\"></textarea></div>";
		//Fill content to the dialog
		document.getElementById('NewWidget_Form').innerHTML = SizeAndCSSClassMarkup + x;
		break;
	case "Image":
		var imgFields =
			"<hr><span id='widgetURL'><label>Image URL: </label><input id='txtWidgetURL' name='URL' type='text' placeholder='https://…'></input><br /></span>" +
			"<label>Sizing: </label>" +
			"<select id='ddlImageObjectFit' name='Notes'>" +
			"<option value='contain'>Fit — show whole image (contain)</option>" +
			"<option value='cover'>Fill — cover box, may crop (cover)</option>" +
			"<option value='fill'>Stretch — distort to fill (fill)</option>" +
			"<option value='none'>Original pixels (none)</option>" +
			"<option value='scale-down'>Smaller of none or contain (scale-down)</option>" +
    "</select><br />";
			"</select><br />";
		document.getElementById("NewWidget_Form").innerHTML =
			SizeAndCSSClassMarkup + imgFields;
		break;
case "Flash Cards":
		var flashFields =
			"<hr><label>Widget Title: </label><input id='txtWidgetDisplayText' name='DisplayText'></input><br />" +
			"<label>Style: </label><select id='flashDisplayStyle'><option value='full'>Full — show question and answer</option><option value='guess'>Guess — hide answer until you tap "Show answer"</option><option value='multiplechoice'>Multiple Choice — select from answer options</option></select><br />" +
			"<label>Sort Method: </label><select id='flashSortMethod'><option value='random' selected>Random</option><option value='forward'>Forward</option><option value='reverse'>Reverse</option></select><br />" +
			"<label><input id='flashAutoAdvanceEnabled' type='checkbox' /> Auto advance cards</label><br />" +
			"<label>Auto advance delay (ms): </label><input id='flashAutoAdvanceMs' type='number' min='50' step='50' value='5000'></input><br />" +
			"<label id='flashCardsCountHint' class='cloud-dialog-muted'>0 questions saved.</label>" +
			"<textarea id='txtFlashCardsData' style='display:none;'>[]</textarea>";
		document.getElementById("NewWidget_Form").innerHTML = SizeAndCSSClassMarkup + flashFields;
		break;
	case "SQLServerScalarQuery":
		//Position, Sizine, HTML fields needed
		//As well as position / size elements
		var x1 = "<hr>SQL Server Address<input ID=\"SQLServerAddressName\" name=\"SQLServerAddressName\"></input><br />";
		var x2 = "SQL DBName<input ID=\"SQLDBName\" name=\"SQLDBName\"></input><br />";
		var x3 = "SQLServer Username: (Empty for windows / SQLite auth) <input ID=\"sqluser\" name=\"sqluser\"></input><br />";
		var x4 = "SQLServer PW: <input ID=\"sqlpass\" name=\"sqlpass\"></input><br />";
		var x5 = "SQL Query: <input ID=\"sqlquery\" name=\"sqlquery\"></input><br />";
		var sqlcontent = x1 + x2 + x3 + x4 + x5;
		//var x = "<br />HTML:<br /> <textarea ID=\"txtNotes\" rows=\"4\" cols=\"50\" name=\"Notes\"></textarea><br />";
		//Fill content to the dialog
		document.getElementById('NewWidget_Form').innerHTML = SizeAndCSSClassMarkup + sqlcontent;
		break;
		
	case "SQLServerResultsList":
		//Position, Sizine, HTML fields needed
		//As well as position / size elements
		var x1 = "<hr>SQL Server Address<input ID=\"SQLServerAddressName\" name=\"SQLServerAddressName\"></input><br />";
		var x2 = "SQL DBName<input ID=\"SQLDBName\" name=\"SQLDBName\"></input><br />";
		var x3 = "SQLServer Username: (Empty for windows / SQLite auth) <input ID=\"sqluser\" name=\"sqluser\"></input><br />";
		var x4 = "SQLServer PW: <input ID=\"sqlpass\" name=\"sqlpass\"></input><br />";
		var x5 = "SQL Query: <input ID=\"sqlquery\" name=\"sqlquery\"></input><br />";
		var sqlcontent = x1 + x2 + x3 + x4 + x5;
		//var x = "<br />HTML:<br /> <textarea ID=\"txtNotes\" rows=\"4\" cols=\"50\" name=\"Notes\"></textarea><br />";
		//Fill content to the dialog
		document.getElementById('NewWidget_Form').innerHTML = SizeAndCSSClassMarkup + sqlcontent;
		break;		
	case "SQLiteResultsList":
		//Position, Sizine, HTML fields needed
		//As well as position / size elements
		var x1 = "<hr>SQLite File Name / Path: <input ID=\"SQLDBName\" name=\"SQLDBName\"></input><br />";
		var x2 = "SQL Query: <input ID=\"sqlquery\" name=\"sqlquery\"></input><br />";
		var sqlcontent = x1 + x2;
		//var x = "<br />HTML:<br /> <textarea ID=\"txtNotes\" rows=\"4\" cols=\"50\" name=\"Notes\"></textarea><br />";
		//Fill content to the dialog
		document.getElementById('NewWidget_Form').innerHTML = SizeAndCSSClassMarkup + sqlcontent;
		break;
	case "SQLite Chart (PHPGD)":
		//Position, Sizine, HTML fields needed
		//As well as position / size elements
		var x1 = "<hr>SQLite File Name / Path: <input ID=\"SQLDBName\" name=\"SQLDBName\"></input><br />";
		var x2 = "SQL Query: <input ID=\"sqlquery\" name=\"sqlquery\"></input><br />";
		var x3 = "<br /> Note: You must <b>explicitly</b> specify column aliases as 'x' and 'y' for which columns should be treated as the x and y axis.";
		var sqlcontent = x1 + x2 + x3;
		//var x = "<br />HTML:<br /> <textarea ID=\"txtNotes\" rows=\"4\" cols=\"50\" name=\"Notes\"></textarea><br />";
		//Fill content to the dialog
		document.getElementById('NewWidget_Form').innerHTML = SizeAndCSSClassMarkup + sqlcontent;
		break;	
		

	default:
		var y = "";

		document.getElementById('NewWidget_Form').innerHTML = SizeAndCSSClassMarkup + y;
		break;
		
	}
	// Need a div box to serve as the container for the HTML form
	
	// Clear out the existing items in the div container
	
	// Switch case based on the selected widget type, draw up the HTML form content
	
	// Set div container's inner HTML to HTML form

	
	
}

function UpdateSizeCalcs() {
	// Collect ID's
	var positionx = document.getElementById('txtpositionx2');
	var positiony = document.getElementById('txtpositiony2');
	var sizex = document.getElementById('txtsizeX2');
	var sizey = document.getElementById('txtsizeY2');
	
	var outputdata = "<br /> X: " + positionx.value + ", Y: " + positiony.value + ", Size X: " + sizex.value + ", SizeY: " + sizey.value + "<br />";
	//alert(outputdata);
	document.getElementById('sizelabel').innerHTML = outputdata;
}

function Experimental_New_Widget_Form_Sizer_init() {

			if(canvas || ctx) return;

    		canvas = document.createElement('canvas');

    		canvas.width = document.body.clientWidth;

    		canvas.height = document.body.clientHeight;

    		canvas.style.position = 'absolute';

    		canvas.style.top = '0px';

    		canvas.style.left = '0px';

    		canvas.style.cursor = 'crosshair';

    		// canvas.style.background = "#f2f2f2";

    		canvas.style.zIndex = 9999;

    		document.body.appendChild(canvas);

    		ctx = canvas.getContext('2d');

		   	document.getElementById('txtpositionx2').value = 0;

		    document.getElementById('txtpositiony2').value = 0;

		    document.getElementById('txtsizeX2').value = 0;

		    document.getElementById('txtsizeY2').value = 0;

			canvas.addEventListener('mousedown', mouseDown, false);

			canvas.addEventListener('mouseup', mouseUp, false);

			canvas.addEventListener('mousemove', mouseMove, false);

		}

function dashboardifyGetWidgetByRecId(recId) {
	var adapter = window.DashboardifyDataAdapter;
	var widgets = (adapter && adapter.dataCache && adapter.dataCache.widgets) || [];
	return widgets.find(function (w) { return String(w.RecID) === String(recId); }) || null;
}

function dashboardifyFlashCardsCsvEscape(v) {
	var s = String(v == null ? "" : v);
	if (s.indexOf('"') !== -1) s = s.replace(/"/g, '""');
	if (/[",\n\r]/.test(s)) return '"' + s + '"';
	return s;
}

function dashboardifyBuildFlashCardsCsv(cards) {
	var hasNote = false;
	var hasMcq = false;
	(cards || []).forEach(function (c) {
		if (c.note) hasNote = true;
		if (c.mcq1 || c.mcq2 || c.mcq3 || c.mcq4) hasMcq = true;
	});
	var headers = ["question", "answer"];
	if (hasNote) headers.push("note");
	if (hasMcq) {
		headers.push("mcq1", "mcq2", "mcq3", "mcq4");
	}
	var rows = [headers.join(",")];
	(cards || []).forEach(function (c) {
		var fields = [dashboardifyFlashCardsCsvEscape(c.q || ""), dashboardifyFlashCardsCsvEscape(c.a || "")];
		if (hasNote) fields.push(dashboardifyFlashCardsCsvEscape(c.note || ""));
		if (hasMcq) {
			fields.push(dashboardifyFlashCardsCsvEscape(c.mcq1 || ""));
			fields.push(dashboardifyFlashCardsCsvEscape(c.mcq2 || ""));
			fields.push(dashboardifyFlashCardsCsvEscape(c.mcq3 || ""));
			fields.push(dashboardifyFlashCardsCsvEscape(c.mcq4 || ""));
		}
		rows.push(fields.join(","));
	});
	return rows.join("\r\n");
}

function dashboardifyParseFlashCardsCsv(text) {
	var rows = [];
	var src = String(text || "");
	var i = 0, cur = "", row = [], inQuotes = false;
	while (i < src.length) {
		var ch = src[i];
		if (inQuotes) {
			if (ch === '"') {
				if (src[i + 1] === '"') { cur += '"'; i += 2; continue; }
				inQuotes = false;
				i++;
				continue;
			}
			cur += ch;
			i++;
			continue;
		}
		if (ch === '"') { inQuotes = true; i++; continue; }
		if (ch === ",") { row.push(cur); cur = ""; i++; continue; }
		if (ch === "\n") { row.push(cur); rows.push(row); row = []; cur = ""; i++; continue; }
		if (ch === "\r") { i++; continue; }
		cur += ch;
		i++;
	}
	row.push(cur);
	rows.push(row);
	var start = 0;
	var header0 = rows.length ? String(rows[0][0] || "").trim().toLowerCase() : "";
	var header1 = rows.length ? String(rows[0][1] || "").trim().toLowerCase() : "";
	if (header0 === "question" && header1 === "answer") {
		start = 1;
	}
	var hasNote = header0 === "note" || (rows.length && rows[0].indexOf("note") >= 0);
	var hasMcq = header0 === "mcq1" || (rows.length && rows[0].some(function (h) { return /^mcq\d+$/.test(String(h).trim()); }));
	var cards = [];
	for (var r = start; r < rows.length; r++) {
		var q = String((rows[r] && rows[r][0]) || "").trim();
		var a = String((rows[r] && rows[r][1]) || "").trim();
		var note = hasNote ? String((rows[r] && rows[r][2]) || "").trim() : "";
		var mcq1 = hasMcq ? String((rows[r] && rows[r][2 + (hasNote ? 1 : 0)]) || "").trim() : "";
		var mcq2 = hasMcq ? String((rows[r] && rows[r][3 + (hasNote ? 1 : 0)]) || "").trim() : "";
		var mcq3 = hasMcq ? String((rows[r] && rows[r][4 + (hasNote ? 1 : 0)]) || "").trim() : "";
		var mcq4 = hasMcq ? String((rows[r] && rows[r][5 + (hasNote ? 1 : 0)]) || "").trim() : "";
		if (!q && !a && !note && !mcq1 && !mcq2 && !mcq3 && !mcq4) continue;
		cards.push({ q: q, a: a, note: note, mcq1: mcq1, mcq2: mcq2, mcq3: mcq3, mcq4: mcq4 });
	}
	return cards;
}

function dashboardifyCloseFlashCardsManager() {
	var d = document.getElementById("FlashCardsManagerDialog");
	if (d) d.style.display = "none";
}

function dashboardifyCloseFlashCardsQuickAdd() {
	var d = document.getElementById("FlashCardsQuickAddDialog");
	if (d) d.style.display = "none";
}

window.dashboardifyCloseFlashCardsManager = dashboardifyCloseFlashCardsManager;
window.dashboardifyCloseFlashCardsQuickAdd = dashboardifyCloseFlashCardsQuickAdd;

function dashboardifyRenderFlashCardsManagerList() {
	var state = window.DashboardifyFlashCardsEditorState;
	var list = document.getElementById("flashCardsManagerList");
	if (!state || !list) return;
	list.innerHTML = "";
	(state.model.cards || []).forEach(function (c, idx) {
		var option = document.createElement("option");
		var qShort = String(c.q || "");
		if (qShort.length > 70) qShort = qShort.slice(0, 67) + "...";
		option.value = String(idx);
		option.textContent = (idx + 1) + ". " + qShort;
		list.appendChild(option);
	});
}

function dashboardifyLoadFlashCardsManagerSelection() {
	var state = window.DashboardifyFlashCardsEditorState;
	var list = document.getElementById("flashCardsManagerList");
	var q = document.getElementById("flashCardManagerQuestion");
	var a = document.getElementById("flashCardManagerAnswer");
	var note = document.getElementById("flashCardManagerNote");
	var mcq1 = document.getElementById("flashCardManagerMcq1");
	var mcq2 = document.getElementById("flashCardManagerMcq2");
	var mcq3 = document.getElementById("flashCardManagerMcq3");
	var mcq4 = document.getElementById("flashCardManagerMcq4");
	if (!state || !list || !q || !a) return;
	var idx = Number(list.value);
	if (!Number.isFinite(idx) || idx < 0) {
		q.value = "";
		a.value = "";
		if (note) note.value = "";
		if (mcq1) mcq1.value = "";
		if (mcq2) mcq2.value = "";
		if (mcq3) mcq3.value = "";
		if (mcq4) mcq4.value = "";
		return;
	}
	var card = state.model.cards[idx] || { q: "", a: "" };
	q.value = card.q || "";
	a.value = card.a || "";
	if (note) note.value = card.note || "";
	if (mcq1) mcq1.value = card.mcq1 || "";
	if (mcq2) mcq2.value = card.mcq2 || "";
	if (mcq3) mcq3.value = card.mcq3 || "";
	if (mcq4) mcq4.value = card.mcq4 || "";
}

function dashboardifyOpenFlashCardsManager(recId) {
	var widget = dashboardifyGetWidgetByRecId(recId);
	if (!widget) return;
	window.DashboardifyFlashCardsEditorState = {
		recId: String(recId),
		model: dashboardifyParseFlashCardsNotes(widget.Notes),
		importMode: "merge"
	};
	dashboardifyRenderFlashCardsManagerList();
	var list = document.getElementById("flashCardsManagerList");
	if (list && list.options.length) list.selectedIndex = 0;
	dashboardifyLoadFlashCardsManagerSelection();
	var dialog = document.getElementById("FlashCardsManagerDialog");
	if (dialog) dialog.style.display = "block";
}

window.dashboardifyOpenFlashCardsManager = dashboardifyOpenFlashCardsManager;

function dashboardifyOpenFlashCardsQuickAdd(recId) {
	window.DashboardifyFlashCardsQuickAddRecId = String(recId);
	var q = document.getElementById("flashCardQuickAddQuestion");
	var a = document.getElementById("flashCardQuickAddAnswer");
	var note = document.getElementById("flashCardQuickAddNote");
	var mcq1 = document.getElementById("flashCardQuickAddMcq1");
	var mcq2 = document.getElementById("flashCardQuickAddMcq2");
	var mcq3 = document.getElementById("flashCardQuickAddMcq3");
	var mcq4 = document.getElementById("flashCardQuickAddMcq4");
	if (q) q.value = "";
	if (a) a.value = "";
	if (note) note.value = "";
	if (mcq1) mcq1.value = "";
	if (mcq2) mcq2.value = "";
	if (mcq3) mcq3.value = "";
	if (mcq4) mcq4.value = "";
	var dialog = document.getElementById("FlashCardsQuickAddDialog");
	if (dialog) dialog.style.display = "block";
}

window.dashboardifyOpenFlashCardsQuickAdd = dashboardifyOpenFlashCardsQuickAdd;

document.addEventListener("DOMContentLoaded", function () {
	var list = document.getElementById("flashCardsManagerList");
	if (list) list.addEventListener("change", dashboardifyLoadFlashCardsManagerSelection);

	var addBtn = document.getElementById("btnFlashCardsManagerAdd");
	if (addBtn) addBtn.addEventListener("click", function () {
		var state = window.DashboardifyFlashCardsEditorState;
		var q = document.getElementById("flashCardManagerQuestion");
		var a = document.getElementById("flashCardManagerAnswer");
		var note = document.getElementById("flashCardManagerNote");
		var mcq1 = document.getElementById("flashCardManagerMcq1");
		var mcq2 = document.getElementById("flashCardManagerMcq2");
		var mcq3 = document.getElementById("flashCardManagerMcq3");
		var mcq4 = document.getElementById("flashCardManagerMcq4");
		if (!state || !q || !a) return;
		var card = dashboardifyNormalizeFlashCard({
			q: q.value,
			a: a.value,
			note: note ? note.value : "",
			mcq1: mcq1 ? mcq1.value : "",
			mcq2: mcq2 ? mcq2.value : "",
			mcq3: mcq3 ? mcq3.value : "",
			mcq4: mcq4 ? mcq4.value : ""
		});
		if (!card) return;
		state.model.cards.push(card);
		dashboardifyRenderFlashCardsManagerList();
		var listEl = document.getElementById("flashCardsManagerList");
		if (listEl) listEl.value = String(state.model.cards.length - 1);
		q.value = "";
		a.value = "";
		if (note) note.value = "";
		if (mcq1) mcq1.value = "";
		if (mcq2) mcq2.value = "";
		if (mcq3) mcq3.value = "";
		if (mcq4) mcq4.value = "";
		q.focus();
	});

	var updateBtn = document.getElementById("btnFlashCardsManagerUpdate");
	if (updateBtn) updateBtn.addEventListener("click", function () {
		var state = window.DashboardifyFlashCardsEditorState;
		var listEl = document.getElementById("flashCardsManagerList");
		var q = document.getElementById("flashCardManagerQuestion");
		var a = document.getElementById("flashCardManagerAnswer");
		var note = document.getElementById("flashCardManagerNote");
		var mcq1 = document.getElementById("flashCardManagerMcq1");
		var mcq2 = document.getElementById("flashCardManagerMcq2");
		var mcq3 = document.getElementById("flashCardManagerMcq3");
		var mcq4 = document.getElementById("flashCardManagerMcq4");
		if (!state || !listEl || !q || !a) return;
		var idx = Number(listEl.value);
		if (!Number.isFinite(idx) || idx < 0) return;
		var card = dashboardifyNormalizeFlashCard({
			q: q.value,
			a: a.value,
			note: note ? note.value : "",
			mcq1: mcq1 ? mcq1.value : "",
			mcq2: mcq2 ? mcq2.value : "",
			mcq3: mcq3 ? mcq3.value : "",
			mcq4: mcq4 ? mcq4.value : ""
		});
		if (!card) return;
		state.model.cards[idx] = card;
		dashboardifyRenderFlashCardsManagerList();
		listEl.value = String(idx);
	});

	var delBtn = document.getElementById("btnFlashCardsManagerDelete");
	if (delBtn) delBtn.addEventListener("click", function () {
		var state = window.DashboardifyFlashCardsEditorState;
		var listEl = document.getElementById("flashCardsManagerList");
		if (!state || !listEl) return;
		var idx = Number(listEl.value);
		if (!Number.isFinite(idx) || idx < 0) return;
		state.model.cards.splice(idx, 1);
		dashboardifyRenderFlashCardsManagerList();
		dashboardifyLoadFlashCardsManagerSelection();
	});

	var saveBtn = document.getElementById("btnFlashCardsManagerSave");
	if (saveBtn) saveBtn.addEventListener("click", async function () {
		var state = window.DashboardifyFlashCardsEditorState;
		var adapter = window.DashboardifyDataAdapter;
		if (!state || !adapter || typeof adapter.patchWidget !== "function") return;
		if (typeof window.DashboardifyEnsureCloudWriteAccess === "function" && !(await window.DashboardifyEnsureCloudWriteAccess())) return;
		var widget = dashboardifyGetWidgetByRecId(state.recId);
		var current = dashboardifyParseFlashCardsNotes(widget && widget.Notes);
		current.cards = state.model.cards || [];
		await adapter.patchWidget(state.recId, { Notes: dashboardifySerializeFlashCardsModel(current) });
		dashboardifyCloseFlashCardsManager();
		var dashboardId = (widget && widget.DashboardRecID) || "";
		if (dashboardId && typeof getWidgetsForDashboard === "function") {
			await getWidgetsForDashboard(dashboardId);
		}
	});

	var exportBtn = document.getElementById("btnFlashCardsCsvExport");
	if (exportBtn) exportBtn.addEventListener("click", function () {
		var state = window.DashboardifyFlashCardsEditorState;
		if (!state) return;
		var csv = dashboardifyBuildFlashCardsCsv(state.model.cards || []);
		var blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
		var url = URL.createObjectURL(blob);
		var a = document.createElement("a");
		a.href = url;
		a.download = "flash-cards.csv";
		document.body.appendChild(a);
		a.click();
		a.remove();
		URL.revokeObjectURL(url);
	});

	var importInput = document.getElementById("flashCardsCsvImportFile");
	var importMergeBtn = document.getElementById("btnFlashCardsCsvImportMerge");
	var importReplaceBtn = document.getElementById("btnFlashCardsCsvImportReplace");
	if (importMergeBtn) importMergeBtn.addEventListener("click", function () {
		var state = window.DashboardifyFlashCardsEditorState;
		if (!state || !importInput) return;
		state.importMode = "merge";
		importInput.click();
	});
	if (importReplaceBtn) importReplaceBtn.addEventListener("click", function () {
		var state = window.DashboardifyFlashCardsEditorState;
		if (!state || !importInput) return;
		state.importMode = "replace";
		importInput.click();
	});
	if (importInput) importInput.addEventListener("change", async function (ev) {
		var file = ev.target.files && ev.target.files[0];
		ev.target.value = "";
		var state = window.DashboardifyFlashCardsEditorState;
		if (!file || !state) return;
		var text = await file.text();
		var incoming = dashboardifyParseFlashCardsCsv(text);
		if (state.importMode === "replace") {
			state.model.cards = incoming;
		} else {
			state.model.cards = (state.model.cards || []).concat(incoming);
		}
		dashboardifyRenderFlashCardsManagerList();
	});

	var quickSave = document.getElementById("btnFlashCardsQuickAddSave");
	if (quickSave) quickSave.addEventListener("click", async function () {
		var recId = window.DashboardifyFlashCardsQuickAddRecId;
		var adapter = window.DashboardifyDataAdapter;
		if (!recId || !adapter || typeof adapter.patchWidget !== "function") return;
		if (typeof window.DashboardifyEnsureCloudWriteAccess === "function" && !(await window.DashboardifyEnsureCloudWriteAccess())) return;
		var q = document.getElementById("flashCardQuickAddQuestion");
		var a = document.getElementById("flashCardQuickAddAnswer");
		var note = document.getElementById("flashCardQuickAddNote");
		var mcq1 = document.getElementById("flashCardQuickAddMcq1");
		var mcq2 = document.getElementById("flashCardQuickAddMcq2");
		var mcq3 = document.getElementById("flashCardQuickAddMcq3");
		var mcq4 = document.getElementById("flashCardQuickAddMcq4");
		var card = dashboardifyNormalizeFlashCard({
			q: q && q.value,
			a: a && a.value,
			note: note ? note.value : "",
			mcq1: mcq1 ? mcq1.value : "",
			mcq2: mcq2 ? mcq2.value : "",
			mcq3: mcq3 ? mcq3.value : "",
			mcq4: mcq4 ? mcq4.value : ""
		});
		if (!card) return;
		var widget = dashboardifyGetWidgetByRecId(recId);
		if (!widget) return;
		var model = dashboardifyParseFlashCardsNotes(widget.Notes);
		model.cards.push(card);
		await adapter.patchWidget(recId, { Notes: dashboardifySerializeFlashCardsModel(model) });
		dashboardifyCloseFlashCardsQuickAdd();
		if (typeof getWidgetsForDashboard === "function") {
			await getWidgetsForDashboard(widget.DashboardRecID);
		}
	});
});

var DashboardifyWidgetManagerState = { widgets: [], selectedRecId: null };

function openWidgetManager() {
	var dialog = document.getElementById("widgetManagerDialog");
	var list = document.getElementById("widgetManagerList");
	var details = document.getElementById("widgetManagerDetails");
	var noWidgets = document.getElementById("widgetManagerNoWidgets");
	if (!dialog || !list) return;
	DashboardifyWidgetManagerState.widgets = [];
	DashboardifyWidgetManagerState.selectedRecId = null;
	list.innerHTML = "";
	details.style.display = "none";
	noWidgets.style.display = "none";
	var widgets = window.DashboardifyAllWidgets || [];
	if (widgets.length === 0) {
		noWidgets.style.display = "block";
		dialog.style.display = "block";
		return;
	}
	DashboardifyWidgetManagerState.widgets = widgets;
	widgets.forEach(function(w) {
		var opt = document.createElement("option");
		opt.value = w.RecID;
		var displayText = w.BookmarkDisplayText || w.WidgetType || "Widget";
		opt.textContent = displayText + " (#" + w.RecID + ")";
		list.appendChild(opt);
	});
	if (list.options.length > 0) {
		list.selectedIndex = 0;
		loadWidgetManagerDetails();
	}
	dialog.style.display = "block";
}

window.openWidgetManager = openWidgetManager;

function closeWidgetManager() {
	var dialog = document.getElementById("widgetManagerDialog");
	if (dialog) dialog.style.display = "none";
}

window.closeWidgetManager = closeWidgetManager;

function loadWidgetManagerDetails() {
	var list = document.getElementById("widgetManagerList");
	var details = document.getElementById("widgetManagerDetails");
	if (!list || !details) return;
	var recId = list.value;
	if (!recId) {
		details.style.display = "none";
		return;
	}
	DashboardifyWidgetManagerState.selectedRecId = recId;
	var widget = DashboardifyWidgetManagerState.widgets.find(function(w) { return String(w.RecID) === String(recId); });
	if (!widget) {
		details.style.display = "none";
		return;
	}
	document.getElementById("widgetManagerType").textContent = widget.WidgetType || "Unknown";
	document.getElementById("widgetManagerX").value = widget.PositionX || 0;
	document.getElementById("widgetManagerY").value = widget.PositionY || 0;
	document.getElementById("widgetManagerWidth").value = widget.SizeX || 200;
	document.getElementById("widgetManagerHeight").value = widget.SizeY || 150;
	details.style.display = "block";
}

function saveWidgetManagerChanges() {
	var recId = DashboardifyWidgetManagerState.selectedRecId;
	if (!recId) return;
	var x = parseInt(document.getElementById("widgetManagerX").value) || 0;
	var y = parseInt(document.getElementById("widgetManagerY").value) || 0;
	var w = parseInt(document.getElementById("widgetManagerWidth").value) || 200;
	var h = parseInt(document.getElementById("widgetManagerHeight").value) || 150;
	var adapter = window.DashboardifyDataAdapter;
	if (adapter && typeof adapter.patchWidget === "function") {
		adapter.patchWidget(recId, { PositionX: x, PositionY: y, SizeX: w, SizeY: h }).then(function() {
			var idx = DashboardifyWidgetManagerState.widgets.findIndex(function(wi) { return String(wi.RecID) === String(recId); });
			if (idx >= 0) {
				DashboardifyWidgetManagerState.widgets[idx].PositionX = x;
				DashboardifyWidgetManagerState.widgets[idx].PositionY = y;
				DashboardifyWidgetManagerState.widgets[idx].SizeX = w;
				DashboardifyWidgetManagerState.widgets[idx].SizeY = h;
			}
			var currentDashboardId = document.getElementById("ddlSelectedDashboard") ? document.getElementById("ddlSelectedDashboard").value : null;
			if (currentDashboardId && typeof getWidgetsForDashboard === "function") {
				getWidgetsForDashboard(currentDashboardId);
			}
		}).catch(function(err) {
			alert("Failed to save: " + err.message);
		});
	} else {
		var xhr = new XMLHttpRequest();
		xhr.open("POST", "actions/update_widget_position.php", true);
		xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		xhr.onreadystatechange = function() {
			if (xhr.readyState === 4 && xhr.status === 200) {
				var idx = DashboardifyWidgetManagerState.widgets.findIndex(function(wi) { return String(wi.RecID) === String(recId); });
				if (idx >= 0) {
					DashboardifyWidgetManagerState.widgets[idx].PositionX = x;
					DashboardifyWidgetManagerState.widgets[idx].PositionY = y;
					DashboardifyWidgetManagerState.widgets[idx].SizeX = w;
					DashboardifyWidgetManagerState.widgets[idx].SizeY = h;
				}
				var currentDashboardId = document.getElementById("ddlSelectedDashboard") ? document.getElementById("ddlSelectedDashboard").value : null;
				if (currentDashboardId && typeof getWidgetsForDashboard === "function") {
					getWidgetsForDashboard(currentDashboardId);
				}
			}
		};
		xhr.send("recid=" + encodeURIComponent(recId) + "&x=" + x + "&y=" + y + "&width=" + w + "&height=" + h);
	}
}

function deleteWidgetFromManager() {
	var recId = DashboardifyWidgetManagerState.selectedRecId;
	if (!recId) return;
	if (!confirm("Are you sure you want to remove this widget?")) return;
	var adapter = window.DashboardifyDataAdapter;
	if (adapter && typeof adapter.deleteWidget === "function") {
		adapter.deleteWidget(recId).then(function() {
			openWidgetManager();
			var currentDashboardId = document.getElementById("ddlSelectedDashboard") ? document.getElementById("ddlSelectedDashboard").value : null;
			if (currentDashboardId && typeof getWidgetsForDashboard === "function") {
				getWidgetsForDashboard(currentDashboardId);
			}
		}).catch(function(err) {
			alert("Failed to delete: " + err.message);
		});
	} else {
		var xhr = new XMLHttpRequest();
		xhr.open("POST", "actions/DeleteWidget.php", true);
		xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		xhr.onreadystatechange = function() {
			if (xhr.readyState === 4 && xhr.status === 200) {
				openWidgetManager();
				var currentDashboardId = document.getElementById("ddlSelectedDashboard") ? document.getElementById("ddlSelectedDashboard").value : null;
				if (currentDashboardId && typeof getWidgetsForDashboard === "function") {
					getWidgetsForDashboard(currentDashboardId);
				}
			}
		};
		xhr.send("recid=" + encodeURIComponent(recId));
	}
}

if (document.getElementById("widgetManagerList")) {
	document.getElementById("widgetManagerList").addEventListener("change", loadWidgetManagerDetails);
}

if (document.getElementById("btnWidgetManagerSave")) {
	document.getElementById("btnWidgetManagerSave").addEventListener("click", saveWidgetManagerChanges);
}

if (document.getElementById("btnWidgetManagerDelete")) {
	document.getElementById("btnWidgetManagerDelete").addEventListener("click", deleteWidgetFromManager);
}

// End of index.js
