<?php use function App\Core\base_url; ?>
<section>
  <h2>Credit Note <?= htmlspecialchars($sr['sr_no'] ?? '',ENT_QUOTES,'UTF-8') ?></h2>
  <p>Against Invoice: <strong><?= htmlspecialchars($sr['inv_no'] ?? '',ENT_QUOTES,'UTF-8') ?></strong></p>
  <p>
    <a href="<?= base_url('/salesreturns/print?id='.(int)($sr['id'] ?? 0)) ?>">Print</a>
    · <a href="<?= base_url('/invoices/show?id='.(int)($sr['sales_invoice_id'] ?? 0)) ?>">Back to invoice</a>
  </p>

  <table style="width:100%;border-collapse:collapse;margin-top:10px;">
    <thead><tr>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Product</th>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Warehouse</th>
      <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Qty</th>
      <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Price</th>
      <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Line Total</th>
    </tr></thead>
    <tbody>
      <?php $subtotal=0.0; foreach (($items ?? []) as $it): $lt=(float)($it['line_total'] ?? 0); $subtotal+=$lt; ?>
      <tr>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars(($it['product_code'] ?? '').' — '.($it['product_name'] ?? ''),ENT_QUOTES,'UTF-8') ?></td>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($it['warehouse_name'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= (int)($it['qty'] ?? 0) ?></td>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= number_format((float)($it['price'] ?? 0),2) ?></td>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= number_format($lt,2) ?></td>
      </tr>
      <?php endforeach; if (empty($items)): ?>
      <tr><td colspan="5" style="padding:12px;">No lines.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <p style="text-align:right;margin-top:8px;">
    <strong>Total: <?= number_format((float)($sr['total'] ?? $subtotal),2) ?></strong>
  </p>
</section>
