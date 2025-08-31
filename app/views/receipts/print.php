<?php
use function App\Core\base_url;
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>GRN — <?= htmlspecialchars($pi['pi_no'],ENT_QUOTES,'UTF-8') ?></title>
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
    <a class="no-print" href="<?= base_url('/purchaseinvoices/show?id='.(int)$pi['id']) ?>" style="margin-left:12px;">Back</a>
  </div>

  <div style="margin-bottom:10px;">
    <img src="<?= base_url('/img/logo.png') ?>" alt="Logo" style="height:48px;vertical-align:middle;">
  </div>

  <header>
    <h1>Goods Received Note</h1>
    <div class="muted">
      PI: <?= htmlspecialchars($pi['pi_no'],ENT_QUOTES,'UTF-8') ?> •
      Supplier: <?= htmlspecialchars($pi['supplier_name'],ENT_QUOTES,'UTF-8') ?> •
      PO: <?= htmlspecialchars($pi['po_no'],ENT_QUOTES,'UTF-8') ?>
    </div>
  </header>

  <h3 style="margin-top:12px;">Ordered vs Received (to date)</h3>
  <table>
    <thead><tr>
      <th>Product</th>
      <th>Warehouse</th>
      <th class="r">Ordered</th>
      <th class="r">Received</th>
    </tr></thead>
    <tbody>
      <?php
        // Build map ordered & received
        $ordered = [];
        foreach ($items as $it) {
          $k = $it['product_id'].':'.$it['warehouse_id'];
          $ordered[$k] = ($ordered[$k] ?? 0) + (int)$it['qty'];
        }
        $receivedMap = [];
        foreach ($receipts as $r) {
          $k = $r['product_id'].':'.$r['warehouse_id'];
          $receivedMap[$k] = ($receivedMap[$k] ?? 0) + (int)$r['qty'];
        }
        foreach ($items as $it):
          $k = $it['product_id'].':'.$it['warehouse_id'];
          $ord = (int)$ordered[$k];
          $rec = (int)($receivedMap[$k] ?? 0);
      ?>
        <tr>
          <td><?= htmlspecialchars($it['product_code'].' — '.$it['product_name'],ENT_QUOTES,'UTF-8') ?></td>
          <td><?= htmlspecialchars($it['warehouse_name'],ENT_QUOTES,'UTF-8') ?></td>
          <td class="r"><?= $ord ?></td>
          <td class="r"><?= $rec ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <?php if ($receipts): ?>
    <h3 style="margin-top:16px;">Receipt Lines</h3>
    <table>
      <thead><tr>
        <th>Date</th>
        <th>Product</th>
        <th>Warehouse</th>
        <th class="r">Qty</th>
        <th class="r">Price</th>
      </tr></thead>
      <tbody>
        <?php foreach ($receipts as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['created_at'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
            <td><?= htmlspecialchars(($r['product_code'] ?? '').' — '.($r['product_name'] ?? ''),ENT_QUOTES,'UTF-8') ?></td>
            <td><?= htmlspecialchars($r['warehouse_name'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
            <td class="r"><?= (int)$r['qty'] ?></td>
            <td class="r"><?= number_format((float)$r['price'],2) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</body>
</html>
