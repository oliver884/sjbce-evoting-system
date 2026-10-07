<?php
/**
 * Login rate limiting.
 *
 * Two layers:
 *  - Per account (identifier + user_type): blocks repeated guessing against
 *    one specific email.
 *  - Per IP (looser threshold): catches someone cycling through many
 *    different emails from the same source, e.g. a credential-stuffing script.
 */

const RATE_LIMIT_MAX_ATTEMPTS_PER_ACCOUNT = 5;
const RATE_LIMIT_MAX_ATTEMPTS_PER_IP      = 20;
const RATE_LIMIT_WINDOW_MINUTES           = 15;

/**
 * Returns a lockout message if the login should be blocked, or null if it's allowed.
 */
function login_rate_limit_check(string $identifier, string $userType): ?string
{
    $pdo = get_db();
    $ip = client_ip();

    $stmt = $pdo->prepare(
        "SELECT COUNT(*) c FROM login_attempts
         WHERE identifier = ? AND user_type = ? AND attempted_at > (NOW() - INTERVAL ? MINUTE)"
    );
    $stmt->execute([$identifier, $userType, RATE_LIMIT_WINDOW_MINUTES]);
    $accountAttempts = (int) $stmt->fetch()['c'];

    if ($accountAttempts >= RATE_LIMIT_MAX_ATTEMPTS_PER_ACCOUNT) {
        return "Too many failed login attempts for this account. Please wait " . RATE_LIMIT_WINDOW_MINUTES . " minutes and try again.";
    }

    $stmt = $pdo->prepare(
        "SELECT COUNT(*) c FROM login_attempts
         WHERE ip_address = ? AND attempted_at > (NOW() - INTERVAL ? MINUTE)"
    );
    $stmt->execute([$ip, RATE_LIMIT_WINDOW_MINUTES]);
    $ipAttempts = (int) $stmt->fetch()['c'];

    if ($ipAttempts >= RATE_LIMIT_MAX_ATTEMPTS_PER_IP) {
        return "Too many failed login attempts from this network. Please wait " . RATE_LIMIT_WINDOW_MINUTES . " minutes and try again.";
    }

    return null;
}

/**
 * Call this after a FAILED login attempt only — successful logins don't count.
 */
function login_rate_limit_record(string $identifier, string $userType): void
{
    $pdo = get_db();
    $pdo->prepare(
        'INSERT INTO login_attempts (identifier, ip_address, user_type, attempted_at) VALUES (?, ?, ?, NOW())'
    )->execute([$identifier, client_ip(), $userType]);
}
