<?php /** Giriş ekranı. $error değişkeni admin_login() içinden gelir. */ ?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Giriş – <?= e(opt('site_name')) ?> Yönetim</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap">
<link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body class="adm-login">
  <main class="adm-login__box">
    <?php if (setting('logo') !== ''): ?><img src="<?= e(media(setting('logo'))) ?>" alt="<?= e(opt('site_name')) ?>"><?php else: ?><strong class="adm-login__name"><?= e(opt('site_name')) ?></strong><?php endif; ?>
    <h1>Yönetim Paneli</h1>
    <?php if ($error !== ''): ?><div class="adm-alert adm-alert--err"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="<?= e(admin_url('login')) ?>" autocomplete="on">
      <?= csrf_field() ?>
      <label class="adm-label" for="username">Kullanıcı adı</label>
      <input type="text" id="username" name="username" required autofocus autocomplete="username" maxlength="60">
      <label class="adm-label" for="password">Şifre</label>
      <input type="password" id="password" name="password" required autocomplete="current-password">
      <button class="adm-btn adm-btn--primary adm-btn--block" type="submit"><?= icon('lock', 16) ?><span>Giriş Yap</span></button>
    </form>
    <a class="adm-link" href="<?= e(url()) ?>">← Siteye dön</a>
  </main>
</body>
</html>
