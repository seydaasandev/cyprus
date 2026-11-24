<?php
/**
 * İçerik türü ekranlarının ortak yardımcıları.
 */
declare(strict_types=1);

/** Tek parçalı adreslerde (site.com/slug) çakışmaması gereken tablolar ve ayrılmış yollar. */
const SLUG_SHARED_TABLES = ['services', 'regions', 'factsheets', 'pages'];
const SLUG_RESERVED = ['admin', 'app', 'assets', 'uploads', 'oteller', 'hizmetlerimiz', 'kuzey-kibris', 'hotel-factsheets', 'iletisim', 'talep-formu', 'rezervasyon', 'urun', 'sitemap.xml', 'robots.txt', 'index.php'];

/** @return array{0: string, 1: array} */
function crud_entity(): array
{
    $key = preg_replace('/[^a-z0-9_]/', '', (string) ($_GET['e'] ?? ''));
    $all = entities();
    if (!isset($all[$key])) {
        flash('İstenen içerik türü bulunamadı.', 'err');
        redirect(admin_url());
    }
    return [$key, $all[$key]];
}

/** Türün sabit koşulu (ör. items.grp = 'counter'). @return array{0: string, 1: array} */
function crud_where(array $def, string $prefix = ' WHERE '): array
{
    $sql = '';
    $params = [];
    foreach (($def['where'] ?? []) as $col => $v) {
        $sql .= ($sql === '' ? $prefix : ' AND ') . $col . ' = ?';
        $params[] = $v;
    }
    return [$sql, $params];
}

function crud_find(array $def, int $id): ?array
{
    [$w, $p] = crud_where($def, ' AND ');
    return row('SELECT * FROM ' . $def['table'] . ' WHERE id = ?' . $w, array_merge([$id], $p));
}

/** Benzersiz ve güvenli sayfa adresi üretir. */
function crud_unique_slug(string $table, string $wanted, int $ignoreId): string
{
    $base = slugify($wanted) ?: 'sayfa';
    $base = mb_substr($base, 0, 150);
    $tables = in_array($table, SLUG_SHARED_TABLES, true) ? SLUG_SHARED_TABLES : [$table];
    $taken = static function (string $slug) use ($tables, $table, $ignoreId): bool {
        if (in_array($table, SLUG_SHARED_TABLES, true) && in_array($slug, SLUG_RESERVED, true)) {
            return true;
        }
        foreach ($tables as $t) {
            $sql = 'SELECT COUNT(*) FROM ' . $t . ' WHERE slug = ?' . ($t === $table ? ' AND id <> ?' : '');
            if ((int) val($sql, $t === $table ? [$slug, $ignoreId] : [$slug]) > 0) {
                return true;
            }
        }
        return false;
    };
    $slug = $base;
    for ($i = 2; $taken($slug); $i++) {
        $slug = $base . '-' . $i;
    }
    return $slug;
}

function crud_view_url(array $def, array $r): string
{
    if (empty($def['view'])) {
        return '';
    }
    return url(str_replace('{slug}', (string) ($r['slug'] ?? ''), $def['view']));
}
