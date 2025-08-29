<?php
use function App\Core\base_url;
use function App\Core\csrf_field;
use function App\Core\flash_get;
?>
<section>
  <h2>Models</h2>

  <?php if ($m = flash_get('success')): ?>
    <div style="background:#e7f8ee;border:1px solid #b9e7c9;padding:10px;border-radius:8px;margin:10px 0;"><?= htmlspecialchars($m, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
  <?php if ($m = flash_get('error')): ?>
    <div style="background:#ffe9e9;border:1px solid #ffb3b3;padding:10px;border-radius:8px;margin:10px 0;"><?= htmlspecialchars($m, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
    <form method="get" action="<?= base_url('/models') ?>">
      <label>
        Filter by make:
        <select name="make_id" onchange="this.form.submit()" style="padding:6px;border:1px solid #ddd;border-radius:6px;">
          <option value="">All</option>
          <?php foreach ($makes as $mk): ?>
            <option value="<?= (int)$mk['id'] ?>" <?php if (!empty($selected_make) && (int)$selected_make === (int)$mk['id']) echo 'selected'; ?>>
              <?= htmlspecialchars($mk['name'], ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
    </form>
    <a href="<?= base_url('/models/create') ?>">+ New Model</a>
  </div>

  <table style="width:100%;border-collapse:collapse;">
    <thead>
      <tr>
        <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Make</th>
        <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Name</th>
        <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Slug</th>
        <th style="border-bottom:1px solid #eee;padding:8px;">Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $row): ?>
      <tr>
        <td style="border-bottom:1px solid #f2f2f4;padding:8px;"><?= htmlspecialchars($row['make_name'], ENT_QUOTES, 'UTF-8') ?></td>
        <td style="border-bottom:1px solid #f2f2f4;padding:8px;"><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></td>
        <td style="border-bottom:1px solid #f2f2f4;padding:8px;"><?= htmlspecialchars($row['slug'], ENT_QUOTES, 'UTF-8') ?></td>
        <td style="border-bottom:1px solid #f2f2f4;padding:8px;white-space:nowrap;">
          <a href="<?= base_url('/models/edit?id='.(int)$row['id']) ?>">Edit</a>
          &nbsp;|&nbsp;
          <form method="post" action="<?= base_url('/models/delete') ?>" style="display:inline" onsubmit="return confirm('Delete this model?');">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
            <button type="submit" style="background:none;border:none;color:#c00;cursor:pointer;">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$items): ?>
      <tr><td colspan="4" style="padding:12px;">No models yet.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</section>
