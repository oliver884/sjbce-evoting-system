<?php
require_once __DIR__ . '/../config/config.php';
if (student_logged_in()) {
    log_action('student', current_student_id(), 'LOGOUT', 'Student logged out');
}
logout_all();
redirect(BASE_URL . 'student/login.php');
