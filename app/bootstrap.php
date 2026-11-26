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
const SCHEMA_VERSION = 7;
const MIGRATION_LOCK = 'cyprus_schema_migration';
const MIGRATION_LOCK_WAIT_SECONDS = 15;

/**
 * Kod güncellendiğinde gereken tablo ve içerik değişikliklerini bir kez uygular.
 * (Site git ile güncellendiği için elle SQL çalıştırmak gerekmez.)
 */
function run_migrations(): void
{
    if ((int) setting('schema_version', '1') >= SCHEMA_VERSION) {
        return;
    }
    // Aynı anda gelen iki istek adımları iki kez uygulamasın.
    if ((int) val('SELECT GET_LOCK(?, ?)', [MIGRATION_LOCK, MIGRATION_LOCK_WAIT_SECONDS]) !== 1) {
        return;
    }
    try {
        settings_all(true);
        $version = (int) setting('schema_version', '1');
        if ($version < 2) {
            migrate_rooms_schema();
        }
        if ($version < 3) {
            migrate_hotel_setup(require APP_DIR . '/data/hotel_setup_v3.php');
        }
        if ($version < 4) {
            migrate_content_v4();
        }
        if ($version < 5) {
            migrate_gallery_captions(require APP_DIR . '/data/gallery_captions_v5.php');
        }
        if ($version < 6) {
            migrate_data_v6();
        }
        if ($version < 7) {
            migrate_data_v7();
        }
        set_setting('schema_version', (string) SCHEMA_VERSION);
        settings_all(true);
    } finally {
        q('DO RELEASE_LOCK(?)', [MIGRATION_LOCK]);
    }
}

/** v2: otel oda seçenekleri/fiyatları tablosu ve rezervasyon formu alanları. */
function migrate_rooms_schema(): void
{
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
}

/**
 * v3: otel sıralaması, oda tipleri ve oda fotoğrafları galerisi.
 * Panelden girilmiş veriyi ezmez: odası olan otele oda eklemez, galeride zaten bulunan görseli yinelemez.
 */
function migrate_hotel_setup(array $setup): void
{
    $ids = array_column(rows('SELECT id, slug FROM hotels'), 'id', 'slug');

    $position = 0;
    foreach ($setup['order'] as $slug) {
        if (isset($ids[$slug])) {
            q('UPDATE hotels SET sort = ? WHERE id = ?', [++$position, $ids[$slug]]);
        }
    }
    $unlisted = array_diff_key($ids, array_flip($setup['order']));
    if ($unlisted) {
        $marks = implode(',', array_fill(0, count($unlisted), '?'));
        foreach (rows("SELECT id FROM hotels WHERE id IN ($marks) ORDER BY sort, id", array_values($unlisted)) as $hotel) {
            q('UPDATE hotels SET sort = ? WHERE id = ?', [++$position, $hotel['id']]);
        }
    }

    foreach ($setup['rooms'] as $slug => $rooms) {
        if (!isset($ids[$slug]) || (int) val('SELECT COUNT(*) FROM hotel_rooms WHERE hotel_id = ?', [$ids[$slug]]) > 0) {
            continue;
        }
        foreach ($rooms as $i => [$name, $details]) {
            q('INSERT INTO hotel_rooms (hotel_id, name, details, sort) VALUES (?, ?, ?, ?)', [$ids[$slug], $name, $details, $i + 1]);
        }
    }

    foreach ($ids as $slug => $hotelId) {
        $files = glob(ROOT_DIR . '/uploads/oteller/galeri/' . $slug . '/*.{jpg,jpeg,png,webp}', GLOB_BRACE) ?: [];
        sort($files);
        $have = array_column(rows('SELECT image FROM hotel_images WHERE hotel_id = ?', [$hotelId]), 'image');
        $next = (int) val('SELECT COALESCE(MAX(sort), 0) FROM hotel_images WHERE hotel_id = ?', [$hotelId]);
        foreach ($files as $file) {
            $path = 'uploads/oteller/galeri/' . $slug . '/' . basename($file);
            if (!in_array($path, $have, true)) {
                q('INSERT INTO hotel_images (hotel_id, image, sort) VALUES (?, ?, ?)', [$hotelId, $path, ++$next]);
            }
        }
    }
}

