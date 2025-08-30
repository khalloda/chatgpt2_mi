<?php
use function App\Core\base_url;
use function App\Core\csrf_field;
use function App\Core\format_note_html;

/** @var string $entity_type */
/** @var int $entity_id */
/** @var array $notes */

$PER_PAGE = 10; // show first N, reveal more with JS
?>
<section style="margin-top:18px;">
  <h3>Notes</h3>

  <div style="margin:10px 0;">
    <?php if (!$notes): ?>
      <div style="color:#666;">No notes yet.</div>
    <?php else: ?>
      <ul id="notes-list" style="list-style:none;padding:0;margin:0;display:grid;gap:10px;">
        <?php foreach ($notes as $idx => $n): ?>
          <?php
            $isHidden = ($idx >= $PER_PAGE);
            $noteId = (int)$n['id'];
            $isPublic = (int)$n['is_public'] === 1;
          ?>
          <li class="note-item<?= $isHidden ? ' note-hidden' : '' ?>" data-idx="<?= $idx ?>"
              style="border:1px solid #eee;border-radius:8px;padding:10px;<?= $isHidden ? 'display:none;' : '' ?>">
            <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;">
              <div>
                <?php if ($isPublic): ?>
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

            <!-- display mode -->
            <div class="note-body" style="white-space:normal;margin-top:6px;"><?= format_note_html($n['body'] ?? '') ?></div>

            <!-- edit mode -->
            <form class="note-edit" method="post" action="<?= base_url('/notes/update') ?>" style="display:none; margin-top:6px;">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= $noteId ?>">
              <input type="hidden" name="_return" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/', ENT_QUOTES, 'UTF-8') ?>">
              <label style="display:block;margin-bottom:6px;">
                <textarea name="body" rows="3" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;"><?= htmlspecialchars($n['body'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
              </label>
              <label style="display:flex;gap:8px;align-items:center;margin-bottom:6px;">
                <input type="checkbox" name="is_public" value="1" <?= $isPublic ? 'checked' : '' ?>>
                <span>Public</span>
              </label>
              <div style="display:flex;gap:8px;">
                <button type="submit" style="padding:6px 10px;border:0;border-radius:8px;background:#111;color:#fff;cursor:pointer;">Save</button>
                <button type="button" class="btn-cancel-edit" style="padding:6px 10px;border:1px solid #ccc;border-radius:8px;background:#fff;cursor:pointer;">Cancel</button>
              </div>
            </form>

            <div style="display:flex;gap:8px;margin-top:8px;">
              <button type="button" class="btn-edit" style="padding:4px 8px;border:1px solid #ddd;border-radius:6px;background:#f9f9fb;cursor:pointer;">Edit</button>

              <form method="post" action="<?= base_url('/notes/delete') ?>"
                    onsubmit="return confirm('Delete this note?');" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $noteId ?>">
                <input type="hidden" name="_return" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/', ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" style="padding:4px 8px;border:1px solid #cc0000;color:#cc0000;border-radius:6px;background:#fff;cursor:pointer;">Delete</button>
              </form>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>

      <?php if (count($notes) > $PER_PAGE): ?>
        <div style="margin-top:10px;">
          <button id="notes-more" type="button" style="padding:6px 10px;border:1px solid #ddd;border-radius:8px;background:#f9f9fb;cursor:pointer;">
            Show more
          </button>
          <button id="notes-less" type="button" style="padding:6px 10px;border:1px solid #ddd;border-radius:8px;background:#f9f9fb;cursor:pointer; display:none;">
            Show less
          </button>
        </div>
      <?php endif; ?>
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

<script>
(function(){
  const root = document.getElementById('notes-list');
  if (!root) return;

  // Edit toggles
  root.addEventListener('click', function(e){
    if (e.target && e.target.classList.contains('btn-edit')) {
      const li = e.target.closest('.note-item');
      const view = li.querySelector('.note-body');
      const form = li.querySelector('.note-edit');
      view.style.display = 'none';
      form.style.display = 'block';
    }
    if (e.target && e.target.classList.contains('btn-cancel-edit')) {
      const li = e.target.closest('.note-item');
      const view = li.querySelector('.note-body');
      const form = li.querySelector('.note-edit');
      form.style.display = 'none';
      view.style.display = 'block';
    }
  });

  // Progressive pagination
  const moreBtn = document.getElementById('notes-more');
  const lessBtn = document.getElementById('notes-less');
  if (moreBtn && lessBtn) {
    moreBtn.addEventListener('click', function(){
      root.querySelectorAll('.note-hidden').forEach(el => { el.style.display='block'; });
      moreBtn.style.display = 'none';
      lessBtn.style.display = 'inline-block';
    });
    lessBtn.addEventListener('click', function(){
      const per = <?= (int)$PER_PAGE ?>;
      root.querySelectorAll('.note-item').forEach((el, idx) => {
        el.style.display = (idx < per) ? 'block' : 'none';
      });
      lessBtn.style.display = 'none';
      moreBtn.style.display = 'inline-block';
    });
  }
})();
</script>
