<?php
require_once __DIR__ . '/../config/config.php';
require_super_admin();
$pdo = get_db();

if (is_post() && ($_POST['action'] ?? '') === 'add') {
    verify_csrf();
    $name  = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $role  = $_POST['role'] === 'super_admin' ? 'super_admin' : 'admin';

    if ($name === '' || $email === '' || strlen($pass) < 8) {
        flash_set('error', 'Name, email, and a password of at least 8 characters are required.');
    } else {
        try {
            $hash = password_hash($pass, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare(
                'INSERT INTO admins (full_name, email, password_hash, role, created_at) VALUES (?, ?, ?, ?, NOW())'
            );
            $stmt->execute([$name, $email, $hash, $role]);
            log_action('admin', current_admin_id(), 'CREATE_ADMIN', $email);
            flash_set('success', 'Administrator added.');
        } catch (PDOException $e) {
            flash_set('error', 'That email is already registered.');
        }
    }
    redirect(BASE_URL . 'admin/admins.php');
}

if (is_post() && ($_POST['action'] ?? '') === 'toggle') {
    verify_csrf();
    $id = (int) $_POST['admin_id'];
    if ($id === current_admin_id()) {
        flash_set('error', 'You cannot deactivate your own account.');
    } else {
        $newStatus = (int) $_POST['new_status'];
        $pdo->prepare('UPDATE admins SET is_active = ? WHERE id = ?')->execute([$newStatus, $id]);
        log_action('admin', current_admin_id(), 'ADMIN_STATUS_CHANGE', "Admin #$id -> " . ($newStatus ? 'active' : 'inactive'));
        flash_set('success', 'Administrator updated.');
    }
    redirect(BASE_URL . 'admin/admins.php');
}

$admins = $pdo->query('SELECT * FROM admins ORDER BY created_at DESC')->fetchAll();

$activeNav = 'admins';
$pageTitle = 'Administrators';
require __DIR__ . '/../includes/header_admin.php';
?>

<h1>Administrators</h1>

<div class="grid-2" style="align-items:start;">
  <div class="card">
    <h3>Add Administrator</h3>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add">
      <div class="form-group">
        <label for="full_name">Full Name</label>
        <input type="text" id="full_name" name="full_name" required>
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required minlength="8">
      </div>
      <div class="form-group">
        <label for="role">Role</label>
        <select id="role" name="role">
          <option value="admin">Admin</option>
          <option value="super_admin">Super Admin</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Add Administrator</button>
    </form>
  </div>

  <div class="card" style="padding:0;overflow-x:auto;">
    <table>
      <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($admins as $a): ?>
          <tr>
            <td><?= h($a['full_name']) ?></td>
            <td><?= h($a['email']) ?></td>
            <td><?= h(ucfirst(str_replace('_', ' ', $a['role']))) ?></td>
            <td><span class="badge badge-<?= $a['is_active'] ? 'active' : 'ended' ?>"><?= $a['is_active'] ? 'Active' : 'Inactive' ?></span></td>
            <td>
              <?php if ($a['id'] != current_admin_id()): ?>
                <form method="post">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="toggle">
                  <input type="hidden" name="admin_id" value="<?= $a['id'] ?>">
                  <input type="hidden" name="new_status" value="<?= $a['is_active'] ? 0 : 1 ?>">
                  <button class="btn btn-outline btn-sm"><?= $a['is_active'] ? 'Deactivate' : 'Activate' ?></button>
                </form>
              <?php else: ?>
                <span class="muted" style="font-size:.8rem;">You</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer_admin.php'; ?>
