<?php
use function App\Core\base_url;
use function App\Core\csrf_field;

/** @var string $entity_type */
/** @var int $entity_id */
/** @var array $notes */
?>
<section style="margin-top:18px;">
  <h3>Notes</h3>

  <div style="margin:10px 0;">
    <?php if (!$notes): ?>
      <div style="color:#666;">No notes yet.</div>
    <?php else: ?>
      <ul style="list-style:none;padding:0;margin:0;display:grid;gap:10px;">
        <?php foreach ($notes as $n): ?>
          <li style="border:1px solid #eee;border-radius:8px;padding:10px;">
            <div style="display:flex;justify-content:space-between;gap:10px;">
              <div>
                <?php if ((int)$n['is_public'] === 1): ?>
                  <span style="border:1px solid #0a0;color:#0a0;padding:2px 6px;border-radius:6px;font-size:12px;">Public</span>
                <?php else: ?>
                  <span style="border:1px solid #999;color:#333;padding:2px 6px;border-radius:6px;font-size:12px;">Private</span>
                <?php endif; ?>
              </div>
              <div style="color:#666;font-size:12px;">
                <?= htmlspecialchars($n['created_by'] ?? 'system', ENT_QUOTES, 'UTF-8') ?>
                · <?= htmlspecialchars($n['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?>
              </div>
            </div>
            <div style="white-space:pre-wrap;margin-top:6px;">
              <?= htmlspecialchars($n['body'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <form method="post" action="<?= base_url('/notes/delete') ?>" style="margin-top:6px;">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$n['id'] ?>">
              <input type="hidden" name="_return" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/', ENT_QUOTES, 'UTF-8') ?>">
              <button type="submit" style="background:none;border:1px solid #cc0000;color:#cc0000;border-radius:6px;padding:4px 8px;cursor:pointer;">Delete</button>
            </form>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>

  <form method="post" action="<?= base_url('/notes') ?>" style="display:grid;gap:10px;max-width:900px;">
    <?= csrf_field() ?>
    <input type="hidden" name="entity_type" value="<?= htmlspecialchars($entity_type, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="entity_id" value="<?= (int)$entity_id ?>">
    <input type="hidden" name="_return" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/', ENT_QUOTES, 'UTF-8') ?>">
    <label>
      <div>Note</div>
      <textarea name="body" rows="3" required
        style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;"></textarea>
    </label>
    <label style="display:flex;gap:8px;align-items:center;">
      <input type="checkbox" name="is_public" value="1">
      <span>Public (can appear on printed documents)</span>
    </label>
    <div>
      <button type="submit" style="padding:8px 12px;border:0;border-radius:8px;background:#111;color:#fff;cursor:pointer;">Add Note</button>
    </div>
  </form>
</section>
