<?php
If (isset($_POST['siteurlconfig'])) { 
    include_once('../shared_functions.php');

    // Retrieve the value from the form submission
    $siteurlconfig = $_POST['siteurlconfig'];
    execquery("
    DELETE FROM Settings Where Name = 'SiteUrlConfig'");
    execquery("
    INSERT INTO Settings (Name, Value) VALUES ('SiteUrlConfig', '" . $siteurlconfig . "')");
    header('Location: ../setup.php');
}
?>