/**
 * v4: rezervasyon yalnızca kurumsal girişten yapıldığı için "Rezervasyon Yap" başlıkları "Oteller" olur,
 * ofis adresi güncellenir, Türkiye adresi ve "Türkiye'yi tanıtmak" ifadesi kaldırılır.
 * Yalnızca eski değeri taşıyan alanlar değişir; panelden yeniden yazılmış metinlere dokunulmaz.
 */
function migrate_content_v4(): void
{
    $oldLabel = 'Rezervasyon Yap';
    $newLabel = 'Oteller';
    q("UPDATE menu_items SET title = ? WHERE title = ? AND url = 'oteller'", [$newLabel, $oldLabel]);
    q("UPDATE sections SET title = ? WHERE title = ? AND skey = 'hotels'", [$newLabel, $oldLabel]);
    q("UPDATE slides SET btn1_text = ? WHERE btn1_text = ? AND btn1_url = 'oteller'", [$newLabel, $oldLabel]);
    q("UPDATE slides SET btn2_text = ? WHERE btn2_text = ? AND btn2_url = 'oteller'", [$newLabel, $oldLabel]);
    if (setting('hotels_title') === $oldLabel) {
        set_setting('hotels_title', $newLabel);
    }
    if (setting('label_rooms') === 'Oda Seçenekleri ve Fiyatlar') {
        set_setting('label_rooms', 'Oda Seçenekleri');
    }

    set_setting('address', "Karaoğlanoğlu Cd. Sel Trade Center\nDış Kapı No: F Blok 166\nGirne, KKTC");
    set_setting('address2', '');

    // Hakkımızda metinlerinin ilk cümlesi tümüyle kalkar; kısa tanıtım satırlarında yalnızca "ve Türkiye'yi" çıkarılır.
    $sentence = 'CYPRUS TOURISTIC, dünya arenasında Kuzey Kıbrıs’ı ve Türkiye’yi tanıtmak için faaliyet göstermektedir. ';
    q("UPDATE pages SET content = REPLACE(content, ?, '') WHERE slug = 'hakkimizda'", [$sentence]);
    q("UPDATE sections SET body = REPLACE(body, ?, '') WHERE skey = 'intro'", [$sentence]);
    foreach (['topbar_text', 'footer_about', 'meta_desc'] as $key) {
        $text = setting($key);
        $clean = str_replace(' ve Türkiye’yi tanıtmak', ' tanıtmak', $text);
        if ($clean !== $text) {
            set_setting($key, $clean);
        }
    }
}

/**
 * v5: galeri fotoğraflarına başlık (oda adı) alanı. Hazır gelen oda fotoğraflarının başlıkları doldurulur;
 * panelden yazılmış başlıklar korunur.
 */
function migrate_gallery_captions(array $captions): void
{
    if (!in_array('caption', array_column(rows('SHOW COLUMNS FROM hotel_images'), 'Field'), true)) {
        q("ALTER TABLE hotel_images ADD COLUMN caption VARCHAR(190) NOT NULL DEFAULT ''");
    }
    foreach ($captions as $slug => $files) {
        foreach ($files as $file => $caption) {
            q("UPDATE hotel_images SET caption = ? WHERE image = ? AND caption = ''", [$caption, 'uploads/oteller/galeri/' . $slug . '/' . $file]);
        }
    }
}

