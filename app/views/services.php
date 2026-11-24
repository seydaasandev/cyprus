<?php /** Hizmetler listesi. */ ?>
<?= page_hero(setting('services_title', 'Hizmetlerimiz'), setting('services_kicker', 'Cyprus Touristic'), setting('services_image'), [[setting('services_title', 'Hizmetlerimiz'), '']], setting('services_text')) ?>
<section class="sec sec--light">
  <div class="container">
    <?php foreach ($services as $i => $sv): ?>
    <article class="split<?= $i % 2 ? ' split--rev' : '' ?>">
      <div class="split__media" data-reveal="<?= $i % 2 ? 'right' : 'left' ?>">
        <?php if ($sv['image'] !== ''): ?><img src="<?= e(thumb($sv['image'], 900)) ?>" alt="<?= e($sv['title']) ?>" loading="lazy"><?php endif; ?>
        <span class="split__no"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
      </div>
      <div class="split__body">
        <span class="service-card__icon" data-reveal><?= icon($sv['icon'], 26) ?></span>
        <?= section_head($sv['kicker'], $sv['title'], '', 'left') ?>
        <p data-reveal data-delay="2"><?= e(excerpt($sv['summary'] ?: $sv['content'], 420)) ?></p>
        <a class="btn btn--primary" href="<?= e(url($sv['slug'])) ?>" data-reveal data-delay="3"><span><?= e(setting('label_details', 'Detaylar')) ?></span><?= icon('arrow-right', 17) ?></a>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
</section>
<?php require __DIR__ . '/_cta.php'; ?>
