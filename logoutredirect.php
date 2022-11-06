<?php
	//If not logged in, redirect to login page
	if (!isset($_COOKIE["SessionID"])) {
		header("Location: start-login.php");
	}  
?>