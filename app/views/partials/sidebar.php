<?php
/**
 * File: app/views/partials/sidebar.php
 * Purpose: Left sidebar navigation (Admin) with Tabler icons, RTL-aware, and active state handling
 */

use function App\Core\auth_user;
use function App\Core\base_url;

/* --- Safe helpers --- */
// str_starts_with polyfill for PHP < 8 (harmless on PHP 8+)
if (!function_exists('str_starts_with')) {
  function str_starts_with($haystack, $needle) {
    return $needle === '' || strpos($haystack, $needle) === 0;
  }
}
// Safe translator: 1 or 2 args OK
$T = function(string $key, string $fallback = null) {
  if (function_exists('t')) {
    $val = t($key);
    if ($val !== null && $val !== '') return $val;
  }
  return $fallback ?? $key;
};

$current_user   = function_exists('auth_user') ? auth_user() : ($_SESSION['user'] ?? null);
$current_locale = $_SESSION['locale'] ?? ($_GET['lang'] ?? 'en'); // fallback
$is_rtl         = ($current_locale === 'ar');

/** Get the current request path (no query/hash) */
$req_path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

/** Build URLs (use base_url() if available) */
$u = function (string $path): string {
    $path = '/' . ltrim($path, '/');
    return function_exists('base_url') ? base_url($path) : $path;
};

/** Returns true if current path starts with any of the given prefixes */
$is_active = function ($prefix) use ($req_path): bool {
    $prefixes = is_array($prefix) ? $prefix : [$prefix];
    foreach ($prefixes as $p) {
        $p = '/' . ltrim($p, '/');
        if ($p !== '/' && str_starts_with($req_path, $p)) return true;
        if ($p === '/' && $req_path === '/') return true;
    }
    return false;
};

/** Generate a unique id */
$uid = function(string $seed): string { return 'sb_' . preg_replace('~[^a-z0-9]+~', '_', strtolower($seed)) . '_' . substr(md5($seed), 0, 6); };

