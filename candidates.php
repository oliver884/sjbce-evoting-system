<?php
require_once __DIR__ . '/../config/config.php';
require_admin_login();
$pdo = get_db();

$elections = $pdo->query('SELECT id, title FROM elections ORDER BY created_at DESC')->fetchAll();
$electionId = (int) ($_GET['election_id'] ?? ($elections[0]['id'] ?? 0));

$positions = [];
if ($electionId) {
    $stmt = $pdo->prepare('SELECT * FROM positions WHERE election_id = ? ORDER BY display_order, id');
    $stmt->execute([$electionId]);
    $positions = $stmt->fetchAll();
}

function handle_photo_upload(): ?string
{
    if (empty($_FILES['photo']['name'])) return null;

    $file = $_FILES['photo'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        flash_set('error', 'Photo upload failed.');
        return null;
    }
    if ($file['size'] > MAX_PHOTO_BYTES) {
        flash_set('error', 'Photo must be under 2MB.');
        return null;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($allowed[$mime])) {
        flash_set('error', 'Only JPG, PNG, or WEBP photos are allowed.');
        return null;
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $dest = UPLOAD_DIR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        flash_set('error', 'Could not save photo.');
        return null;
    }
    return $filename;
}

// --- Add candidate ---
if (is_post() && ($_POST['action'] ?? '') === 'add') {
    verify_csrf();
    $eid   = (int) $_POST['election_id'];
    $pid   = (int) $_POST['position_id'];
    $name  = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $manifesto = trim($_POST['manifesto'] ?? '');

    if ($name === '' || !$pid) {
        flash_set('error', 'Candidate name and position are required.');
    } else {
        $photo = handle_photo_upload();
        $stmt = $pdo->prepare(
            'INSERT INTO candidates (election_id, position_id, full_name, phone, photo_path, manifesto, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([$eid, $pid, $name, $phone, $photo, $manifesto]);
        log_action('admin', current_admin_id(), 'CREATE_CANDIDATE', $name);
        flash_set('success', 'Candidate added.');
    }
    redirect(BASE_URL . 'admin/candidates.php?election_id=' . $eid);
}

// --- Delete candidate ---
if (is_post() && ($_POST['action'] ?? '') === 'delete') {
    verify_csrf();
    $id  = (int) $_POST['candidate_id'];
    $eid = (int) $_POST['election_id'];

    $stmt = $pdo->prepare('SELECT photo_path FROM candidates WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row && $row['photo_path'] && file_exists(UPLOAD_DIR . $row['photo_path'])) {
        @unlink(UPLOAD_DIR . $row['photo_path']);
    }
    $pdo->prepare('DELETE FROM candidates WHERE id = ?')->execute([$id]);
    log_action('admin', current_admin_id(), 'DELETE_CANDIDATE', "Candidate #$id");
    flash_set('success', 'Candidate removed.');
    redirect(BASE_URL . 'admin/candidates.php?election_id=' . $eid);
}

$candidates = [];
if ($electionId) {
    $stmt = $pdo->prepare(
        'SELECT c.*, p.title AS position_title FROM candidates c
         JOIN positions p ON p.id = c.position_id
         WHERE c.election_id = ? ORDER BY p.display_order, p.id, c.full_name'
    );
    $stmt->execute([$electionId]);
    $candidates = $stmt->fetchAll();
}

$activeNav = 'candidates';
$pageTitle = 'Candidates';
require __DIR__ . '/../includes/header_admin.php';
?>

<h1>Candidates</h1>

<div class="form-group" style="max-width:360px;">
  <label for="election_select">Election</label>
  <select id="election_select" onchange="location.href='<?= BASE_URL ?>admin/candidates.php?election_id='+this.value">
    <?php foreach ($elections as $e): ?>
      <option value="<?= $e['id'] ?>" <?= $e['id'] == $electionId ? 'selected' : '' ?>><?= h($e['title']) ?></option>
    <?php endforeach; ?>
  </select>
</div>

<?php if (!$positions): ?>
  <div class="card"><p class="muted">Add positions for this election before adding candidates.</p></div>
<?php else: ?>

<div class="card mb-lg">
  <h3>Add Candidate</h3>
  <form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add">
    <input type="hidden" name="election_id" value="<?= $electionId ?>">
    <div class="form-row">
      <div class="form-group">
        <label for="full_name">Full Name</label>
        <input type="text" id="full_name" name="full_name" required>
      </div>
      <div class="form-group">
        <label for="position_id">Position</label>
        <select id="position_id" name="position_id" required>
          <?php foreach ($positions as $p): ?>
            <option value="<?= $p['id'] ?>"><?= h($p['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone">
      </div>
      <div class="form-group">
        <label for="photo">Photo</label>
        <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp">
      </div>
    </div>
    <div class="form-group">
      <label for="manifesto">Manifesto (optional)</label>
      <textarea id="manifesto" name="manifesto" rows="3"></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Add Candidate</button>
  </form>
</div>

<?php
$grouped = [];
foreach ($candidates as $c) { $grouped[$c['position_title']][] = $c; }
?>

<?php foreach ($grouped as $posTitle => $list): ?>
  <div class="section-title"><h3><?= h($posTitle) ?></h3></div>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1rem;">
    <?php foreach ($list as $c): ?>
      <div class="ballot-stub">
        <div class="stub-photo" style="<?= $c['photo_path'] ? "background-image:url('" . h(UPLOAD_URL . $c['photo_path']) . "')" : '' ?>">
          <?= $c['photo_path'] ? '' : h(strtoupper(substr($c['full_name'], 0, 1))) ?>
        </div>
        <div class="stub-divider"></div>
        <div class="stub-body">
          <div class="stub-name"><?= h($c['full_name']) ?></div>
          <?php if ($c['phone']): ?><small class="muted"><?= h($c['phone']) ?></small><?php endif; ?>
          <?php if ($c['manifesto']): ?><div class="stub-manifesto"><?= h($c['manifesto']) ?></div><?php endif; ?>
          <div class="stub-actions">
            <form method="post" onsubmit="return confirm('Remove this candidate?');">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="candidate_id" value="<?= $c['id'] ?>">
              <input type="hidden" name="election_id" value="<?= $electionId ?>">
              <button class="btn btn-danger btn-sm">Remove</button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endforeach; ?>

<?php if (!$candidates): ?>
  <div class="card text-center"><p class="muted">No candidates added yet.</p></div>
<?php endif; ?>

<?php endif; ?>

<?php require __DIR__ . '/../includes/footer_admin.php'; ?>
