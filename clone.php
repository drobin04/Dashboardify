<?php
function downloadAndExtractGitHubRepo($repoUrl) {
    // Download the zipball
    $zipUrl = $repoUrl . '/archive/master.zip';
    $zipData = file_get_contents($zipUrl);

    // Save the zipball to a temporary file
    $tempZipFile = tempnam(sys_get_temp_dir(), 'github_repo_');
    file_put_contents($tempZipFile, $zipData);

    // Extract the zipball
    $zip = new ZipArchive;
    if ($zip->open($tempZipFile) === TRUE) {
        $zip->extractTo(dirname(__FILE__));
        $zip->close();
        //echo 'Zipball extracted successfully';
    } else {
        echo 'Failed to extract zipball';
    }

    // Clean up the temporary file
    unlink($tempZipFile);
}

function copyDirectory($src, $dst, $excludedFiles) {
    $dir = opendir($src);
    @mkdir($dst);

    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (in_array($file, $excludedFiles)) {
                continue;  // Skip excluded files
            }
            if (is_dir($src . '/' . $file)) {
                copyDirectory($src . '/' . $file, $dst . '/' . $file, $excludedFiles);  // Recursive copy for subdirectories
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);  // Copy the file from subdirectory to current directory
            }
        }
    }
    closedir($dir);
}
function deleteDirectory($dir) {
    if (!file_exists($dir) || !is_dir($dir)) {
        return false;
    }

    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? deleteDirectory("$dir/$file") : unlink("$dir/$file");
    }

    return rmdir($dir);
}

include('config/check_admin.php');

if (AmIAdmin()) {
// Replace 'your_repo_url' with the string variable containing the GitHub repo URL
$repoUrl = 'https://github.com/drobin04/Dashboardify';
downloadAndExtractGitHubRepo($repoUrl);
$subdir = 'Dashboardify-main';  // Subdirectory name
$excludedFiles = array('clone.php','defaultdashboardurl.txt', 'globalcss.css');  // Array of file names to be excluded

copyDirectory('Dashboardify-main', '.', $excludedFiles);  // Starting point for copying

// Call the function to delete the directory and its contents
deleteDirectory('Dashboardify-main');

header('Location: setup.php');
} else { echo "You don't have permissions to do this.";}


?>