<?php
/** İçerik ekleme / düzenleme formu. Kaydetme hatasında crud_save.php bu dosyayı $formValues/$formErrors ile yeniden çağırır. */
require_once __DIR__ . '/_crud.php';

[$key, $def] = crud_entity();
$id = (int) ($_GET['id'] ?? 0);
$record = $id > 0 ? crud_find($def, $id) : null;
if ($id > 0 && !$record) {
    flash('Kayıt bulunamadı.', 'err');
    redirect(admin_url('list', ['e' => $key]));
}
if ($id === 0 && !empty($def['fixed'])) {
    redirect(admin_url('list', ['e' => $key]));
}
$values = $formValues ?? ($record ?? []);
$errors = $formErrors ?? [];
$fields = $def['fields'];
foreach ($fields as $name => &$spec) {
    if (($spec['type'] ?? '') === 'relation' && $spec['table'] === $def['table']) {
        $spec['exclude'] = $id;
    }
}
unset($spec);

$gallery = [];
if (!empty($def['gallery'])) {
    $gallery = isset($formGallery) ? $formGallery : ($id > 0 ? array_column(rows('SELECT image FROM ' . $def['gallery']['table'] . ' WHERE ' . $def['gallery']['fk'] . ' = ? ORDER BY sort, id', [$id]), 'image') : []);
}

$title = $record ? ($def['single'] . ': ' . strip_tags((string) $record[$def['label']])) : ('Yeni ' . $def['single']);
$actions = [admin_btn('Listeye dön', admin_url('list', ['e' => $key]), 'arrow-left', 'adm-btn--ghost')];
if ($record && ($view = crud_view_url($def, $record)) !== '') {
    $actions[] = admin_btn('Sitede gör', $view, 'eye', 'adm-btn--ghost', true);
}
admin_header($title, $actions);
?>
<?php if ($errors): ?>
<div class="adm-alert adm-alert--err"><strong>Kaydedilemedi:</strong> <?= e(implode(' ', $errors)) ?></div>
<?php endif; ?>
<form method="post" action="<?= e(admin_url('save', ['e' => $key] + ($id ? ['id' => $id] : []))) ?>" class="adm-form" data-dirty>
  <?= csrf_field() ?>
  <?= admin_fields($fields, $values, $def['single'] . ' bilgileri') ?>

  <?php if (!empty($def['gallery'])): ?>
  <section class="adm-card">
    <h2>Fotoğraf galerisi</h2>
    <p class="adm-hint">Birden çok görsel ekleyebilir, sürükleyerek sıralayabilirsiniz. Ana görsel galerinin ilk fotoğrafı olarak gösterilir.</p>
    <div class="adm-gallery" data-gallery>
      <div class="adm-gallery__items" data-gallery-items>
        <?php foreach ($gallery as $img): ?>
        <div class="adm-gallery__item"><input type="hidden" name="gallery[]" value="<?= e($img) ?>"><?= admin_media_preview($img) ?><button type="button" data-gallery-remove title="Kaldır"><?= icon('close', 14) ?></button></div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="adm-btn adm-btn--soft" data-gallery-add><?= icon('plus', 16) ?><span>Fotoğraf Ekle</span></button>
    </div>
  </section>
  <?php endif; ?>

  <div class="adm-savebar">
    <button class="adm-btn adm-btn--primary adm-btn--lg" type="submit"><?= icon('check', 18) ?><span>Kaydet</span></button>
    <button class="adm-btn adm-btn--soft adm-btn--lg" type="submit" name="_stay" value="1"><span>Kaydet ve düzenlemeye devam et</span></button>
    <a class="adm-btn adm-btn--ghost adm-btn--lg" href="<?= e(admin_url('list', ['e' => $key])) ?>">Vazgeç</a>
  </div>
</form>
<?php admin_footer(); ?>
