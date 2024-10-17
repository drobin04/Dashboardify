<?php
require 'vendor/autoload.php'; // Ensure this path is correct

// Check if the autoload file exists and is readable

if (file_exists('vendor/autoload.php')) {
    require 'vendor/autoload.php';
    echo "Autoload file found and included.";
} else {
    echo "Autoload file not found.";
}

use Google\Client as Google_Client;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize the Google Client
$client = new Google_Client(['client_id' => 'YOUR_CLIENT_ID']); // Replace with your Client ID

echo "Google_Client class loaded successfully.";
?>