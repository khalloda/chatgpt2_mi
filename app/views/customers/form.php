<?php
use function App\Core\base_url;
use function App\Core\csrf_field;
?>
<section>
  <h2><?= $mode==='create'?'New Customer':'Edit Customer' ?></h2>

  <form method="post" action="<?= $mode==='create'?base_url('/customers'):base_url('/customers/update') ?>" style="display:grid;gap:12px;max-width:720px;">
    <?= csrf_field() ?>
    <?php if ($mode==='edit'): ?><input type="hidden" name="id" value="<?= (int)$item['id'] ?>"><?php endif; ?>
    <label><div>Name</div>
      <input type="text" name="name" required value="<?= htmlspecialchars($item['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
    </label>
    <label><div>Phone</div>
      <input type="text" name="phone" value="<?= htmlspecialchars($item['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
    </label>
    <label><div>Email</div>
      <input type="email" name="email" value="<?= htmlspecialchars($item['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
    </label>
    <label><div>Address</div>
      <textarea name="address" rows="3" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;"><?= htmlspecialchars($item['address'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
    </label>
    <div style="display:flex;gap:10px;">
      <button type="submit" style="padding:10px 14px;border:0;border-radius:10px;background:#111;color:#fff;cursor:pointer;"><?= $mode==='create'?'Create':'Save Changes' ?></button>
      <a href="<?= base_url('/customers') ?>" style="align-self:center;">Cancel</a>
    </div>
  </form>
  <?php if ($mode === 'edit'): ?>
  <?php
    $entity_type = 'customer';
    $entity_id   = (int)$item['id'];
    $notes       = $notes ?? [];
    include __DIR__ . '/../partials/notes.php';
  ?>
<?php endif; ?>
</section>
