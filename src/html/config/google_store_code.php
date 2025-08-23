<?php
// Get the authorization code from the request
$code = $_GET["code"];


// Exchange the authorization code for an access token
$clientid = '814465180043-ir2l2aejp965j0eug05kfi51clid8f7a.apps.googleusercontent.com';
$clientsecret = file_get_contents("google_config_app_secret.txt");
$redirecturi = 'https://dashboardify.app/config/storegoogleconfig.php';

$tokenurl = 'https://oauth2.googleapis.com/token';
$data = array(
    'code' => $code,
    'client_id' => $clientid,
    'client_secret' => $clientsecret,
    'redirect_uri' => $redirecturi,
    'grant_type' => 'authorization_code'
);

$options = array(
    'http' => array(
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST',
        'content' => http_build_query($data)
    )
);

$context = stream_context_create($options);
$response = file_get_contents($tokenurl, false, $context);
$token_data = json_decode($response, true);

// Use the access token to make API requests
$access_token = $token_data['access_token'];

// TODO: Use the access token to make API requests

echo "ACCESS TOKEN: " . $access_token;
file_put_contents("access_token", $access_token);
?>