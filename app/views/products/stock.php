<?php
use function App\Core\base_url;
use function App\Core\csrf_field;
use function App\Core\flash_get;
?>
<section>
  <h2>Stock: <?= htmlspecialchars($item['code'].' — '.$item['name'], ENT_QUOTES, 'UTF-8') ?></h2>

  <?php if ($m = flash_get('success')): ?>
    <div style="background:#e7f8ee;border:1px solid #b9e7c9;padding:10px;border-radius:8px;margin:10px 0;"><?= htmlspecialchars($m, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
  <?php if ($m = flash_get('error')): ?>
    <div style="background:#ffe9e9;border:1px solid #ffb3b3;padding:10px;border-radius:8px;margin:10px 0;"><?= htmlspecialchars($m, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <form method="post" action="<?= base_url('/products/stock') ?>" style="display:block;">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">

    <table style="width:100%;border-collapse:collapse;">
      <thead>
        <tr>
          <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Warehouse</th>
          <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Qty on hand</th>
          <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Qty reserved</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td style="border-bottom:1px solid #f2f2f4;padding:8px;"><?= htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td style="border-bottom:1px solid #f2f2f4;padding:8px;text-align:right;">
              <input type="number" min="0" name="qty_on[<?= (int)$r['id'] ?>]" value="<?= (int)$r['qty_on_hand'] ?>" style="width:120px;padding:6px;border:1px solid #ddd;border-radius:6px;text-align:right;">
            </td>
            <td style="border-bottom:1px solid #f2f2f4;padding:8px;text-align:right;">
              <input type="number" min="0" name="qty_res[<?= (int)$r['id'] ?>]" value="<?= (int)$r['qty_reserved'] ?>" style="width:120px;padding:6px;border:1px solid #ddd;border-radius:6px;text-align:right;">
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?>
          <tr><td colspan="3" style="padding:12px;">No warehouses defined yet. <a href="<?= base_url('/warehouses') ?>">Create a warehouse</a>.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <div style="margin-top:12px;display:flex;gap:10px;">
      <button type="submit" style="padding:10px 14px;border:0;border-radius:10px;background:#111;color:#fff;cursor:pointer;">Save Stock</button>
      <a href="<?= base_url('/products') ?>" style="align-self:center;">Back to Products</a>
    </div>
  </form>
</section>
