<?php
use function App\Core\base_url;
/** @var array $supplier,$po_list,$receipt_items,$pi_list,$spayments; @var float $ap_balance,$inv_total,$pay_total,$ret_total */
?>
<section>
  <h2>Supplier — <?= htmlspecialchars($supplier['name'] ?? '',ENT_QUOTES,'UTF-8') ?></h2>
  <p>
    <strong>Phone:</strong> <?= htmlspecialchars($supplier['phone'] ?? '',ENT_QUOTES,'UTF-8') ?> ·
    <strong>Email:</strong> <?= htmlspecialchars($supplier['email'] ?? '',ENT_QUOTES,'UTF-8') ?> ·
    <strong>Address:</strong> <?= htmlspecialchars($supplier['address'] ?? '',ENT_QUOTES,'UTF-8') ?>
  </p>
  <p>
    <strong>AP Totals</strong> — Invoices: <?= number_format($inv_total,2) ?> ·
    Payments: <?= number_format($pay_total,2) ?> ·
    Credits: <?= number_format($ret_total,2) ?> ·
    <strong>Balance:</strong> <?= number_format($ap_balance,2) ?>
  </p>
  <p>
    <a class="button" href="<?= base_url('/suppliers/statement?id='.(int)$supplier['id']) ?>">View Statement</a>
    · <a href="<?= base_url('/suppliers') ?>">Back to Suppliers</a>
  </p>

  <div class="tabs">
    <div class="tabbar">
      <button data-tab="pos" class="active">Purchase Orders</button>
      <button data-tab="receipts">Delivered Items (Receipts)</button>
      <button data-tab="pis">Purchase Invoices</button>
      <button data-tab="payments">Payments (AP)</button>
    </div>

    <div class="tabcontent" id="tab-pos" style="display:block;">
      <table class="grid">
        <thead><tr>
          <th>PO #</th><th>Date</th><th>Status</th><th class="r">Total</th><th>Action</th>
        </tr></thead>
        <tbody>
          <?php foreach ($po_list as $po): ?>
          <tr>
            <td><?= htmlspecialchars($po['po_no'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
            <td><?= htmlspecialchars($po['created_at'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
            <td><?= htmlspecialchars($po['status'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
            <td class="r"><?= number_format((float)$po['total'],2) ?></td>
            <td><a href="<?= base_url('/purchaseorders/show?id='.(int)$po['id']) ?>">Open</a></td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$po_list): ?><tr><td colspan="5">No purchase orders.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="tabcontent" id="tab-receipts">
      <table class="grid">
        <thead><tr>
          <th>Date</th><th>PI #</th><th>Product</th><th>Warehouse</th><th class="r">Qty</th><th class="r">Price</th><th class="r">Line Total</th>
        </tr></thead>
        <tbody>
          <?php foreach ($receipt_items as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['created_at'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
            <td><a href="<?= base_url('/purchaseinvoices/show?id='.(int)$r['purchase_invoice_id']) ?>"><?= htmlspecialchars($r['pi_no'] ?? '',ENT_QUOTES,'UTF-8') ?></a></td>
            <td><?= htmlspecialchars(($r['product_code'] ?? '').' — '.($r['product_name'] ?? ''),ENT_QUOTES,'UTF-8') ?></td>
            <td><?= htmlspecialchars($r['warehouse_name'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
            <td class="r"><?= (int)($r['qty'] ?? 0) ?></td>
            <td class="r"><?= number_format((float)($r['price'] ?? 0),2) ?></td>
            <td class="r"><?= number_format((float)($r['line_total'] ?? 0),2) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$receipt_items): ?><tr><td colspan="7">No receipts.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="tabcontent" id="tab-pis">
      <table class="grid">
        <thead><tr>
          <th>PI #</th><th>Date</th><th>Status</th><th class="r">Paid</th><th class="r">Total</th><th class="r">Balance</th><th>Action</th>
        </tr></thead>
        <tbody>
          <?php foreach ($pi_list as $pi): ?>
          <?php $bal = (float)$pi['total'] - (float)$pi['paid_amount']; ?>
          <tr>
            <td><?= htmlspecialchars($pi['pi_no'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
            <td><?= htmlspecialchars($pi['created_at'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
            <td><?= htmlspecialchars($pi['status'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
            <td class="r"><?= number_format((float)$pi['paid_amount'],2) ?></td>
            <td class="r"><?= number_format((float)$pi['total'],2) ?></td>
            <td class="r"><?= number_format($bal,2) ?></td>
            <td><a href="<?= base_url('/purchaseinvoices/show?id='.(int)$pi['id']) ?>">Open</a></td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$pi_list): ?><tr><td colspan="7">No purchase invoices.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="tabcontent" id="tab-payments">
      <table class="grid">
        <thead><tr>
          <th>Date</th><th>Method</th><th>Reference</th><th class="r">Amount</th>
        </tr></thead>
        <tbody>
          <?php foreach ($spayments as $p): ?>
          <tr>
            <td><?= htmlspecialchars($p['paid_at'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
            <td><?= htmlspecialchars($p['method'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
            <td><?= htmlspecialchars($p['reference'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
            <td class="r"><?= number_format((float)($p['amount'] ?? 0),2) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$spayments): ?><tr><td colspan="4">No payments.</td></tr><?php endif; ?>
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
    'pos': document.getElementById('tab-pos'),
    'receipts': document.getElementById('tab-receipts'),
    'pis': document.getElementById('tab-pis'),
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
