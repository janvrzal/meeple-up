<?php
/** @var string $content */
/** @var string $base */
?>

<!DOCTYPE html>
<html lang="cs" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MeepleUp</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-base-200">
    <div class="flex-1">
        <a href="<?= BASE_PATH ?>/" class="btn btn-ghost text-xl">🎲 Meeple-Up</a>
    </div>
    <div class="flex-none gap-2">
        <?php if (Auth::check()): ?>
            <span class="px-2"><?= htmlspecialchars(Auth::user()['username']) ?></span>
            <a href="<?= BASE_PATH ?>/logout" class="btn btn-sm">Logout</a>
        <?php else: ?>
            <a href="<?= BASE_PATH ?>/login" class="btn btn-sm">Login</a>
            <a href="<?= BASE_PATH ?>/register" class="btn btn-sm btn-primary">Register</a>
        <?php endif; ?>
    </div>

    <main class="container mx-auto p-4">
        <?= $content ?>
    </main>
</body>