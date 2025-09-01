<?php
use function App\Core\base_url;
/** @var array $rows,$totals; @var string $asof */
?>
<section>
  <h2>AP Aging</h2>

  <form class="no-print" method="get" action="<?= base_url('/reports/ap-aging') ?>" style="display:flex;gap:8px;align-items:end;margin:8px 0;">
    <label><div>As of</div><input type="date" name="asof" value="<?= htmlspecialchars($asof,ENT_QUOTES,'UTF-8') ?>" style="padding:8px;border:1px solid #ddd;border-radius:6px;"></label>
    <button type="submit" style="padding:8px 12px;border:0;border-radius:8px;background:#111;color:#fff;cursor:pointer;">Apply</button>
    <button type="button" onclick="window.print()" style="padding:8px 12px;border:1px solid #111;border-radius:8px;background:#fff;color:#111;cursor:pointer;">Print</button>
  </form>

  <table style="width:100%;border-collapse:collapse;">
    <thead><tr>
      <th style="border-bottom:1px solid #eee;padding:8px;">Supplier</th>
      <th style="border-bottom:1px solid #eee;padding:8px;text-align:right;">0–30</th>
      <th style="border-bottom:1px solid #eee;padding:8px;text-align:right;">31–60</th>
      <th style="border-bottom:1px solid #eee;padding:8px;text-align:right;">61–90</th>
      <th style="border-bottom:1px solid #eee;padding:8px;text-align:right;">90+</th>
      <th style="border-bottom:1px solid #eee;padding:8px;text-align:right;">Total</th>
    </tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
      <tr>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($r['supplier_name'],ENT_QUOTES,'UTF-8') ?></td>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= number_format((float)$r['bucket_0_30'],2) ?></td>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= number_format((float)$r['bucket_31_60'],2) ?></td>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= number_format((float)$r['bucket_61_90'],2) ?></td>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= number_format((float)$r['bucket_90_plus'],2) ?></td>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= number_format((float)$r['total'],2) ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?>
        <tr><td colspan="6" style="padding:12px;">No outstanding payables.</td></tr>
      <?php endif; ?>
    </tbody>
    <tfoot>
      <tr>
        <th style="padding:8px;text-align:right;">Grand Total</th>
        <th style="padding:8px;text-align:right;"><?= number_format((float)$totals['b0'],2) ?></th>
        <th style="padding:8px;text-align:right;"><?= number_format((float)$totals['b31'],2) ?></th>
        <th style="padding:8px;text-align:right;"><?= number_format((float)$totals['b61'],2) ?></th>
        <th style="padding:8px;text-align:right;"><?= number_format((float)$totals['b90'],2) ?></th>
        <th style="padding:8px;text-align:right;"><?= number_format((float)$totals['total'],2) ?></th>
      </tr>
    </tfoot>
  </table>
</section>
