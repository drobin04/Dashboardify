<?php
// Function to save email settings
function savemailsettings($username, $password, $smtpSecure, $smtpPort)
{
    $configFile = __DIR__ . '/emailconfig.ini';
    
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
    $configData['smtpSecure'] = $smtpSecure;
    $configData['smtpPort'] = $smtpPort;
    
    // Write the updated config data to the file
    $configContent = '';
    foreach ($configData as $key => $value) {
        $configContent .= "$key = \"$value\"\n";
    }
    
    file_put_contents($configFile, $configContent);
}

// Function to retrieve email settings
function retrievemailsettings($settingsfilepath)
{
//    $configFile = 'emailconfig.ini';
  	  
    // Check if the config file exists
    if (file_exists($settingsfilepath)) {
        return parse_ini_file($settingsfilepath);
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