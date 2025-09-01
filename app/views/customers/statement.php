<?php
use function App\Core\base_url;
/** @var array $customer,$rows; @var string $from,$to; @var float $opening,$closing */
?>
<section>
  <h2>Customer Statement — <?= htmlspecialchars($customer['name'],ENT_QUOTES,'UTF-8') ?></h2>

  <form class="no-print" method="get" action="<?= base_url('/customers/statement') ?>" style="display:flex;gap:8px;align-items:end;margin:8px 0;">
    <input type="hidden" name="id" value="<?= (int)$customer['id'] ?>">
    <label><div>From</div><input type="date" name="from" value="<?= htmlspecialchars($from,ENT_QUOTES,'UTF-8') ?>" style="padding:8px;border:1px solid #ddd;border-radius:6px;"></label>
    <label><div>To</div><input type="date" name="to" value="<?= htmlspecialchars($to,ENT_QUOTES,'UTF-8') ?>" style="padding:8px;border:1px solid #ddd;border-radius:6px;"></label>
    <button type="submit" style="padding:8px 12px;border:0;border-radius:8px;background:#111;color:#fff;cursor:pointer;">Apply</button>
    <button type="button" onclick="window.print()" style="padding:8px 12px;border:1px solid #111;border-radius:8px;background:#fff;color:#111;cursor:pointer;">Print</button>
    <a href="<?= base_url('/customers/show?id='.(int)$customer['id']) ?>" style="margin-left:8px;">Back</a>
  </form>

  <p>Opening balance (before <?= htmlspecialchars($from,ENT_QUOTES,'UTF-8') ?>): <strong><?= number_format((float)$opening,2) ?></strong></p>

  <table style="width:100%;border-collapse:collapse;">
    <thead><tr>
      <th style="border-bottom:1px solid #eee;padding:8px;">Date</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">Type</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">Ref</th>
      <th style="border-bottom:1px solid #eee;padding:8px;text-align:right;">Debit</th>
      <th style="border-bottom:1px solid #eee;padding:8px;text-align:right;">Credit</th>
      <th style="border-bottom:1px solid #eee;padding:8px;text-align:right;">Running</th>
    </tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
      <tr>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($r['txn_date'],ENT_QUOTES,'UTF-8') ?></td>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars(ucfirst($r['kind']),ENT_QUOTES,'UTF-8') ?></td>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($r['ref_no'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= number_format((float)$r['debit'],2) ?></td>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= number_format((float)$r['credit'],2) ?></td>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;"><?= number_format((float)$r['running'],2) ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?>
        <tr><td colspan="6" style="padding:12px;">No movements in this period.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <p style="margin-top:10px;">Closing balance (as of <?= htmlspecialchars($to,ENT_QUOTES,'UTF-8') ?>): <strong><?= number_format((float)$closing,2) ?></strong></p>
</section>
