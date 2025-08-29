<?php
use function App\Core\csrf_field;
use function App\Core\flash_get;
?>
<section>
  <h2>My Profile</h2>

  <?php if ($msg = flash_get('success')): ?>
    <div style="background:#e7f8ee;border:1px solid #b9e7c9;padding:10px;border-radius:8px;margin:10px 0;">
      <?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>

  <?php if ($err = flash_get('error')): ?>
    <div style="background:#ffe9e9;border:1px solid #ffb3b3;padding:10px;border-radius:8px;margin:10px 0;">
      <?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>

  <h3>Change Password</h3>
  <form method="post" action="/profile/password" style="display:grid;gap:12px;max-width:420px;">
    <?= csrf_field() ?>
    <label>
      <div>Current password</div>
      <input type="password" name="current_password" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
    </label>
    <label>
      <div>New password</div>
      <input type="password" name="new_password" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
    </label>
    <label>
      <div>Confirm new password</div>
      <input type="password" name="new_password_confirm" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
    </label>
    <button type="submit" style="padding:10px 14px;border:0;border-radius:10px;background:#111;color:#fff;cursor:pointer;">Update Password</button>
  </form>
</section>
