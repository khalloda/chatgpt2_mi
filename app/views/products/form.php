<?php
use function App\Core\base_url;
use function App\Core\csrf_field;
?>
<section>
  <h2><?= $mode==='create'?'New Product':'Edit Product' ?></h2>

  <form method="post" action="<?= $mode==='create'?base_url('/products'):base_url('/products/update') ?>" style="display:grid;gap:12px;max-width:720px;">
    <?= csrf_field() ?>
    <?php if ($mode==='edit'): ?><input type="hidden" name="id" value="<?= (int)$item['id'] ?>"><?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 2fr;gap:10px;">
      <label><div>Code</div>
        <input type="text" name="code" required value="<?= htmlspecialchars($item['code'] ?? '', ENT_QUOTES, 'UTF-8') ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
      </label>
      <label><div>Name</div>
        <input type="text" name="name" required value="<?= htmlspecialchars($item['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
      </label>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;">
      <label><div>Category</div>
        <select name="category_id" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
          <option value="">— none —</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?php if (!empty($item['category_id']) && (int)$item['category_id']===(int)$c['id']) echo 'selected'; ?>>
              <?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
      <label><div>Make</div>
        <select name="make_id" id="make_id" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
          <option value="">— none —</option>
          <?php foreach ($makes as $mk): ?>
            <option value="<?= (int)$mk['id'] ?>" <?php if (!empty($item['make_id']) && (int)$item['make_id']===(int)$mk['id']) echo 'selected'; ?>>
              <?= htmlspecialchars($mk['name'], ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
      <label><div>Model</div>
        <select name="model_id" id="model_id" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
          <option value="">— none —</option>
          <?php foreach ($models as $md): ?>
            <option data-make="<?= (int)$md['make_id'] ?>" value="<?= (int)$md['id'] ?>" <?php if (!empty($item['model_id']) && (int)$item['model_id']===(int)$md['id']) echo 'selected'; ?>>
              <?= htmlspecialchars($md['name'], ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
      <label><div>Cost</div>
        <input type="number" step="0.01" name="cost" value="<?= htmlspecialchars((string)($item['cost'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
      </label>
      <label><div>Price</div>
        <input type="number" step="0.01" name="price" value="<?= htmlspecialchars((string)($item['price'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
      </label>
    </div>

    <div style="display:flex;gap:10px;">
      <button type="submit" style="padding:10px 14px;border:0;border-radius:10px;background:#111;color:#fff;cursor:pointer;"><?= $mode==='create'?'Create':'Save Changes' ?></button>
      <a href="<?= base_url('/products') ?>" style="align-self:center;">Cancel</a>
    </div>
  </form>

  <script>
    // client-side filter: show only models that belong to selected make
    (function(){
      const makeSel=document.getElementById('make_id');
      const modelSel=document.getElementById('model_id');
      const allOpts=[...modelSel.querySelectorAll('option[data-make]')];
      function apply(){
        const mk=makeSel.value;
        allOpts.forEach(o=>{
          o.hidden = (mk && o.dataset.make !== mk);
          if (o.hidden && o.selected) { modelSel.value = ''; }
        });
      }
      makeSel.addEventListener('change', apply);
      apply();
    })();
  </script>
</section>
