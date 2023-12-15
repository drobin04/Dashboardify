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
		
				function syncbookstatus(){
			const bookname = 'https://test.site/Dashboardify/madrigals.epub'
			// Retrieve the localstorage item
			const localstorageItem = localStorage.getItem(bookname);

			// Parse the localstorage item to a JSON object
			const data = JSON.parse(localstorageItem);

			// Create a new object with the userID, bookname, and location
			const payload = {
			userID: localStorage.getItem('userID'),
			bookname: bookname,
			location: data
			};

			// Send the payload to the PHP web page using an API request
			fetch('syncbookprogress.php', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json'
			},
			body: JSON.stringify(payload)
			})
			.then(response => {
			if (response.ok) {
				console.log('Data sent successfully');
			} else {
				console.error('Error sending data');
			}
			})
			.catch(error => {
			console.error('Error sending data:', error);
			});
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