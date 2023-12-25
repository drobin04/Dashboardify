<?php 

If (isset($_POST["username"])) {

	$username = $_POST["username"];
	$password = $_POST["password"];
	
	include('mail_functions.php');
	savemailsettings($username, $password);
	echo "saved mail settings successfully.";
} else {
	echo "username not sent";
}


?>