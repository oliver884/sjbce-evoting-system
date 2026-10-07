<?php
require_once __DIR__ . '/../config/config.php';

$pdo = get_db();

$errors = [];

if (is_post() && ($_POST['stage'] ?? '') === 'verify_code') {
    verify_csrf();

    $email = trim($_POST['email'] ?? '');
    $code  = trim($_POST['code'] ?? '');

    if ($email === '' || $code === '') {
        $errors[] = 'Enter your email and 6-digit access code.';
    } elseif (!preg_match('/^\d{6}$/', $code)) {
        $errors[] = 'Enter a valid 6-digit access code.';
    } else {
        // Find the active student
        $stmt = $pdo->prepare(
            "SELECT id, full_name
             FROM students
             WHERE email = ?
             AND status = 'active'
             LIMIT 1"
        );
        $stmt->execute([$email]);
        $student = $stmt->fetch();

        if (!$student) {
            $errors[] = 'That code is incorrect or has expired. Request a new one.';
        } else {
            // Get the student's newest unused, unexpired OTP
            $stmt = $pdo->prepare(
                "SELECT *
                 FROM otp_codes
                 WHERE student_id = ?
                 AND used = 0
                 AND expires_at > NOW()
                 ORDER BY created_at DESC
                 LIMIT 1"
            );
            $stmt->execute([$student['id']]);
            $otp = $stmt->fetch();

            if (!$otp) {
                $errors[] = 'That code is incorrect or has expired. Request a new one.';
            } elseif ((int) $otp['attempts'] >= 5) {
                $errors[] = 'Too many incorrect attempts. Please request a new code.';
            } elseif (!hash_equals((string) $otp['code'], $code)) {

                // Wrong OTP
                $pdo->prepare(
                    'UPDATE otp_codes
                     SET attempts = attempts + 1
                     WHERE id = ?'
                )->execute([$otp['id']]);

                $remaining = 5 - ((int) $otp['attempts'] + 1);

                $errors[] = $remaining > 0
                    ? "That code is incorrect. $remaining attempt(s) remaining."
                    : 'That code is incorrect. Please request a new one.';

            } else {

                // OTP is correct.
                // Make sure the election connected to this OTP is still active.
                $stmt = $pdo->prepare(
                    "SELECT id
                     FROM elections
                     WHERE id = ?
                     AND status = 'active'
                     AND NOW() BETWEEN start_time AND end_time
                     LIMIT 1"
                );
                $stmt->execute([$otp['election_id']]);
                $election = $stmt->fetch();

                if (!$election) {
                    $errors[] = 'The election is not currently active. Please try again during the voting period.';
                } else {

                    // Mark this OTP as used BEFORE logging the student in.
                    $pdo->prepare(
                        'UPDATE otp_codes
                         SET used = 1
                         WHERE id = ?'
                    )->execute([$otp['id']]);

                    // Create the normal student authentication session.
                    student_login($student);

                    // Keep election context in the session if needed elsewhere.
                    $_SESSION['student_election_id'] = (int) $otp['election_id'];

                    log_action(
                        'student',
                        $student['id'],
                        'OTP_LOGIN',
                        "Election #{$otp['election_id']}"
                    );

                    // Go directly to the voting dashboard.
                    redirect(BASE_URL . 'voter/dashboard.php');
                }
            }
        }
    }
}

$pageTitle = 'Verify Access Code';
require __DIR__ . '/../includes/header_site.php';
?>

<div class="auth-shell" style="min-height:50vh;">
  <div class="card" style="width:100%;max-width:420px;">
    <div class="position-tag">Voter Access</div>

    <?php foreach ($errors as $e): ?>
      <div class="alert alert-error"><?= h($e) ?></div>
    <?php endforeach; ?>

    <h2>Enter Your Code</h2>

    <p class="muted">
      Enter the email you requested a code for and the 6-digit code sent to your inbox.
    </p>

    <form method="post">
      <?= csrf_field() ?>

      <input type="hidden" name="stage" value="verify_code">

      <div class="form-group">
        <label for="email">Email</label>
        <input
          type="email"
          id="email"
          name="email"
          required
          autofocus
          value="<?= h($_POST['email'] ?? '') ?>"
        >
      </div>

      <div class="form-group">
        <label for="code">6-Digit Code</label>
        <input
          type="text"
          id="code"
          name="code"
          inputmode="numeric"
          maxlength="6"
          pattern="[0-9]{6}"
          required
          placeholder="123456"
          autocomplete="one-time-code"
        >
      </div>

      <button type="submit" class="btn btn-primary btn-block">
        Verify &amp; Enter Election
      </button>

      <p class="help-text text-center">
        <a href="<?= BASE_URL ?>student/request_otp.php">
          Didn't get a code? Request one
        </a>
      </p>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer_site.php'; ?>