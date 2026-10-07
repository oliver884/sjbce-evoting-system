<?php
require_once __DIR__ . '/../config/config.php';
// Superseded by the OTP access-code flow (student/verify_otp.php).
redirect(BASE_URL . 'student/request_otp.php');
