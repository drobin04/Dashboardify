<?php

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
                if ($row["DashboardID"] == $_GET["SelectDashboardID"]) { 
                    $match = 1; 
                    debuglog(array($match,$matcheddashboardid), "Match / selected db from URL if present?"); 
                    $dashboardphotourl = $row["BackgroundPhotoURL"];
                    $usercss = $row["CustomCSS"];
                
                }
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
