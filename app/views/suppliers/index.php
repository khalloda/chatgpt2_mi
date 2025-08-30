<?php use function App\Core\base_url; use function App\Core\csrf_field; ?>
<section>
  <h2>Suppliers</h2>
  <p><a href="<?= base_url('/suppliers/create') ?>">New Supplier</a></p>

  <table style="width:100%;border-collapse:collapse;">
    <thead><tr>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Name</th>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Phone</th>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Email</th>
      <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Address</th>
      <th style="border-bottom:1px solid #eee;padding:8px;">Actions</th>
    </tr></thead>
    <tbody>
    <?php foreach ($items as $s): ?>
      <tr>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($s['name'],ENT_QUOTES,'UTF-8') ?></td>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($s['phone'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($s['email'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;"><?= htmlspecialchars($s['address'] ?? '',ENT_QUOTES,'UTF-8') ?></td>
        <td style="padding:8px;border-bottom:1px solid #f2f2f4;">
          <a href="<?= base_url('/suppliers/edit?id='.(int)$s['id']) ?>">Edit</a>
          <form method="post" action="<?= base_url('/suppliers/delete') ?>" style="display:inline" onsubmit="return confirm('Delete supplier?');">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
            <button type="submit" style="border:1px solid #cc0000;color:#cc0000;background:#fff;border-radius:6px;padding:4px 8px;margin-left:6px;">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$items): ?><tr><td colspan="5" style="padding:12px;">No suppliers yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</section>
