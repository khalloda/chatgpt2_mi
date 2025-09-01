<?php use function App\Core\base_url; ?>
<section>
  <h2>Stock Adjustments</h2>
  <p><a class="no-print" href="<?= base_url('/adjustments/create') ?>">New Adjustment</a></p>

  <table style="width:100%;border-collapse:collapse;">
    <thead><tr>
      <th style="border-bottom:1px solid #eee;padding:8px;">AD #</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">Date</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">Warehouse</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">Reason</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">Action</th>
    </tr></thead>
    <tbody>
      <?php foreach (($items ?? []) as $a): ?>
      <tr>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($a['adj_no'],ENT_QUOTES,'UTF-8') ?></td>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($a['created_at'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($a['warehouse_name'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($a['reason'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;">
          <a href="<?= base_url('/adjustments/show?id='.(int)$a['id']) ?>">Open</a> ·
          <a href="<?= base_url('/adjustments/print?id='.(int)$a['id']) ?>">Print</a>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($items)): ?><tr><td colspan="5" style="padding:12px;">No adjustments yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</section>
