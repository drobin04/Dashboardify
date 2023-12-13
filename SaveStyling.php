<html>
<head>    <?php include("logoutredirect.php");?> </head>
<body>

<?php
    include("shared_functions.php");

    debuglog($_POST);
    $CSS = $_POST["txtCSS"];
    $backgroundurl = $_POST["backgroundurl"];
    $dashboardid = $_POST["dashboardID"]; //Identify which dashboard the styling needs to be added to.

    //debuglog("shared functions module loaded.");
    $db_file = new PDO('sqlite:Dashboardify.s3db'); // Connect to SQLite database file.
	$select = "Update Dashboards Set BackgroundPhotoURL = ?, CustomCSS = ? Where DashboardID = ?";

	$stmt = $db_file->prepare($select);
    $stmt->bindParam(1,$backgroundurl,PDO::PARAM_STR);
	$stmt->bindParam(2,$CSS,PDO::PARAM_STR);
    $stmt->bindParam(3,$dashboardid,PDO::PARAM_STR);
	debuglog(array($select,$CSS,$backgroundurl,$dashboardid), "Data at time of write to DB");
	
	$stmt->execute();
	header("Location: index.php");
?>

</body>

</html>