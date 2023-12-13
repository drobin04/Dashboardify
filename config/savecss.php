<?php
$siteurl = file_get_contents('siteurlconfig.txt');

If (isset($_GET['CSS'])) { 
    // Retrieve the value from the form submission
    $css = $_GET['CSS'];

    // Sanitize the input
    //$siteurlconfig = htmlspecialchars($siteurlconfig);

    // Open the file in write mode
    $file = fopen('globalcss.css', 'w');

    // Write the value to the file
    fwrite($file, $css);

    // Close the file
    fclose($file);
    echo "<h1>Site URL Config Stored!</h1> <br /><br />
    <a href='" . $siteurl . "/setup.php'>Click Here to return to Setup.</a>";
}
?>

