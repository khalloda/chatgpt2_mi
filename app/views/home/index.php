<section>
  <h2>Bootstrap OK</h2>
  <p>Router, controller, view, layout are wired.</p>

  <p>Database connection: <strong><?= $db_ok ? 'OK' : 'FAILED' ?></strong></p>

  <?php if (!$db_ok && !empty($debug) && !empty($db_error)): ?>
    <pre><?= htmlspecialchars($db_error, ENT_QUOTES, 'UTF-8') ?></pre>
  <?php endif; ?>

  <p>Health check: <a href="/health">/health</a></p>
</section>
