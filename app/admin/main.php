<?php
/**
 * Yönetim paneli: kimlik doğrulama, güvenlik başlıkları ve sayfa yönlendirmesi.
 */
declare(strict_types=1);

const LOGIN_MAX_ATTEMPTS = 6;
const LOGIN_WINDOW_MINUTES = 15;
const SESSION_IDLE_SECONDS = 7200;

require APP_DIR . '/admin/ui.php';
require APP_DIR . '/admin/entities.php';

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');
ob_start();

function admin_url(string $page = '', array $params = []): string
{
    $q = $page !== '' ? array_merge(['p' => $page], $params) : $params;
    return base_path() . '/admin/' . ($q ? '?' . http_build_query($q) : '');
}

function admin_user(): ?array
{
    start_session();
    $id = (int) ($_SESSION['admin_id'] ?? 0);
    if ($id < 1) {
        return null;
    }
    if (time() - (int) ($_SESSION['admin_seen'] ?? 0) > SESSION_IDLE_SECONDS) {
        unset($_SESSION['admin_id']);
        return null;
    }
    $user = row('SELECT id, username, name, email FROM users WHERE id = ? AND active = 1', [$id]);
    if ($user) {
        $_SESSION['admin_seen'] = time();
    }
    return $user;
}

/** Durum değiştiren her istek POST + CSRF doğrulamasından geçer. */
function admin_require_post(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !csrf_ok()) {
        if (admin_wants_json()) {
            admin_json(['ok' => false, 'error' => 'Oturum doğrulanamadı. Sayfayı yenileyin.'], 400);
        }
        flash('Oturum doğrulanamadı. Lütfen sayfayı yenileyip yeniden deneyin.', 'err');
        redirect(admin_url());
    }
}

function admin_wants_json(): bool
{
    return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';
}

function admin_json(array $data, int $status = 200): never
{
    if (ob_get_level()) {
        ob_clean();
    }
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function admin_login(): void
{
    $error = '';
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $ip = client_ip();
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        // Hem IP'ye hem kullanıcı adına göre sınırla (IP değiştirerek deneme yapılmasını da keser).
        $recent = (int) val('SELECT COUNT(*) FROM login_attempts WHERE (ip = ? OR username = ?) AND created_at > (NOW() - INTERVAL ' . LOGIN_WINDOW_MINUTES . ' MINUTE)', [$ip, mb_substr($username, 0, 60)]);
        if (!csrf_ok()) {
            $error = 'Oturum süresi doldu. Lütfen yeniden deneyin.';
        } elseif ($recent >= LOGIN_MAX_ATTEMPTS) {
            $error = 'Çok fazla hatalı deneme yapıldı. ' . LOGIN_WINDOW_MINUTES . ' dakika sonra yeniden deneyin.';
        } else {
            $user = row('SELECT * FROM users WHERE username = ? AND active = 1', [$username]);
            // Kullanıcı yoksa da aynı süreyi harca (kullanıcı adı tahminini zorlaştırır).
            $hash = $user['password_hash'] ?? '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG';
            if (password_verify($password, $hash) && $user) {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = (int) $user['id'];
                $_SESSION['admin_seen'] = time();
                $_SESSION['csrf'] = bin2hex(random_bytes(32));
                q('UPDATE users SET last_login = NOW() WHERE id = ?', [$user['id']]);
                q('DELETE FROM login_attempts WHERE ip = ? OR username = ? OR created_at < (NOW() - INTERVAL 1 DAY)', [$ip, $user['username']]);
                if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                    q('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $user['id']]);
                }
                redirect(admin_url());
            }
            q('INSERT INTO login_attempts (ip, username) VALUES (?, ?)', [$ip, mb_substr($username, 0, 60)]);
            $error = 'Kullanıcı adı veya şifre hatalı.';
        }
    }
    require APP_DIR . '/admin/pages/login.php';
}

try {
    $page = preg_replace('/[^a-z_]/', '', (string) ($_GET['p'] ?? 'dashboard'));
    $me = admin_user();

    if ($page === 'logout') {
        admin_require_post();
        $_SESSION = [];
        session_destroy();
        redirect(admin_url('login'));
    }
    if (!$me) {
        if (admin_wants_json()) {
            admin_json(['ok' => false, 'error' => 'Oturum süresi doldu. Yeniden giriş yapın.'], 401);
        }
        admin_login();
        exit;
    }
    if ($page === 'login') {
        redirect(admin_url());
    }

    $routes = [
        'dashboard' => 'dashboard',
        'list' => 'crud_list',
        'edit' => 'crud_edit',
        'save' => 'crud_save',
        'delete' => 'crud_actions',
        'sort' => 'crud_actions',
        'toggle' => 'crud_actions',
        'settings' => 'settings',
        'messages' => 'messages',
        'media' => 'media',
        'upload' => 'media',
        'profile' => 'profile',
    ];
    if (!isset($routes[$page])) {
        http_response_code(404);
        $page = 'dashboard';
    }
    require APP_DIR . '/admin/pages/' . $routes[$page] . '.php';
} catch (Throwable $ex) {
    error_log('[admin] ' . $ex->getMessage() . ' @ ' . $ex->getFile() . ':' . $ex->getLine());
    if (admin_wants_json()) {
        admin_json(['ok' => false, 'error' => 'Beklenmeyen bir hata oluştu.'], 500);
    }
    if (!headers_sent()) {
        http_response_code(500);
    }
    if (ob_get_level()) {
        ob_clean();
    }
    echo '<!doctype html><meta charset="utf-8"><title>Hata</title><div style="font:16px system-ui;max-width:560px;margin:16vh auto;text-align:center;color:#33414f"><h1 style="font-size:22px">İşlem tamamlanamadı</h1><p>Beklenmeyen bir hata oluştu. Hata kaydı tutuldu.</p><p><a href="' . e(admin_url()) . '">Panele dön</a></p></div>';
}
