<?php $h = fn($v)=>htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); ?>
<div class="page-header">
  <div class="title"><?= $h($warehouse['code'].' — '.$warehouse['name']) ?></div>
  <a class="ms-auto btn btn-sm btn-outline-secondary" href="/warehouses">Back</a>
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>Code/SKU</th>
            <th>Name</th>
            <th class="text-end">On hand</th>
            <th class="text-end">Reserved</th>
            <th class="text-end">Avg cost</th>
            <th class="text-end">Value</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($lines as $r): ?>
          <tr>
            <td><?= $h($r['code']) ?></td>
            <td><?= $h($r['name']) ?></td>
            <td class="text-end"><?= number_format((float)$r['on_hand'], 2) ?></td>
            <td class="text-end"><?= number_format((float)$r['reserved'], 2) ?></td>
            <td class="text-end"><?= number_format((float)$r['avg_cost'], 4) ?></td>
            <td class="text-end"><?= number_format((float)$r['value'], 2) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($lines)): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">No stock yet</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
