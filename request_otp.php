<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/mailer.php';
$pdo = get_db();

$sent = false;
$devCode = null; // shown only if email sending fails, for testing

if (is_post()) {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');

    // Always show the same message whether or not this succeeds — avoid
    // revealing which emails are on the roster.
    $sent = true;

    $election = $pdo->query(
        "SELECT * FROM elections WHERE status = 'active' AND NOW() BETWEEN start_time AND end_time
         ORDER BY start_time ASC LIMIT 1"
    )->fetch();

    if ($election) {
        $stmt = $pdo->prepare("SELECT id, full_name FROM students WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $student = $stmt->fetch();

        if ($student) {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            $pdo->prepare(
                'INSERT INTO otp_codes (student_id, election_id, code, expires_at, created_at) VALUES (?, ?, ?, ?, NOW())'
            )->execute([$student['id'], $election['id'], $code, $expires]);

            log_action('student', $student['id'], 'OTP_REQUESTED', $email);

            $html = "<p>Hello " . htmlspecialchars($student['full_name']) . ",</p>
                <p>Your one-time access code for <strong>" . htmlspecialchars($election['title']) . "</strong> is:</p>
                <p style='font-size:28px;font-weight:bold;letter-spacing:4px;'>$code</p>
                <p>This code expires in 10 minutes. Enter it on the verification page to set your password and vote.</p>";

            $emailSent = send_email($email, $student['full_name'], 'Your SJBCE Election access code', $html);

            if (!$emailSent) {
                $devCode = $code; // fallback for local/testing use only
            }
        }
    }
}

$pageTitle = 'Get Access Code';
require __DIR__ . '/../includes/header_site.php';
?>

<div class="auth-shell" style="min-height:50vh;">
  <div class="card" style="width:100%;max-width:420px;">
    <div class="position-tag">Voter Access</div>
    <h2>Get Your Access Code</h2>

    <?php if ($sent): ?>
      <div class="alert alert-success">If there's an active election and your email is on the student list, a code has been sent.</div>
      <?php if ($devCode): ?>
        <div class="alert alert-error">
          <strong>Email delivery failed</strong> — check config/mail.php. For now, here's the code directly: <strong><?= h($devCode) ?></strong>
        </div>
      <?php endif; ?>
      <a href="<?= BASE_URL ?>student/verify_otp.php" class="btn btn-primary btn-block">Enter Code</a>
    <?php else: ?>
      <p class="muted">Enter the email your school registered for you. A one-time code will be sent for this election's voting session.</p>
      <form method="post">
        <?= csrf_field() ?>
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required autofocus>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Send Access Code</button>
      </form>
    <?php endif; ?>
    <p class="help-text text-center mt-lg"><a href="<?= BASE_URL ?>student/login.php">Already have a password for this election? Log in</a></p>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer_site.php'; ?>
