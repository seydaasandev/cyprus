<?php
/** Liste işlemleri: silme, sürükle-bırak sıralama, yayında anahtarı. Hepsi POST + CSRF ister. */
require_once __DIR__ . '/_crud.php';

admin_require_post();
[$key, $def] = crud_entity();
$action = (string) ($_GET['p'] ?? '');
$id = (int) ($_GET['id'] ?? 0);

if ($action === 'sort') {
    if (empty($def['sortable'])) {
        admin_json(['ok' => false, 'error' => 'Bu liste sıralanamaz.'], 400);
    }
    $ids = array_values(array_unique(array_map('intval', (array) ($_POST['ids'] ?? []))));
    [$w, $p] = crud_where($def, ' AND ');
    $st = db()->prepare('UPDATE ' . $def['table'] . ' SET sort = ? WHERE id = ?' . $w);
    db()->beginTransaction();
    foreach ($ids as $pos => $rowId) {
        $st->execute(array_merge([$pos + 1, $rowId], $p));
    }
    db()->commit();
    admin_json(['ok' => true]);
}

$record = crud_find($def, $id);
if (!$record) {
    if (admin_wants_json()) {
        admin_json(['ok' => false, 'error' => 'Kayıt bulunamadı.'], 404);
    }
    flash('Kayıt bulunamadı.', 'err');
    redirect(admin_url('list', ['e' => $key]));
}

if ($action === 'toggle') {
    $col = $def['toggle'] ?? '';
    if ($col === '') {
        admin_json(['ok' => false, 'error' => 'Geçersiz işlem.'], 400);
    }
    if ($def['table'] === 'users' && $id === (int) $me['id']) {
        admin_json(['ok' => false, 'error' => 'Kendi hesabınızı devre dışı bırakamazsınız.'], 400);
    }
    $new = (int) $record[$col] === 1 ? 0 : 1;
    q('UPDATE ' . $def['table'] . ' SET ' . $col . ' = ? WHERE id = ?', [$new, $id]);
    admin_json(['ok' => true, 'value' => $new]);
}

// Silme
if (!empty($def['fixed'])) {
    flash('Bu kayıt silinemez; gizlemek için yayından kaldırın.', 'err');
    redirect(admin_url('list', ['e' => $key]));
}
if ($def['table'] === 'users') {
    if ($id === (int) $me['id']) {
        flash('Kendi hesabınızı silemezsiniz.', 'err');
        redirect(admin_url('list', ['e' => $key]));
    }
    if ((int) val('SELECT COUNT(*) FROM users WHERE active = 1 AND id <> ?', [$id]) === 0) {
        flash('Son yönetici hesabı silinemez.', 'err');
        redirect(admin_url('list', ['e' => $key]));
    }
}

db()->beginTransaction();
switch ($def['table']) {
    case 'hotels':
        q('DELETE FROM hotel_images WHERE hotel_id = ?', [$id]);
        q('UPDATE factsheets SET hotel_id = NULL WHERE hotel_id = ?', [$id]);
        break;
    case 'regions':
        q('UPDATE hotels SET region_id = NULL WHERE region_id = ?', [$id]);
        break;
    case 'menu_items':
        q('DELETE FROM menu_items WHERE parent_id = ?', [$id]);
        break;
}
q('DELETE FROM ' . $def['table'] . ' WHERE id = ?', [$id]);
db()->commit();

flash($def['single'] . ' silindi.');
redirect(admin_url('list', ['e' => $key]));
