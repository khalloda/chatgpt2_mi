<?php
use function App\Core\base_url;
use function App\Core\csrf_field;
?>
<section>
  <h2>Invoice <?= htmlspecialchars($i['inv_no'], ENT_QUOTES, 'UTF-8') ?></h2>
  <div>Status: <strong><?= htmlspecialchars($i['status'], ENT_QUOTES, 'UTF-8') ?></strong></div>
  <div>Total: <strong><?= number_format((float)$i['total'], 2) ?></strong>
      &nbsp;| Paid: <strong><?= number_format((float)$i['paid_amount'], 2) ?></strong>
      &nbsp;| Balance: <strong><?= number_format((float)$i['total'] - (float)$i['paid_amount'], 2) ?></strong></div>

  <p style="margin-top:6px;">
    <a href="<?= base_url('/invoices/print?id='.(int)$i['id']) ?>">Print</a>
  </p>

  <table style="width:100%;border-collapse:collapse;margin-top:10px;">
    <thead><tr>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Product</th>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Warehouse</th>
      <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Qty</th>
      <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Unit Price</th>
      <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Line Total</th>
    </tr></thead>
    <tbody>
      <?php foreach ($items as $it): ?>
        <tr>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($it['product_code'].' — '.$it['product_name'], ENT_QUOTES, 'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($it['warehouse_name'], ENT_QUOTES, 'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= (int)$it['qty'] ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= number_format((float)$it['price'], 2) ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= number_format((float)$it['line_total'], 2) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <p style="text-align:right;margin-top:8px;">
    Subtotal: <?= number_format((float)$i['subtotal'], 2) ?>
    &nbsp;| Tax (<?= number_format((float)$i['tax_rate'], 2) ?>%): <?= number_format((float)$i['tax_amount'], 2) ?>
    &nbsp;| <strong>Total: <?= number_format((float)$i['total'], 2) ?></strong>
  </p>

  <hr style="margin:16px 0;">

  <h3>Payments</h3>
  <table style="width:100%;border-collapse:collapse;">
    <thead><tr>
      <th style="border-bottom:1px solid #eee;padding:8px;">Date</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">Method</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">Ref</th>
      <th style="border-bottom:1px solid #eee;padding:8px;text-align:right;">Amount</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">Actions</th>
    </tr></thead>
    <tbody>
      <?php foreach ($payments as $p): ?>
        <tr>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($p['paid_at'], ENT_QUOTES, 'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($p['method'], ENT_QUOTES, 'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($p['reference'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= number_format((float)$p['amount'], 2) ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;">
            <?php if (($i['status'] ?? '') !== 'paid'): ?>
              <form method="post" action="<?= base_url('/payments/delete') ?>" onsubmit="return confirm('Delete payment?')">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                <input type="hidden" name="invoice_id" value="<?= (int)$i['id'] ?>">
                <input type="hidden" name="_return" value="/invoices/show?id=<?= (int)$i['id'] ?>">
                <button type="submit" style="border:1px solid #cc0000;color:#cc0000;background:#fff;border-radius:6px;padding:4px 8px;">Delete</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$payments): ?>
        <tr><td colspan="5" style="padding:8px;">No payments yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <?php if (($i['status'] ?? '') !== 'paid'): ?>
    <form method="post" action="<?= base_url('/payments') ?>" style="margin-top:10px;display:grid;grid-template-columns: 1fr 1fr 1fr 1fr 1fr; gap:8px; align-items:end;">
      <?= csrf_field() ?>
      <input type="hidden" name="invoice_id" value="<?= (int)$i['id'] ?>">
      <input type="hidden" name="_return" value="/invoices/show?id=<?= (int)$i['id'] ?>">
      <label><div>Date</div><input type="datetime-local" name="paid_at" required style="padding:8px;border:1px solid #ddd;border-radius:6px;"></label>
      <label><div>Method</div><input type="text" name="method" value="cash" required style="padding:8px;border:1px solid #ddd;border-radius:6px;"></label>
      <label><div>Reference</div><input type="text" name="reference" style="padding:8px;border:1px solid #ddd;border-radius:6px;"></label>
      <label><div>Amount</div><input type="number" step="0.01" min="0.01" name="amount" required style="padding:8px;border:1px solid #ddd;border-radius:6px;"></label>
      <div><button type="submit" style="padding:8px 12px;border:0;border-radius:8px;background:#111;color:#fff;cursor:pointer;">Add Payment</button></div>
    </form>
  <?php else: ?>
    <p style="margin-top:10px;color:#555;">Invoice is fully paid; payments are locked.</p>
  <?php endif; ?>

  <?php
    // Notes (sales_invoice)
    $entity_type = 'sales_invoice';
    $entity_id   = (int)$i['id'];
    $notes       = $notes ?? [];
    include __DIR__ . '/../partials/notes.php';
  ?>
</section>
