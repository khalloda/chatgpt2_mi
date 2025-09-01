<?php use function App\Core\base_url; ?>
<section>
  <h2>Stock Transfers</h2>
  <p><a class="no-print" href="<?= base_url('/transfers/create') ?>">New Transfer</a></p>

  <table style="width:100%;border-collapse:collapse;">
    <thead><tr>
      <th style="border-bottom:1px solid #eee;padding:8px;">TR #</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">Date</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">From</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">To</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">Action</th>
    </tr></thead>
    <tbody>
      <?php foreach (($items ?? []) as $t): ?>
      <tr>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($t['tr_no'],ENT_QUOTES,'UTF-8') ?></td>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($t['created_at'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($t['from_name'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($t['to_name'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;">
          <a href="<?= base_url('/transfers/show?id='.(int)$t['id']) ?>">Open</a> ·
          <a href="<?= base_url('/transfers/print?id='.(int)$t['id']) ?>">Print</a>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($items)): ?><tr><td colspan="5" style="padding:12px;">No transfers yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</section>
