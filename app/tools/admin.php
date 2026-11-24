<?php
/**
 * Komut satırından yönetici hesabı oluşturur ya da şifresini sıfırlar.
 * Kullanım: php app/tools/admin.php <kullanici-adi> <sifre>
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Yalnızca komut satırından çalışır.');
}
require dirname(__DIR__) . '/bootstrap.php';

const ADMIN_PASSWORD_MIN = 10;

[$username, $password] = [trim((string) ($argv[1] ?? '')), (string) ($argv[2] ?? '')];
if (!preg_match('/^[A-Za-z0-9_.\-]{3,60}$/', $username) || mb_strlen($password) < ADMIN_PASSWORD_MIN) {
    fwrite(STDERR, "Kullanım: php app/tools/admin.php <kullanici-adi> <sifre>  (şifre en az " . ADMIN_PASSWORD_MIN . " karakter)\n");
    exit(1);
}
q(
    'INSERT INTO users (username, name, password_hash, active) VALUES (?, ?, ?, 1) ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), active = 1',
    [$username, 'Yönetici', password_hash($password, PASSWORD_DEFAULT)]
);
echo "Yönetici hesabı hazır: $username\n";
