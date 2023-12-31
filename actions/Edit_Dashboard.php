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

	//$select = "Update Dashboards Set Name = '" . $dashboardname . "', Embeddable = '" . $embeddable . "' Where DashboardID = '" . $dashboardid . "'";
	
	
	$sql = "Update Dashboards Set Name = ?, Embeddable = ? Where DashboardID = ?";
	
	$localdb = getPDO_DBFile();
	$stmt1 = $localdb->prepare($sql);
	$stmt1->bindParam(1, $dashboardname, PDO::PARAM_STR);
	$stmt1->bindParam(2,$embeddable,PDO::PARAM_BOOL);
	$stmt1->bindParam(3,$dashboardid,PDO::PARAM_STR);
	$stmt1->execute();

	redirect("../index.php");
?>

</body>

</html>
