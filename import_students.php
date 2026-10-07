<?php
require_once __DIR__ . '/../config/config.php';
require_admin_login();
$pdo = get_db();

$results = null;

if (is_post() && ($_POST['action'] ?? '') === 'import') {
    verify_csrf();

    if (empty($_FILES['roster']['tmp_name']) || $_FILES['roster']['error'] !== UPLOAD_ERR_OK) {
        flash_set('error', 'Please choose a CSV file to upload.');
        redirect(BASE_URL . 'admin/import_students.php');
    }

    $handle = fopen($_FILES['roster']['tmp_name'], 'r');
    if (!$handle) {
        flash_set('error', 'Could not read that file.');
        redirect(BASE_URL . 'admin/import_students.php');
    }

    $added = 0;
    $skipped = 0;
    $errors = [];
    $rowNum = 0;

    // Expected columns: full_name, email, phone, department, level
    $header = fgetcsv($handle);
    $rowNum++;

    while (($row = fgetcsv($handle)) !== false) {
        $rowNum++;
        if (count(array_filter($row, fn($v) => trim((string)$v) !== '')) === 0) continue; // skip blank rows

        [$fullName, $email, $phone, $department, $level] = array_pad(array_map('trim', $row), 5, '');

        if ($fullName === '' || $email === '' || $phone === '' || $department === '' || $level === '') {
            $errors[] = "Row $rowNum: missing a required field, skipped.";
            $skipped++;
            continue;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Row $rowNum: invalid email '$email', skipped.";
            $skipped++;
            continue;
        }

        $stmt = $pdo->prepare('SELECT id FROM students WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Row $rowNum: '$email' already exists, skipped.";
            $skipped++;
            continue;
        }

        try {
            $pdo->beginTransaction();

            // No usable password yet — students set their own via the OTP flow.
            $placeholderHash = password_hash(bin2hex(random_bytes(32)), PASSWORD_BCRYPT);

            $stmt = $pdo->prepare(
                'INSERT INTO students (student_id, full_name, email, phone, department, level, password_hash, is_verified, status, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 1, \'active\', NOW())'
            );
            $stmt->execute(['PENDING-' . bin2hex(random_bytes(8)), $fullName, $email, $phone, $department, $level, $placeholderHash]);
            $newId = (int) $pdo->lastInsertId();

            $studentId = sprintf('SJBCE/%d/%05d', date('Y'), $newId);
            $pdo->prepare('UPDATE students SET student_id = ? WHERE id = ?')->execute([$studentId, $newId]);

            $pdo->commit();
            $added++;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Row $rowNum: could not save ($email).";
            $skipped++;
        }
    }
    fclose($handle);

    log_action('admin', current_admin_id(), 'IMPORT_STUDENTS', "$added added, $skipped skipped");
    $results = ['added' => $added, 'skipped' => $skipped, 'errors' => $errors];
}

$activeNav = 'import';
$pageTitle = 'Import Students';
require __DIR__ . '/../includes/header_admin.php';
?>

<h1>Import Student Roster</h1>
<p class="muted" style="max-width:640px;">
    Upload the official student list as a CSV file. Only students on this list will ever be able
    to access the system — there is no public self-registration. Students set their own password
    later using a one-time code emailed to them (see the OTP login flow).
</p>

<div class="card mb-lg" style="max-width:560px;">
    <h3>CSV Format</h3>
    <p class="muted" style="font-size:.85rem;">
        First row is a header (ignored). Columns in this exact order:<br>
        <code>full_name, email, phone, department, level</code>
    </p>
    <pre style="background:var(--wine-tint);padding:.75rem;border-radius:var(--radius);font-size:.8rem;overflow-x:auto;">full_name,email,phone,department,level
Ama Boateng,ama.boateng@example.com,0244111111,Computer Science,Level 200
Kwame Mensah,kwame.mensah@example.com,0244222222,Mathematics,Level 100</pre>

    <form method="post" enctype="multipart/form-data" style="margin-top:1rem;">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="import">
        <div class="form-group">
            <label for="roster">CSV File</label>
            <input type="file" id="roster" name="roster" accept=".csv,text/csv" required>
        </div>
        <button type="submit" class="btn btn-primary">Import Students</button>
    </form>
</div>

<?php if ($results): ?>
    <div class="card" style="max-width:640px;">
        <h3>Import Results</h3>
        <p><strong style="color:var(--success);"><?= $results['added'] ?> added</strong> &middot;
           <strong style="color:var(--danger);"><?= $results['skipped'] ?> skipped</strong></p>
        <?php if ($results['errors']): ?>
            <div class="alert alert-error" style="max-height:200px;overflow-y:auto;">
                <?php foreach ($results['errors'] as $e): ?>
                    <div><?= h($e) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer_admin.php'; ?>
