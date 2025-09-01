<?php
use function App\Core\base_url;
/** @var array $t,$items */
?>
<section>
  <h2>Transfer <?= htmlspecialchars($t['tr_no'],ENT_QUOTES,'UTF-8') ?></h2>
  <p>Date: <?= htmlspecialchars($t['created_at'] ?? '',ENT_QUOTES,'UTF-8') ?></p>
  <p>From: <strong><?= htmlspecialchars($t['from_name'],ENT_QUOTES,'UTF-8') ?></strong>
     → To: <strong><?= htmlspecialchars($t['to_name'],ENT_QUOTES,'UTF-8') ?></strong></p>
  <?php if (!empty($t['note'])): ?>
    <p><strong>Note:</strong> <?= nl2br(htmlspecialchars($t['note'],ENT_QUOTES,'UTF-8')) ?></p>
  <?php endif; ?>

  <table style="width:100%;border-collapse:collapse;margin-top:10px;">
    <thead><tr>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Product</th>
      <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Qty</th>
    </tr></thead>
    <tbody>
      <?php foreach ($items as $it): ?>
      <tr>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars(($it['product_code'] ?? '').' — '.($it['product_name'] ?? ''),ENT_QUOTES,'UTF-8') ?></td>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= (int)$it['qty'] ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$items): ?><tr><td colspan="2" style="padding:12px;">No lines.</td></tr><?php endif; ?>
    </tbody>
  </table>

  <p style="margin-top:10px;">
    <a href="<?= base_url('/transfers') ?>">Back</a> ·
    <a href="<?= base_url('/transfers/print?id='.(int)$t['id']) ?>">Print</a>
  </p>
</section>
