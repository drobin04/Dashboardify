<?php
	//If not logged in, redirect to login page
	//Need to check if current session ID is VALID! 
	function selectquery2($sql) {
		debuglog2($sql,"about to execute query");
		$db_file = new PDO('sqlite:Dashboardify.s3db');
		$stmt1 = $db_file->prepare($sql);
		$stmt1->execute();
		$results = $stmt1->fetchAll(PDO::FETCH_ASSOC);
		return $results;
	}
	function debuglog2( $object=null, $label=null ){
		$message = json_encode($object, JSON_PRETTY_PRINT);
		$label = "Debug" . ($label ? " ($label): " : ': ');
		echo "<script>console.log(\"$label\", $message);</script>";
	}

	$sessionid = $_COOKIE["SessionID"]; 
	$userid = selectquery2("Select Count(UserID) As Matches From Sessions Where SessionID = '" . $sessionid . "'")[0]["Matches"];
	debuglog2($userid);
	if (!isset($_COOKIE["SessionID"])) {
		setcookie("SessionID", "", time() - 3600, "/Dashboardify");
		unset($_COOKIE['SessionID']);
		header("Location: start-login.php");
	}
	if ($userid == 0) {
		setcookie("SessionID", "", time() - 3600, "/Dashboardify");
		unset($_COOKIE['SessionID']);
		header("Location: start-login.php");
	}
?>