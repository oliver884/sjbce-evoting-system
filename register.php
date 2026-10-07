<?php
require_once __DIR__ . '/../config/config.php';

// Self-registration has been disabled. Students are added to the system
// by an administrator from the official school roster (see admin/import_students.php).
// Anyone landing here is redirected to the access-code flow instead.
redirect(BASE_URL . 'student/request_otp.php');
