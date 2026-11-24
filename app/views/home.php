<?php
/** Anasayfa: bölümler yönetim panelindeki sıraya ve görünürlüğe göre basılır. */
$renderers = [
    'hero' => 'home_hero',
    'intro' => 'home_intro',
    'marquee' => 'home_marquee',
    'hotels' => 'home_hotels',
    'services' => 'home_services',
    'regions' => 'home_regions',
    'counters' => 'home_counters',
    'why' => 'home_why',
    'factsheets' => 'home_factsheets',
    'cta' => 'home_cta',
    'contact' => 'home_contact',
];

function home_btn(array $s, string $class = 'btn btn--primary'): string
{
    if ($s['btn_text'] === '') {
        return '';
    }
    return '<a class="' . $class . '" href="' . e(url($s['btn_url'])) . '"><span>' . e($s['btn_text']) . '</span>' . icon('arrow-right', 17) . '</a>';
}

function home_hero(array $s, array $slides): void
{
    if (!$slides) {
        return;
    }
    ?>
    <section class="hero" data-hero aria-roledescription="carousel" aria-label="<?= e($s['name']) ?>">
      <div class="hero__slides">
        <?php foreach ($slides as $i => $sl): ?>
        <div class="hero__slide hero__slide--<?= e($sl['align'] === 'center' ? 'center' : 'left') ?><?= $i === 0 ? ' is-active' : '' ?>" data-slide>
          <div class="hero__media">
            <?php if ($sl['kind'] === 'video' && $sl['media'] !== ''): ?>
              <video muted loop playsinline preload="<?= $i === 0 ? 'auto' : 'none' ?>"<?= $i === 0 ? ' autoplay' : '' ?><?= $sl['poster'] !== '' ? ' poster="' . e(thumb($sl['poster'], 1920)) . '"' : '' ?>>
                <source src="<?= e(media($sl['media'])) ?>" type="video/<?= str_ends_with(strtolower($sl['media']), '.webm') ? 'webm' : 'mp4' ?>">
              </video>
            <?php elseif ($sl['media'] !== ''): ?>
              <img src="<?= e(thumb($sl['media'], 1920)) ?>" alt="" <?= $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>>
            <?php endif; ?>
          </div>
          <div class="hero__shade" style="--shade:<?= max(0, min(90, (int) $sl['overlay'])) / 100 ?>"></div>
          <div class="container hero__content">
            <?php if ($sl['kicker'] !== ''): ?><span class="kicker kicker--light hero__kicker"><?= kicker_html($sl['kicker']) ?></span><?php endif; ?>
            <?php if ($sl['title'] !== ''): ?><<?= $i === 0 ? 'h1' : 'h2' ?> class="hero__title"><?= title_html($sl['title']) ?></<?= $i === 0 ? 'h1' : 'h2' ?>><?php endif; ?>
            <?php if (trim((string) $sl['body']) !== ''): ?><p class="hero__text"><?= nl2br(e($sl['body'])) ?></p><?php endif; ?>
            <?php if ($sl['btn1_text'] !== '' || $sl['btn2_text'] !== ''): ?>
            <div class="hero__btns">
              <?php if ($sl['btn1_text'] !== ''): ?><a class="btn btn--primary btn--lg" href="<?= e(url($sl['btn1_url'])) ?>"><span><?= e($sl['btn1_text']) ?></span><?= icon('arrow-right', 18) ?></a><?php endif; ?>
              <?php if ($sl['btn2_text'] !== ''): ?><a class="btn btn--glass btn--lg" href="<?= e(url($sl['btn2_url'])) ?>"><span><?= e($sl['btn2_text']) ?></span></a><?php endif; ?>
            </div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php if (count($slides) > 1): ?>
      <div class="hero__ui container">
        <div class="hero__dots" role="tablist">
          <?php foreach ($slides as $i => $sl): ?>
          <button type="button" role="tab" class="<?= $i === 0 ? 'is-active' : '' ?>" data-go="<?= $i ?>" aria-label="Slayt <?= $i + 1 ?>"><b><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></b><i><em></em></i></button>
          <?php endforeach; ?>
        </div>
        <div class="hero__arrows">
          <button type="button" data-prev aria-label="Önceki slayt"><?= icon('arrow-left', 20) ?></button>
          <button type="button" data-next aria-label="Sonraki slayt"><?= icon('arrow-right', 20) ?></button>
        </div>
      </div>
      <?php endif; ?>
      <a class="hero__scroll" href="#after-hero" aria-label="Aşağı kaydır"><span></span></a>
      <svg class="hero__wave" viewBox="0 0 1440 110" preserveAspectRatio="none" aria-hidden="true"><path d="M0 60c200 50 420 50 660 15S1180 5 1440 55v55H0z"/></svg>
    </section>
    <span id="after-hero"></span>
    <?php
}

