<?php /** Tek bir factsheet: sayfa içi PDF görüntüleyici + indirme. */ ?>
<?= page_hero($sheet['title'], setting('factsheets_title', 'Hotel Factsheets'), setting('factsheets_image'), [[setting('factsheets_title', 'Hotel Factsheets'), 'hotel-factsheets'], [$sheet['title'], '']]) ?>
<section class="sec sec--light">
  <div class="container">
    <div class="sheet-bar" data-reveal>
      <div>
        <h2><?= e($sheet['title']) ?></h2>
        <?php if (!empty($sheet['hotel'])): ?><a class="link-arrow" href="<?= e(url('oteller/' . $sheet['hotel_slug'])) ?>"><span><?= e($sheet['hotel']) ?></span><?= icon('arrow-right', 15) ?></a><?php endif; ?>
      </div>
      <?php if ($sheet['file'] !== ''): ?>
      <div class="sheet-bar__btns">
        <a class="btn btn--outline" href="<?= e(media($sheet['file'])) ?>" target="_blank" rel="noopener"><?= icon('eye', 17) ?><span><?= e(setting('label_open', 'Yeni sekmede aç')) ?></span></a>
        <a class="btn btn--primary" href="<?= e(media($sheet['file'])) ?>" download><?= icon('download', 17) ?><span><?= e(setting('label_download', 'İndir')) ?><?= ($sz = file_size_label($sheet['file'])) ? ' (' . e($sz) . ')' : '' ?></span></a>
      </div>
      <?php endif; ?>
    </div>
    <?php if ($sheet['file'] !== ''): ?>
    <div class="pdf-frame" data-reveal>
      <object data="<?= e(media($sheet['file'])) ?>#view=FitH" type="application/pdf" aria-label="<?= e($sheet['title']) ?>">
        <p class="empty">PDF tarayıcınızda görüntülenemiyor. <a href="<?= e(media($sheet['file'])) ?>" target="_blank" rel="noopener">Belgeyi açmak için tıklayın.</a></p>
      </object>
    </div>
    <?php endif; ?>
    <?php if ($others): ?>
    <div class="sheet-others" data-reveal>
      <?php foreach ($others as $o): ?><a class="chip-link" href="<?= e(url($o['slug'])) ?>"><?= icon('file', 16) ?><?= e($o['title']) ?></a><?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
