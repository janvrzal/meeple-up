<?php
/** @var array $user */
/** @var array $mySessions */
/** @var array $pending */
/** @var array $stats */
/** @var array $tournaments */
$tournaments = $tournaments ?? [];

// rozdělení dat
$active    = array_values(array_filter($mySessions, fn($s) => $s['status'] === 'open'));
$cancelled = array_values(array_filter($mySessions, fn($s) =>
    $s['status'] === 'cancelled' && (int) $s['creator_id'] !== Auth::id()
));
$next      = $active[0] ?? null;
$others    = array_slice($active, 1);

$notificationCount = count($pending) + count($cancelled);

// kalendář: mapa 'Y-m-d' => [sezení toho dne]
$calMap = [];
foreach ($active as $cs) {
    $d = date('Y-m-d', strtotime($cs['scheduled_at']));
    $calMap[$d][] = [
        'id'    => (int) $cs['id'],
        'title' => $cs['title'],
        'time'  => date('H:i', strtotime($cs['scheduled_at'])),
    ];
}
?>

<?php /* ===== Pozdrav + statistiky + akce ===== */ ?>
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <h1 class="text-2xl font-bold">Hi, <?= htmlspecialchars($user['username']) ?></h1>
    <div class="flex flex-wrap gap-2">
        <a href="<?= BASE_PATH ?>/sessions/create" class="btn btn-primary btn-sm gap-1"><i class="ti ti-plus"></i> New session</a>
        <a href="<?= BASE_PATH ?>/sessions" class="btn btn-sm btn-ghost gap-1"><i class="ti ti-calendar-event"></i> Sessions</a>
        <a href="<?= BASE_PATH ?>/tournaments" class="btn btn-sm btn-ghost gap-1"><i class="ti ti-trophy"></i> Tournaments</a>
        <a href="<?= BASE_PATH ?>/games" class="btn btn-sm btn-ghost gap-1"><i class="ti ti-cards"></i> My games</a>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-4">

    <?php /* ============ HLAVNÍ SLOUPEC ============ */ ?>
    <div class="lg:col-span-2 space-y-4">

        <?php /* ----- NEXT SESSION (hero) ----- */ ?>
        <?php if ($next === null): ?>
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body items-center text-center py-12">
                    <i class="ti ti-calendar-off text-5xl opacity-40 mb-2"></i>
                    <p class="opacity-70">No upcoming sessions.</p>
                    <a href="<?= BASE_PATH ?>/sessions" class="btn btn-primary btn-sm mt-2 gap-1"><i class="ti ti-search"></i> Find one to join</a>
                </div>
            </div>
        <?php else: ?>
            <?php
            $isHost = (int) $next['creator_id'] === Auth::id();
            ?>
            <div class="card bg-base-100 shadow-md overflow-hidden">
                <div class="px-5 pt-4 pb-2 flex items-center justify-between bg-base-200/50">
                    <span class="text-xs font-medium uppercase tracking-wide opacity-60 flex items-center gap-1">
                        <i class="ti ti-player-play"></i> Next up
                    </span>
                    <?php if ($isHost): ?>
                        <span class="badge badge-info badge-sm gap-1"><i class="ti ti-crown"></i> Host</span>
                    <?php else: ?>
                        <span class="badge badge-success badge-sm gap-1"><i class="ti ti-check"></i> Joined</span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-5 gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg flex items-center justify-center shrink-0 bg-primary text-primary-content">
                            <i class="ti ti-dice text-2xl"></i>
                        </div>
                        <div class="min-w-0">
                            <h2 class="text-xl font-bold truncate"><?= htmlspecialchars($next['title']) ?></h2>
                            <p class="text-sm opacity-70 truncate"><?= htmlspecialchars($next['game_name'] ?? 'No game selected') ?></p>
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-2 text-sm">
                        <span class="flex items-center gap-2"><i class="ti ti-map-pin opacity-60"></i>
                            <?= htmlspecialchars($next['location_name'] . ', ' . $next['location_city']) ?></span>
                        <span class="flex items-center gap-2"><i class="ti ti-clock opacity-60"></i>
                            <?= date('j.n.Y H:i', strtotime($next['scheduled_at'])) ?></span>
                        <span class="flex items-center gap-2"><i class="ti ti-users opacity-60"></i>
                            <?php if ($next['max_players'] === null): ?>
                                <?= (int) $next['player_count'] ?> joined
                            <?php else: ?>
                                <?= (int) $next['player_count'] ?> / <?= (int) $next['max_players'] ?> players
                            <?php endif; ?>
                        </span>
                    </div>

                    <div class="card-actions">
                        <a href="<?= BASE_PATH ?>/sessions/<?= $next['id'] ?>" class="btn btn-primary btn-sm gap-1">
                            <i class="ti ti-arrow-right"></i> View session
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php /* ----- OTHER UPCOMING ----- */ ?>
        <?php if (!empty($others)): ?>
            <div>
                <h2 class="text-lg font-bold mb-2 flex items-center gap-2"><i class="ti ti-calendar-event"></i> Later</h2>
                <div class="grid sm:grid-cols-2 gap-3">
                    <?php foreach ($others as $s): ?>
                        <?php
                        $cardBadge = ((int) $s['creator_id'] === Auth::id())
                            ? '<span class="badge badge-info badge-sm gap-1"><i class="ti ti-crown"></i> Host</span>'
                            : '<span class="badge badge-success badge-sm gap-1"><i class="ti ti-check"></i> Joined</span>';
                        require __DIR__ . '/partials/session-card.php';
                        ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php /* ----- TOURNAMENTS ----- */ ?>
        <div>
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-lg font-bold flex items-center gap-2"><i class="ti ti-trophy"></i> Tournaments</h2>
                <a href="<?= BASE_PATH ?>/tournaments" class="btn btn-ghost btn-xs">All</a>
            </div>
            <?php if (empty($tournaments)): ?>
                <div class="card bg-base-100 shadow-sm border border-base-200 border-dashed">
                    <div class="card-body items-center text-center py-8 opacity-60">
                        <i class="ti ti-trophy-off text-3xl mb-1"></i>
                        <p class="text-sm">You’re not in any tournaments yet.</p>
                        <a href="<?= BASE_PATH ?>/tournaments" class="btn btn-primary btn-xs mt-2">Browse tournaments</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="grid sm:grid-cols-2 gap-3">
                    <?php foreach ($tournaments as $t): ?>
                        <a href="<?= BASE_PATH ?>/tournaments/<?= $t['id'] ?>"
                           class="card bg-base-100 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition border border-base-200">
                            <div class="card-body p-4 flex-row items-center gap-3">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 bg-primary text-primary-content">
                                    <i class="ti ti-trophy text-xl"></i>
                                </div>
                                <div class="min-w-0 mr-auto">
                                    <div class="font-bold truncate"><?= htmlspecialchars($t['name']) ?></div>
                                    <div class="text-xs opacity-60 truncate"><?= htmlspecialchars($t['game_name']) ?></div>
                                </div>
                                <span class="badge badge-ghost gap-1"><i class="ti ti-calendar-event"></i> <?= (int) $t['session_count'] ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php /* ============ POSTRANNÍ SLOUPEC ============ */ ?>
    <div class="space-y-4">

        <?php /* ----- NOTIFICATIONS ----- */ ?>
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-4">
                <h2 class="font-bold flex items-center gap-2 mb-1">
                    <i class="ti ti-bell text-primary"></i> Notifications
                    <?php if ($notificationCount > 0): ?>
                        <span class="badge badge-primary badge-sm"><?= $notificationCount ?></span>
                    <?php endif; ?>
                </h2>

                <?php if ($notificationCount === 0): ?>
                    <p class="text-sm opacity-60 py-2">You’re all caught up.</p>
                <?php else: ?>
                    <?php /* join requesty (host) */ ?>
                    <?php foreach ($pending as $p): ?>
                        <div class="flex items-center gap-2 py-2 border-b border-base-200 last:border-0">
                            <?= Avatar::html($p['username'], 'w-7 h-7') ?>
                            <span class="mr-auto text-sm min-w-0">
                                <strong><?= htmlspecialchars($p['username']) ?></strong>
                                <span class="opacity-60">→ <?= htmlspecialchars($p['title']) ?></span>
                            </span>
                            <form method="POST" action="<?= BASE_PATH ?>/sessions/<?= $p['session_id'] ?>/approve">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="user_id" value="<?= (int) $p['user_id'] ?>">
                                <input type="hidden" name="redirect" value="/">
                                <button class="btn btn-xs btn-circle btn-success" title="Approve"><i class="ti ti-check"></i></button>
                            </form>
                            <form method="POST" action="<?= BASE_PATH ?>/sessions/<?= $p['session_id'] ?>/reject">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="user_id" value="<?= (int) $p['user_id'] ?>">
                                <input type="hidden" name="redirect" value="/">
                                <button class="btn btn-xs btn-circle btn-error btn-outline" title="Reject"><i class="ti ti-x"></i></button>
                            </form>
                        </div>
                    <?php endforeach; ?>

                    <?php /* zrušená sezení */ ?>
                    <?php foreach ($cancelled as $c): ?>
                        <div class="flex items-center gap-2 py-2 border-b border-base-200 last:border-0">
                            <i class="ti ti-ban text-error shrink-0"></i>
                            <a href="<?= BASE_PATH ?>/sessions/<?= $c['id'] ?>" class="text-sm mr-auto min-w-0 hover:underline">
                                <strong><?= htmlspecialchars($c['title']) ?></strong>
                                <span class="opacity-60">was cancelled</span>
                            </a>
                            <form method="POST" action="<?= BASE_PATH ?>/sessions/<?= $c['id'] ?>/leave">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="redirect" value="/">
                                <button class="btn btn-ghost btn-xs btn-circle" title="Dismiss"><i class="ti ti-x"></i></button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <?php /* ----- CALENDAR ----- */ ?>
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-4">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="font-bold flex items-center gap-2">
                        <i class="ti ti-calendar text-primary"></i> <span id="cal-month"></span>
                    </h2>
                    <div class="flex gap-1">
                        <button id="cal-prev" class="btn btn-ghost btn-xs btn-circle" aria-label="Previous month"><i class="ti ti-chevron-left"></i></button>
                        <button id="cal-today" class="btn btn-ghost btn-xs" aria-label="Today">Today</button>
                        <button id="cal-next" class="btn btn-ghost btn-xs btn-circle" aria-label="Next month"><i class="ti ti-chevron-right"></i></button>
                    </div>
                </div>
                <div id="cal-grid" class="grid grid-cols-7 gap-1 text-center text-xs"></div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const sessions = <?= json_encode($calMap) ?>;
    const base  = '<?= BASE_PATH ?>';
    const grid  = document.getElementById('cal-grid');
    const label = document.getElementById('cal-month');
    const view  = new Date();
    view.setDate(1);

    const pad = n => (n < 10 ? '0' : '') + n;

    function render() {
        const year = view.getFullYear(), month = view.getMonth();
        label.textContent = view.toLocaleString('en-US', { month: 'long', year: 'numeric' });
        grid.innerHTML = '';

        ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'].forEach(d => {
            const h = document.createElement('div');
            h.className = 'opacity-50 font-medium pb-1';
            h.textContent = d;
            grid.appendChild(h);
        });

        const startDow   = (new Date(year, month, 1).getDay() + 6) % 7; // Po = 0
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const today = new Date(); const todayStr = today.getFullYear() + '-' + pad(today.getMonth() + 1) + '-' + pad(today.getDate());

        for (let i = 0; i < startDow; i++) grid.appendChild(document.createElement('div'));

        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = year + '-' + pad(month + 1) + '-' + pad(day);
            const items = sessions[dateStr] || [];
            const isToday = dateStr === todayStr;

            if (!items.length) {
                const cell = document.createElement('div');
                cell.className = 'aspect-square flex items-center justify-center rounded' + (isToday ? ' ring-1 ring-primary' : '');
                cell.textContent = day;
                grid.appendChild(cell);
                continue;
            }

            const wrap = document.createElement('div');
            wrap.className = 'relative';

            const cell = document.createElement('div');
            cell.className = 'aspect-square flex flex-col items-center justify-center rounded font-bold hover:bg-base-200 cursor-pointer'
                           + (isToday ? ' ring-1 ring-primary' : '');
            const num = document.createElement('span'); num.textContent = day; cell.appendChild(num);
            const dot = document.createElement('span');
            dot.className = 'w-1.5 h-1.5 rounded-full mt-0.5 bg-primary';
            cell.appendChild(dot);

            const pop = document.createElement('div');
            pop.className = 'cal-pop absolute z-50 left-1/2 -translate-x-1/2 top-full mt-1 w-52 bg-base-100 shadow-lg rounded-box border border-base-200 p-1 hidden text-left';
            items.forEach(s => {
                const a = document.createElement('a');
                a.href = base + '/sessions/' + s.id;
                a.className = 'flex items-center gap-2 p-2 rounded hover:bg-base-200';
                const d2 = document.createElement('span');
                d2.className = 'w-2.5 h-2.5 rounded-full shrink-0 bg-primary';
                const info = document.createElement('span'); info.className = 'min-w-0';
                const t = document.createElement('span'); t.className = 'block text-xs font-medium truncate'; t.textContent = s.title;
                const tm = document.createElement('span'); tm.className = 'block text-[11px] opacity-60'; tm.textContent = s.time;
                info.append(t, tm); a.append(d2, info); pop.appendChild(a);
            });

            wrap.append(cell, pop);

            let timer;
            cell.addEventListener('mouseenter', () => { timer = setTimeout(() => { closeAllPops(); pop.classList.remove('hidden'); }, 1000); });
            wrap.addEventListener('mouseleave', () => { clearTimeout(timer); pop.classList.add('hidden'); });
            cell.addEventListener('click', (e) => {
                e.stopPropagation();
                clearTimeout(timer);
                const hidden = pop.classList.contains('hidden');
                closeAllPops();
                if (hidden) pop.classList.remove('hidden');
            });

            grid.appendChild(wrap);
        }
    }

    function closeAllPops() {
        document.querySelectorAll('.cal-pop').forEach(p => p.classList.add('hidden'));
    }
    document.addEventListener('click', closeAllPops);

    document.getElementById('cal-prev').addEventListener('click', () => { view.setMonth(view.getMonth() - 1); render(); });
    document.getElementById('cal-next').addEventListener('click', () => { view.setMonth(view.getMonth() + 1); render(); });
    document.getElementById('cal-today').addEventListener('click', () => { const n = new Date(); view.setFullYear(n.getFullYear(), n.getMonth(), 1); render(); });

    render();
})();
</script>