/** Menu definition (Admin) */
$menu = [
    [
        'type'  => 'link',
        'icon'  => 'ti ti-layout-dashboard',
        'label' => $T('nav.dashboard','Dashboard'),
        'href'  => $u('/'),
        'match' => ['/'],
    ],

    // Sales
    [
        'type'  => 'group',
        'icon'  => 'ti ti-cash',
        'label' => $T('nav.sales','Sales'),
        'match' => ['/quotes','/orders','/invoices','/salesreturns','/payments'],
        'items' => [
            ['icon'=>'ti ti-file-invoice','label'=>'Quotes',        'href'=>$u('/quotes'),        'match'=>'/quotes'],
            ['icon'=>'ti ti-shopping-cart','label'=>'Sales Orders', 'href'=>$u('/orders'),  'match'=>'/orders'],
            ['icon'=>'ti ti-receipt-2',    'label'=>'Invoices',     'href'=>$u('/invoices'),     'match'=>'/invoices'],
            ['icon'=>'ti ti-rotate-rectangle','label'=>'Sales Returns','href'=>$u('/salesreturns'),'match'=>'/salesreturns'],
            ['icon'=>'ti ti-wallet','label'=>'Payments In (AR)',    'href'=>$u('/payments'),   'match'=>'/payments'],
        ],
    ],

    // Purchasing
    [
        'type'  => 'group',
        'icon'  => 'ti ti-truck-delivery',
        'label' => $T('nav.purchasing','Purchasing'),
        'match' => ['/suppliers','/purchaseorders','/purchaseinvoices','/goodsreceipts','/purchasereturns', '/supplierpayments'],
        'items' => [
            ['icon'=>'ti ti-users','label'=>'Suppliers',            'href'=>$u('/suppliers'),         'match'=>'/suppliers'],
            ['icon'=>'ti ti-file-description','label'=>'Purchase Orders','href'=>$u('/purchaseorders'),'match'=>'/purchaseorders'],
            ['icon'=>'ti ti-receipt','label'=>'Purchase Invoices',  'href'=>$u('/purchaseinvoices'),  'match'=>'/purchaseinvoices'],
            ['icon'=>'ti ti-download','label'=>'Goods Receipts',    'href'=>$u('/goodsreceipts'),     'match'=>'/goodsreceipts'],
            ['icon'=>'ti ti-rotate-rectangle','label'=>'Purchase Returns','href'=>$u('/purchasereturns'),'match'=>'/purchasereturns'],
			['icon'=>'ti ti-wallet','label'=>'Payments In (AP)',    'href'=>$u('/supplierpayments'),   'match'=>'/supplierpayments'],
        ],
    ],

    // Inventory
    [
        'type'  => 'group',
        'icon'  => 'ti ti-archive',
        'label' => $T('nav.inventory','Inventory'),
        'match' => ['/products','/categories','/makes','/models','/warehouses','/transfers','/adjustments','/reservations','/lowstock'],
        'items' => [
            ['icon'=>'ti ti-box','label'=>'Products',        'href'=>$u('/products'),       'match'=>'/products'],
            ['icon'=>'ti ti-category-2','label'=>'Categories','href'=>$u('/categories'),    'match'=>'/categories'],
            ['icon'=>'ti ti-steering-wheel','label'=>'Makes','href'=>$u('/makes'),          'match'=>'/makes'],
            ['icon'=>'ti ti-car','label'=>'Models',          'href'=>$u('/models'),         'match'=>'/models'],
            ['icon'=>'ti ti-building-warehouse','label'=>'Warehouses','href'=>$u('/warehouses'),'match'=>'/warehouses'],
            ['icon'=>'ti ti-arrows-exchange','label'=>'Transfers','href'=>$u('/transfers'), 'match'=>'/transfers'],
            ['icon'=>'ti ti-adjustments-alt','label'=>'Adjustments','href'=>$u('/adjustments'), 'match'=>'/adjustments'],
            ['icon'=>'ti ti-bookmark','label'=>'Reservations','href'=>$u('/reservations'),  'match'=>'/reservations'],
            ['icon'=>'ti ti-bell','label'=>'Low Stock','href'=>$u('/lowstock'),             'match'=>'/lowstock'],
        ],
    ],

    // CRM
    [
        'type'  => 'group',
        'icon'  => 'ti ti-address-book',
        'label' => $T('nav.crm','CRM'),
        'match' => ['/customers','/contacts'],
        'items' => [
            ['icon'=>'ti ti-user','label'=>'Clients', 'href'=>$u('/customers'), 'match'=>'/customers'],
            ['icon'=>'ti ti-address-book','label'=>'Contacts', 'href'=>$u('/contacts'), 'match'=>'/contacts'],
        ],
    ],

    // Reports
    [
        'type'  => 'group',
        'icon'  => 'ti ti-report',
        'label' => $T('nav.reports','Reports'),
        'match' => ['/reports'],
        'items' => [
            ['icon'=>'ti ti-report-money','label'=>'Sales',       'href'=>$u('/reports/sales'),     'match'=>'/reports/sales'],
            ['icon'=>'ti ti-report-analytics','label'=>'Purchasing','href'=>$u('/reports/purchasing'),'match'=>'/reports/purchasing'],
            ['icon'=>'ti ti-report-search','label'=>'Inventory',  'href'=>$u('/reports/inventory-valuation'), 'match'=>'/reports/inventory-valuation'],
            ['icon'=>'ti ti-currency-dollar','label'=>'AR',       'href'=>$u('/reports/ar-aging'),        'match'=>'/reports/ar-aging'],
            ['icon'=>'ti ti-currency-dollar-off','label'=>'AP',   'href'=>$u('/reports/ap-aging'),        'match'=>'/reports/ap-aging'],
        ],
    ],

    // Settings
    [
        'type'  => 'group',
        'icon'  => 'ti ti-settings',
        'label' => $T('nav.settings','Settings'),
        'match' => ['/users','/roles','/settings/tax-currency','/settings/units-sequences','/translations','/notifications','/integrations'],
        'items' => [
            ['icon'=>'ti ti-users','label'=>'Users & Roles',      'href'=>$u('/users'),                   'match'=>'/users'],
            ['icon'=>'ti ti-calculator','label'=>'Taxes & Currency','href'=>$u('/settings/tax-currency'), 'match'=>'/settings/tax-currency'],
            ['icon'=>'ti ti-ruler-measure','label'=>'Units & Sequences','href'=>$u('/settings/units-sequences'),'match'=>'/settings/units-sequences'],
            ['icon'=>'ti ti-language','label'=>'Translations',    'href'=>$u('/translations'),            'match'=>'/translations'],
            ['icon'=>'ti ti-bell','label'=>'Notifications',       'href'=>$u('/notifications'),           'match'=>'/notifications'],
            ['icon'=>'ti ti-plug-connected','label'=>'Integrations','href'=>$u('/integrations'),          'match'=>'/integrations'],
        ],
    ],

    // Tools
    [
        'type'  => 'group',
        'icon'  => 'ti ti-tool',
        'label' => $T('nav.tools','Tools'),
        'match' => ['/import','/backups','/audit','/health'],
        'items' => [
            ['icon'=>'ti ti-database-import','label'=>'Import/Export','href'=>$u('/import'),  'match'=>'/import'],
            ['icon'=>'ti ti-database','label'=>'Backups',            'href'=>$u('/backups'), 'match'=>'/backups'],
            ['icon'=>'ti ti-list-details','label'=>'Audit Log',      'href'=>$u('/audit'),   'match'=>'/audit'],
            ['icon'=>'ti ti-heartbeat','label'=>'System Health',     'href'=>$u('/health'),  'match'=>'/health'],
        ],
    ],
];

