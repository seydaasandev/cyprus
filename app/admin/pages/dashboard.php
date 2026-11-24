<?php
/** Panel özeti: sayılar, son mesajlar ve hızlı bağlantılar. */
$stats = [
    ['Oteller', (int) val('SELECT COUNT(*) FROM hotels'), 'building', admin_url('list', ['e' => 'hotels'])],
    ['Hizmetler', (int) val('SELECT COUNT(*) FROM services'), 'briefcase', admin_url('list', ['e' => 'services'])],
    ['Bölgeler', (int) val('SELECT COUNT(*) FROM regions'), 'map-pin', admin_url('list', ['e' => 'regions'])],
    ['Sayfalar', (int) val('SELECT COUNT(*) FROM pages'), 'edit', admin_url('list', ['e' => 'pages'])],
    ['Okunmamış mesaj', (int) val('SELECT COUNT(*) FROM messages WHERE is_read = 0'), 'inbox', admin_url('messages')],
];
$latest = rows('SELECT * FROM messages ORDER BY created_at DESC, id DESC LIMIT 6');
$quick = [
    ['Slider / video düzenle', 'film', admin_url('list', ['e' => 'slides']), 'Anasayfanın üstündeki video ve slaytlar'],
    ['Anasayfa sıralaması', 'layers', admin_url('list', ['e' => 'sections']), 'Bölümleri sürükleyerek sıralayın, gizleyin'],
    ['Renkler ve stil', 'palette', admin_url('settings', ['g' => 'appearance']), 'Renk, yazı tipi, düğme ve kart stilleri'],
    ['Logo ve genel ayarlar', 'settings', admin_url('settings', ['g' => 'general']), 'Logo, üst şerit, menü düğmeleri'],
    ['Üst menü', 'menu', admin_url('list', ['e' => 'menu_header']), 'Menü öğeleri ve sırası'],
    ['Yeni otel ekle', 'plus', admin_url('edit', ['e' => 'hotels']), 'Görsel, konum, konsept, açıklama'],
];
admin_header('Özet');
?>
<div class="adm-stats">
  <?php foreach ($stats as [$label, $n, $ic, $href]): ?>
  <a class="adm-stat" href="<?= e($href) ?>"><span><?= icon($ic, 22) ?></span><strong><?= $n ?></strong><em><?= e($label) ?></em></a>
  <?php endforeach; ?>
</div>
<div class="adm-cols">
  <section class="adm-card">
    <h2>Hızlı işlemler</h2>
    <div class="adm-quick">
      <?php foreach ($quick as [$label, $ic, $href, $desc]): ?>
      <a href="<?= e($href) ?>"><span><?= icon($ic, 20) ?></span><div><strong><?= e($label) ?></strong><em><?= e($desc) ?></em></div><?= icon('chevron-right', 16) ?></a>
      <?php endforeach; ?>
    </div>
  </section>
  <section class="adm-card">
    <h2>Son mesajlar</h2>
    <?php if (!$latest): ?>
      <p class="adm-empty">Henüz form mesajı yok.</p>
    <?php else: ?>
    <ul class="adm-msglist">
      <?php foreach ($latest as $m): ?>
      <li class="<?= (int) $m['is_read'] === 0 ? 'is-unread' : '' ?>">
        <a href="<?= e(admin_url('messages', ['id' => $m['id']])) ?>">
          <strong><?= e(trim($m['name'] . ' ' . $m['surname'])) ?></strong>
          <span class="adm-tag<?= $m['kind'] === 'rezervasyon' ? ' adm-tag--accent' : '' ?>"><?= e(['talep' => 'Talep', 'rezervasyon' => 'Rezervasyon'][$m['kind']] ?? 'İletişim') ?></span>
          <em><?= e(excerpt(trim($m['hotel'] . ' ' . $m['room']) ?: ($m['message'] ?: $m['subject']), 70)) ?></em>
          <time><?= e(date('d.m.Y H:i', strtotime($m['created_at']))) ?></time>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>
    <a class="adm-link" href="<?= e(admin_url('messages')) ?>">Tüm mesajlar →</a>
    <?php endif; ?>
  </section>
</div>
<?php admin_footer(); ?>
