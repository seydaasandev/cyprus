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

$roomRows = [];
if (!empty($def['rooms'])) {
    $roomRows = $formRooms ?? ($id > 0 ? rows('SELECT name, details, price, note FROM hotel_rooms WHERE hotel_id = ? ORDER BY sort, id', [$id]) : []);
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

  <?php if (!empty($def['rooms'])):
      $roomRow = static function (array $r): string {
          return '<div class="adm-room" data-room>'
              . '<span class="adm-grip" title="Sürükleyerek sırala">' . icon('grip', 18) . '</span>'
              . '<label><span>Oda adı *</span><input type="text" name="room_name[]" maxlength="190" value="' . e($r['name'] ?? '') . '" placeholder="Standart Oda (Deniz Manzaralı)"></label>'
              . '<label><span>Fiyat</span><input type="text" name="room_price[]" maxlength="80" value="' . e($r['price'] ?? '') . '" placeholder="14.040 ₺"></label>'
              . '<label><span>Fiyat notu</span><input type="text" name="room_note[]" maxlength="190" value="' . e($r['note'] ?? '') . '" placeholder="2 kişi / gecelik"></label>'
              . '<label class="adm-room__wide"><span>Açıklama</span><input type="text" name="room_details[]" maxlength="400" value="' . e($r['details'] ?? '') . '" placeholder="Kapasite, pansiyon, oda özellikleri"></label>'
              . '<button type="button" class="adm-icon-btn adm-icon-btn--danger" data-room-remove title="Odayı kaldır">' . icon('trash', 16) . '</button>'
              . '</div>';
      };
  ?>
  <section class="adm-card">
    <h2>Oda seçenekleri ve fiyatlar</h2>
    <p class="adm-hint">Her satır otel sayfasında bir oda seçeneği olarak görünür; rezervasyon düğmesi açıksa (Sayfa Metinleri > Oteller sayfası) yanında "Rezervasyon Yap" düğmesi çıkar. Fiyatı istediğiniz biçimde yazabilirsiniz (ör. "14.040 ₺", "€120", "Fiyat sorunuz"). Sırayı değiştirmek için satırı tutup sürükleyin; oda adı boş bırakılan satır kaydedilmez.</p>
    <div class="adm-rooms" data-rooms>
      <div data-room-list><?php foreach ($roomRows as $r) { echo $roomRow($r); } ?></div>
      <template data-room-template><?= $roomRow([]) ?></template>
      <button type="button" class="adm-btn adm-btn--soft" data-room-add><?= icon('plus', 16) ?><span>Oda Ekle</span></button>
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
