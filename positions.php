<?php
require_once __DIR__ . '/../config/config.php';
require_admin_login();
$pdo = get_db();

$elections = $pdo->query('SELECT id, title, status FROM elections ORDER BY created_at DESC')->fetchAll();
$electionId = (int) ($_GET['election_id'] ?? ($elections[0]['id'] ?? 0));

if (is_post() && ($_POST['action'] ?? '') === 'add') {
    verify_csrf();
    $eid = (int) $_POST['election_id'];
    $title = trim($_POST['title'] ?? '');
    if ($title === '') {
        flash_set('error', 'Position title is required.');
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO positions (election_id, title, description, created_at) VALUES (?, ?, ?, NOW())');
            $stmt->execute([$eid, $title, trim($_POST['description'] ?? '')]);
            log_action('admin', current_admin_id(), 'CREATE_POSITION', $title);
            flash_set('success', 'Position added.');
        } catch (PDOException $ex) {
            flash_set('error', 'That position already exists for this election.');
        }
    }
    redirect(BASE_URL . 'admin/positions.php?election_id=' . $eid);
}

if (is_post() && ($_POST['action'] ?? '') === 'delete') {
    verify_csrf();
    $id = (int) $_POST['position_id'];
    $eid = (int) $_POST['election_id'];
    $pdo->prepare('DELETE FROM positions WHERE id = ?')->execute([$id]);
    log_action('admin', current_admin_id(), 'DELETE_POSITION', "Position #$id");
    flash_set('success', 'Position deleted.');
    redirect(BASE_URL . 'admin/positions.php?election_id=' . $eid);
}

$positions = [];
if ($electionId) {
    $stmt = $pdo->prepare('SELECT * FROM positions WHERE election_id = ? ORDER BY display_order, id');
    $stmt->execute([$electionId]);
    $positions = $stmt->fetchAll();
}

$activeNav = 'positions';
$pageTitle = 'Positions';
require __DIR__ . '/../includes/header_admin.php';
?>

<h1>Positions</h1>

<div class="form-group" style="max-width:360px;">
  <label for="election_select">Election</label>
  <select id="election_select" onchange="location.href='<?= BASE_URL ?>admin/positions.php?election_id='+this.value">
    <?php foreach ($elections as $e): ?>
      <option value="<?= $e['id'] ?>" <?= $e['id'] == $electionId ? 'selected' : '' ?>><?= h($e['title']) ?></option>
    <?php endforeach; ?>
  </select>
</div>

<?php if (!$elections): ?>
  <div class="card"><p class="muted">Create an election first.</p></div>
<?php else: ?>

<div class="grid-2" style="align-items:start;">
  <div class="card">
    <h3>Add Position</h3>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add">
      <input type="hidden" name="election_id" value="<?= $electionId ?>">
      <div class="form-group">
        <label for="ptitle">Title</label>
        <input type="text" id="ptitle" name="title" required placeholder="e.g. SRC President">
      </div>
      <div class="form-group">
        <label for="pdesc">Description (optional)</label>
        <textarea id="pdesc" name="description" rows="2"></textarea>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Add Position</button>
    </form>
  </div>

  <div>
    <?php if (!$positions): ?>
      <div class="card text-center"><p class="muted">No positions yet for this election.</p></div>
    <?php endif; ?>
    <?php foreach ($positions as $i => $p): ?>
      <div class="card mb-lg">
        <div class="flex-between">
          <div>
            <span class="position-tag">Position <?= $i + 1 ?></span>
            <h3 style="margin:0;"><?= h($p['title']) ?></h3>
            <?php if ($p['description']): ?><p class="muted" style="margin:.3rem 0 0;"><?= h($p['description']) ?></p><?php endif; ?>
          </div>
          <form method="post" onsubmit="return confirm('Delete this position and all its candidates?');">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="position_id" value="<?= $p['id'] ?>">
            <input type="hidden" name="election_id" value="<?= $electionId ?>">
            <button class="btn btn-danger btn-sm">Delete</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php endif; ?>

<?php require __DIR__ . '/../includes/footer_admin.php'; ?>
