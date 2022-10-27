<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml">
<head >
    <title>Dashboardify</title>
    <style>
        html, body{
            height: 100%;
        }
            .highlight {
                border-color: black;
                border: 2px dashed;
                background-color: lightgreen;
            }
            .black_overlay{
	            display: none;
	            position: absolute;
	            top: 0%;
	            left: 0%;
	            width: 100%;
	            height: 100%;
	            background-color: black;
	            z-index:1001;
	            -moz-opacity: 0.8;
	            opacity:.80;
	            filter: alpha(opacity=80);
            }
            .white_content {
	            display: none;
	            position: absolute;
	            width: auto;
	            height: 35%;
	            padding: 20px;
	            border: 1px solid black;
	            background-color: white;
	            z-index:1002;
	            overflow: auto;
                bottom: 0;
                right: 0;
                border-radius: 25px;
            }
            <?php //Grab custom CSS from Database - This part is important for background colors for example
            	// Create and connect to SQLite database file.
				$db_file = new PDO('sqlite:Dashboardify.s3db');
				// Prepare SELECT statement.
				$select = "SELECT * FROM UserCSS";
				$stmt = $db_file->prepare($select);
				
				// Execute statement.
				$stmt->execute();
				
				// Get the results.
				$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
				
				foreach($results as $row) {
                    			echo $row["CSS"];
				}
            ?>
    </style>
<?php
	//If not logged in, redirect to login page
	if (!isset($_COOKIE["SessionID"])) {
		header("Location: start-login.php");
	}  
