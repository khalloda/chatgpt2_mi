<?php use function App\Core\base_url; ?>
<section>
  <h2>Sales Order <?= htmlspecialchars($o['so_no'],ENT_QUOTES,'UTF-8') ?></h2>
  <p>Status: <strong><?= htmlspecialchars($o['status'],ENT_QUOTES,'UTF-8') ?></strong></p>
  <p>Total: <strong><?= number_format((float)$o['total'],2) ?></strong></p>

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

  <p style="margin-top:12px;"><a href="<?= base_url('/orders') ?>">Back to Orders</a></p>
</section>
