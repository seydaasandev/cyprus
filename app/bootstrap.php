<?php
/**
 * Cyprus Touristic — çekirdek: yapılandırma, veritabanı, oturum ve yardımcı fonksiyonlar.
 */
declare(strict_types=1);

define('APP_DIR', __DIR__);
define('ROOT_DIR', dirname(__DIR__));
define('UPLOAD_DIR', ROOT_DIR . '/uploads');

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', APP_DIR . '/error.log');
mb_internal_encoding('UTF-8');
date_default_timezone_set('Europe/Istanbul');

/* ───────────── Temel adres (site hangi klasörde olursa olsun çalışır) ───────────── */
function base_path(): string
{
    static $base = null;
    if ($base === null) {
        $doc = rtrim(str_replace('\\', '/', (string) realpath($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
        $root = str_replace('\\', '/', ROOT_DIR);
        $base = ($doc !== '' && str_starts_with($root, $doc)) ? substr($root, strlen($doc)) : '';
        $base = rtrim($base, '/');
    }
    return $base;
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

function site_origin(): string
{
    $fixed = rtrim(setting('site_url'), '/');
    if (preg_match('#^https?://[a-z0-9.\-]+(:\d+)?$#i', $fixed)) {
        return $fixed;
    }
    $host = preg_replace('/[^a-zA-Z0-9.\-:]/', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    return (is_https() ? 'https://' : 'http://') . $host;
}

/** Site içi adres üretir: url('oteller') → /demo/oteller */
function url(string $path = ''): string
{
    if ($path === '' || $path === '/') {
        return base_path() . '/';
    }
    if (preg_match('#^(https?:)?//|^(mailto|tel|whatsapp):|^\##i', $path)) {
        return $path;
    }
    return base_path() . '/' . ltrim($path, '/');
}

function abs_url(string $path = ''): string
{
    $u = url($path);
    return preg_match('#^https?://#i', $u) ? $u : site_origin() . $u;
}

function asset(string $path): string
{
    $file = ROOT_DIR . '/assets/' . ltrim($path, '/');
    return base_path() . '/assets/' . ltrim($path, '/') . (is_file($file) ? '?v=' . filemtime($file) : '');
}

/** Yüklenen dosyanın adresi ("uploads/..." göreli yolu veya tam adres). */
function media(?string $path): string
{
    $path = trim((string) $path);
    if ($path === '') {
        return '';
    }
    if (preg_match('#^(https?:)?//#i', $path)) {
        return $path;
    }
    return base_path() . '/' . ltrim($path, '/');
}

/* ───────────── Veritabanı ───────────── */
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $c = require APP_DIR . '/config.php';
        $pdo = new PDO(
            'mysql:host=' . $c['db_host'] . ';dbname=' . $c['db_name'] . ';charset=utf8mb4',
            $c['db_user'],
            $c['db_pass'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }
    return $pdo;
}

function q(string $sql, array $params = []): PDOStatement
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}

function rows(string $sql, array $params = []): array
{
    return q($sql, $params)->fetchAll();
}

function row(string $sql, array $params = []): ?array
{
    $r = q($sql, $params)->fetch();
    return $r === false ? null : $r;
}

function val(string $sql, array $params = [])
{
    $v = q($sql, $params)->fetchColumn();
    return $v === false ? null : $v;
}

/* ───────────── Ayarlar ───────────── */
function settings_all(bool $reload = false): array
{
    static $cache = null;
    if ($cache === null || $reload) {
        $cache = [];
        foreach (rows('SELECT k, v FROM settings') as $r) {
            $cache[$r['k']] = (string) $r['v'];
        }
    }
    return $cache;
}

function setting(string $key, string $default = ''): string
{
    $all = settings_all();
    return (isset($all[$key]) && $all[$key] !== '') ? $all[$key] : $default;
}

function set_setting(string $key, string $value): void
{
    q('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)', [$key, $value]);
}

/* ───────────── Veritabanı sürüm yükseltmeleri ───────────── */
const SCHEMA_VERSION = 2;

/**
 * Kod güncellendiğinde gereken tablo değişikliklerini bir kez uygular.
 * (Site git ile güncellendiği için elle SQL çalıştırmak gerekmez.)
 */
function run_migrations(): void
{
    if ((int) setting('schema_version', '1') >= SCHEMA_VERSION) {
        return;
    }
    // v2: otel oda seçenekleri/fiyatları ve rezervasyon formu alanları
    q("CREATE TABLE IF NOT EXISTS hotel_rooms (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, hotel_id INT UNSIGNED NOT NULL, name VARCHAR(190) NOT NULL, details VARCHAR(400) NOT NULL DEFAULT '', price VARCHAR(80) NOT NULL DEFAULT '', note VARCHAR(190) NOT NULL DEFAULT '', sort INT NOT NULL DEFAULT 0, KEY hotel_id (hotel_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $existing = array_column(rows('SHOW COLUMNS FROM messages'), 'Field');
    $columns = [
        'room' => "VARCHAR(255) NOT NULL DEFAULT ''",
        'checkin' => 'DATE NULL',
        'checkout' => 'DATE NULL',
        'adults' => 'TINYINT UNSIGNED NOT NULL DEFAULT 0',
        'children' => 'TINYINT UNSIGNED NOT NULL DEFAULT 0',
    ];
    foreach ($columns as $name => $definition) {
        if (!in_array($name, $existing, true)) {
            q('ALTER TABLE messages ADD COLUMN ' . $name . ' ' . $definition);
        }
    }
    set_setting('schema_version', (string) SCHEMA_VERSION);
    settings_all(true);
}

/* ───────────── Çıktı yardımcıları ───────────── */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Başlıklarda yalnızca vurgu etiketlerine izin verir (<u>, <em>, <strong>, <br>). */
function title_html(?string $s): string
{
    $s = strip_tags((string) $s, '<u><em><strong><b><i><br>');
    return preg_replace('#<(/?)(u|em|strong|b|i|br)\b[^>]*>#i', '<$1$2>', $s);
}

/** Zengin içerikteki göreli "uploads/..." yollarını geçerli klasöre göre çözer. */
function content_html(?string $html): string
{
    $html = (string) $html;
    return preg_replace('#(src|href|poster)=(["\'])uploads/#i', '$1=$2' . base_path() . '/uploads/', $html);
}

/** Editörden gelen içerikte tam yolları yeniden göreli hale getirir ve tehlikeli kodu ayıklar. */
function clean_html(?string $html): string
{
    $html = (string) $html;
    $html = preg_replace('#<(script|style|iframe|object|embed|form)\b[^>]*>.*?</\1>#is', '', $html);
    $html = preg_replace('#<(script|style|iframe|object|embed|form|link|meta|base)\b[^>]*/?>#i', '', $html);
    $html = preg_replace('#\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $html);
    $html = preg_replace('#(href|src)\s*=\s*(["\'])\s*(javascript|data|vbscript):[^"\']*\2#i', '$1=$2#$2', $html);
    $base = preg_quote(base_path(), '#');
    $origin = preg_quote(site_origin(), '#');
    $html = preg_replace('#(src|href|poster)=(["\'])(?:' . $origin . ')?' . $base . '/uploads/#i', '$1=$2uploads/', $html);
    return trim($html);
}

function excerpt(?string $html, int $len = 160): string
{
    $t = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $html), ENT_QUOTES, 'UTF-8')));
    return mb_strlen($t) > $len ? rtrim(mb_substr($t, 0, $len - 1)) . '…' : $t;
}

function slugify(string $s): string
{
    $map = ['ç' => 'c', 'Ç' => 'c', 'ğ' => 'g', 'Ğ' => 'g', 'ı' => 'i', 'İ' => 'i', 'I' => 'i', 'ö' => 'o', 'Ö' => 'o', 'ş' => 's', 'Ş' => 's', 'ü' => 'u', 'Ü' => 'u', '&' => ' ve '];
    $s = mb_strtolower(strtr($s, $map));
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim((string) $s, '-');
}

function lines(?string $text): array
{
    return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $text)), 'strlen'));
}

