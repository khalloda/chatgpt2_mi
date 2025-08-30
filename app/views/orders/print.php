<?php
use function App\Core\base_url;
use function App\Core\format_note_html;
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Sales Order <?= htmlspecialchars($o['so_no'], ENT_QUOTES, 'UTF-8') ?></title>
  <style>
    body{font-family:Arial, sans-serif; margin:24px;}
    h1{margin:0 0 8px 0;}
    .muted{color:#666;}
    table{width:100%;border-collapse:collapse;margin-top:12px;}
    th,td{padding:8px;border-bottom:1px solid #eee;text-align:left;}
    td.r, th.r {text-align:right;}
    .toolbar{margin-bottom:12px; padding:10px; border:1px solid #eee; border-radius:8px;}
    @media print{ .no-print{ display:none !important; } .toolbar{ display:none !important; } }
	@page { size: A4; margin: 16mm; }
@media print {
  footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 11px; color:#666; }
  thead { display: table-header-group; }
  tr { break-inside: avoid; }
}
  </style>
</head>
<body>
  <div class="toolbar no-print">
    <form method="get" action="<?= base_url('/orders/print') ?>" style="display:flex;gap:12px;align-items:center;">
      <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
      <label style="display:flex;gap:6px;align-items:center;">
        <input type="checkbox" name="include_notes" value="1" <?= $include_notes ? 'checked' : '' ?>> Include public notes
      </label>
      <button type="submit" style="padding:6px 10px;border:1px solid #ddd;border-radius:8px;background:#f9f9fb;cursor:pointer;">Apply</button>
      <button type="button" onclick="window.print()" style="padding:6px 10px;border:1px solid #111;border-radius:8px;background:#111;color:#fff;cursor:pointer;">Print</button>
      <a class="no-print" href="<?= base_url('/orders/show?id='.(int)$o['id']) ?>" style="margin-left:auto;">Back</a>
    </form>
  </div>

  <header>
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
  <div>
    <div style="font-weight:700;font-size:20px;">Spare Parts Management</div>
    <div class="muted">Address line • Phone • Email</div>
  </div>
  <!-- brand/logo -->
  <div style="margin-bottom:10px;">
    <img src="<?= base_url('/img/logo.png') ?>" alt="Logo" style="height:48px;vertical-align:middle;">
  </div>
  <!-- Optional logo: <img src="/public/img/logo.png" alt="Logo" style="height:48px"> -->
</div>

    <h1>Sales Order <?= htmlspecialchars($o['so_no'], ENT_QUOTES, 'UTF-8') ?></h1>
    <div class="muted">Customer ID: <?= (int)$o['customer_id'] ?> • Status: <?= htmlspecialchars($o['status'], ENT_QUOTES, 'UTF-8') ?></div>
  </header>

  <table>
    <thead>
      <tr>
        <th>Product</th>
        <th>Warehouse</th>
        <th class="r">Qty</th>
        <th class="r">Unit Price</th>
        <th class="r">Line Total</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $it): ?>
      <tr>
        <td><?= htmlspecialchars($it['product_code'].' — '.$it['product_name'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars($it['warehouse_name'], ENT_QUOTES, 'UTF-8') ?></td>
        <td class="r"><?= (int)$it['qty'] ?></td>
        <td class="r"><?= number_format((float)$it['price'],2) ?></td>
        <td class="r"><?= number_format((float)$it['line_total'],2) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <p class="r" style="text-align:right;margin-top:8px;">
    Subtotal: <?= number_format((float)$o['subtotal'],2) ?>
    &nbsp;|&nbsp; Tax (<?= number_format((float)$o['tax_rate'],2) ?>%): <?= number_format((float)$o['tax_amount'],2) ?>
    &nbsp;|&nbsp; <strong>Total: <?= number_format((float)$o['total'],2) ?></strong>
  </p>

  <?php if (!empty($public_notes) && $include_notes): ?>
    <section style="margin-top:16px;">
      <h3>Public Notes</h3>
      <ul style="list-style:none;padding:0;display:grid;gap:8px;">
        <?php foreach ($public_notes as $n): ?>
          <li style="border:1px solid #eee;border-radius:8px;padding:10px;">
            <div style="font-size:12px;color:#666;"><?= htmlspecialchars($n['created_by'] ?? 'system', ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($n['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
            <div style="margin-top:6px;"><?= format_note_html($n['body'] ?? '') ?></div>
          </li>
        <?php endforeach; ?>
      </ul>
    </section>
  <?php endif; ?>
  <footer>Page <span class="pageNumber"></span></footer>
<script>try{document.querySelector('.pageNumber').textContent='';}catch(e){}</script>

</body>
</html>