/** Render helpers */
$render_link = function(array $link) use ($is_active) {
    $active = $is_active($link['match'] ?? ($link['href'] ?? ''));
    $cls = 'nav-link d-flex align-items-center gap-2'.($active ? ' active' : '');
    $aria = $active ? ' aria-current="page"' : '';
    $icon = $link['icon'] ?? 'ti ti-dot';
    $label= $link['label'] ?? '';
    $href = $link['href'] ?? '#';
    echo '<li class="nav-item"><a class="'.$cls.'" href="'.htmlspecialchars($href,ENT_QUOTES).'"'.$aria.'>'.
         '<i class="'.htmlspecialchars($icon,ENT_QUOTES).' fs-5"></i>'.
         '<span class="nav-link-title">'.htmlspecialchars($label).'</span>'.
         '</a></li>';
};

$render_group = function(array $group) use ($render_link, $is_active, $uid) {
    $open  = $is_active($group['match'] ?? []);
    $gid   = $uid($group['label'] ?? 'group');
    $icon  = $group['icon'] ?? 'ti ti-folder';
    $label = $group['label'] ?? '';
    $btn   = 'btn btn-toggle align-items-center rounded collapsed';
    if ($open) $btn .= ' show';
    echo '<li class="nav-item">';
    echo '  <button class="'.$btn.'" data-bs-toggle="collapse" data-bs-target="#'.$gid.'" aria-expanded="'.($open?'true':'false').'">';
    echo '    <i class="'.htmlspecialchars($icon,ENT_QUOTES).' fs-5 me-2"></i><span>'.$label.'</span>';
    echo '  </button>';
    echo '  <div class="collapse'.($open?' show':'').'" id="'.$gid.'">';
    echo '    <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">';
    foreach (($group['items'] ?? []) as $link) $render_link($link);
    echo '    </ul>';
    echo '  </div>';
    echo '</li>';
};
?>
<!-- Sidebar -->
<aside class="sidebar" style="min-height:100vh;">
  <div class="p-3 d-flex align-items-center gap-2 border-bottom">
    <a href="<?= $u('/') ?>" class="d-inline-flex align-items-center text-decoration-none">
      <img src="/assets/images/logo.png" alt="logo" height="32" class="me-2">
      <span class="fw-semibold">MI Spare Parts</span>
    </a>
    <span class="ms-auto small text-muted"><?= htmlspecialchars(strtoupper($current_locale)) ?></span>
  </div>

  <nav class="p-2">
    <ul class="nav nav-pills flex-column gap-1">
      <?php foreach ($menu as $item): ?>
        <?php if (($item['type'] ?? '') === 'link') { $render_link($item); } ?>
        <?php if (($item['type'] ?? '') === 'group') { $render_group($item); } ?>
      <?php endforeach; ?>
    </ul>
  </nav>

  <div class="mt-auto p-3 small text-muted border-top">
    <div class="d-flex align-items-center">
      <i class="ti ti-user-circle me-2"></i>
      <div class="flex-grow-1">
        <div class="fw-semibold"><?= htmlspecialchars($current_user['name'] ?? 'Admin') ?></div>
        <div class="text-muted"><?= htmlspecialchars($current_user['role'] ?? 'admin') ?></div>
      </div>
      <div class="ms-2">
        <a class="btn btn-sm btn-outline-secondary" href="<?= $u('/locale?lang=en') ?>">EN</a>
        <a class="btn btn-sm btn-outline-secondary" href="<?= $u('/locale?lang=ar') ?>">AR</a>
      </div>
    </div>
  </div>
</aside>
