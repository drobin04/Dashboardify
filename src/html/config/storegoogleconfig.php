<?php
// Get the authorization code from the request
//$code = $_GET['code'];
// Can't use $_GET because the data got sent as [url]#code= instead of [url]?code=
// Get the current URL
$currentUrl = $_SERVER['REQUEST_URI'];

// Parse the URL
$parsedUrl = parse_url($currentUrl);

// Get the fragment part of the URL
$fragment = $parsedUrl['fragment'];

// Parse the fragment string into an array
parse_str($fragment, $params);

// Get the value of the 'code' parameter
$code = $params['code'];

// Output the parsed code
echo $code;

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