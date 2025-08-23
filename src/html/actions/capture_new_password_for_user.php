<?php
include('../shared_functions.php');
$password = "";

if (isset($_POST["password"])) {
// pw is hashed by this point in time, hash again on way to server
		
	$password = $_POST["password"];
	
	$password = hash('sha256', $password);
	
	
	// Store in DB for the current user
	$userid = getCurrentUserID();
	$sql = "update Users Set password = ? where RecID = '" . $userid . "'";
	execquery_bind1($sql, $password);
		
	redirect('../index.php');
	//echo "Password Changed. Incoming hash: " . $_POST["password"] . ", Hash sent to server: " . $password;
	
	
		
}


?>
