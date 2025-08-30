<?php
use function App\Core\base_url;
use function App\Core\csrf_field;
/** @var array $po, $items */
?>
<section>
  <h2>PO <?= htmlspecialchars($po['po_no'],ENT_QUOTES,'UTF-8') ?></h2>
  <div>Supplier: <strong><?= htmlspecialchars($po['supplier_name'],ENT_QUOTES,'UTF-8') ?></strong></div>
  <div>Status: <strong><?= htmlspecialchars($po['status'],ENT_QUOTES,'UTF-8') ?></strong></div>
  <p>
    <a href="<?= base_url('/purchaseorders/print?id='.(int)$po['id']) ?>">Print</a>
    <?php if (($po['status'] ?? '')==='draft'): ?>
      · <a href="<?= base_url('/purchaseorders/edit?id='.(int)$po['id']) ?>">Edit</a>
    <?php endif; ?>
    · <a href="<?= base_url('/purchaseorders') ?>">Back</a>
  </p>

  <?php if (($po['status'] ?? '')==='draft'): ?>
    <form method="post" action="<?= base_url('/purchaseorders/mark-ordered') ?>" style="margin:8px 0;">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int)$po['id'] ?>">
      <button type="submit" style="padding:6px 10px;border:1px solid #111;border-radius:8px;background:#111;color:#fff;cursor:pointer;">Mark as Ordered</button>
    </form>
  <?php endif; ?>

  <table style="width:100%;border-collapse:collapse;margin-top:10px;">
    <thead><tr>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Product</th>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Warehouse</th>
      <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Qty</th>
      <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Price</th>
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
    Subtotal: <?= number_format((float)$po['subtotal'],2) ?>
    &nbsp;| Tax (<?= number_format((float)$po['tax_rate'],2) ?>%): <?= number_format((float)$po['tax_amount'],2) ?>
    &nbsp;| <strong>Total: <?= number_format((float)$po['total'],2) ?></strong>
  </p>
<?php if (($po['status'] ?? '') !== 'closed'): ?>
<form method="post" action="<?= base_url('/purchaseinvoices/create-from-po') ?>" style="display:inline-block;margin-right:8px;">
  <?= csrf_field() ?>
  <input type="hidden" name="purchase_order_id" value="<?= (int)$po['id'] ?>">
  <button type="submit" style="padding:6px 10px;border:1px solid #111;border-radius:8px;background:#111;color:#fff;cursor:pointer;">
    Create Purchase Invoice
  </button>
</form>
<?php endif; ?>
  <?php
    // Notes (purchase_order)
    $entity_type = 'purchase_order';
    $entity_id   = (int)$po['id'];
    $notes       = $notes ?? [];
    include __DIR__ . '/../partials/notes.php';
  ?>
</section>
