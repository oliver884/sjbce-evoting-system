<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/sms.php';
require_admin_login();
$pdo = get_db();

// Current election (used for the "haven't voted yet" option)
$election = $pdo->query("SELECT id, title, end_time FROM elections WHERE status = 'active' ORDER BY id DESC LIMIT 1")->fetch();

// Levels that currently have active students
$levels = $pdo->query("SELECT DISTINCT level FROM students WHERE status = 'active' ORDER BY level")->fetchAll(PDO::FETCH_COLUMN);

$defaultMessage = "SJBCE Elections: Voting is now open! Go to {link} , enter your registered email to get an access code, then cast your vote.";

/** Collect the unique, valid phone numbers for an audience. */
function sms_collect_numbers(PDO $pdo, string $audience, string $level, ?int $electionId): array
{
    $sql = "SELECT phone FROM students WHERE status = 'active' AND phone <> ''";
    $params = [];
    if ($audience === 'level') {
        $sql .= " AND level = ?";
        $params[] = $level;
    } elseif ($audience === 'notvoted' && $electionId) {
        $sql .= " AND id NOT IN (SELECT student_id FROM votes WHERE election_id = ?)";
        $params[] = $electionId;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $valid = [];
    $invalid = 0;
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $raw) {
        $n = sms_normalize_phone($raw);
        if ($n) { $valid[$n] = true; } else { $invalid++; }
    }
    return ['numbers' => array_keys($valid), 'invalid' => $invalid];
}

function sms_audience_label(string $audience, string $level): string
{
    return match ($audience) {
        'level'    => 'Level: ' . $level,
        'notvoted' => 'Students who have not voted yet',
        default    => 'All active students',
    };
}

$message  = trim($_POST['message'] ?? $defaultMessage);
$audience = $_POST['audience'] ?? 'all';
$level    = trim($_POST['level'] ?? '');
$preview  = null;

if (!in_array($audience, ['all', 'level', 'notvoted'], true)) $audience = 'all';

if (is_post()) {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $finalText = str_replace('{link}', sms_access_link(), $message);

    if (!sms_configured()) {
        flash_set('error', 'SMS is not set up yet. Add your API key in config/sms.php.');
        redirect(BASE_URL . 'admin/send_sms.php');
    }

    if ($action === 'test') {
        $to = sms_normalize_phone($_POST['test_phone'] ?? '');
        if (!$to) {
            flash_set('error', 'Enter a valid test phone number (e.g. 0244123456).');
        } elseif ($message === '') {
            flash_set('error', 'Message cannot be empty.');
        } else {
            $r = sms_send_batch([$to], $finalText);
            if ($r['ok']) {
                flash_set('success', 'Test SMS sent to ' . $to . '.');
            } else {
                flash_set('error', 'Test failed: ' . substr($r['raw'], 0, 300));
            }
            log_action('admin', current_admin_id(), 'SMS_TEST', 'Test SMS to ' . $to . ($r['ok'] ? ' (sent)' : ' (failed)'));
        }
        redirect(BASE_URL . 'admin/send_sms.php');
    }

    if ($message === '') {
        flash_set('error', 'Message cannot be empty.');
        redirect(BASE_URL . 'admin/send_sms.php');
    }
    if ($audience === 'level' && $level === '') {
        flash_set('error', 'Choose a level.');
        redirect(BASE_URL . 'admin/send_sms.php');
    }
    if ($audience === 'notvoted' && !$election) {
        flash_set('error', 'There is no active election, so "not voted yet" cannot be used.');
        redirect(BASE_URL . 'admin/send_sms.php');
    }

    $collected = sms_collect_numbers($pdo, $audience, $level, $election['id'] ?? null);
    $numbers = $collected['numbers'];

    if (!$numbers) {
        flash_set('error', 'No valid phone numbers found for that selection.');
        redirect(BASE_URL . 'admin/send_sms.php');
    }

    if ($action === 'preview') {
        $preview = [
            'count'   => count($numbers),
            'invalid' => $collected['invalid'],
            'credits' => count($numbers) * sms_parts($finalText),
            'text'    => $finalText,
        ];
    }

    if ($action === 'send') {
        set_time_limit(300);
        $sent = 0;
        $failed = 0;
        $errors = [];

        foreach (array_chunk($numbers, 100) as $chunk) {
            $r = sms_send_batch($chunk, $finalText);
            if ($r['ok']) {
                $sent += count($chunk);
            } else {
                $failed += count($chunk);
                $errors[] = substr($r['raw'], 0, 300);
            }
            usleep(300000);
        }

        $pdo->prepare(
            'INSERT INTO sms_logs (admin_id, audience, message, total_recipients, sent_count, failed_count, error_detail, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())'
        )->execute([
            current_admin_id(),
            sms_audience_label($audience, $level),
            $finalText,
            count($numbers),
            $sent,
            $failed,
            $errors ? implode(' | ', array_unique($errors)) : null,
        ]);
        log_action('admin', current_admin_id(), 'SEND_SMS', "$sent sent, $failed failed (" . sms_audience_label($audience, $level) . ')');

        if ($failed === 0) {
            flash_set('success', "SMS sent to $sent students.");
        } else {
            flash_set('error', "$sent sent, $failed failed. See the history below for details.");
        }
        redirect(BASE_URL . 'admin/send_sms.php');
    }
}

