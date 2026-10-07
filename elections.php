<?php
require_once __DIR__ . '/../config/config.php';
require_admin_login();
$pdo = get_db();

// --- Create new election ---
if (is_post() && ($_POST['action'] ?? '') === 'create') {
    verify_csrf();
    $title      = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $start      = $_POST['start_time'] ?? '';
    $end        = $_POST['end_time'] ?? '';

    $errors = [];
    if ($title === '') $errors[] = 'Title is required.';
    if (!$start || !$end) $errors[] = 'Start and end time are required.';
    if ($start && $end && strtotime($start) >= strtotime($end)) {
        $errors[] = 'End time must be after start time.';
    }

    if ($errors) {
        flash_set('error', implode(' ', $errors));
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO elections (title, description, start_time, end_time, status, created_by, created_at)
             VALUES (?, ?, ?, ?, 'pending', ?, NOW())"
        );
        $stmt->execute([$title, $description, $start, $end, current_admin_id()]);
        log_action('admin', current_admin_id(), 'CREATE_ELECTION', $title);
        flash_set('success', 'Election created.');
    }
    redirect(BASE_URL . 'admin/elections.php');
}

// --- Edit an existing election (title, description, schedule) ---
if (is_post() && ($_POST['action'] ?? '') === 'update') {
    verify_csrf();
    $id = (int) $_POST['election_id'];
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $start = $_POST['start_time'] ?? '';
    $end   = $_POST['end_time'] ?? '';

    $errors = [];
    if ($title === '') $errors[] = 'Title is required.';
    if (!$start || !$end) $errors[] = 'Start and end time are required.';
    if ($start && $end && strtotime($start) >= strtotime($end)) {
        $errors[] = 'End time must be after start time.';
    }

    if ($errors) {
        flash_set('error', implode(' ', $errors));
    } else {
        $stmt = $pdo->prepare(
            'UPDATE elections SET title = ?, description = ?, start_time = ?, end_time = ? WHERE id = ?'
        );
        $stmt->execute([$title, $description, $start, $end, $id]);
        log_action('admin', current_admin_id(), 'UPDATE_ELECTION', "Election #$id updated ($title)");
        flash_set('success', 'Election updated.');
    }
    redirect(BASE_URL . 'admin/elections.php');
}

// --- Status transitions ---
if (is_post() && ($_POST['action'] ?? '') === 'set_status') {
    verify_csrf();
    $id = (int) $_POST['election_id'];
    $newStatus = $_POST['status'];
    $allowed = ['pending', 'active', 'paused', 'ended'];

    if (in_array($newStatus, $allowed, true)) {
        $stmt = $pdo->prepare('UPDATE elections SET status = ? WHERE id = ?');
        $stmt->execute([$newStatus, $id]);
        log_action('admin', current_admin_id(), 'ELECTION_STATUS_CHANGE', "Election #$id -> $newStatus");
        flash_set('success', 'Election status updated to ' . ucfirst($newStatus) . '.');
    }
    redirect(BASE_URL . 'admin/elections.php');
}

$elections = $pdo->query('SELECT * FROM elections ORDER BY created_at DESC')->fetchAll();

$activeNav = 'elections';
$pageTitle = 'Elections';
require __DIR__ . '/../includes/header_admin.php';
?>

<h1>Elections</h1>

