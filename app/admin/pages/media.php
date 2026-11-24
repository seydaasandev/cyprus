<?php
/**
 * Medya kütüphanesi: yükleme (POST ?p=upload), JSON listesi (?p=media&json=1) ve yönetim ekranı.
 */
const MEDIA_LIST_LIMIT = 600;
const MEDIA_EXTENSIONS = [
    'image' => ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'ico'],
    'video' => ['mp4', 'webm'],
    'file' => ['pdf'],
];

/** uploads/ altındaki dosyaları (önbellek hariç) yeniden eskiye listeler. */
function media_scan(array $kinds): array
{
    $allowed = [];
    foreach ($kinds as $k) {
        $allowed = array_merge($allowed, MEDIA_EXTENSIONS[$k] ?? []);
    }
    $out = [];
    if (!is_dir(UPLOAD_DIR)) {
        return $out;
    }
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(UPLOAD_DIR, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        if (!$file->isFile()) {
            continue;
        }
        $rel = 'uploads/' . ltrim(str_replace('\\', '/', substr($file->getPathname(), strlen(UPLOAD_DIR))), '/');
        if (str_starts_with($rel, 'uploads/cache/')) {
            continue;
        }
        $ext = strtolower($file->getExtension());
        if (!in_array($ext, $allowed, true)) {
            continue;
        }
        $kind = in_array($ext, MEDIA_EXTENSIONS['video'], true) ? 'video' : (in_array($ext, MEDIA_EXTENSIONS['file'], true) ? 'file' : 'image');
        $out[] = ['path' => $rel, 'name' => $file->getFilename(), 'kind' => $kind, 'time' => $file->getMTime(), 'size' => $file->getSize()];
    }
    usort($out, static fn(array $a, array $b): int => $b['time'] <=> $a['time']);
    return array_slice($out, 0, MEDIA_LIST_LIMIT);
}

function media_item_json(array $f): array
{
    return [
        'path' => $f['path'],
        'name' => $f['name'],
        'kind' => $f['kind'],
        'url' => media($f['path']),
        'thumb' => $f['kind'] === 'image' ? thumb($f['path'], 400) : '',
    ];
}

function media_kinds(string $raw): array
{
    $kinds = array_values(array_intersect(['image', 'video', 'file'], explode(',', $raw)));
    return $kinds ?: ['image'];
}

$action = (string) ($_GET['p'] ?? 'media');

if ($action === 'upload') {
    admin_require_post();
    $kinds = media_kinds((string) ($_POST['kinds'] ?? 'image,video,file'));
    try {
        if (empty($_FILES['file'])) {
            throw new RuntimeException('Dosya alınamadı. Dosya, sunucunun yükleme sınırından (' . ini_get('post_max_size') . ') büyük olabilir.');
        }
        $path = save_upload($_FILES['file'], $kinds);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $kind = in_array($ext, MEDIA_EXTENSIONS['video'], true) ? 'video' : (in_array($ext, MEDIA_EXTENSIONS['file'], true) ? 'file' : 'image');
        admin_json(['ok' => true, 'item' => media_item_json(['path' => $path, 'name' => basename($path), 'kind' => $kind])]);
    } catch (RuntimeException $ex) {
        admin_json(['ok' => false, 'error' => $ex->getMessage()], 422);
    }
}

if (!empty($_GET['json'])) {
    $kinds = media_kinds((string) ($_GET['kinds'] ?? 'image'));
    admin_json(['ok' => true, 'items' => array_map('media_item_json', media_scan($kinds))]);
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    admin_require_post();
    $path = (string) admin_clean_value(['type' => 'image'], $_POST['path'] ?? '');
    $abs = $path !== '' ? realpath(ROOT_DIR . '/' . $path) : false;
    // Yalnızca yükleme klasörünün içindeki gerçek dosyalar silinebilir.
    if ($abs && str_starts_with($abs, realpath(UPLOAD_DIR) . DIRECTORY_SEPARATOR) && is_file($abs) && !str_starts_with(basename($abs), '.')) {
        unlink($abs);
        flash('Dosya silindi.');
    } else {
        flash('Dosya bulunamadı.', 'err');
    }
    redirect(admin_url('media'));
}

$files = media_scan(['image', 'video', 'file']);
admin_header('Medya Kütüphanesi');
?>
<p class="adm-hint">Görsel (JPG, PNG, WebP, SVG), video (MP4, WebM) ve PDF yükleyebilirsiniz. Tek dosya için sunucu sınırı: <?= e(ini_get('upload_max_filesize')) ?>. Bir içerikte kullanılan dosyayı silerseniz o içerikte görsel boş görünür.</p>
<section class="adm-card">
  <label class="adm-drop" data-drop>
    <?= icon('image', 28) ?>
    <strong>Dosyaları buraya sürükleyin ya da tıklayıp seçin</strong>
    <input type="file" hidden multiple data-media-upload data-reload="1">
    <em data-media-status></em>
  </label>
</section>
<section class="adm-card">
  <h2>Dosyalar (<?= count($files) ?>)</h2>
  <?php if (!$files): ?><p class="adm-empty">Henüz dosya yüklenmedi.</p><?php endif; ?>
  <div class="adm-media-grid adm-media-grid--manage">
    <?php foreach ($files as $f): ?>
    <figure>
      <a href="<?= e(media($f['path'])) ?>" target="_blank" rel="noopener"><?= admin_media_preview($f['path']) ?></a>
      <figcaption title="<?= e($f['path']) ?>"><?= e($f['name']) ?></figcaption>
      <form method="post" action="<?= e(admin_url('media')) ?>" data-confirm="“<?= e($f['name']) ?>” silinsin mi? Bu dosyayı kullanan içeriklerde görsel boş kalır."><?= csrf_field() ?><input type="hidden" name="path" value="<?= e($f['path']) ?>"><button class="adm-icon-btn adm-icon-btn--danger" type="submit" title="Sil"><?= icon('trash', 15) ?></button></form>
    </figure>
    <?php endforeach; ?>
  </div>
</section>
<?php admin_footer(); ?>
