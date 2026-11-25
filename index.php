<?php
/**
 * Cyprus Touristic — ön yüz denetleyicisi (tüm sayfa adresleri buradan yönlendirilir).
 */
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';
require APP_DIR . '/forms.php';
require APP_DIR . '/parts.php';

/** Sayfayı üst/alt şablonla birlikte basar. */
function render(string $view, array $data = [], int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: text/html; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    $data += ['meta' => [], 'overHero' => false];
    extract($data, EXTR_SKIP);
    require APP_DIR . '/views/header.php';
    require APP_DIR . '/views/' . $view . '.php';
    require APP_DIR . '/views/footer.php';
    exit;
}

function not_found(): never
{
    render('404', ['meta' => ['title' => 'Sayfa bulunamadı', 'noindex' => true]], 404);
}

function moved(string $path): never
{
    header('Location: ' . url($path), true, 301);
    exit;
}

/** Tek parçalı adresleri (hizmet, bölge, factsheet, sayfa) çözer. */
function route_slug(string $slug): never
{
    if ($s = row('SELECT * FROM services WHERE slug = ? AND active = 1', [$slug])) {
        render('service', [
            'meta' => ['title' => $s['meta_title'] ?: $s['title'], 'desc' => $s['meta_desc'] ?: excerpt($s['summary'] ?: $s['content']), 'image' => $s['image']],
            'service' => $s,
            'others' => rows('SELECT * FROM services WHERE active = 1 AND id <> ? ORDER BY sort, id', [$s['id']]),
        ]);
    }
    if ($r = row('SELECT * FROM regions WHERE slug = ? AND active = 1', [$slug])) {
        render('region', [
            'meta' => ['title' => $r['meta_title'] ?: $r['title'], 'desc' => $r['meta_desc'] ?: excerpt($r['summary'] ?: $r['content']), 'image' => $r['image']],
            'region' => $r,
            'hotels' => rows('SELECT * FROM hotels WHERE active = 1 AND region_id = ? ORDER BY sort, id', [$r['id']]),
            'others' => rows('SELECT * FROM regions WHERE active = 1 AND id <> ? ORDER BY sort, id', [$r['id']]),
        ]);
    }
    if ($f = row('SELECT f.*, h.name AS hotel, h.slug AS hotel_slug FROM factsheets f LEFT JOIN hotels h ON h.id = f.hotel_id WHERE f.slug = ? AND f.active = 1', [$slug])) {
        render('factsheet', [
            'meta' => ['title' => $f['title']],
            'sheet' => $f,
            'others' => rows('SELECT * FROM factsheets WHERE active = 1 AND id <> ? ORDER BY sort, id', [$f['id']]),
        ]);
    }
    if ($p = row('SELECT * FROM pages WHERE slug = ? AND active = 1', [$slug])) {
        render('page', [
            'meta' => ['title' => $p['meta_title'] ?: $p['title'], 'desc' => $p['meta_desc'] ?: excerpt($p['content']), 'image' => $p['image']],
            'page' => $p,
        ]);
    }
    not_found();
}

function route_hotel(string $slug): never
{
    $hotel = row('SELECT h.*, r.title AS region, r.slug AS region_slug FROM hotels h LEFT JOIN regions r ON r.id = h.region_id WHERE h.slug = ? AND h.active = 1', [$slug]);
    if (!$hotel) {
        not_found();
    }
    render('hotel', [
        'meta' => ['title' => $hotel['meta_title'] ?: $hotel['name'], 'desc' => $hotel['meta_desc'] ?: excerpt($hotel['summary'] ?: $hotel['description']), 'image' => $hotel['image']],
        'hotel' => $hotel,
        'gallery' => rows('SELECT * FROM hotel_images WHERE hotel_id = ? ORDER BY sort, id', [$hotel['id']]),
        'sheets' => rows('SELECT * FROM factsheets WHERE hotel_id = ? AND active = 1 ORDER BY sort, id', [$hotel['id']]),
        'rooms' => rows('SELECT * FROM hotel_rooms WHERE hotel_id = ? ORDER BY sort, id', [$hotel['id']]),
        'others' => rows('SELECT * FROM hotels WHERE active = 1 AND id <> ? ORDER BY (region_id <=> ?) DESC, sort, id LIMIT 3', [$hotel['id'], $hotel['region_id']]),
    ]);
}

