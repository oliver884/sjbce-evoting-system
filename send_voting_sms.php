<?php
/**
 * Automatic voting SMS. Call this URL every 5 minutes from a scheduler:
 *   https://YOUR-SITE/sjbce/cron/send_voting_sms.php?token=YOUR_SMS_CRON_TOKEN
 *
 * It sends:
 *   1. "Voting is open" SMS to all active students, once per election, as soon as
 *      the election is Active and its start time has passed.
 *   2. (optional) a reminder to students who haven't voted, SMS_REMINDER_HOURS before closing.
 * Each message is logged in sms_logs, so it can never be sent twice for the same election.
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/sms.php';

header('Content-Type: text/plain; charset=utf-8');

$isCli = (PHP_SAPI === 'cli');
$token = $isCli ? ($argv[1] ?? '') : ($_GET['token'] ?? '');
if (SMS_CRON_TOKEN === 'CHANGE_THIS_TO_A_LONG_RANDOM_TEXT' || !hash_equals(SMS_CRON_TOKEN, (string) $token)) {
    http_response_code(403);
    exit("Forbidden\n");
}
if (!sms_configured()) {
    exit("SMS not configured (config/sms.php)\n");
}

$pdo = get_db();
$now = date('Y-m-d H:i:s');

/** Send $message to the given audience for one election, once. $tag makes the job unique. */
function run_job(PDO $pdo, array $election, string $tag, string $message, bool $onlyNotVoted): string
{
    $label = $tag . ' #' . $election['id'];

    // Already sent (or being sent by another run)? Stop.
    $chk = $pdo->prepare('SELECT id FROM sms_logs WHERE audience = ? LIMIT 1');
    $chk->execute([$label]);
    if ($chk->fetch()) return "$label: already sent, skipping";

    $sql = "SELECT phone FROM students WHERE status = 'active' AND phone <> ''";
    $params = [];
    if ($onlyNotVoted) {
        $sql .= ' AND id NOT IN (SELECT student_id FROM votes WHERE election_id = ?)';
        $params[] = $election['id'];
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $numbers = [];
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $raw) {
        $n = sms_normalize_phone($raw);
        if ($n) $numbers[$n] = true;
    }
    $numbers = array_keys($numbers);
    if (!$numbers) return "$label: no valid numbers";

    $text = str_replace('{link}', sms_access_link(), $message);

    // Claim the job first so an overlapping run can't send it again
    $pdo->prepare('INSERT INTO sms_logs (admin_id, audience, message, total_recipients, sent_count, failed_count, created_at) VALUES (NULL, ?, ?, ?, 0, 0, NOW())')
        ->execute([$label, $text, count($numbers)]);
    $logId = (int) $pdo->lastInsertId();

    $sent = 0; $failed = 0; $errors = [];
    foreach (array_chunk($numbers, 100) as $chunk) {
        $r = sms_send_batch($chunk, $text);
        if ($r['ok']) { $sent += count($chunk); }
        else { $failed += count($chunk); $errors[] = substr($r['raw'], 0, 300); }
        usleep(300000);
    }
    $pdo->prepare('UPDATE sms_logs SET sent_count = ?, failed_count = ?, error_detail = ? WHERE id = ?')
        ->execute([$sent, $failed, $errors ? implode(' | ', array_unique($errors)) : null, $logId]);
    log_action('system', null, 'AUTO_SMS', "$label: $sent sent, $failed failed");

    return "$label: $sent sent, $failed failed";
}

set_time_limit(300);

// Elections whose voting window is open right now
$stmt = $pdo->prepare("SELECT id, title, start_time, end_time FROM elections WHERE status = 'active' AND start_time <= ? AND end_time > ?");
$stmt->execute([$now, $now]);
$open = $stmt->fetchAll();

if (!$open) exit("[$now] No election is open right now.\n");

foreach ($open as $e) {
    echo run_job($pdo, $e, 'AUTO: voting open', SMS_AUTO_START_MESSAGE, false), "\n";

    if (SMS_REMINDER_HOURS > 0) {
        $remindAt = strtotime($e['end_time']) - (SMS_REMINDER_HOURS * 3600);
        if (time() >= $remindAt) {
            echo run_job($pdo, $e, 'AUTO: reminder', SMS_AUTO_REMINDER_MESSAGE, true), "\n";
        }
    }
}