// Recent history (table may not exist until add_sms_logs.sql has been run)
$history = [];
$tableMissing = false;
try {
    $history = $pdo->query('SELECT * FROM sms_logs ORDER BY id DESC LIMIT 15')->fetchAll();
} catch (PDOException $e) {
    $tableMissing = true;
}

$activeNav = 'sms';
$pageTitle = 'Send SMS';
require __DIR__ . '/../includes/header_admin.php';
?>

<h1>Send SMS to Students</h1>
<p class="muted" style="max-width:640px;">
    Send students the link to log in and vote. They open the link, enter their registered email to get an
    access code, then vote. Only active students on the roster receive messages.
</p>

<?php if (!sms_configured()): ?>
    <div class="alert alert-error" style="max-width:640px;">
        SMS is not set up yet. Open <code>config/sms.php</code> and add your gateway API key and Sender ID.
    </div>
<?php endif; ?>
<?php if ($tableMissing): ?>
    <div class="alert alert-error" style="max-width:640px;">
        The <code>sms_logs</code> table is missing. Run <code>add_sms_logs.sql</code> in phpMyAdmin first.
    </div>
<?php endif; ?>

<?php if ($preview): ?>
    <div class="card mb-lg" style="max-width:640px;">
        <h3>Confirm before sending</h3>
        <p>This will send to <strong><?= (int) $preview['count'] ?></strong> students
           (about <strong><?= (int) $preview['credits'] ?></strong> SMS credits).
           <?php if ($preview['invalid'] > 0): ?>
               <br><span class="muted"><?= (int) $preview['invalid'] ?> student(s) skipped because their phone number is invalid.</span>
           <?php endif; ?>
        </p>
        <p style="background:var(--wine-tint);padding:.75rem;border-radius:var(--radius);"><?= nl2br(h($preview['text'])) ?></p>
        <form method="post" class="flex gap-sm">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="send">
            <input type="hidden" name="message" value="<?= h($message) ?>">
            <input type="hidden" name="audience" value="<?= h($audience) ?>">
            <input type="hidden" name="level" value="<?= h($level) ?>">
            <button type="submit" class="btn btn-primary">Confirm &amp; Send</button>
            <a href="<?= BASE_URL ?>admin/send_sms.php" class="btn btn-outline">Cancel</a>
        </form>
    </div>
<?php else: ?>
    <div class="card mb-lg" style="max-width:640px;">
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="preview">

            <div class="form-group">
                <label for="audience">Send to</label>
                <select id="audience" name="audience" onchange="document.getElementById('levelBox').style.display = this.value === 'level' ? 'block' : 'none';">
                    <option value="all" <?= $audience === 'all' ? 'selected' : '' ?>>All active students</option>
                    <option value="level" <?= $audience === 'level' ? 'selected' : '' ?>>One level only</option>
                    <?php if ($election): ?>
                        <option value="notvoted" <?= $audience === 'notvoted' ? 'selected' : '' ?>>Students who haven't voted yet (<?= h($election['title']) ?>)</option>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group" id="levelBox" style="display:<?= $audience === 'level' ? 'block' : 'none' ?>;">
                <label for="level">Level</label>
                <select id="level" name="level">
                    <?php foreach ($levels as $lv): ?>
                        <option value="<?= h($lv) ?>" <?= $level === $lv ? 'selected' : '' ?>><?= h($lv) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" rows="4" required oninput="document.getElementById('cnt').textContent = this.value.length;"><?= h($message) ?></textarea>
                <small class="muted"><span id="cnt"><?= sms_strlen($message) ?></span> characters. <code>{link}</code> is replaced with the login link. Messages over 160 characters (after the link is added) use extra credits.</small>
            </div>

            <button type="submit" class="btn btn-primary">Preview</button>
        </form>
    </div>

    <div class="card mb-lg" style="max-width:640px;">
        <h3>Send a test first</h3>
        <p class="muted" style="font-size:.85rem;">Sends the message above to one number so you can check it before texting everyone.</p>
        <form method="post" class="flex gap-sm">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="test">
            <input type="hidden" name="message" value="<?= h($message) ?>">
            <input type="text" name="test_phone" placeholder="e.g. 0244123456" required>
            <button type="submit" class="btn btn-secondary">Send test</button>
        </form>
    </div>
<?php endif; ?>

<h3>Recent messages</h3>
<div class="card" style="padding:0;overflow-x:auto;">
    <table>
        <thead><tr><th>When</th><th>Audience</th><th>Sent</th><th>Failed</th><th>Message</th></tr></thead>
        <tbody>
        <?php if (!$history): ?>
            <tr><td colspan="5" class="muted">No messages sent yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($history as $row): ?>
            <tr>
                <td><?= format_datetime($row['created_at']) ?></td>
                <td><?= h($row['audience']) ?></td>
                <td><?= (int) $row['sent_count'] ?></td>
                <td><?= (int) $row['failed_count'] ?><?php if ($row['error_detail']): ?><br><small class="muted"><?= h($row['error_detail']) ?></small><?php endif; ?></td>
                <td style="max-width:320px;"><?= h($row['message']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../includes/footer_admin.php'; ?>
