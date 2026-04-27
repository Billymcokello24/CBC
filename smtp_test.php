<?php

/**
 * SMTP Connectivity Test Script
 * This script bypasses Laravel to test raw SMTP connection.
 */

$host = 'smtp.gmail.com';
$port = 587;
$user = 'billyochiengokello@gmail.com';
$pass = 'fram ncsw piba mwqn'; // Ensure this is a valid App Password if 2FA is on

echo "Connecting to $host:$port...\n";

$socket = fsockopen($host, $port, $errno, $errstr, 30);

if (!$socket) {
    die("Connection failed: $errstr ($errno)\n");
}

echo "Connected. Reading response...\n";
echo fgets($socket);

// Start Handshake
fputs($socket, "EHLO localhost\r\n");
echo "EHLO: " . fgets($socket);
while ($line = fgets($socket)) {
    echo $line;
    if (substr($line, 3, 1) == ' ') break;
}

// Start TLS
fputs($socket, "STARTTLS\r\n");
echo "STARTTLS: " . fgets($socket);

if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
    die("TLS negotiation failed\n");
}

echo "TLS enabled. Re-authenticating...\n";

fputs($socket, "EHLO localhost\r\n");
while ($line = fgets($socket)) {
    if (substr($line, 3, 1) == ' ') break;
}

// Authenticate
fputs($socket, "AUTH LOGIN\r\n");
echo "AUTH LOGIN: " . fgets($socket);

fputs($socket, base64_encode($user) . "\r\n");
echo "User: " . fgets($socket);

fputs($socket, base64_encode($pass) . "\r\n");
$authResponse = fgets($socket);
echo "Pass: " . $authResponse;

if (strpos($authResponse, '235') === false) {
    echo "\nAUTHENTICATION FAILED. Check credentials or App Password.\n";
} else {
    echo "\nAUTHENTICATION SUCCESSFUL.\n";
    
    // Try to send a test command to see if limit is hit
    fputs($socket, "MAIL FROM: <$user>\r\n");
    echo "MAIL FROM: " . fgets($socket);
    
    fputs($socket, "RCPT TO: <$user>\r\n");
    $rcptResponse = fgets($socket);
    echo "RCPT TO: " . $rcptResponse;
    
    if (strpos($rcptResponse, '250') !== false) {
        echo "Recipient OK.\n";
        
        fputs($socket, "DATA\r\n");
        echo "DATA: " . fgets($socket);
        
        fputs($socket, "Subject: SMTP Test\r\n\r\nTest body.\r\n.\r\n");
        $dataResponse = fgets($socket);
        echo "Data Response: " . $dataResponse;
        
        if (strpos($dataResponse, '550') !== false) {
            echo "LIMIT HIT AT DATA STAGE: " . $dataResponse;
        }
    }
}

fputs($socket, "QUIT\r\n");
fclose($socket);
