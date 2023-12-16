<?php
If (isset($_GET['siteurlconfig'])) { 
    // Retrieve the value from the form submission
    $defaultdashimg = $_GET['defaultdashimage'];

    // Open the file in write mode
    $file = fopen('defaultdashboardurl.txt', 'w');

    // Write the value to the file
    fwrite($file, $defaultdashimg);

    // Close the file
    fclose($file);
    header('Location: ../setup.php');
}
?>
