<?php
require_once __DIR__ . '/../config/config.php';
require_student_login();
$pdo = get_db();

$stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
$stmt->execute([current_student_id()]);
$student = $stmt->fetch();

$errors = [];

if (is_post() && ($_POST['action'] ?? '') === 'update_profile') {
    verify_csrf();
    $phone      = trim($_POST['phone'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $level      = trim($_POST['level'] ?? '');

    if ($phone === '' || $department === '' || $level === '') {
        $errors[] = 'All fields are required.';
    } else {
        $pdo->prepare('UPDATE students SET phone = ?, department = ?, level = ? WHERE id = ?')
            ->execute([$phone, $department, $level, $student['id']]);
        log_action('student', $student['id'], 'PROFILE_UPDATED', '');
        flash_set('success', 'Profile updated.');
        redirect(BASE_URL . 'student/profile.php');
    }
}

if (is_post() && ($_POST['action'] ?? '') === 'change_password') {
    verify_csrf();
    $current = $_POST['current_password'] ?? '';
    $newPass = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!password_verify($current, $student['password_hash'])) {
        $errors[] = 'Current password is incorrect.';
    } elseif (strlen($newPass) < 8) {
        $errors[] = 'New password must be at least 8 characters.';
    } elseif ($newPass !== $confirm) {
        $errors[] = 'New passwords do not match.';
    } else {
        $hash = password_hash($newPass, PASSWORD_BCRYPT);
        $pdo->prepare('UPDATE students SET password_hash = ? WHERE id = ?')->execute([$hash, $student['id']]);
        log_action('student', $student['id'], 'PASSWORD_CHANGED', '');
        flash_set('success', 'Password changed.');
        redirect(BASE_URL . 'student/profile.php');
    }
}

$pageTitle = 'My Profile';
require __DIR__ . '/../includes/header_site.php';
?>

<h1>My Profile</h1>

<?php foreach ($errors as $e): ?>
  <div class="alert alert-error"><?= h($e) ?></div>
<?php endforeach; ?>

<div class="grid-2" style="align-items:start;">
  <div class="card">
    <h3>Profile Details</h3>
    <p class="muted" style="font-size:.85rem;">
      Student ID: <strong><?= h($student['student_id']) ?></strong><br>
      Email: <strong><?= h($student['email']) ?></strong> (contact admin to change)
    </p>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="update_profile">
      <div class="form-group">
        <label for="full_name">Full Name</label>
        <input type="text" id="full_name" value="<?= h($student['full_name']) ?>" disabled>
      </div>
      <div class="form-group">
        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone" required value="<?= h($student['phone']) ?>">
      </div>
      <div class="form-group">
        <label for="department">Department / Faculty</label>
        <input type="text" id="department" name="department" required value="<?= h($student['department']) ?>">
      </div>
      <div class="form-group">
        <label for="level">Level / Year</label>
        <select id="level" name="level" required>
          <?php foreach (['Level 100','Level 200','Level 300','Level 400'] as $lv): ?>
            <option value="<?= $lv ?>" <?= $student['level'] === $lv ? 'selected' : '' ?>><?= $lv ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
  </div>

  <div class="card">
    <h3>Change Password</h3>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="change_password">
      <div class="form-group">
        <label for="current_password">Current Password</label>
        <input type="password" id="current_password" name="current_password" required>
      </div>
      <div class="form-group">
        <label for="new_password">New Password</label>
        <input type="password" id="new_password" name="new_password" required minlength="8">
      </div>
      <div class="form-group">
        <label for="confirm_password">Confirm New Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
      </div>
      <button type="submit" class="btn btn-secondary">Update Password</button>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer_site.php'; ?>
