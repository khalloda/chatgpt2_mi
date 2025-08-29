<?php
use function App\Core\base_url;
use function App\Core\csrf_field;
?>
<section>
  <h2>Quote <?= htmlspecialchars($q['quote_no'], ENT_QUOTES, 'UTF-8') ?></h2>
  <p>Status: <strong><?= htmlspecialchars($q['status'], ENT_QUOTES, 'UTF-8') ?></strong></p>
  <p>Subtotal: <?= number_format((float)$q['subtotal'],2) ?> |
     Tax (<?= number_format((float)$q['tax_rate'],2) ?>%): <?= number_format((float)$q['tax_amount'],2) ?> |
     Total: <strong><?= number_format((float)$q['total'],2) ?></strong></p>
  <?php if (!empty($q['expires_at'])): ?><p>Expires at: <?= htmlspecialchars($q['expires_at'], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>

  <table style="width:100%;border-collapse:collapse;margin-top:10px;">
    <thead><tr>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Product</th>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Warehouse</th>
      <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Qty</th>
      <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Price</th>
      <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Line total</th>
    </tr></thead>
    <tbody>
      <?php foreach ($items as $it): ?>
        <tr>
          <td style="border-bottom:1px solid #f2f2f4;padding:8px;"><?= htmlspecialchars($it['product_code'].' — '.$it['product_name'], ENT_QUOTES, 'UTF-8') ?></td>
          <td style="border-bottom:1px solid #f2f2f4;padding:8px;"><?= htmlspecialchars($it['warehouse_name'], ENT_QUOTES, 'UTF-8') ?></td>
          <td style="border-bottom:1px solid #f2f2f4;padding:8px;text-align:right;"><?= (int)$it['qty'] ?></td>
          <td style="border-bottom:1px solid #f2f2f4;padding:8px;text-align:right;"><?= number_format((float)$it['price'],2) ?></td>
          <td style="border-bottom:1px solid #f2f2f4;padding:8px;text-align:right;"><?= number_format((float)$it['line_total'],2) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <?php if ($q['status']==='sent'): ?>
    <form method="post" action="<?= base_url('/quotes/cancel') ?>" style="display:inline">
      <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$q['id'] ?>">
      <button type="submit" style="margin-top:10px;background:none;border:1px solid #c00;color:#c00;border-radius:8px;padding:8px 12px;cursor:pointer;">Cancel (release)</button>
    </form>
    <form method="post" action="<?= base_url('/quotes/expire') ?>" style="display:inline;margin-left:8px;">
      <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$q['id'] ?>">
      <button type="submit" style="margin-top:10px;background:none;border:1px solid #999;color:#333;border-radius:8px;padding:8px 12px;cursor:pointer;">Mark Expired</button>
    </form>
    <!-- Convert to Order button will arrive in Phase 4B -->
	<form method="post" action="<?= base_url('/quotes/convert') ?>" style="display:inline;margin-left:8px;">
  <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$q['id'] ?>">
  <button type="submit" style="margin-top:10px;background:#0a0;border:1px solid #0a0;color:#fff;border-radius:8px;padding:8px 12px;cursor:pointer;">
    Convert to Order
  </button>
</form>
  <?php endif; ?>

  <p style="margin-top:12px;"><a href="<?= base_url('/quotes') ?>">Back to Quotes</a></p>
</section>
