<?php /** Bölgeler listesi. */ ?>
<?= page_hero(setting('regions_title', 'Kuzey Kıbrıs'), setting('regions_kicker', 'Cyprus Touristic'), setting('regions_image'), [[setting('regions_title', 'Kuzey Kıbrıs'), '']], setting('regions_text')) ?>
<section class="sec sec--light">
  <div class="container">
    <div class="region-grid">
      <?php foreach ($regions as $i => $r): ?>
      <a class="region-card" href="<?= e(url($r['slug'])) ?>" data-reveal data-delay="<?= $i % 3 ?>">
        <?php if ($r['image'] !== ''): ?><img src="<?= e(thumb($r['image'], 900)) ?>" alt="<?= e($r['title']) ?>" loading="lazy"><?php endif; ?>
        <span class="panel__shade"></span>
        <span class="panel__no"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
        <span class="region-card__body">
          <strong><?= e($r['title']) ?></strong>
          <?php if (trim((string) $r['summary']) !== ''): ?><em><?= e(excerpt($r['summary'], 140)) ?></em><?php endif; ?>
          <span class="link-arrow"><span><?= e(setting('label_details', 'Detaylar')) ?></span><?= icon('arrow-right', 16) ?></span>
        </span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/_cta.php'; ?>
