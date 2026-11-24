<?php
/**
 * İletişim ve talep formları: doğrulama, spam koruması, kayıt ve e-posta bildirimi.
 */
declare(strict_types=1);

const FORM_MAX_PER_HOUR = 5;
const FORM_MIN_SECONDS = 3;

const TURNSTILE_VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

/** Doğrulama türü: math (görsel işlem sorusu), turnstile (Cloudflare) ya da off. */
function captcha_mode(): string
{
    $mode = setting('captcha_mode', 'math');
    if ($mode === 'turnstile' && (setting('turnstile_site') === '' || setting('turnstile_secret') === '')) {
        return 'math';
    }
    return in_array($mode, ['math', 'turnstile', 'off'], true) ? $mode : 'math';
}

/** Yeni bir işlem sorusu üretir, yanıtı oturuma yazar ve soruyu PNG (data URI) olarak döndürür. */
function captcha_image(): string
{
    start_session();
    $a = random_int(2, 9);
    $b = random_int(1, 9);
    $_SESSION['captcha'] = (string) ($a + $b);
    $text = $a . ' + ' . $b . ' = ?';
    if (!function_exists('imagecreatetruecolor')) {
        return '';
    }
    $small = imagecreatetruecolor(72, 18);
    $bg = imagecolorallocate($small, 243, 247, 250);
    imagefill($small, 0, 0, $bg);
    imagestring($small, 5, 4, 1, $text, imagecolorallocate($small, 0, 65, 106));
    $img = imagecreatetruecolor(180, 46);
    imagecopyresampled($img, $small, 0, 0, 0, 0, 180, 46, 72, 18);
    for ($i = 0; $i < 5; $i++) {
        imageline($img, random_int(0, 180), random_int(0, 46), random_int(0, 180), random_int(0, 46), imagecolorallocate($img, random_int(120, 200), random_int(150, 210), random_int(190, 235)));
    }
    for ($i = 0; $i < 120; $i++) {
        imagesetpixel($img, random_int(0, 179), random_int(0, 45), imagecolorallocate($img, random_int(90, 200), random_int(120, 200), random_int(160, 230)));
    }
    ob_start();
    imagepng($img);
    $png = (string) ob_get_clean();
    imagedestroy($small);
    imagedestroy($img);
    return 'data:image/png;base64,' . base64_encode($png);
}

/** Gönderilen doğrulamayı denetler. Her soru tek kullanımlıktır. */
function captcha_ok(): bool
{
    $mode = captcha_mode();
    if ($mode === 'off') {
        return true;
    }
    if ($mode === 'turnstile') {
        $token = (string) ($_POST['cf-turnstile-response'] ?? '');
        if ($token === '' || strlen($token) > 2048) {
            return false;
        }
        $ctx = stream_context_create(['http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query(['secret' => setting('turnstile_secret'), 'response' => $token, 'remoteip' => client_ip()]),
            'timeout' => 8,
        ]]);
        $res = @file_get_contents(TURNSTILE_VERIFY_URL, false, $ctx);
        $data = $res !== false ? json_decode($res, true) : null;
        if (!is_array($data)) {
            error_log('[form] Turnstile doğrulama servisine ulaşılamadı');
            return false;
        }
        return !empty($data['success']);
    }
    start_session();
    $expected = (string) ($_SESSION['captcha'] ?? '');
    unset($_SESSION['captcha']);
    $given = trim((string) ($_POST['captcha'] ?? ''));
    return $expected !== '' && hash_equals($expected, $given);
}

/** Formda gösterilecek doğrulama alanı. */
function captcha_field(array $form): string
{
    $mode = captcha_mode();
    if ($mode === 'off') {
        return '';
    }
    $err = isset($form['errors']['captcha']) ? '<small class="field-error">' . e($form['errors']['captcha']) . '</small>' : '';
    if ($mode === 'turnstile') {
        return '<div class="field"><div class="cf-turnstile" data-sitekey="' . e(setting('turnstile_site')) . '"></div>' . $err . '</div>'
            . '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>';
    }
    $img = captcha_image();
    return '<div class="field captcha"><span>Güvenlik sorusu *</span><div class="captcha__row">'
        . ($img !== '' ? '<img src="' . $img . '" alt="Güvenlik sorusu: görseldeki işlemin sonucunu yazın" width="180" height="46">' : '')
        . '<input type="text" name="captcha" inputmode="numeric" autocomplete="off" maxlength="3" required placeholder="Sonuç" aria-label="Görseldeki işlemin sonucu"></div>' . $err . '</div>';
}

/**
 * @return array{sent: bool, errors: array<string,string>, old: array<string,string>}
 */
