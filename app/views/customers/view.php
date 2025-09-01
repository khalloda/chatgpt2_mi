<?php
use function App\Core\base_url;
/** @var array $customer,$quotes,$orders,$invoices,$payments; @var float $ar_balance,$inv_total,$pay_total,$ret_total */
?>
<section>
  <h2>Customer — <?= htmlspecialchars($customer['name'] ?? '',ENT_QUOTES,'UTF-8') ?></h2>
  <p>
    <strong>Phone:</strong> <?= htmlspecialchars($customer['phone'] ?? '',ENT_QUOTES,'UTF-8') ?> ·
    <strong>Email:</strong> <?= htmlspecialchars($customer['email'] ?? '',ENT_QUOTES,'UTF-8') ?> ·
    <strong>Address:</strong> <?= htmlspecialchars($customer['address'] ?? '',ENT_QUOTES,'UTF-8') ?>
  </p>
  <p>
    <strong>AR Totals</strong> — Invoices: <?= number_format($inv_total,2) ?> ·
    Payments: <?= number_format($pay_total,2) ?> ·
    Credits: <?= number_format($ret_total,2) ?> ·
    <strong>Balance:</strong> <?= number_format($ar_balance,2) ?>
  </p>
  <p>
    <a class="button" href="<?= base_url('/customers/statement?id='.(int)$customer['id']) ?>">View Statement</a>
    · <a href="<?= base_url('/customers') ?>">Back to Customers</a>
  </p>

  <div class="tabs">
    <div class="tabbar">
      <button data-tab="quotes" class="active">Quotes</button>
      <button data-tab="orders">Sales Orders</button>
      <button data-tab="invoices">Invoices</button>
      <button data-tab="payments">Payments</button>
    </div>

    <div class="tabcontent" id="tab-quotes" style="display:block;">
      <table class="grid">
        <thead><tr><th>#</th><th>Date</th><th>Status</th><th class="r">Total</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach ($quotes as $q): ?>
          <tr>
            <td><?= htmlspecialchars($q['q_no'] ?? $q['id'],ENT_QUOTES,'UTF-8') ?></td>
            <td><?= htmlspecialchars($q['created_at'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
            <td><?= htmlspecialchars($q['status'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
            <td class="r"><?= number_format((float)($q['total'] ?? 0),2) ?></td>
            <td><a href="<?= base_url('/quotes/show?id='.(int)$q['id']) ?>">Open</a></td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$quotes): ?><tr><td colspan="5">No quotes.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="tabcontent" id="tab-orders">
      <table class="grid">
        <thead><tr><th>SO #</th><th>Date</th><th>Status</th><th class="r">Total</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
          <tr>
            <td><?= htmlspecialchars($o['so_no'] ?? $o['id'],ENT_QUOTES,'UTF-8') ?></td>
            <td><?= htmlspecialchars($o['created_at'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
            <td><?= htmlspecialchars($o['status'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
            <td class="r"><?= number_format((float)($o['total'] ?? 0),2) ?></td>
            <td><a href="<?= base_url('/orders/show?id='.(int)$o['id']) ?>">Open</a></td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$orders): ?><tr><td colspan="5">No orders.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="tabcontent" id="tab-invoices">
      <table class="grid">
        <thead><tr><th>INV #</th><th>Date</th><th>Status</th><th class="r">Paid</th><th class="r">Total</th><th class="r">Balance</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach ($invoices as $i): ?>
          <?php $bal = (float)$i['total'] - (float)$i['paid_amount']; ?>
          <tr>
            <td><?= htmlspecialchars($i['inv_no'] ?? $i['id'],ENT_QUOTES,'UTF-8') ?></td>
            <td><?= htmlspecialchars($i['created_at'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
            <td><?= htmlspecialchars($i['status'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
            <td class="r"><?= number_format((float)$i['paid_amount'],2) ?></td>
            <td class="r"><?= number_format((float)$i['total'],2) ?></td>
            <td class="r"><?= number_format($bal,2) ?></td>
            <td><a href="<?= base_url('/invoices/show?id='.(int)$i['id']) ?>">Open</a></td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$invoices): ?><tr><td colspan="7">No invoices.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="tabcontent" id="tab-payments">
      <table class="grid">
        <thead><tr><th>Date</th><th>Method</th><th>Ref</th><th>Invoice</th><th class="r">Amount</th></tr></thead>
        <tbody>
          <?php foreach ($payments as $p): ?>
          <tr>
            <td><?= htmlspecialchars($p['paid_at'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
            <td><?= htmlspecialchars($p['method'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
            <td><?= htmlspecialchars($p['reference'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
            <td><a href="<?= base_url('/invoices/show?id='.(int)($p['invoice_id'] ?? 0)) ?>"><?= htmlspecialchars($p['inv_no'] ?? ($p['invoice_id'] ?? ''),ENT_QUOTES,'UTF-8') ?></a></td>
            <td class="r"><?= number_format((float)($p['amount'] ?? 0),2) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$payments): ?><tr><td colspan="5">No payments.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<style>
.tabs .tabbar { display:flex; gap:6px; margin:10px 0; flex-wrap:wrap; }
.tabs .tabbar button { padding:6px 10px; border:1px solid #ddd; background:#fff; border-radius:8px; cursor:pointer; }
.tabs .tabbar button.active { background:#111; color:#fff; border-color:#111; }
.grid { width:100%; border-collapse:collapse; }
.grid th, .grid td { padding:8px; border-bottom:1px solid #eee; text-align:left; }
.grid .r { text-align:right; }
</style>
<script>
(function(){
  const buttons = document.querySelectorAll('.tabbar button');
  const tabs = {
    'quotes': document.getElementById('tab-quotes'),
    'orders': document.getElementById('tab-orders'),
    'invoices': document.getElementById('tab-invoices'),
    'payments': document.getElementById('tab-payments'),
  };
  buttons.forEach(btn=>{
    btn.addEventListener('click', ()=>{
      buttons.forEach(b=>b.classList.remove('active'));
      btn.classList.add('active');
      const target = btn.dataset.tab;
      Object.keys(tabs).forEach(k => tabs[k].style.display = (k===target)?'block':'none');
    });
  });
})();
</script>
