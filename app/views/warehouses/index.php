<?php
use function App\Core\base_url;
use function App\Core\csrf_field;
use function App\Core\flash_get;
?>
<section>
  <h2>Warehouses</h2>

  <?php if ($m = flash_get('success')): ?>
    <div style="background:#e7f8ee;border:1px solid #b9e7c9;padding:10px;border-radius:8px;margin:10px 0;"><?= htmlspecialchars($m, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
  <?php if ($m = flash_get('error')): ?>
    <div style="background:#ffe9e9;border:1px solid #ffb3b3;padding:10px;border-radius:8px;margin:10px 0;"><?= htmlspecialchars($m, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <p><a href="<?= base_url('/warehouses/create') ?>">+ New Warehouse</a></p>

  <table style="width:100%;border-collapse:collapse;">
    <thead><tr>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Code</th>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Name</th>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Location</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">Actions</th>
    </tr></thead>
    <tbody>
      <?php foreach ($items as $w): ?>
        <tr>
          <td style="border-bottom:1px solid #f2f2f4;padding:8px;"><?= htmlspecialchars($w['code'], ENT_QUOTES, 'UTF-8') ?></td>
          <td style="border-bottom:1px solid #f2f2f4;padding:8px;"><?= htmlspecialchars($w['name'], ENT_QUOTES, 'UTF-8') ?></td>
          <td style="border-bottom:1px solid #f2f2f4;padding:8px;"><?= htmlspecialchars($w['location'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
          <td style="border-bottom:1px solid #f2f2f4;padding:8px;">
            <a href="<?= base_url('/warehouses/edit?id='.(int)$w['id']) ?>">Edit</a> &nbsp;|&nbsp;
            <form method="post" action="<?= base_url('/warehouses/delete') ?>" style="display:inline" onsubmit="return confirm('Delete this warehouse?');">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$w['id'] ?>">
              <button type="submit" style="background:none;border:none;color:#c00;cursor:pointer;">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$items): ?><tr><td colspan="4" style="padding:12px;">No warehouses yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</section>
