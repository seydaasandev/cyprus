<?php
/**
 * Tema: varsayılan ayarlar, yazı tipleri ve yönetim panelinden değişen CSS değişkenleri.
 */
declare(strict_types=1);

function theme_defaults(): array
{
    return [
        'site_name' => 'Cyprus Touristic',
        'tagline' => 'Akdenizin Kalbi Kuzey Kıbrıs',
        'logo_height' => '54',
        'c_primary' => '#00416A',
        'c_secondary' => '#0077BE',
        'c_accent' => '#FFC20E',
        'c_dark' => '#06243A',
        'c_text' => '#33414F',
        'c_muted' => '#6B7A89',
        'c_bg' => '#FFFFFF',
        'c_soft' => '#F3F7FA',
        'c_footer' => '#041B2D',
        'font_heading' => 'Playfair Display',
        'font_body' => 'Poppins',
        'font_size' => '16',
        'heading_weight' => '600',
        'radius' => '16',
        'btn_shape' => 'pill',
        'btn_style' => 'gradient',
        'btn_upper' => '0',
        'card_style' => 'shadow',
        'container' => '1240',
        'section_space' => '110',
        'header_transparent' => '1',
        'header_sticky' => '1',
        'topbar' => '1',
        'anim' => '1',
        'anim_style' => 'fade-up',
        'preloader' => '1',
        'hero_height' => '100',
        'hero_autoplay' => '12',
        'tr_enabled' => '1',
        'tr_langs' => 'tr,en,de,ru,ar,fr,el,iw',
        'float_whatsapp' => '1',
    ];
}

/** Ayarı okur; boşsa tema varsayılanına düşer. */
function opt(string $key): string
{
    return setting($key, theme_defaults()[$key] ?? '');
}

/** Seçilebilir yazı tipleri: ad => [Google Fonts sorgusu | null (yerel), yedek aile]. */
function font_catalog(): array
{
    return [
        'Playfair Display' => ['Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,500', 'Georgia, serif'],
        'Cormorant Garamond' => ['Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500', 'Georgia, serif'],
        'Marcellus' => ['Marcellus', 'Georgia, serif'],
        'DM Serif Display' => ['DM+Serif+Display:ital@0;1', 'Georgia, serif'],
        'Poppins' => ['Poppins:wght@300;400;500;600;700', 'system-ui, sans-serif'],
        'Montserrat' => ['Montserrat:wght@300;400;500;600;700', 'system-ui, sans-serif'],
        'Inter' => ['Inter:wght@300;400;500;600;700', 'system-ui, sans-serif'],
        'Manrope' => ['Manrope:wght@300;400;500;600;700', 'system-ui, sans-serif'],
        'Jost' => ['Jost:wght@300;400;500;600;700', 'system-ui, sans-serif'],
        'Outfit' => ['Outfit:wght@300;400;500;600;700', 'system-ui, sans-serif'],
        'Lato' => ['Lato:wght@300;400;700', 'system-ui, sans-serif'],
        'Raleway' => ['Raleway:wght@300;400;500;600;700', 'system-ui, sans-serif'],
        'Open Sans' => ['Open+Sans:wght@300;400;500;600;700', 'system-ui, sans-serif'],
        'Gotham Rounded' => [null, 'system-ui, sans-serif'],
    ];
}

function theme_font_links(): string
{
    $cat = font_catalog();
    $families = [];
    foreach (array_unique([opt('font_heading'), opt('font_body')]) as $name) {
        if (!empty($cat[$name][0])) {
            $families[] = 'family=' . $cat[$name][0];
        }
    }
    if (!$families) {
        return '';
    }
    return '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>'
        . '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?' . implode('&amp;', $families) . '&amp;display=swap">';
}

function css_color(string $value, string $fallback): string
{
    return preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6}|[0-9a-f]{8})$/i', trim($value)) ? trim($value) : $fallback;
}

function hex_rgb(string $hex): string
{
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    return hexdec(substr($hex, 0, 2)) . ',' . hexdec(substr($hex, 2, 2)) . ',' . hexdec(substr($hex, 4, 2));
}

