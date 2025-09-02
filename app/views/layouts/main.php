<?php /** @var string $content */ ?>
<?php
use function App\Core\base_url;
use function App\Core\csrf_field;
use function App\Core\auth_check;
use function App\Core\auth_user;
use function App\Core\flash_get;

// Locale & direction (fallback to session → 'en')
$locale = $_SESSION['locale'] ?? 'en';
$is_ar  = ($locale === 'ar');
$dir    = $is_ar ? 'rtl' : 'ltr';

// Helpers
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$page_title = $page_title ?? (function_exists('t') ? t('app.title') : 'MI Spare Parts');

// Current user (if any)
$user = auth_check() ? (auth_user() ?? []) : null;

// Safe base_url wrapper
$u = function (string $path): string {
  $path = '/' . ltrim($path, '/');
  return function_exists('base_url') ? base_url($path) : $path;
};
?>
<!doctype html>
<html lang="<?= $h($locale) ?>" dir="<?= $dir ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title><?= $h($page_title) ?></title>
  <link rel="icon" href="/assets/images/favicon.ico">

  <?php if ($is_ar): ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <?php else: ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <?php endif; ?>
  <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" rel="stylesheet">
  

  <!-- Design tokens & optional UI kits (add these files under /public/assets/ as we planned) -->
  <link href="/assets/css/tokens.css" rel="stylesheet">
  <link href="/assets/css/system.css" rel="stylesheet">
  <link href="/assets/css/tablekit.css" rel="stylesheet">

  <!-- Page-specific CSS hook (optional) -->
  <?= $extra_css ?? '' ?>

  <style>
    /* Minimal fallbacks until system.css/tablekit.css are in place */
    :root{
      --color-bg:#f7f7f9; --color-surface:#fff; --color-text:#111; --muted:#6b7280;
      --border:#e5e7eb; --shadow:0 8px 28px rgba(0,0,0,.10);
    }
    body{ background:var(--color-bg); }
    .app-shell{ min-height:100vh; }
    .app-main{ padding:1rem; }
    .flash { border-radius:.5rem; padding:.75rem 1rem; margin:.5rem 0; }
    .flash-ok { background:#e7f8ee; border:1px solid #b9e7c9; }
    .flash-err{ background:#ffe9e9; border:1px solid #ffb3b3; }
    /* When sidebar is missing, keep old container look as fallback */
    .fallback-container{ max-width:1100px; margin: 1.25rem auto; background:var(--color-surface); border-radius:12px; padding:24px; box-shadow: 0 2px 16px rgba(0,0,0,.06); }
  </style>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
  <script src="/assets/js/app.js" defer></script>
  <script src="/assets/js/tablekit.js" defer></script>

  <!-- Page-specific head scripts hook (optional) -->
  <?= $head_scripts ?? '' ?>
</head>
<body>

  <!-- Top navbar -->
  <header class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center gap-2" href="<?= $u('/') ?>">
        <img src="/assets/images/logo.png" alt="logo" height="28">
        <span class="fw-semibold"><?= $h(function_exists('t') ? t('app.title') : 'Spare Parts App') ?></span>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topnav" aria-controls="topnav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div id="topnav" class="collapse navbar-collapse">
        <form class="ms-auto me-3" role="search" method="get" action="<?= $u('/search') ?>">
          <div class="input-group">
            <input class="form-control" type="search" name="q" placeholder="<?= $h(function_exists('t') ? t('table.search_placeholder') : 'Search…') ?>">
            <button class="btn btn-outline-secondary" type="submit"><i class="ti ti-search"></i></button>
          </div>
        </form>

        <ul class="navbar-nav align-items-lg-center">
          <li class="nav-item me-2">
            <a class="btn btn-sm btn-outline-secondary" href="<?= $u('/locale?lang=en') ?>">EN</a>
          </li>
          <li class="nav-item me-3">
            <a class="btn btn-sm btn-outline-secondary" href="<?= $u('/locale?lang=ar') ?>">AR</a>
          </li>

          <?php if ($user): ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="ti ti-user-circle"></i><span><?= $h($user['email'] ?? $user['name'] ?? 'User') ?></span>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="<?= $u('/profile') ?>"><?= $h('Profile') ?></a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <form method="post" action="<?= $u('/logout') ?>" class="px-3 py-1">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger w-100"><?= $h('Logout') ?></button>
                  </form>
                </li>
              </ul>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a class="btn btn-sm btn-primary" href="<?= $u('/login') ?>"><?= $h('Login') ?></a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </header>

  <div class="app-shell container-fluid">
    <div class="row g-0">
      <!-- Sidebar (if present) -->
      <aside class="col-12 col-md-3 col-lg-2 border-end bg-white d-print-none">
        <?php
          $sidebar_path = __DIR__ . '/../partials/sidebar.php';
          if (file_exists($sidebar_path)) {
            include $sidebar_path;
          } else {
            // Fallback quick links (old layout) if sidebar.php not added yet
            ?>
            <div class="fallback-container">
              <h6 class="mb-3 text-muted">Quick Links</h6>
              <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-sm btn-outline-secondary" href="<?= $u('/') ?>">Home</a>
                <a class="btn btn-sm btn-outline-secondary" href="<?= $u('/health') ?>">Health</a>
                <a class="btn btn-sm btn-outline-secondary" href="<?= $u('/quotes') ?>">Quotes</a>
                <a class="btn btn-sm btn-outline-secondary" href="<?= $u('/salesorders') ?>">Sales Orders</a>
                <a class="btn btn-sm btn-outline-secondary" href="<?= $u('/invoices') ?>">Invoices</a>
                <a class="btn btn-sm btn-outline-secondary" href="<?= $u('/purchaseorders') ?>">POs</a>
                <a class="btn btn-sm btn-outline-secondary" href="<?= $u('/purchaseinvoices') ?>">PIs</a>
                <a class="btn btn-sm btn-outline-secondary" href="<?= $u('/products') ?>">Products</a>
              </div>
            </div>
            <?php
          }
        ?>
      </aside>

      <!-- Main content -->
      <main class="app-main col-12 col-md-9 col-lg-10">
        <!-- Flash messages -->
        <?php if ($m = flash_get('success')): ?>
          <div class="alert alert-success d-flex align-items-center" role="alert">
            <i class="ti ti-check me-2"></i><div><?= $h($m) ?></div>
          </div>
        <?php endif; ?>
        <?php if ($m = flash_get('error')): ?>
          <div class="alert alert-danger d-flex align-items-center" role="alert">
            <i class="ti ti-alert-triangle me-2"></i><div><?= $h($m) ?></div>
          </div>
        <?php endif; ?>

        <!-- Page content -->
        <?= $content ?>
      </main>
    </div>
  </div>

  <!-- Page-tail scripts hook (optional) -->
  <?= $body_scripts ?? '' ?>
</body>
</html>
