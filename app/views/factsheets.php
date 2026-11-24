<?php /** Hotel Factsheets listesi (PDF belgeler). */ ?>
<?= page_hero(setting('factsheets_title', 'Hotel Factsheets'), setting('factsheets_kicker', 'Cyprus Touristic'), setting('factsheets_image'), [[setting('factsheets_title', 'Hotel Factsheets'), '']], setting('factsheets_text')) ?>
<section class="sec sec--light">
  <div class="container">
    <?php if ($sheets): ?>
    <div class="grid grid--3">
      <?php foreach ($sheets as $i => $f): $img = $f['image'] ?: $f['hotel_image']; ?>
      <a class="sheet-card" href="<?= e(url($f['slug'])) ?>" data-reveal data-delay="<?= $i % 3 ?>">
        <?php if ($img): ?><img src="<?= e(thumb($img, 720)) ?>" alt="" loading="lazy"><?php endif; ?>
        <span class="sheet-card__shade"></span>
        <span class="sheet-card__icon"><?= icon('file', 24) ?></span>
        <span class="sheet-card__body"><small>PDF<?= ($sz = file_size_label($f['file'])) ? ' · ' . e($sz) : '' ?></small><strong><?= e($f['title']) ?></strong></span>
        <span class="sheet-card__go"><?= icon('arrow-up-right', 20) ?></span>
      </a>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p class="empty">Henüz belge eklenmedi.</p>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/_cta.php'; ?>
