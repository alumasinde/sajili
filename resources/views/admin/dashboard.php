<div class="topbar">
    <div>
        <div class="eyebrow">ADMINISTRATION</div>
        <h1>Dashboard</h1>
        <p class="muted">Welcome, <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name'], ENT_QUOTES, 'UTF-8') ?>.</p>
    </div>
    <form method="post" action="/logout">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
        <button class="button-secondary" type="submit">Sign out</button>
    </form>
</div>

<div class="stats">
    <div class="stat"><span>Users</span><strong><?= (int) $userCount ?></strong></div>
    <div class="stat"><span>Departments</span><strong><?= (int) $departmentCount ?></strong></div>
    <div class="stat"><span>Roles</span><strong><?= (int) $roleCount ?></strong></div>
</div>

<section class="panel-inner">
    <h2>Access</h2>
    <p class="muted">Your account is authenticated inside the current company tenant.</p>
</section>
