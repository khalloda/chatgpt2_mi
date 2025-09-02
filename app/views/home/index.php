<?php
/**
 * File: app/views/home/index.php
 * Dashboard home with KPIs + system status
 * - Safe to render even if $kpi is not provided by the controller.
 * - Preserves your previous diagnostics ($db_ok, $debug, $db_error).
 */

use function App\Core\base_url;

$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$U = fn($path) => (function_exists('base_url') ? base_url('/' . ltrim($path, '/')) : '/' . ltrim($path, '/'));
$T = function(string $key, string $fallback){
  return function_exists('t') ? t($key) : $fallback;
};

// KPI defaults (controller can override by passing ['kpi'=>[...]])
$kpi = array_merge([
  'stock_value'   => 0,
  'out_of_stock'  => 0,
  'low_stock'     => 0,
  'quotes_week'   => 0,
  'orders_week'   => 0,
  'invoices_week' => 0,
  'overdue_ar'    => 0,
  'best_seller'   => ['name' => '—', 'qty' => 0],
], $kpi ?? []);

// Legacy diagnostics defaults (from your old view)
$db_ok    = $db_ok    ?? false;
$debug    = $debug    ?? false;
$db_error = $db_error ?? '';
?>

<div class="page-header">
  <div class="title"><?= $T('nav.dashboard','Dashboard') ?></div>
  <nav aria-label="breadcrumb" class="ms-auto">
    <ol class="breadcrumb mb-0">
      <li class="breadcrumb-item active" aria-current="page"><?= $T('nav.dashboard','Dashboard') ?></li>
    </ol>
  </nav>
</div>

<!-- KPIs -->
<section class="kpis">
  <div class="kpi">
    <div class="label"><?= $T('kpi.total_stock_value','Total Stock Value') ?></div>
    <div class="value"><?= $h(number_format((float)$kpi['stock_value'], 2)) ?></div>
  </div>
  <div class="kpi">
    <div class="label"><?= $T('kpi.out_of_stock','Out of Stock') ?></div>
    <div class="value"><?= $h((int)$kpi['out_of_stock']) ?></div>
  </div>
  <div class="kpi">
    <div class="label"><?= $T('kpi.low_stock','Low Stock') ?></div>
    <div class="value"><?= $h((int)$kpi['low_stock']) ?></div>
  </div>
  <div class="kpi">
    <div class="label"><?= $T('kpi.quotes_week','Quotes (This Week)') ?></div>
    <div class="value"><?= $h((int)$kpi['quotes_week']) ?></div>
  </div>
  <div class="kpi">
    <div class="label"><?= $T('kpi.orders_week','Sales Orders (This Week)') ?></div>
    <div class="value"><?= $h((int)$kpi['orders_week']) ?></div>
  </div>
  <div class="kpi">
    <div class="label"><?= $T('kpi.invoices_week','Invoices (This Week)') ?></div>
    <div class="value"><?= $h((int)$kpi['invoices_week']) ?></div>
  </div>
</section>

<div class="row g-3 mt-2">
  <!-- Left: Trends / Activity placeholder -->
  <div class="col-12 col-xl-8">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <strong><?= $h('Activity') ?></strong>
        <div class="text-muted small"><?= $h('Last 30 days') ?></div>
      </div>
      <div class="card-body">
        <p class="text-muted mb-2">
          <?= $h('Charts will appear here (Sales vs. Purchases, Top Products, etc.).') ?>
        </p>
        <div class="empty">
          <i class="ti ti-chart-bar"></i>
          <div class="mt-2"><?= $h('Connect real metrics to replace this placeholder.') ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Right: Alerts / Best seller -->
  <div class="col-12 col-xl-4">
    <div class="card mb-3">
      <div class="card-body d-flex align-items-center gap-3">
        <span class="chip chip-primary"><span class="dot"></span><?= $h('Alerts') ?></span>
        <div>
          <div class="fw-semibold"><?= $h('Low Stock Items') ?></div>
          <div class="text-muted small"><?= $h((int)$kpi['low_stock']) ?> <?= $h('products below threshold') ?></div>
        </div>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-body d-flex align-items-center gap-3">
        <i class="ti ti-star"></i>
        <div>
          <div class="fw-semibold"><?= $T('kpi.best_seller_week','Best Selling (This Week)') ?></div>
          <div class="text-muted small">
            <?= $h($kpi['best_seller']['name']) ?> — <?= $h((int)$kpi['best_seller']['qty']) ?> <?= $h('units') ?>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body d-flex align-items-center gap-3">
        <i class="ti ti-calendar-exclamation"></i>
        <div>
          <div class="fw-semibold"><?= $h('Overdue AR') ?></div>
          <div class="text-muted small"><?= $h((int)$kpi['overdue_ar']) ?> <?= $h('invoices overdue') ?></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- System status (preserves your old checks) -->
<div class="card mt-3">
  <div class="card-header"><strong><?= $h('System Status') ?></strong></div>
  <div class="card-body">
    <p class="mb-2"><?= $h('Router, controller, view, layout are wired.') ?></p>
    <p class="mb-2">
      <?= $h('Database connection:') ?>
      <span class="<?= $db_ok ? 'text-success' : 'text-danger' ?>"><strong><?= $db_ok ? 'OK' : 'FAILED' ?></strong></span>
    </p>

    <?php if (!$db_ok && !empty($debug) && !empty($db_error)): ?>
      <pre class="p-2 rounded bg-light border text-danger" style="white-space:pre-wrap"><?= $h($db_error) ?></pre>
    <?php endif; ?>

    <p class="mb-0">
      <?= $h('Health check:') ?>
      <a href="<?= $U('/health') ?>"><?= $U('/health') ?></a>
    </p>
  </div>
</div>
