<?php /** Bulunamayan sayfa. */ ?>
<?= page_hero('Sayfa bulunamadı', '404', '', [['404', '']]) ?>
<section class="sec sec--light">
  <div class="container narrow center">
    <p class="lead" data-reveal>Aradığınız sayfa taşınmış ya da kaldırılmış olabilir.</p>
    <div class="cta__btns" data-reveal data-delay="1">
      <a class="btn btn--primary" href="<?= e(url()) ?>"><span>Anasayfaya dön</span><?= icon('arrow-right', 17) ?></a>
      <a class="btn btn--outline" href="<?= e(url('oteller')) ?>"><span><?= e(setting('hotels_title', 'Rezervasyon Yap')) ?></span></a>
    </div>
  </div>
</section>
