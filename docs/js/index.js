// Function to add options to the select element
function addOptionsToSelect() {
    const selectElement = document.getElementById('ddlWidgetType2');

    if (selectElement) {
        const optionValues = [
            { value: 'SQLServerScalarQuery', text: 'SQLServerScalarQuery' },
            { value: 'SQLiteResultsList', text: 'SQLiteResultsList' },
            { value: 'SQLite Chart (PHPGD)', text: 'SQLite Chart (PHPGD)' }
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
		case "SQLiteResultsList":
		case "SQLServerScalarQuery":
		case "SQLite Chart (PHPGD)":
			var dbname = document.getElementById("SQLDBName");
			if (dbname) dbname.value = result2.sqldbname || "";
			var sqlquery = document.getElementById("sqlquery");
			if (sqlquery) sqlquery.value = result2.sqlquery || "";
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
		case "Collapseable IFrame":
			let combined2 =
				"<div id='" +
				RecID +
				"' style='display:none; position:absolute; background-color: white;  border: 1px solid black;" +
				"width: " +
				SizeX +
				"px; height: " +
				SizeY +
				"px; width: " +
				SizeX +
				"px;' class='widget resize " +
				WidgetCSSClass +
				"'>" +
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
    
			let hidden = "<div id='' class='collapse' style='" + "left: " + PositionX + "px; top: " + PositionY + "px; width: " + SizeX + "px; height: 20px; width: " + SizeX + "px;'" + "'>";
			echo(hidden + "<a style='border: none !important;' class='collapse' onclick='opencollapsediframe(&quot;" + RecID + "&quot;)'>" + BookmarkDisplayText + "</a>");
			echo(combined2 + "<iframe style='height:100%;width:100%;' id='" + RecID + "/iframe' src2='" + WidgetURL + "'></iframe></a></div>");
			echo("</div>"); //this wraps combined variable, into a surrounding div.
			
			break;
		case "HTMLEmbed":
			//echo(combined + Notes+"</div>");
			break;
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
}

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
    $( ".white_content" ).draggable();
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
	//alert(recid);
	document.getElementById(recid).style.display='block';
	var iframeID = recid + '/iframe';
	//alert(document.getElementById(iframeID).getAttribute('src2'));
	document.getElementById(iframeID).setAttribute("src", document.getElementById(iframeID).getAttribute("src2"));
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
	var SizeAndCSSClassMarkup = "<hr><button type=\"button\" style=\"margin-left:5px\" onclick=\"Experimental_New_Widget_Form_Sizer_init()\">Set Position & Size</button><br /><label id='sizelabel'></label><div style='display:none;'><label>PositionX: </label><input id=\"txtpositionx2\" value=\"\" autocomplete=\"off\" name=\"PositionX\"><br /><label>PositionY: </label><input id=\"txtpositiony2\" value=\"\" autocomplete=\"off\" name=\"PositionY\"><br /><label>SizeX: </label><input id=\"txtsizeX2\" value=\"\" autocomplete=\"off\" name=\"SizeX\"><br /><label>SizeY: </label><input onchange='UpdateSizeCalcs();' id=\"txtsizeY2\" value=\"\" autocomplete=\"off\" name=\"SizeY\"></div><br /><label>CSS Class: </label><input id=\"txtCSSClass\" name=\"CSSClass\"><br />";
	
	switch (ddlValue) {
		
	case "Bookmark":
		//URL and Display Text needed,
		//As well as position / size elements
		var x = "<hr><span id='widgetURL'><label>Widget URL: </label><input ID='txtWidgetURL' name='URL'></input><br /></span><label>Display Text: </label><input ID=\"txtWidgetDisplayText\" name=\"DisplayText\"></input><br />";
		//Fill content to the dialog
		document.getElementById('NewWidget_Form').innerHTML = SizeAndCSSClassMarkup + x;
		break;
		
	case "Countdown":
		var x = "<hr></span><label>Countdown Title: </label><input ID=\"txtWidgetDisplayText\" name=\"DisplayText\"></input><br />";
		var y = "<input type=\"date\" id=\"datepicker\" name=\"Notes\"> <br/>";
		//Fill content to the dialog
		document.getElementById('NewWidget_Form').innerHTML = SizeAndCSSClassMarkup + x + y;
		
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
		var x = "<br />Notes:<br /> <textarea ID=\"txtNotes\" rows=\"4\" cols=\"50\" name=\"Notes\"></textarea><br />";
		//Fill content to the dialog
		document.getElementById('NewWidget_Form').innerHTML = SizeAndCSSClassMarkup + x;
		break;

	case "HTMLEmbed":
		//Position, Sizine, HTML fields needed
		//As well as position / size elements
		var x = "<br />HTML:<br /> <textarea ID=\"txtNotes\" rows=\"4\" cols=\"50\" name=\"Notes\"></textarea><br />";
		//Fill content to the dialog
		document.getElementById('NewWidget_Form').innerHTML = SizeAndCSSClassMarkup + x;
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