<?php
/** Talep formu: otel/hizmet talebi toplar (satış ya da ödeme yoktur). */
$old = $form['old'];
$preselect = $old['hotel'] ?? trim((string) ($_GET['otel'] ?? ''));
$steps = rows("SELECT * FROM items WHERE grp = 'step' AND active = 1 ORDER BY sort, id");
?>
<?= page_hero(setting('request_title', 'Talep Formu'), opt('site_name'), setting('request_image'), [[setting('request_title', 'Talep Formu'), '']], setting('request_text')) ?>
<section class="sec sec--light">
  <div class="container contact">
    <div class="contact__info">
      <?= section_head(opt('site_name'), setting('request_heading', 'Talebinizi Bize İletin'), setting('request_text'), 'left') ?>
      <?php if ($steps): ?>
      <ol class="steps">
        <?php foreach ($steps as $i => $st): ?>
        <li data-reveal data-delay="<?= $i % 4 ?>"><b><?= $i + 1 ?></b><div><h3><?= e($st['title']) ?></h3><?php if (trim((string) $st['body']) !== ''): ?><p><?= e($st['body']) ?></p><?php endif; ?></div></li>
        <?php endforeach; ?>
      </ol>
      <?php endif; ?>
      <ul class="contact-list contact-list--compact">
        <?php if (setting('phone') !== ''): ?><li data-reveal><span><?= icon('phone', 20) ?></span><div><h3><?= e(setting('label_phone', 'Telefon')) ?></h3><p><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', setting('phone'))) ?>"><?= e(setting('phone')) ?></a></p></div></li><?php endif; ?>
        <?php if (setting('email') !== ''): ?><li data-reveal data-delay="1"><span><?= icon('mail', 20) ?></span><div><h3><?= e(setting('label_email', 'E-posta')) ?></h3><p><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></p></div></li><?php endif; ?>
      </ul>
    </div>
    <div class="form-card" id="form" data-reveal="right">
      <?php if ($form['sent']): ?>
      <div class="notice notice--ok"><?= icon('check-circle', 22) ?><div><strong><?= e(setting('request_ok_title', 'Talebiniz bize ulaştı.')) ?></strong><p><?= e(setting('form_ok_text', 'En kısa sürede sizinle iletişime geçeceğiz.')) ?></p></div></div>
      <?php endif; ?>
      <?php if (isset($form['errors']['_form'])): ?><div class="notice notice--err"><?= e($form['errors']['_form']) ?></div><?php endif; ?>
      <h2><?= e(setting('request_title', 'Talep Formu')) ?></h2>
      <form method="post" action="<?= e(url('talep-formu')) ?>#form" class="form" novalidate>
        <?= csrf_field() ?>
        <div class="hp" aria-hidden="true"><label>Web sitesi<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
        <div class="form__row">
          <label class="field"><span>Adınız *</span><input type="text" name="name" required maxlength="120" autocomplete="given-name" value="<?= e($old['name'] ?? '') ?>"><?= field_error($form, 'name') ?></label>
          <label class="field"><span>Soyadınız</span><input type="text" name="surname" maxlength="120" autocomplete="family-name" value="<?= e($old['surname'] ?? '') ?>"><?= field_error($form, 'surname') ?></label>
        </div>
        <div class="form__row">
          <label class="field"><span>Firma Adı</span><input type="text" name="company" maxlength="190" autocomplete="organization" value="<?= e($old['company'] ?? '') ?>"><?= field_error($form, 'company') ?></label>
          <label class="field"><span>Ülke</span><input type="text" name="country" maxlength="120" autocomplete="country-name" value="<?= e($old['country'] ?? '') ?>"><?= field_error($form, 'country') ?></label>
        </div>
        <div class="form__row">
          <label class="field"><span>Telefon</span><input type="tel" name="phone" maxlength="30" autocomplete="tel" value="<?= e($old['phone'] ?? '') ?>"><?= field_error($form, 'phone') ?></label>
          <label class="field"><span>E-posta adresiniz *</span><input type="email" name="email" required maxlength="190" autocomplete="email" value="<?= e($old['email'] ?? '') ?>"><?= field_error($form, 'email') ?></label>
        </div>
        <?php if ($hotels): ?>
        <label class="field"><span>İlgilendiğiniz otel</span>
          <select name="hotel">
            <option value="">Seçiniz (isteğe bağlı)</option>
            <?php foreach ($hotels as $h): ?><option value="<?= e($h['name']) ?>"<?= $preselect === $h['name'] ? ' selected' : '' ?>><?= e($h['name']) ?></option><?php endforeach; ?>
          </select><?= field_error($form, 'hotel') ?>
        </label>
        <?php endif; ?>
        <label class="field"><span>Mesajınız</span><textarea name="message" rows="5" maxlength="4000"><?= e($old['message'] ?? '') ?></textarea><?= field_error($form, 'message') ?></label>
        <?= captcha_field($form) ?>
        <button class="btn btn--primary btn--lg" type="submit"><span><?= e(setting('label_request', 'Talep Gönder')) ?></span><?= icon('send', 18) ?></button>
      </form>
    </div>
  </div>
</section>
