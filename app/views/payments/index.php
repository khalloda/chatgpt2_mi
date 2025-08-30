<?php use function App\Core\base_url; ?>
<section>
  <h2>Payments</h2>

  <p><a href="<?= base_url('/invoices') ?>">Back to Invoices</a></p>

  <table style="width:100%;border-collapse:collapse;">
    <thead><tr>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Date</th>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Invoice</th>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Customer</th>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Method</th>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Ref</th>
      <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Amount</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">Actions</th>
    </tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($r['paid_at'], ENT_QUOTES, 'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;">
            <a href="<?= base_url('/invoices/show?id='.(int)$r['invoice_id']) ?>">
              <?= htmlspecialchars($r['inv_no'], ENT_QUOTES, 'UTF-8') ?>
            </a>
          </td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($r['customer_name'], ENT_QUOTES, 'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($r['method'], ENT_QUOTES, 'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($r['reference'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= number_format((float)$r['amount'], 2) ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;">
            <a href="<?= base_url('/payments/create?invoice_id='.(int)$r['invoice_id'].'&_return='.urlencode('/payments')) ?>">New for this invoice</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?>
        <tr><td colspan="7" style="padding:12px;">No payments yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</section>
