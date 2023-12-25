<html>
<head>    <?php include_once("../actions/logoutredirect.php");?> </head>
<body>

<?php
    include_once("../shared_functions.php"); // Using for SQLite connectivity via execquery / selectquery functions, and debuglogging

    debuglog($_POST);
    $dashboardname = $_POST["dashboardname"];
    $dashboardid = $_GET["DashboardID"];
    $embeddable = $_POST["embeddable"];
    $sessionid = $_COOKIE["SessionID"];
    //Get User for Session ID
    //$userid = selectquery("Select UserID From Sessions Where SessionID = '" . $sessionid . "'")[0]["UserID"];

	$select = "Update Dashboards Set Name = '" . $dashboardname . "', Embeddable = '" . $embeddable . "' Where DashboardID = '" . $dashboardid . "'";
	execquery($select);
	redirect("../index.php");
?>

</body>

</html>