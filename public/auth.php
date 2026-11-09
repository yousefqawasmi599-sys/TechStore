<?php
function login_attempt(PDO $pdo, string $email): string {
    $key = hash('sha256', strtolower($email) . '|' . ($_SERVER['REMOTE_ADDR'] ?? 'local'));
    $stmt = $pdo->prepare('INSERT INTO auth_attempts (attempt_key,attempts,reset_at) VALUES (?,1,UNIX_TIMESTAMP()+900) ON DUPLICATE KEY UPDATE attempts=IF(reset_at < UNIX_TIMESTAMP(),1,attempts+1), reset_at=IF(reset_at < UNIX_TIMESTAMP(),UNIX_TIMESTAMP()+900,reset_at)');
    $stmt->execute([$key]);
    $stmt = $pdo->prepare('SELECT attempts,reset_at FROM auth_attempts WHERE attempt_key=?');
    $stmt->execute([$key]);
    $attempt = $stmt->fetch();
    if ($attempt['attempts'] > 5) {
        header('Retry-After: ' . max(1, $attempt['reset_at'] - time()));
        fail_request('محاولات دخول كثيرة. حاول بعد 15 دقيقة.', 429);
    }
    // Keep the table bounded without changing any account data.
    $pdo->exec('DELETE FROM auth_attempts WHERE reset_at < UNIX_TIMESTAMP() LIMIT 100');
    return $key;
}
