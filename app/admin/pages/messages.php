<?php
/** İletişim ve talep formundan gelen mesajlar. */
const MESSAGES_PER_PAGE = 25;

function message_kind_label(string $kind): string
{
    return ['talep' => 'Talep', 'rezervasyon' => 'Rezervasyon', 'iletisim' => 'İletişim'][$kind] ?? 'İletişim';
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    admin_require_post();
    $id = (int) ($_POST['id'] ?? 0);
    $do = (string) ($_POST['do'] ?? '');
    if ($do === 'delete' && $id > 0) {
        q('DELETE FROM messages WHERE id = ?', [$id]);
        flash('Mesaj silindi.');
    } elseif ($do === 'unread' && $id > 0) {
        q('UPDATE messages SET is_read = 0 WHERE id = ?', [$id]);
        flash('Mesaj okunmadı olarak işaretlendi.');
    } elseif ($do === 'readall') {
        q('UPDATE messages SET is_read = 1 WHERE is_read = 0');
        flash('Tüm mesajlar okundu olarak işaretlendi.');
    }
    redirect(admin_url('messages'));
}

$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    $m = row('SELECT * FROM messages WHERE id = ?', [$id]);
    if (!$m) {
        flash('Mesaj bulunamadı.', 'err');
        redirect(admin_url('messages'));
    }
    q('UPDATE messages SET is_read = 1 WHERE id = ?', [$id]);
    admin_header('Mesaj', [admin_btn('Mesajlara dön', admin_url('messages'), 'arrow-left', 'adm-btn--ghost')]);
    $fmtDate = static fn(?string $d): string => $d ? date('d.m.Y', strtotime($d)) : '';
    $nights = ($m['checkin'] && $m['checkout']) ? (int) ((strtotime($m['checkout']) - strtotime($m['checkin'])) / 86400) : 0;
    $rowsOut = ['Tür' => message_kind_label($m['kind']) . ' Formu', 'Ad Soyad' => trim($m['name'] . ' ' . $m['surname']), 'Firma' => $m['company'], 'Ülke' => $m['country'], 'Konu' => $m['subject'], 'Otel' => $m['hotel'], 'Oda' => $m['room'],
        'Giriş tarihi' => $fmtDate($m['checkin']), 'Çıkış tarihi' => $fmtDate($m['checkout']) . ($nights > 0 ? ' (' . $nights . ' gece)' : ''),
        'Kişi sayısı' => $m['kind'] === 'rezervasyon' ? ((int) $m['adults'] . ' yetişkin' . ((int) $m['children'] > 0 ? ', ' . (int) $m['children'] . ' çocuk' : '')) : '', 'Tarih' => date('d.m.Y H:i', strtotime($m['created_at'])), 'IP' => $m['ip']];
    ?>
    <section class="adm-card">
      <dl class="adm-dl">
        <?php foreach ($rowsOut as $k => $v): if ($v === '') { continue; } ?><dt><?= e($k) ?></dt><dd><?= e($v) ?></dd><?php endforeach; ?>
        <?php if ($m['email'] !== ''): ?><dt>E-posta</dt><dd><a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a></dd><?php endif; ?>
        <?php if ($m['phone'] !== ''): ?><dt>Telefon</dt><dd><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $m['phone'])) ?>"><?= e($m['phone']) ?></a></dd><?php endif; ?>
      </dl>
      <?php if (trim((string) $m['message']) !== ''): ?><div class="adm-msgbody"><?= nl2br(e($m['message'])) ?></div><?php endif; ?>
      <div class="adm-savebar adm-savebar--static">
        <?php if ($m['email'] !== ''): ?><a class="adm-btn adm-btn--primary" href="mailto:<?= e($m['email']) ?>?subject=<?= rawurlencode('Re: ' . ($m['subject'] ?: opt('site_name'))) ?>"><?= icon('mail', 16) ?><span>E-posta ile yanıtla</span></a><?php endif; ?>
        <form method="post" action="<?= e(admin_url('messages')) ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $m['id'] ?>"><button class="adm-btn adm-btn--soft" name="do" value="unread" type="submit">Okunmadı yap</button></form>
        <form method="post" action="<?= e(admin_url('messages')) ?>" data-confirm="Bu mesaj kalıcı olarak silinsin mi?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $m['id'] ?>"><button class="adm-btn adm-btn--danger-ghost" name="do" value="delete" type="submit"><?= icon('trash', 16) ?><span>Sil</span></button></form>
      </div>
    </section>
    <?php
    admin_footer();
    return;
}

