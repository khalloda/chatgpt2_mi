<?php
use function App\Core\base_url;
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Credit Note <?= htmlspecialchars($sr['sr_no'],ENT_QUOTES,'UTF-8') ?></title>
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
  </style>
</head>
<body>
  <div class="toolbar no-print">
    <button type="button" onclick="window.print()" style="padding:6px 10px;border:1px solid #111;border-radius:8px;background:#111;color:#fff;cursor:pointer;">Print</button>
    <a class="no-print" href="<?= base_url('/invoices/show?id='.(int)$sr['sales_invoice_id']) ?>" style="margin-left:12px;">Back</a>
  </div>

  <div style="margin-bottom:10px;">
    <img src="<?= base_url('/img/logo.png') ?>" alt="Logo" style="height:48px;vertical-align:middle;">
  </div>

  <header>
    <h1>Credit Note <?= htmlspecialchars($sr['sr_no'],ENT_QUOTES,'UTF-8') ?></h1>
    <div class="muted">
      Against Invoice: <?= htmlspecialchars($inv['inv_no'] ?? '',ENT_QUOTES,'UTF-8') ?> •
      Client: <?= htmlspecialchars($inv['client_name'] ?? '',ENT_QUOTES,'UTF-8') ?>
    </div>
  </header>

  <table>
    <thead>
      <tr>
        <th>Product</th>
        <th>Warehouse</th>
        <th class="r">Qty</th>
        <th class="r">Price</th>
        <th class="r">Line Total</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach (($items ?? []) as $it): ?>
      <tr>
        <td><?= htmlspecialchars(($it['product_code'] ?? '').' — '.($it['product_name'] ?? ''),ENT_QUOTES,'UTF-8') ?></td>
        <td><?= htmlspecialchars($it['warehouse_name'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
        <td class="r"><?= (int)($it['qty'] ?? 0) ?></td>
        <td class="r"><?= number_format((float)($it['price'] ?? 0),2) ?></td>
        <td class="r"><?= number_format((float)($it['line_total'] ?? 0),2) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <p class="r" style="text-align:right;margin-top:8px;">
    Subtotal: <?= number_format((float)$sr['subtotal'],2) ?>
    &nbsp;| Tax (<?= number_format((float)$sr['tax_rate'],2) ?>%): <?= number_format((float)$sr['tax_amount'],2) ?>
    &nbsp;| <strong>Total: <?= number_format((float)$sr['total'],2) ?></strong>
  </p>
</body>
</html>
