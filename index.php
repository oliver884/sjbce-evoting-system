<?php
require_once __DIR__ . '/config/config.php';
$pageTitle = 'Welcome';
require __DIR__ . '/includes/header_site.php';
?>

<div class="photo-slideshow" style="min-height:60vh;">
  <div class="slide"></div>
  <div class="slide"></div>
  <div class="slide"></div>
  <div class="slide"></div>
  <div class="slide"></div>
  <div class="slide"></div>
  <div class="slide-overlay"></div>

  <div class="text-center" style="position:relative;z-index:1;padding:3rem 1rem;max-width:680px;">
    <div class="position-tag" style="background:rgba(255,255,255,0.15);color:var(--white);">St. John Bosco College of Education</div>
    <h1 style="font-size:2.4rem;color:var(--white);">Student Election Portal</h1>
    <p style="max-width:520px;margin:0 auto 2rem;color:var(--sky-tint);">
      Get your one-time access code by email and cast your vote securely — one vote per position, every time.
    </p>
    <div class="flex gap-md" style="justify-content:center;">
      <a href="<?= BASE_URL ?>student/login.php" class="btn btn-primary">Student Login</a>
      <a href="<?= BASE_URL ?>student/request_otp.php" class="btn btn-secondary">Get Access Code</a>
    </div>
  </div>
</div>

<div class="grid-2" style="margin-top:1.5rem;">
  <div class="card">
    <h3>How voting works</h3>
    <p class="muted">Only students on the official roster can access the system. Request your access code by email, verify it, set a password for this election, then vote once per position from your dashboard. Your vote is final and confidential.</p>
  </div>
  <div class="card">
    <h3>Need help?</h3>
    <p class="muted">Your password only works for the current election — for each new election you'll need a fresh access code. If you didn't receive one, contact the Student Affairs office.</p>
  </div>
</div>

<?php require __DIR__ . '/includes/footer_site.php'; ?>
