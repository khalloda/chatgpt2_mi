<?php
use function App\Core\base_url;
use function App\Core\csrf_field;
/** @var array $warehouses, $products */
?>
<section>
  <h2>New Stock Adjustment</h2>
  <form method="post" action="<?= base_url('/adjustments') ?>">
    <?= csrf_field() ?>
    <div style="display:flex;gap:12px;flex-wrap:wrap;">
      <label>Warehouse
        <select name="warehouse_id" required style="padding:8px;border:1px solid #ddd;border-radius:6px;min-width:220px;">
          <option value="">-- choose --</option>
          <?php foreach ($warehouses as $w): ?>
            <option value="<?= (int)$w['id'] ?>"><?= htmlspecialchars($w['name'],ENT_QUOTES,'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Reason
        <select name="reason" style="padding:8px;border:1px solid #ddd;border-radius:6px;min-width:160px;">
          <option value="count">Stock count</option>
          <option value="damage">Damage</option>
          <option value="shrink">Shrink</option>
          <option value="other">Other</option>
        </select>
      </label>
    </div>

    <table id="lines" style="width:100%;border-collapse:collapse;margin-top:12px;">
      <thead><tr>
        <th style="border-bottom:1px solid #eee;padding:8px;">Product</th>
        <th style="border-bottom:1px solid #eee;padding:8px;text-align:right;">Qty change (+/-)</th>
        <th style="border-bottom:1px solid #eee;padding:8px;">&nbsp;</th>
      </tr></thead>
      <tbody></tbody>
    </table>

    <p><button type="button" id="add-line" style="padding:6px 10px;border:1px solid #ddd;border-radius:8px;background:#fff;cursor:pointer;">+ Add line</button></p>

    <p>
      <label>Note<br>
        <textarea name="note" rows="3" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;"></textarea>
      </label>
    </p>

    <p><button type="submit" style="padding:8px 12px;border:0;border-radius:8px;background:#111;color:#fff;cursor:pointer;">Save Adjustment</button>
       <a href="<?= base_url('/adjustments') ?>" style="margin-left:8px;">Cancel</a></p>
  </form>
</section>

<script>
(function(){
  const products = <?= json_encode($products, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>;
  const tbody = document.querySelector('#lines tbody');
  const btn   = document.getElementById('add-line');

  function addRow() {
    const tr = document.createElement('tr');

    const tdProd = document.createElement('td');
    tdProd.style.padding='8px'; tdProd.style.borderBottom='1px solid #f2f2f4';
    const sel = document.createElement('select');
    sel.name='product_id[]';
    sel.required = true;
    sel.style.cssText = 'padding:8px;border:1px solid #ddd;border-radius:6px;min-width:320px;';
    sel.innerHTML = '<option value="">-- choose product --</option>' +
      products.map(p => `<option value="${p.id}">${(p.code||'') + ' — ' + (p.name||'')}</option>`).join('');
    tdProd.appendChild(sel);

    const tdQty = document.createElement('td');
    tdQty.style.padding='8px'; tdQty.style.borderBottom='1px solid #f2f2f4'; tdQty.style.textAlign='right';
    const qty = document.createElement('input');
    qty.type='number'; qty.name='qty_change[]'; qty.step='1'; qty.required = true;
    qty.placeholder = 'e.g. 5 or -2';
    qty.style.cssText='padding:8px;border:1px solid #ddd;border-radius:6px;width:140px;text-align:right;';
    tdQty.appendChild(qty);

    const tdAct = document.createElement('td');
    tdAct.style.padding='8px'; tdAct.style.borderBottom='1px solid #f2f2f4';
    const rm = document.createElement('button');
    rm.type='button'; rm.textContent='Remove';
    rm.style.cssText='padding:6px 10px;border:1px solid #cc0000;color:#cc0000;background:#fff;border-radius:8px;cursor:pointer;';
    rm.addEventListener('click', ()=>tr.remove());
    tdAct.appendChild(rm);

    tr.appendChild(tdProd); tr.appendChild(tdQty); tr.appendChild(tdAct);
    tbody.appendChild(tr);
  }

  btn.addEventListener('click', addRow);
  addRow();
})();
</script>
