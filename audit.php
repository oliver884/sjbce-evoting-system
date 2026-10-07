<?php
/**
 * Record an entry in audit_logs.
 * $userType: 'admin' | 'student' | 'system'
 */
function log_action(string $userType, ?int $userId, string $action, string $details = ''): void
{
    try {
        $pdo = get_db();
        $stmt = $pdo->prepare(
            'INSERT INTO audit_logs (user_type, user_id, action, details, ip_address, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([$userType, $userId, $action, $details, client_ip()]);
    } catch (Throwable $e) {
        error_log('Audit log failed: ' . $e->getMessage());
    }
}
