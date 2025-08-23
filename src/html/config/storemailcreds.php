<?php 

If (isset($_POST["username"])) {

	$username = $_POST["username"];
	$password = $_POST["password"];
	$smtpSecure = $_POST["smtpSecure"];
	$smtpPort = $_POST["smtpPort"];
	
	include('mail_functions.php');
	savemailsettings($username, $password, $smtpSecure, $smtpPort);
	echo "saved mail settings successfully.";
} else {
	echo "username not sent";
}


?>