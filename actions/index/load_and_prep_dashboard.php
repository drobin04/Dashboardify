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
    if ((int)$count == 1) 
    { // If there's only one dashboard for the user
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
            if (isset($_GET["SelectDashboardID"])){
                if (!DoIHaveAccessToThisDashboard($_GET["SelectDashboardID"])) {
                    $last_dashboard_selected_cookie_found = FALSE;
                    $last_dashboard_id = "";
                    $matcheddashboardid = -1; // Undoes the part earlier where it sets this to _GET value.
                }
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
                If ($recid == $matcheddashboardid)
                {echo "selected='selected'";} 
                elseif ($matcheddashboardid <> -1 || $last_dashboard_selected_cookie_found) 
                {} else 
                { // What on earth conditions is this triggering for? 
                    // This triggers if recid is not 'matched dashboard id', which would be matched by URL selector for dashboard ID...
                    // Also if matcheddashboardID is NOT SET, AND the 'last dashboard selected cookie' is NOT found...
                    // So... Is this supposed to select a dashboard if we aren't selecting a dashboard manually, and aren't selecting the 'last' dashboard we were using?
                    // If so, we need to figure out a way to check that we don't inadvertently mark this for 'shared' dashboards, as there's a bug with that. 
                    echo "selected='selected' x_data_1='x'";
                    // So 'Home' (Default dashboard) is confirmed getting selected by this one.
                    // Looks like the shared DB is ALSO getting marked by this one.
                    // I suspect maybe this is happening when I test, because perhaps the 'last dashboard selected cookie found' is still holding my previous dashboard ID 
                    // And maybe it's not getting cleared on logout.
                    // Confirmed - Need to clear that cookie on logout.
                }
                echo ">" . $row["Name"] . "</option>";

            } else {
                //If not 'DefaultDB'
                echo "<option value='" . $row["DashboardID"] . "'";
                If ($recid == $matcheddashboardid || ($last_dashboard_selected_cookie_found && $recid == $last_dashboard_id && !isset($_GET['SelectDashboardID'])))
                {
                    // If Recid = matcheddashboard ID, OR (Previous dashboardcookie found 
                    // AND recid for this option matches last dash id AND we don't have a selection in URL) then
                	echo "selected='selected' x_data_1='y'"; // Some extra junk added just so i can 'see' which code path is being used to mark selected.
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

    } else { 
        // If dash not found, create one
        include('create_first_dashboard.php');
    }

     
} catch (exception $ex) {
    echo $ex;debuglog($ex,"Exception found during dashboard checks");
    debuglog($ex); echo $ex;
} 
//Try to set background photo and CSS for dashboard
If (isset($dashboardphotourl)) {echo "<style>body {background-image: url('" . $dashboardphotourl . "'); background-size: cover;} " . $usercss . "</style>";}



?>
