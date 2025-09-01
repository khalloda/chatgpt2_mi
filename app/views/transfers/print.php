<?php
use function App\Core\base_url;
/** @var array $t,$items */
?>
<section class="printable">
  <!-- Optional logo: <img src="/public/img/logo.png" alt="Logo" style="height:48px"> -->
  <h2>Stock Transfer</h2>
  <p><strong>TR #:</strong> <?= htmlspecialchars($t['tr_no'],ENT_QUOTES,'UTF-8') ?><br>
     <strong>Date:</strong> <?= htmlspecialchars($t['created_at'] ?? '',ENT_QUOTES,'UTF-8') ?><br>
     <strong>From:</strong> <?= htmlspecialchars($t['from_name'],ENT_QUOTES,'UTF-8') ?> →
     <strong>To:</strong> <?= htmlspecialchars($t['to_name'],ENT_QUOTES,'UTF-8') ?></p>

  <?php if (!empty($t['note'])): ?>
    <p><strong>Note:</strong> <?= nl2br(htmlspecialchars($t['note'],ENT_QUOTES,'UTF-8')) ?></p>
  <?php endif; ?>

  <table style="width:100%;border-collapse:collapse;margin-top:10px;">
    <thead><tr>
      <th style="text-align:left;border-bottom:1px solid #000;padding:6px;">Product</th>
      <th style="text-align:right;border-bottom:1px solid #000;padding:6px;">Qty</th>
    </tr></thead>
    <tbody>
      <?php foreach ($items as $it): ?>
      <tr>
        <td style="padding:6px;border-bottom:1px solid #000;"><?= htmlspecialchars(($it['product_code'] ?? '').' — '.($it['product_name'] ?? ''),ENT_QUOTES,'UTF-8') ?></td>
        <td style="padding:6px;border-bottom:1px solid #000;text-align:right;"><?= (int)$it['qty'] ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <p class="no-print" style="margin-top:10px;"><button onclick="window.print()">Print</button> · <a href="<?= base_url('/transfers/show?id='.(int)$t['id']) ?>">Back</a></p>
</section>
<style>
@media print {
  .no-print, nav, header, footer { display:none !important; }
  body { margin: 0; }
}
</style>
