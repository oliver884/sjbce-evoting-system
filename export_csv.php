<?php
require_once __DIR__ . '/../config/config.php';
require_admin_login();
$pdo = get_db();

$electionId = (int) ($_GET['election_id'] ?? 0);
if (!$electionId) { die('No election specified.'); }

$stmt = $pdo->prepare('SELECT title FROM elections WHERE id = ?');
$stmt->execute([$electionId]);
$election = $stmt->fetch();
if (!$election) { die('Election not found.'); }

$stmt = $pdo->prepare(
    'SELECT p.title AS position, c.full_name AS candidate, COUNT(v.id) AS votes
     FROM positions p
     JOIN candidates c ON c.position_id = p.id
     LEFT JOIN votes v ON v.candidate_id = c.id
     WHERE p.election_id = ?
     GROUP BY p.id, c.id
     ORDER BY p.display_order, p.id, votes DESC'
);
$stmt->execute([$electionId]);
$rows = $stmt->fetchAll();

log_action('admin', current_admin_id(), 'EXPORT_RESULTS_CSV', $election['title']);

$filename = 'results_' . preg_replace('/[^a-z0-9]+/i', '_', $election['title']) . '_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
fputcsv($out, ['Position', 'Candidate', 'Votes']);
foreach ($rows as $row) {
    fputcsv($out, [$row['position'], $row['candidate'], $row['votes']]);
}
fclose($out);
exit;
