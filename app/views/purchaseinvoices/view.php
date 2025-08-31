<?php
use function App\Core\base_url;
use function App\Core\csrf_field;
use function App\Core\format_note_html;
/** @var array $pi, $items, $received, $receipts, $payments */
?>
<section>
  <h2>Purchase Invoice <?= htmlspecialchars($pi['pi_no'],ENT_QUOTES,'UTF-8') ?></h2>
  <div>Supplier: <strong><?= htmlspecialchars($pi['supplier_name'],ENT_QUOTES,'UTF-8') ?></strong></div>
  <div>PO: <a href="<?= base_url('/purchaseorders/show?id='.(int)$pi['purchase_order_id']) ?>"><?= htmlspecialchars($pi['po_no'],ENT_QUOTES,'UTF-8') ?></a></div>
  <div>Status: <strong><?= htmlspecialchars($pi['status'] ?? 'unpaid',ENT_QUOTES,'UTF-8') ?></strong></div>
  <div>Total: <strong><?= number_format((float)$pi['total'],2) ?></strong>
      &nbsp;| Paid: <strong><?= number_format((float)$pi['paid_amount'],2) ?></strong>
      &nbsp;| Balance: <strong><?= number_format((float)$pi['total'] - (float)$pi['paid_amount'],2) ?></strong></div>
  <p style="margin-top:6px;">
    <a href="<?= base_url('/purchaseinvoices/print?id='.(int)$pi['id']) ?>">Print</a> ·
    <a href="<?= base_url('/receipts/print?invoice_id='.(int)$pi['id']) ?>">Print GRN</a> ·
    <a href="<?= base_url('/purchaseinvoices') ?>">Back</a>
  </p>

  <hr style="margin:12px 0;">

  <h3>Supplier Payments (AP)</h3>
  <table style="width:100%;border-collapse:collapse;">
    <thead><tr>
      <th style="border-bottom:1px solid #eee;padding:8px;">Date</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">Method</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">Ref</th>
      <th style="border-bottom:1px solid #eee;padding:8px;text-align:right;">Amount</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">Actions</th>
    </tr></thead>
    <tbody>
      <?php foreach (($payments ?? []) as $p): ?>
        <tr>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($p['paid_at'],ENT_QUOTES,'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($p['method'],ENT_QUOTES,'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($p['reference'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= number_format((float)$p['amount'],2) ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;">
            <?php if (($pi['status'] ?? 'unpaid') !== 'paid'): ?>
              <form method="post" action="<?= base_url('/supplierpayments/delete') ?>" onsubmit="return confirm('Delete supplier payment?')">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                <input type="hidden" name="invoice_id" value="<?= (int)$pi['id'] ?>">
                <input type="hidden" name="_return" value="/purchaseinvoices/show?id=<?= (int)$pi['id'] ?>">
                <button type="submit" style="border:1px solid #cc0000;color:#cc0000;background:#fff;border-radius:6px;padding:4px 8px;">Delete</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($payments)): ?>
        <tr><td colspan="5" style="padding:8px;">No supplier payments yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <?php if (($pi['status'] ?? 'unpaid') !== 'paid'): ?>
    <form method="post" action="<?= base_url('/supplierpayments') ?>" style="margin-top:10px;display:grid;grid-template-columns: 1fr 1fr 1fr 1fr 1fr; gap:8px; align-items:end;">
      <?= csrf_field() ?>
      <input type="hidden" name="invoice_id" value="<?= (int)$pi['id'] ?>">
      <input type="hidden" name="_return" value="/purchaseinvoices/show?id=<?= (int)$pi['id'] ?>">
      <label><div>Date</div><input type="datetime-local" name="paid_at" required style="padding:8px;border:1px solid #ddd;border-radius:6px;"></label>
      <label><div>Method</div><input type="text" name="method" value="bank" required style="padding:8px;border:1px solid #ddd;border-radius:6px;"></label>
      <label><div>Reference</div><input type="text" name="reference" style="padding:8px;border:1px solid #ddd;border-radius:6px;"></label>
      <label><div>Amount</div><input type="number" step="0.01" min="0.01" name="amount" required style="padding:8px;border:1px solid #ddd;border-radius:6px;"></label>
      <div><button type="submit" style="padding:8px 12px;border:0;border-radius:8px;background:#111;color:#fff;cursor:pointer;">Add Payment</button></div>
    </form>
  <?php else: ?>
    <p style="margin-top:10px;color:#555;">Invoice is fully paid; supplier payments are locked.</p>
  <?php endif; ?>

  <hr style="margin:16px 0;">

  <h3>Items & Receiving</h3>
  <form id="receive-form" method="post" action="<?= base_url('/receipts') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="invoice_id" value="<?= (int)$pi['id'] ?>">
    <table style="width:100%;border-collapse:collapse;">
      <thead><tr>
        <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Product</th>
        <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Warehouse</th>
        <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Ordered</th>
        <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Received</th>
        <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Remaining</th>
        <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Receive now</th>
        <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Price</th>
      </tr></thead>
      <tbody>
        <?php
          $hasRemaining = false;
          foreach ($items as $it):
            $key = $it['product_id'].':'.$it['warehouse_id'];
            $rec = (int)($received[$key] ?? 0);
            $ord = (int)$it['qty'];
            $rem = max(0, $ord - $rec);
            $hasRemaining = $hasRemaining || ($rem > 0);
        ?>
          <tr>
            <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($it['product_code'].' — '.$it['product_name'],ENT_QUOTES,'UTF-8') ?></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($it['warehouse_name'],ENT_QUOTES,'UTF-8') ?></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= (int)$ord ?></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= (int)$rec ?></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><span class="remaining"><?= (int)$rem ?></span></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;">
              <?php if ($rem > 0): ?>
                <input class="rec-qty" type="number" name="rec_qty[]" min="0" max="<?= (int)$rem ?>" step="1" value="0"
                       style="width:90px;padding:6px;border:1px solid #ddd;border-radius:6px;text-align:right;">
              <?php else: ?>
                <span class="muted">—</span>
              <?php endif; ?>
              <input type="hidden" name="rec_product_id[]" value="<?= (int)$it['product_id'] ?>">
              <input type="hidden" name="rec_warehouse_id[]" value="<?= (int)$it['warehouse_id'] ?>">
            </td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;">
              <input type="number" name="rec_price[]" step="0.01" min="0" value="<?= number_format((float)$it['price'],2,'.','') ?>"
                     style="width:110px;padding:6px;border:1px solid #ddd;border-radius:6px;text-align:right;">
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div style="margin-top:10px; display:flex; gap:8px;">
      <button id="btn-fill-remaining" type="button"
              style="padding:8px 12px;border:1px solid #ddd;border-radius:8px;background:#f9f9fb;cursor:pointer;" <?= $hasRemaining ? '' : 'disabled' ?>>
        Receive all remaining
      </button>
      <button type="submit"
              style="padding:8px 12px;border:0;border-radius:8px;background:#111;color:#fff;cursor:pointer;" <?= $hasRemaining ? '' : 'disabled' ?>>
        Post Receipt
      </button>
    </div>
  </form>

  <h3 style="margin-top:18px;">Receipts History</h3>
  <table style="width:100%;border-collapse:collapse;">
    <thead><tr>
      <th style="border-bottom:1px solid #eee;padding:8px;">Date</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">Product</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">Warehouse</th>
      <th style="border-bottom:1px solid #eee;padding:8px;text-align:right;">Qty</th>
      <th style="border-bottom:1px solid #eee;padding:8px;text-align:right;">Price</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">Actions</th>
    </tr></thead>
    <tbody>
      <?php foreach ($receipts as $r): ?>
        <tr>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($r['created_at'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars(($r['product_code'] ?? '').' — '.($r['product_name'] ?? ''),ENT_QUOTES,'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($r['warehouse_name'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= (int)$r['qty'] ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= number_format((float)$r['price'],2) ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;">
            <form method="post" action="<?= base_url('/receipts/delete') ?>" onsubmit="return confirm('Delete this receipt line?');" style="display:inline;">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <input type="hidden" name="invoice_id" value="<?= (int)$pi['id'] ?>">
              <button type="submit" style="border:1px solid #cc0000;color:#cc0000;background:#fff;border-radius:6px;padding:4px 8px;">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$receipts): ?>
        <tr><td colspan="6" style="padding:12px;">No receipts yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <p style="text-align:right;margin-top:10px;">
    Subtotal: <?= number_format((float)$pi['subtotal'],2) ?>
    &nbsp;| Tax (<?= number_format((float)$pi['tax_rate'],2) ?>%): <?= number_format((float)$pi['tax_amount'],2) ?>
    &nbsp;| <strong>Total: <?= number_format((float)$pi['total'],2) ?></strong>
  </p>

  <?php
    // Notes (purchase_invoice)
    $entity_type = 'purchase_invoice';
    $entity_id   = (int)$pi['id'];
    $notes       = $notes ?? [];
    include __DIR__ . '/../partials/notes.php';
  ?>
</section>

<script>
(function(){
  const btn = document.getElementById('btn-fill-remaining');
  if (!btn) return;
  btn.addEventListener('click', function(){
    document.querySelectorAll('table .remaining').forEach(function(span){
      const rem = parseInt(span.textContent || '0', 10) || 0;
      const row = span.closest('tr');
      const input = row ? row.querySelector('.rec-qty') : null;
      if (input && rem > 0) { input.value = rem; }
    });
  });
})();
</script>
