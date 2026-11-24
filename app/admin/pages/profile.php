<?php
/** Oturum açan yöneticinin kendi bilgileri ve şifre değişikliği. */
const PROFILE_PASSWORD_MIN = 10;

$errors = [];
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    admin_require_post();
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $current = (string) ($_POST['current_password'] ?? '');
    $new = (string) ($_POST['new_password'] ?? '');
    $again = (string) ($_POST['new_password2'] ?? '');
    $hash = (string) val('SELECT password_hash FROM users WHERE id = ?', [$me['id']]);

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'E-posta adresi geçerli değil.';
    }
    if ($new !== '') {
        if (!password_verify($current, $hash)) {
            $errors[] = 'Mevcut şifre hatalı.';
        }
        if (mb_strlen($new) < PROFILE_PASSWORD_MIN) {
            $errors[] = 'Yeni şifre en az ' . PROFILE_PASSWORD_MIN . ' karakter olmalı.';
        }
        if ($new !== $again) {
            $errors[] = 'Yeni şifreler birbiriyle aynı değil.';
        }
    }
    if (!$errors) {
        q('UPDATE users SET name = ?, email = ? WHERE id = ?', [mb_substr($name, 0, 120), mb_substr($email, 0, 190), $me['id']]);
        if ($new !== '') {
            q('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($new, PASSWORD_DEFAULT), $me['id']]);
            session_regenerate_id(true);
        }
        flash($new !== '' ? 'Bilgileriniz ve şifreniz güncellendi.' : 'Bilgileriniz güncellendi.');
        redirect(admin_url('profile'));
    }
    $me['name'] = $name;
    $me['email'] = $email;
}
admin_header('Hesabım');
?>
<?php if ($errors): ?><div class="adm-alert adm-alert--err"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
<form method="post" action="<?= e(admin_url('profile')) ?>" class="adm-form">
  <?= csrf_field() ?>
  <section class="adm-card">
    <h2>Bilgilerim</h2>
    <div class="adm-grid">
      <div class="adm-field"><label class="adm-label">Kullanıcı adı</label><input type="text" value="<?= e($me['username']) ?>" disabled></div>
      <div class="adm-field"><label class="adm-label" for="p_name">Ad Soyad</label><input type="text" id="p_name" name="name" value="<?= e($me['name']) ?>" maxlength="120"></div>
      <div class="adm-field"><label class="adm-label" for="p_email">E-posta</label><input type="email" id="p_email" name="email" value="<?= e($me['email']) ?>" maxlength="190"></div>
    </div>
  </section>
  <section class="adm-card">
    <h2>Şifre değiştir</h2>
    <p class="adm-hint">Şifrenizi değiştirmek istemiyorsanız bu alanları boş bırakın.</p>
    <div class="adm-grid">
      <div class="adm-field"><label class="adm-label" for="p_cur">Mevcut şifre</label><input type="password" id="p_cur" name="current_password" autocomplete="current-password"></div>
      <div class="adm-field"><label class="adm-label" for="p_new">Yeni şifre</label><input type="password" id="p_new" name="new_password" autocomplete="new-password" minlength="<?= PROFILE_PASSWORD_MIN ?>"><small class="adm-help">En az <?= PROFILE_PASSWORD_MIN ?> karakter.</small></div>
      <div class="adm-field"><label class="adm-label" for="p_new2">Yeni şifre (tekrar)</label><input type="password" id="p_new2" name="new_password2" autocomplete="new-password"></div>
    </div>
  </section>
  <div class="adm-savebar"><button class="adm-btn adm-btn--primary adm-btn--lg" type="submit"><?= icon('check', 18) ?><span>Kaydet</span></button></div>
</form>
<?php admin_footer(); ?>