function home_intro(array $s): void
{
    $badges = rows("SELECT * FROM items WHERE grp = 'counter' AND active = 1 ORDER BY sort, id LIMIT 2");
    ?>
    <section class="<?= sec_class($s) ?> intro">
      <div class="container intro__grid">
        <div class="intro__visual" data-reveal="left">
          <?php if ($s['image'] !== ''): ?>
          <div class="intro__img"><img src="<?= e(thumb($s['image'], 900)) ?>" alt="<?= e(strip_tags($s['title'])) ?>" loading="lazy" data-parallax-img></div>
          <?php endif; ?>
          <?php if (!empty($badges[0])): ?>
          <div class="intro__badge" data-float>
            <strong><span data-count="<?= e($badges[0]['val']) ?>"><?= e($badges[0]['val']) ?></span></strong>
            <span><?= e($badges[0]['title']) ?></span>
          </div>
          <?php endif; ?>
          <span class="intro__ring" aria-hidden="true"></span>
        </div>
        <div class="intro__text">
          <?= section_head($s['kicker'], $s['title'], '', 'left', is_dark_style($s)) ?>
          <div class="prose" data-reveal data-delay="2"><?= content_html($s['body']) ?></div>
          <div data-reveal data-delay="3"><?= home_btn($s) ?></div>
        </div>
      </div>
    </section>
    <?php
}

function home_marquee(array $s): void
{
    $names = array_column(rows('SELECT name FROM hotels WHERE active = 1 ORDER BY sort, id'), 'name');
    if (!$names) {
        return;
    }
    ?>
    <section class="marquee" aria-label="<?= e($s['name']) ?>">
      <div class="marquee__track">
        <?php for ($k = 0; $k < 2; $k++): ?>
        <div class="marquee__group"<?= $k ? ' aria-hidden="true"' : '' ?>>
          <?php foreach ($names as $n): ?><span><?= e($n) ?></span><i><?= icon('sun', 16) ?></i><?php endforeach; ?>
        </div>
        <?php endfor; ?>
      </div>
    </section>
    <?php
}

function home_hotels(array $s): void
{
    $hotels = rows('SELECT * FROM hotels WHERE active = 1 AND featured = 1 ORDER BY sort, id' . ((int) $s['lim'] > 0 ? ' LIMIT ' . (int) $s['lim'] : ''));
    if (!$hotels) {
        return;
    }
    $carousel = $s['layout'] === 'carousel';
    ?>
    <section class="<?= sec_class($s) ?> home-hotels">
      <div class="container">
        <div class="sec-top">
          <?= section_head($s['kicker'], $s['title'], $s['body'], 'left', is_dark_style($s)) ?>
          <?php if ($carousel): ?>
          <div class="car-nav" data-reveal>
            <button type="button" data-car-prev="#hotelCar" aria-label="Önceki"><?= icon('arrow-left', 20) ?></button>
            <button type="button" data-car-next="#hotelCar" aria-label="Sonraki"><?= icon('arrow-right', 20) ?></button>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php if ($carousel): ?>
      <div class="carousel" id="hotelCar" data-carousel>
        <div class="carousel__track">
          <?php foreach ($hotels as $i => $h): ?><div class="carousel__item"><?= hotel_card($h, $i) ?></div><?php endforeach; ?>
        </div>
      </div>
      <?php else: ?>
      <div class="container"><div class="grid grid--4"><?php foreach ($hotels as $i => $h) { echo hotel_card($h, $i); } ?></div></div>
      <?php endif; ?>
      <div class="container sec-foot" data-reveal><?= home_btn($s, 'btn btn--outline') ?></div>
    </section>
    <?php
}

function home_services(array $s): void
{
    $services = rows('SELECT * FROM services WHERE active = 1 ORDER BY sort, id' . ((int) $s['lim'] > 0 ? ' LIMIT ' . (int) $s['lim'] : ''));
    if (!$services) {
        return;
    }
    ?>
    <section class="<?= sec_class($s) ?> home-services">
      <div class="container">
        <?= section_head($s['kicker'], $s['title'], $s['body'], 'center', is_dark_style($s)) ?>
        <div class="grid grid--3 service-grid">
          <?php foreach ($services as $i => $sv): ?>
          <a class="service-card" href="<?= e(url($sv['slug'])) ?>" data-reveal data-delay="<?= $i % 3 ?>" data-tilt>
            <div class="service-card__media">
              <?php if ($sv['image'] !== ''): ?><img src="<?= e(thumb($sv['image'], 720)) ?>" alt="<?= e($sv['title']) ?>" loading="lazy"><?php endif; ?>
            </div>
            <div class="service-card__body">
              <span class="service-card__icon"><?= icon($sv['icon'], 26) ?></span>
              <span class="service-card__no"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
              <h3><?= e($sv['title']) ?></h3>
              <?php if (trim((string) $sv['summary']) !== ''): ?><p><?= e(excerpt($sv['summary'], 150)) ?></p><?php endif; ?>
              <span class="link-arrow"><span><?= e(setting('label_details', 'Detaylar')) ?></span><?= icon('arrow-right', 16) ?></span>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
        <div class="sec-foot" data-reveal><?= home_btn($s, 'btn btn--outline') ?></div>
      </div>
    </section>
    <?php
}

