<?php
/** Otel detay sayfası. Satış yok: bilgi + talep formu yönlendirmesi. */
$features = lines($hotel['features']);
// Ana görsel + galeri; galeri fotoğraflarının başlığı (oda adı) ışık kutusunda ve küçük resimde gösterilir.
$shots = array_merge(
    $hotel['image'] !== '' ? [['image' => $hotel['image'], 'caption' => '']] : [],
    array_values(array_filter($gallery, static fn(array $g): bool => $g['image'] !== ''))
);
$requestUrl = url('talep-formu') . '?otel=' . rawurlencode($hotel['name']);
$reserveUrl = url('rezervasyon') . '?otel=' . rawurlencode($hotel['slug']);
$bookLabel = setting('label_book', 'Rezervasyon Yap');
$canReserve = reservations_enabled();
?>
<?= page_hero($hotel['name'], $hotel['concept'] ?: setting('hotels_title', 'Oteller'), $hotel['image'], [[setting('hotels_title', 'Oteller'), 'oteller'], [$hotel['name'], '']], $hotel['location']) ?>
<section class="sec sec--light hotel">
  <div class="container hotel__grid">
    <div class="hotel__main">
      <?php if ($shots): ?>
      <div class="gallery" data-gallery data-reveal>
        <a class="gallery__main" href="<?= e(media($shots[0]['image'])) ?>" data-lb data-caption="<?= e($shots[0]['caption'] ?: $hotel['name']) ?>"><img src="<?= e(thumb($shots[0]['image'], 1200)) ?>" alt="<?= e($shots[0]['caption'] ?: $hotel['name']) ?>"></a>
        <?php if (count($shots) > 1): ?>
        <div class="gallery__thumbs">
          <?php foreach (array_slice($shots, 1) as $shot): $cap = (string) ($shot['caption'] ?? ''); ?>
          <a href="<?= e(media($shot['image'])) ?>" data-lb data-caption="<?= e($cap !== '' ? $cap : $hotel['name']) ?>"<?= $cap !== '' ? ' title="' . e($cap) . '"' : '' ?>><img src="<?= e(thumb($shot['image'], 400)) ?>" alt="<?= e($cap !== '' ? $hotel['name'] . ' – ' . $cap : $hotel['name']) ?>" loading="lazy"><?php if ($cap !== ''): ?><span class="gallery__cap"><?= e($cap) ?></span><?php endif; ?></a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <?php if (trim((string) $hotel['summary']) !== ''): ?>
      <div class="hotel__facts" data-reveal>
        <?php foreach (lines($hotel['summary']) as $line): ?>
          <?php if (preg_match('/^([^:]{2,60}):\s*(.+)$/u', $line, $m)): ?>
          <div class="fact"><span class="fact__icon"><?= icon('check-circle', 20) ?></span><div><h3><?= e($m[1]) ?></h3><p><?= e($m[2]) ?></p></div></div>
          <?php else: ?>
          <p class="hotel__lead"><?= e($line) ?></p>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if ($rooms): ?>
      <div class="rooms" id="odalar" data-reveal>
        <h2><?= e(setting('label_rooms', 'Oda Seçenekleri')) ?></h2>
        <ul class="rooms__list">
          <?php foreach ($rooms as $room): ?>
          <li class="room">
            <span class="room__icon"><?= icon('bed', 22) ?></span>
            <div class="room__info">
              <h3><?= e($room['name']) ?></h3>
              <?php if ($room['details'] !== ''): ?><p><?= e($room['details']) ?></p><?php endif; ?>
            </div>
            <?php if ($room['price'] !== '' || $room['note'] !== ''): ?>
            <div class="room__price">
              <?php if ($room['price'] !== ''): ?><strong><?= e($room['price']) ?></strong><?php endif; ?>
              <?php if ($room['note'] !== ''): ?><small><?= e($room['note']) ?></small><?php endif; ?>
            </div>
            <?php endif; ?>
            <?php if ($canReserve): ?><a class="btn btn--primary btn--sm" href="<?= e($reserveUrl . '&oda=' . (int) $room['id']) ?>"><span><?= e($bookLabel) ?></span><?= icon('arrow-right', 16) ?></a><?php endif; ?>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php if (setting('rooms_note') !== ''): ?><p class="rooms__note"><?= nl2br(e(setting('rooms_note'))) ?></p><?php endif; ?>
      </div>
      <?php endif; ?>

      <?php if (trim(strip_tags((string) $hotel['description'])) !== ''): ?>
      <div class="prose" data-reveal><?= content_html($hotel['description']) ?></div>
      <?php endif; ?>

      <?php if ($features): ?>
      <div class="hotel__features" data-reveal>
        <h2><?= e(setting('label_features', 'Öne Çıkan Özellikler')) ?></h2>
        <ul class="chips chips--lg"><?php foreach ($features as $f): ?><li><?= icon('check', 15) ?><?= e($f) ?></li><?php endforeach; ?></ul>
      </div>
      <?php endif; ?>
    </div>

    <aside class="hotel__side">
      <div class="side-card" data-reveal="right">
        <?= stars((int) $hotel['stars']) ?>
        <h2><?= e($hotel['name']) ?></h2>
        <ul class="side-list">
          <?php if ($hotel['location'] !== ''): ?><li><?= icon('map-pin', 18) ?><span><small><?= e(setting('label_location', 'Konum')) ?></small><?= e($hotel['location']) ?></span></li><?php endif; ?>
          <?php if ($hotel['concept'] !== ''): ?><li><?= icon('utensils', 18) ?><span><small><?= e(setting('label_concept', 'Konsept')) ?></small><?= e($hotel['concept']) ?></span></li><?php endif; ?>
          <?php if (!empty($hotel['region'])): ?><li><?= icon('compass', 18) ?><span><small><?= e(setting('label_region', 'Bölge')) ?></small><a href="<?= e(url($hotel['region_slug'])) ?>"><?= e($hotel['region']) ?></a></span></li><?php endif; ?>
        </ul>
        <?php if ($canReserve): ?><a class="btn btn--primary btn--block" href="<?= e($rooms ? '#odalar' : $reserveUrl) ?>"><span><?= e($bookLabel) ?></span><?= icon('calendar', 17) ?></a><?php endif; ?>
        <a class="btn btn--outline btn--block" href="<?= e($requestUrl) ?>"><?= icon('send', 17) ?><span><?= e(setting('label_request', 'Talep Gönder')) ?></span></a>
        <?php foreach ($sheets as $sh): ?>
        <a class="btn btn--outline btn--block" href="<?= e(url($sh['slug'])) ?>"><?= icon('file', 17) ?><span><?= e($sh['title']) ?></span></a>
        <?php endforeach; ?>
        <?php if ($hotel['factsheet'] !== ''): ?>
        <a class="btn btn--outline btn--block" href="<?= e(media($hotel['factsheet'])) ?>" target="_blank" rel="noopener"><?= icon('download', 17) ?><span>Factsheet (PDF)</span></a>
        <?php endif; ?>
        <?php if ($hotel['website'] !== '' && preg_match('#^https?://#i', $hotel['website'])): ?>
        <a class="link-arrow" href="<?= e($hotel['website']) ?>" target="_blank" rel="noopener nofollow"><span><?= e(setting('label_website', 'Otel web sitesi')) ?></span><?= icon('external', 15) ?></a>
        <?php endif; ?>
        <?php if (setting('phone') !== ''): ?>
        <p class="side-card__call"><?= icon('headset', 18) ?><span><?= e(setting('label_call', 'Bilgi için')) ?> <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', setting('phone'))) ?>"><?= e(setting('phone')) ?></a></span></p>
        <?php endif; ?>
      </div>
    </aside>
  </div>
</section>

<?php if ($others): ?>
<section class="sec sec--soft">
  <div class="container">
    <?= section_head(setting('hotels_kicker', 'Kuzey Kıbrıs'), setting('label_other_hotels', 'Diğer Oteller'), '', 'center') ?>
    <div class="grid grid--3"><?php foreach ($others as $i => $h) { echo hotel_card($h, $i); } ?></div>
  </div>
</section>
<?php endif; ?>
