<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("actions/logoutredirect.php");
include_once("shared_functions.php");

// Widget Types To Include on New Widget dialog. Moved here for convenience in hiding one or multiple. 
$New_Widget_Dropdown_Options = "
<option value='Bookmark'>Bookmark</option>
<option value='IFrame'>IFrame</option>
<option value='Collapseable IFrame'>Collapseable IFrame</option>
<option value='Notes'>Notes</option>
<option value='HTMLEmbed'>HTMLEmbed</option>
<option value='Countdown'>Countdown</option>
<option value='CountUp_Hours'>Count Up- Hours Since Last</option>
<option value='CountUp_Days'>Count Up- Days Since Last</option>
"
?>
<!--
Removed following options from New_Widget_Dropdown_Options: 
<option value='SQLServerScalarQuery'>SQLServerScalarQuery</option>
<option value='SQLiteResultsList'>SQLiteResultsList</option>
<option value='SQLite Chart (PHPGD)'>SQLite Chart (PHPGD)</option>
-->
<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>
<link rel="stylesheet" href="config/globalcss.css">
<link rel="stylesheet" href="css/settings_flyout.css">
<script type="module" src="js/md-block.js"></script>
<script type="text/javascript" src="js/index.js"></script>
<script type="text/javascript" src="js/settings_flyout.js"></script>
<title>Dashboardify</title><link type="text/css" rel="stylesheet" href="css/index.css">
    <style>
		/*.repositioning_widget {
            text-decoration: underline;
            cursor: pointer;
            user-select: none; /* Prevents text selection */
        }*/
    
	<?php include("usercss.php"); //Load user-defined CSS for page from DB. Moved to file to make this file easier to read, as this code has long been finished. ?>
	md-block:not([rendered]) { display: none }
	</style></head>
