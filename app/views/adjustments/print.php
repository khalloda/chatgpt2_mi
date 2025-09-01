<?php
use function App\Core\base_url;
/** @var array $a,$items */
?>
<section class="printable">
  <!-- Optional logo: <img src="/public/img/logo.png" alt="Logo" style="height:48px"> -->
  <h2>Stock Adjustment</h2>
  <p><strong>AD #:</strong> <?= htmlspecialchars($a['adj_no'],ENT_QUOTES,'UTF-8') ?><br>
     <strong>Date:</strong> <?= htmlspecialchars($a['created_at'] ?? '',ENT_QUOTES,'UTF-8') ?><br>
     <strong>Warehouse:</strong> <?= htmlspecialchars($a['warehouse_name'],ENT_QUOTES,'UTF-8') ?><br>
     <strong>Reason:</strong> <?= htmlspecialchars($a['reason'],ENT_QUOTES,'UTF-8') ?></p>

  <?php if (!empty($a['note'])): ?>
    <p><strong>Note:</strong> <?= nl2br(htmlspecialchars($a['note'],ENT_QUOTES,'UTF-8')) ?></p>
  <?php endif; ?>

  <table style="width:100%;border-collapse:collapse;margin-top:10px;">
    <thead><tr>
      <th style="text-align:left;border-bottom:1px solid #000;padding:6px;">Product</th>
      <th style="text-align:right;border-bottom:1px solid #000;padding:6px;">Qty change</th>
    </tr></thead>
    <tbody>
      <?php foreach ($items as $it): ?>
      <tr>
        <td style="padding:6px;border-bottom:1px solid #000;"><?= htmlspecialchars(($it['product_code'] ?? '').' — '.($it['product_name'] ?? ''),ENT_QUOTES,'UTF-8') ?></td>
        <td style="padding:6px;border-bottom:1px solid #000;text-align:right;"><?= (int)$it['qty_change'] ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <p class="no-print" style="margin-top:10px;"><button onclick="window.print()">Print</button> · <a href="<?= base_url('/adjustments/show?id='.(int)$a['id']) ?>">Back</a></p>
</section>
<style>
@media print {
  .no-print, nav, header, footer { display:none !important; }
  body { margin: 0; }
}
</style>
