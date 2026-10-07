<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($pageTitle ?? 'Welcome') ?> — <?= h(SITE_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
<div class="site-wrap">
  <header class="topbar">
    <div class="brand"><img src="<?= BASE_URL ?>assets/img/logo.png" alt="SJBCE" class="brand-logo"> <span class="dot"></span> SJBCE Elections</div>
    <nav>
      <?php if (student_logged_in()): ?>
        <a href="<?= BASE_URL ?>voter/dashboard.php">Dashboard</a>
        <a href="<?= BASE_URL ?>voter/results.php">Results</a>
        <a href="<?= BASE_URL ?>student/profile.php">Profile</a>
        <a href="<?= BASE_URL ?>student/logout.php">Log out</a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>student/login.php">Student Login</a>
        <a href="<?= BASE_URL ?>student/request_otp.php">Get Access Code</a>
      <?php endif; ?>
    </nav>
  </header>
  <div class="page-body" style="display:block;">
    <main class="content" style="max-width:1100px;margin:0 auto;">
      <?php foreach (flash_get() as $f): ?>
        <div class="alert alert-<?= h($f['type']) ?>"><?= h($f['message']) ?></div>
      <?php endforeach; ?>