try {
    run_migrations();
    $path = current_path();
    $seg = $path === '' ? [] : explode('/', $path);
    $first = $seg[0] ?? '';
    $second = $seg[1] ?? '';

    if ($first === 'index.php') {
        moved('');
    }

    switch (true) {
        case $path === '':
            render('home', [
                'overHero' => true,
                'slides' => rows('SELECT * FROM slides WHERE active = 1 ORDER BY sort, id'),
                'sections' => rows('SELECT * FROM sections WHERE active = 1 ORDER BY sort, id'),
            ]);

        case $path === 'sitemap.xml':
            require APP_DIR . '/views/sitemap.php';
            exit;

        case $path === 'robots.txt':
            header('Content-Type: text/plain; charset=utf-8');
            echo "User-agent: *\nDisallow: " . base_path() . "/admin/\nSitemap: " . abs_url('sitemap.xml') . "\n";
            exit;

        case $path === 'oteller':
            render('hotels', [
                'meta' => ['title' => setting('hotels_title', 'Oteller'), 'desc' => setting('hotels_text')],
                'hotels' => rows('SELECT h.*, r.title AS region FROM hotels h LEFT JOIN regions r ON r.id = h.region_id WHERE h.active = 1 ORDER BY h.sort, h.id'),
                'regions' => rows('SELECT r.id, r.title FROM regions r WHERE r.active = 1 AND EXISTS (SELECT 1 FROM hotels h WHERE h.region_id = r.id AND h.active = 1) ORDER BY r.sort'),
            ]);

        case $first === 'oteller' && count($seg) === 2:
            route_hotel($second);

        case $first === 'urun' && $second !== '':
            moved('oteller/' . $second);

        case in_array($first, ['urun-kategori', 'magaza', 'shop', 'sepet', 'odeme', 'hesabim'], true):
            moved('oteller');

        case $path === 'm-i-c-e':
            moved('mice');

        case $path === 'hizmetlerimiz':
            render('services', [
                'meta' => ['title' => setting('services_title', 'Hizmetlerimiz'), 'desc' => setting('services_text')],
                'services' => rows('SELECT * FROM services WHERE active = 1 ORDER BY sort, id'),
            ]);

        case $path === 'kuzey-kibris':
            render('regions', [
                'meta' => ['title' => setting('regions_title', 'Kuzey Kıbrıs'), 'desc' => setting('regions_text')],
                'regions' => rows('SELECT * FROM regions WHERE active = 1 ORDER BY sort, id'),
            ]);

        case $path === 'hotel-factsheets':
            render('factsheets', [
                'meta' => ['title' => setting('factsheets_title', 'Hotel Factsheets'), 'desc' => setting('factsheets_text')],
                'sheets' => rows('SELECT f.*, h.name AS hotel, h.image AS hotel_image FROM factsheets f LEFT JOIN hotels h ON h.id = f.hotel_id WHERE f.active = 1 ORDER BY f.sort, f.id'),
            ]);

        case $path === 'iletisim':
            render('contact', [
                'meta' => ['title' => setting('contact_title', 'İletişim'), 'desc' => setting('contact_text')],
                'form' => handle_form('iletisim'),
            ]);

        case $path === 'talep-formu':
            render('request', [
                'meta' => ['title' => setting('request_title', 'Talep Formu'), 'desc' => setting('request_text')],
                'form' => handle_form('talep'),
                'hotels' => rows('SELECT name, slug FROM hotels WHERE active = 1 ORDER BY sort, id'),
            ]);

        case $path === 'rezervasyon':
            if (!reservations_enabled()) {
                // Rezervasyon formu kapalıyken eski bağlantılar otel listesine gider.
                header('Location: ' . url('oteller'), true, 302);
                exit;
            }
            render('reserve', [
                'meta' => ['title' => setting('reserve_title', 'Rezervasyon Talebi'), 'desc' => setting('reserve_text')],
                'form' => handle_form('rezervasyon'),
                'hotels' => rows('SELECT id, name, slug, image, location, concept FROM hotels WHERE active = 1 ORDER BY sort, id'),
                'rooms' => rows('SELECT r.* FROM hotel_rooms r JOIN hotels h ON h.id = r.hotel_id WHERE h.active = 1 ORDER BY r.hotel_id, r.sort, r.id'),
            ]);

        case count($seg) === 1:
            route_slug($first);

        default:
            not_found();
    }
} catch (Throwable $ex) {
    error_log('[site] ' . $ex->getMessage() . ' @ ' . $ex->getFile() . ':' . $ex->getLine());
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
    }
    echo '<!doctype html><meta charset="utf-8"><title>Geçici hata</title><div style="font:16px system-ui;max-width:520px;margin:18vh auto;text-align:center;color:#33414f"><h1 style="font-size:22px">Geçici bir sorun oluştu</h1><p>Lütfen birkaç dakika sonra yeniden deneyin.</p></div>';
}
