<?php /** Hizmet detay sayfası. */ ?>
<?= page_hero($service['title'], $service['kicker'] ?: setting('services_title', 'Hizmetlerimiz'), $service['image'], [[setting('services_title', 'Hizmetlerimiz'), 'hizmetlerimiz'], [$service['title'], '']]) ?>
<section class="sec sec--light">
  <div class="container detail">
    <div class="detail__main">
      <?php if ($service['image'] !== ''): ?>
      <figure class="detail__figure" data-reveal><img src="<?= e(thumb($service['image'], 1200)) ?>" alt="<?= e($service['title']) ?>"></figure>
      <?php endif; ?>
      <div class="prose" data-reveal><?= content_html($service['content']) ?></div>
    </div>
    <aside class="detail__side">
      <div class="side-card" data-reveal="right">
        <h2><?= e(setting('services_title', 'Hizmetlerimiz')) ?></h2>
        <ul class="side-nav">
          <li class="is-active"><a href="<?= e(url($service['slug'])) ?>"><?= icon($service['icon'], 18) ?><span><?= e($service['title']) ?></span><?= icon('chevron-right', 15) ?></a></li>
          <?php foreach ($others as $o): ?>
          <li><a href="<?= e(url($o['slug'])) ?>"><?= icon($o['icon'], 18) ?><span><?= e($o['title']) ?></span><?= icon('chevron-right', 15) ?></a></li>
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
