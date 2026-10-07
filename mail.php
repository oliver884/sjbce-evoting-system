<?php
/**
 * Email (SMTP) configuration.
 *
 * Using Gmail as the SMTP provider (works reliably from XAMPP, unlike PHP's
 * built-in mail() which usually doesn't work on Windows without extra setup).
 *
 * IMPORTANT: You cannot use your normal Gmail password here — Google
 * requires an "App Password" for this. Steps:
 *   1. Go to https://myaccount.google.com/security
 *   2. Turn on 2-Step Verification (required before App Passwords are available)
 *   3. Go to https://myaccount.google.com/apppasswords
 *   4. Create a new app password (name it e.g. "SJBCE Elections")
 *   5. Google gives you a 16-character password — paste it below (SMTP_PASS)
 */

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'fredricabless@gmail.com');   // <-- your Gmail address
define('SMTP_PASSWORD', 'hecv cdvr xiyq kvdy');             // <-- 16-char App Password from Google
define('MAIL_FROM_EMAIL', 'fredricabless@gmail.com');  // usually same as SMTP_USERNAME
define('MAIL_FROM_NAME', 'SJBCE Election Portal');
