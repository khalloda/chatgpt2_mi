<?php
use function App\Core\base_url;
use function App\Core\csrf_field;
?>
<section>
  <h2>New Quote</h2>

  <form method="post" action="<?= base_url('/quotes') ?>" style="display:grid;gap:12px;">
    <?= csrf_field() ?>

    <div><strong>Quote No:</strong> <?= htmlspecialchars($quote_no, ENT_QUOTES, 'UTF-8') ?> (assigned on save)</div>

    <label><div>Customer</div>
      <select name="customer_id" required style="padding:10px;border:1px solid #ddd;border-radius:8px;">
        <option value="">— select —</option>
        <?php foreach ($customers as $c): ?>
          <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>
    </label>

    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;">
      <label><div>Tax %</div><input type="number" step="0.01" name="tax_rate" value="0" style="padding:10px;border:1px solid #ddd;border-radius:8px;"></label>
      <label><div>Expires at</div><input type="date" name="expires_at" style="padding:10px;border:1px solid #ddd;border-radius:8px;"></label>
    </div>

    <h3>Items</h3>
    <table style="width:100%;border-collapse:collapse;">
      <thead><tr>
        <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Product</th>
        <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Warehouse</th>
        <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Qty</th>
        <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Price</th>
      </tr></thead>
      <tbody id="rows">
        <?php for ($i=0; $i<$item_rows; $i++): ?>
          <tr>
            <td style="padding:6px;">
              <select name="product_id[]" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;">
                <option value="">— select —</option>
                <?php foreach ($products as $p): ?>
                  <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['label'], ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
              </select>
            </td>
            <td style="padding:6px;">
              <select name="warehouse_id[]" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;">
                <option value="">— select —</option>
                <?php foreach ($warehouses as $w): ?>
                  <option value="<?= (int)$w['id'] ?>"><?= htmlspecialchars($w['name'], ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
              </select>
            </td>
            <td style="padding:6px;text-align:right;"><input type="number" min="0" name="qty[]" value="0" style="width:110px;padding:8px;border:1px solid #ddd;border-radius:6px;text-align:right;"></td>
            <td style="padding:6px;text-align:right;"><input type="number" step="0.01" min="0" name="price[]" value="0.00" style="width:130px;padding:8px;border:1px solid #ddd;border-radius:6px;text-align:right;"></td>
          </tr>
        <?php endfor; ?>
      </tbody>
    </table>
    <button type="button" id="addrow" style="margin-top:8px;padding:6px 10px;border:1px solid #ddd;border-radius:8px;background:#f9f9fb;cursor:pointer;">+ Add row</button>

    <div style="display:flex;gap:10px;margin-top:12px;">
      <button type="submit" style="padding:10px 14px;border:0;border-radius:10px;background:#111;color:#fff;cursor:pointer;">Save Quote</button>
      <a href="<?= base_url('/quotes') ?>" style="align-self:center;">Cancel</a>
    </div>
  </form>

  <script>
    document.getElementById('addrow').addEventListener('click', function(){
      const rows=document.getElementById('rows');
      const tr=document.createElement('tr');
      tr.innerHTML = rows.children[0].innerHTML;
      rows.appendChild(tr);
    });
  </script>
</section>
