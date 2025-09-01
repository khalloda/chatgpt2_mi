<?php /** @var array $rows,$tot */ use function App\Core\base_url; ?>
<section>
  <h2>Inventory Valuation (Weighted Avg)</h2>

  <p class="no-print" style="margin:8px 0;">
    <a href="<?= base_url('/') ?>" style="margin-right:12px;">Back</a>
    <button onclick="window.print()" style="padding:6px 10px;border:1px solid #111;border-radius:8px;background:#fff;cursor:pointer;">Print</button>
  </p>

  <table style="width:100%;border-collapse:collapse;">
    <thead>
      <tr>
        <th style="border-bottom:1px solid #eee;padding:8px;text-align:left;">Warehouse</th>
        <th style="border-bottom:1px solid #eee;padding:8px;text-align:left;">Product</th>
        <th style="border-bottom:1px solid #eee;padding:8px;text-align:right;">On hand</th>
        <th style="border-bottom:1px solid #eee;padding:8px;text-align:right;">Avg cost</th>
        <th style="border-bottom:1px solid #eee;padding:8px;text-align:right;">Value</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($r['warehouse_name'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars(($r['product_code'] ?? '').' — '.($r['product_name'] ?? ''),ENT_QUOTES,'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= (int)($r['qty_on_hand'] ?? 0) ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= number_format((float)($r['avg_cost'] ?? 0),4) ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= number_format((float)($r['value'] ?? 0),2) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?>
        <tr><td colspan="5" style="padding:12px;">No stock.</td></tr>
      <?php endif; ?>
    </tbody>
    <tfoot>
      <tr>
        <th colspan="2" style="padding:8px;text-align:right;">Totals</th>
        <th style="padding:8px;text-align:right;"><?= (int)($tot['qty'] ?? 0) ?></th>
        <th></th>
        <th style="padding:8px;text-align:right;"><?= number_format((float)($tot['value'] ?? 0),2) ?></th>
      </tr>
    </tfoot>
  </table>
</section>

<style>
  @media print { .no-print, nav, header, footer { display:none !important; } body { margin:0; } }
</style>
