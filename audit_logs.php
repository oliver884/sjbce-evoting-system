<?php
require_once __DIR__ . '/../config/config.php';
require_super_admin();
$pdo = get_db();

$logs = $pdo->query('SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 300')->fetchAll();

$activeNav = 'audit';
$pageTitle = 'Audit Logs';
require __DIR__ . '/../includes/header_admin.php';
?>

<h1>Audit Logs</h1>
<p class="muted">Most recent 300 actions across the system.</p>

<div class="card" style="padding:0;overflow-x:auto;">
  <table>
    <thead><tr><th>Time</th><th>Actor</th><th>Action</th><th>Details</th><th>IP</th></tr></thead>
    <tbody>
      <?php foreach ($logs as $l): ?>
        <tr>
          <td style="white-space:nowrap;"><?= format_datetime($l['created_at']) ?></td>
          <td><span class="badge badge-paused"><?= h($l['user_type']) ?></span> #<?= h((string)$l['user_id']) ?></td>
          <td><?= h($l['action']) ?></td>
          <td><?= h($l['details']) ?></td>
          <td class="muted"><?= h($l['ip_address']) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$logs): ?>
        <tr><td colspan="5" class="text-center muted">No activity recorded yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/../includes/footer_admin.php'; ?>
