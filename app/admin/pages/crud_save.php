<?php
/** İçerik kaydetme (ekleme ve güncelleme). */
require_once __DIR__ . '/_crud.php';

const PASSWORD_MIN_LENGTH = 10;
const GALLERY_MAX_IMAGES = 60;
const GALLERY_CAPTION_MAX = 190;
const ROOMS_MAX = 40;

admin_require_post();
[$key, $def] = crud_entity();
$id = (int) ($_GET['id'] ?? 0);
$record = $id > 0 ? crud_find($def, $id) : null;
if (($id > 0 && !$record) || ($id === 0 && !empty($def['fixed']))) {
    flash('Kayıt bulunamadı.', 'err');
    redirect(admin_url('list', ['e' => $key]));
}

$data = [];
$errors = [];
$shown = [];
foreach ($def['fields'] as $name => $spec) {
    $type = $spec['type'] ?? 'text';
    $raw = $_POST[$name] ?? '';
    $shown[$name] = is_array($raw) ? '' : (string) $raw;

    if ($type === 'password') {
        $pw = (string) $raw;
        if ($pw === '' && $record) {
            continue;
        }
        if (mb_strlen($pw) < PASSWORD_MIN_LENGTH) {
            $errors[] = 'Şifre en az ' . PASSWORD_MIN_LENGTH . ' karakter olmalı.';
            continue;
        }
        $data['password_hash'] = password_hash($pw, PASSWORD_DEFAULT);
        continue;
    }
    if ($type === 'slug') {
        $source = trim((string) $raw) !== '' ? (string) $raw : (string) ($_POST[$spec['from']] ?? '');
        $data[$name] = crud_unique_slug($def['table'], strip_tags($source), $id);
        continue;
    }
    $clean = admin_clean_value($spec, $raw);
    if (!empty($spec['required']) && trim(strip_tags((string) $clean)) === '') {
        $errors[] = '“' . $spec['label'] . '” alanı boş bırakılamaz.';
    }
    if (isset($spec['pattern']) && $clean !== '' && !preg_match('/^' . $spec['pattern'] . '$/', (string) $clean)) {
        $errors[] = '“' . $spec['label'] . '” alanı geçerli değil.';
    }
    if ($type === 'relation' && $spec['table'] === $def['table'] && $clean !== null) {
        // Kendisinin ya da alt öğesi olan bir kaydın altına taşınamaz.
        $hasChildren = $id > 0 && (int) val('SELECT COUNT(*) FROM ' . $def['table'] . ' WHERE ' . $name . ' = ?', [$id]) > 0;
        if ((int) $clean === $id || $hasChildren) {
            $clean = null;
            $errors[] = $hasChildren ? 'Alt öğeleri olan bir menü öğesi başka bir öğenin altına taşınamaz.' : 'Bir öğe kendi altına taşınamaz.';
        }
    }
    $data[$name] = $clean;
}

if ($def['table'] === 'users' && isset($data['username'])) {
    if ((int) val('SELECT COUNT(*) FROM users WHERE username = ? AND id <> ?', [$data['username'], $id]) > 0) {
        $errors[] = 'Bu kullanıcı adı zaten kullanılıyor.';
    }
    if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'E-posta adresi geçerli değil.';
    }
    if ($id === (int) $me['id'] && (int) $data['active'] === 0) {
        $errors[] = 'Kendi hesabınızı devre dışı bırakamazsınız.';
    }
}

$gallery = [];
if (!empty($def['gallery'])) {
    $captions = array_values((array) ($_POST['gallery_caption'] ?? []));
    foreach (array_slice(array_values((array) ($_POST['gallery'] ?? [])), 0, GALLERY_MAX_IMAGES) as $i => $img) {
        $img = admin_clean_value(['type' => 'image'], $img);
        if ($img !== '') {
            $caption = is_string($captions[$i] ?? null) ? $captions[$i] : '';
            $gallery[] = ['image' => $img, 'caption' => mb_substr(trim(strip_tags($caption)), 0, GALLERY_CAPTION_MAX)];
        }
    }
}

$roomRows = [];
if (!empty($def['rooms'])) {
    $text = static fn($v, int $max): string => mb_substr(trim(preg_replace('/[\x00-\x1F\x7F]/u', '', (string) $v)), 0, $max);
    $names = array_slice((array) ($_POST['room_name'] ?? []), 0, ROOMS_MAX);
    foreach ($names as $i => $name) {
        $name = $text($name, 190);
        if ($name === '') {
            continue;
        }
        $roomRows[] = [
            'name' => $name,
            'details' => $text($_POST['room_details'][$i] ?? '', 400),
            'price' => $text($_POST['room_price'][$i] ?? '', 80),
            'note' => $text($_POST['room_note'][$i] ?? '', 190),
        ];
    }
}

if ($errors) {
    $formRooms = $roomRows;
    $formValues = array_merge($record ?? [], $shown);
    $formErrors = $errors;
    $formGallery = $gallery;
    require __DIR__ . '/crud_edit.php';
    return;
}

if ($record) {
    $set = implode(', ', array_map(static fn(string $c): string => $c . ' = ?', array_keys($data)));
    q('UPDATE ' . $def['table'] . ' SET ' . $set . ' WHERE id = ?', array_merge(array_values($data), [$id]));
} else {
    foreach (($def['where'] ?? []) as $col => $v) {
        $data[$col] = $v;
    }
    if (!empty($def['sortable'])) {
        [$w, $p] = crud_where($def);
        $data['sort'] = (int) val('SELECT COALESCE(MAX(sort), 0) + 1 FROM ' . $def['table'] . $w, $p);
    }
    $cols = array_keys($data);
    q('INSERT INTO ' . $def['table'] . ' (' . implode(', ', $cols) . ') VALUES (' . implode(', ', array_fill(0, count($cols), '?')) . ')', array_values($data));
    $id = (int) db()->lastInsertId();
}

if (!empty($def['gallery'])) {
    $g = $def['gallery'];
    q('DELETE FROM ' . $g['table'] . ' WHERE ' . $g['fk'] . ' = ?', [$id]);
    foreach ($gallery as $i => $shot) {
        q('INSERT INTO ' . $g['table'] . ' (' . $g['fk'] . ', image, caption, sort) VALUES (?, ?, ?, ?)', [$id, $shot['image'], $shot['caption'], $i]);
    }
}

if (!empty($def['rooms'])) {
    // Var olan satırları yerinde güncelle ki ziyaretçilerin elindeki "oda" bağlantıları geçerli kalsın.
    $existing = array_column(rows('SELECT id FROM hotel_rooms WHERE hotel_id = ? ORDER BY sort, id', [$id]), 'id');
    foreach ($roomRows as $i => $r) {
        if (isset($existing[$i])) {
            q('UPDATE hotel_rooms SET name = ?, details = ?, price = ?, note = ?, sort = ? WHERE id = ?', [$r['name'], $r['details'], $r['price'], $r['note'], $i, $existing[$i]]);
        } else {
            q('INSERT INTO hotel_rooms (hotel_id, name, details, price, note, sort) VALUES (?,?,?,?,?,?)', [$id, $r['name'], $r['details'], $r['price'], $r['note'], $i]);
        }
    }
    foreach (array_slice($existing, count($roomRows)) as $extra) {
        q('DELETE FROM hotel_rooms WHERE id = ?', [$extra]);
    }
}

flash($def['single'] . ' kaydedildi.');
redirect(!empty($_POST['_stay']) ? admin_url('edit', ['e' => $key, 'id' => $id]) : admin_url('list', ['e' => $key]));
