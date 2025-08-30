<?php
use function App\Core\base_url;
use function App\Core\csrf_field;
/** @var string $mode */
/** @var array $item */
?>
<section>
  <h2><?= $mode === 'create' ? 'New Supplier' : 'Edit Supplier' ?></h2>

  <form method="post" action="<?= base_url($mode==='create' ? '/suppliers' : '/suppliers/update') ?>" style="display:grid;gap:10px;max-width:800px;">
    <?= csrf_field() ?>
    <?php if ($mode==='edit'): ?>
      <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
    <?php endif; ?>

    <label><div>Name</div>
      <input type="text" name="name" value="<?= htmlspecialchars($item['name'] ?? '',ENT_QUOTES,'UTF-8') ?>" required style="padding:8px;border:1px solid #ddd;border-radius:6px;width:100%;">
    </label>

    <label><div>Phone</div>
      <input type="text" name="phone" value="<?= htmlspecialchars($item['phone'] ?? '',ENT_QUOTES,'UTF-8') ?>" style="padding:8px;border:1px solid #ddd;border-radius:6px;width:100%;">
    </label>

    <label><div>Email</div>
      <input type="email" name="email" value="<?= htmlspecialchars($item['email'] ?? '',ENT_QUOTES,'UTF-8') ?>" style="padding:8px;border:1px solid #ddd;border-radius:6px;width:100%;">
    </label>

    <label><div>Address</div>
      <input type="text" name="address" value="<?= htmlspecialchars($item['address'] ?? '',ENT_QUOTES,'UTF-8') ?>" style="padding:8px;border:1px solid #ddd;border-radius:6px;width:100%;">
    </label>

    <div>
      <button type="submit" style="padding:8px 12px;border:0;border-radius:8px;background:#111;color:#fff;cursor:pointer;">
        <?= $mode==='create' ? 'Create' : 'Save' ?>
      </button>
      <a href="<?= base_url('/suppliers') ?>" style="margin-left:8px;">Back</a>
    </div>
  </form>
</section>