?>
</head>
<body>
    <form id="form1" method="POST" action="NewWidget.php" >

	<!-- New Widget box-->
		<div id="light" class="white_content">
		<?php   

			$querystring = $_SERVER['QUERY_STRING']; //Get value from URL
			$WidgetID = str_replace("EditRecID=","",$querystring); //Strip out extra junk

			If ($WidgetID <> "") {
					// Connect to SQLite database file.
				$db_file = new PDO('sqlite:Dashboardify.s3db');
				// Prepare SELECT statement.
				$select = "SELECT * FROM Widgets";
				$stmt = $db_file->prepare($select);
				
				// Execute statement.
				$stmt->execute();
				
				// Get the results.
				$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach($results as $row) {

					If ($row["RecID"] == $WidgetID) {
						//echo "Widget " . $row["RecID"] . "selected for editing.";
						$WidgetTypeValue = $row["WidgetType"];
						$WidgetURLValue = $row["WidgetURL"];
						$WidgetDisplayText = $row["BookmarkDisplayText"];
						$WidgetPositionX = $row["PositionX"];
						$WidgetPositionY = $row["PositionY"];
						$WidgetSizeX = $row["SizeX"];
						$WidgetSizeY = $row["SizeY"];
						$WidgetCSSClass = $row["WidgetCSSClass"];
						$WidgetNotes = $row["Notes"];
						//echo $WidgetTypeValue . ", " . $WidgetURLValue;
						}
				}
			} else {
				// Prepare unused variables
				$WidgetTypeValue = "Bookmark";
				$WidgetURLValue = "";
				$WidgetDisplayText = "";
				$WidgetPositionX = "";
				$WidgetPositionY = "";
				$WidgetSizeX = "";
				$WidgetSizeY = "";
				$WidgetCSSClass = "";
				$WidgetNotes = "";
				$WidgetID = "";
			}
		?>
			
                    <button type="button" style="float: left !important;" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none'">Close</button>
                    <br />
                    <div id="columnc" class="column" style="width: 85% !important; clear: both; margin: 0 auto;">
                        <header >New Widget<hr /></header>
                        
                        <label>Widget Type: </label><select ID="ddlWidgetType" name="WidgetType">
							<option value='<?php echo $WidgetTypeValue?>' selected='selected'><?php echo $WidgetTypeValue?></option>
                            <!--<option value="Bookmark">Bookmark</option> Removed because duplicated by default value in line above-->
                            <option value="IFrame">IFrame</option>
                            <option value="Collapseable IFrame">Collapseable IFrame</option>
                            <option value="Notes">Notes</option>
                            <option value="HTMLEmbed">HTMLEmbed</option>
                        </select>
                        <br />
                        <label>Widget URL: </label><input ID="txtWidgetURL" name="URL" value="<?php echo $WidgetURLValue; ?>"></input>
                        <br />
                        <label>Display Text: </label><input ID="txtWidgetDisplayText" name="DisplayText" value="<?php echo $WidgetDisplayText; ?>"></input>
                        <br />
                        <hr>

                        <button type="button" style="margin-left:5px" onclick="init()">Set Position & Size</button>

                        <br />
                        <label>PositionX: </label><input ID="txtpositionx" Text="0" name="PositionX" value="<?php echo $WidgetPositionX; ?>"></input>
                        <br />
                        <label>PositionY: </label><input ID="txtpositiony" Text="0" name="PositionY" value="<?php echo $WidgetPositionY; ?>"></input>
                        <br />
                        <label>SizeX: </label><input ID="txtsizeX" Text="0" name="SizeX" value="<?php echo $WidgetSizeX; ?>"></input>
                        <br />
                        <label>SizeY: </label><input ID="txtsizeY" Text="0" name="SizeY" value="<?php echo $WidgetSizeY; ?>"></input>
                        <br />
                        <label>CSS Class: </label><input ID="txtCSSClass" name="CSSClass" value="<?php echo $WidgetCSSClass; ?>"></input>
                        <br />
                        <input ID="txtNotes"  TextMode="MultiLine" name="Notes" value="<?php echo $WidgetNotes; ?>"></input>
                        <br />
						Edit Widget ID: <input ID="txtWidgetID" name="ID" value="<?php echo $WidgetID; ?>"></input>
                        <br />
                        <button id="btnSubmitNewWidget">Submit</button>

                    </div>
		</div>

		<!--Edit CSS Box-->
        <div id="light2" class="white_content" style="right: initial !important; left:0 !important; width:400px !important;">
                    <button type="button" style="float: left !important;" onclick="document.getElementById('light2').style.display='none';">Close</button>
                    <br />
                    <div id="columnd" class="column" style="width: 85% !important; height:100% !important; clear: both; margin: 0 auto;">
                        <header >CSS<hr /></header>
                        <button ID="btnUpdateCSS"  Text="Save CSS" /><br />
                        <input ID="txtCSS"  TextMode="MultiLine" style="width:100%; height:80%;"></input>
                        
                    </div>
		</div>

        <div>

			<script>
				<!--If EditRecID is present, open the new/edit widget box-->
				if (window.location.href.indexOf("EditRecID") != -1) {
					document.getElementById('light').style.display='block';
				}
			</script>
			<!--Buttons at top left-->
            <button type="button" style="float: left !important;" onclick="document.getElementById('light').style.display='block';document.getElementById('fade').style.display='block'">New Widget</button>
            <button type="button" style="float: left !important;" onclick="document.getElementById('light2').style.display='block';">Edit CSS</button>
            <button type="button" style="float: left !important;" onclick="var all = document.getElementsByClassName('editbuttons'); for (var i = 0; i < all.length; i++) {all[i].style.display = 'initial';}">Edit Widgets</button>
            <br />

			<?php 
				function selectquery($sql) {
					$localdb = $db_file = new PDO('sqlite:Dashboardify.s3db'); 
					$stmt1 = $localdb->prepare($sql);
					$stmt1->execute();
					$results = $stmt1->fetchAll(PDO::FETCH_ASSOC);
					return $results;
				}
				function execquery($sql) {
					$localdb = $db_file = new PDO('sqlite:Dashboardify.s3db'); 
					$stmt1 = $localdb->prepare($sql);
					$stmt1->execute();
					
				}
				function GUID()
				{
					if (function_exists('com_create_guid') === true)
					{
						return trim(com_create_guid(), '{}');
					}
				
					return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
				}



				//Check for dashboards for user; Create first dashboard if none exist, then load any widgets found for dashboard if exists.
				// Connect to SQLite database file.
				$db_file = new PDO('sqlite:Dashboardify.s3db');
				
				//Get session ID.
				$sessionid = $_COOKIE["SessionID"];
				//Get User for Session ID
				$userid = selectquery("Select UserID From Sessions Where SessionID = '" . $sessionid . "'")[0]["UserID"];
				//echo "USER ID FOUND - User - " . $userid;
				// Query for dashboards for user. 
				$dashboards = selectquery("Select RecID From Dashboards Where UserID = '" . $userid . "'");
				$dashboardid = "";
				Try {

					if (count($dashboards) >= 1) {
						echo "DASHBOARDS FOUND: " . (count($dashboards));
					} else { // If dash not found, create one
						//echo "NO DASHBOARDS FOUND.";
						$dashboardid = GUID(); 
						//echo "INSERT INTO Dashboards (DashboardID, UserID) VALUES ('" . $dashboardid . "', '" . $userid . "')";
						execquery("INSERT INTO Dashboards (DashboardID, UserID) VALUES ('" . $dashboardid . "', '" . $userid . "')");
					} 
				} catch (exception $ex) {
						echo $ex;
				}
				//




				// Prepare SELECT statement.
				$select = "SELECT * FROM Widgets";
				$stmt = $db_file->prepare($select);
				
				// Execute statement.
				$stmt->execute();
				
				// Get the results.
				$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
				
				//Variable for site url
				$siteurl = "http://localhost/";

				foreach($results as $row) {
					
					If ($row["WidgetType"] == "Bookmark") {
						echo "<div style='padding: 5px; margin: 5px; width:100px; background-color: lightgrey;  border: 1px solid black;' class='" . $row["WidgetCSSClass"] . "'>
						<a target='_blank' href='". $row["WidgetURL"] ."'>". $row["BookmarkDisplayText"] ."</a>
						
		<a class='editbuttons' style='display:none;height:24px; width:24px;' href='" . $siteurl . "?EditRecID=" . $row["RecID"] . "'>
							<img style='height:24px; width:24px;' src='" . $siteurl . "icons/edit.png'></img>
						</a>
		
		<a class='editbuttons' style='display:none;height:24px; width:24px;' href='" . $siteurl . "DeleteWidget.php?RecID=" . $row["RecID"] . "'>
							<img style='height:24px; width:24px;' src='" . $siteurl . "icons/cancel.png'></img>
						</a>
					</div>";
					};

					
					If ($row["WidgetType"] == "IFrame") {
						echo "
					            <div style='margin:15px; position:absolute; background-color: white;  border: 1px solid black;
					left: " . $row["PositionX"] . "px; top: " . $row["PositionY"] . "px; width: " . $row["SizeX"] . "px; height: " . $row["SizeY"] . "px; max-width: " . $row["SizeX"] . "px;' class='" . $row["WidgetCSSClass"] . "'>
					<a class='editbuttons' style='display:none;height:24px; width:24px;' href='" . $siteurl . "?EditRecID=" . $row["RecID"] . "'>
					                    <img style='height:24px; width:24px;' src='" . $siteurl . "icons/edit.png'></img>
					                </a>
					
					<a class='editbuttons' style='display:none;height:24px; width:24px;' href='" . $siteurl . "DeleteWidget.php?RecID=" . $row["RecID"] . "'>
					                    <img style='height:24px; width:24px;' src='" . $siteurl . "icons/cancel.png'></img>
					                </a>
					
					<iframe style='height:100%;width:100%' src='". $row["WidgetURL"] ."'></iframe></a>
					
					</div>
					";
					}
					
					If ($row["WidgetType"] == "Notes") {
						echo "
					            <div style='margin:15px; position:absolute; background-color: white;  border: 1px solid black;
					left: " . $row["PositionX"] . "px; top: " . $row["PositionY"] . "px; width: " . $row["SizeX"] . "px; height: " . $row["SizeY"] . "px; max-width: " . $row["SizeX"] . "px;' class='" . $row["WidgetCSSClass"] . "'>
					<a class='editbuttons' style='display:none;height:24px; width:24px;' href='" . $siteurl . "?EditRecID=" . $row["RecID"] . "'>
					                    <img style='height:24px; width:24px;' src='" . $siteurl . "icons/edit.png'></img>
					                </a>
					
					<a class='editbuttons' style='display:none;height:24px; width:24px;' href='" . $siteurl . "DeleteWidget.php?RecID=" . $row["RecID"] . "'>
					                    <img style='height:24px; width:24px;' src='" . $siteurl . "icons/cancel.png'></img>
					                </a>
					
					<p>". $row["Notes"] ."</p>
					
					</div>
					";
					}
					If ($row["WidgetType"] == "HTMLEmbed") {
						echo "
					            <div style='margin:15px; position:absolute; background-color: white;  border: 1px solid black;
					left: " . $row["PositionX"] . "px; top: " . $row["PositionY"] . "px; width: " . $row["SizeX"] . "px; height: " . $row["SizeY"] . "px; max-width: " . $row["SizeX"] . "px;' class='" . $row["WidgetCSSClass"] . "'>
					<a class='editbuttons' style='display:none;height:24px; width:24px;' href='" . $siteurl . "?EditRecID=" . $row["RecID"] . "'>
					                    <img style='height:24px; width:24px;' src='" . $siteurl . "icons/edit.png'></img>
					                </a>
					
					<a class='editbuttons' style='display:none;height:24px; width:24px;' href='" . $siteurl . "DeleteWidget.php?RecID=" . $row["RecID"] . "'>
					                    <img style='height:24px; width:24px;' src='" . $siteurl . "icons/cancel.png'></img>
					                </a>
					
					". $row["Notes"] ."
					
					</div>
					";
					}
					
				}
				echo "</table>";

			?>

        </div>
    </form>
    <script type="text/javascript"> <!--Script for drawing widget boundary with mouse-->

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
    </script>
</body>
</html>