<?php
require_once __DIR__ . '/../config/config.php';

if (student_logged_in()) {
    redirect(BASE_URL . 'voter/dashboard.php');
}

$errors = [];

if (is_post()) {
    verify_csrf();
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Enter both email and password.';
    } elseif ($lockoutMsg = login_rate_limit_check($email, 'student')) {
        $errors[] = $lockoutMsg;
    } else {
        $student = student_attempt_login($email, $password);
        if ($student) {
            // Password is only valid for the election it was set for via OTP.
            // Check it against every currently active election.
            $pdo = get_db();
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) c FROM elections
                 WHERE id = ? AND status = 'active' AND NOW() BETWEEN start_time AND end_time"
            );
            $stmt->execute([$student['password_valid_for_election_id']]);
            $stillValid = $student['password_valid_for_election_id'] && (int) $stmt->fetch()['c'] > 0;

            if (!$stillValid) {
                $errors[] = 'Your access has expired for the current election. Request a new access code below.';
                log_action('student', $student['id'], 'LOGIN_BLOCKED_STALE_PASSWORD', 'Password not valid for any active election');
            } else {
                student_login($student);
                log_action('student', $student['id'], 'LOGIN', 'Student logged in');
                redirect(BASE_URL . 'voter/dashboard.php');
            }
        } else {
            $errors[] = 'Invalid email or password.';
            login_rate_limit_record($email, 'student');
            log_action('system', null, 'STUDENT_LOGIN_FAILED', 'Attempt for email: ' . $email);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Student Login — <?= h(SITE_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>

<div class="split-auth">

  <div class="split-auth-visual">
    <div class="slide"></div>
    <div class="slide"></div>
    <div class="slide"></div>
    <div class="slide"></div>
    <div class="slide"></div>
    <div class="slide"></div>
    <div class="slide-overlay"></div>

    <div class="visual-content">
      <span class="visual-badge">SJBCE ELECTIONS</span>
      <h1>St. John Bosco<br>College of Education</h1>
      <div class="visual-divider"></div>
      <p class="visual-tagline">"Your Vote, Your Voice — Cast It Securely"</p>
    </div>
    <div class="visual-footer">&copy; <?= date('Y') ?> St. John Bosco College of Education — All rights reserved.</div>
  </div>

  <div class="split-auth-form">
    <div class="auth-panel">
      <img src="<?= BASE_URL ?>assets/img/logo.png" alt="St. John Bosco's College of Education" class="auth-logo-img">
      <h2>Student Sign In</h2>
      <p class="auth-subtitle">Sign in to access your voting dashboard</p>

      <a href="<?= BASE_URL ?>student/request_otp.php" class="quick-link-pill">&rarr; GET ACCESS CODE</a>

      <?php foreach ($errors as $e): ?>
        <div class="alert alert-error"><?= h($e) ?></div>
      <?php endforeach; ?>

      <p class="help-text" style="margin-bottom:1rem;">
        <a href="<?= BASE_URL ?>student/request_otp.php">Forgot / need a new access code?</a>
      </p>

      <p class="support-footer">Need help? Contact <a href="mailto:studentaffairs@sjbce.edu.gh">the Student Affairs office</a></p>
    </div>
  </div>

</div>

</body>
</html>
