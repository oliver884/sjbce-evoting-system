<?php
require_once __DIR__ . '/../config/config.php';
require_admin_login();

$pdo = get_db();

// Current/most recent election (active first, then most recently created)
$election = $pdo->query(
    "SELECT * FROM elections
     ORDER BY FIELD(status,'active','paused','pending','ended'), created_at DESC
     LIMIT 1"
)->fetch();

$stats = [
    'total_students'   => (int) $pdo->query("SELECT COUNT(*) c FROM students WHERE status='active'")->fetch()['c'],
    'total_candidates' => 0,
    'total_votes'      => 0,
    'positions'        => 0,
];

if ($election) {
    $eid = $election['id'];

    $stmt = $pdo->prepare("SELECT COUNT(*) c FROM candidates WHERE election_id = ?");
    $stmt->execute([$eid]);
    $stats['total_candidates'] = (int) $stmt->fetch()['c'];

    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT student_id) c FROM votes WHERE election_id = ?");
    $stmt->execute([$eid]);
    $stats['total_votes'] = (int) $stmt->fetch()['c'];

    $stmt = $pdo->prepare("SELECT COUNT(*) c FROM positions WHERE election_id = ?");
    $stmt->execute([$eid]);
    $stats['positions'] = (int) $stmt->fetch()['c'];
}

$turnout = $stats['total_students'] > 0
    ? round(($stats['total_votes'] / $stats['total_students']) * 100, 1)
    : 0;

$activeNav = 'dashboard';
$pageTitle = 'Dashboard';
require __DIR__ . '/../includes/header_admin.php';
?>

<div class="flex-between mb-lg">
  <h1>Dashboard</h1>
  <a href="<?= BASE_URL ?>admin/elections.php" class="btn btn-primary">Manage Elections</a>
</div>

<?php if (!$election): ?>
  <div class="card text-center" style="padding:3rem;">
    <h3>No election has been created yet</h3>
    <p class="muted">Create your first election to start adding positions and candidates.</p>
    <a href="<?= BASE_URL ?>admin/elections.php" class="btn btn-primary">Create Election</a>
  </div>
<?php else: ?>

  <div class="card mb-lg">
    <div class="flex-between">
      <div>
        <span class="badge badge-<?= h($election['status']) ?>"><?= h(ucfirst($election['status'])) ?></span>
        <h2 style="margin-top:.5rem;"><?= h($election['title']) ?></h2>
        <p class="muted" style="margin:0;">
          <?= format_datetime($election['start_time']) ?> &nbsp;→&nbsp; <?= format_datetime($election['end_time']) ?>
        </p>
      </div>
      <a href="<?= BASE_URL ?>admin/elections.php" class="btn btn-outline btn-sm">Manage</a>
    </div>
  </div>

  <div class="stat-grid">
    <div class="stat-card">
      <div class="stat-value"><?= $stats['total_students'] ?></div>
      <div class="stat-label">Registered Voters</div>
    </div>
    <div class="stat-card accent-blue">
      <div class="stat-value"><?= $stats['total_votes'] ?></div>
      <div class="stat-label">Votes Cast</div>
    </div>
    <div class="stat-card">
      <div class="stat-value"><?= $stats['positions'] ?></div>
      <div class="stat-label">Positions</div>
    </div>
    <div class="stat-card accent-blue">
      <div class="stat-value"><?= $stats['total_candidates'] ?></div>
      <div class="stat-label">Candidates</div>
    </div>
  </div>

  <div class="card">
    <div class="flex-between" style="margin-bottom:.6rem;">
      <h3 style="margin:0;">Voter Turnout</h3>
      <span class="muted"><?= $turnout ?>%</span>
    </div>
    <div class="turnout-bar"><div class="fill" style="width:<?= min($turnout, 100) ?>%;"></div></div>
  </div>

  <div class="section-title"><h3 style="margin:0;">Quick Actions</h3></div>
  <div class="flex gap-md">
    <a href="<?= BASE_URL ?>admin/positions.php" class="btn btn-secondary">Manage Positions</a>
    <a href="<?= BASE_URL ?>admin/candidates.php" class="btn btn-secondary">Manage Candidates</a>
    <a href="<?= BASE_URL ?>admin/results.php" class="btn btn-secondary">View Live Results</a>
  </div>

<?php endif; ?>

<?php require __DIR__ . '/../includes/footer_admin.php'; ?>