$total = (int) val('SELECT COUNT(*) FROM messages');
$pages = max(1, (int) ceil($total / MESSAGES_PER_PAGE));
$pageNo = max(1, min($pages, (int) ($_GET['s'] ?? 1)));
$list = rows('SELECT * FROM messages ORDER BY created_at DESC, id DESC LIMIT ' . MESSAGES_PER_PAGE . ' OFFSET ' . (($pageNo - 1) * MESSAGES_PER_PAGE));
admin_header('Mesajlar');
?>
<?php if (setting('mail_enabled') !== '1'): ?><p class="adm-hint">E-posta bildirimi kapalı: form mesajları yalnızca burada görünür. Açmak için <a href="<?= e(admin_url('settings', ['g' => 'contact'])) ?>">İletişim ve Sosyal</a> ayarlarına gidin.</p><?php endif; ?>
<section class="adm-card adm-card--flush">
  <?php if (!$list): ?>
    <p class="adm-empty">Henüz form mesajı yok.</p>
  <?php else: ?>
  <div class="adm-table-wrap">
  <table class="adm-table">
    <thead><tr><th>Gönderen</th><th>Tür</th><th>Özet</th><th>Tarih</th><th class="adm-col-act">İşlemler</th></tr></thead>
    <tbody>
      <?php foreach ($list as $m): ?>
      <tr class="<?= (int) $m['is_read'] === 0 ? 'is-unread' : '' ?>">
        <td data-label="Gönderen"><a class="adm-rowtitle" href="<?= e(admin_url('messages', ['id' => $m['id']])) ?>"><?= e(trim($m['name'] . ' ' . $m['surname'])) ?></a><br><small><?= e($m['email']) ?></small></td>
        <td data-label="Tür"><span class="adm-tag<?= $m['kind'] === 'rezervasyon' ? ' adm-tag--accent' : '' ?>"><?= e(message_kind_label($m['kind'])) ?></span></td>
        <td data-label="Özet"><?= e(excerpt(trim($m['hotel'] . ' ' . $m['room'] . ' ' . ($m['checkin'] ? date('d.m.Y', strtotime($m['checkin'])) . '–' . date('d.m.Y', strtotime((string) $m['checkout'])) : '') . ' ' . $m['subject'] . ' ' . $m['message']), 110)) ?></td>
        <td data-label="Tarih"><?= e(date('d.m.Y H:i', strtotime($m['created_at']))) ?></td>
        <td class="adm-col-act">
          <a class="adm-icon-btn" href="<?= e(admin_url('messages', ['id' => $m['id']])) ?>" title="Oku"><?= icon('eye', 17) ?></a>
          <form method="post" action="<?= e(admin_url('messages')) ?>" data-confirm="Bu mesaj kalıcı olarak silinsin mi?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $m['id'] ?>"><button class="adm-icon-btn adm-icon-btn--danger" name="do" value="delete" type="submit" title="Sil"><?= icon('trash', 17) ?></button></form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?php endif; ?>
</section>
<?php if ($list): ?>
<div class="adm-pager">
  <form method="post" action="<?= e(admin_url('messages')) ?>"><?= csrf_field() ?><button class="adm-btn adm-btn--soft" name="do" value="readall" type="submit">Tümünü okundu yap</button></form>
  <span>
    <?php for ($i = 1; $i <= $pages; $i++): ?><a class="<?= $i === $pageNo ? 'is-active' : '' ?>" href="<?= e(admin_url('messages', ['s' => $i])) ?>"><?= $i ?></a><?php endfor; ?>
  </span>
</div>
<?php endif; ?>
<?php admin_footer(); ?>
