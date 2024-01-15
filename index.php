<?php
include("actions/logoutredirect.php");


// Widget Types To Include on New Widget dialog. Moved here for convenience in hiding one or multiple. 
$New_Widget_Dropdown_Options = "<option value='IFrame'>IFrame</option>
<option value='Collapseable IFrame'>Collapseable IFrame</option>
<option value='Notes'>Notes</option>
<option value='HTMLEmbed'>HTMLEmbed</option>
<option value='SQLServerScalarQuery'>SQLServerScalarQuery</option>
<option value='SQLiteResultsList'>SQLiteResultsList</option>
<option value='SQLite Chart (PHPGD)'>SQLite Chart (PHPGD)</option>
<option value='Countdown'>Countdown</option>

"
?>

<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>
<link rel="stylesheet" href="config/globalcss.css">
<script type="module" src="js/md-block.js"></script>
<title>Dashboardify</title><link type="text/css" rel="stylesheet" href="css/index.css">
    <style>
	<?php include("usercss.php"); //Load user-defined CSS for page from DB. Moved to file to make this file easier to read, as this code has long been finished. ?>
	md-block:not([rendered]) { display: none }
	</style></head>
<body id='dashboardcontent'>
        <div>
			<!--Buttons at top left-->
            <!--<button type="button" class="menubar" onclick='toggleDisplay("NewWidgetDialog");'>New Widget</button> -->
            <button type="button" class="menubar" onclick='toggleDisplay("NewWidgetDialog2");drawNewWidgetBasedOnType();'>New Widget</button>
            <button type="button" class="menubar" onclick='toggleDisplay("cssEditorBox");'>Edit CSS</button>
            <button type="button" class="menubar" onclick='toggleDisplay("NewDashboardDialog");'>New Dashboard</button>
            <button type="button" class="menubar" onclick="var all = toggleDisplayByClass('editbuttons');">Edit Widgets</button>
			<button><a class='nodeco menubar' href="cachedpage.html">Cached Page</a></button>
			<button><a class='nodeco menubar' href="index.php">Main Page / Reload Cache</a></button>
			<button><a type="button" class="menubar" onclick="toggleDisplay('EditDashboardDialog');">Edit Dashboard</button>
			<button><a class="menubar nodeco" href="actions/logout.php">Log Out</a></button>
			<button><a id="editmode" class="menubar nodeco">Move Widgets</button>
			<button><a id="resizewidgets" class="menubar nodeco">Resize Widgets</a></button>
			<button><a id="changepassword" class="menubar nodeco" href="actions/change_password.php">Change Password</a></button>
			<!--<button type="button" class="menubar" onclick='toggleDisplay("NewWidgetDialog2");drawNewWidgetBasedOnType();'>New Widget (Experimental)</button> -->
			<?php //Check for dashboards for user; Create first dashboard if none exist, then load any widgets found for dashboard if exists.
				include_once("shared_functions.php");
				doesDatabaseExist() ? null : redirect('setup.php'); // Redirect IF DB DOESNT EXIST
				include("config/check_admin.php");
				
				// Load and Prep Dashboard
				include('actions/index/load_and_prep_dashboard.php');
				// Lots of code here.
				//
				//
				//
				//
				// Load Widgets For Selected Dashboard
				$select = "SELECT * FROM Widgets Where DashboardRecID = '" . $dashboardid . "'"; debuglog($select,"Query for widgets");
				$stmt = $db_file->prepare($select); $stmt->execute();
				$results = $stmt->fetchAll(PDO::FETCH_ASSOC); debuglog($results, "Widget results");
				$siteurl = scalarquery("Select Value From Settings Where Name = 'SiteUrlConfig'", "Value");
				foreach($results as $row) { //Load Widgets, Starting with Re-useable texts
					// import Widget_Handling.php
					include('actions/index/Widget_Handling.php');
					// Lots of code here.
					//
					//
				}
			
			?>
			<!-- Original (DEPRECATED) New Widget box; Now only using for EDITS! -->
			<div id="NewWidgetDialog" class="white_content">
				<form id="form1" method="POST" action="NewWidget.php" >
					<?php   //Check if this is an 'Edit' or 'New' widget submission , set up.  
						// NEW WIDGET FORM
						
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
									//$WidgetNotes = $row["Notes"];
									$WidgetNotes = str_replace("''", "'", $row["Notes"]);
				
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
					<header >New Widget<hr /></header>
					<label>Widget Type: </label><select ID="ddlWidgetType" name="WidgetType" onclick="renderNewWidgetOptionsByDropdown()">
						
					<div id="columnc" class="column" style="width: 85% !important; clear: both; margin: 0 auto;">
						
							<!-- Need to identify what to do with this... Option has to get submitted w/ html form. -->
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
							echo "<input type='checkbox'id='GlobalWidgetBool' "  . $global_checked . " name='GlobalDefault' value='1'><label>Create as Default widget for new users?</label></input>"; 
							echo "<br /><input type='checkbox'id='StoredWidgetBool' "  . "" . " name='storedwidgetbool' value='1'><label>Create as a re-usable, stored widget that can be selected from a list?</label></input>"; } ?>
						<hr><!--<button type="button" style="margin-left:5px" onclick="init()">Set Position & Size</button>-->
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
						<br />
						</label>If you use GD / PHP Chart widget type, manually specify 'x' and 'y' column aliases for the x and y axis'.</label>
					</div><!-- End of 'columnc' div -->
				</form><!-- End of original new widget form -->
			</div><!-- End of 'NewWidgetDialog' div-->

		<!-- New Test Version of New Widget Dialog -->
		
		<div id="NewWidgetDialog2" class="white_content">
		<form id="form_ExperimentalNewWidgetDialog" method="POST" action="NewWidget.php" >
		<?php   //Check if this is an 'Edit' or 'New' widget submission , set up.  
			// NEW WIDGET FORM
			// NEW WIDGET FORM
			
			$querystring = $_SERVER['QUERY_STRING']; //Get value from URL
			//If EditRecID is in the URL, load details from DB
			
		?>
                    <button type="button" style="float: left !important;" onclick="document.getElementById('NewWidgetDialog2').style.display='none';">Close</button>
                    <button id="btnSubmitNewWidget">Submit</button>
                    <!--<button onclick='' type="button">Render</button>-->
                    <br />
                    <header >New Widget<hr /></header>
                    <label>Widget Type: </label>
                    <div class="column" style="width: 85% !important; clear: both; margin: 0 auto;">
                    
                    
                    <select ID="ddlWidgetType2" name="WidgetType" onchange="drawNewWidgetBasedOnType()">
					                       
                        	<!-- Option has to get submitted w/ html form. So keep this inside the <form> element, ideally above the pasted section below -->
                    		<option value='<?php echo $WidgetTypeValue?>' selected='selected'><?php echo $WidgetTypeValue?></option><!--default value = Bookmark-->        
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
                        <?php if (isadmin($userid)) { 
							$global_checked = "";
							if ($globalwidget == "1") {
								$global_checked = "checked";
							}
						echo "<br /><input type='checkbox'id='GlobalWidgetBool' "  . $global_checked . " name='GlobalDefault' value='1'><label>Create as Default widget for new users?</label></input>"; 
						echo "<br /><input type='checkbox'id='StoredWidgetBool' "  . "" . " name='storedwidgetbool' value='1'><label>Create as a re-usable, stored widget that can be selected from a list?</label></input>"; } ?>

						<!-- FORM CONTENT SHOULD GET PASTED HERE -->
						<div id="NewWidget_Form">
						<!-- This section intentionally left empty, as it gets pasted into / filled by code-->
						
						</div>
					<!-- Used to store Dashboard ID-->
					<span style="display: none;">Edit Widget ID: <input ID="txtWidgetID" name="ID" value="<?php echo $WidgetID; ?>"></input><br />
					Dashboard ID: <input ID="txtDashboardID" name="dashboardID" value="<?php echo $dashboardid; ?>"></input></span><br />

					</div>
		</div></form>

		
		<!-- End of new Test version of New Widget Dialog
		                                  
		
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
		<script type="text/javascript" src="js/index.js"></script><br />
	
	
	
		<script>
			  

  


		</script>
		<script>


</script>
	
	</body><script>localStorage.setItem("dashboardcontent",document.getElementById("dashboardcontent").innerHTML)</script>

		
		</html>