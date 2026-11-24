<?php
/** Serbest içerikli sayfa (Hakkımızda, sözleşmeler vb.). Hakkımızda sayfasında değer/garanti kutuları da gösterilir. */
$isAbout = $page['slug'] === setting('about_slug', 'hakkimizda');
?>
<?= page_hero($page['title'], $page['subtitle'] ?: opt('site_name'), $page['cover'], [[$page['title'], '']]) ?>
<section class="sec sec--light">
  <div class="container <?= $page['image'] !== '' ? 'about' : 'narrow' ?>">
    <?php if ($page['image'] !== ''): ?>
    <div class="about__visual" data-reveal="left">
      <div class="intro__img"><img src="<?= e(thumb($page['image'], 900)) ?>" alt="<?= e($page['title']) ?>" data-parallax-img></div>
      <span class="intro__ring" aria-hidden="true"></span>
    </div>
    <?php endif; ?>
    <div class="prose prose--article" data-prose-reveal><?= content_html($page['content']) ?></div>
  </div>
</section>
<?php if ($isAbout):
    $guarantees = rows("SELECT * FROM items WHERE grp = 'guarantee' AND active = 1 ORDER BY sort, id");
    $values = rows("SELECT * FROM items WHERE grp = 'value' AND active = 1 ORDER BY sort, id");
    $offers = rows("SELECT * FROM items WHERE grp = 'offer' AND active = 1 ORDER BY sort, id");
    $counters = rows("SELECT * FROM items WHERE grp = 'counter' AND active = 1 ORDER BY sort, id");
?>
<?php if ($counters): ?>
<section class="sec sec--brand counters">
  <div class="container">
    <div class="counters__grid">
      <?php foreach ($counters as $i => $it): ?>
      <div class="counter" data-reveal data-delay="<?= $i % 4 ?>"><span class="counter__icon"><?= icon($it['icon'] ?: 'award', 28) ?></span><strong><span data-count="<?= e($it['val']) ?>"><?= e($it['val']) ?></span></strong><span class="counter__label"><?= e($it['title']) ?></span></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
<?php if ($offers): ?>
<section class="sec sec--soft">
  <div class="container">
    <?= section_head(opt('site_name'), setting('offers_title', 'Hizmetlerimiz'), '', 'center') ?>
    <div class="offer-grid">
      <?php foreach ($offers as $i => $o): ?>
      <div class="offer" data-reveal data-delay="<?= $i % 4 ?>"><span><?= icon($o['icon'] ?: 'check', 20) ?></span><?= e($o['title']) ?></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
<?php if ($guarantees || $values): ?>
<section class="sec sec--light why">
  <div class="container why__grid">
    <div class="why__lead">
      <?= section_head(opt('site_name'), setting('guarantee_title', 'Garanti ediyoruz'), '', 'left') ?>
      <?php if ($values): ?>
      <div class="why__values" data-reveal data-delay="2">
        <h3><?= e(setting('values_title', 'Öz Değerlerimiz')) ?></h3>
        <ul class="chips"><?php foreach ($values as $v): ?><li><?= icon($v['icon'] ?: 'check', 15) ?><?= e($v['title']) ?></li><?php endforeach; ?></ul>
      </div>
      <?php endif; ?>
    </div>
    <div class="why__cards">
      <?php foreach ($guarantees as $i => $g): ?>
      <div class="why-card" data-reveal data-delay="<?= $i % 2 ?>"><span class="why-card__icon"><?= icon($g['icon'] ?: 'shield', 26) ?></span><h3><?= e($g['title']) ?></h3><?php if (trim((string) $g['body']) !== ''): ?><p><?= e($g['body']) ?></p><?php endif; ?></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
<?php require __DIR__ . '/_cta.php'; ?>
<?php endif; ?>
