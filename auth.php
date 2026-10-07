<?php
/* =========================================================
   ADMIN AUTH
   ========================================================= */

function admin_attempt_login(string $email, string $password): ?array
{
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT * FROM admins WHERE email = ? AND is_active = 1 LIMIT 1');
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        // Rehash transparently if the cost factor was upgraded
        if (password_needs_rehash($admin['password_hash'], PASSWORD_BCRYPT)) {
            $newHash = password_hash($password, PASSWORD_BCRYPT);
            $pdo->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')
                ->execute([$newHash, $admin['id']]);
        }
        $pdo->prepare('UPDATE admins SET last_login_at = NOW() WHERE id = ?')->execute([$admin['id']]);
        return $admin;
    }
    return null;
}

function admin_login(array $admin): void
{
    session_regenerate_id(true); // prevent session fixation
    $_SESSION['admin_id']   = $admin['id'];
    $_SESSION['admin_name'] = $admin['full_name'];
    $_SESSION['admin_role'] = $admin['role'];
}

function admin_logged_in(): bool
{
    return isset($_SESSION['admin_id']);
}

function require_admin_login(): void
{
    if (!admin_logged_in()) {
        redirect(BASE_URL . 'admin/login.php');
    }
}

function require_super_admin(): void
{
    require_admin_login();
    if (($_SESSION['admin_role'] ?? '') !== 'super_admin') {
        http_response_code(403);
        die('You do not have permission to view this page.');
    }
}

function current_admin_id(): ?int
{
    return $_SESSION['admin_id'] ?? null;
}

/* =========================================================
   STUDENT / VOTER AUTH
   ========================================================= */

function student_attempt_login(string $email, string $password): ?array
{
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$email]);
    $student = $stmt->fetch();

    if ($student && password_verify($password, $student['password_hash'])) {
        if (password_needs_rehash($student['password_hash'], PASSWORD_BCRYPT)) {
            $newHash = password_hash($password, PASSWORD_BCRYPT);
            $pdo->prepare('UPDATE students SET password_hash = ? WHERE id = ?')
                ->execute([$newHash, $student['id']]);
        }
        return $student;
    }
    return null;
}

function student_login(array $student): void
{
    session_regenerate_id(true);
    $_SESSION['student_id']   = $student['id'];
    $_SESSION['student_name'] = $student['full_name'];
}

function student_logged_in(): bool
{
    return isset($_SESSION['student_id']);
}

function require_student_login(): void
{
    if (!student_logged_in()) {
        redirect(BASE_URL . 'student/login.php');
    }
}

function current_student_id(): ?int
{
    return $_SESSION['student_id'] ?? null;
}

function logout_all(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
