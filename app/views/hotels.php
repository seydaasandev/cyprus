<?php /** Otel listesi (bölgeye göre süzme). */ ?>
<?= page_hero(setting('hotels_title', 'Oteller'), setting('hotels_kicker', 'Kuzey Kıbrıs'), setting('hotels_image'), [[setting('hotels_title', 'Oteller'), '']], setting('hotels_text')) ?>
<section class="sec sec--light">
  <div class="container">
    <?php if (count($regions) > 1): ?>
    <div class="filters" data-filters data-reveal>
      <button type="button" class="is-active" data-filter="*"><?= e(setting('label_all', 'Tümü')) ?></button>
      <?php foreach ($regions as $r): ?><button type="button" data-filter="<?= (int) $r['id'] ?>"><?= e($r['title']) ?></button><?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php if ($hotels): ?>
    <div class="grid grid--3" data-filter-grid>
      <?php foreach ($hotels as $i => $h) { echo hotel_card($h, $i); } ?>
    </div>
    <?php else: ?>
    <p class="empty">Henüz otel eklenmedi.</p>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/_cta.php'; ?>