/** v6: Otel sıralaması, Arkin İskele oda ismi, Salamis Standard Villa, hero autoplay. */
function migrate_data_v6(): void
{
    // Otel sıralama güncelleme (kullanıcı tarafından belirlenen sıra)
    $order = [
        'concorde-luxury-hotel'          => 10,
        'elexus-hotel'                   => 20,
        'limak-cyprus-deluxe-hotel-single' => 30,
        'arkin-iskele-hotel'             => 40,
        'acapulco-hotel'                 => 50,
        'salamis-hotel'                  => 60,
        'kaya-artemis-hotel'             => 70,
        'concorde-aria-hotel-casino'     => 80,
        'kaya-palazzo-hotel-kyrenia'     => 90,
        'lords-palace-hotel-kyrenia'     => 100,
        'lords-palace-lagoon-hotel'      => 110,
        'cratos-hotel'                   => 120,
        'vuni-palace-hotel'              => 130,
        'merit-park-hotel'               => 140,
        'merit-royal-premium-hotel'      => 150,
        'merit-royal-diamond-hotel'      => 160,
        'grand-sapphire-hotel'           => 170,
    ];
    foreach ($order as $slug => $sort) {
        q('UPDATE hotels SET sort = ? WHERE slug = ?', [$sort, $slug]);
    }

    // Arkin İskele oda tipi isim değişikliği: Deluxe Side Sea → Standard Side Sea
    q("UPDATE hotel_rooms SET name = 'Standard Side Sea' WHERE name = 'Deluxe Side Sea' AND hotel_id = (SELECT id FROM hotels WHERE slug = 'arkin-iskele-hotel')");

    // Salamis'e Standard Villa oda ekleme (yoksa)
    $exists = val(
        "SELECT COUNT(*) FROM hotel_rooms WHERE name = 'Standard Villa' AND hotel_id = (SELECT id FROM hotels WHERE slug = 'salamis-hotel')"
    );
    if ((int) $exists === 0) {
        $hotelId = val("SELECT id FROM hotels WHERE slug = 'salamis-hotel'");
        $maxSort = val('SELECT COALESCE(MAX(sort),0) FROM hotel_rooms WHERE hotel_id = ?', [(int) $hotelId]);
        q(
            "INSERT INTO hotel_rooms (hotel_id, name, details, price, note, sort) VALUES (?, ?, ?, '', '', ?)",
            [
                (int) $hotelId,
                'Standard Villa',
                '36 m² – 2 separate bedrooms: 1 French bed, 1 sofa bed, and 1 French or 2 twin beds. Max capacity: 4+1',
                (int) $maxSort + 1,
            ]
        );
    }

    // Hero slayt geçiş süresi varsayılanını güncelle (zaten 7 ise 12'ye çek)
    q("UPDATE settings SET v = '12' WHERE k = 'hero_autoplay' AND v = '7'");
}

/**
 * v7: Salamis "Standard Villa" fotoğrafları galeriye eklenir; Arkin İskele'deki fotoğraf başlığı
 * yeni oda adına ("Standard Side Sea") çekilir. Var olan kayıtlara dokunmaz, tekrar çalışması zararsızdır.
 */
function migrate_data_v7(): void
{
    $salamisId = (int) val("SELECT id FROM hotels WHERE slug = 'salamis-hotel'");
    if ($salamisId > 0) {
        $dir = 'uploads/oteller/galeri/salamis-hotel/';
        $have = array_column(rows('SELECT image FROM hotel_images WHERE hotel_id = ?', [$salamisId]), 'image');
        $next = (int) val('SELECT COALESCE(MAX(sort), 0) FROM hotel_images WHERE hotel_id = ?', [$salamisId]);
        foreach (['09-salamis-standard-villa.webp', '10-salamis-standard-villa-2.webp', '11-salamis-standard-villa-3.webp'] as $file) {
            $path = $dir . $file;
            if (!in_array($path, $have, true) && is_file(ROOT_DIR . '/' . $path)) {
                q('INSERT INTO hotel_images (hotel_id, image, sort, caption) VALUES (?, ?, ?, ?)', [$salamisId, $path, ++$next, 'Standard Villa']);
            }
        }
    }
    q("UPDATE hotel_images SET caption = 'Standard Side Sea' WHERE caption = 'Deluxe Side Sea' AND hotel_id = (SELECT id FROM hotels WHERE slug = 'arkin-iskele-hotel')");
}

/** Otel sayfalarındaki "Rezervasyon Yap" düğmesi ve rezervasyon formu açık mı? (Panelden açılır.) */
function reservations_enabled(): bool
{
    return setting('reserve_enabled', '0') === '1';
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
