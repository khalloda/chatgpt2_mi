<?php
use function App\Core\base_url;
use function App\Core\csrf_field;
use function App\Core\format_note_html;
/** @var array $i, $items, $payments */

// Load credits total and returns history (controller must pass these; see patch below)
$credits_total = (float)($credits_total ?? 0.0);
$paid_amount   = (float)($i['paid_amount'] ?? 0);
$total         = (float)($i['total'] ?? 0);
$balance       = max(0.0, $total - $paid_amount - $credits_total);
$status        = $i['status'] ?? 'unpaid';
?>
<section>
  <h2>Invoice <?= htmlspecialchars($i['inv_no'],ENT_QUOTES,'UTF-8') ?></h2>
  <div>Status: <strong><?= htmlspecialchars($status,ENT_QUOTES,'UTF-8') ?></strong></div>
  <div>
    Total: <strong><?= number_format($total,2) ?></strong>
    &nbsp;| Paid: <strong><?= number_format($paid_amount,2) ?></strong>
    &nbsp;| Credits: <strong><?= number_format($credits_total,2) ?></strong>
    &nbsp;| Balance: <strong><?= number_format($balance,2) ?></strong>
  </div>

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
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($it['product_code'].' — '.$it['product_name'],ENT_QUOTES,'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($it['warehouse_name'],ENT_QUOTES,'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= (int)$it['qty'] ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= number_format((float)$it['price'],2) ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= number_format((float)$it['line_total'],2) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <p style="text-align:right;margin-top:8px;">
    Subtotal: <?= number_format((float)$i['subtotal'],2) ?>
    &nbsp;| Tax (<?= number_format((float)$i['tax_rate'],2) ?>%): <?= number_format((float)$i['tax_amount'],2) ?>
    &nbsp;| <strong>Total: <?= number_format((float)$i['total'],2) ?></strong>
  </p>

<div class="card" style="margin-top:16px;">
  <div class="card-body">
    <h3 style="margin:0 0 10px 0;">Payments</h3>

    <div style="margin-bottom:10px;color:#374151;">
      <strong>Paid:</strong> <?= number_format((float)($paid ?? 0), 2) ?>
      &nbsp; | &nbsp;
      <strong>Due:</strong> <?= number_format(max(0, (float)$i['total'] - (float)($paid ?? 0)), 2) ?>
    </div>

    <?php if (!empty($payments)): ?>
      <table class="table">
        <thead><tr>
          <th>Date</th><th>Method</th><th>Reference</th><th class="text-end">Amount</th><th></th>
        </tr></thead>
        <tbody>
          <?php foreach ($payments as $p): ?>
            <tr>
              <td><?= htmlspecialchars($p['paid_at'] ?: ($p['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($p['method'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($p['reference'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
              <td class="text-end"><?= number_format((float)$p['amount'], 2) ?></td>
              <td class="text-end">
                <form method="post" action="<?= base_url('/invoices/deletepayment') ?>" onsubmit="return confirm('Delete this payment?');" style="display:inline;">
                  <?= csrf_field() ?>
                  <input type="hidden" name="invoice_id" value="<?= (int)$i['id'] ?>">
                  <input type="hidden" name="payment_id" value="<?= (int)$p['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <div style="color:#6b7280;margin-bottom:10px;">No payments yet.</div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('/invoices/addpayment') ?>" class="row g-2" style="margin-top:10px;">
      <?= csrf_field() ?>
      <input type="hidden" name="invoice_id" value="<?= (int)$i['id'] ?>">
      <div class="col-md-2"><input type="date" name="paid_at" class="form-control" value="<?= date('Y-m-d') ?>"></div>
      <div class="col-md-2"><input type="text" name="method" class="form-control" placeholder="Method (cash/bank)"></div>
      <div class="col-md-3"><input type="text" name="reference" class="form-control" placeholder="Reference"></div>
      <div class="col-md-2"><input type="number" step="0.01" min="0.01" name="amount" class="form-control" placeholder="Amount" required></div>
      <div class="col-md-3"><button type="submit" class="btn btn-primary w-100">Add Payment</button></div>
    </form>
  </div>
</div>	
	
  <hr style="margin:14px 0;">

  <h3>Create Credit Note (Sales Return)</h3>
  <form method="post" action="<?= base_url('/salesreturns') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="invoice_id" value="<?= (int)$i['id'] ?>">
    <table style="width:100%;border-collapse:collapse;">
      <thead><tr>
        <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Product</th>
        <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Warehouse</th>
        <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Invoiced</th>
        <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Returned</th>
        <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Remaining</th>
        <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Return now</th>
        <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Price</th>
      </tr></thead>
      <tbody>
        <?php
          $ret_map = $ret_map ?? []; // 'product:warehouse' => returned so far
          $hasRemain = false;
          foreach ($items as $it):
            $key = $it['product_id'].':'.$it['warehouse_id'];
            $sold = (int)$it['qty'];
            $ret  = (int)($ret_map[$key] ?? 0);
            $rem  = max(0, $sold - $ret);
            $hasRemain = $hasRemain || ($rem > 0);
        ?>
        <tr>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($it['product_code'].' — '.$it['product_name'],ENT_QUOTES,'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($it['warehouse_name'],ENT_QUOTES,'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= $sold ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= $ret ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><span class="sr-rem"><?= $rem ?></span></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;">
            <?php if ($rem > 0): ?>
              <input class="sr-qty" type="number" name="ret_qty[]" min="0" max="<?= $rem ?>" step="1" value="0"
                     style="width:90px;padding:6px;border:1px solid #ddd;border-radius:6px;text-align:right;">
            <?php else: ?>
              <span class="muted">—</span>
            <?php endif; ?>
            <input type="hidden" name="ret_product_id[]" value="<?= (int)$it['product_id'] ?>">
            <input type="hidden" name="ret_warehouse_id[]" value="<?= (int)$it['warehouse_id'] ?>">
          </td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;">
            <input type="number" name="ret_price[]" step="0.01" min="0" value="<?= number_format((float)$it['price'],2,'.','') ?>"
                   style="width:110px;padding:6px;border:1px solid #ddd;border-radius:6px;text-align:right;">
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div style="margin-top:10px;">
      <button type="button" id="btn-fill-all"
              style="padding:8px 12px;border:1px solid #ddd;border-radius:8px;background:#f9f9fb;cursor:pointer;" <?= $hasRemain ? '' : 'disabled' ?>>
        Return all remaining
      </button>
      <button type="submit"
              style="padding:8px 12px;border:0;border-radius:8px;background:#111;color:#fff;cursor:pointer;" <?= $hasRemain ? '' : 'disabled' ?>>
        Create Credit Note
      </button>
    </div>
  </form>

  <h3 style="margin-top:18px;">Returns / Credit Notes</h3>
  <table style="width:100%;border-collapse:collapse;">
    <thead><tr>
      <th style="border-bottom:1px solid #eee;padding:8px;">Credit #</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">Date</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">Product</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">Warehouse</th>
      <th style="border-bottom:1px solid #eee;padding:8px;text-align:right;">Qty</th>
      <th style="border-bottom:1px solid #eee;padding:8px;text-align:right;">Price</th>
    </tr></thead>
    <tbody>
      <?php foreach (($returns ?? []) as $r): ?>
        <tr>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;">
            <a href="<?= base_url('/salesreturns/print?id='.(int)$r['sales_return_id']) ?>"><?= htmlspecialchars($r['sr_no'] ?? '',ENT_QUOTES,'UTF-8') ?></a>
          </td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($r['created_at'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars(($r['product_code'] ?? '').' — '.($r['product_name'] ?? ''),ENT_QUOTES,'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($r['warehouse_name'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= (int)$r['qty'] ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= number_format((float)$r['price'],2) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($returns)): ?>
        <tr><td colspan="6" style="padding:12px;">No credit notes yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <?php
    // Notes (sales_invoice)
    $entity_type = 'sales_invoice';
    $entity_id   = (int)$i['id'];
    $notes       = $notes ?? [];
    include __DIR__ . '/../partials/notes.php';
  ?>
</section>

<script>
(function(){
  const btn = document.getElementById('btn-fill-all');
  if (!btn) return;
  btn.addEventListener('click', function(){
    document.querySelectorAll('.sr-rem').forEach(function(span){
      const rem = parseInt(span.textContent || '0', 10) || 0;
      const row = span.closest('tr');
      const input = row ? row.querySelector('.sr-qty') : null;
      if (input && rem > 0) { input.value = rem; }
    });
  });
})();
</script>