<body id='dashboardcontent'>
        <div>
			<!--Buttons at top left-->
            <button type="button" class="menubar" onclick='toggleDisplay("NewWidgetDialog2");drawNewWidgetBasedOnType();'>New Widget</button>
            <button type="button" class="menubar" onclick="var all = toggleDisplayByClass('editbuttons');">Edit Widgets</button>
            <button type="button" class="menubar" onclick='toggleDisplay("NewDashboardDialog");'>New Dashboard</button>
			<button><a id="changepassword" class="menubar nodeco" href="actions/change_password.php">Change Password</a></button>
			<button id="settingsLink"><a href="#" id="settingsLink2">Settings</a></button>	
			<!--Settings Flyout Menu-->
			<div id="settingsMenu" class="hidden">
				<button><a id="editmode" class="menubar nodeco">Move Widgets</button>
				<button><a id="resizewidgets" class="menubar nodeco">Resize Widgets</a></button>
				<?php 
					doesDatabaseExist() ? null : redirect('setup.php'); // Redirect IF DB DOESNT EXIST
					include("config/check_admin.php"); // this needs to be here before next line
					include('actions/index/loaddb_checkadmin_populate-setup-button.php'); 
				?>
				<button type="button" class="menubar" onclick='toggleDisplay("cssEditorBox");'>Edit CSS</button><br />
				<button><a type="button" class="menubar" onclick="toggleDisplay('EditDashboardDialog');">Edit Dashboard</button>
				<button><a class="menubar nodeco" href="actions/logout.php">Log Out</a></button>
			</div>
			<?php //Check for dashboards for user; Create first dashboard if none exist, then load any widgets found for dashboard if exists.				
				// Load and Prep Dashboard
				// Renders setup button here >>
				include('actions/index/load_and_prep_dashboard.php');
				// Lots of code here.
			?>
			<!-- Begin Widget Loading -->
			<div id="widgetcontainer">	
			<script>getWidgetsForDashboard('<?php echo $dashboardid; ?>');</script>
			<?php
				//
				//
				//
				// Load Widgets For Selected Dashboard
				$select = "SELECT * FROM Widgets Where DashboardRecID = '" . $dashboardid . "'"; 
				$stmt = $db_file->prepare($select); $stmt->execute();
				$results = $stmt->fetchAll(PDO::FETCH_ASSOC); 
				$siteurl = scalarquery("Select Value From Settings Where Name = 'SiteUrlConfig'", "Value");
				foreach($results as $row) { //Load Widgets, Starting with Re-useable texts
					// import Widget_Handling.php
					include('actions/index/Widget_Handling.php');
					// Lots of code here.
					//
				}
			?>
			</div>
											
		<!-- Edit/New Widget Dialog -->
		<div id="NewWidgetDialog2" class="white_content">
		<form id="form_ExperimentalNewWidgetDialog" method="POST" action="NewWidget.php" >
                    <button type="button" style="float: left !important;" onclick="document.getElementById('NewWidgetDialog2').style.display='none';">Close</button>
                    <button id="btnSubmitNewWidget">Submit</button>
                    <!--<button onclick='' type="button">Render</button>-->
                    <br />
                    <header >New Widget<hr /></header>
                    <label>Widget Type: </label>
                    <div class="column" style="width: 85% !important; clear: both; margin: 0 auto;">
                    
                    <select ID="ddlWidgetType2" name="WidgetType" onchange="drawNewWidgetBasedOnType()">
                        	<!-- Option has to get submitted w/ html form. So keep this inside the <form> element, ideally above the pasted section below -->
                    		<!--default value = Bookmark-->        
							<?php
								echo $New_Widget_Dropdown_Options; // Populated from beginning of page, for convenient editing / switching on/off until we get a config editor for this
								// Populate additional options for Custom Widget Providers
								if (scalarquery("Select Count(*) As Matches From CustomWidgetProviders", "Matches") != 0) {
									$wis = selectquery("Select WidgetProviderName From CustomWidgetProviders");
									foreach ($wis as $widgetprovider) {
										echo "<option value='" . $widgetprovider["WidgetProviderName"] ."'>" . $widgetprovider["WidgetProviderName"] . "</option>";
									}
								}
							?>
                        </select><br />
                        
						<br /><input type='checkbox'id='GlobalWidgetBool' name='GlobalDefault' value='1'><label>Create as Default widget for new users?</label></input>
						<br /><input type='checkbox'id='StoredWidgetBool'  name='storedwidgetbool' value='1'><label>Create as a re-usable, stored widget that can be selected from a list?</label></input>
						<!-- FORM CONTENT SHOULD GET PASTED HERE -->
						<div id="NewWidget_Form">
						<!-- This section intentionally left empty, as it gets pasted into / filled by code-->
						</div>
					<!-- Used to store Dashboard ID-->
					<span id="WidgetAndDashboardIDSpan" style="display: none;">Edit Widget ID: <input ID="txtWidgetID" name="ID" ></input><br />
					Dashboard ID: <input ID="txtDashboardID" name="dashboardID" value="<?php echo $dashboardid; ?>"></input></span><br />

					</div>
		</div></form>
		<!-- End of New/Edit Widget Dialog-->
		                          
			<!--Edit CSS Box-->
			<form id="form1" method="POST" action="SaveStyling.php">
			<div id="cssEditorBox" class="white_content" style="right: initial !important; left:0 !important; width:400px !important;">
				<button type="button" style="float: left !important;" onclick="document.getElementById('cssEditorBox').style.display='none';">Close</button><br />
				<div id="columnd" class="column" style="width: 85% !important; height:100% !important; clear: both; margin: 0 auto;">
					<header >Dashboard-Specific CSS<hr /></header>
					<button ID="btnUpdateCSS" >Save CSS</button><br />
					<textarea ID="txtCSS" name="txtCSS" cols="40" rows="5" style="width:100%; height:50%;"><?php echo $usercss ?></textarea><br />
					Background Image URL: <input ID="backgroundurl" name="backgroundurl" value="<?php echo $dashboardphotourl ?>"></input><br /> 
					Dashboard ID: <input ID="txtDashboardID" name="dashboardID" value="<?php echo $dashboardid; ?>"></input><br />
					User ID: <?php echo getCurrentUserID() ?>
					User Email: <?php echo getCurrentUserEmail() ?>
				</div>
			</div></form>
        </div>
		<form id="form1" method="POST" action="actions/NewDashboard.php">
		<div id="NewDashboardDialog" class="white_content" style="right: initial !important; left:0 !important; width:200px !important; height:150px;">
			
				<header >New Dashboard<hr /></header>
				<br />
				<input ID="dashboardname" name="dashboardname"></input><br />
				<button ID="btnSubmitDashboard">Save Dashboard</button>
		</div>
		</form>
		<form id="EditDashboard" method="POST" action="actions/Edit_Dashboard.php?DashboardID=<?php echo $dashboardid; ?>">
		<div id="EditDashboardDialog" class="white_content" style="right: initial !important; left:0 !important; width:450px; height: 300px;">
			
				<header >Edit Current Dashboard<hr /></header>
				<br />
				<label>Dashboard Name:</label><br />
				<input ID="dashboardname" name="dashboardname" value="<?php echo $dashboardname ?>"></input><br />
				<input ID="Embeddable" type="checkbox" name="embeddable" <?php if($embeddable == "1") {echo "checked='true'";} ?> value="1"><label for="Embeddable">Embeddable? This will make this dashboard publicly accessible via the following URL:</label><br />
				<p><?php echo $siteurl . "api/embed_dashboard.php?DashboardID=" . $dashboardid; ?></p> 
				<button ID="btnSubmitDashboard">Save Dashboard</button>
		</div>
		</form>
		<script>localStorage.setItem("dashboardcontent",document.getElementById("dashboardcontent").innerHTML)</script>
<script>
        
    </script>
		</body>
		</html>