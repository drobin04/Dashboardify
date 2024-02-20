async function getWidgetsForDashboard(dashboardID) {
	document.cookie = "dashboardid=" + dashboardID;
	var urlforapi = getrooturlpath() + '/api/getWidgetsForDashboard.php?recid='+ dashboardID;
		
	var widgets = await fetchData(urlforapi);
	console.log(widgets);
	
	if (widgets == "") {
		console.log("Response for new widgets was empty. Will try to draw from cache.");	
	} else {
		console.log("Response received for widgets, drawing with updated data.");	
	}
	
	// Before this point, check if we received any response data at all from the API request. If the body / response data is empty, don't proceed, and don't run clearWidgetContainer. 
	
	// The server side can check whether our cookie value for the hash for this dashboard is correct or not. Therefore, the server will determine whether to send us an update or not. 
	
	
	//clearWidgetContainer();
	
	renderwidgetsfromjson(widgets);
	
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

async function fetchData(url) {
    const response = await fetch(url);
    const data = await response.json();
    return data;
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
	  
		// Variable Declarations to be shared between widget types: 
		let siteurl = getrooturlpath();
		let editbuttonscss = "<a class='editbuttons' style='display:none;height:24px; width:24px;' href='";
		
		
		const floatingbookmarkPositionAndCSSClass = "left: " + PositionX + "px; top: " + PositionY + "px;' class='widget resize " + WidgetCSSClass + "'>";
		
		const dashboardid = DashboardRecID;
		const imgstylecss = "<img style='height:24px; width:24px;' src='";
		
		deletebuttonOnClick = "deleteWidget(" + RecID + ",)";
		deletebuttoncss = "<a class='editbuttons' style='display:none;height:24px; width:24px;' onclick='" + deletebuttonOnClick + "'";
		
		
		
		PositionAndSize = "left: " + PositionX + "px; top: " + PositionY + "px; width: " + SizeX + "px; height: " + SizeY + "px; width: " + SizeX + "px;' ";
		
		PositionAndCSSClass = PositionAndSize + "class='widget resize " + WidgetCSSClass + " Countdown'>";
		
		combined = "<div id='" + RecID + "' style='margin:15px; position:absolute; background-color: white;  border: 1px solid black;" + PositionAndCSSClass + editbuttonscss + siteurl + "?EditRecID=" + RecID + "&SelectDashboardID=" + dashboardid + "'>" + imgstylecss + siteurl + "icons/edit.png'></img></a>" + deletebuttoncss + siteurl + "actions/DeleteWidget.php?RecID=" + RecID + "'>" + imgstylecss + siteurl + "icons/cancel.png'></img></a>";
	  
	  
	  switch (WidgetType) {
	  	case "Bookmark":
	  			  		
	  		if (PositionX != "") {
	  			//console.log(RecID + " is a positioned bookmark");
	  			// echo $floatingbookmark
	  			var floatingbookmark = `<div id='${RecID}' class='widget bookmark' style='position: absolute; width:100px; background-color: lightgrey;  border: 1px solid black; ${floatingbookmarkPositionAndCSSClass}${editbuttonscss}${siteurl}?EditRecID=${RecID}&SelectDashboardID=" 
${dashboardid}'>${imgstylecss}${siteurl}/icons/edit.png'></img></a>${deletebuttoncss}>${imgstylecss}${siteurl}/icons/cancel.png'></img></a>`;
	  		
				var widget_output = floatingbookmark + " <div style='padding: 5px; width: 100%; class='widget bookmark " + WidgetCSSClass + "'><a target='_blank' href='" + WidgetURL + "'>" + BookmarkDisplayText + "</a></div></div>";
				let y = document.getElementById('widgetcontainer');
				y.innerHTML += widget_output;
			
			}
	  		if (PositionX == "") {
	  			//console.log(RecID + " is a normal bookmark");
	  			
	  			let x = `<div id='${RecID}' style='padding: 5px; margin: 5px; width:100px; background-color: lightgrey;  border: 1px solid black;' class='widget bookmark ${WidgetCSSClass}'><a target='_blank' href='${WidgetURL}'>${BookmarkDisplayText}</a>${editbuttonscss}${siteurl}?EditRecID=${RecID}&SelectDashboardID=${dashboardid}'>${imgstylecss}${siteurl}icons/edit.png'></img></a>${deletebuttoncss}${siteurl}actions/DeleteWidget.php?RecID=${RecID}'>${imgstylecss}${siteurl}icons/cancel.png'></img></a></div>`;
	  			let y = document.getElementById('widgetcontainer');
				y.innerHTML += x;
	  		}
	  		break;
	  	case "Notes":
	  		// Escape the Notes value to replace '' with '
			let notesvalue = Notes.replace(/''/g, "'");
			let left = PositionX + "px";
			let top = PositionY + "px";
			let width = SizeX + "px";
			let height = SizeY + "px";
			let customclass = WidgetCSSClass;
			let combined_notes = "<div class='notes resize widget " + customclass + " ' id='" + RecID + "' style='margin:15px; position:absolute; background-color: white;  border: 1px solid black;left: " + left + "; top: " + top + "; width: " + width + "; height: " + height + ";'>" + "<a class='editbuttons' style='display:none;height:24px; width:24px;' href='" + siteurl +  "?EditRecID=" + RecID + "&SelectDashboardID=" + dashboardid + "'>" +  imgstylecss + siteurl + "icons/edit.png'></img></a>" +  deletebuttoncss + ">" + imgstylecss + siteurl +  "icons/cancel.png'></img></a>";
		
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
		case "IFrame":
			echo(combined + "<iframe style='height:100%;width:100%' src='" + WidgetURL + "'></iframe></a></div>");
			break;
		case "Collapseable IFrame":
			let combined2 = "<div id='" + RecID + "' style='display:none; position:absolute; background-color: white;  border: 1px solid black;" + "width: " + SizeX + "px; height: " + SizeY + "px; width: " + SizeX + "px;' class='widget resize " + WidgetCSSClass + "'>" + editbuttonscss + siteurl + "?EditRecID=" + RecID + "&SelectDashboardID=" + dashboardid + "'>" + imgstylecss + siteurl + "icons/edit.png'></img></a>" + editbuttonscss + siteurl + "actions/DeleteWidget.php?RecID=" + RecID + "'>" + imgstylecss + siteurl + "icons/cancel.png'></img></a>";
    
			let hidden = "<div id='' class='collapse' style='" + "left: " + PositionX + "px; top: " + PositionY + "px; width: " + SizeX + "px; height: 20px; width: " + SizeX + "px;'" + "'>";
			echo(hidden + "<a style='border: none !important;' class='collapse' onclick='opencollapsediframe(&quot;" + RecID + "&quot;)'>" + BookmarkDisplayText + "</a>");
			echo(combined2 + "<iframe style='height:100%;width:100%;' id='" + RecID + "/iframe' src2='" + WidgetURL + "'></iframe></a></div>");
			echo("</div>"); //this wraps combined variable, into a surrounding div.
			
			break;
		case "HTMLEmbed":
			//echo(combined + Notes+"</div>");
			break;
		default: 
			
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

function editExistingNote(RecID) {
	// Get note contents from API call
	// Construct the URL with the parameters
	var NotesData = "";
	var requesturl = getrooturlpath() + '/api/getnotesforwidget.php';
	const url = new URL(requesturl);
	const params = new URLSearchParams();
	params.append('RecID', RecID);
	url.search = params.toString();
	fetch(url)
	  .then(response => response.text())  // Update to handle response as text
	  .then(data => {
		// Save the response to a variable here
		const apiResponse = data;
		NotesData = apiResponse;
		// Now you can use the apiResponse variable to work with the API response
		var y = document.getElementById(RecID + '_note');
		y.innerHTML = "<form style='height: 100%;width:100%;' method='POST' id='notes_submission' action='" + getrooturlpath() + "/api/updatenoteswidget.php?RecID=" + RecID + "'><textarea name='Notes' style='width: 95%; height: 90%;'>" + NotesData + "</textarea><br /><button>Save</button></form>";
	  })
	  .catch(error => {
		// Handle any errors here
		console.error('Error:', error);
	  });
	
	
	// After we get note contents, blank the content already in the note widget
	
	// Then, display text box with the API content ready for editing, and a text box / HTML form that submits the edit? 
	
	// Probably want the saved content to trigger a postback / page refresh, unless we can trigger md-block to render content in realtime, in which case we would just want it to be javascript... but html form would be easier.
	
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

function deleteWidget(recID) {
  var x = document.getElementById(recID);
  x.remove();
  
  const apiURL = getrooturlpath() + '/actions/DeleteWidget.php';
  // Delete The Widget From The Screen
  

  // Construct the URL with the parameters
  const url = new URL(apiURL);
  const params = new URLSearchParams();
  params.append('RecID', recID);
  url.search = params.toString();

  // Send a request to the API with the data in the URL
  fetch(url, {
	method: 'POST'
  })
  .then(response => {
	// Handle the API response here
  })
  .catch(error => {
	console.error('Error:', error);
  });
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
  
	// Function to toggle edit mode
function toggleEditMode() {
// Toggle the "draggable" property of elements with the class "widget"


		var draggable = $(this).data("ui-draggable");
		if (draggable) {
		  $(this).draggable("option", "disabled", !draggable.options.disabled);
		}
  
  // Initialize the draggable property of elements with the class "widget"
  $(".widget").draggable({
	  stop: function(event, ui) {
		// Get the current position of the widget element
		var x = ui.position.left;
		var y = ui.position.top;
  
		// Get the ID of the moved element
		var widgetId = $(this).attr("id");
  
		// Send API request with X, Y, and ID as query string parameters
		var apiUrl = "actions/move_resize_widget.php?action=movewidget";
		var urlWithParams = apiUrl + "&x=" + x + "&y=" + y + "&RecID=" + widgetId;
  
		$.ajax({
		  url: urlWithParams,
		  method: "GET",
		  success: function(response) {
			// Handle the API response if needed
			console.log("API request successful");
		  },
		  error: function(xhr, status, error) {
			// Handle any errors that occur during the API request
			console.error("API request failed:", error);
		  }
		});
	  }
	});
  
  
	}
  });



  $(document).ready(function() {
	// Add event handler to the button with id "resizewidgets"
	$("#resizewidgets").click(function() {
	  console.log("resize mode active");
	  toggleresize();
	});
  
	// Function to toggle edit mode
	function toggleresize() {
	  $(".resize").each(function() {

		// Add the desired styles to the widget
		$(this).css({
			'resize': 'both',
			'overflow': 'auto'
		});

		// Get the element you want to monitor for resize
		var widget = $(this)[0];
  
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
			  widget.resizeTimeout = setTimeout(function() {
				// Get the width and height of the resized widget
				const width = widget.offsetWidth;
				const height = widget.offsetHeight;
  
				// Get the ID of the element being resized
				const id = widget.id;
  
				// Construct the URL with the parameters
				const url = new URL(window.location.origin + "/Dashboardify/actions/move_resize_widget.php");
				const params = new URLSearchParams();
				params.append("action", "resize");
				params.append("width", width);
				params.append("height", height);
				params.append("id", id);
				url.search = params.toString();
  
				// Send a request to the API with the data in the URL
				fetch(url, {
				  method: "POST"
				})
				  .then(response => {
					// Handle the API response here
				  })
				  .catch(error => {
					console.error("Error:", error);
				  });
			  }, 500); // Adjust the delay as needed
			}
		  }
		});
  
		// Start observing the widget for attribute changes
		observer.observe(widget, { attributes: true });
	  });
	}
  });

