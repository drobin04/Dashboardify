<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="stylesheet" href="../config/globalcss.css">
<script type="module" src="../js/md-block.js"></script>
<title>Dashboardify</title><link type="text/css" rel="stylesheet" href="css/index.css">
		<!--Have a php include here that loads dashboard-specific css! -->
<style>
	md-block:not([rendered]) { display: none }
	html {overflow: hidden;}
	</style></head>
<body id='dashboardcontent'>
	
        <div>
						
			<?php //Check for dashboards for user; Create first dashboard if none exist, then load any widgets found for dashboard if exists.
				include_once("../shared_functions.php");
				if (isset($_GET["DashboardID"])) {
				$dashboardid = $_GET["DashboardID"];
				} else {
					echo "<h1>No Dashboard ID Specified For Embed</h1>";
				}
				$dashboardphotourl = "";
				$break_if_dashboard_isnot_embeddable = false;
				Try { 
					$row = selectquery("Select RecID, DefaultDB, CustomCSS, BackgroundPhotoURL, UserID, Name, DashboardID from Dashboards Where DashboardID = '" . $dashboardid . "' And Embeddable = '1'");
					debuglog($row, "Results from DB load from database");
					if (isset($row[0])) {
						$row = $row[0];
						$dashboardphotourl = $row["BackgroundPhotoURL"]; 
						$dashboardcss = $row["CustomCSS"];
					} else {
						$break_if_dashboard_isnot_embeddable = true;
						echo "<h1>No Dashboard Found. Verify you have the right ID and/or that this dashboard is embeddable.";
						exit();

					}
					
				} catch (exception $ex) {
					echo $ex;
				} 
				//Try to set background photo and CSS for dashboard
				If (isset($dashboardphotourl)) {echo "<style>body {background-image: url('" . $dashboardphotourl . "'); background-size: cover;} " . $dashboardcss . "</style>";}

				// Load Widgets For Selected Dashboard
				$select = "SELECT * FROM Widgets Where DashboardRecID = '" . $dashboardid . "'"; debuglog($select,"Query for widgets");
				$results = selectquery($select);
				debuglog($results, "Widget results");
				foreach($results as $row) { //Load Widgets, Starting with Re-useable texts
					$editbuttonscss = "";
					$imgstylecss = "";
					$PositionAndCSSClass = "left: " . $row["PositionX"] . "px; top: " . $row["PositionY"] . "px; width: " . $row["SizeX"] . "px; height: " . $row["SizeY"] . "px; max-width: " . $row["SizeX"] . "px;' class='" . $row["WidgetCSSClass"] . "'>";
					$combined = "<div style='margin:15px; position:absolute; background-color: white;  border: 1px solid black;" . $PositionAndCSSClass;
					$floatingbookmarkPositionAndCSSClass = "left: " . $row["PositionX"] . "px; top: " . $row["PositionY"] . "px;' class='" . $row["WidgetCSSClass"] . "'>";
					$floatingbookmark = "<div id='" . $row["RecID"] . "' class='bookmark' style='position: absolute; width:100px; background-color: lightgrey;  border: 1px solid black; " 
					. $floatingbookmarkPositionAndCSSClass;
					
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
					If (($row["WidgetType"] == "Bookmark") and ($row["PositionX"] != 0 and $row["PositionX"] != "")) { // For Bookmarks with custom positions
						echo $floatingbookmark 
						." <div style='padding: 5px; width: 100%; class='bookmark'"
						. $row["WidgetCSSClass"] 
						. "'><a target='_blank' href='" 
						. $row["WidgetURL"] 
						. "'>" 
						. $row["BookmarkDisplayText"] 
						."</a></div></div>";};

						//For bookmarks to be lumped together on the left -->
					If ($row["WidgetType"] == "Bookmark" and ($row["PositionX"] == 0 or $row["PositionX"] == "")) {echo "<div id='" . $row["RecID"] . "' style='padding: 5px; margin: 5px; width:100px; background-color: lightgrey;  border: 1px solid black;' class='bookmark" . $row["WidgetCSSClass"] . "'><a target='_blank' href='". $row["WidgetURL"] ."'>". $row["BookmarkDisplayText"] ."</a></div>";};
					If ($row["WidgetType"] == "IFrame") {echo $combined . "<iframe style='height:100%;width:100%' src='". $row["WidgetURL"] ."'></iframe></a></div>";}
					If ($row["WidgetType"] == "Collapseable IFrame") {
						$combined2 = "<div id='" . $row["RecID"] . "' style='display:none; position:absolute; background-color: white;  border: 1px solid black;" . "width: " . $row["SizeX"] . "px; height: " . $row["SizeY"] . "px; max-width: " . $row["SizeX"] . "px;' class='" . $row["WidgetCSSClass"] . "'>";
					
						$hidden = "<div id='' class='collapse' style='" . "left: " . $row["PositionX"] . "px; top: " . $row["PositionY"] . "px; width: " . $row["SizeX"] . "px; height: 20px; max-width: " . $row["SizeX"] . "px;'" . "'>";
						echo $hidden . "<a style='border: none !important;' class='collapse' onclick='opencollapsediframe(&quot;" . $row["RecID"] . "&quot;)'>" . $row["BookmarkDisplayText"] . "</a>";
						echo $combined2 . "<iframe style='height:100%;width:100%;' id='" . $row["RecID"] . "/iframe' src2='". $row["WidgetURL"] ."'></iframe></a></div>";
						echo "</div>"; //this wraps combined variable, into a surrounding div.
					}
					If ($row["WidgetType"] == "Notes") {echo $combined . "<p class='note' style='padding-left: 15px; padding-right: 15px;'><md-block>". $row["Notes"] ."</md-block></p></div>";}
					If ($row["WidgetType"] == "HTMLEmbed") {echo $combined . $row["Notes"] ."</div>";}
					If ($row["WidgetType"] == "SQLiteResultsList") {
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
				} // End of Widget Loading for-each list
			?>
        </div>
		</form>
		<script type="text/javascript" src="js/index.js"></script><br /></body></script>
		</html>