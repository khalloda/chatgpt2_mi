<?php use function App\Core\base_url; ?>
<section>
  <h2>Supplier Payments</h2>
  <p><a href="<?= base_url('/purchaseinvoices') ?>">Back to Purchase Invoices</a></p>
  <table style="width:100%;border-collapse:collapse;">
    <thead><tr>
      <th style="border-bottom:1px solid #eee;padding:8px;">Date</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">Supplier</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">PI #</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">Method</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">Ref</th>
      <th style="border-bottom:1px solid #eee;padding:8px;text-align:right;">Amount</th>
    </tr></thead>
    <tbody>
      <?php foreach ($items as $r): ?>
        <tr>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($r['paid_at'],ENT_QUOTES,'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($r['supplier_name'],ENT_QUOTES,'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;">
            <a href="<?= base_url('/purchaseinvoices/show?id='.(int)$r['purchase_invoice_id']) ?>"><?= htmlspecialchars($r['pi_no'],ENT_QUOTES,'UTF-8') ?></a>
          </td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($r['method'],ENT_QUOTES,'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($r['reference'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= number_format((float)$r['amount'],2) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$items): ?><tr><td colspan="6" style="padding:12px;">No supplier payments yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</section>
