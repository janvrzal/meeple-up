<?php
/** @var array $sessions */
/** @var array $locations */
/** @var array $games */
/** @var array $filters */

// předvyplnění inputu názvem vybrané volby
$selLocation = '';
foreach ($locations as $l) {
    if (($filters['location_id'] ?? '') == $l['id']) { $selLocation = $l['name'] . ' (' . $l['city'] . ')'; break; }
}
$selGame = '';
foreach ($games as $g) {
    if (($filters['game_id'] ?? '') == $g['id']) { $selGame = $g['name']; break; }
}
?>

<div class="flex items-center justify-between mb-5">
    <h1 class="text-2xl font-bold flex items-center gap-2">
        <i class="ti ti-calendar-event text-primary"></i> Upcoming sessions
    </h1>
    <a href="<?= BASE_PATH ?>/sessions/create" class="btn btn-primary btn-sm gap-1">
        <i class="ti ti-plus"></i> New session
    </a>
</div>

<?php /* ===== Filtr ===== */ ?>
<form method="GET" action="<?= BASE_PATH ?>/sessions"
      class="flex flex-wrap items-center gap-2 mb-6 p-3 bg-base-100 rounded-box shadow-sm">

    <div class="combobox relative">
        <label for="filter-location" class="sr-only">Location</label>
        <div class="relative">
            <i class="ti ti-map-pin absolute left-3 top-1/2 -translate-y-1/2 opacity-50"></i>
            <input type="text" id="filter-location" class="cb-input input input-bordered input-sm pl-8 w-56"
                   placeholder="All locations" autocomplete="off" value="<?= htmlspecialchars($selLocation) ?>">
        </div>
        <input type="hidden" name="location_id" value="<?= htmlspecialchars($filters['location_id'] ?? '') ?>">
        <ul class="cb-list menu bg-base-100 shadow rounded-box absolute z-10 mt-1 max-h-60 overflow-auto hidden w-64">
            <li data-id=""><a>All locations</a></li>
            <?php foreach ($locations as $l): ?>
                <li data-id="<?= $l['id'] ?>"><a><?= htmlspecialchars($l['name'] . ' (' . $l['city'] . ')') ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="combobox relative">
        <label for="filter-game" class="sr-only">Game</label>
        <div class="relative">
            <i class="ti ti-dice absolute left-3 top-1/2 -translate-y-1/2 opacity-50"></i>
            <input type="text" id="filter-game" class="cb-input input input-bordered input-sm pl-8 w-56"
                   placeholder="All games" autocomplete="off" value="<?= htmlspecialchars($selGame) ?>">
        </div>
        <input type="hidden" name="game_id" value="<?= htmlspecialchars($filters['game_id'] ?? '') ?>">
        <ul class="cb-list menu bg-base-100 shadow rounded-box absolute z-10 mt-1 max-h-60 overflow-auto hidden w-64">
            <li data-id=""><a>All games</a></li>
            <?php foreach ($games as $g): ?>
                <li data-id="<?= $g['id'] ?>"><a><?= htmlspecialchars($g['name']) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <label class="label cursor-pointer gap-2">
        <input type="checkbox" name="free_only" value="1" class="checkbox checkbox-sm"
            <?= !empty($filters['free_only']) ? 'checked' : '' ?>>
        <span class="label-text">Free seats only</span>
    </label>

    <button class="btn btn-sm btn-primary gap-1"><i class="ti ti-filter"></i> Filter</button>
    <a href="<?= BASE_PATH ?>/sessions" class="btn btn-sm btn-ghost">Reset</a>
</form>

<?php /* ===== Výpis ===== */ ?>
<?php if (empty($sessions)): ?>
    <div class="flex flex-col items-center text-center py-16 opacity-70">
        <i class="ti ti-mood-empty text-5xl mb-3"></i>
        <p class="text-lg">No upcoming sessions match your filters.</p>
        <a href="<?= BASE_PATH ?>/sessions/create" class="btn btn-primary btn-sm mt-4 gap-1">
            <i class="ti ti-plus"></i> Create one
        </a>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <?php foreach ($sessions as $s): ?>
            <?php
            $full = $s['max_players'] !== null && (int) $s['player_count'] >= (int) $s['max_players'];
            if ($s['max_players'] === null) {
                $cardBadge = '<span class="badge badge-ghost badge-sm gap-1"><i class="ti ti-users"></i> ' . (int) $s['player_count'] . '</span>';
            } else {
                $cardBadge = '<span class="badge badge-sm gap-1 ' . ($full ? 'badge-error' : 'badge-success') . '">'
                        . '<i class="ti ti-users"></i> ' . (int) $s['player_count'] . '/' . (int) $s['max_players'] . '</span>';
            }
            require __DIR__ . '/../partials/session-card.php';
            ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
    document.querySelectorAll('.combobox').forEach(box => {
        const input  = box.querySelector('.cb-input');
        const hidden = box.querySelector('input[type=hidden]');
        const list   = box.querySelector('.cb-list');
        const items  = Array.from(list.querySelectorAll('li'));

        const show = () => list.classList.remove('hidden');
        const hide = () => list.classList.add('hidden');

        input.addEventListener('focus', show);
        input.addEventListener('input', () => {
            const q = input.value.trim().toLowerCase();
            hidden.value = '';
            items.forEach(li => {
                const match = q === '' || li.textContent.trim().toLowerCase().includes(q);
                li.style.display = match ? '' : 'none';
            });
            show();
        });

        items.forEach(li => li.addEventListener('click', () => {
            hidden.value = li.dataset.id;
            input.value  = li.dataset.id === '' ? '' : li.textContent.trim();
            hide();
        }));

        document.addEventListener('click', e => { if (!box.contains(e.target)) hide(); });
    });
</script>
