<?php
/** İletişim sayfası: adres kartları, form ve harita. */
$old = $form['old'];
$map = setting('map_embed');
?>
<?= page_hero(setting('contact_title', 'İletişim'), opt('site_name'), setting('contact_image'), [[setting('contact_title', 'İletişim'), '']], setting('contact_text')) ?>
<section class="sec sec--light">
  <div class="container contact">
    <div class="contact__info">
      <?= section_head(opt('site_name'), setting('contact_heading', 'Bizimle İletişime Geçin'), setting('contact_text'), 'left') ?>
      <ul class="contact-list">
        <?php if (setting('address') !== ''): ?><li data-reveal><span><?= icon('map-pin', 22) ?></span><div><h3><?= e(setting('address_label', 'KKTC')) ?></h3><p><?= nl2br(e(setting('address'))) ?></p></div></li><?php endif; ?>
        <?php if (setting('address2') !== ''): ?><li data-reveal data-delay="1"><span><?= icon('map-pin', 22) ?></span><div><h3><?= e(setting('address2_label', 'Türkiye')) ?></h3><p><?= nl2br(e(setting('address2'))) ?></p></div></li><?php endif; ?>
        <?php if (setting('phone') !== ''): ?><li data-reveal data-delay="2"><span><?= icon('phone', 22) ?></span><div><h3><?= e(setting('label_phone', 'Telefon')) ?></h3><p><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', setting('phone'))) ?>"><?= e(setting('phone')) ?></a></p></div></li><?php endif; ?>
        <?php if (setting('email') !== ''): ?><li data-reveal data-delay="3"><span><?= icon('mail', 22) ?></span><div><h3><?= e(setting('label_email', 'E-posta')) ?></h3><p><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></p></div></li><?php endif; ?>
      </ul>
      <?php if (!empty($GLOBALS['site_socials'])): ?>
      <div class="social social--dark" data-reveal>
        <?php foreach ($GLOBALS['site_socials'] as $net => $link): ?><a href="<?= e($link) ?>"<?= $net === 'mail' ? ' title="' . e(substr($link, 7)) . '" aria-label="' . e(substr($link, 7)) . '"' : ' target="_blank" rel="noopener" aria-label="' . e(ucfirst($net)) . '"' ?>><?= icon($net, 18) ?></a><?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <div class="form-card" id="form" data-reveal="right">
      <?php if ($form['sent']): ?>
      <div class="notice notice--ok"><?= icon('check-circle', 22) ?><div><strong><?= e(setting('form_ok_title', 'Mesajınız bize ulaştı.')) ?></strong><p><?= e(setting('form_ok_text', 'En kısa sürede sizinle iletişime geçeceğiz.')) ?></p></div></div>
      <?php endif; ?>
      <?php if (isset($form['errors']['_form'])): ?><div class="notice notice--err"><?= e($form['errors']['_form']) ?></div><?php endif; ?>
      <h2><?= e(setting('contact_form_title', 'Bize Yazın')) ?></h2>
      <form method="post" action="<?= e(url('iletisim')) ?>#form" class="form" novalidate>
        <?= csrf_field() ?>
        <div class="hp" aria-hidden="true"><label>Web sitesi<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
        <div class="form__row">
          <label class="field"><span>Adınız *</span><input type="text" name="name" required maxlength="120" autocomplete="name" value="<?= e($old['name'] ?? '') ?>"><?= field_error($form, 'name') ?></label>
          <label class="field"><span>E-posta adresiniz *</span><input type="email" name="email" required maxlength="190" autocomplete="email" value="<?= e($old['email'] ?? '') ?>"><?= field_error($form, 'email') ?></label>
        </div>
        <div class="form__row">
          <label class="field"><span>Telefon</span><input type="tel" name="phone" maxlength="30" autocomplete="tel" value="<?= e($old['phone'] ?? '') ?>"><?= field_error($form, 'phone') ?></label>
          <label class="field"><span>Konu</span><input type="text" name="subject" maxlength="250" value="<?= e($old['subject'] ?? '') ?>"><?= field_error($form, 'subject') ?></label>
        </div>
        <label class="field"><span>İletiniz *</span><textarea name="message" rows="6" required maxlength="4000"><?= e($old['message'] ?? '') ?></textarea><?= field_error($form, 'message') ?></label>
        <?= captcha_field($form) ?>
        <button class="btn btn--primary btn--lg" type="submit"><span>Gönder</span><?= icon('send', 18) ?></button>
      </form>
    </div>
  </div>
</section>
<?php if ($map !== '' && preg_match('#^https://(www\.)?google\.[a-z.]+/maps/embed\?#i', $map)): ?>
<section class="map" data-reveal>
  <iframe src="<?= e($map) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Harita" allowfullscreen></iframe>
</section>
<?php endif; ?>
