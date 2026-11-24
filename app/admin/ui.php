<?php
/**
 * Yönetim paneli arayüz parçaları: sayfa iskeleti, yan menü ve form alanları.
 */
declare(strict_types=1);

function admin_nav(): array
{
    return [
        'Genel' => [
            ['dashboard', [], 'Özet', 'home'],
            ['messages', [], 'Mesajlar', 'inbox'],
        ],
        'Anasayfa' => [
            ['list', ['e' => 'slides'], 'Slider / Video', 'film'],
            ['list', ['e' => 'sections'], 'Bölümler ve Sıralama', 'layers'],
            ['list', ['e' => 'counters'], 'Sayaçlar', 'target'],
        ],
        'İçerik' => [
            ['list', ['e' => 'hotels'], 'Oteller', 'building'],
            ['list', ['e' => 'services'], 'Hizmetler', 'briefcase'],
            ['list', ['e' => 'regions'], 'Bölgeler', 'map-pin'],
            ['list', ['e' => 'factsheets'], 'Hotel Factsheets', 'file'],
            ['list', ['e' => 'pages'], 'Sayfalar', 'edit'],
            ['list', ['e' => 'guarantees'], 'Garanti Ediyoruz', 'shield'],
            ['list', ['e' => 'values'], 'Öz Değerlerimiz', 'heart'],
            ['list', ['e' => 'offers'], 'Hizmet Listesi', 'check-circle'],
            ['list', ['e' => 'steps'], 'Talep Adımları', 'list'],
            ['media', [], 'Medya Kütüphanesi', 'image'],
        ],
        'Menüler' => [
            ['list', ['e' => 'menu_header'], 'Üst Menü', 'menu'],
            ['list', ['e' => 'menu_footer'], 'Alt Menü 1', 'list'],
            ['list', ['e' => 'menu_footer2'], 'Alt Menü 2', 'list'],
        ],
        'Tasarım ve Ayarlar' => [
            ['settings', ['g' => 'appearance'], 'Renkler ve Stil', 'palette'],
            ['settings', ['g' => 'general'], 'Genel ve Logo', 'settings'],
            ['settings', ['g' => 'contact'], 'İletişim ve Sosyal', 'phone'],
            ['settings', ['g' => 'texts'], 'Sayfa Metinleri', 'edit'],
            ['settings', ['g' => 'footer'], 'Alt Bilgi', 'layers'],
            ['settings', ['g' => 'seo'], 'SEO ve Kodlar', 'search'],
            ['settings', ['g' => 'translate'], 'Dil / Çeviri', 'globe'],
            ['list', ['e' => 'users'], 'Yöneticiler', 'user'],
        ],
    ];
}

