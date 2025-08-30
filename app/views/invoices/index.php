<?php use function App\Core\base_url; ?>
<section>
  <h2>Invoices</h2>
  <table style="width:100%;border-collapse:collapse;">
    <thead><tr>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Invoice #</th>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Customer</th>
      <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Total</th>
      <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Paid</th>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Status</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">Actions</th>
    </tr></thead>
    <tbody>
      <?php foreach ($items as $i): ?>
        <tr>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($i['inv_no'],ENT_QUOTES,'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($i['customer_name'],ENT_QUOTES,'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= number_format((float)$i['total'],2) ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= number_format((float)$i['paid_amount'],2) ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($i['status'],ENT_QUOTES,'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;">
            <a href="<?= base_url('/invoices/show?id='.(int)$i['id']) ?>">View</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$items): ?><tr><td colspan="6" style="padding:12px;">No invoices yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</section>
