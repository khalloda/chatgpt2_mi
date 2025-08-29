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
      <label><div>Tax %</div><input id="tax_rate" type="number" step="0.01" name="tax_rate" value="0"
         style="padding:10px;border:1px solid #ddd;border-radius:8px;"></label>
      <label><div>Expires at</div><input type="date" name="expires_at" style="padding:10px;border:1px solid #ddd;border-radius:8px;"></label>
    </div>

    <h3>Items</h3>
    <table style="width:100%;border-collapse:collapse;">
      <thead><tr>
  <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Product</th>
  <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Warehouse</th>
  <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Qty</th>
  <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Unit Price</th>
  <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Line Total</th>
</tr></thead>
      <tbody id="rows">
        <?php for ($i=0; $i<$item_rows; $i++): ?>
<tr>
  <td style="padding:6px;">
    <select name="product_id[]" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;">
      <option value="">— select —</option>
      <?php foreach ($products as $p): ?>
        <option value="<?= (int)$p['id'] ?>" data-price="<?= htmlspecialchars((string)$p['price'], ENT_QUOTES, 'UTF-8') ?>">
          <?= htmlspecialchars($p['label'], ENT_QUOTES, 'UTF-8') ?>
        </option>
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
  <td style="padding:6px;text-align:right;">
    <input type="number" min="0" name="qty[]" value="0"
           style="width:110px;padding:8px;border:1px solid #ddd;border-radius:6px;text-align:right;">
  </td>
  <td style="padding:6px;text-align:right;">
    <input type="number" step="0.01" min="0" name="price[]" value="0.00"
           style="width:130px;padding:8px;border:1px solid #ddd;border-radius:6px;text-align:right;">
  </td>
  <td style="padding:6px;text-align:right;">
    <input type="text" value="0.00" class="line-total"
           style="width:130px;padding:8px;border:1px solid #eee;background:#fafafa;border-radius:6px;text-align:right;"
           readonly>
  </td>
</tr>

        <?php endfor; ?>
      </tbody>
    </table>
	<div style="display:flex;gap:20px;justify-content:flex-end;margin-top:10px;">
  <div>Subtotal: <strong><span id="subtotal">0.00</span></strong></div>
  <div>Tax: <strong><span id="taxamount">0.00</span></strong></div>
  <div>Total: <strong><span id="grandtotal">0.00</span></strong></div>
</div>
    <button type="button" id="addrow" style="margin-top:8px;padding:6px 10px;border:1px solid #ddd;border-radius:8px;background:#f9f9fb;cursor:pointer;">+ Add row</button>

    <div style="display:flex;gap:10px;margin-top:12px;">
      <button type="submit" style="padding:10px 14px;border:0;border-radius:10px;background:#111;color:#fff;cursor:pointer;">Save Quote</button>
      <a href="<?= base_url('/quotes') ?>" style="align-self:center;">Cancel</a>
    </div>
  </form>

<script>
(function () {
  const rows = document.getElementById('rows');

  function setPriceFromProduct(selectEl) {
    const tr = selectEl.closest('tr');
    const priceInput = tr.querySelector('input[name="price[]"]');
    const opt = selectEl.options[selectEl.selectedIndex];
    const p = opt && opt.dataset.price ? parseFloat(opt.dataset.price) : 0;
    // only overwrite when empty or zero, so manual edits are respected
    const cur = parseFloat(priceInput.value || '0');
    if (!cur || cur === 0) priceInput.value = (isNaN(p) ? 0 : p).toFixed(2);
  }

  // delegate change events for any current/future rows
  rows.addEventListener('change', function (e) {
    if (e.target && e.target.name === 'product_id[]') {
      setPriceFromProduct(e.target);
    }
  });

  // Add row button – clone first row, but clear inputs
  const addBtn = document.getElementById('addrow');
  addBtn.addEventListener('click', function () {
    const tr0 = rows.children[0];
    const tr = tr0.cloneNode(true);
    // reset fields
    tr.querySelector('select[name="product_id[]"]').value = '';
    tr.querySelector('select[name="warehouse_id[]"]').value = '';
    tr.querySelector('input[name="qty[]"]').value = '0';
    tr.querySelector('input[name="price[]"]').value = '0.00';
    rows.appendChild(tr);
  });

  // initialize existing rows (fill price if 0)
  [...rows.querySelectorAll('select[name="product_id[]"]')].forEach(setPriceFromProduct);
})();
</script>

</section>
