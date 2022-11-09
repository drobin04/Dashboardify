<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml"><head><title>Dashboardify</title><link type="text/css" rel="stylesheet" href="index.css">
    <style><?php include("usercss.php"); //Load user-defined CSS for page from DB. Moved to file to make this file easier to read, as this code has long been finished. ?></style><?php include("logoutredirect.php");?></head>
<body><form id="form1" method="POST" action="NewWidget.php" >
	
        <div>
			<!--Buttons at top left-->
            <button type="button" style="float: left !important;" onclick="document.getElementById('light').style.display='block';document.getElementById('fade').style.display='block'">New Widget</button>
            <button type="button" style="float: left !important;" onclick="document.getElementById('light2').style.display='block';">Edit CSS</button>
            <button type="button" style="float: left !important;" onclick="document.getElementById('light3').style.display='block';">New Dashboard</button>
            <button type="button" style="float: left !important;" onclick="var all = document.getElementsByClassName('editbuttons'); for (var i = 0; i < all.length; i++) {all[i].style.display = 'initial';}">Edit Widgets</button>
			<?php //Check for dashboards for user; Create first dashboard if none exist, then load any widgets found for dashboard if exists.
				include("shared_functions.php");
				//debuglog("shared functions module loaded.");
				$db_file = new PDO('sqlite:Dashboardify.s3db'); // Connect to SQLite database file.
				$sessionid = $_COOKIE["SessionID"]; debuglog($sessionid, "SessionID"); //Get User for Session ID
				$userid = selectquery("Select UserID From Sessions Where SessionID = '" . $sessionid . "'")[0]["UserID"]; debuglog($userid, "User ID found for user");
				$dashboards = selectquery("Select * From Dashboards Where UserID = '" . $userid . "'"); // Query for dashboards for user. 
				
				$dashboardid = "";
				$dashboardphotourl = ""; // Used later at end of script to pre-populate value into input for CSS box.
				Try {
					$count = count($dashboards);
					debuglog($count, "Count variable");
					if ((int)$count == 1) {
						debuglog($dashboards, "Dashboards Array - Single Result"); $dashboardid = $dashboards[0]["RecID"]; debuglog($dashboardid, "Selected Dashboard ID."); $dashboardphotourl = $row["BackgroundPhotoURL"]; $usercss = $row["CustomCSS"];
					} elseif ((int)$count > 1) {
						debuglog($dashboards, "Dashboards Array - Multiple Results"); 
						echo "<label> Dashboard: </label><select ID='ddlSelectedDashboard' name='SelectedDashboard' onchange='loadselecteddashboard()'>";
						$match = 0;
						$matcheddashboardid = -1;
						If (Isset($_GET["SelectDashboardID"])) { $matcheddashboardid = $_GET["SelectDashboardID"];}

						foreach($dashboards as $row) {
							$recid = $row["RecID"];
							debuglog($row);
							if ($row["DefaultDB"] == "Y") {
								$dashboardid = $recid;
								$dashboardphotourl = $row["BackgroundPhotoURL"];
								$usercss = $row["CustomCSS"];
								debuglog($dashboardid, "Selected Dashboard ID.");
								
								echo "<option value='" . $row["RecID"] . "'";
								If ($recid == $matcheddashboardid){echo "selected='selected'";} elseif ($matcheddashboardid <> -1) {} else {echo "selected='selected'";}
								echo ">" . $row["Name"] . "</option>";

							} else {
								echo "<option value='" . $row["RecID"] . "'";
								If ($recid == $matcheddashboardid){echo "selected='selected'";}
								echo ">" . $row["Name"] . "</option>";

							}

							if ($row["RecID"] == $_GET["SelectDashboardID"]) { $match = 1; debuglog(array($match,$matcheddashboardid), "Match / selected db from URL if present?"); }
						}
						echo "</select><br />";

						//debuglog($_GET['SelectDashboardID'], "Selected Dashboard ID from URL");
						If ($match == 1) {$dashboardid = $_GET["SelectDashboardID"];}

					} else { // If dash not found, create one
						$dashboardid = GUID(); 
						$sql1 = "INSERT INTO Dashboards (DashboardID, UserID) VALUES ('" . $dashboardid . "', '" . $userid . "')";
						debuglog($sql1,"SQL Dashboard Insert Query"); execquery($sql1);
					} 
				} catch (exception $ex) {echo $ex;debuglog($ex,"Exception found during dashboard checks");
					debuglog($ex); echo $ex;
				} 
				//Try to set background photo and CSS for dashboard
				If (isset($dashboardphotourl)) {echo "<style>body {background-image: url('" . $dashboardphotourl . "'); background-size: cover;} " . $usercss . "</style>";}

				// Load Widgets For Selected Dashboard
				$select = "SELECT * FROM Widgets Where DashboardRecID = '" . $dashboardid . "'"; debuglog($select,"Query for widgets");
				$stmt = $db_file->prepare($select); $stmt->execute();
				$results = $stmt->fetchAll(PDO::FETCH_ASSOC); debuglog($results, "Widget results");
				include("siteurlconfig.php"); //Variable for site url
				foreach($results as $row) { //Starting with Re-useable texts
					$editbuttonscss = "<a class='editbuttons' style='display:none;height:24px; width:24px;' href='";
					$imgstylecss = "<img style='height:24px; width:24px;' src='";
					$PositionAndCSSClass = "left: " . $row["PositionX"] . "px; top: " . $row["PositionY"] . "px; width: " . $row["SizeX"] . "px; height: " . $row["SizeY"] . "px; max-width: " . $row["SizeX"] . "px;' class='" . $row["WidgetCSSClass"] . "'>";
					$combined = "<div style='margin:15px; position:absolute; background-color: white;  border: 1px solid black;" . $PositionAndCSSClass . $editbuttonscss . $siteurl . "?EditRecID=" . $row["RecID"] . "&SelectDashboardID=" . $dashboardid . "'>" . $imgstylecss . $siteurl . "icons/edit.png'></img></a>" . $editbuttonscss . $siteurl . "DeleteWidget.php?RecID=" . $row["RecID"] . "'>" . $imgstylecss . $siteurl . "icons/cancel.png'></img></a>";
					If ($row["WidgetType"] == "SQLServerScalarQuery") {
						$sqlservaddress = $row["sqlserveraddress"];
						$sqldbname = $row["sqldbname"];
						$sqlserveruser = $row["sqluser"];
						$sqlserverpass = $row["sqlpass"];
						$sqlquery = $row["sqlquery"];
						debuglog($sqlquery,"sql query about to be executed");
						$result = "";
						try { // reference for sql query from PHP - https://social.technet.microsoft.com/wiki/contents/articles/1258.accessing-sql-server-databases-from-php.aspx
							$connectionInfo = array( "Database"=>$sqldbname, "UID"=>$sqlserveruser, "PWD"=>$sqlserverpass);
							//debuglog($connectionInfo,"debug - connection info");
							$conn = sqlsrv_connect( $sqlservaddress, $connectionInfo);
							if( $conn ) {
								 debuglog("Connection established.");
								$tempresult = sqlsrv_query($conn,$sqlquery);
								if ( sqlsrv_fetch( $tempresult ) )
									{$result = sqlsrv_get_field( $tempresult, 0);debuglog($result, "Results of SQL query");}
							}else{debuglog("Connection could not be established."); debuglog(sqlsrv_errors());}
					   }
					   catch (Exception $ex) {
							debuglog($ex, "error during SQL server connection");
					   }
						echo $combined . "<p>" . $row["BookmarkDisplayText"] . ": " . $result ."</p></div>";
					}
					If ($row["WidgetType"] == "Bookmark") {echo "<div style='padding: 5px; margin: 5px; width:100px; background-color: lightgrey;  border: 1px solid black;' class='bookmark" . $row["WidgetCSSClass"] . "'><a target='_blank' href='". $row["WidgetURL"] ."'>". $row["BookmarkDisplayText"] ."</a>" . $editbuttonscss . $siteurl . "?EditRecID=" . $row["RecID"] . "&SelectDashboardID=" . $dashboardid . "'>" . $imgstylecss . $siteurl . "icons/edit.png'></img></a>" . $editbuttonscss . $siteurl . "DeleteWidget.php?RecID=" . $row["RecID"] . "'>" . $imgstylecss . $siteurl . "icons/cancel.png'></img></a></div>";};
					If ($row["WidgetType"] == "IFrame") {echo $combined . "<iframe style='height:100%;width:100%' src='". $row["WidgetURL"] ."'></iframe></a></div>";}
					If ($row["WidgetType"] == "Notes") {echo $combined . "<p>". $row["Notes"] ."</p></div>";}
					If ($row["WidgetType"] == "HTMLEmbed") {echo $combined . $row["Notes"] ."</div>";}} echo "</table>";
			?>
			<!-- New Widget box-->
		<div id="light" class="white_content">
		<?php   
			$querystring = $_SERVER['QUERY_STRING']; //Get value from URL
			//$WidgetID = str_replace("EditRecID=","",$querystring); //Strip out extra junk
			If (Isset($_GET["EditRecID"])) {
				$WidgetID = $_GET["EditRecID"];
				$db_file = new PDO('sqlite:Dashboardify.s3db'); // Connect to SQLite database file.
				$select = "SELECT * FROM Widgets"; // Prepare SELECT statement.
				$stmt = $db_file->prepare($select);
				$stmt->execute(); // execute
				$results = $stmt->fetchAll(PDO::FETCH_ASSOC); // Get the results.
				foreach($results as $row) {
					If ($row["RecID"] == $WidgetID) {
						$WidgetTypeValue = $row["WidgetType"];
						$WidgetURLValue = $row["WidgetURL"];
						$WidgetDisplayText = $row["BookmarkDisplayText"];
						$WidgetPositionX = $row["PositionX"];
						$WidgetPositionY = $row["PositionY"];
						$WidgetSizeX = $row["SizeX"];
						$WidgetSizeY = $row["SizeY"];
						$WidgetCSSClass = $row["WidgetCSSClass"];
						$WidgetNotes = $row["Notes"];
						}
				}
			} else {$WidgetTypeValue = "Bookmark";$WidgetURLValue = "";$WidgetDisplayText = "";$WidgetPositionX = "";$WidgetPositionY = "";$WidgetSizeX = "";$WidgetSizeY = "";$WidgetCSSClass = "";$WidgetNotes = "";$WidgetID = "";} // Prepare unused variables
		?>
                    <button type="button" style="float: left !important;" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none'">Close</button>
                    <br />
                    <div id="columnc" class="column" style="width: 85% !important; clear: both; margin: 0 auto;">
                        <header >New Widget<hr /></header>
                        
                        <label>Widget Type: </label><select ID="ddlWidgetType" name="WidgetType">
							<option value='<?php echo $WidgetTypeValue?>' selected='selected'><?php echo $WidgetTypeValue?></option><!--<option value="Bookmark">Bookmark</option> Removed because duplicated by default value in line above-->
                            <option value="IFrame">IFrame</option>
                            <option value="Collapseable IFrame">Collapseable IFrame</option>
                            <option value="Notes">Notes</option>
                            <option value="HTMLEmbed">HTMLEmbed</option>
							<option value="SQLServerScalarQuery">SQLServerScalarQuery</option>
                        </select><br />
                        <label>Widget URL: </label><input ID="txtWidgetURL" name="URL" value="<?php echo $WidgetURLValue; ?>"></input><br />
                        <label>Display Text: </label><input ID="txtWidgetDisplayText" name="DisplayText" value="<?php echo $WidgetDisplayText; ?>"></input><br /><hr>
                        <button type="button" style="margin-left:5px" onclick="init()">Set Position & Size</button><br />
                        <label>PositionX: </label><input ID="txtpositionx" Text="0" name="PositionX" value="<?php echo $WidgetPositionX; ?>"></input><br />
                        <label>PositionY: </label><input ID="txtpositiony" Text="0" name="PositionY" value="<?php echo $WidgetPositionY; ?>"></input><br />
                        <label>SizeX: </label><input ID="txtsizeX" Text="0" name="SizeX" value="<?php echo $WidgetSizeX; ?>"></input><br />
                        <label>SizeY: </label><input ID="txtsizeY" Text="0" name="SizeY" value="<?php echo $WidgetSizeY; ?>"></input><br />
                        <label>CSS Class: </label><input ID="txtCSSClass" name="CSSClass" value="<?php echo $WidgetCSSClass; ?>"></input><br />
                        Notes/HTML Embed: <input ID="txtNotes"  TextMode="MultiLine" name="Notes" value="<?php echo $WidgetNotes; ?>"></input><br />
						Edit Widget ID: <input ID="txtWidgetID" name="ID" value="<?php echo $WidgetID; ?>"></input><br />
						Dashboard ID: <input ID="txtDashboardID" name="dashboardID" value="<?php echo $dashboardid; ?>"></input><br />
						SQL Server Address<input ID="SQLServerAddressName" name="SQLServerAddressName" value="<?php ?>"></input><br />
						SQL DBName<input ID="SQLDBName" name="SQLDBName" value="<?php ?>"></input><br />
						SQLServer Username: (Empty for windows / SQLite auth) <input ID="sqluser" name="sqluser" value="<?php  ?>"></input><br />
						SQLServer PW: <input ID="sqlpass" name="sqlpass" value="<?php  ?>"></input><br />
						SQL Query: <input ID="sqlquery" name="sqlquery" value="<?php  ?>"></input><br />
                        <button id="btnSubmitNewWidget">Submit</button>
                    </div>
		</div></form>

			<!--Edit CSS Box--><form id="form1" method="POST" action="SaveStyling.php">
			<div id="light2" class="white_content" style="right: initial !important; left:0 !important; width:400px !important;">
				<button type="button" style="float: left !important;" onclick="document.getElementById('light2').style.display='none';">Close</button><br />
				<div id="columnd" class="column" style="width: 85% !important; height:100% !important; clear: both; margin: 0 auto;">
					<header >CSS<hr /></header>
					<button ID="btnUpdateCSS" >Save CSS</button><br />
					<textarea ID="txtCSS" name="txtCSS" cols="40" rows="5" style="width:100%; height:80%;"><?php echo $usercss ?></textarea><br />
					Background Image URL: <input ID="backgroundurl" name="backgroundurl" value="<?php echo $dashboardphotourl ?>"></input><br /> 
					Dashboard ID: <input ID="txtDashboardID" name="dashboardID" value="<?php echo $dashboardid; ?>"></input><br />
				</div>
			</div></form>
        </div>
		<form id="form1" method="POST" action="NewDashboard.php">
		<div id="light3" class="white_content" style="right: initial !important; left:0 !important; width:400px !important;">
			
				<header >New Dashboard<hr /></header>
				<br />
				<input ID="dashboardname" name="dashboardname"></input><br />
				<button ID="btnSubmitDashboard">Save Dashboard</button>
		</div>
		</form>
		<script type="text/javascript" src="index.js"></script><br /><a href="logout.php">Log Out</a></body></html>