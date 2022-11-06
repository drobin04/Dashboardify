if (window.location.href.indexOf("EditRecID") != -1) {
	document.getElementById('light').style.display='block';
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