function admin_header(string $title, array $actions = []): void
{
    global $me;
    $page = (string) ($_GET['p'] ?? 'dashboard');
    $entity = (string) ($_GET['e'] ?? '');
    $group = (string) ($_GET['g'] ?? '');
    $unread = (int) val('SELECT COUNT(*) FROM messages WHERE is_read = 0');
    $flash = flash();
    ?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> – <?= e(opt('site_name')) ?> Yönetim</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap">
<link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body>
<div class="adm">
  <aside class="adm-side" id="admSide">
    <a class="adm-brand" href="<?= e(admin_url()) ?>">
      <?php if (setting('logo_light') !== ''): ?><img src="<?= e(media(setting('logo_light'))) ?>" alt="<?= e(opt('site_name')) ?>"><?php elseif (setting('logo') !== ''): ?><img class="on-plate" src="<?= e(media(setting('logo'))) ?>" alt="<?= e(opt('site_name')) ?>"><?php else: ?><strong><?= e(opt('site_name')) ?></strong><?php endif; ?>
      <span>Yönetim Paneli</span>
    </a>
    <nav class="adm-nav">
      <?php foreach (admin_nav() as $heading => $links): ?>
      <p><?= e($heading) ?></p>
      <?php foreach ($links as [$p, $params, $label, $ic]):
          $active = $p === $page && (($params['e'] ?? '') === $entity || in_array($page, ['dashboard', 'messages', 'media'], true)) && (($params['g'] ?? '') === $group || $p !== 'settings');
          if (in_array($page, ['edit'], true) && ($params['e'] ?? null) === $entity && $p === 'list') {
              $active = true;
          }
          ?>
      <a href="<?= e(admin_url($p, $params)) ?>" class="<?= $active ? 'is-active' : '' ?>"><?= icon($ic, 18) ?><span><?= e($label) ?></span><?php if ($p === 'messages' && $unread > 0): ?><b><?= $unread ?></b><?php endif; ?></a>
      <?php endforeach; ?>
      <?php endforeach; ?>
    </nav>
  </aside>
  <div class="adm-main">
    <header class="adm-top">
      <button type="button" class="adm-burger" data-side-toggle aria-label="Menü"><?= icon('menu', 22) ?></button>
      <h1><?= e($title) ?></h1>
      <div class="adm-top__right">
        <a class="adm-btn adm-btn--ghost" href="<?= e(url()) ?>" target="_blank" rel="noopener"><?= icon('external', 16) ?><span>Siteyi Gör</span></a>
        <a class="adm-user" href="<?= e(admin_url('profile')) ?>"><?= icon('user', 16) ?><span><?= e($me['name'] ?: $me['username']) ?></span></a>
        <form method="post" action="<?= e(admin_url('logout')) ?>"><?= csrf_field() ?><button class="adm-btn adm-btn--ghost" type="submit" title="Çıkış"><?= icon('logout', 16) ?><span>Çıkış</span></button></form>
      </div>
    </header>
    <div class="adm-content">
      <?php if ($flash): ?><div class="adm-alert adm-alert--<?= e($flash['t']) ?>" data-flash><?= e($flash['m']) ?></div><?php endif; ?>
      <?php if ($actions): ?><div class="adm-actions"><?php foreach ($actions as $a) { echo $a; } ?></div><?php endif; ?>
    <?php
}

function admin_footer(): void
{
    ?>
    </div>
  </div>
</div>
<div class="adm-modal" id="mediaModal" aria-hidden="true">
  <div class="adm-modal__box">
    <header><h2>Medya Kütüphanesi</h2>
      <label class="adm-btn adm-btn--primary"><?= icon('plus', 16) ?><span>Dosya Yükle</span><input type="file" hidden multiple data-media-upload></label>
      <button type="button" class="adm-btn adm-btn--ghost" data-media-close><?= icon('close', 18) ?></button>
    </header>
    <p class="adm-modal__status" data-media-status></p>
    <div class="adm-media-grid" data-media-grid></div>
  </div>
</div>
<script>window.ADM = {csrf: <?= json_encode(csrf_token()) ?>, base: <?= json_encode(base_path()) ?>, mediaUrl: <?= json_encode(admin_url('media', ['json' => 1])) ?>, uploadUrl: <?= json_encode(admin_url('upload')) ?>};</script>
<script src="<?= e(asset('vendor/sortable.min.js')) ?>"></script>
<script src="<?= e(asset('js/admin.js')) ?>"></script>
</body>
</html>
    <?php
}

function admin_btn(string $label, string $href, string $ic = '', string $class = 'adm-btn--primary', bool $newTab = false): string
{
    return '<a class="adm-btn ' . $class . '" href="' . e($href) . '"' . ($newTab ? ' target="_blank" rel="noopener"' : '') . '>' . ($ic ? icon($ic, 16) : '') . '<span>' . e($label) . '</span></a>';
}