function handle_form(string $kind): array
{
    start_session();
    $state = ['sent' => false, 'errors' => [], 'old' => []];
    if (!empty($_SESSION['form_sent'])) {
        unset($_SESSION['form_sent']);
        $state['sent'] = true;
    }
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        $_SESSION['form_time'] = time();
        return $state;
    }

    $in = [];
    foreach (['name', 'surname', 'company', 'country', 'phone', 'email', 'subject', 'hotel', 'message'] as $f) {
        $in[$f] = trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string) ($_POST[$f] ?? '')));
    }
    $state['old'] = $in;
    $errors = [];

    if (!csrf_ok()) {
        $errors['_form'] = 'Oturum süresi doldu. Lütfen formu yeniden gönderin.';
    }
    // Bot tuzağı: gizli alan dolu ya da form çok hızlı gönderildiyse sessizce başarılı say.
    $tooFast = (time() - (int) ($_SESSION['form_time'] ?? 0)) < FORM_MIN_SECONDS;
    if (trim((string) ($_POST['website'] ?? '')) !== '' || $tooFast) {
        if (!$errors) {
            $_SESSION['form_sent'] = true;
            redirect(url(current_path()) . '#form');
        }
    }
    if (mb_strlen($in['name']) < 2 || mb_strlen($in['name']) > 120) {
        $errors['name'] = 'Lütfen adınızı yazın.';
    }
    if (!filter_var($in['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($in['email']) > 190) {
        $errors['email'] = 'Geçerli bir e-posta adresi yazın.';
    }
    if ($in['phone'] !== '' && !preg_match('/^[0-9+()\s.\-]{6,30}$/', $in['phone'])) {
        $errors['phone'] = 'Telefon numarasını kontrol edin.';
    }
    if ($kind === 'iletisim' && mb_strlen($in['message']) < 5) {
        $errors['message'] = 'Lütfen mesajınızı yazın.';
    }
    if (mb_strlen($in['message']) > 4000) {
        $errors['message'] = 'Mesaj en fazla 4000 karakter olabilir.';
    }
    foreach (['surname' => 120, 'company' => 190, 'country' => 120, 'subject' => 250, 'hotel' => 190] as $f => $max) {
        if (mb_strlen($in[$f]) > $max) {
            $errors[$f] = 'Bu alan çok uzun.';
        }
    }
    if (!$errors && !captcha_ok()) {
        $errors['captcha'] = captcha_mode() === 'turnstile' ? 'Güvenlik doğrulaması tamamlanamadı. Lütfen yeniden deneyin.' : 'Güvenlik sorusunun yanıtı hatalı. Lütfen yeniden deneyin.';
    }
    $ip = client_ip();
    if (!$errors && (int) val('SELECT COUNT(*) FROM messages WHERE ip = ? AND created_at > (NOW() - INTERVAL 1 HOUR)', [$ip]) >= FORM_MAX_PER_HOUR) {
        $errors['_form'] = 'Kısa sürede çok fazla form gönderildi. Lütfen daha sonra yeniden deneyin.';
    }
    if ($errors) {
        $state['errors'] = $errors;
        return $state;
    }

    q(
        'INSERT INTO messages (kind, name, surname, company, country, phone, email, subject, hotel, message, ip) VALUES (?,?,?,?,?,?,?,?,?,?,?)',
        [$kind, $in['name'], $in['surname'], $in['company'], $in['country'], $in['phone'], $in['email'], $in['subject'], $in['hotel'], $in['message'], $ip]
    );
    send_form_mail($kind, $in);
    $_SESSION['form_sent'] = true;
    redirect(url(current_path()) . '#form');
}

function send_form_mail(string $kind, array $in): void
{
    if (setting('mail_enabled') !== '1') {
        return;
    }
    $to = setting('mail_to', setting('email'));
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return;
    }
    $site = opt('site_name');
    $host = preg_replace('/^www\./', '', (string) parse_url(site_origin(), PHP_URL_HOST));
    $from = setting('mail_from', 'noreply@' . $host);
    if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
        $from = 'noreply@' . $host;
    }
    $labels = ['name' => 'Ad', 'surname' => 'Soyad', 'company' => 'Firma', 'country' => 'Ülke', 'phone' => 'Telefon', 'email' => 'E-posta', 'subject' => 'Konu', 'hotel' => 'Otel', 'message' => 'Mesaj'];
    $body = ($kind === 'talep' ? 'Yeni talep formu' : 'Yeni iletişim mesajı') . "\n" . str_repeat('-', 40) . "\n";
    foreach ($labels as $k => $label) {
        if ($in[$k] !== '') {
            $body .= $label . ': ' . $in[$k] . "\n";
        }
    }
    $body .= str_repeat('-', 40) . "\n" . abs_url('admin/?p=messages') . "\n";
    $clean = static fn(string $s): string => trim(preg_replace('/[\r\n]+/', ' ', $s));
    $subject = '=?UTF-8?B?' . base64_encode($site . ' – ' . ($kind === 'talep' ? 'Talep Formu' : 'İletişim') . ': ' . $clean($in['name'])) . '?=';
    $headers = [
        'From: =?UTF-8?B?' . base64_encode($site) . '?= <' . $from . '>',
        'Reply-To: ' . $clean($in['email']),
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
    ];
    if (!@mail($to, $subject, $body, implode("\r\n", $headers))) {
        error_log('[form] e-posta gönderilemedi: ' . $to);
    }
}

/** Form alanı için hata iletisi basar. */
function field_error(array $form, string $name): string
{
    return isset($form['errors'][$name]) ? '<small class="field-error">' . e($form['errors'][$name]) . '</small>' : '';
}
