<?php
require_once __DIR__ . '/../config/config.php';
// Password recovery is now handled by the OTP access-code flow, which also
// re-verifies identity every election cycle instead of just resetting a
// standing password.
redirect(BASE_URL . 'student/request_otp.php');
