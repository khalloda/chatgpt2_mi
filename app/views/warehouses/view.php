<?php
use function App\Core\base_url;
?>
<section>
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h2 class="mb-0">
      Warehouse: <?= htmlspecialchars($wh['code'].' — '.$wh['name'], ENT_QUOTES, 'UTF-8') ?>
      <?php if (!empty($wh['location'])): ?>
        <small class="text-muted">· <?= htmlspecialchars($wh['location'], ENT_QUOTES, 'UTF-8') ?></small>
      <?php endif; ?>
    </h2>
    <div>
      <a class="btn btn-outline-secondary" href="<?= base_url('/warehouses') ?>">Back</a>
      <a class="btn btn-outline-primary" href="<?= base_url('/warehouses/exportcsv?id='.(int)$wh['id']) ?>">Export CSV</a>
    </div>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-md-3">
      <div class="card"><div class="card-body">
        <div class="text-muted">On hand</div>
        <div class="h4 mb-0"><?= number_format($totals['on_hand'], 2) ?></div>
      </div></div>
    </div>
    <div class="col-md-3">
      <div class="card"><div class="card-body">
        <div class="text-muted">Reserved</div>
        <div class="h4 mb-0"><?= number_format($totals['reserved'], 2) ?></div>
      </div></div>
    </div>
    <div class="col-md-3">
      <div class="card"><div class="card-body">
        <div class="text-muted">Available</div>
        <div class="h4 mb-0"><?= number_format($totals['available'], 2) ?></div>
      </div></div>
    </div>
    <div class="col-md-3">
      <div class="card"><div class="card-body">
        <div class="text-muted">Stock value</div>
        <div class="h4 mb-0"><?= number_format($totals['value'], 2) ?></div>
      </div></div>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr>
          <th style="position:sticky;left:0;background:#fff;z-index:2;">Code</th>
          <th style="position:sticky;left:120px;background:#fff;z-index:2;">Product</th>
          <th class="text-end">On hand</th>
          <th class="text-end">Reserved</th>
          <th class="text-end">Available</th>
          <th class="text-end">Avg cost</th>
          <th class="text-end">Value</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $r): ?>
          <tr>
            <td style="position:sticky;left:0;background:#fff;"><?= htmlspecialchars($r['code'], ENT_QUOTES, 'UTF-8') ?></td>
            <td style="position:sticky;left:120px;background:#fff;max-width:360px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="<?= htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') ?>">
              <?= htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') ?>
            </td>
            <td class="text-end"><?= number_format((float)$r['on_hand'], 2) ?></td>
            <td class="text-end"><?= number_format((float)$r['reserved'], 2) ?></td>
            <td class="text-end"><?= number_format((float)$r['available'], 2) ?></td>
            <td class="text-end"><?= number_format((float)$r['avg_cost'], 2) ?></td>
            <td class="text-end"><?= number_format((float)$r['value'], 2) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$items): ?>
          <tr><td colspan="7" class="text-muted">No stock in this warehouse.</td></tr>
        <?php endif; ?>
      </tbody>
      <tfoot>
        <tr class="fw-semibold">
          <td style="position:sticky;left:0;background:#fff;">Totals</td>
          <td style="position:sticky;left:120px;background:#fff;"></td>
          <td class="text-end"><?= number_format($totals['on_hand'], 2) ?></td>
          <td class="text-end"><?= number_format($totals['reserved'], 2) ?></td>
          <td class="text-end"><?= number_format($totals['available'], 2) ?></td>
          <td></td>
          <td class="text-end"><?= number_format($totals['value'], 2) ?></td>
        </tr>
      </tfoot>
    </table>
  </div>
</section>
