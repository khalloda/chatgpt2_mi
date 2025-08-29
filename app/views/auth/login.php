<?php use function App\Core\csrf_field; ?>
<section>
  <h2>Login</h2>
  <p>Enter your credentials.</p>
  <?php if (!empty($error)): ?>
    <div style="background:#ffe9e9;border:1px solid #ffb3b3;padding:10px;border-radius:8px;margin:10px 0;">
      <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>
  <form method="post" action="/login" style="display:grid;gap:12px;max-width:380px;">
    <?= csrf_field() ?>
    <label>
      <div>Email</div>
      <input type="email" name="email" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
    </label>
    <label>
      <div>Password</div>
      <input type="password" name="password" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
    </label>
    <button type="submit" style="padding:10px 14px;border:0;border-radius:10px;background:#111;color:#fff;cursor:pointer;">Sign in</button>
  </form>
</section>
