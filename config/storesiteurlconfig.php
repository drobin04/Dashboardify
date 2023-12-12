<?php
If (isset($_GET['siteurlconfig'])) { 
    // Retrieve the value from the form submission
    $siteurlconfig = $_GET['siteurlconfig'];

    // Sanitize the input
    //$siteurlconfig = htmlspecialchars($siteurlconfig);

    // Open the file in write mode
    $file = fopen('siteurlconfig.txt', 'w');

    // Write the value to the file
    fwrite($file, $siteurlconfig);

    // Close the file
    fclose($file);
    echo "<h1>Site URL Config Stored!</h1> <br /><br />
    <a href='" . $siteurlconfig . "/setup.php'>Click Here to return to Setup.</a>";
}
?>
