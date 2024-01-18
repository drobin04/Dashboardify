<?php
If (isset($_POST['siteurlconfig'])) { 
    include_once('../shared_functions.php');

    If (doesDatabaseExist() && filesize($filename) > 0) {
		// Retrieve the value from the form submission
		$siteurlconfig = $_POST['siteurlconfig'];
		execquery("
		DELETE FROM Settings Where Name = 'SiteUrlConfig'");
		execquery("
		INSERT INTO Settings (Name, Value) VALUES ('SiteUrlConfig', '" . $siteurlconfig . "')");
		redirect('../setup.php');
	} else {
		// Break! DB Doesn't exist!
		echo "The database doesn't exist yet; please go back, and open the create DB tab and create the database.";
		exit();
}
?>
