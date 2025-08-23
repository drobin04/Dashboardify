<?php 
// If dash not found, create one
// This is called from 'actions/index/load_and_prep_dashboard.php
$dashboardid = GUID(); 
$defaultdashimage = "";
if (file_exists('config/defaultdashboardurl.txt')) {
    $defaultdashimage = trim(file_get_contents('config/defaultdashboardurl.txt'), "\n\r\t ");
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
}

// Reload dashboard
echo "<script>location.reload();</script>";

?>