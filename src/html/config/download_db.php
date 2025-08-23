<?php 

// CHECK ADMIN CHECK ADMIN CHECK ADMIN
if (file_exists('check_admin.php')) {
	include_once('check_admin.php');
	}


If (!AmIAdmin()) {
    echo "You don't have rights to perform this operation.";
    exit();
}
// Define the file path relative to the current script
$file = '../data/Dashboardify.s3db';

// Check if the file exists
if (file_exists($file)) {
    // Set the headers to initiate the download
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    
    // Clear output buffer
    ob_clean();
    flush();
    
    // Read the file and output it to the user
    readfile($file);
    exit;
} else {
    // Handle the error if the file does not exist
    echo 'File not found.';
}
?>