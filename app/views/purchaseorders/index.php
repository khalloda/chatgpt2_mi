<?php use function App\Core\base_url; ?>
<section>
  <h2>Purchase Orders</h2>
  <p><a href="<?= base_url('/purchaseorders/create') ?>">New PO</a></p>

  <table style="width:100%;border-collapse:collapse;">
    <thead><tr>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">PO #</th>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Supplier</th>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Status</th>
      <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Total</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">Actions</th>
    </tr></thead>
    <tbody>
      <?php foreach ($items as $po): ?>
        <tr>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($po['po_no'],ENT_QUOTES,'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($po['supplier_name'],ENT_QUOTES,'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($po['status'],ENT_QUOTES,'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= number_format((float)$po['total'],2) ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;">
            <a href="<?= base_url('/purchaseorders/show?id='.(int)$po['id']) ?>">View</a>
            <?php if (($po['status'] ?? '') === 'draft'): ?>
              · <a href="<?= base_url('/purchaseorders/edit?id='.(int)$po['id']) ?>">Edit</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$items): ?><tr><td colspan="5" style="padding:12px;">No purchase orders yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</section>
