<?php
use function App\Core\base_url;
use function App\Core\csrf_field;
use function App\Core\flash_get;
$h = fn($v)=>htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
?>
<section>
  <div class="page-header">
    <div class="title">Warehouses</div>
    <a class="ms-auto btn btn-sm btn-primary" href="<?= base_url('/warehouses/create') ?>">+ New Warehouse</a>
  </div>

  <?php if ($m = flash_get('success')): ?>
    <div class="alert alert-success"><?= $h($m) ?></div>
  <?php endif; ?>
  <?php if ($m = flash_get('error')): ?>
    <div class="alert alert-danger"><?= $h($m) ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>Code</th>
              <th>Name</th>
              <th>Location</th>
              <th class="text-end">On hand</th>
              <th class="text-end">Reserved</th>
              <th class="text-end">Value</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($items as $w): ?>
            <tr>
              <td><?= $h($w['code']) ?></td>
              <td><?= $h($w['name']) ?></td>
              <td><?= $h($w['location'] ?? '') ?></td>
              <td class="text-end"><?= number_format((float)$w['on_hand'], 2) ?></td>
              <td class="text-end"><?= number_format((float)$w['reserved'], 2) ?></td>
              <td class="text-end"><?= number_format((float)$w['value'], 2) ?></td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-primary" href="<?= base_url('/warehouses/show?id='.(int)$w['id']) ?>">View</a>
                <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('/warehouses/edit?id='.(int)$w['id']) ?>">Edit</a>
                <form method="post" action="<?= base_url('/warehouses/delete') ?>" style="display:inline" onsubmit="return confirm('Delete this warehouse?');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int)$w['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$items): ?>
            <tr><td colspan="7" class="text-center text-muted py-4">No warehouses yet.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>
