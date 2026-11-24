<?php
/** Alt şablon: alt bilgi, yüzen düğmeler ve betikler. */
$siteName = opt('site_name');
$logoLight = setting('logo_light');
$footerLogo = $logoLight !== '' ? $logoLight : setting('logo');
$socials = $GLOBALS['site_socials'] ?? [];
$footerMenu = menu_tree('footer');
$footerMenu2 = menu_tree('footer2');
$phone = setting('phone');
$email = setting('email');
$whatsapp = preg_replace('/[^0-9]/', '', setting('whatsapp'));
$year = date('Y');
?>
</main>

<footer class="site-footer">
  <div class="site-footer__wave" aria-hidden="true">
    <svg viewBox="0 0 1440 90" preserveAspectRatio="none"><path d="M0 50c180 40 360 40 540 10s360-50 540-20 240 40 360 30v20H0z"/></svg>
  </div>
  <div class="container site-footer__grid">
    <div class="site-footer__brand" data-reveal>
      <?php if ($footerLogo !== ''): ?><img<?= $logoLight === '' ? ' class="on-plate"' : '' ?> src="<?= e(media($footerLogo)) ?>" alt="<?= e($siteName) ?>" loading="lazy"><?php else: ?><strong class="brand__text"><?= e($siteName) ?></strong><?php endif; ?>
      <?php if (setting('footer_about') !== ''): ?><p><?= nl2br(e(setting('footer_about'))) ?></p><?php endif; ?>
      <?php if ($socials): ?>
      <div class="social">
        <?php foreach ($socials as $net => $link): ?><a href="<?= e($link) ?>"<?= $net === 'mail' ? ' title="' . e(substr($link, 7)) . '" aria-label="' . e(substr($link, 7)) . '"' : ' target="_blank" rel="noopener" aria-label="' . e(ucfirst($net)) . '"' ?>><?= icon($net, 18) ?></a><?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <?php if ($footerMenu): ?>
    <nav class="site-footer__col" data-reveal aria-label="<?= e(setting('footer_menu_title', 'Kurumsal')) ?>">
      <h3><?= e(setting('footer_menu_title', 'Kurumsal')) ?></h3>
      <ul>
        <?php foreach ($footerMenu as $m): ?>
          <li><a href="<?= e($m['url'] === '#' ? '#' : url($m['url'])) ?>"<?= $m['new_tab'] ? ' target="_blank" rel="noopener"' : '' ?>><?= e($m['title']) ?></a></li>
          <?php foreach ($m['children'] as $c): ?><li class="is-child"><a href="<?= e(url($c['url'])) ?>"><?= e($c['title']) ?></a></li><?php endforeach; ?>
        <?php endforeach; ?>
      </ul>
    </nav>
    <?php endif; ?>

    <?php if ($footerMenu2): ?>
    <nav class="site-footer__col" data-reveal aria-label="<?= e(setting('footer_menu2_title', 'Keşfet')) ?>">
      <h3><?= e(setting('footer_menu2_title', 'Keşfet')) ?></h3>
      <ul>
        <?php foreach ($footerMenu2 as $m): ?>
          <li><a href="<?= e($m['url'] === '#' ? '#' : url($m['url'])) ?>"<?= $m['new_tab'] ? ' target="_blank" rel="noopener"' : '' ?>><?= e($m['title']) ?></a></li>
          <?php foreach ($m['children'] as $c): ?><li class="is-child"><a href="<?= e(url($c['url'])) ?>"><?= e($c['title']) ?></a></li><?php endforeach; ?>
        <?php endforeach; ?>
      </ul>
    </nav>
    <?php endif; ?>

    <div class="site-footer__col site-footer__contact" data-reveal>
      <h3><?= e(setting('footer_contact_title', 'İletişim')) ?></h3>
      <ul>
        <?php if (setting('address') !== ''): ?><li><?= icon('map-pin', 18) ?><span><b><?= e(setting('address_label', 'KKTC')) ?></b><?= nl2br(e(setting('address'))) ?></span></li><?php endif; ?>
        <?php if (setting('address2') !== ''): ?><li><?= icon('map-pin', 18) ?><span><b><?= e(setting('address2_label', 'Türkiye')) ?></b><?= nl2br(e(setting('address2'))) ?></span></li><?php endif; ?>
        <?php if ($phone !== ''): ?><li><?= icon('phone', 18) ?><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $phone)) ?>"><?= e($phone) ?></a></li><?php endif; ?>
        <?php if ($email !== ''): ?><li><?= icon('mail', 18) ?><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></li><?php endif; ?>
      </ul>
    </div>
  </div>
  <div class="site-footer__bottom">
    <div class="container">
      <p><?= str_replace('{yil}', $year, e(setting('copyright', 'Tüm Hakları Saklıdır © {yil} Cyprus Touristic.'))) ?></p>
      <?php if (setting('footer_credit') !== ''): ?><p class="site-footer__credit"><?= strip_tags(setting('footer_credit'), '<a>') ?></p><?php endif; ?>
    </div>
  </div>
</footer>

<?php if ($whatsapp !== '' && opt('float_whatsapp') === '1'): ?>
<a class="float-wa" href="https://wa.me/<?= e($whatsapp) ?>" target="_blank" rel="noopener" aria-label="WhatsApp ile yazın"><?= icon('whatsapp', 26) ?></a>
<?php endif; ?>
<button class="to-top" type="button" aria-label="Yukarı çık" data-to-top><?= icon('arrow-up', 20) ?></button>

<div class="lightbox" id="lightbox" aria-hidden="true">
  <button type="button" class="lightbox__close" aria-label="Kapat" data-lb-close><?= icon('close', 26) ?></button>
  <button type="button" class="lightbox__nav lightbox__prev" aria-label="Önceki" data-lb-prev><?= icon('arrow-left', 24) ?></button>
  <figure><img alt=""><figcaption></figcaption></figure>
  <button type="button" class="lightbox__nav lightbox__next" aria-label="Sonraki" data-lb-next><?= icon('arrow-right', 24) ?></button>
</div>

<?php if (opt('tr_enabled') === '1' && count(translate_langs()) > 1): ?>
<div id="google_translate_element" class="gt-host" aria-hidden="true"></div>
<script>window.CT_LANGS = <?= json_encode(array_keys(translate_langs())) ?>;</script>
<?php endif; ?>
<script>window.CT = {base: <?= json_encode(base_path()) ?>, autoplay: <?= max(3, min(30, (int) opt('hero_autoplay'))) ?>};</script>
<script src="<?= e(asset('js/site.js')) ?>" defer></script>
<?= setting('body_code') ?>
</body>
</html>
