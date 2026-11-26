<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title) ?> | Beheer Stevig</title>
    <link rel="icon" href="<?= e(asset('img/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body class="admin auth-page">
<main class="auth-box panel">
    <p class="logo"><span class="logo-word">STEVIG</span><span class="logo-sub">beheer</span></p>
    <h1><?= e($title) ?></h1>
    <?php if (!empty($intro)): ?><p class="muted"><?= e($intro) ?></p><?php endif; ?>
    <?php foreach (take_flashes() as $flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div>
    <?php endforeach; ?>
    <?php if (!empty($demoLogin)): ?>
        <div class="alert alert-info" role="note">
            <strong>Demo-inlog</strong><br>
            E-mailadres: <code><?= e($demoLogin['email']) ?></code><br>
            Wachtwoord: <code><?= e($demoLogin['password']) ?></code>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
    <form class="form" method="post">
        <?= Csrf::field() ?>
        <?php if (!empty($confirm)): ?>
            <div class="field">
                <label for="name">Naam</label>
                <input type="text" id="name" name="name" value="<?= e($name ?? '') ?>" maxlength="80" autocomplete="name" required>
            </div>
        <?php endif; ?>
        <div class="field">
            <label for="email">E-mailadres</label>
            <input type="email" id="email" name="email" value="<?= e($email ?? '') ?>" autocomplete="username" required>
        </div>
        <div class="field">
            <label for="password">Wachtwoord</label>
            <?php if (!empty($confirm)): ?><span class="hint" id="password-hint">Minimaal 12 tekens. Een zin van een paar woorden werkt goed.</span><?php endif; ?>
            <input type="password" id="password" name="password" autocomplete="<?= e($passwordAutocomplete) ?>" required<?= !empty($confirm) ? ' minlength="12" maxlength="72" aria-describedby="password-hint"' : '' ?>>
        </div>
        <?php if (!empty($confirm)): ?>
            <div class="field">
                <label for="password_confirm">Herhaal wachtwoord</label>
                <input type="password" id="password_confirm" name="password_confirm" autocomplete="new-password" required minlength="12" maxlength="72">
            </div>
        <?php endif; ?>
        <button class="btn btn-primary" type="submit"><?= e($button) ?></button>
    </form>
</main>
</body>
</html>
