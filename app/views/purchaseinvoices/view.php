<?php
use function App\Core\base_url;
use function App\Core\csrf_field;
use function App\Core\format_note_html;
/** @var array $pi, $items, $received */
?>
<section>
  <h2>Purchase Invoice <?= htmlspecialchars($pi['pi_no'],ENT_QUOTES,'UTF-8') ?></h2>
  <div>Supplier: <strong><?= htmlspecialchars($pi['supplier_name'],ENT_QUOTES,'UTF-8') ?></strong></div>
  <div>PO: <a href="<?= base_url('/purchaseorders/show?id='.(int)$pi['purchase_order_id']) ?>"><?= htmlspecialchars($pi['po_no'],ENT_QUOTES,'UTF-8') ?></a></div>
  <div>Total: <strong><?= number_format((float)$pi['total'],2) ?></strong></div>
  <p style="margin-top:6px;">
    <a href="<?= base_url('/purchaseinvoices/print?id='.(int)$pi['id']) ?>">Print</a> ·
    <a href="<?= base_url('/purchaseinvoices') ?>">Back to Purchase Invoices</a>
  </p>

  <h3>Items & Receiving</h3>
  <form method="post" action="<?= base_url('/receipts') ?>">
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
            <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= (int)$rem ?></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;">
              <?php if ($rem > 0): ?>
                <input type="number" name="rec_qty[]" min="0" max="<?= (int)$rem ?>" step="1" value="0"
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

    <div style="margin-top:10px;">
      <button type="submit" style="padding:8px 12px;border:0;border-radius:8px;background:#111;color:#fff;cursor:pointer;" <?= $hasRemaining ? '' : 'disabled' ?>>
        Post Receipt
      </button>
    </div>
  </form>

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
