<?php
require_once __DIR__ . '/../config/sms.php';

/** True once a real API key has been entered in config/sms.php */
function sms_configured(): bool
{
    return SMS_API_KEY !== '' && SMS_API_KEY !== 'PUT_YOUR_API_KEY_HERE';
}

/** Link students receive: the "Get Access Code" page. */
function sms_access_link(): string
{
    if (SMS_SITE_URL !== '') {
        $base = rtrim(SMS_SITE_URL, '/') . '/';
    } else {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $base = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE_URL;
    }
    return $base . 'student/request_otp.php';
}

/** Normalize a Ghana number to 233XXXXXXXXX. Returns null if it isn't valid. */
function sms_normalize_phone(?string $raw): ?string
{
    $n = preg_replace('/\D+/', '', (string) $raw);
    if (strlen($n) === 10 && $n[0] === '0')                 return '233' . substr($n, 1);
    if (strlen($n) === 9)                                    return '233' . $n;
    if (strlen($n) === 12 && substr($n, 0, 3) === '233')     return $n;
    return null;
}

/** Character count that works even if the mbstring extension is off. */
function sms_strlen(string $s): int
{
    return function_exists('mb_strlen') ? mb_strlen($s) : strlen($s);
}

/** Number of SMS credits one message uses per recipient (160 chars, or 153 per part if longer). */
function sms_parts(string $message): int
{
    $len = sms_strlen($message);
    if ($len <= 160) return 1;
    return (int) ceil($len / 153);
}

/**
 * Send one batch (max ~100 numbers) through the gateway.
 * Returns ['ok' => bool, 'raw' => string].
 */
function sms_send_batch(array $numbers, string $message): array
{
    if (!function_exists('curl_init')) {
        return ['ok' => false, 'raw' => 'PHP cURL extension is not enabled on this server.'];
    }
    $ch = curl_init(SMS_API_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => ['api-key: ' . SMS_API_KEY, 'Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => json_encode([
            'sender'     => SMS_SENDER_ID,
            'message'    => $message,
            'recipients' => array_values($numbers),
        ]),
    ]);
    $raw = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($raw === false) {
        return ['ok' => false, 'raw' => 'Connection error: ' . $err];
    }
    $json = json_decode($raw, true);
    $ok = is_array($json) && (($json['status'] ?? '') === 'success');
    return ['ok' => $ok, 'raw' => (string) $raw];
}
