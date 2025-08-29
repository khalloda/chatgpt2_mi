<?php
use function App\Core\base_url;
use function App\Core\csrf_field;
?>
<section>
  <h2><?= $mode === 'create' ? 'New Category' : 'Edit Category' ?></h2>

  <form method="post" action="<?= $mode === 'create' ? base_url('/categories') : base_url('/categories/update') ?>" style="display:grid;gap:12px;max-width:520px;">
    <?= csrf_field() ?>
    <?php if ($mode === 'edit'): ?>
      <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
    <?php endif; ?>

    <label>
      <div>Name</div>
      <input type="text" name="name" required value="<?= htmlspecialchars($item['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
    </label>

    <label>
      <div>Slug (unique, URL-safe)</div>
      <input type="text" name="slug" required value="<?= htmlspecialchars($item['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
    </label>

    <label>
      <div>Parent (optional)</div>
      <select name="parent_id" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
        <option value="">— none —</option>
        <?php foreach ($options as $opt): ?>
          <option value="<?= (int)$opt['id'] ?>" <?php if (!empty($item['parent_id']) && (int)$item['parent_id'] === (int)$opt['id']) echo 'selected'; ?>>
            <?= htmlspecialchars($opt['name'], ENT_QUOTES, 'UTF-8') ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>

    <div style="display:flex;gap:10px;">
      <button type="submit" style="padding:10px 14px;border:0;border-radius:10px;background:#111;color:#fff;cursor:pointer;">
        <?= $mode === 'create' ? 'Create' : 'Save Changes' ?>
      </button>
      <a href="<?= base_url('/categories') ?>" style="align-self:center;">Cancel</a>
    </div>
  </form>
</section>
