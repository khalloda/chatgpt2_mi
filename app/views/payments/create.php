<?php
use function App\Core\base_url;
use function App\Core\csrf_field;
?>
<section>
  <h2>New Payment</h2>

  <p>Invoice:
    <a href="<?= base_url('/invoices/show?id='.(int)$i['id']) ?>">
      <?= htmlspecialchars($i['inv_no'], ENT_QUOTES, 'UTF-8') ?>
    </a>
    &nbsp;•&nbsp; Customer: <?= htmlspecialchars($i['customer_name'] ?? ('#'.(int)$i['customer_id']), ENT_QUOTES, 'UTF-8') ?>
    &nbsp;•&nbsp; Total: <strong><?= number_format((float)$i['total'], 2) ?></strong>
    &nbsp;•&nbsp; Paid: <strong><?= number_format((float)$i['paid_amount'], 2) ?></strong>
  </p>

  <form method="post" action="<?= base_url('/payments') ?>"
        style="margin-top:10px;display:grid;grid-template-columns: 1fr 1fr 1fr 1fr 1fr; gap:8px; align-items:end; max-width:1000px;">
    <?= csrf_field() ?>
    <input type="hidden" name="invoice_id" value="<?= (int)$i['id'] ?>">
    <input type="hidden" name="_return" value="<?= htmlspecialchars($returnTo, ENT_QUOTES, 'UTF-8') ?>">

    <label>
      <div>Date</div>
      <input type="datetime-local" name="paid_at" value="<?= htmlspecialchars($now, ENT_QUOTES, 'UTF-8') ?>" required
             style="padding:8px;border:1px solid #ddd;border-radius:6px;">
    </label>

    <label>
      <div>Method</div>
      <input type="text" name="method" value="cash" required
             style="padding:8px;border:1px solid #ddd;border-radius:6px;">
    </label>

    <label>
      <div>Reference</div>
      <input type="text" name="reference"
             style="padding:8px;border:1px solid #ddd;border-radius:6px;">
    </label>

    <label>
      <div>Amount</div>
      <input type="number" step="0.01" min="0.01" name="amount" required
             style="padding:8px;border:1px solid #ddd;border-radius:6px;">
    </label>

    <label style="grid-column: 1 / -2;">
      <div>Note</div>
      <input type="text" name="note"
             style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;">
    </label>

    <div>
      <button type="submit"
              style="padding:8px 12px;border:0;border-radius:8px;background:#111;color:#fff;cursor:pointer;">
        Save Payment
      </button>
    </div>
  </form>

  <p style="margin-top:12px;">
    <a href="<?= base_url('/invoices/show?id='.(int)$i['id']) ?>">Back to Invoice</a>
    · <a href="<?= base_url('/payments') ?>">Payments list</a>
  </p>
</section>
