<?php
/** İçerik listesi: sürükle-bırak sıralama, aç/kapat, düzenle, sil. */
require __DIR__ . '/_crud.php';

[$key, $def] = crud_entity();
[$w, $p] = crud_where($def);
$order = !empty($def['sortable']) ? 'sort, id' : 'id';
$list = rows('SELECT * FROM ' . $def['table'] . $w . ' ORDER BY ' . $order, $p);

if (!empty($def['tree'])) {
    // Üst öğeler ve hemen altlarında kendi alt öğeleri.
    $children = [];
    foreach ($list as $r) {
        if ($r['parent_id']) {
            $children[(int) $r['parent_id']][] = $r;
        }
    }
    $ordered = [];
    foreach ($list as $r) {
        if (!$r['parent_id']) {
            $ordered[] = $r;
            foreach ($children[(int) $r['id']] ?? [] as $c) {
                $ordered[] = $c + ['_child' => true];
            }
        }
    }
    $list = $ordered;
}

$actions = [];
if (empty($def['fixed'])) {
    $actions[] = admin_btn('Yeni ' . $def['single'], admin_url('edit', ['e' => $key]), 'plus');
}
admin_header($def['title'], $actions);
$colTypes = $def['fields'];
?>
<?php if (!empty($def['help'])): ?><p class="adm-hint"><?= e($def['help']) ?></p><?php endif; ?>
<section class="adm-card adm-card--flush">
  <?php if (!$list): ?>
    <p class="adm-empty">Henüz kayıt yok. <?php if (empty($def['fixed'])): ?><a href="<?= e(admin_url('edit', ['e' => $key])) ?>">İlk <?= e(mb_strtolower($def['single'])) ?> kaydını ekleyin.</a><?php endif; ?></p>
  <?php else: ?>
  <div class="adm-table-wrap">
  <table class="adm-table">
    <thead><tr>
      <?php if (!empty($def['sortable'])): ?><th class="adm-col-grip" aria-label="Sırala"></th><?php endif; ?>
      <?php foreach ($def['columns'] as $col => $label): ?><th><?= e($label) ?></th><?php endforeach; ?>
      <?php if (!empty($def['toggle'])): ?><th>Yayında</th><?php endif; ?>
      <th class="adm-col-act">İşlemler</th>
    </tr></thead>
    <tbody<?= !empty($def['sortable']) ? ' data-sortable="' . e(admin_url('sort', ['e' => $key])) . '"' : '' ?>>
      <?php foreach ($list as $r): ?>
      <tr data-id="<?= (int) $r['id'] ?>"<?= !empty($r['_child']) ? ' class="is-child"' : '' ?>>
        <?php if (!empty($def['sortable'])): ?><td class="adm-col-grip"><span class="adm-grip" title="Sürükleyerek sırala"><?= icon('grip', 18) ?></span></td><?php endif; ?>
        <?php foreach ($def['columns'] as $col => $label):
            $type = $colTypes[$col]['type'] ?? 'text';
            $v = (string) ($r[$col] ?? ''); ?>
        <td data-label="<?= e($label) ?>">
          <?php if (in_array($type, ['image', 'video', 'file'], true)): ?>
            <span class="adm-thumb"><?= $v !== '' ? admin_media_preview($v) : icon('image', 20) ?></span>
          <?php elseif ($type === 'select'): ?>
            <span class="adm-tag"><?= e($colTypes[$col]['options'][$v] ?? $v) ?></span>
          <?php elseif ($col === $def['label']): ?>
            <a class="adm-rowtitle" href="<?= e(admin_url('edit', ['e' => $key, 'id' => $r['id']])) ?>"><?= e(strip_tags($v) !== '' ? strip_tags($v) : '(başlıksız)') ?></a>
          <?php else: ?>
            <?= e(excerpt($v, 90)) ?>
          <?php endif; ?>
        </td>
        <?php endforeach; ?>
        <?php if (!empty($def['toggle'])): ?>
        <td data-label="Yayında"><label class="adm-switch adm-switch--sm"><input type="checkbox" data-toggle="<?= e(admin_url('toggle', ['e' => $key, 'id' => $r['id']])) ?>"<?= (int) $r[$def['toggle']] === 1 ? ' checked' : '' ?>><i></i></label></td>
        <?php endif; ?>
        <td class="adm-col-act">
          <?php if (($view = crud_view_url($def, $r)) !== ''): ?><a class="adm-icon-btn" href="<?= e($view) ?>" target="_blank" rel="noopener" title="Sitede gör"><?= icon('eye', 17) ?></a><?php endif; ?>
          <a class="adm-icon-btn" href="<?= e(admin_url('edit', ['e' => $key, 'id' => $r['id']])) ?>" title="Düzenle"><?= icon('edit', 17) ?></a>
          <?php if (empty($def['fixed'])): ?>
          <form method="post" action="<?= e(admin_url('delete', ['e' => $key, 'id' => $r['id']])) ?>" data-confirm="“<?= e(strip_tags((string) $r[$def['label']])) ?>” kalıcı olarak silinsin mi?">
            <?= csrf_field() ?><button class="adm-icon-btn adm-icon-btn--danger" type="submit" title="Sil"><?= icon('trash', 17) ?></button>
          </form>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?php endif; ?>
</section>
<?php admin_footer(); ?>