function openeditdialog() {
	if (window.location.href.indexOf("EditRecID") != -1) {
		document.getElementById('NewWidgetDialog').style.display='block';
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
			var value = e.value;
			var text = e.options[e.selectedIndex].text;
			// var newurl = window.location.href.split("?")[0] + "?SelectDashboardID=" + value; // Removed to address bug with dashboard select dropdown not working when on cachedpage.html vs index.php. 
			var newurl = "index.php?SelectDashboardID=" + value; // Added this to address bug RE line above. .
			
			window.location.href = newurl;
			//console.log(newurl);
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
		
		function getLocation(userID, bookname) {
			// Set the data to be sent in the message body as an object
			const data = { userID: userID, bookname: bookname };
			
			// Send a POST request to the PHP page with the data in the message body
			fetch('getbookprogress.php', {
			  method: 'POST',
			  headers: { 'Content-Type': 'application/json' },
			  body: JSON.stringify(data)
			})
			  .then(response => response.json())
			  .then(data => {
				// Update the local storage item with the retrieved location data
				localStorage.setItem(bookname, JSON.stringify(data));
				console.log('updated localstorage with book position: ' + JSON.stringify(data));
			  })
			  .catch(error => {
				console.error('Error retrieving location:', error);
			  });
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
	var SizeAndCSSClassMarkup = "<hr><button type=\"button\" style=\"margin-left:5px\" onclick=\"Experimental_New_Widget_Form_Sizer_init()\">Set Position & Size</button><br /><label id='sizelabel'></label><div style='display:none;'><label>PositionX: </label><input ID=\"txtpositionx2\" Text=\"0\" name=\"PositionX\"></input><br /><label>PositionY: </label><input ID=\"txtpositiony2\" Text=\"0\" name=\"PositionY\"></input><br /><label>SizeX: </label><input ID=\"txtsizeX2\" Text=\"0\" name=\"SizeX\"></input><br /><label>SizeY: </label><input onchange='UpdateSizeCalcs();' ID=\"txtsizeY2\" Text=\"0\" name=\"SizeY\"></input></div><br /><label>CSS Class: </label><input ID=\"txtCSSClass\" name=\"CSSClass\"></input><br />";
	
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