<?php

use Illuminate\Support\Facades\Mail;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Mailer: " . config('mail.mailers.smtp.transport') . "\n";
echo "Host: " . config('mail.mailers.smtp.host') . "\n";
echo "Port: " . config('mail.mailers.smtp.port') . "\n";
echo "Encryption: " . config('mail.mailers.smtp.encryption') . "\n";
echo "Username: " . config('mail.mailers.smtp.username') . "\n";
// Don't echo password for security, but check if it's set
echo "Password set: " . (config('mail.mailers.smtp.password') ? 'Yes' : 'No') . "\n";

try {
    echo "Attempting to send a test email to " . config('mail.mailers.smtp.username') . "...\n";
    Mail::raw('Test email from CBC system at ' . date('Y-m-d H:i:s'), function($message) {
        $message->to(config('mail.mailers.smtp.username'))->subject('Test Email');
    });
    echo "SUCCESS: Email sent successfully!\n";
} catch (\Exception $e) {
    echo "FAILURE: " . $e->getMessage() . "\n";
}