<div class="grid-2" style="align-items:start;">
  <div class="card">
    <h3>Create Election</h3>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="create">
      <div class="form-group">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" required placeholder="e.g. SRC General Elections 2026">
      </div>
      <div class="form-group">
        <label for="description">Description (optional)</label>
        <textarea id="description" name="description" rows="3"></textarea>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="start_time">Start</label>
          <input type="datetime-local" id="start_time" name="start_time" required>
        </div>
        <div class="form-group">
          <label for="end_time">End</label>
          <input type="datetime-local" id="end_time" name="end_time" required>
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Create Election</button>
      <p class="help-text">New elections start as <strong>Pending</strong>. Use the controls below to open voting.</p>
    </form>
  </div>

  <div>
    <?php if (!$elections): ?>
      <div class="card text-center"><p class="muted">No elections yet.</p></div>
    <?php endif; ?>

    <?php foreach ($elections as $e): ?>
      <div class="card mb-lg">
        <div class="flex-between">
          <div>
            <span class="badge badge-<?= h($e['status']) ?>"><?= h(ucfirst($e['status'])) ?></span>
            <h3 style="margin:.4rem 0 0;"><?= h($e['title']) ?></h3>
            <p class="muted" style="margin:.2rem 0 0;font-size:.85rem;">
              <?= format_datetime($e['start_time']) ?> → <?= format_datetime($e['end_time']) ?>
            </p>
          </div>
        </div>

        <div class="flex gap-sm mt-lg" style="flex-wrap:wrap;">
          <?php if ($e['status'] === 'pending'): ?>
            <form method="post"><?= csrf_field() ?>
              <input type="hidden" name="action" value="set_status">
              <input type="hidden" name="election_id" value="<?= $e['id'] ?>">
              <input type="hidden" name="status" value="active">
              <button class="btn btn-primary btn-sm">Start Voting</button>
            </form>
          <?php elseif ($e['status'] === 'active'): ?>
            <form method="post"><?= csrf_field() ?>
              <input type="hidden" name="action" value="set_status">
              <input type="hidden" name="election_id" value="<?= $e['id'] ?>">
              <input type="hidden" name="status" value="paused">
              <button class="btn btn-secondary btn-sm">Pause</button>
            </form>
            <form method="post"><?= csrf_field() ?>
              <input type="hidden" name="action" value="set_status">
              <input type="hidden" name="election_id" value="<?= $e['id'] ?>">
              <input type="hidden" name="status" value="ended">
              <button class="btn btn-danger btn-sm" onclick="return confirm('End this election? Voting cannot resume after this.')">End Election</button>
            </form>
          <?php elseif ($e['status'] === 'paused'): ?>
            <form method="post"><?= csrf_field() ?>
              <input type="hidden" name="action" value="set_status">
              <input type="hidden" name="election_id" value="<?= $e['id'] ?>">
              <input type="hidden" name="status" value="active">
              <button class="btn btn-primary btn-sm">Resume</button>
            </form>
            <form method="post"><?= csrf_field() ?>
              <input type="hidden" name="action" value="set_status">
              <input type="hidden" name="election_id" value="<?= $e['id'] ?>">
              <input type="hidden" name="status" value="ended">
              <button class="btn btn-danger btn-sm" onclick="return confirm('End this election?')">End Election</button>
            </form>
          <?php elseif ($e['status'] === 'ended'): ?>
            <form method="post"><?= csrf_field() ?>
              <input type="hidden" name="action" value="set_status">
              <input type="hidden" name="election_id" value="<?= $e['id'] ?>">
              <input type="hidden" name="status" value="active">
              <button class="btn btn-primary btn-sm">Reopen for Voting</button>
            </form>
            <p class="help-text" style="margin:0;">Edit the dates below first if you want a fresh voting window.</p>
          <?php endif; ?>

          <a href="<?= BASE_URL ?>admin/positions.php?election_id=<?= $e['id'] ?>" class="btn btn-outline btn-sm">Positions</a>
          <a href="<?= BASE_URL ?>admin/results.php?election_id=<?= $e['id'] ?>" class="btn btn-outline btn-sm">Results</a>
        </div>

        <details style="margin-top:1rem;">
          <summary style="cursor:pointer;font-weight:600;font-size:.85rem;color:var(--wine);">Edit title, description, or schedule</summary>
          <form method="post" style="margin-top:.9rem;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="election_id" value="<?= $e['id'] ?>">
            <div class="form-group">
              <label for="edit_title_<?= $e['id'] ?>">Title</label>
              <input type="text" id="edit_title_<?= $e['id'] ?>" name="title" required value="<?= h($e['title']) ?>">
            </div>
            <div class="form-group">
              <label for="edit_desc_<?= $e['id'] ?>">Description</label>
              <textarea id="edit_desc_<?= $e['id'] ?>" name="description" rows="2"><?= h($e['description']) ?></textarea>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label for="edit_start_<?= $e['id'] ?>">Start</label>
                <input type="datetime-local" id="edit_start_<?= $e['id'] ?>" name="start_time" required
                       value="<?= h(date('Y-m-d\TH:i', strtotime($e['start_time']))) ?>">
              </div>
              <div class="form-group">
                <label for="edit_end_<?= $e['id'] ?>">End</label>
                <input type="datetime-local" id="edit_end_<?= $e['id'] ?>" name="end_time" required
                       value="<?= h(date('Y-m-d\TH:i', strtotime($e['end_time']))) ?>">
              </div>
            </div>
            <button type="submit" class="btn btn-secondary btn-sm">Save Changes</button>
          </form>
        </details>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<p class="help-text mt-lg">Voting automatically opens and closes based on start/end time even while status is "Active" — <code>voter/cast_vote.php</code> re-checks the time window on every vote.</p>

<?php require __DIR__ . '/../includes/footer_admin.php'; ?>
