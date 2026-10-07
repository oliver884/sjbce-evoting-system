<?php
require_once __DIR__ . '/../config/config.php';
require_admin_login();
$pdo = get_db();

$elections = $pdo->query('SELECT id, title, status FROM elections ORDER BY created_at DESC')->fetchAll();
$electionId = (int) ($_GET['election_id'] ?? ($elections[0]['id'] ?? 0));

$results = [];
$totalVoters = (int) $pdo->query("SELECT COUNT(*) c FROM students WHERE status='active'")->fetch()['c'];
$totalVotesCast = 0;

if ($electionId) {
    $stmt = $pdo->prepare('SELECT * FROM positions WHERE election_id = ? ORDER BY display_order, id');
    $stmt->execute([$electionId]);
    $positions = $stmt->fetchAll();

    foreach ($positions as $pos) {
        $stmt = $pdo->prepare(
            'SELECT c.id, c.full_name, c.photo_path, COUNT(v.id) AS votes
             FROM candidates c
             LEFT JOIN votes v ON v.candidate_id = c.id
             WHERE c.position_id = ?
             GROUP BY c.id
             ORDER BY votes DESC'
        );
        $stmt->execute([$pos['id']]);
        $candidates = $stmt->fetchAll();
        $posVotes = array_sum(array_column($candidates, 'votes'));

        // Determine winner(s), correctly handling ties for first place
        $topVoteCount = $candidates ? (int) $candidates[0]['votes'] : 0;
        $winners = $topVoteCount > 0
            ? array_filter($candidates, fn($c) => (int) $c['votes'] === $topVoteCount)
            : [];
        $isTie = count($winners) > 1;

        $results[] = [
            'position' => $pos,
            'candidates' => $candidates,
            'total_votes' => $posVotes,
            'winners' => $winners,
            'is_tie' => $isTie,
        ];
    }

    $stmt = $pdo->prepare('SELECT COUNT(DISTINCT student_id) c FROM votes WHERE election_id = ?');
    $stmt->execute([$electionId]);
    $totalVotesCast = (int) $stmt->fetch()['c'];
}

$turnout = $totalVoters > 0 ? round(($totalVotesCast / $totalVoters) * 100, 1) : 0;

$activeNav = 'results';
$pageTitle = 'Live Results';
require __DIR__ . '/../includes/header_admin.php';
?>

<div class="flex-between">
  <h1>Live Results</h1>
  <?php if ($electionId): ?>
    <a href="<?= BASE_URL ?>admin/export_csv.php?election_id=<?= $electionId ?>" class="btn btn-secondary">Export CSV</a>
  <?php endif; ?>
</div>

<div class="form-group" style="max-width:360px;">
  <label for="election_select">Election</label>
  <select id="election_select" onchange="location.href='<?= BASE_URL ?>admin/results.php?election_id='+this.value">
    <?php foreach ($elections as $e): ?>
      <option value="<?= $e['id'] ?>" <?= $e['id'] == $electionId ? 'selected' : '' ?>><?= h($e['title']) ?></option>
    <?php endforeach; ?>
  </select>
</div>

<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-value"><?= $totalVoters ?></div>
    <div class="stat-label">Registered Voters</div>
  </div>
  <div class="stat-card accent-blue">
    <div class="stat-value"><?= $totalVotesCast ?></div>
    <div class="stat-label">Votes Cast</div>
  </div>
  <div class="stat-card">
    <div class="stat-value"><?= $turnout ?>%</div>
    <div class="stat-label">Turnout</div>
  </div>
</div>

<?php
$electionStatus = null;
foreach ($elections as $e) { if ($e['id'] == $electionId) { $electionStatus = $e['status']; break; } }
$isEnded = $electionStatus === 'ended';
?>

<?php foreach ($results as $r): ?>
  <div class="card mb-lg">
    <div class="position-tag"><?= h($r['position']['title']) ?></div>

    <?php if ($isEnded && $r['winners']): ?>
      <?php if ($r['is_tie']): ?>
        <div class="alert alert-info">
          🏆 <strong>Tie</strong> between: <?= h(implode(', ', array_column($r['winners'], 'full_name'))) ?>
          (<?= (int) reset($r['winners'])['votes'] ?> votes each)
        </div>
      <?php else: ?>
        <div class="alert alert-success">
          🏆 <strong>Winner: <?= h(reset($r['winners'])['full_name']) ?></strong>
          (<?= (int) reset($r['winners'])['votes'] ?> votes)
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <?php if (!$r['candidates']): ?>
      <p class="muted">No candidates for this position.</p>
    <?php endif; ?>
    <?php foreach ($r['candidates'] as $i => $c):
        $pct = $r['total_votes'] > 0 ? round(($c['votes'] / $r['total_votes']) * 100, 1) : 0;
        $isWinner = in_array($c, $r['winners'], true);
    ?>
      <div style="margin-bottom:.9rem;">
        <div class="flex-between" style="margin-bottom:.25rem;">
          <span style="font-weight:600;">
            <?= h($c['full_name']) ?>
            <?php if ($isWinner && !$isEnded): ?><span class="badge badge-active"><?= $r['is_tie'] ? 'Tied for Lead' : 'Leading' ?></span><?php endif; ?>
            <?php if ($isWinner && $isEnded): ?><span class="badge badge-active"><?= $r['is_tie'] ? 'Tied' : 'Winner' ?></span><?php endif; ?>
          </span>
          <span class="muted"><?= $c['votes'] ?> votes (<?= $pct ?>%)</span>
        </div>
        <div class="turnout-bar"><div class="fill" style="width:<?= $pct ?>%;"></div></div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endforeach; ?>

<?php if ($electionId && !$results): ?>
  <div class="card"><p class="muted">No positions set up yet for this election.</p></div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer_admin.php'; ?>
