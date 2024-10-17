<?php //Prior to reaching this point, the DB is loaded into $db_file and an admin check is done 
// to see if we should populate the setup button or not. 
// for reference, check 'loaddb_checkadmin_populate-setup-button.php' under actions/index.
echo "<script>localStorage.setItem('userID', '$userid');</script>"; //Not sure if this is still needed, think leftover debugging item.
$dashboards = selectquery("Select * From Dashboards Where (UserID = '" . $userid . "') Or (Dashboards.OrgRecID In (Select OrgRecID From OrgMemberships Where UserRecID = '" . $userid ."'))"); // Query for dashboards for user or user's org's. 
$dashboardname = "";
$dashboardid = "";
$embeddable = "";
$dashboardphotourl = ""; // Used later at end of script to pre-populate value into input for CSS box.
Try { // Load Dashboard list
    $count = count($dashboards);
    debuglog($count, "Count variable");
    if ((int)$count == 1) { // If there's only one dashboard for the user
        foreach($dashboards as $row) {
            debuglog($dashboards, "Dashboards Array - Single Result"); 
            $dashboardid = $dashboards[0]["DashboardID"]; 
            debuglog($dashboardid, "Selected Dashboard ID."); 
            $dashboardphotourl = $row["BackgroundPhotoURL"]; 
            $usercss = $row["CustomCSS"];
            $dashboardname = $row["Name"];
            $embeddable = $row["Embeddable"];
        }
    } elseif ((int)$count > 1) { // If there's multiple dash's for the user
        debuglog($dashboards, "Dashboards Array - Multiple Results"); 
        echo "<label> Dashboard: </label><select ID='ddlSelectedDashboard' name='SelectedDashboard' onchange='loadselecteddashboard()'>";
        $match = 0;
        $matcheddashboardid = -1;
        If (Isset($_GET["SelectDashboardID"])) { 
        	$matcheddashboardid = $_GET["SelectDashboardID"];
        }
        $last_dashboard_selected_cookie_found = FALSE;
        $last_dashboard_id = "";
        if (isset($_COOKIE['lastselecteddashboardid'])) {
        	$last_dashboard_selected_cookie_found = TRUE;
        	$last_dashboard_id = $_COOKIE['lastselecteddashboardid'];

            //Check if dashboard still exists. If we just deleted it, ignore that it's in the cookie.
            // If it is found, this if statement doesn't do anything and we continue on as usual.
            // Need to update in case of a dashboard that currentuser doesn't directly own, but is owned by the org. 
            if (!DoIHaveAccessToThisDashboard($_GET["SelectDashboardID"])) {
                $last_dashboard_selected_cookie_found = FALSE;
                $last_dashboard_id = "";
                $matcheddashboardid = -1; // Undoes the part earlier where it sets this to _GET value.
            }
        }
        
        $debugdata = " Last Dashboard ID Readin: " . ($last_dashboard_selected_cookie_found ? 'true' : 'false') . ", Last Dashboard ID: " . $_COOKIE['lastselecteddashboardid'];

        
        foreach($dashboards as $row) {
            $recid = $row["DashboardID"];
            if ($row["DefaultDB"] == "Y") {
                $dashboardid = $recid;
                $dashboardname = $row["Name"];
                $embeddable = $row["Embeddable"];
                $dashboardphotourl = $row["BackgroundPhotoURL"];
                $usercss = $row["CustomCSS"];
                debuglog($dashboardid, "Selected Dashboard ID.");
                // Need to check whether we have a value for lastselected dashboard
                // If value exists, we need to select THAT dashboard in the dropdown instead of the default 
                // 
                echo "<option value='" . $row["DashboardID"] . "'";
                If ($recid == $matcheddashboardid){echo "selected='selected'";} elseif ($matcheddashboardid <> -1 || $last_dashboard_selected_cookie_found) {} else {echo "selected='selected'";}
                echo ">" . $row["Name"] . "</option>";

            } else {
                echo "<option value='" . $row["DashboardID"] . "'";
                If ($recid == $matcheddashboardid || ($last_dashboard_selected_cookie_found && $recid == $last_dashboard_id && !isset($_GET['SelectDashboardID'])))
                {
                    // If Recid = matcheddashboard ID, OR (Previous dashboardcookie found AND recid for this option matches last dash id AND we don't have a selection in URL) then
                	echo "selected='selected'";
                	$dashboardphotourl = $row["BackgroundPhotoURL"];
                	$usercss = $row["CustomCSS"];
                }
                echo ">" . $row["Name"] . "</option>";
                
            }
            if (isset($_GET["SelectDashboardID"])) {
            	// If this is a dashboard selected via the changing dropdown, mark that we found a match & collect some data. 
                if ($row["DashboardID"] == $_GET["SelectDashboardID"]) { 
                    $match = 1; 
                    debuglog(array($match,$matcheddashboardid), "Match / selected db from URL if present?"); 
                    $dashboardphotourl = $row["BackgroundPhotoURL"];
                    $usercss = $row["CustomCSS"];
                
                }
            }
        }
        
        If ($matcheddashboardid == -1 && isset($_COOKIE['lastselecteddashboardid'])) { 
            // Updating this to only proceed if the dashboard actually exists, because this broke when we tested deleting dashboards.
            If (DoIHaveAccessToThisDashboard($_COOKIE['lastselecteddashboardid'])) {
        	    $dashboardid = $_COOKIE['lastselecteddashboardid'];
            }
        }
        echo "</select><br />";

        //debuglog($_GET['SelectDashboardID'], "Selected Dashboard ID from URL");
        If ($match == 1) {
        	$dashboardid = $_GET["SelectDashboardID"]; 
        	setcookie('lastselecteddashboardid', $dashboardid, 0, '/');
        }

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
        execquery($sql1); //Create Dashboard // THIS ONE SHOULD BE FINE TO NOT REPLACE FOR SQL INJECTION, NO USER SUPPLIED INPUT.... RIGHT???
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
            $notesvalue = str_replace("'", "''",$w["Notes"]);
                                
            // Prepare INSERT statement.
            $select = "INSERT INTO Widgets (WidgetType,BookmarkDisplayText,PositionX,PositionY,SizeX,SizeY,WidgetURL,WidgetCSSClass,Notes,DashboardRecID
            ,sqlserveraddress,sqldbname,sqluser,sqlpass,sqlquery, Global) 
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            
            //$old = "
            //('" 
            //. $w["WidgetType"] . $sep . $w["BookmarkDisplayText"] . $sep . $w["PositionX"] 
            //. $sep . $w["PositionY"] . $sep . $w["SizeX"] . $sep . $w["SizeY"] . $sep . $w["WidgetURL"] 
            //. $sep . $w["WidgetCSSClass"] . $sep . str_replace("'", "''",$w["Notes"]) . $sep . $dashboardid . $sep 
            //.$sqlserveraddress . $sep . $sqldbname . $sep . $sqlusername . $sep . $sqlpass . $sep . $sqlquery .
            //$sep . $globaldefault . "')";
            //debuglog($select, "Query for inserting global widget");
            
            $localdb = getPDO_DBFile();
			$stmt1 = $localdb->prepare($select);
			$stmt1->bindParam(1, $w["WidgetType"], PDO::PARAM_STR);
			$stmt1->bindParam(2, $w["BookmarkDisplayText"], PDO::PARAM_STR);
			$stmt1->bindParam(3, $w["PositionX"], PDO::PARAM_STR);
			$stmt1->bindParam(4, $w["PositionY"], PDO::PARAM_STR);
			$stmt1->bindParam(5, $w["SizeX"], PDO::PARAM_STR);
			$stmt1->bindParam(6, $w["SizeY"], PDO::PARAM_STR);
			$stmt1->bindParam(7, $w["WidgetURL"], PDO::PARAM_STR);
			$stmt1->bindParam(8, $w["WidgetCSSClass"], PDO::PARAM_STR);
			$stmt1->bindParam(9, $notesvalue, PDO::PARAM_STR);
			$stmt1->bindParam(10, $dashboardid, PDO::PARAM_STR);
			$stmt1->bindParam(11, $sqlserveraddress, PDO::PARAM_STR);
			$stmt1->bindParam(12, $sqldbname, PDO::PARAM_STR);
			$stmt1->bindParam(13, $sqlusername, PDO::PARAM_STR);
			$stmt1->bindParam(14, $sqlpass, PDO::PARAM_STR);
			$stmt1->bindParam(15, $sqlquery, PDO::PARAM_STR);
			$stmt1->bindParam(16, $globaldefault, PDO::PARAM_STR);
			
			$stmt1->execute();
            
            
            // Reload dashboard
            echo "<script>location.reload();</script>";
        }

    } 
} catch (exception $ex) {echo $ex;debuglog($ex,"Exception found during dashboard checks");
    debuglog($ex); echo $ex;
} 
//Try to set background photo and CSS for dashboard
If (isset($dashboardphotourl)) {echo "<style>body {background-image: url('" . $dashboardphotourl . "'); background-size: cover;} " . $usercss . "</style>";}



?>
