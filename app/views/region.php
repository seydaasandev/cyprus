<?php /** Bölge detay sayfası (zengin içerik + o bölgedeki oteller). */ ?>
<?= page_hero($region['title'], setting('regions_title', 'Kuzey Kıbrıs'), $region['cover'] ?: $region['image'], [[setting('regions_title', 'Kuzey Kıbrıs'), 'kuzey-kibris'], [$region['title'], '']]) ?>
<section class="sec sec--light">
  <div class="container detail detail--wide">
    <div class="detail__main">
      <div class="prose prose--article" data-prose-reveal><?= content_html($region['content']) ?></div>
    </div>
    <aside class="detail__side">
      <div class="side-card" data-reveal="right">
        <h2><?= e(setting('regions_title', 'Kuzey Kıbrıs')) ?></h2>
        <ul class="side-nav">
          <li class="is-active"><a href="<?= e(url($region['slug'])) ?>"><?= icon('map-pin', 18) ?><span><?= e($region['title']) ?></span><?= icon('chevron-right', 15) ?></a></li>
          <?php foreach ($others as $o): ?>
          <li><a href="<?= e(url($o['slug'])) ?>"><?= icon('map-pin', 18) ?><span><?= e($o['title']) ?></span><?= icon('chevron-right', 15) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="side-card side-card--brand" data-reveal="right" data-delay="1">
        <h2><?= e(setting('request_title', 'Talep Formu')) ?></h2>
        <p><?= e(setting('request_text')) ?></p>
        <a class="btn btn--accent btn--block" href="<?= e(url('talep-formu')) ?>"><span><?= e(setting('label_request', 'Talep Gönder')) ?></span><?= icon('send', 17) ?></a>
      </div>
    </aside>
  </div>
</section>
<?php if ($hotels): ?>
<section class="sec sec--soft">
  <div class="container">
    <?= section_head($region['title'], setting('hotels_title', 'Rezervasyon Yap'), '', 'center') ?>
    <div class="grid grid--3"><?php foreach ($hotels as $i => $h) { echo hotel_card($h, $i); } ?></div>
  </div>
</section>
<?php endif; ?>
