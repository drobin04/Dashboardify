<?php
include_once('../shared_functions.php');
session_start();
header('Content-Type: application/octet-stream');

function debug_log($msg) {
    file_put_contents(__DIR__ . '/../debug_token.log', date('c') . ' ' . $msg . "\n", FILE_APPEND);
}

$key = 'hardcoded_super_secret_key_32bytes!'; // 32 bytes for AES-256
try {
    $userid = getCurrentUserID();
    if (!$userid) {
        debug_log('No user ID found in session.');
        http_response_code(401);
        echo "ERROR: Not logged in or session expired.";
        exit;
    }
    header('Content-Disposition: attachment; filename="login_token.dashify"');
    $iv = openssl_random_pseudo_bytes(16); // 16 bytes for AES-256-CBC
    $ciphertext = openssl_encrypt($userid, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    if ($ciphertext === false) {
        debug_log('OpenSSL encryption failed.');
        http_response_code(500);
        echo 'ERROR: Encryption failed.';
        exit;
    }
    echo base64_encode($iv) . ':' . base64_encode($ciphertext);
} catch (Exception $e) {
    debug_log('Exception: ' . $e->getMessage());
    http_response_code(500);
    echo 'ERROR: ' . $e->getMessage();
    exit;
} 