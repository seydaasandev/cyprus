<?php
/** Ayar grupları (renkler, logo, iletişim, metinler, alt bilgi, SEO, dil). */
$schema = settings_schema();
$group = preg_replace('/[^a-z]/', '', (string) ($_GET['g'] ?? 'general'));
if (!isset($schema[$group])) {
    $group = 'general';
}
$fields = $schema[$group]['fields'];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    admin_require_post();
    if (!empty($_POST['_reset'])) {
        // Yalnızca bu gruptaki, varsayılanı olan alanları ilk haline döndür.
        foreach ($fields as $name => $spec) {
            if (array_key_exists('default', $spec)) {
                set_setting($name, (string) $spec['default']);
            }
        }
        flash('Bu sayfadaki ayarlar varsayılan değerlere döndürüldü.');
        redirect(admin_url('settings', ['g' => $group]));
    }
    foreach ($fields as $name => $spec) {
        // PHP, alan adlarındaki tire ve noktayı alt çizgiye çevirmez; ancak boşluk/nokta için güvenli tarafta kal.
        $raw = $_POST[$name] ?? ($_POST[str_replace(['.', ' '], '_', $name)] ?? '');
        set_setting($name, (string) admin_clean_value($spec, $raw));
    }
    flash('Ayarlar kaydedildi.');
    redirect(admin_url('settings', ['g' => $group]));
}

$values = [];
$all = settings_all();
foreach ($fields as $name => $spec) {
    $values[$name] = array_key_exists($name, $all) ? $all[$name] : ($spec['default'] ?? '');
}
$hasDefaults = (bool) array_filter($fields, static fn(array $s): bool => array_key_exists('default', $s));
admin_header($schema[$group]['title']);
?>
<nav class="adm-tabs">
  <?php foreach ($schema as $g => $info): ?>
  <a href="<?= e(admin_url('settings', ['g' => $g])) ?>" class="<?= $g === $group ? 'is-active' : '' ?>"><?= icon($info['icon'], 16) ?><span><?= e($info['title']) ?></span></a>
  <?php endforeach; ?>
</nav>
<?php if ($group === 'appearance'): ?>
<section class="adm-card adm-preview" data-theme-preview>
  <h2>Canlı önizleme</h2>
  <div class="adm-preview__box">
    <span class="adm-preview__kicker" lang="en">Cyprus Touristic</span>
    <strong class="adm-preview__title">Akdenizin Kalbi Kuzey Kıbrıs</strong>
    <p>Renk, yazı tipi ve düğme ayarlarını değiştirdikçe bu kutu güncellenir. Kaydettiğinizde tüm siteye uygulanır.</p>
    <span class="adm-preview__btn">Talep Formu</span>
    <span class="adm-preview__btn adm-preview__btn--accent">Detaylar</span>
  </div>
</section>
<?php endif; ?>
<form method="post" action="<?= e(admin_url('settings', ['g' => $group])) ?>" class="adm-form" data-dirty>
  <?= csrf_field() ?>
  <?= admin_fields($fields, $values, $schema[$group]['title']) ?>
  <div class="adm-savebar">
    <button class="adm-btn adm-btn--primary adm-btn--lg" type="submit"><?= icon('check', 18) ?><span>Ayarları Kaydet</span></button>
    <a class="adm-btn adm-btn--ghost adm-btn--lg" href="<?= e(url()) ?>" target="_blank" rel="noopener"><?= icon('external', 16) ?><span>Siteyi Gör</span></a>
    <?php if ($hasDefaults): ?>
    <button class="adm-btn adm-btn--danger-ghost adm-btn--lg" type="submit" name="_reset" value="1" data-confirm-click="Bu sayfadaki ayarlar varsayılan değerlere dönsün mü?">Varsayılana dön</button>
    <?php endif; ?>
  </div>
</form>
<?php admin_footer(); ?>
