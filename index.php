<?php include("checkifdbexists.php");
include("actions/logoutredirect.php");

// Widget Types To Include on New Widget dialog. Moved here for convenience in hiding one or multiple. 
$New_Widget_Dropdown_Options = "<option value='IFrame'>IFrame</option>
<option value='Collapseable IFrame'>Collapseable IFrame</option>
<option value='Notes'>Notes</option>
<option value='HTMLEmbed'>HTMLEmbed</option>
<option value='SQLServerScalarQuery'>SQLServerScalarQuery</option>
<option value='SQLiteResultsList'>SQLiteResultsList</option>"


?>
<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="stylesheet" href="config/globalcss.css">
<script type="module" src="js/md-block.js"></script>
<!--<script src="/js/textboxes.js"></script>-->
<title>Dashboardify</title><link type="text/css" rel="stylesheet" href="css/index.css">
    <style>
		.menubar {
			float: left !important;

		}
	<?php include("usercss.php"); //Load user-defined CSS for page from DB. Moved to file to make this file easier to read, as this code has long been finished. ?>
	md-block:not([rendered]) { display: none }
	</style></head>
<body id='dashboardcontent'>
	
        <div>
			<!--Buttons at top left-->
            <button type="button" class="menubar" onclick="document.getElementById('NewWidgetDialog').style.display='block';document.getElementById('fade').style.display='block'">New Widget</button>
            <button type="button" class="menubar" onclick="document.getElementById('cssEditorBox').style.display='block';">Edit CSS</button>
            <button type="button" class="menubar" onclick="document.getElementById('NewDashboardDialog').style.display='block';">New Dashboard</button>
            <button type="button" class="menubar" onclick="var all = document.getElementsByClassName('editbuttons'); for (var i = 0; i < all.length; i++) {all[i].style.display = 'initial';}">Edit Widgets</button>
			<button><a class='nodeco menubar' href="cachedpage.html">Cached Page</a></button>
			<button><a class='nodeco menubar' href="index.php">Main Page / Reload Cache</a></button>
			<button><a type="button" class="menubar" onclick="document.getElementById('EditDashboardDialog').style.display='block';">Edit Dashboard</button>
			<button><a class="menubar nodeco" href="actions/logout.php">Log Out</a></button>
			<?php //Check for dashboards for user; Create first dashboard if none exist, then load any widgets found for dashboard if exists.
				include_once("shared_functions.php");
				include("config/check_admin.php");
				//debuglog("shared functions module loaded.");
				$db_file = new PDO('sqlite:Dashboardify.s3db'); // Connect to SQLite database file.
				$sessionid = $_COOKIE["SessionID"]; debuglog($sessionid, "SessionID"); //Get User for Session ID
				$userid = selectquery("Select UserID From Sessions Where SessionID = '" . $sessionid . "'")[0]["UserID"]; debuglog($userid, "User ID found for user");
				if (isadmin($userid)) { // Display Setup button if user is an admin. 
					echo "<button type='button' style='float:left !important;''><a class='nodeco' href='setup.php'>Setup</a></button>";
				}
				echo "<script>localStorage.setItem('userID', '$userid');</script>";
				$dashboards = selectquery("Select * From Dashboards Where UserID = '" . $userid . "'"); // Query for dashboards for user. 
				$dashboardname = "";
				$dashboardid = "";
				$embeddable = "";
				$dashboardphotourl = ""; // Used later at end of script to pre-populate value into input for CSS box.
				Try { // Load Dashboard list
					$count = count($dashboards);
					debuglog($count, "Count variable");
					if ((int)$count == 1) {
						foreach($dashboards as $row) {
							debuglog($dashboards, "Dashboards Array - Single Result"); 
							$dashboardid = $dashboards[0]["DashboardID"]; 
							debuglog($dashboardid, "Selected Dashboard ID."); 
							$dashboardphotourl = $row["BackgroundPhotoURL"]; 
							$usercss = $row["CustomCSS"];
							$dashboardname = $row["Name"];
							$embeddable = $row["Embeddable"];
						}
					} elseif ((int)$count > 1) {
						debuglog($dashboards, "Dashboards Array - Multiple Results"); 
						echo "<label> Dashboard: </label><select ID='ddlSelectedDashboard' name='SelectedDashboard' onchange='loadselecteddashboard()'>";
						$match = 0;
						$matcheddashboardid = -1;
						If (Isset($_GET["SelectDashboardID"])) { $matcheddashboardid = $_GET["SelectDashboardID"];}

						foreach($dashboards as $row) {
							$recid = $row["DashboardID"];
							if ($row["DefaultDB"] == "Y") {
								$dashboardid = $recid;
								$dashboardname = $row["Name"];
								$embeddable = $row["Embeddable"];
								$dashboardphotourl = $row["BackgroundPhotoURL"];
								$usercss = $row["CustomCSS"];
								debuglog($dashboardid, "Selected Dashboard ID.");
								
								echo "<option value='" . $row["DashboardID"] . "'";
								If ($recid == $matcheddashboardid){echo "selected='selected'";} elseif ($matcheddashboardid <> -1) {} else {echo "selected='selected'";}
								echo ">" . $row["Name"] . "</option>";

							} else {
								echo "<option value='" . $row["DashboardID"] . "'";
								If ($recid == $matcheddashboardid){echo "selected='selected'";}
								echo ">" . $row["Name"] . "</option>";

							}
							if (isset($_GET["SelectDashboardID"])) {
								if ($row["DashboardID"] == $_GET["SelectDashboardID"]) { $match = 1; debuglog(array($match,$matcheddashboardid), "Match / selected db from URL if present?"); }
							}
						}
						echo "</select><br />";

						//debuglog($_GET['SelectDashboardID'], "Selected Dashboard ID from URL");
						If ($match == 1) {$dashboardid = $_GET["SelectDashboardID"];}

					} else { // If dash not found, create one
						$dashboardid = GUID(); 
						$defaultdashimage = "";
						if (file_exists('config/defaultdashboardurl.txt')) {
							$defaultdashimage = file_get_contents('config/defaultdashboardurl.txt');
							$dashboardphotourl = $defaultdashimage;
						}
						$sql1 = "INSERT INTO Dashboards (DashboardID, UserID, DefaultDB, Name, BackgroundPhotoURL) VALUES ('" . $dashboardid . "', '" . $userid 
						. "', 'Y', 'Main', '" . $defaultdashimage . "')";
						debuglog($sql1,"SQL Dashboard Insert Query"); 
						execquery($sql1); //Create Dashboard
						$dashboardname = "Main";
						$embeddable = "";
						// First / Newly Created Dashboard; Populate Global/Default Widgets

						// Query for Global widgets
						$globalwidgets = selectquery("Select * From Widgets Where Global = '1'");
						debuglog($globalwidgets, "Global Widgets List");
						foreach($globalwidgets as $w) {
							
							//Prepare Variables
							$sep = "','"; // Seperator
							$sqlserveraddress = $w["sqlserveraddress"];
							$sqldbname = $w["sqldbname"];
							$sqlusername = $w["sqluser"];
							$sqlpass = $w["sqlpass"];
							$sqlquery = $w["sqlquery"];
							$globaldefault = "0";
							
												
							// Prepare INSERT statement.
							$select = "INSERT INTO Widgets (WidgetType,BookmarkDisplayText,PositionX,PositionY,SizeX,SizeY,WidgetURL,WidgetCSSClass,Notes,DashboardRecID
							,sqlserveraddress,sqldbname,sqluser,sqlpass,sqlquery, Global) 
							VALUES 
							('" 
							. $w["WidgetType"] . $sep . $w["BookmarkDisplayText"] . $sep . $w["PositionX"] 
							. $sep . $w["PositionY"] . $sep . $w["SizeX"] . $sep . $w["SizeY"] . $sep . $w["WidgetURL"] 
							. $sep . $w["WidgetCSSClass"] . $sep . str_replace("'", "''",$w["Notes"]) . $sep . $dashboardid . $sep 
							.$sqlserveraddress . $sep . $sqldbname . $sep . $sqlusername . $sep . $sqlpass . $sep . $sqlquery .
							$sep . $globaldefault . "')";
							debuglog($select, "Query for inserting global widget");
							execquery($select);
							//header('Location: index.php');
							echo "<script>location.reload();</script>";
						}

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
				$siteurl = scalarquery("Select Value From Settings Where Name = 'SiteUrlConfig'", "Value");
				foreach($results as $row) { //Load Widgets, Starting with Re-useable texts
					$editbuttonscss = "<a class='editbuttons' style='display:none;height:24px; width:24px;' href='";
					$imgstylecss = "<img style='height:24px; width:24px;' src='";
					$PositionAndCSSClass = "left: " . $row["PositionX"] . "px; top: " . $row["PositionY"] . "px; width: " . $row["SizeX"] . "px; height: " . $row["SizeY"] . "px; max-width: " . $row["SizeX"] . "px;' class='" . $row["WidgetCSSClass"] . "'>";
					$combined = "<div style='margin:15px; position:absolute; background-color: white;  border: 1px solid black;" . $PositionAndCSSClass . $editbuttonscss . $siteurl . "?EditRecID=" . $row["RecID"] . "&SelectDashboardID=" . $dashboardid . "'>" . $imgstylecss . $siteurl . "icons/edit.png'></img></a>" . $editbuttonscss . $siteurl . "actions/DeleteWidget.php?RecID=" . $row["RecID"] . "'>" . $imgstylecss . $siteurl . "icons/cancel.png'></img></a>";
					$floatingbookmarkPositionAndCSSClass = "left: " . $row["PositionX"] . "px; top: " . $row["PositionY"] . "px;' class='" . $row["WidgetCSSClass"] . "'>";
					$floatingbookmark = "<div id='" . $row["RecID"] . "' class='bookmark' style='position: absolute; width:100px; background-color: lightgrey;  border: 1px solid black; " 
					. $floatingbookmarkPositionAndCSSClass . $editbuttonscss 
					. $siteurl . "?EditRecID=" . $row["RecID"] . "&SelectDashboardID=" 
					. $dashboardid . "'>" . $imgstylecss . $siteurl . "icons/edit.png'></img></a>" . $editbuttonscss 
					. $siteurl . "actions/DeleteWidget.php?RecID=" . $row["RecID"] . "'>" . $imgstylecss . $siteurl . "icons/cancel.png'></img></a>";
					
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
					elseIf (($row["WidgetType"] == "Bookmark") and ($row["PositionX"] != 0 and $row["PositionX"] != "")) { // For Bookmarks with custom positions
						echo $floatingbookmark 
						." <div style='padding: 5px; width: 100%; class='bookmark'"
						. $row["WidgetCSSClass"] 
						. "'><a target='_blank' href='" 
						. $row["WidgetURL"] 
						. "'>" 
						. $row["BookmarkDisplayText"] 
						."</a></div></div>";}
					elseIf ($row["WidgetType"] == "Bookmark" and ($row["PositionX"] == 0 or $row["PositionX"] == "")) {echo "<div id='" . $row["RecID"] . "' style='padding: 5px; margin: 5px; width:100px; background-color: lightgrey;  border: 1px solid black;' class='bookmark" . $row["WidgetCSSClass"] . "'><a target='_blank' href='". $row["WidgetURL"] ."'>". $row["BookmarkDisplayText"] ."</a>" . $editbuttonscss . $siteurl . "?EditRecID=" . $row["RecID"] . "&SelectDashboardID=" . $dashboardid . "'>" . $imgstylecss . $siteurl . "icons/edit.png'></img></a>" . $editbuttonscss . $siteurl . "actions/DeleteWidget.php?RecID=" . $row["RecID"] . "'>" . $imgstylecss . $siteurl . "icons/cancel.png'></img></a></div>";}
					elseIf ($row["WidgetType"] == "IFrame") {echo $combined . "<iframe style='height:100%;width:100%' src='". $row["WidgetURL"] ."'></iframe></a></div>";}
					elseIf ($row["WidgetType"] == "Collapseable IFrame") {
						$combined2 = "<div id='" . $row["RecID"] . "' style='display:none; position:absolute; background-color: white;  border: 1px solid black;" . "width: " . $row["SizeX"] . "px; height: " . $row["SizeY"] . "px; max-width: " . $row["SizeX"] . "px;' class='" . $row["WidgetCSSClass"] . "'>" . $editbuttonscss . $siteurl . "?EditRecID=" . $row["RecID"] . "&SelectDashboardID=" . $dashboardid . "'>" . $imgstylecss . $siteurl . "icons/edit.png'></img></a>" . $editbuttonscss . $siteurl . "actions/DeleteWidget.php?RecID=" . $row["RecID"] . "'>" . $imgstylecss . $siteurl . "icons/cancel.png'></img></a>";
					
						$hidden = "<div id='' class='collapse' style='" . "left: " . $row["PositionX"] . "px; top: " . $row["PositionY"] . "px; width: " . $row["SizeX"] . "px; height: 20px; max-width: " . $row["SizeX"] . "px;'" . "'>";
						echo $hidden . "<a style='border: none !important;' class='collapse' onclick='opencollapsediframe(&quot;" . $row["RecID"] . "&quot;)'>" . $row["BookmarkDisplayText"] . "</a>";
						echo $combined2 . "<iframe style='height:100%;width:100%;' id='" . $row["RecID"] . "/iframe' src2='". $row["WidgetURL"] ."'></iframe></a></div>";
						echo "</div>"; //this wraps combined variable, into a surrounding div.
					}
					elseIf ($row["WidgetType"] == "Notes") {echo $combined . "<p class='note' style='padding-left: 15px; padding-right: 15px;'><md-block>". $row["Notes"] ."</md-block></p></div>";}
					elseIf ($row["WidgetType"] == "HTMLEmbed") {echo $combined . $row["Notes"] ."</div>";}
					elseIf ($row["WidgetType"] == "SQLiteResultsList") {
						// Wrap in a try statement!! Throw the error into debug + the widget. 
						try {
						// Get variables
						If ($row["sqlquery"] == "") {$row["sqlquery"] = "Select 'Query Not Present' as Error";}
						$sqlitedbname = $row["sqldbname"];
						$sqlitequery = $row["sqlquery"];
						debuglog($sqlitequery,"about to execute query");
						$dbpath = 'sqlite:' . $sqlitedbname; // setup proper sqlite DSN pathname from supplied input
						$db_file = new PDO($dbpath);
						$stmt9 = $db_file->prepare($sqlitequery);
						$stmt9->execute();
						$localresults = $stmt9->fetchAll(PDO::FETCH_ASSOC);
						debuglog($localresults,"Query results");
						echo $combined . generateTableFromObjects($localresults) ."</div>";
						} catch (exception $e) {
							
						} finally {
						}
					} // End of SQLite DB 'If' block
					elseIf ($row["WidgetType"] == "Javascript API Request") {
						
					
						// End of Widget Loading for-each list
					} else { // Look for and handle custom widget providers defined in DB!
								// WidgetProviderName
								// CSS_Styling
								// HTML_Content
								// PHP_To_Run
								// CREATE TABLE CustomWidgetProviders (
								// WidgetProviderName TEXT,
								// CSS_Styling TEXT  NULL,
								// HTML_Content TEXT  NULL,
								// PHP_To_Run TEXT  NULL,
							// OK... PHP eval handler test... lets go...
							// At this point we have a 'widget' defined that didn't match any other pre-defined widget
							// 	Types... So we'll see if there's a matching widget handler with this name in the DB.
							
							
							// THE FOLLOWING CODE USES EVAL() WHICH IS DANGEROUS.
							// ONLY ALLOW RUNNING THE PHP PART OF THIS IF USER IS ADMIN?
							$w_provider = $row["WidgetType"];
							$prov = selectquery("Select WidgetProviderName, CSS_Styling, HTML_Content, PHP_To_Run From 
							CustomWidgetProviders Where WidgetProviderName = '" . $w_provider . "'");
							if (isset($prov)) {
								debuglog($prov, "Custom Widget Provider");
								$p = $prov[0];
								$widget_css = $p["CSS_Styling"];
								$w_HTML_Content = $p["HTML_Content"];
								$w_PHP_To_Run = $p["PHP_To_Run"];
								
								
								// Draw the widget first i guess?
								echo $combined;
								if (getAuthMode() == "None") {
									// Bad. 
								}
								if (AmIAdmin()) {
								eval($w_PHP_To_Run);
								} // ONLY RUN EVAL IF I AM ADMIN.
								echo $w_HTML_Content ."</div>";

//https://enplnnfh4ifmp.x.pipedream.net/
							}
				}
			}
			?>
			<!-- New Widget box-->
		<div id="NewWidgetDialog" class="white_content"><form id="form1" method="POST" action="NewWidget.php" >
		<?php   //Check if this is an 'Edit' or 'New' widget submission , set up.  
			$querystring = $_SERVER['QUERY_STRING']; //Get value from URL
			//If EditRecID is in the URL, load details from DB
			If (Isset($_GET["EditRecID"])) {
				$WidgetID = $_GET["EditRecID"];
				$select = "SELECT * FROM Widgets Where RecID = '" . $WidgetID . "'"; // Prepare SELECT statement.
				$results = selectquery($select);
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
						// LOAD SQL FIELDS
						$sqlwidgetquery = $row["sqlquery"];
						$sqlwidgetdbname = $row["sqldbname"];
						$sqlwidgetusername = $row["sqluser"];
						$sqlwidgetpass = $row["sqlpass"];
						$sqlwidgetserveraddress = $row["sqlserveraddress"];
						$globalwidget = $row["Global"];
						}
				}
			} else {$WidgetTypeValue = "Bookmark";$WidgetURLValue = "";$WidgetDisplayText = "";$WidgetPositionX = "";
				$WidgetPositionY = "";$WidgetSizeX = "";$WidgetSizeY = "";$WidgetCSSClass = "";$WidgetNotes = "";
				$WidgetID = "";
				$sqlwidgetquery = ""; $sqlwidgetdbname = ""; $sqlwidgetserveraddress = ""; $sqlwidgetusername = ""; $sqlwidgetpass = ""; $globalwidget = "0";
			} // Prepare unused variables
		?>
                    <button type="button" style="float: left !important;" onclick="document.getElementById('NewWidgetDialog').style.display='none';document.getElementById('fade').style.display='none'">Close</button>
                    <button id="btnSubmitNewWidget">Submit</button>
                    <br />
                    <div id="columnc" class="column" style="width: 85% !important; clear: both; margin: 0 auto;">
                        <header >New Widget<hr /></header>
                        
                        <label>Widget Type: </label><select ID="ddlWidgetType" name="WidgetType" onclick="renderNewWidgetOptionsByDropdown()">
							<option value='<?php echo $WidgetTypeValue?>' selected='selected'><?php echo $WidgetTypeValue?></option><!--<option value="Bookmark">Bookmark</option> Removed because duplicated by default value in line above-->
                            
							<?php
								echo $New_Widget_Dropdown_Options;
								// Populate additional options for Custom Widget Providers
								if (scalarquery("Select Count(*) As Matches From CustomWidgetProviders", "Matches") != 0) {
									$wis = selectquery("Select WidgetProviderName From CustomWidgetProviders");
									foreach ($wis as $widgetprovider) {
										echo "<option value='" . $widgetprovider["WidgetProviderName"] ."'>" . $widgetprovider["WidgetProviderName"] . "</option>";
									}
								}
							?>
                        </select><br />
                        <span id="widgetURL"><label>Widget URL: </label><input ID="txtWidgetURL" name="URL" value="<?php echo $WidgetURLValue; ?>"></input><br /></span>
                        <label>Display Text: </label><input ID="txtWidgetDisplayText" name="DisplayText" value="<?php echo $WidgetDisplayText; ?>"></input><br />
                        <?php if (isadmin($userid)) { 
							$global_checked = "";
							if ($globalwidget == "1") {
								$global_checked = "checked";
							}
							echo "<input type='checkbox'id='GlobalWidgetBool' "  . $global_checked . " name='GlobalDefault' value='1'><label>Global?</label></input>"; } ?>
						<hr><button type="button" style="margin-left:5px" onclick="init()">Set Position & Size</button>
						<br />
                        <label>PositionX: </label><input ID="txtpositionx" Text="0" name="PositionX" value="<?php echo $WidgetPositionX; ?>"></input><br />
                        <label>PositionY: </label><input ID="txtpositiony" Text="0" name="PositionY" value="<?php echo $WidgetPositionY; ?>"></input><br />
                        <label>SizeX: </label><input ID="txtsizeX" Text="0" name="SizeX" value="<?php echo $WidgetSizeX; ?>"></input><br />
                        <label>SizeY: </label><input ID="txtsizeY" Text="0" name="SizeY" value="<?php echo $WidgetSizeY; ?>"></input><br />
                        <label>CSS Class: </label><input ID="txtCSSClass" name="CSSClass" value="<?php echo $WidgetCSSClass; ?>"></input><br />
                        Notes/HTML Embed: <textarea ID="txtNotes" rows="4" cols="50" name="Notes"><?php echo $WidgetNotes; ?></textarea><br />
						<span style="display: none;">Edit Widget ID: <input ID="txtWidgetID" name="ID" value="<?php echo $WidgetID; ?>"></input><br />
						Dashboard ID: <input ID="txtDashboardID" name="dashboardID" value="<?php echo $dashboardid; ?>"></input></span><br />
						
						<span id="SQL">SQL Server Address<input ID="SQLServerAddressName" name="SQLServerAddressName" value="<?php echo $sqlwidgetserveraddress ?>"></input><br />
						SQL DBName<input ID="SQLDBName" name="SQLDBName" value="<?php echo $sqlwidgetdbname ?>"></input><br />
						SQLServer Username: (Empty for windows / SQLite auth) <input ID="sqluser" name="sqluser" value="<?php echo $sqlwidgetusername ?>"></input><br />
						SQLServer PW: <input ID="sqlpass" name="sqlpass" value="<?php echo $sqlwidgetpass ?>"></input><br />
						SQL Query: <input ID="sqlquery" name="sqlquery" value="<?php echo $sqlwidgetquery ?>"></input><br /></span>

                    </div>
		</div></form>

			<!--Edit CSS Box--><form id="form1" method="POST" action="SaveStyling.php">
			<div id="cssEditorBox" class="white_content" style="right: initial !important; left:0 !important; width:400px !important;">
				<button type="button" style="float: left !important;" onclick="document.getElementById('cssEditorBox').style.display='none';">Close</button><br />
				<div id="columnd" class="column" style="width: 85% !important; height:100% !important; clear: both; margin: 0 auto;">
					<header >Dashboard-Specific CSS<hr /></header>
					<button ID="btnUpdateCSS" >Save CSS</button><br />
					<textarea ID="txtCSS" name="txtCSS" cols="40" rows="5" style="width:100%; height:50%;"><?php echo $usercss ?></textarea><br />
					Background Image URL: <input ID="backgroundurl" name="backgroundurl" value="<?php echo $dashboardphotourl ?>"></input><br /> 
					Dashboard ID: <input ID="txtDashboardID" name="dashboardID" value="<?php echo $dashboardid; ?>"></input><br />
				</div>
			</div></form>
        </div>
		<form id="form1" method="POST" action="actions/NewDashboard.php">
		<div id="NewDashboardDialog" class="white_content" style="right: initial !important; left:0 !important; width:400px !important;">
			
				<header >New Dashboard<hr /></header>
				<br />
				<input ID="dashboardname" name="dashboardname"></input><br />
				<button ID="btnSubmitDashboard">Save Dashboard</button>
		</div>
		</form>
		<form id="EditDashboard" method="POST" action="actions/Edit_Dashboard.php?DashboardID=<?php echo $dashboardid; ?>">
		<div id="EditDashboardDialog" class="white_content" style="right: initial !important; left:0 !important; width:400px !important;">
			
				<header >Edit Current Dashboard<hr /></header>
				<br />
				<label>Dashboard Name:</label><br />
				<input ID="dashboardname" name="dashboardname" value="<?php echo $dashboardname ?>"></input><br />
				<input ID="Embeddable" type="checkbox" name="embeddable" <?php if($embeddable == "1") {echo "checked='true'";} ?> value="1"><label for="Embeddable">Embeddable? This will make this dashboard publicly accessible via the following URL:</label><br />
				<p><?php echo $siteurl . "api/embed_dashboard.php?DashboardID=" . $dashboardid; ?></p> 
				<button ID="btnSubmitDashboard">Save Dashboard</button>
		</div>
		</form>
		<script type="text/javascript" src="js/index.js"></script><br /></body><script>localStorage.setItem("dashboardcontent",document.getElementById("dashboardcontent").innerHTML)</script>
		</html>