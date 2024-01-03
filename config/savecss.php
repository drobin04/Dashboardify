<?php
include('../saved_functions.php');

If (isset($_GET['CSS'])) { 
    // Retrieve the value from the form submission
    $css = $_GET['CSS'];

    // Open the file in write mode
    $file = fopen('globalcss.css', 'w');

    // Write the value to the file
    fwrite($file, $css);

    // Close the file
    fclose($file);
    redirect('../setup.php');
}
?>

