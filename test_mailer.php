<?php

require_once __DIR__ . '/includes/mailer.php';

$result = send_email(
    'fredricabless@gmail.com',
    'Test User',
    'SJBCE Mailer Test',
    '<h2>Mailer Test Successful</h2><p>This email was sent using the exact send_email() function used by the OTP system.</p>'
);

if ($result) {
    echo 'EMAIL SENT SUCCESSFULLY';
} else {
    echo 'EMAIL FAILED. Check the PHP error log.';
}