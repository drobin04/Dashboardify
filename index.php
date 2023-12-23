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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>
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
            <button type="button" class="menubar" onclick='toggleDisplay("NewWidgetDialog");'>New Widget</button>
            <button type="button" class="menubar" onclick='toggleDisplay("cssEditorBox");'>Edit CSS</button>
            <button type="button" class="menubar" onclick='toggleDisplay("NewDashboardDialog");'>New Dashboard</button>
            <button type="button" class="menubar" onclick="var all = toggleDisplayByClass('editbuttons');">Edit Widgets</button>
			<button><a class='nodeco menubar' href="cachedpage.html">Cached Page</a></button>
			<button><a class='nodeco menubar' href="index.php">Main Page / Reload Cache</a></button>
			<button><a type="button" class="menubar" onclick="toggleDisplay('EditDashboardDialog');">Edit Dashboard</button>
			<button><a class="menubar nodeco" href="actions/logout.php">Log Out</a></button>
			<button><a id="editmode" class="menubar nodeco">Move Widgets</button>
			<button><a id="resizewidgets" class ="menubar nodeco">Resize Widgets</button>
			<?php //Check for dashboards for user; Create first dashboard if none exist, then load any widgets found for dashboard if exists.
				include_once("shared_functions.php");
				include("config/check_admin.php");
				
				// Load and Prep Dashboard
				include('actions/index/load_and_prep_dashboard.php');

				// Load Widgets For Selected Dashboard
				$select = "SELECT * FROM Widgets Where DashboardRecID = '" . $dashboardid . "'"; debuglog($select,"Query for widgets");
				$stmt = $db_file->prepare($select); $stmt->execute();
				$results = $stmt->fetchAll(PDO::FETCH_ASSOC); debuglog($results, "Widget results");
				$siteurl = scalarquery("Select Value From Settings Where Name = 'SiteUrlConfig'", "Value");
				foreach($results as $row) { //Load Widgets, Starting with Re-useable texts
					// import Widget_Handling.php
					include('actions/index/Widget_Handling.php');
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