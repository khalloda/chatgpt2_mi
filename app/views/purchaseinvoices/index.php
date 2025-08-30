<?php use function App\Core\base_url; ?>
<section>
  <h2>Purchase Invoices</h2>
  <p><a href="<?= base_url('/purchaseorders') ?>">Back to Purchase Orders</a></p>

  <table style="width:100%;border-collapse:collapse;">
    <thead><tr>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">PI #</th>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Supplier</th>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">PO #</th>
      <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Total</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">Actions</th>
    </tr></thead>
    <tbody>
      <?php foreach ($items as $pi): ?>
        <tr>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($pi['pi_no'],ENT_QUOTES,'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($pi['supplier_name'],ENT_QUOTES,'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($pi['po_no'],ENT_QUOTES,'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= number_format((float)$pi['total'],2) ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;">
            <a href="<?= base_url('/purchaseinvoices/show?id='.(int)$pi['id']) ?>">View</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$items): ?><tr><td colspan="5" style="padding:12px;">No purchase invoices yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</section>
