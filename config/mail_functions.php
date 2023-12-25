<?php
// Function to save email settings
function savemailsettings($username, $password)
{
    $configFile = 'emailconfig.ini';
    
    // Check if the config file exists, create it if not
    if (!file_exists($configFile)) {
        $config = fopen($configFile, 'w');
        fclose($config);
    }
    
    // Read the existing config file
    $configData = parse_ini_file($configFile);
    
    // Set the new email credentials
    $configData['username'] = $username;
    $configData['password'] = $password;
    
    // Write the updated config data to the file
    $configContent = '';
    foreach ($configData as $key => $value) {
        $configContent .= "$key = \"$value\"\n";
    }
    
    file_put_contents($configFile, $configContent);
}

// Function to retrieve email settings
function retrievemailsettings()
{
    $configFile = 'emailconfig.ini';
    
    // Check if the config file exists
    if (file_exists($configFile)) {
        return parse_ini_file($configFile);
    } else {
        return null;
    }
}

// Retrieve email settings
//$emailSettings = retrievemailsettings();
//if ($emailSettings) {
//    $username = $emailSettings['username'];
//    $password = $emailSettings['password'];
//} else {
//    echo "Email settings not found.\n";
//}

?>