/** Site içi bağlantı önerileri (menü ve düğme bağlantı alanları için). */
function admin_link_options(): array
{
    static $opts = null;
    if ($opts !== null) {
        return $opts;
    }
    $opts = ['' => 'Anasayfa', 'oteller' => 'Rezervasyon Yap (oteller)', 'hizmetlerimiz' => 'Hizmetlerimiz', 'kuzey-kibris' => 'Kuzey Kıbrıs (bölgeler)', 'hotel-factsheets' => 'Hotel Factsheets', 'iletisim' => 'İletişim', 'talep-formu' => 'Talep Formu'];
    foreach (rows('SELECT slug, title FROM pages ORDER BY sort, id') as $r) {
        $opts[$r['slug']] = 'Sayfa: ' . $r['title'];
    }
    foreach (rows('SELECT slug, title FROM services ORDER BY sort, id') as $r) {
        $opts[$r['slug']] = 'Hizmet: ' . $r['title'];
    }
    foreach (rows('SELECT slug, title FROM regions ORDER BY sort, id') as $r) {
        $opts[$r['slug']] = 'Bölge: ' . $r['title'];
    }
    foreach (rows('SELECT slug, title FROM factsheets ORDER BY sort, id') as $r) {
        $opts[$r['slug']] = 'Factsheet: ' . $r['title'];
    }
    foreach (rows('SELECT slug, name FROM hotels ORDER BY sort, id') as $r) {
        $opts['oteller/' . $r['slug']] = 'Otel: ' . $r['name'];
    }
    return $opts;
}

function admin_media_preview(string $path): string
{
    if ($path === '') {
        return '';
    }
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if (in_array($ext, ['mp4', 'webm'], true)) {
        return '<video src="' . e(media($path)) . '" muted preload="metadata"></video>';
    }
    if ($ext === 'pdf') {
        return '<span class="adm-file">' . icon('file', 22) . e(basename($path)) . '</span>';
    }
    return '<img src="' . e(thumb($path, 400)) . '" alt="">';
}

