<?php /** @var string $content */ ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Spare Parts App</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin:0; padding:24px; background:#f7f7f9; color:#111; }
    .container { max-width: 980px; margin: 0 auto; background:#fff; border-radius:12px; padding:24px; box-shadow: 0 2px 16px rgba(0,0,0,.06); }
    header { margin-bottom: 16px; }
    code, pre { background:#f2f2f4; padding:2px 6px; border-radius:6px; }
    a { color:#0a58ca; text-decoration: none; }
  </style>
</head>
<body>
  <div class="container">
    <header>
      <h1>Spare Parts Management</h1>
      <nav>
        <a href="/">Home</a> ·
        <a href="/health">Health</a>
      </nav>
    </header>
    <main>
      <?= $content ?>
    </main>
  </div>
</body>
</html>
