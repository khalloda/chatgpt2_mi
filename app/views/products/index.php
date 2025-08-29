<?php
use function App\Core\base_url;
use function App\Core\csrf_field;
use function App\Core\flash_get;
?>
<section>
  <h2>Products</h2>

  <?php if ($m = flash_get('success')): ?>
    <div style="background:#e7f8ee;border:1px solid #b9e7c9;padding:10px;border-radius:8px;margin:10px 0;"><?= htmlspecialchars($m, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
  <?php if ($m = flash_get('error')): ?>
    <div style="background:#ffe9e9;border:1px solid #ffb3b3;padding:10px;border-radius:8px;margin:10px 0;"><?= htmlspecialchars($m, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <form method="get" action="<?= base_url('/products') ?>" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:10px;">
    <input type="text" name="q" placeholder="Search name or code" value="<?= htmlspecialchars((string)$q, ENT_QUOTES, 'UTF-8') ?>" style="padding:8px;border:1px solid #ddd;border-radius:8px;">
    <select name="category_id" style="padding:8px;border:1px solid #ddd;border-radius:8px;">
      <option value="">All categories</option>
      <?php foreach ($categories as $c): ?>
        <option value="<?= (int)$c['id'] ?>" <?php if ((int)$category_id === (int)$c['id']) echo 'selected'; ?>>
          <?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?>
        </option>
      <?php endforeach; ?>
    </select>
    <select name="make_id" style="padding:8px;border:1px solid #ddd;border-radius:8px;">
      <option value="">All makes</option>
      <?php foreach ($makes as $mk): ?>
        <option value="<?= (int)$mk['id'] ?>" <?php if ((int)$make_id === (int)$mk['id']) echo 'selected'; ?>>
          <?= htmlspecialchars($mk['name'], ENT_QUOTES, 'UTF-8') ?>
        </option>
      <?php endforeach; ?>
    </select>
    <select name="model_id" style="padding:8px;border:1px solid #ddd;border-radius:8px;">
      <option value="">All models</option>
      <?php foreach ($models as $md): ?>
        <option value="<?= (int)$md['id'] ?>" <?php if ((int)$model_id === (int)$md['id']) echo 'selected'; ?>>
          <?= htmlspecialchars($md['name'], ENT_QUOTES, 'UTF-8') ?>
        </option>
      <?php endforeach; ?>
    </select>
    <button type="submit" style="padding:8px 12px;border:0;border-radius:8px;background:#111;color:#fff;">Filter</button>
    <a href="<?= base_url('/products/create') ?>" style="align-self:center;margin-left:auto;">+ New Product</a>
  </form>

  <table style="width:100%;border-collapse:collapse;">
    <thead>
      <tr>
        <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Code</th>
        <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Name</th>
        <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Category</th>
        <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Make / Model</th>
        <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Cost</th>
        <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Price</th>
        <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Avail / Resv</th>
        <th style="border-bottom:1px solid #eee;padding:8px;">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $p): $avail = (int)$p['on_hand'] - (int)$p['reserved']; ?>
        <tr>
          <td style="border-bottom:1px solid #f2f2f4;padding:8px;"><?= htmlspecialchars($p['code'], ENT_QUOTES, 'UTF-8') ?></td>
          <td style="border-bottom:1px solid #f2f2f4;padding:8px;"><?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?></td>
          <td style="border-bottom:1px solid #f2f2f4;padding:8px;"><?= htmlspecialchars($p['category_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
          <td style="border-bottom:1px solid #f2f2f4;padding:8px;"><?= htmlspecialchars(trim(($p['make_name'] ?? '').' / '.($p['model_name'] ?? ''), ' /'), ENT_QUOTES, 'UTF-8') ?></td>
          <td style="border-bottom:1px solid #f2f2f4;padding:8px;text-align:right;"><?= number_format((float)$p['cost'],2) ?></td>
          <td style="border-bottom:1px solid #f2f2f4;padding:8px;text-align:right;"><?= number_format((float)$p['price'],2) ?></td>
          <td style="border-bottom:1px solid #f2f2f4;padding:8px;text-align:right;"><?= (int)$avail ?> / <?= (int)$p['reserved'] ?></td>
          <td style="border-bottom:1px solid #f2f2f4;padding:8px;white-space:nowrap;">
            <a href="<?= base_url('/products/stock?id='.(int)$p['id']) ?>">Stock</a> &nbsp;|&nbsp;
            <a href="<?= base_url('/products/edit?id='.(int)$p['id']) ?>">Edit</a> &nbsp;|&nbsp;
            <form method="post" action="<?= base_url('/products/delete') ?>" style="display:inline" onsubmit="return confirm('Delete this product?');">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
              <button type="submit" style="background:none;border:none;color:#c00;cursor:pointer;">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$items): ?><tr><td colspan="8" style="padding:12px;">No products yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</section>