/** <head> içine basılan, panelden yönetilen CSS değişkenleri. */
function theme_css(): string
{
    $d = theme_defaults();
    $cat = font_catalog();
    $col = static fn(string $k): string => css_color(opt($k), $d[$k]);
    $num = static fn(string $k, int $min, int $max): int => max($min, min($max, (int) opt($k)));
    $fh = isset($cat[opt('font_heading')]) ? opt('font_heading') : $d['font_heading'];
    $fb = isset($cat[opt('font_body')]) ? opt('font_body') : $d['font_body'];
    $radius = $num('radius', 0, 40);
    $btnRadius = match (opt('btn_shape')) {
        'square' => '0px',
        'rounded' => '10px',
        default => '999px',
    };
    $vars = [
        '--c-primary' => $col('c_primary'),
        '--c-primary-rgb' => hex_rgb($col('c_primary')),
        '--c-secondary' => $col('c_secondary'),
        '--c-secondary-rgb' => hex_rgb($col('c_secondary')),
        '--c-accent' => $col('c_accent'),
        '--c-accent-rgb' => hex_rgb($col('c_accent')),
        '--c-dark' => $col('c_dark'),
        '--c-dark-rgb' => hex_rgb($col('c_dark')),
        '--c-text' => $col('c_text'),
        '--c-muted' => $col('c_muted'),
        '--c-bg' => $col('c_bg'),
        '--c-soft' => $col('c_soft'),
        '--c-footer' => $col('c_footer'),
        '--f-heading' => "'" . $fh . "', " . $cat[$fh][1],
        '--f-body' => "'" . $fb . "', " . $cat[$fb][1],
        '--fs-base' => $num('font_size', 13, 20) . 'px',
        '--fw-heading' => (string) $num('heading_weight', 300, 800),
        '--radius' => $radius . 'px',
        '--radius-sm' => (int) round($radius * 0.6) . 'px',
        '--btn-radius' => $btnRadius,
        '--btn-transform' => opt('btn_upper') === '1' ? 'uppercase' : 'none',
        '--btn-spacing' => opt('btn_upper') === '1' ? '.08em' : '.01em',
        '--container' => $num('container', 960, 1600) . 'px',
        '--section-space' => $num('section_space', 40, 200) . 'px',
        '--logo-h' => $num('logo_height', 24, 120) . 'px',
        '--hero-h' => $num('hero_height', 50, 100) . 'vh',
    ];
    $css = ':root{';
    foreach ($vars as $k => $v) {
        $css .= $k . ':' . $v . ';';
    }
    $css .= '}';
    if (is_file(ROOT_DIR . '/assets/fonts/gotham-rounded-medium.woff')) {
        $css .= "@font-face{font-family:'Gotham Rounded';font-weight:400 500;font-display:swap;src:url('" . base_path() . "/assets/fonts/gotham-rounded-medium.woff') format('woff')}";
        $css .= "@font-face{font-family:'Gotham Rounded';font-weight:600 800;font-display:swap;src:url('" . base_path() . "/assets/fonts/gotham-rounded-bold.woff') format('woff')}";
    }
    $custom = str_ireplace('</style', '', setting('custom_css'));
    return '<style id="theme-vars">' . $css . '</style>' . ($custom !== '' ? '<style id="custom-css">' . $custom . '</style>' : '');
}

/** <body> sınıfları: panelden seçilen stil varyantları. */
function theme_body_class(): string
{
    $c = [];
    $c[] = 'btn-' . (in_array(opt('btn_style'), ['solid', 'gradient', 'outline'], true) ? opt('btn_style') : 'gradient');
    $c[] = 'card-' . (in_array(opt('card_style'), ['shadow', 'border', 'flat'], true) ? opt('card_style') : 'shadow');
    $c[] = 'anim-' . (in_array(opt('anim_style'), ['fade-up', 'zoom', 'slide', 'blur'], true) ? opt('anim_style') : 'fade-up');
    if (opt('anim') !== '1') {
        $c[] = 'no-anim';
    }
    if (opt('header_sticky') === '1') {
        $c[] = 'hdr-sticky';
    }
    return implode(' ', $c);
}

/** Çeviri menüsünde gösterilecek diller. */
function translate_langs(): array
{
    $names = ['tr' => 'Türkçe', 'en' => 'English', 'de' => 'Deutsch', 'ru' => 'Русский', 'ar' => 'العربية', 'fr' => 'Français', 'el' => 'Ελληνικά', 'iw' => 'עברית', 'sk' => 'Slovenčina', 'it' => 'Italiano', 'es' => 'Español', 'fa' => 'فارسی', 'nl' => 'Nederlands', 'pl' => 'Polski', 'uk' => 'Українська', 'zh-CN' => '中文'];
    $out = [];
    foreach (array_map('trim', explode(',', opt('tr_langs'))) as $code) {
        if (isset($names[$code])) {
            $out[$code] = $names[$code];
        }
    }
    return $out ?: ['tr' => 'Türkçe'];
}
