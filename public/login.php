<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

if (Auth::check()) {
    header('Location: index.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string) ($_POST['csrf_token'] ?? '');

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $error = 'Sesija baigėsi, bandykite dar kartą.';
    } else {
        $identifier = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($identifier === '' || $password === '' || !Auth::attempt($identifier, $password)) {
            $error = 'Neteisingas vartotojo vardas arba slaptažodis.';
        } else {
            header('Location: index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prisijungimas — Tvarkaraštis</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">
    <form class="login-card" method="post" action="login.php" novalidate>
        <h1>Tvarkaraštis</h1>
        <?php if ($error !== null): ?>
            <p class="login-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
        <label for="username">Vartotojo vardas arba el. paštas</label>
        <input type="text" id="username" name="username" autocomplete="username" required autofocus>
        <label for="password">Slaptažodis</label>
        <input type="password" id="password" name="password" autocomplete="current-password" required>
        <button type="submit" class="button">Prisijungti</button>
    </form>
</body>
</html>
