<?php ob_start(); ?>
<div class="eyebrow">SAJILI · SECURE ACCESS</div>
<h1>Sign in</h1>
<p class="muted"><?= htmlspecialchars($organizationName ?? 'Company Portal', ENT_QUOTES, 'UTF-8') ?></p>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<form method="post" action="/login" class="form">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

    <label>
        Email
        <input type="email" name="email" autocomplete="username" required>
    </label>

    <label>
        Password
        <input type="password" name="password" autocomplete="current-password" required>
    </label>

    <button type="submit">Sign in</button>
</form>
<?php $content = ob_get_clean(); ?>

<?php ob_start(); ?>
<?= $content ?>
<?php $content = ob_get_clean(); ?>
