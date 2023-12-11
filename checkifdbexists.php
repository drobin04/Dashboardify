<?php
$filename = 'Dashboardify.s3db';
if (file_exists($filename) && filesize($filename) > 0) {
    
} else {
    header("Location: setup.php");
}
?>