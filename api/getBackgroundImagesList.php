<?php
// Documentation: Requires no input; returns a list of image file paths. Should return relative file paths from the site dir, not absolute host-system file paths.

include_once("../shared_functions.php");
header('Content-Type: application/json');

// Define the path to the subdirectory
$directory = rootdir() . '/Backgrounds';

// Define allowed image extensions
$allowedExtensions = ['jpeg', 'jpg', 'png', 'avif'];

// Ensure the directory exists
if (!is_dir($directory)) {
    echo json_encode(['error' => 'Directory not found']);
    exit;
}



// Get all files in the directory
$files = array_diff(scandir($directory), array('..', '.'));

// Filter files by allowed extensions
$imageFiles = array_filter($files, function($file) use ($directory, $allowedExtensions) {
    $fileExtension = pathinfo($file, PATHINFO_EXTENSION);
    return in_array(strtolower($fileExtension), $allowedExtensions);
});

// Build a list of relative file paths
$fileList = array_map(function($file) use ($directory) {
	$currentUrl = $_SERVER['REQUEST_URI'];
	$baseUrl = rtrim(dirname($currentUrl), '/');
    // Construct the full file path
    $fullPath = $directory . '/' . $file;
    // Get the path relative to the 'Backgrounds' directory
    $relativePath = str_replace(rootdir() . '/Backgrounds/', '', $fullPath);
    $CompleteURLImagePath = $baseUrl . "/Backgrounds/" . $relativePath;
    // Use forward slashes for URLs
    return str_replace('\\', '/', $CompleteURLImagePath);
}, $imageFiles);

// Output the JSON-encoded file list
echo json_encode($fileList);
