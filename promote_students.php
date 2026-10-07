<?php
require_once __DIR__ . '/../config/config.php';
require_admin_login();
$pdo = get_db();

const LEVEL_ORDER = ['Level 100', 'Level 200', 'Level 300', 'Level 400'];
const NEXT_LEVEL = [
    'Level 100' => 'Level 200',
    'Level 200' => 'Level 300',
    'Level 300' => 'Level 400',
];

// --- Promote a level up ---
if (is_post() && ($_POST['action'] ?? '') === 'promote') {
    verify_csrf();
    $fromLevel = $_POST['level'] ?? '';

    if (!isset(NEXT_LEVEL[$fromLevel])) {
        flash_set('error', 'Invalid level selected.');
    } else {
        $toLevel = NEXT_LEVEL[$fromLevel];
        $stmt = $pdo->prepare("UPDATE students SET level = ? WHERE level = ? AND status = 'active'");
        $stmt->execute([$toLevel, $fromLevel]);
        $count = $stmt->rowCount();

        log_action('admin', current_admin_id(), 'PROMOTE_STUDENTS', "$count students moved from $fromLevel to $toLevel");
        flash_set('success', "$count student(s) promoted from $fromLevel to $toLevel.");
    }
    redirect(BASE_URL . 'admin/promote_students.php');
}

// --- Mark Level 400 as graduated ---
if (is_post() && ($_POST['action'] ?? '') === 'graduate') {
    verify_csrf();
    $stmt = $pdo->prepare("UPDATE students SET status = 'graduated' WHERE level = 'Level 400' AND status = 'active'");
    $stmt->execute();
    $count = $stmt->rowCount();

    log_action('admin', current_admin_id(), 'GRADUATE_STUDENTS', "$count Level 400 students marked as graduated");
    flash_set('success', "$count student(s) marked as graduated. They can no longer log in or vote, but their past voting history is preserved.");
    redirect(BASE_URL . 'admin/promote_students.php');
}

// Current counts per level (active students only)
$counts = [];
foreach (LEVEL_ORDER as $lvl) {
    $stmt = $pdo->prepare("SELECT COUNT(*) c FROM students WHERE level = ? AND status = 'active'");
    $stmt->execute([$lvl]);
    $counts[$lvl] = (int) $stmt->fetch()['c'];
}

$stmt = $pdo->query("SELECT COUNT(*) c FROM students WHERE status = 'graduated'");
$graduatedCount = (int) $stmt->fetch()['c'];

$activeNav = 'promote';
$pageTitle = 'Promote / Graduate Students';
require __DIR__ . '/../includes/header_admin.php';
?>

<h1>Promote / Graduate Students</h1>
<p class="muted" style="max-width:640px;">
    Run this once a year when moving to a new academic year. Promoting a level moves every
    active student in it up one level. Graduating Level 400 removes their ability to log in
    or vote going forward — their full voting history stays saved permanently.
</p>

<div class="stat-grid">
    <?php foreach (LEVEL_ORDER as $lvl): ?>
        <div class="stat-card">
            <div class="stat-value"><?= $counts[$lvl] ?></div>
            <div class="stat-label"><?= h($lvl) ?></div>
        </div>
    <?php endforeach; ?>
    <div class="stat-card accent-blue">
        <div class="stat-value"><?= $graduatedCount ?></div>
        <div class="stat-label">Graduated (Inactive)</div>
    </div>
</div>

<div class="section-title"><h3>Promote a Level</h3></div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:1rem;">
    <?php foreach (NEXT_LEVEL as $from => $to): ?>
        <div class="card">
            <p style="margin:0 0 .8rem;"><strong><?= h($from) ?></strong> &rarr; <?= h($to) ?></p>
            <p class="muted" style="font-size:.85rem;margin-bottom:1rem;"><?= $counts[$from] ?> student(s) will be moved.</p>
            <form method="post" onsubmit="return confirm('Promote all <?= $counts[$from] ?> students from <?= h($from) ?> to <?= h($to) ?>? This cannot be undone in bulk.');">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="promote">
                <input type="hidden" name="level" value="<?= h($from) ?>">
                <button type="submit" class="btn btn-primary btn-sm" <?= $counts[$from] === 0 ? 'disabled' : '' ?>>Promote</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>

<div class="section-title"><h3>Graduate Level 400</h3></div>
<div class="card" style="max-width:420px;">
    <p class="muted"><?= $counts['Level 400'] ?> student(s) currently in Level 400.</p>
    <form method="post" onsubmit="return confirm('Mark all <?= $counts['Level 400'] ?> Level 400 students as graduated? They will no longer be able to log in or vote in future elections. Their past votes stay recorded permanently.');">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="graduate">
        <button type="submit" class="btn btn-danger btn-sm" <?= $counts['Level 400'] === 0 ? 'disabled' : '' ?>>Mark Level 400 as Graduated</button>
    </form>
</div>

<?php require __DIR__ . '/../includes/footer_admin.php'; ?>
