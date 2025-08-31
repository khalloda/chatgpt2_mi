<?php use function App\Core\base_url; ?>
<section>
  <h2>Suppliers</h2>
  <p><a href="<?= base_url('/purchaseorders') ?>">Back to Purchase Orders</a></p>

  <table style="width:100%;border-collapse:collapse;">
    <thead>
      <tr>
        <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Name</th>
        <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Phone</th>
        <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Email</th>
        <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Address</th>
        <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Balance</th>
        <th style="border-bottom:1px solid #eee;padding:8px;">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach (($items ?? []) as $s): ?>
        <tr>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($s['name'],ENT_QUOTES,'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($s['phone'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($s['email'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($s['address'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;text-align:right;">
            <?= number_format((float)($s['balance'] ?? 0), 2) ?>
          </td>
          <td style="padding:8px;border-bottom:1px solid #f2f2f4;">
            <!-- Adjust links to whatever routes you already have -->
            <a href="<?= base_url('/suppliers/show?id='.(int)$s['id']) ?>">View</a>
            <?php /* If you have edit:
            · <a href="<?= base_url('/suppliers/edit?id='.(int)$s['id']) ?>">Edit</a>
            */ ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($items)): ?>
        <tr><td colspan="6" style="padding:12px;">No suppliers found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</section>