/* ───────────── Görsel küçültme (WebP önbellek) ───────────── */
function thumb(?string $path, int $width = 800): string
{
    $path = trim((string) $path);
    if ($path === '' || preg_match('#^(https?:)?//#i', $path)) {
        return $path;
    }
    $src = ROOT_DIR . '/' . ltrim($path, '/');
    if (!is_file($src) || !function_exists('imagewebp')) {
        return media($path);
    }
    $ext = strtolower(pathinfo($src, PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
        return media($path);
    }
    $rel = 'uploads/cache/' . $width . '/' . substr(md5($path . '|' . filemtime($src)), 0, 16) . '.webp';
    $dst = ROOT_DIR . '/' . $rel;
    if (is_file($dst)) {
        return media($rel);
    }
    $info = @getimagesize($src);
    if (!$info || $info[0] <= $width || $info[0] * $info[1] > 40000000) {
        return media($path);
    }
    $img = match ($info[2]) {
        IMAGETYPE_JPEG => @imagecreatefromjpeg($src),
        IMAGETYPE_PNG => @imagecreatefrompng($src),
        IMAGETYPE_WEBP => @imagecreatefromwebp($src),
        default => false,
    };
    if (!$img) {
        return media($path);
    }
    $h = (int) round($info[1] * $width / $info[0]);
    $out = imagecreatetruecolor($width, $h);
    imagealphablending($out, false);
    imagesavealpha($out, true);
    imagecopyresampled($out, $img, 0, 0, 0, 0, $width, $h, $info[0], $info[1]);
    if (!is_dir(dirname($dst))) {
        @mkdir(dirname($dst), 0755, true);
    }
    $ok = @imagewebp($out, $dst, 82);
    imagedestroy($img);
    imagedestroy($out);
    return $ok ? media($rel) : media($path);
}

/* ───────────── Oturum, CSRF, bildirim ───────────── */
function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_name('ctsess');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => base_path() . '/',
        'secure' => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function csrf_token(): string
{
    start_session();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_ok(): bool
{
    start_session();
    $sent = (string) ($_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
    return $sent !== '' && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $sent);
}

function flash(?string $message = null, string $type = 'ok'): ?array
{
    start_session();
    if ($message !== null) {
        $_SESSION['flash'] = ['m' => $message, 't' => $type];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function redirect(string $to): never
{
    header('Location: ' . $to, true, 303);
    exit;
}

/** Cloudflare'in yayımladığı çıkış ağları (https://www.cloudflare.com/ips/). */
const CLOUDFLARE_RANGES = [
    '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22', '141.101.64.0/18', '108.162.192.0/18',
    '190.93.240.0/20', '188.114.96.0/20', '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
    '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
    '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32', '2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32',
];

function ip_in_cidr(string $ip, string $cidr): bool
{
    [$net, $bits] = explode('/', $cidr);
    $a = @inet_pton($ip);
    $b = @inet_pton($net);
    if ($a === false || $b === false || strlen($a) !== strlen($b)) {
        return false;
    }
    $bytes = intdiv((int) $bits, 8);
    $rest = (int) $bits % 8;
    if (strncmp($a, $b, $bytes) !== 0) {
        return false;
    }
    return $rest === 0 || (ord($a[$bytes]) >> (8 - $rest)) === (ord($b[$bytes]) >> (8 - $rest));
}

/**
 * Ziyaretçinin gerçek IP adresi. CF-Connecting-IP başlığına yalnızca istek gerçekten
 * Cloudflare ağından geldiyse güvenilir; aksi halde başlık sahte olabilir.
 */
function client_ip(): string
{
    $remote = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    $forwarded = (string) ($_SERVER['HTTP_CF_CONNECTING_IP'] ?? '');
    if ($forwarded !== '' && filter_var($forwarded, FILTER_VALIDATE_IP) && filter_var($remote, FILTER_VALIDATE_IP)) {
        foreach (CLOUDFLARE_RANGES as $range) {
            if (ip_in_cidr($remote, $range)) {
                return $forwarded;
            }
        }
    }
    return filter_var($remote, FILTER_VALIDATE_IP) ? $remote : '0.0.0.0';
}

/* ───────────── Dosya yükleme ───────────── */
const UPLOAD_TYPES = [
    'image' => ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif', 'image/svg+xml' => 'svg', 'image/x-icon' => 'ico', 'image/vnd.microsoft.icon' => 'ico'],
    'video' => ['video/mp4' => 'mp4', 'video/webm' => 'webm'],
    'file' => ['application/pdf' => 'pdf'],
];

/**
 * Yüklenen dosyayı doğrular ve uploads/YYYY/MM altına kaydeder.
 * @return string Göreli yol (uploads/...)
 * @throws RuntimeException
 */
function save_upload(array $file, array $kinds = ['image']): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $msg = match ($file['error'] ?? 0) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Dosya çok büyük (sunucu sınırı: ' . ini_get('upload_max_filesize') . ').',
            UPLOAD_ERR_NO_FILE => 'Dosya seçilmedi.',
            default => 'Dosya yüklenemedi.',
        };
        throw new RuntimeException($msg);
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Geçersiz yükleme.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) ?: '';
    $ext = null;
    foreach ($kinds as $kind) {
        if (isset(UPLOAD_TYPES[$kind][$mime])) {
            $ext = UPLOAD_TYPES[$kind][$mime];
            break;
        }
    }
    if ($ext === null) {
        throw new RuntimeException('Bu dosya türüne izin verilmiyor (' . $mime . ').');
    }
    if ($ext === 'svg' && preg_match('/<script|on[a-z]+\s*=|javascript:|<foreignObject/i', (string) file_get_contents($file['tmp_name']))) {
        throw new RuntimeException('SVG dosyası güvenli değil.');
    }
    $name = substr(slugify(pathinfo((string) $file['name'], PATHINFO_FILENAME)) ?: 'dosya', 0, 80);
    $dirRel = 'uploads/' . date('Y') . '/' . date('m');
    $dirAbs = ROOT_DIR . '/' . $dirRel;
    if (!is_dir($dirAbs) && !mkdir($dirAbs, 0755, true) && !is_dir($dirAbs)) {
        throw new RuntimeException('Yükleme klasörü oluşturulamadı.');
    }
    $target = $name . '.' . $ext;
    for ($i = 2; file_exists($dirAbs . '/' . $target); $i++) {
        $target = $name . '-' . $i . '.' . $ext;
    }
    if (!move_uploaded_file($file['tmp_name'], $dirAbs . '/' . $target)) {
        throw new RuntimeException('Dosya kaydedilemedi.');
    }
    @chmod($dirAbs . '/' . $target, 0644);
    return $dirRel . '/' . $target;
}

/* ───────────── Menü ───────────── */
function menu_tree(string $menu, bool $onlyActive = true): array
{
    $all = rows('SELECT * FROM menu_items WHERE menu = ?' . ($onlyActive ? ' AND active = 1' : '') . ' ORDER BY sort, id', [$menu]);
    $tree = [];
    $children = [];
    foreach ($all as $m) {
        if ($m['parent_id']) {
            $children[(int) $m['parent_id']][] = $m;
        }
    }
    foreach ($all as $m) {
        if (!$m['parent_id']) {
            $m['children'] = $children[(int) $m['id']] ?? [];
            $tree[] = $m;
        }
    }
    return $tree;
}

/** Geçerli sayfanın site içi yolu (ör. "oteller/salamis-hotel"). */
function current_path(): string
{
    $uri = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $base = base_path();
    if ($base !== '' && str_starts_with($uri, $base)) {
        $uri = substr($uri, strlen($base));
    }
    return trim(rawurldecode($uri), '/');
}

function is_current(string $link): bool
{
    $link = trim((string) parse_url($link, PHP_URL_PATH), '/');
    $cur = current_path();
    return $link === $cur || ($link !== '' && str_starts_with($cur, $link . '/'));
}

require APP_DIR . '/icons.php';
require APP_DIR . '/theme.php';
