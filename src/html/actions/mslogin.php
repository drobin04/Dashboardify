<?php 
session_start();

require '../vendor/autoload.php'; // Adjust path as needed

use League\OAuth2\Client\Provider\GenericProvider;

$provider = new GenericProvider([
    'clientId'                => '0daa48bb-1104-4b72-a3ae-04978b3e6a96',
    'clientSecret'            => 'yKI8Q~RDq33LmTSPoQgbT9WOhfWhICaDkRKRYbFg',
    'redirectUri'             => 'https://dashboardify.app/Dashboardify/actions/verify-login.php',
    'urlAuthorize'            => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
    'urlAccessToken'          => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
    'urlResourceOwnerDetails' => '',
    'scopes'                  => 'openid profile'
]);

if (!isset($_GET['code'])) {
    // If we don't have an authorization code, get one
    $authorizationUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: ' . $authorizationUrl);
    exit;
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    // State is invalid
    unset($_SESSION['oauth2state']);
    exit('Invalid state');
} else {
    // Try to get an access token (using the authorization code grant)
    try {
        $accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);

        // Get user information
        $resourceOwner = $provider->getResourceOwner($accessToken);
        $userData = $resourceOwner->toArray();

        // Here, $userData will contain user info like unique ID and profile info
        // You can store the unique ID in your session or use it for further verification
        $_SESSION['user'] = $userData;

        // Redirect to a protected page or display user info
        header('Location: /protected-page.php');
        exit;

    } catch (\Exception $e) {
        exit('Failed to get access token: ' . $e->getMessage());
    }
}

?>