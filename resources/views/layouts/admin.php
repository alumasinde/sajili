<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Administration', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
<main class="admin-shell">
    <aside class="sidebar">
        <div class="brand">SAJILI</div>
        <nav>
            <a href="/admin">Dashboard</a>
        </nav>
    </aside>
    <section class="admin-content">
        <?= $content ?? '' ?>
    </section>
</main>
</body>
</html>
