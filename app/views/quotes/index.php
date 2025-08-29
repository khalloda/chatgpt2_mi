<?php
use function App\Core\base_url;
use function App\Core\csrf_field;
use function App\Core\flash_get;
?>
<section>
  <h2>Quotes</h2>

  <?php if ($m = flash_get('success')): ?><div style="background:#e7f8ee;border:1px solid #b9e7c9;padding:10px;border-radius:8px;margin:10px 0;"><?= htmlspecialchars($m, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
  <?php if ($m = flash_get('error')): ?><div style="background:#ffe9e9;border:1px solid #ffb3b3;padding:10px;border-radius:8px;margin:10px 0;"><?= htmlspecialchars($m, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

  <p><a href="<?= base_url('/quotes/create') ?>">+ New Quote</a></p>

  <table style="width:100%;border-collapse:collapse;">
    <thead><tr>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Quote #</th>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Customer</th>
      <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Total</th>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Status</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">Actions</th>
    </tr></thead>
    <tbody>
      <?php foreach ($items as $q): ?>
        <tr>
          <td style="border-bottom:1px solid #f2f2f4;padding:8px;"><?= htmlspecialchars($q['quote_no'], ENT_QUOTES, 'UTF-8') ?></td>
          <td style="border-bottom:1px solid #f2f2f4;padding:8px;"><?= htmlspecialchars($q['customer_name'], ENT_QUOTES, 'UTF-8') ?></td>
          <td style="border-bottom:1px solid #f2f2f4;padding:8px;text-align:right;"><?= number_format((float)$q['total'],2) ?></td>
          <td style="border-bottom:1px solid #f2f2f4;padding:8px;"><?= htmlspecialchars($q['status'], ENT_QUOTES, 'UTF-8') ?></td>
          <td style="border-bottom:1px solid #f2f2f4;padding:8px;">
            <a href="<?= base_url('/quotes/view?id='.(int)$q['id']) ?>">View</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$items): ?><tr><td colspan="5" style="padding:12px;">No quotes yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</section>
