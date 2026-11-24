<?php
/** sitemap.xml — yayındaki tüm sayfalar. */
header('Content-Type: application/xml; charset=utf-8');
$urls = ['', 'oteller', 'hizmetlerimiz', 'kuzey-kibris', 'hotel-factsheets', 'iletisim', 'talep-formu', 'rezervasyon'];
foreach (rows('SELECT slug FROM hotels WHERE active = 1 ORDER BY sort, id') as $r) {
    $urls[] = 'oteller/' . $r['slug'];
}
foreach (['services', 'regions', 'factsheets', 'pages'] as $table) {
    foreach (rows('SELECT slug FROM ' . $table . ' WHERE active = 1 ORDER BY sort, id') as $r) {
        $urls[] = $r['slug'];
    }
}
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
    echo '  <url><loc>' . e(abs_url($u)) . '</loc></url>' . "\n";
}
echo '</urlset>' . "\n";
