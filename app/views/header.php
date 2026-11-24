<?php
/** Üst şablon: <head>, üst bilgi çubuğu, menü ve mobil çekmece. */
$siteName = opt('site_name');
$pageTitle = trim((string) ($meta['title'] ?? ''));
$fullTitle = $pageTitle !== '' ? $pageTitle . ' – ' . $siteName : setting('meta_title', $siteName . ' – ' . opt('tagline'));
$metaDesc = trim((string) ($meta['desc'] ?? '')) ?: setting('meta_desc');
$ogImage = !empty($meta['image']) ? $meta['image'] : setting('og_image', setting('logo'));
$logoDark = setting('logo');
$logoLight = setting('logo_light');
$hasLightLogo = $logoLight !== '';
$transparent = opt('header_transparent') === '1';
$headerMenu = menu_tree('header');
$socials = [];
foreach (['instagram', 'facebook', 'x-twitter', 'youtube', 'linkedin', 'tiktok', 'telegram'] as $net) {
    if (($link = setting('social_' . $net)) !== '') {
        $socials[$net] = $link;
    }
}
// Sosyal ikonların yanında gösterilen e-posta adresi (ör. rezervasyon adresi).
$socialMail = setting('social_mail');
if (filter_var($socialMail, FILTER_VALIDATE_EMAIL)) {
    $socials['mail'] = 'mailto:' . $socialMail;
}
$GLOBALS['site_socials'] = $socials;
$langs = opt('tr_enabled') === '1' ? translate_langs() : [];
$phone = setting('phone');
$email = setting('email');
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script>document.documentElement.className += ' js';</script>
<title><?= e($fullTitle) ?></title>
<?php if ($metaDesc !== ''): ?><meta name="description" content="<?= e(excerpt($metaDesc, 300)) ?>"><?php endif; ?>
<?php if (!empty($meta['noindex'])): ?><meta name="robots" content="noindex"><?php endif; ?>
<link rel="canonical" href="<?= e(abs_url(current_path())) ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e($siteName) ?>">
<meta property="og:title" content="<?= e($fullTitle) ?>">
<?php if ($metaDesc !== ''): ?><meta property="og:description" content="<?= e(excerpt($metaDesc, 300)) ?>"><?php endif; ?>
<meta property="og:url" content="<?= e(abs_url(current_path())) ?>">
<?php if ($ogImage !== ''): ?><meta property="og:image" content="<?= e(abs_url($ogImage)) ?>"><?php endif; ?>
<meta name="theme-color" content="<?= e(css_color(opt('c_primary'), '#00416A')) ?>">
<?php if (($fav = setting('favicon')) !== ''): ?><link rel="icon" href="<?= e(media($fav)) ?>"><?php endif; ?>
<?= theme_font_links() ?>
<?= theme_css() ?>
<link rel="stylesheet" href="<?= e(asset('css/site.css')) ?>">
<?= setting('head_code') ?>
</head>
<body class="<?= e(theme_body_class()) ?><?= $transparent ? ' hdr-over' : '' ?><?= $hasLightLogo ? '' : ' hdr-plate' ?>">
<a class="skip-link" href="#icerik">İçeriğe geç</a>
<?php if (opt('preloader') === '1'): ?>
<div class="preloader" id="preloader" aria-hidden="true">
  <div class="preloader__inner">
    <?php if ($logoDark !== ''): ?><img src="<?= e(media($logoDark)) ?>" alt="" width="220"><?php else: ?><strong><?= e($siteName) ?></strong><?php endif; ?>
    <span class="preloader__bar"><i></i></span>
  </div>
</div>
<?php endif; ?>
<div class="scroll-progress" aria-hidden="true"><i></i></div>