/** Tek bir form alanını basar. */
function admin_field(string $name, array $spec, $value): string
{
    $type = $spec['type'] ?? 'text';
    $id = 'f_' . preg_replace('/[^a-z0-9_]/i', '_', $name);
    $value = $value ?? ($spec['default'] ?? '');
    $req = !empty($spec['required']) ? ' required' : '';
    $help = !empty($spec['help']) ? '<small class="adm-help">' . e($spec['help']) . '</small>' : '';
    $label = '<label class="adm-label" for="' . $id . '">' . e($spec['label'] ?? $name) . (!empty($spec['required']) ? ' <i>*</i>' : '') . '</label>';
    $wide = in_array($type, ['html', 'textarea', 'code', 'checks'], true) ? ' adm-field--wide' : '';
    $h = '<div class="adm-field adm-field--' . e($type) . $wide . '">';

    switch ($type) {
        case 'textarea':
        case 'code':
            $h .= $label . '<textarea id="' . $id . '" name="' . e($name) . '" rows="' . (int) ($spec['rows'] ?? 4) . '"' . (isset($spec['max']) ? ' maxlength="' . (int) $spec['max'] . '"' : '') . ($type === 'code' ? ' class="adm-code" spellcheck="false"' : '') . $req . '>' . e($value) . '</textarea>';
            break;
        case 'html':
            $h .= $label . '<textarea id="' . $id . '" name="' . e($name) . '" rows="14" data-rich>' . e(content_html((string) $value)) . '</textarea>';
            break;
        case 'bool':
            $h .= '<label class="adm-switch"><input type="hidden" name="' . e($name) . '" value="0"><input type="checkbox" id="' . $id . '" name="' . e($name) . '" value="1"' . ((string) $value === '1' ? ' checked' : '') . '><i></i><span>' . e($spec['label'] ?? $name) . '</span></label>';
            break;
        case 'select':
            $h .= $label . '<select id="' . $id . '" name="' . e($name) . '">';
            foreach ($spec['options'] as $k => $v) {
                $h .= '<option value="' . e($k) . '"' . ((string) $k === (string) $value ? ' selected' : '') . '>' . e($v) . '</option>';
            }
            $h .= '</select>';
            break;
        case 'relation':
            $where = '';
            $params = [];
            foreach (($spec['where'] ?? []) as $col => $v) {
                $where .= ($where ? ' AND ' : ' WHERE ') . $col . ($v === null ? ' IS NULL' : ' = ?');
                if ($v !== null) {
                    $params[] = $v;
                }
            }
            $h .= $label . '<select id="' . $id . '" name="' . e($name) . '"><option value="">' . e($spec['empty'] ?? '—') . '</option>';
            foreach (rows('SELECT id, ' . $spec['label_col'] . ' AS lbl FROM ' . $spec['table'] . $where . ' ORDER BY sort, id', $params) as $r) {
                if (!empty($spec['exclude']) && (int) $spec['exclude'] === (int) $r['id']) {
                    continue;
                }
                $h .= '<option value="' . (int) $r['id'] . '"' . ((string) $r['id'] === (string) $value ? ' selected' : '') . '>' . e($r['lbl']) . '</option>';
            }
            $h .= '</select>';
            break;
        case 'icon':
            $h .= $label . '<div class="adm-icon-pick" data-icon-pick><input type="hidden" id="' . $id . '" name="' . e($name) . '" value="' . e($value) . '"><div class="adm-icon-grid">';
            foreach (array_keys(icon_paths()) as $ic) {
                $h .= '<button type="button" title="' . e($ic) . '" data-icon="' . e($ic) . '"' . ($ic === $value ? ' class="is-active"' : '') . '>' . icon($ic, 20) . '</button>';
            }
            $h .= '</div></div>';
            break;
        case 'color':
            $v = css_color((string) $value, (string) ($spec['default'] ?? '#000000'));
            $h .= $label . '<div class="adm-color" data-color><input type="color" value="' . e(substr($v, 0, 7)) . '" aria-label="Renk seçici"><input type="text" id="' . $id . '" name="' . e($name) . '" value="' . e($v) . '" maxlength="9" pattern="#[0-9A-Fa-f]{3,8}">'
                . (isset($spec['default']) ? '<button type="button" class="adm-link" data-color-reset="' . e($spec['default']) . '">Varsayılan</button>' : '') . '</div>';
            break;
        case 'range':
            $h .= $label . '<div class="adm-range" data-range><input type="range" id="' . $id . '" name="' . e($name) . '" min="' . (int) $spec['min'] . '" max="' . (int) $spec['max'] . '" step="' . (int) ($spec['step'] ?? 1) . '" value="' . e($value) . '"><output>' . e($value) . '</output></div>';
            break;
        case 'number':
            $h .= $label . '<input type="number" id="' . $id . '" name="' . e($name) . '" value="' . e($value) . '"' . (isset($spec['min']) ? ' min="' . (int) $spec['min'] . '"' : '') . (isset($spec['max']) ? ' max="' . (int) $spec['max'] . '"' : '') . $req . '>';
            break;
        case 'image':
        case 'video':
        case 'file':
            $kinds = implode(',', $spec['kinds'] ?? ['image']);
            $h .= $label . '<div class="adm-media" data-media-field data-kinds="' . e($kinds) . '"><input type="hidden" id="' . $id . '" name="' . e($name) . '" value="' . e($value) . '"><div class="adm-media__preview" data-media-preview>' . admin_media_preview((string) $value) . '</div><div class="adm-media__btns"><button type="button" class="adm-btn adm-btn--soft" data-media-pick>' . icon('image', 16) . '<span>Seç / Yükle</span></button><button type="button" class="adm-btn adm-btn--ghost" data-media-clear>' . icon('trash', 16) . '<span>Kaldır</span></button></div></div>';
            break;
        case 'slug':
            $h .= $label . '<div class="adm-slug"><span>' . e(base_path() . '/' . ($spec['prefix'] ?? '')) . '</span><input type="text" id="' . $id . '" name="' . e($name) . '" value="' . e($value) . '" data-slug-from="' . e($spec['from'] ?? '') . '" placeholder="otomatik"></div>';
            $help = $help ?: '<small class="adm-help">Boş bırakılırsa başlıktan otomatik üretilir. Yayındaki bir sayfanın adresini değiştirmek eski bağlantıları bozar.</small>';
            break;
        case 'url':
            $h .= $label . '<input type="text" id="' . $id . '" name="' . e($name) . '" value="' . e($value) . '" list="adm-links" placeholder="oteller  ·  https://...">';
            break;
        case 'password':
            $h .= $label . '<input type="password" id="' . $id . '" name="' . e($name) . '" value="" autocomplete="new-password" minlength="10">';
            break;
        case 'checks':
            $selected = array_map('trim', explode(',', (string) $value));
            $h .= '<span class="adm-label">' . e($spec['label'] ?? $name) . '</span><input type="hidden" name="' . e($name) . '[]" value=""><div class="adm-checks">';
            foreach ($spec['options'] as $k => $v) {
                $h .= '<label><input type="checkbox" name="' . e($name) . '[]" value="' . e($k) . '"' . (in_array((string) $k, $selected, true) ? ' checked' : '') . '><span>' . e($v) . '</span></label>';
            }
            $h .= '</div>';
            break;
        default:
            $h .= $label . '<input type="text" id="' . $id . '" name="' . e($name) . '" value="' . e($value) . '"' . (isset($spec['pattern']) ? ' pattern="' . e($spec['pattern']) . '"' : '') . $req . '>';
    }
    return $h . $help . '</div>';
}

