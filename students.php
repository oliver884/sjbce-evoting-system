<?php
require_once __DIR__ . '/../config/config.php';
require_admin_login();
$pdo = get_db();

if (is_post() && ($_POST['action'] ?? '') === 'toggle_status') {
    verify_csrf();
    $id = (int) $_POST['student_id'];
    $newStatus = $_POST['new_status'] === 'active' ? 'active' : 'suspended';
    $pdo->prepare('UPDATE students SET status = ? WHERE id = ?')->execute([$newStatus, $id]);
    log_action('admin', current_admin_id(), 'STUDENT_STATUS_CHANGE', "Student #$id -> $newStatus");
    flash_set('success', 'Student status updated.');
    redirect(BASE_URL . 'admin/students.php');
}

$search = trim($_GET['q'] ?? '');
if ($search !== '') {
    $stmt = $pdo->prepare(
        "SELECT * FROM students WHERE full_name LIKE ? OR student_id LIKE ? OR email LIKE ?
         ORDER BY created_at DESC LIMIT 200"
    );
    $like = '%' . $search . '%';
    $stmt->execute([$like, $like, $like]);
} else {
    $stmt = $pdo->query('SELECT * FROM students ORDER BY created_at DESC LIMIT 200');
}
$students = $stmt->fetchAll();

$activeNav = 'students';
$pageTitle = 'Registered Students';
require __DIR__ . '/../includes/header_admin.php';
?>

<h1>Registered Students</h1>

<form method="get" class="flex gap-sm mb-lg" style="max-width:420px;">
  <input type="text" name="q" placeholder="Search name, student ID, or email" value="<?= h($search) ?>">
  <button class="btn btn-secondary">Search</button>
</form>

<div class="card" style="padding:0;overflow-x:auto;">
  <table>
    <thead>
      <tr><th>Student ID</th><th>Name</th><th>Email</th><th>Department</th><th>Level</th><th>Status</th><th>Registered</th><th></th></tr>
    </thead>
    <tbody>
      <?php foreach ($students as $s): ?>
        <tr>
          <td><?= h($s['student_id']) ?></td>
          <td><?= h($s['full_name']) ?></td>
          <td><?= h($s['email']) ?></td>
          <td><?= h($s['department']) ?></td>
          <td><?= h($s['level']) ?></td>
          <td><span class="badge badge-<?= $s['status'] === 'active' ? 'active' : ($s['status'] === 'graduated' ? 'paused' : 'ended') ?>"><?= h(ucfirst($s['status'])) ?></span></td>
          <td><?= format_datetime($s['created_at']) ?></td>
          <td>
            <?php if ($s['status'] === 'graduated'): ?>
              <span class="muted" style="font-size:.8rem;">No longer eligible to vote</span>
            <?php else: ?>
              <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="toggle_status">
                <input type="hidden" name="student_id" value="<?= $s['id'] ?>">
                <input type="hidden" name="new_status" value="<?= $s['status'] === 'active' ? 'suspended' : 'active' ?>">
                <button class="btn btn-outline btn-sm"><?= $s['status'] === 'active' ? 'Suspend' : 'Reactivate' ?></button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$students): ?>
        <tr><td colspan="8" class="text-center muted">No students found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/../includes/footer_admin.php'; ?>