function home_regions(array $s): void
{
    $regions = rows('SELECT * FROM regions WHERE active = 1 ORDER BY sort, id' . ((int) $s['lim'] > 0 ? ' LIMIT ' . (int) $s['lim'] : ''));
    if (!$regions) {
        return;
    }
    ?>
    <section class="<?= sec_class($s) ?> home-regions">
      <div class="container">
        <?= section_head($s['kicker'], $s['title'], $s['body'], 'center', is_dark_style($s)) ?>
      </div>
      <div class="panels" data-panels data-reveal>
        <?php foreach ($regions as $i => $r): ?>
        <a class="panel<?= $i === 0 ? ' is-active' : '' ?>" href="<?= e(url($r['slug'])) ?>">
          <?php if ($r['image'] !== ''): ?><img src="<?= e(thumb($r['image'], 900)) ?>" alt="<?= e($r['title']) ?>" loading="lazy"><?php endif; ?>
          <span class="panel__shade"></span>
          <span class="panel__no"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
          <span class="panel__body">
            <small lang="en"><?= e(opt('site_name')) ?></small>
            <strong><?= e($r['title']) ?></strong>
            <?php if (trim((string) $r['summary']) !== ''): ?><em><?= e(excerpt($r['summary'], 120)) ?></em><?php endif; ?>
            <span class="link-arrow"><span><?= e(setting('label_details', 'Detaylar')) ?></span><?= icon('arrow-right', 16) ?></span>
          </span>
        </a>
        <?php endforeach; ?>
      </div>
      <div class="container sec-foot" data-reveal><?= home_btn($s, 'btn btn--outline') ?></div>
    </section>
    <?php
}

function home_counters(array $s): void
{
    $items = rows("SELECT * FROM items WHERE grp = 'counter' AND active = 1 ORDER BY sort, id");
    if (!$items) {
        return;
    }
    ?>
    <section class="<?= sec_class($s) ?> counters"<?= $s['image'] !== '' ? ' style="--bg:url(\'' . e(thumb($s['image'], 1920)) . '\')"' : '' ?>>
      <div class="container">
        <?php if ($s['title'] !== '' || $s['kicker'] !== ''): ?><?= section_head($s['kicker'], $s['title'], $s['body'], 'center', is_dark_style($s)) ?><?php endif; ?>
        <div class="counters__grid">
          <?php foreach ($items as $i => $it): ?>
          <div class="counter" data-reveal data-delay="<?= $i % 4 ?>">
            <span class="counter__icon"><?= icon($it['icon'] ?: 'award', 28) ?></span>
            <strong><span data-count="<?= e($it['val']) ?>"><?= e($it['val']) ?></span></strong>
            <span class="counter__label"><?= e($it['title']) ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php
}

function home_why(array $s): void
{
    $guarantees = rows("SELECT * FROM items WHERE grp = 'guarantee' AND active = 1 ORDER BY sort, id");
    $values = rows("SELECT * FROM items WHERE grp = 'value' AND active = 1 ORDER BY sort, id");
    if (!$guarantees && !$values) {
        return;
    }
    ?>
    <section class="<?= sec_class($s) ?> why">
      <div class="container why__grid">
        <div class="why__lead">
          <?= section_head($s['kicker'], $s['title'], $s['body'], 'left', is_dark_style($s)) ?>
          <?php if ($values): ?>
          <div class="why__values" data-reveal data-delay="2">
            <h3><?= e(setting('values_title', 'Öz Değerlerimiz')) ?></h3>
            <ul class="chips">
              <?php foreach ($values as $v): ?><li><?= icon($v['icon'] ?: 'check', 15) ?><?= e($v['title']) ?></li><?php endforeach; ?>
            </ul>
          </div>
          <?php endif; ?>
          <div data-reveal data-delay="3"><?= home_btn($s) ?></div>
        </div>
        <div class="why__cards">
          <?php foreach ($guarantees as $i => $g): ?>
          <div class="why-card" data-reveal data-delay="<?= $i % 2 ?>">
            <span class="why-card__icon"><?= icon($g['icon'] ?: 'shield', 26) ?></span>
            <h3><?= e($g['title']) ?></h3>
            <?php if (trim((string) $g['body']) !== ''): ?><p><?= e($g['body']) ?></p><?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php
}

