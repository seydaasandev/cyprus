<?php
/**
 * Görünümlerde tekrar kullanılan parçalar (sayfa başlığı, kartlar, bölüm başlıkları).
 */
declare(strict_types=1);

/**
 * Küçük üst başlık metni. Büyük harfe çevrilirken marka adının Türkçe kurala göre
 * "TOURİSTİC" olmaması için site adı İngilizce olarak işaretlenir.
 */
function kicker_html(?string $text): string
{
    $out = e($text);
    $brand = e(opt('site_name'));
    return $brand !== '' ? str_replace($brand, '<span lang="en">' . $brand . '</span>', $out) : $out;
}

/** İç sayfaların üst görsel bandı. $crumbs: [[başlık, adres], ...] */
function page_hero(string $title, string $kicker = '', ?string $image = '', array $crumbs = [], string $text = ''): string
{
    $image = trim((string) $image) ?: setting('page_hero_image');
    $h = '<section class="page-hero">';
    if ($image !== '') {
        $h .= '<div class="page-hero__bg" data-parallax="0.25" style="background-image:url(\'' . e(thumb($image, 1920)) . '\')"></div>';
    }
    $h .= '<div class="page-hero__shade"></div><div class="container page-hero__in">';
    if ($kicker !== '') {
        $h .= '<span class="kicker kicker--light" data-reveal>' . kicker_html($kicker) . '</span>';
    }
    $h .= '<h1 data-reveal data-delay="1">' . title_html($title) . '</h1>';
    if ($text !== '') {
        $h .= '<p class="page-hero__text" data-reveal data-delay="2">' . e($text) . '</p>';
    }
    $h .= '<nav class="crumbs" aria-label="Sayfa yolu" data-reveal data-delay="3"><a href="' . e(url()) . '">Anasayfa</a>';
    foreach ($crumbs as [$label, $link]) {
        $h .= icon('chevron-right', 13) . ($link !== '' ? '<a href="' . e(url($link)) . '">' . e($label) . '</a>' : '<span>' . e($label) . '</span>');
    }
    $h .= '</nav></div>';
    $h .= '<svg class="page-hero__wave" viewBox="0 0 1440 80" preserveAspectRatio="none" aria-hidden="true"><path d="M0 40c240 40 480 40 720 10s480-40 720-10v40H0z"/></svg>';
    return $h . '</section>';
}

function section_head(?string $kicker, ?string $title, ?string $text = '', string $align = 'center', bool $light = false): string
{
    $h = '<header class="sec-head sec-head--' . e($align) . ($light ? ' sec-head--light' : '') . '">';
    if (trim((string) $kicker) !== '') {
        $h .= '<span class="kicker' . ($light ? ' kicker--light' : '') . '" data-reveal>' . kicker_html($kicker) . '</span>';
    }
    if (trim((string) $title) !== '') {
        $h .= '<h2 data-reveal data-delay="1">' . title_html($title) . '</h2>';
    }
    if (trim((string) $text) !== '') {
        $h .= '<p data-reveal data-delay="2">' . nl2br(e($text)) . '</p>';
    }
    return $h . '</header>';
}

function stars(int $n): string
{
    $n = max(0, min(7, $n));
    if ($n === 0) {
        return '';
    }
    return '<span class="stars" aria-label="' . $n . ' yıldız">' . str_repeat('<svg width="13" height="13" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="m12 2.500 2.900 6 6.600 1-4.800 4.600 1.100 6.600L12 17.600 6.200 20.700l1.100-6.600L2.500 9.500l6.600-1z"/></svg>', $n) . '</span>';
}

function hotel_card(array $h, int $i = 0): string
{
    $link = url('oteller/' . $h['slug']);
    $o = '<article class="hotel-card" data-reveal data-delay="' . ($i % 3) . '"' . (!empty($h['region_id']) ? ' data-region="' . (int) $h['region_id'] . '"' : '') . '>';
    $o .= '<a class="hotel-card__media" href="' . e($link) . '" tabindex="-1" aria-hidden="true">';
    if ($h['image'] !== '') {
        $o .= '<img src="' . e(thumb($h['image'], 720)) . '" alt="' . e($h['name']) . '" loading="lazy">';
    } else {
        $o .= '<span class="hotel-card__ph">' . icon('building', 44) . '</span>';
    }
    if ($h['concept'] !== '') {
        $o .= '<span class="badge">' . e($h['concept']) . '</span>';
    }
    $o .= '</a><div class="hotel-card__body">' . stars((int) $h['stars']);
    $o .= '<h3><a href="' . e($link) . '">' . e($h['name']) . '</a></h3>';
    if ($h['location'] !== '') {
        $o .= '<p class="hotel-card__loc">' . icon('map-pin', 15) . '<span>' . e($h['location']) . '</span></p>';
    }
    if (trim((string) $h['summary']) !== '') {
        $o .= '<p class="hotel-card__sum">' . e(excerpt($h['summary'], 130)) . '</p>';
    }
    $o .= '<a class="link-arrow" href="' . e($link) . '"><span>' . e(setting('label_details', 'Detaylar')) . '</span>' . icon('arrow-right', 16) . '</a>';
    return $o . '</div></article>';
}

/** Bölüm arka plan stili sınıfı. */
function sec_class(array $s): string
{
    $style = in_array($s['style'], ['light', 'soft', 'dark', 'brand'], true) ? $s['style'] : 'light';
    return 'sec sec--' . $style;
}

function is_dark_style(array $s): bool
{
    return in_array($s['style'], ['dark', 'brand'], true);
}

/** PDF gibi dosyalar için okunur boyut. */
function file_size_label(?string $path): string
{
    $f = ROOT_DIR . '/' . ltrim((string) $path, '/');
    if (!is_file($f)) {
        return '';
    }
    $b = filesize($f);
    return $b >= 1048576 ? number_format($b / 1048576, 1, ',', '.') . ' MB' : max(1, (int) round($b / 1024)) . ' KB';
}
