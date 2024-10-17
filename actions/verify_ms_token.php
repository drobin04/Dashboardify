<?php
session_start();

require '../vendor/autoload.php'; // Adjust the path as necessary

use League\OAuth2\Client\Provider\GenericProvider;

$provider = new GenericProvider([
    'clientId'                => '0daa48bb-1104-4b72-a3ae-04978b3e6a96',
    'clientSecret'            => 'yKI8Q~RDq33LmTSPoQgbT9WOhfWhICaDkRKRYbFg',
    'redirectUri'             => 'https://dashboardify.app/Dashboardify/actions/verify_ms_token.php',
    'urlAuthorize'            => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
    'urlAccessToken'          => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
    'urlResourceOwnerDetails' => 'https://graph.microsoft.com/v1.0/me',
    'scopes'                  => 'openid profile'
]);

// Check for authorization code in the query parameters
if (isset($_GET['code'])) {
    try {
        // Get the access token
        $accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);
    
        // Decode the ID token to extract user information
        $idToken = $accessToken->getValues()['id_token'];
        $payload = explode('.', $idToken)[1];
        $userData = json_decode(base64_decode($payload), true);
    
        // Extract the unique user ID
        $uniqueId = $userData['sub']; // Unique identifier
    
        // Store the unique ID in the session
        $_SESSION['user_id'] = $uniqueId;
    
        // Redirect to a protected page
        echo "SUCCESS! " . $uniqueId;
        exit;
    
    } catch (\Exception $e) {
        exit('Error retrieving access token: ' . $e->getMessage());
    }
    
} else {
    // Handle missing authorization code
    exit('Authorization code not found.');
}
