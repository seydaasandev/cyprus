<?php
/**
 * Rezervasyon talebi formu: ziyaretçi otel, oda, tarih ve kişi bilgilerini doldurur;
 * talep e-posta ile iletilir ve paneldeki Mesajlar bölümüne kaydedilir. Çevrimiçi ödeme yoktur.
 */
$old = $form['old'];
$hotelBySlug = array_column($hotels, null, 'slug');
$selectedHotel = null;
if (!empty($old['hotel'])) {
    foreach ($hotels as $h) {
        if ($h['name'] === $old['hotel']) {
            $selectedHotel = $h;
        }
    }
} else {
    $selectedHotel = $hotelBySlug[(string) ($_GET['otel'] ?? '')] ?? null;
}
$selectedRoom = (int) ($old['room'] ?? ($_GET['oda'] ?? 0));

// Otel adına göre oda listesi (otel değiştirildiğinde oda seçenekleri tarayıcıda güncellenir).
$hotelNameById = array_column($hotels, 'name', 'id');
$roomsByHotel = [];
foreach ($rooms as $r) {
    $label = $r['name'] . ($r['price'] !== '' ? ' — ' . $r['price'] : '') . ($r['note'] !== '' ? ' (' . $r['note'] . ')' : '');
    $roomsByHotel[$hotelNameById[$r['hotel_id']] ?? ''][] = ['id' => (int) $r['id'], 'label' => $label];
}
$currentRooms = $selectedHotel ? ($roomsByHotel[$selectedHotel['name']] ?? []) : [];
$today = date('Y-m-d');
$title = setting('reserve_title', 'Rezervasyon Talebi');
?>
<?= page_hero($title, opt('site_name'), $selectedHotel['image'] ?? setting('request_image'), [[setting('hotels_title', 'Oteller'), 'oteller'], [$title, '']], setting('reserve_text')) ?>
<section class="sec sec--light">
  <div class="container contact">
    <div class="contact__info">
      <?= section_head(opt('site_name'), setting('reserve_heading', 'Rezervasyon Talebinizi İletin'), setting('reserve_text'), 'left') ?>
      <?php if ($selectedHotel): ?>
      <a class="reserve-hotel" href="<?= e(url('oteller/' . $selectedHotel['slug'])) ?>" data-reveal>
        <?php if ($selectedHotel['image'] !== ''): ?><img src="<?= e(thumb($selectedHotel['image'], 720)) ?>" alt="<?= e($selectedHotel['name']) ?>" loading="lazy"><?php endif; ?>
        <span>
          <?php if ($selectedHotel['concept'] !== ''): ?><small><?= e($selectedHotel['concept']) ?></small><?php endif; ?>
          <strong><?= e($selectedHotel['name']) ?></strong>
          <?php if ($selectedHotel['location'] !== ''): ?><em><?= icon('map-pin', 15) ?><?= e($selectedHotel['location']) ?></em><?php endif; ?>
        </span>
      </a>
      <?php endif; ?>
      <ul class="contact-list contact-list--compact">
        <?php if (setting('phone') !== ''): ?><li data-reveal><span><?= icon('phone', 20) ?></span><div><h3><?= e(setting('label_phone', 'Telefon')) ?></h3><p><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', setting('phone'))) ?>"><?= e(setting('phone')) ?></a></p></div></li><?php endif; ?>
        <?php $resMail = setting('reservation_mail_to', setting('social_mail', setting('email'))); if ($resMail !== ''): ?>
        <li data-reveal data-delay="1"><span><?= icon('mail', 20) ?></span><div><h3><?= e(setting('label_email', 'E-posta')) ?></h3><p><a href="mailto:<?= e($resMail) ?>"><?= e($resMail) ?></a></p></div></li>
        <?php endif; ?>
      </ul>
      <?php if (setting('reserve_note') !== ''): ?><p class="reserve-note" data-reveal><?= icon('shield', 18) ?><span><?= nl2br(e(setting('reserve_note'))) ?></span></p><?php endif; ?>
    </div>

    <div class="form-card" id="form" data-reveal="right">
      <?php if ($form['sent']): ?>
      <div class="notice notice--ok"><?= icon('check-circle', 22) ?><div><strong><?= e(setting('reserve_ok_title', 'Rezervasyon talebiniz bize ulaştı.')) ?></strong><p><?= e(setting('form_ok_text', 'En kısa sürede sizinle iletişime geçeceğiz.')) ?></p></div></div>
      <?php endif; ?>
      <?php if (isset($form['errors']['_form'])): ?><div class="notice notice--err"><?= e($form['errors']['_form']) ?></div><?php endif; ?>
      <h2><?= e($title) ?></h2>
      <form method="post" action="<?= e(url('rezervasyon')) ?>#form" class="form" novalidate>
        <?= csrf_field() ?>
        <div class="hp" aria-hidden="true"><label>Web sitesi<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

        <label class="field"><span>Otel *</span>
          <select name="hotel" required data-room-source="<?= e(json_encode($roomsByHotel, JSON_UNESCAPED_UNICODE)) ?>">
            <option value="">Seçiniz</option>
            <?php foreach ($hotels as $h): ?><option value="<?= e($h['name']) ?>"<?= $selectedHotel && $selectedHotel['id'] === $h['id'] ? ' selected' : '' ?>><?= e($h['name']) ?></option><?php endforeach; ?>
          </select><?= field_error($form, 'hotel') ?>
        </label>
        <label class="field" data-room-field<?= $currentRooms ? '' : ' hidden' ?>><span>Oda seçeneği</span>
          <select name="room">
            <option value="">Fark etmez / bilgi istiyorum</option>
            <?php foreach ($currentRooms as $r): ?><option value="<?= (int) $r['id'] ?>"<?= $selectedRoom === $r['id'] ? ' selected' : '' ?>><?= e($r['label']) ?></option><?php endforeach; ?>
          </select><?= field_error($form, 'room') ?>
        </label>

        <div class="form__row">
          <label class="field"><span>Giriş tarihi *</span><input type="date" name="checkin" required min="<?= e($today) ?>" value="<?= e($old['checkin'] ?? '') ?>"><?= field_error($form, 'checkin') ?></label>
          <label class="field"><span>Çıkış tarihi *</span><input type="date" name="checkout" required min="<?= e($today) ?>" value="<?= e($old['checkout'] ?? '') ?>"><?= field_error($form, 'checkout') ?></label>
        </div>
        <div class="form__row">
          <label class="field"><span>Yetişkin sayısı *</span><input type="number" name="adults" required min="1" max="20" value="<?= e($old['adults'] ?? '2') ?>"><?= field_error($form, 'adults') ?></label>
          <label class="field"><span>Çocuk sayısı</span><input type="number" name="children" min="0" max="20" value="<?= e($old['children'] ?? '0') ?>"><?= field_error($form, 'children') ?></label>
        </div>
        <div class="form__row">
          <label class="field"><span>Adınız *</span><input type="text" name="name" required maxlength="120" autocomplete="given-name" value="<?= e($old['name'] ?? '') ?>"><?= field_error($form, 'name') ?></label>
          <label class="field"><span>Soyadınız *</span><input type="text" name="surname" required maxlength="120" autocomplete="family-name" value="<?= e($old['surname'] ?? '') ?>"><?= field_error($form, 'surname') ?></label>
        </div>
        <div class="form__row">
          <label class="field"><span>Telefon *</span><input type="tel" name="phone" required maxlength="30" autocomplete="tel" value="<?= e($old['phone'] ?? '') ?>"><?= field_error($form, 'phone') ?></label>
          <label class="field"><span>E-posta adresiniz *</span><input type="email" name="email" required maxlength="190" autocomplete="email" value="<?= e($old['email'] ?? '') ?>"><?= field_error($form, 'email') ?></label>
        </div>
        <label class="field"><span>Notunuz</span><textarea name="message" rows="4" maxlength="4000" placeholder="Çocuk yaşları, özel istekler vb."><?= e($old['message'] ?? '') ?></textarea><?= field_error($form, 'message') ?></label>
        <?= captcha_field($form) ?>
        <button class="btn btn--primary btn--lg" type="submit"><span><?= e(setting('label_book_send', 'Rezervasyon Talebi Gönder')) ?></span><?= icon('send', 18) ?></button>
      </form>
    </div>
  </div>
</section>
