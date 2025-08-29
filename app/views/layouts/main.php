<?php /** @var string $content */ ?>
<?php
use function App\Core\base_url;
use function App\Core\csrf_field;
use function App\Core\auth_check;
use function App\Core\auth_user;
use function App\Core\flash_get;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Spare Parts App</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin:0; padding:24px; background:#f7f7f9; color:#111; }
    .container { max-width: 980px; margin: 0 auto; background:#fff; border-radius:12px; padding:24px; box-shadow: 0 2px 16px rgba(0,0,0,.06); }
    header { margin-bottom: 16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px;}
    nav a { color:#0a58ca; text-decoration:none; margin-right:10px; }
    .logout-form { display:inline; }
    .logout-form button { background:none; border:none; color:#0a58ca; cursor:pointer; padding:0; }
    code, pre { background:#f2f2f4; padding:2px 6px; border-radius:6px; }
    a { color:#0a58ca; text-decoration: none; }
    .banner { padding:10px;border-radius:8px;margin:10px 0; }
    .banner.ok { background:#e7f8ee;border:1px solid #b9e7c9; }
    .banner.err{ background:#ffe9e9;border:1px solid #ffb3b3; }
  </style>
</head>
<body>
  <div class="container">
    <header>
      <div>
        <h1 style="margin:0 0 4px 0;">Spare Parts Management</h1>
        <nav>
          <a href="<?= base_url('/') ?>">Home</a> ·
          <a href="<?= base_url('/health') ?>">Health</a> ·
		  <a href="<?= base_url('/categories') ?>">Categories</a> ·
		  <a href="<?= base_url('/makes') ?>">Makes</a> ·
		  <a href="<?= base_url('/models') ?>">Models</a> ·
		  <a href="<?= base_url('/warehouses') ?>">Warehouses</a> ·
		  <a href="<?= base_url('/products') ?>">Products</a>

        </nav>
      </div>
      <div>
        <?php if (auth_check()): $u = auth_user(); ?>
          <a href="<?= base_url('/profile') ?>" style="margin-right:8px;">Hello, <?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></a>
          <form class="logout-form" method="post" action="<?= base_url('/logout') ?>">
            <?= csrf_field() ?>
            <button type="submit" title="Log out">Logout</button>
          </form>
        <?php else: ?>
          <a href="<?= base_url('/login') ?>">Login</a>
        <?php endif; ?>
      </div>
    </header>

    <?php if ($m = flash_get('success')): ?>
      <div class="banner ok"><?= htmlspecialchars($m, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if ($m = flash_get('error')): ?>
      <div class="banner err"><?= htmlspecialchars($m, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <main>
      <?= $content ?>
    </main>
  </div>
</body>
</html>
