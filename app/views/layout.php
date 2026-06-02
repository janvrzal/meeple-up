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
    <nav class="navbar bg-base-100 shadow">
        <a href="<?= $base ?? '' ?>/" class="btn btn-ghost text-x1">🎲 MeepleUp</a>
    </nav>

    <main class="container mx-auto p-4">
        <?= $content ?>
    </main>
</body>