<header class="site-header" id="siteHeader">
  <?php if (opt('topbar') === '1'): ?>
  <div class="topbar">
    <div class="container topbar__in">
      <p class="topbar__text"><?= e(setting('topbar_text')) ?></p>
      <div class="topbar__right">
        <?php if ($phone !== ''): ?><a class="topbar__link" href="tel:<?= e(preg_replace('/[^0-9+]/', '', $phone)) ?>"><?= icon('phone', 15) ?><span><?= e($phone) ?></span></a><?php endif; ?>
        <?php if ($email !== ''): ?><a class="topbar__link topbar__mail" href="mailto:<?= e($email) ?>"><?= icon('mail', 15) ?><span><?= e($email) ?></span></a><?php endif; ?>
        <?php if ($socials): ?>
        <span class="topbar__social">
          <?php foreach ($socials as $net => $link): ?><a href="<?= e($link) ?>"<?= $net === 'mail' ? ' title="' . e($socialMail) . '" aria-label="' . e($socialMail) . '"' : ' target="_blank" rel="noopener" aria-label="' . e(ucfirst($net)) . '"' ?>><?= icon($net, 15) ?></a><?php endforeach; ?>
        </span>
        <?php endif; ?>
        <?php if (count($langs) > 1): ?>
        <div class="lang notranslate" data-lang>
          <button type="button" class="lang__btn" aria-haspopup="true" aria-expanded="false"><?= icon('globe', 15) ?><span data-lang-current>TR</span><?= icon('chevron-down', 13) ?></button>
          <ul class="lang__list">
            <?php foreach ($langs as $code => $label): ?><li><button type="button" data-lang-set="<?= e($code) ?>"><?= e($label) ?></button></li><?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>
        <?php if (setting('hbtn1_text') !== ''): ?><a class="topbar__btn topbar__btn--fill" href="<?= e(url(setting('hbtn1_url'))) ?>"><?= icon('send', 14) ?><?= e(setting('hbtn1_text')) ?></a><?php endif; ?>
        <?php if (setting('hbtn2_text') !== ''): ?><a class="topbar__btn" href="<?= e(url(setting('hbtn2_url'))) ?>"<?= preg_match('#^https?://#', setting('hbtn2_url')) ? ' target="_blank" rel="noopener"' : '' ?>><?= icon('lock', 14) ?><?= e(setting('hbtn2_text')) ?></a><?php endif; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>
  <div class="navbar">
    <div class="container navbar__in">
      <a class="brand" href="<?= e(url()) ?>" aria-label="<?= e($siteName) ?>">
        <?php if ($logoDark !== ''): ?>
          <img class="brand__dark" src="<?= e(media($logoDark)) ?>" alt="<?= e($siteName) ?>">
          <?php if ($hasLightLogo): ?><img class="brand__light" src="<?= e(media($logoLight)) ?>" alt="" aria-hidden="true"><?php endif; ?>
        <?php else: ?><span class="brand__text"><?= e($siteName) ?></span><?php endif; ?>
      </a>
      <nav class="nav" aria-label="Ana menü">
        <ul class="nav__list">
          <?php foreach ($headerMenu as $m): $has = !empty($m['children']); ?>
          <li class="nav__item<?= $has ? ' has-sub' : '' ?><?= is_current(url($m['url'])) && $m['url'] !== '' && $m['url'] !== '#' ? ' is-active' : '' ?>">
            <a href="<?= e($m['url'] === '#' ? '#' : url($m['url'])) ?>"<?= $m['new_tab'] ? ' target="_blank" rel="noopener"' : '' ?><?= $has ? ' aria-haspopup="true"' : '' ?>><span><?= e($m['title']) ?></span><?= $has ? icon('chevron-down', 14) : '' ?></a>
            <?php if ($has): ?>
            <ul class="nav__sub">
              <?php foreach ($m['children'] as $c): ?><li><a href="<?= e(url($c['url'])) ?>"<?= $c['new_tab'] ? ' target="_blank" rel="noopener"' : '' ?>><?= e($c['title']) ?></a></li><?php endforeach; ?>
            </ul>
            <?php endif; ?>
          </li>
          <?php endforeach; ?>
        </ul>
      </nav>
      <div class="navbar__actions">
        <?php if (setting('hbtn1_text') !== '' && opt('topbar') !== '1'): ?><a class="btn btn--primary btn--sm navbar__cta" href="<?= e(url(setting('hbtn1_url'))) ?>"><span><?= e(setting('hbtn1_text')) ?></span><?= icon('arrow-up-right', 16) ?></a><?php endif; ?>
        <button class="burger" type="button" aria-label="Menüyü aç" aria-controls="drawer" aria-expanded="false" data-drawer-open><i></i><i></i><i></i></button>
      </div>
    </div>
  </div>
</header>

<div class="drawer" id="drawer" aria-hidden="true">
  <div class="drawer__backdrop" data-drawer-close></div>
  <aside class="drawer__panel" role="dialog" aria-label="Menü">
    <div class="drawer__head">
      <?php if ($logoDark !== ''): ?><img src="<?= e(media($logoDark)) ?>" alt="<?= e($siteName) ?>"><?php else: ?><strong><?= e($siteName) ?></strong><?php endif; ?>
      <button type="button" class="drawer__close" aria-label="Menüyü kapat" data-drawer-close><?= icon('close', 22) ?></button>
    </div>
    <ul class="drawer__nav">
      <?php foreach ($headerMenu as $m): $has = !empty($m['children']); ?>
      <li<?= $has ? ' class="has-sub"' : '' ?>>
        <?php if ($has): ?>
          <button type="button" data-acc><?= e($m['title']) ?><?= icon('chevron-down', 18) ?></button>
          <ul>
            <?php foreach ($m['children'] as $c): ?><li><a href="<?= e(url($c['url'])) ?>"><?= e($c['title']) ?></a></li><?php endforeach; ?>
          </ul>
        <?php else: ?>
          <a href="<?= e(url($m['url'])) ?>"><?= e($m['title']) ?></a>
        <?php endif; ?>
      </li>
      <?php endforeach; ?>
    </ul>
    <div class="drawer__foot">
      <?php if (setting('hbtn1_text') !== ''): ?><a class="btn btn--primary" href="<?= e(url(setting('hbtn1_url'))) ?>"><span><?= e(setting('hbtn1_text')) ?></span><?= icon('arrow-up-right', 16) ?></a><?php endif; ?>
      <?php if (setting('hbtn2_text') !== ''): ?><a class="btn btn--ghost" href="<?= e(url(setting('hbtn2_url'))) ?>"><?= icon('lock', 16) ?><span><?= e(setting('hbtn2_text')) ?></span></a><?php endif; ?>
      <?php if (count($langs) > 1): ?>
      <div class="drawer__langs notranslate">
        <?php foreach ($langs as $code => $label): ?><button type="button" data-lang-set="<?= e($code) ?>"><?= e(strtoupper($code === 'iw' ? 'he' : $code)) ?></button><?php endforeach; ?>
      </div>
      <?php endif; ?>
      <?php if ($phone !== ''): ?><a class="drawer__contact" href="tel:<?= e(preg_replace('/[^0-9+]/', '', $phone)) ?>"><?= icon('phone', 16) ?><?= e($phone) ?></a><?php endif; ?>
      <?php if ($email !== ''): ?><a class="drawer__contact" href="mailto:<?= e($email) ?>"><?= icon('mail', 16) ?><?= e($email) ?></a><?php endif; ?>
    </div>
  </aside>
</div>

<main id="icerik">
