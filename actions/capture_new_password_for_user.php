<?php
include('../shared_functions.php');
$password = "";

if (isset($_POST["password"])) {
// pw is hashed by this point in time, hash again on way to server
		
	$password = $_POST["password"];
	
	$password = hash('sha256', $password);
	
	
	// Store in DB for the current user
	$userid = getCurrentUserID();
	
	execquery("update Users Set password = '" . $password . "' where RecID = '" . $userid . "'");
		
	redirect('../index.php');
	//echo "Password Changed. Incoming hash: " . $_POST["password"] . ", Hash sent to server: " . $password;
	
	
		
}


?>