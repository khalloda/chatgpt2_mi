<?php
use function App\Core\base_url;
use function App\Core\csrf_field;
?>
<section>
  <h2><?= $mode==='create'?'New Warehouse':'Edit Warehouse' ?></h2>

  <form method="post" action="<?= $mode==='create'?base_url('/warehouses'):base_url('/warehouses/update') ?>" style="display:grid;gap:12px;max-width:520px;">
    <?= csrf_field() ?>
    <?php if ($mode==='edit'): ?><input type="hidden" name="id" value="<?= (int)$item['id'] ?>"><?php endif; ?>

    <label><div>Code</div>
      <input type="text" name="code" required value="<?= htmlspecialchars($item['code'] ?? '', ENT_QUOTES, 'UTF-8') ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
    </label>
    <label><div>Name</div>
      <input type="text" name="name" required value="<?= htmlspecialchars($item['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
    </label>
    <label><div>Location (optional)</div>
      <input type="text" name="location" value="<?= htmlspecialchars($item['location'] ?? '', ENT_QUOTES, 'UTF-8') ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
    </label>

    <div style="display:flex;gap:10px;">
      <button type="submit" style="padding:10px 14px;border:0;border-radius:10px;background:#111;color:#fff;cursor:pointer;"><?= $mode==='create'?'Create':'Save Changes' ?></button>
      <a href="<?= base_url('/warehouses') ?>" style="align-self:center;">Cancel</a>
    </div>
  </form>
  <?php if ($mode === 'edit'): ?>
  <?php
    $entity_type = 'warehouse';
    $entity_id   = (int)$item['id'];
    $notes       = $notes ?? [];
    include __DIR__ . '/../partials/notes.php';
  ?>
<?php endif; ?>
</section>
