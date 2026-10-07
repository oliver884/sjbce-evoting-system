<?php
/** Expects $activeNav to be set by the including page (e.g. 'dashboard') */
$activeNav = $activeNav ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($pageTitle ?? 'Admin') ?> — <?= h(SITE_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
<div class="site-wrap">
  <header class="topbar">
    <div class="brand"><img src="<?= BASE_URL ?>assets/img/logo.png" alt="SJBCE" class="brand-logo"> <span class="dot"></span> SJBCE Elections — Admin</div>
    <nav>
      <span style="opacity:.85;font-size:.88rem;">Signed in as <?= h($_SESSION['admin_name'] ?? '') ?></span>
      <a href="<?= BASE_URL ?>admin/logout.php">Log out</a>
    </nav>
  </header>
  <div class="page-body">
    <aside class="sidebar">
      <span class="section-label">Elections</span>
      <a href="<?= BASE_URL ?>admin/dashboard.php" class="<?= $activeNav === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
      <a href="<?= BASE_URL ?>admin/elections.php" class="<?= $activeNav === 'elections' ? 'active' : '' ?>">Elections</a>
      <a href="<?= BASE_URL ?>admin/positions.php" class="<?= $activeNav === 'positions' ? 'active' : '' ?>">Positions</a>
      <a href="<?= BASE_URL ?>admin/candidates.php" class="<?= $activeNav === 'candidates' ? 'active' : '' ?>">Candidates</a>
      <span class="section-label">Voters</span>
      <a href="<?= BASE_URL ?>admin/students.php" class="<?= $activeNav === 'students' ? 'active' : '' ?>">Registered Students</a>
      <a href="<?= BASE_URL ?>admin/import_students.php" class="<?= $activeNav === 'import' ? 'active' : '' ?>">Import Students</a>
      <a href="<?= BASE_URL ?>admin/promote_students.php" class="<?= $activeNav === 'promote' ? 'active' : '' ?>">Promote / Graduate</a>
      <a href="<?= BASE_URL ?>admin/send_sms.php" class="<?= $activeNav === 'sms' ? 'active' : '' ?>">Send SMS</a>
      <span class="section-label">Results</span>
      <a href="<?= BASE_URL ?>admin/results.php" class="<?= $activeNav === 'results' ? 'active' : '' ?>">Live Results</a>
      <?php if (($_SESSION['admin_role'] ?? '') === 'super_admin'): ?>
      <span class="section-label">System</span>
      <a href="<?= BASE_URL ?>admin/admins.php" class="<?= $activeNav === 'admins' ? 'active' : '' ?>">Administrators</a>
      <a href="<?= BASE_URL ?>admin/audit_logs.php" class="<?= $activeNav === 'audit' ? 'active' : '' ?>">Audit Logs</a>
      <?php endif; ?>
    </aside>
    <main class="content">
      <?php foreach (flash_get() as $f): ?>
        <div class="alert alert-<?= h($f['type']) ?>"><?= h($f['message']) ?></div>
      <?php endforeach; ?>
