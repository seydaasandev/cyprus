<?php
/** İç sayfaların altındaki çağrı bandı (anasayfadaki "cta" bölümünün metinlerini kullanır). */
$cta = row("SELECT * FROM sections WHERE skey = 'cta' AND active = 1");
if (!$cta) {
    return;
}
?>
<section class="cta">
  <div class="cta__bg" data-parallax="0.2"<?= $cta['image'] !== '' ? ' style="background-image:url(\'' . e(thumb($cta['image'], 1920)) . '\')"' : '' ?>></div>
  <div class="cta__shade"></div>
  <div class="container cta__in">
    <?php if ($cta['kicker'] !== ''): ?><span class="kicker kicker--light" data-reveal><?= kicker_html($cta['kicker']) ?></span><?php endif; ?>
    <h2 data-reveal data-delay="1"><?= title_html($cta['title']) ?></h2>
    <?php if (trim((string) $cta['body']) !== ''): ?><p data-reveal data-delay="2"><?= nl2br(e(strip_tags((string) $cta['body']))) ?></p><?php endif; ?>
    <div class="cta__btns" data-reveal data-delay="3">
      <?php if ($cta['btn_text'] !== ''): ?><a class="btn btn--accent btn--lg" href="<?= e(url($cta['btn_url'])) ?>"><span><?= e($cta['btn_text']) ?></span><?= icon('arrow-right', 18) ?></a><?php endif; ?>
      <?php if (setting('phone') !== ''): ?><a class="btn btn--glass btn--lg" href="tel:<?= e(preg_replace('/[^0-9+]/', '', setting('phone'))) ?>"><?= icon('phone', 18) ?><span><?= e(setting('phone')) ?></span></a><?php endif; ?>
    </div>
  </div>
</section>
