<?php
use function App\Core\base_url;
use function App\Core\csrf_field;
?>
<section>
  <h2>Sales Order <?= htmlspecialchars($o['so_no'], ENT_QUOTES, 'UTF-8') ?></h2>
  <p>Status: <strong><?= htmlspecialchars($o['status'], ENT_QUOTES, 'UTF-8') ?></strong></p>
  <p>Total: <strong><?= number_format((float)$o['total'], 2) ?></strong></p>

  <!-- Actions toolbar -->
  <div style="margin:6px 0 12px 0; display:flex; gap:8px; flex-wrap:wrap;">
    <form method="post" action="<?= base_url('/invoices/create-from-order') ?>" style="display:inline-block;">
      <?= csrf_field() ?>
      <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
      <button type="submit"
              style="border:1px solid #111;background:#111;color:#fff;border-radius:8px;padding:6px 10px;cursor:pointer;">
        Create Invoice
      </button>
    </form>

    <a class="no-print"
       href="<?= base_url('/orders/print?id='.(int)$o['id']) ?>"
       style="border:1px solid #ddd;border-radius:8px;padding:6px 10px;background:#f9f9fb;text-decoration:none;display:inline-block;">
      Print
    </a>

    <a href="<?= base_url('/orders') ?>"
       style="border:1px solid #ddd;border-radius:8px;padding:6px 10px;background:#fff;text-decoration:none;display:inline-block;">
      Back to Orders
    </a>
  </div>

  <table style="width:100%;border-collapse:collapse;margin-top:10px;">
    <thead>
      <tr>
        <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Product</th>
        <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Warehouse</th>
        <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Qty</th>
        <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Unit Price</th>
        <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Line Total</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $it): ?>
        <tr>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;">
            <?= htmlspecialchars($it['product_code'].' — '.$it['product_name'], ENT_QUOTES, 'UTF-8') ?>
          </td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;">
            <?= htmlspecialchars($it['warehouse_name'], ENT_QUOTES, 'UTF-8') ?>
          </td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;">
            <?= (int)$it['qty'] ?>
          </td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;">
            <?= number_format((float)$it['price'], 2) ?>
          </td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;">
            <?= number_format((float)$it['line_total'], 2) ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <?php
    // Notes (sales_order)
    $entity_type = 'sales_order';
    $entity_id   = (int)$o['id'];
    $notes       = $notes ?? [];
    include __DIR__ . '/../partials/notes.php';
  ?>
</section>
