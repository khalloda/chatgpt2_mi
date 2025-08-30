<?php
use function App\Core\base_url;
use function App\Core\csrf_field;
/** @var string $mode */
/** @var array $po, $items, $suppliers, $products, $warehouses */
?>
<section>
  <h2><?= $mode==='create' ? 'New Purchase Order' : 'Edit Purchase Order' ?></h2>

  <form method="post" action="<?= base_url($mode==='create' ? '/purchaseorders' : '/purchaseorders/update') ?>">
    <?= csrf_field() ?>
    <?php if ($mode==='edit'): ?><input type="hidden" name="id" value="<?= (int)$po['id'] ?>"><?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;max-width:1100px;">
      <label><div>PO #</div>
        <input type="text" value="<?= htmlspecialchars($po['po_no'],ENT_QUOTES,'UTF-8') ?>" disabled
               style="padding:8px;border:1px solid #ddd;border-radius:6px;width:100%;background:#f7f7f9;">
      </label>

      <label><div>Supplier</div>
        <select name="supplier_id" required style="padding:8px;border:1px solid #ddd;border-radius:6px;width:100%;">
          <option value="">-- Select supplier --</option>
          <?php foreach ($suppliers as $s): ?>
            <option value="<?= (int)$s['id'] ?>" <?= ((int)($po['supplier_id'] ?? 0) === (int)$s['id']) ? 'selected':'' ?>>
              <?= htmlspecialchars($s['name'],ENT_QUOTES,'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <label><div>Tax Rate (%)</div>
        <input id="tax_rate" type="number" step="0.01" min="0" name="tax_rate" value="<?= (float)($po['tax_rate'] ?? 0) ?>"
               style="padding:8px;border:1px solid #ddd;border-radius:6px;width:100%;">
      </label>
    </div>

    <h3 style="margin-top:14px;">Items</h3>
    <table id="po-items" style="width:100%;border-collapse:collapse;">
      <thead><tr>
        <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Product</th>
        <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Warehouse</th>
        <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Qty</th>
        <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Price</th>
        <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Line Total</th>
        <th style="border-bottom:1px solid #eee;padding:8px;">#</th>
      </tr></thead>
      <tbody>
        <?php
          $rows = $items ?: [['product_id'=>'','warehouse_id'=>'','qty'=>1,'price'=>0,'line_total'=>0]];
          foreach ($rows as $r):
        ?>
          <tr>
            <td style="padding:6px;border-bottom:1px solid #f2f2f4;">
              <select name="item_product_id[]" required style="width:100%;padding:6px;border:1px solid #ddd;border-radius:6px;">
                <option value="">-- Product --</option>
                <?php foreach ($products as $p): ?>
                  <option value="<?= (int)$p['id'] ?>" data-price="<?= (float)$p['price'] ?>"
                    <?= ((int)($r['product_id'] ?? 0) === (int)$p['id']) ? 'selected':'' ?>>
                    <?= htmlspecialchars($p['code'].' — '.$p['name'],ENT_QUOTES,'UTF-8') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </td>
            <td style="padding:6px;border-bottom:1px solid #f2f2f4;">
              <select name="item_warehouse_id[]" required style="width:100%;padding:6px;border:1px solid #ddd;border-radius:6px;">
                <option value="">-- Warehouse --</option>
                <?php foreach ($warehouses as $w): ?>
                  <option value="<?= (int)$w['id'] ?>" <?= ((int)($r['warehouse_id'] ?? 0) === (int)$w['id']) ? 'selected':'' ?>>
                    <?= htmlspecialchars($w['name'],ENT_QUOTES,'UTF-8') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </td>
            <td style="padding:6px;border-bottom:1px solid #f2f2f4;text-align:right;">
              <input class="qty" name="item_qty[]" type="number" min="1" step="1" value="<?= (int)($r['qty'] ?? 1) ?>"
                     style="width:100%;padding:6px;border:1px solid #ddd;border-radius:6px;text-align:right;">
            </td>
            <td style="padding:6px;border-bottom:1px solid #f2f2f4;text-align:right;">
              <input class="price" name="item_price[]" type="number" step="0.01" min="0" value="<?= number_format((float)($r['price'] ?? 0),2,'.','') ?>"
                     style="width:100%;padding:6px;border:1px solid #ddd;border-radius:6px;text-align:right;">
            </td>
            <td style="padding:6px;border-bottom:1px solid #f2f2f4;text-align:right;">
              <span class="line_total"><?= number_format((float)($r['line_total'] ?? 0),2) ?></span>
            </td>
            <td style="padding:6px;border-bottom:1px solid #f2f2f4;text-align:center;">
              <button type="button" class="btn-remove" style="padding:4px 8px;border:1px solid #cc0000;color:#cc0000;border-radius:6px;background:#fff;cursor:pointer;">Remove</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div style="margin-top:8px;">
      <button id="btn-add-row" type="button" style="padding:6px 10px;border:1px solid #ddd;border-radius:8px;background:#f9f9fb;cursor:pointer;">Add Row</button>
    </div>

    <p style="text-align:right;margin-top:8px;">
      Subtotal: <span id="subtotal">0.00</span>
      &nbsp;| Tax (<span id="tax_rate_view"><?= number_format((float)($po['tax_rate'] ?? 0),2) ?></span>%): <span id="tax_amount">0.00</span>
      &nbsp;| <strong>Total: <span id="grand_total">0.00</span></strong>
    </p>

    <div style="margin-top:10px;">
      <button type="submit" style="padding:8px 12px;border:0;border-radius:8px;background:#111;color:#fff;cursor:pointer;"><?= $mode==='create' ? 'Create PO' : 'Save PO' ?></button>
      <a href="<?= base_url('/purchaseorders') ?>" style="margin-left:8px;">Back</a>
    </div>
  </form>
</section>

<script>
(function(){
  const table = document.getElementById('po-items');
  const taxRateInput = document.getElementById('tax_rate');
  const taxRateView = document.getElementById('tax_rate_view');
  function recalc(){
    let subtotal = 0;
    table.querySelectorAll('tbody tr').forEach(row=>{
      const qtyEl = row.querySelector('.qty');
      const priceEl = row.querySelector('.price');
      const qty = parseFloat(qtyEl.value || '0');
      const price = parseFloat(priceEl.value || '0');
      const lt = (qty>0 && price>=0)? (qty*price) : 0;
      row.querySelector('.line_total').textContent = lt.toFixed(2);
      subtotal += lt;
    });
    const tr = parseFloat(taxRateInput.value || '0');
    taxRateView.textContent = (isNaN(tr)?0:tr).toFixed(2);
    const taxAmount = subtotal * (tr/100);
    document.getElementById('subtotal').textContent = subtotal.toFixed(2);
    document.getElementById('tax_amount').textContent = taxAmount.toFixed(2);
    document.getElementById('grand_total').textContent = (subtotal+taxAmount).toFixed(2);
  }
  table.addEventListener('input', recalc);
  if (taxRateInput) taxRateInput.addEventListener('input', recalc);

  // Default price from selected product
  table.addEventListener('change', function(e){
    if (e.target && e.target.tagName === 'SELECT' && e.target.name === 'item_product_id[]') {
      const opt = e.target.selectedOptions[0];
      if (!opt) return;
      const price = parseFloat(opt.getAttribute('data-price') || '0');
      const row = e.target.closest('tr');
      const priceEl = row.querySelector('.price');
      if (priceEl && !priceEl.value) { priceEl.value = price.toFixed(2); }
      recalc();
    }
  });

  // Remove row
  table.addEventListener('click', function(e){
    if (e.target && e.target.classList.contains('btn-remove')) {
      const row = e.target.closest('tr');
      row.parentNode.removeChild(row);
      recalc();
    }
  });

  // Add row
  document.getElementById('btn-add-row').addEventListener('click', function(){
    const tbody = table.querySelector('tbody');
    const tpl = tbody.querySelector('tr');
    const clone = tpl.cloneNode(true);
    // reset fields
    clone.querySelectorAll('select').forEach(s=>{ s.value=''; });
    clone.querySelector('.qty').value = '1';
    clone.querySelector('.price').value = '';
    clone.querySelector('.line_total').textContent = '0.00';
    tbody.appendChild(clone);
    recalc();
  });

  recalc();
})();
</script>
