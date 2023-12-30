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
	  $(".widget").each(function() {

// Get the element you want to monitor for resize
const widget = $(this);

// Add a resize event listener to the widget
$(this).on('resize', () => {
  // Get the width and height of the resized widget
  const width = widget.offsetWidth;
  const height = widget.offsetHeight;

  // Get the ID of the element being resized
  const id = widget.id;

  // Construct the URL with the parameters
  const url = new URL('https://enspnistvlrb.x.pipedream.net');
  const params = new URLSearchParams();
  params.append('action', 'resize');
  params.append('width', width);
  params.append('height', height);
  params.append('id', id);
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
});














		var draggable = $(this).data("ui-draggable");
		if (draggable) {
		  $(this).draggable("option", "disabled", !draggable.options.disabled);
		}
	  });
  
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


if (window.location.href.indexOf("EditRecID") != -1) {
	document.getElementById('NewWidgetDialog').style.display='block';
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

		   	document.getElementById('txtpositionx').value = 0;

		    document.getElementById('txtpositiony').value = 0;

		    document.getElementById('txtsizeX').value = 0;

		    document.getElementById('txtsizeY').value = 0;

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

			   	document.getElementById('txtpositionx').value = rect.endX;

			    document.getElementById('txtpositiony').value = rect.endY;

			}

		    ctx.clearRect(0,0,canvas.width,canvas.height);

		    destroy_canvas();

		}

		function mouseMove(e) {

		  if (drag) {

		    rect.w = (e.pageX - this.offsetLeft) - rect.startX;

		    rect.h = (e.pageY - this.offsetTop) - rect.startY ;

		    ctx.clearRect(0,0,canvas.width,canvas.height);

		    draw();

		   	document.getElementById('txtpositionx').value = rect.startX;

		    document.getElementById('txtpositiony').value = rect.startY;

		    document.getElementById('txtsizeX').value = Math.abs(rect.w);

		    document.getElementById('txtsizeY').value = Math.abs(rect.h);

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