function home_factsheets(array $s): void
{
    $sheets = rows('SELECT f.*, h.image AS hotel_image FROM factsheets f LEFT JOIN hotels h ON h.id = f.hotel_id WHERE f.active = 1 ORDER BY f.sort, f.id' . ((int) $s['lim'] > 0 ? ' LIMIT ' . (int) $s['lim'] : ''));
    if (!$sheets) {
        return;
    }
    ?>
    <section class="<?= sec_class($s) ?> home-sheets">
      <div class="container">
        <?= section_head($s['kicker'], $s['title'], $s['body'], 'center', is_dark_style($s)) ?>
        <div class="grid grid--3">
          <?php foreach ($sheets as $i => $f): $img = $f['image'] ?: $f['hotel_image']; ?>
          <a class="sheet-card" href="<?= e(url($f['slug'])) ?>" data-reveal data-delay="<?= $i % 3 ?>">
            <?php if ($img): ?><img src="<?= e(thumb($img, 720)) ?>" alt="" loading="lazy"><?php endif; ?>
            <span class="sheet-card__shade"></span>
            <span class="sheet-card__icon"><?= icon('file', 24) ?></span>
            <span class="sheet-card__body"><small>PDF · Factsheet</small><strong><?= e($f['title']) ?></strong></span>
            <span class="sheet-card__go"><?= icon('arrow-up-right', 20) ?></span>
          </a>
          <?php endforeach; ?>
        </div>
        <div class="sec-foot" data-reveal><?= home_btn($s, 'btn btn--outline') ?></div>
      </div>
    </section>
    <?php
}

function home_cta(array $s): void
{
    ?>
    <section class="cta">
      <div class="cta__bg" data-parallax="0.2"<?= $s['image'] !== '' ? ' style="background-image:url(\'' . e(thumb($s['image'], 1920)) . '\')"' : '' ?>></div>
      <div class="cta__shade"></div>
      <div class="container cta__in">
        <?php if ($s['kicker'] !== ''): ?><span class="kicker kicker--light" data-reveal><?= kicker_html($s['kicker']) ?></span><?php endif; ?>
        <h2 data-reveal data-delay="1"><?= title_html($s['title']) ?></h2>
        <?php if (trim((string) $s['body']) !== ''): ?><p data-reveal data-delay="2"><?= nl2br(e(strip_tags((string) $s['body']))) ?></p><?php endif; ?>
        <div class="cta__btns" data-reveal data-delay="3">
          <?= home_btn($s, 'btn btn--accent btn--lg') ?>
          <?php if (setting('phone') !== ''): ?><a class="btn btn--glass btn--lg" href="tel:<?= e(preg_replace('/[^0-9+]/', '', setting('phone'))) ?>"><?= icon('phone', 18) ?><span><?= e(setting('phone')) ?></span></a><?php endif; ?>
        </div>
      </div>
    </section>
    <?php
}

function home_contact(array $s): void
{
    ?>
    <section class="<?= sec_class($s) ?> home-contact">
      <div class="container">
        <?= section_head($s['kicker'], $s['title'], $s['body'], 'center', is_dark_style($s)) ?>
        <div class="grid grid--3 contact-cards">
          <?php if (setting('address') !== ''): ?>
          <div class="contact-card" data-reveal><span class="contact-card__icon"><?= icon('map-pin', 24) ?></span><h3><?= e(setting('address_label', 'KKTC')) ?></h3><p><?= nl2br(e(setting('address'))) ?></p></div>
          <?php endif; ?>
          <?php if (setting('address2') !== ''): ?>
          <div class="contact-card" data-reveal data-delay="1"><span class="contact-card__icon"><?= icon('map-pin', 24) ?></span><h3><?= e(setting('address2_label', 'Türkiye')) ?></h3><p><?= nl2br(e(setting('address2'))) ?></p></div>
          <?php endif; ?>
          <div class="contact-card" data-reveal data-delay="2"><span class="contact-card__icon"><?= icon('headset', 24) ?></span><h3><?= e(setting('contact_card_title', 'Bize Ulaşın')) ?></h3>
            <p>
              <?php if (setting('phone') !== ''): ?><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', setting('phone'))) ?>"><?= e(setting('phone')) ?></a><br><?php endif; ?>
              <?php if (setting('email') !== ''): ?><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a><?php endif; ?>
            </p>
          </div>
        </div>
        <div class="sec-foot" data-reveal><?= home_btn($s, 'btn btn--primary') ?></div>
      </div>
    </section>
    <?php
}

foreach ($sections as $s) {
    $fn = $renderers[$s['skey']] ?? null;
    if ($fn === null) {
        continue;
    }
    if ($s['skey'] === 'hero') {
        home_hero($s, $slides);
    } else {
        $fn($s);
    }
}
