<?php
/** @var string $content */
?>
<!DOCTYPE html>
<html lang="cs" data-theme="emerald">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Roll Call</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4/dist/full.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3/dist/tabler-icons.min.css" rel="stylesheet" type="text/css" />
    <style>
        /* Sjednotí zaoblení napříč všemi DaisyUI tématy (jinak forest dělá pill buttony) */
        [data-theme] {
            --rounded-btn: 0.5rem;   /* tlačítka, inputy, selecty */
            --rounded-box: 0.75rem;  /* karty, dropdowny */
            --tab-radius: 0.5rem;
        }
    </style>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Nastav uložené téma co nejdřív (proti bliknutí při načtení)
        (function () {
            const t = localStorage.getItem('theme');
            if (t) document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
</head>
<body class="min-h-screen flex flex-col bg-base-200">

    <nav class="navbar bg-base-100 shadow px-4">
        <div class="flex-1">
            <a href="<?= BASE_PATH ?>/" class="btn btn-ghost text-xl gap-2 normal-case">
                <img src="<?= BASE_PATH ?>/assets/mascot.svg" alt="" class="h-7 w-7">
                <span class="font-bold">Roll<span class="text-primary">Call</span></span>
            </a>
        </div>

        <div class="flex-none items-center gap-1">
            <button id="theme-toggle" class="btn btn-ghost btn-circle btn-sm" aria-label="Toggle dark mode">
                <i id="theme-icon" class="ti ti-moon text-lg"></i>
            </button>

            <?php if (Auth::check()): ?>
                <a href="<?= BASE_PATH ?>/sessions/create" class="btn btn-primary btn-sm gap-1 hidden sm:inline-flex">
                    <i class="ti ti-plus"></i> Create session
                </a>

                <div class="dropdown dropdown-end">
                    <button tabindex="0" class="btn btn-ghost btn-circle" aria-label="Menu">
                        <?= Avatar::html(Auth::user()['username'], 'w-9 h-9') ?>
                    </button>
                    <ul tabindex="0" class="dropdown-content menu bg-base-100 shadow-lg rounded-box z-30 w-56 mt-2 p-2">
                        <li class="menu-title px-3 pt-1 text-xs opacity-60"><?= htmlspecialchars(Auth::user()['username']) ?></li>
                        <li><a href="<?= BASE_PATH ?>/"><i class="ti ti-layout-dashboard"></i> Dashboard</a></li>
                        <li><a href="<?= BASE_PATH ?>/sessions"><i class="ti ti-calendar-event"></i> Browse sessions</a></li>
                        <li class="sm:hidden"><a href="<?= BASE_PATH ?>/sessions/create"><i class="ti ti-plus"></i> Create session</a></li>
                        <li><hr class="border-base-200 my-1"></li>
                        <li><a href="<?= BASE_PATH ?>/logout" class="text-error"><i class="ti ti-logout"></i> Logout</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="<?= BASE_PATH ?>/sessions" class="btn btn-ghost btn-sm hidden sm:inline-flex">Browse</a>
                <a href="<?= BASE_PATH ?>/login" class="btn btn-ghost btn-sm">Login</a>
                <a href="<?= BASE_PATH ?>/register" class="btn btn-primary btn-sm">Register</a>
            <?php endif; ?>
        </div>
    </nav>

    <main class="container mx-auto p-4 flex-1 w-full max-w-5xl">
        <?= $content ?>
    </main>

    <footer class="footer footer-center p-4 bg-base-100 text-base-content/60 text-sm">
        <span class="flex items-center gap-2">
            <i class="ti ti-dice text-primary"></i>
            <span><span class="font-semibold">Roll Call</span> — board game session organizer · powered by
                <a href="https://boardgamegeek.com" class="link" target="_blank" rel="noopener">BoardGameGeek</a></span>
        </span>
    </footer>

    <script>
        (function () {
            const LIGHT = 'emerald', DARK = 'forest';
            const btn  = document.getElementById('theme-toggle');
            const icon = document.getElementById('theme-icon');

            const apply = (t) => {
                document.documentElement.setAttribute('data-theme', t);
                icon.className = 'ti text-lg ' + (t === DARK ? 'ti-sun' : 'ti-moon');
            };

            apply(localStorage.getItem('theme') || LIGHT);

            btn.addEventListener('click', () => {
                const cur  = document.documentElement.getAttribute('data-theme');
                const next = (cur === DARK) ? LIGHT : DARK;
                localStorage.setItem('theme', next);
                apply(next);
            });
        })();
    </script>

</body>
</html>