/** Alanları "group" anahtarına göre kartlara ayırarak basar. */
function admin_fields(array $fields, array $values, string $firstGroup = 'İçerik'): string
{
    $groups = [];
    foreach ($fields as $name => $spec) {
        $groups[$spec['group'] ?? $firstGroup][$name] = $spec;
    }
    $h = '';
    foreach ($groups as $title => $set) {
        $h .= '<section class="adm-card"><h2>' . e($title) . '</h2><div class="adm-grid">';
        foreach ($set as $name => $spec) {
            $h .= admin_field($name, $spec, $values[$name] ?? null);
        }
        $h .= '</div></section>';
    }
    $h .= '<datalist id="adm-links">';
    foreach (admin_link_options() as $link => $label) {
        $h .= '<option value="' . e($link === '' ? '/' : $link) . '">' . e($label) . '</option>';
    }
    return $h . '</datalist>';
}

/** Formdan gelen ham değeri alan türüne göre doğrulayıp temizler. */
function admin_clean_value(array $spec, $raw)
{
    $type = $spec['type'] ?? 'text';
    switch ($type) {
        case 'bool':
            return (string) $raw === '1' ? 1 : 0;
        case 'number':
        case 'range':
            $n = (int) $raw;
            if (isset($spec['min'])) {
                $n = max((int) $spec['min'], $n);
            }
            if (isset($spec['max'])) {
                $n = min((int) $spec['max'], $n);
            }
            return $n;
        case 'html':
            return clean_html((string) $raw);
        case 'select':
            return array_key_exists((string) $raw, $spec['options']) ? (string) $raw : (string) ($spec['default'] ?? array_key_first($spec['options']));
        case 'relation':
            $id = (int) $raw;
            return $id > 0 && val('SELECT COUNT(*) FROM ' . $spec['table'] . ' WHERE id = ?', [$id]) ? $id : null;
        case 'icon':
            return isset(icon_paths()[(string) $raw]) ? (string) $raw : (string) ($spec['default'] ?? 'star');
        case 'color':
            return css_color((string) $raw, (string) ($spec['default'] ?? '#000000'));
        case 'image':
        case 'video':
        case 'file':
            $p = trim((string) $raw);
            // Yalnızca yükleme klasöründeki dosyalara izin ver.
            return ($p !== '' && preg_match('#^uploads/[A-Za-z0-9_\-./ ()]+$#u', $p) && !str_contains($p, '..')) ? $p : '';
        case 'url':
            $u = trim((string) $raw);
            if ($u === '/' ) {
                return '';
            }
            return preg_match('#^(https?://|mailto:|tel:|\#)#i', $u) ? mb_substr($u, 0, 255) : mb_substr(trim(preg_replace('#[^A-Za-z0-9_\-./?=&%\#]#', '', $u), '/'), 0, 255);
        case 'checks':
            $vals = array_intersect(array_keys($spec['options']), array_map('strval', (array) $raw));
            return implode(',', $vals);
        case 'code':
        case 'textarea':
            return trim(str_replace("\r\n", "\n", (string) $raw));
        default:
            return trim(preg_replace('/[\x00-\x1F\x7F]/u', '', (string) $raw));
    }
}
