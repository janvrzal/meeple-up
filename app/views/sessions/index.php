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

<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold">Upcoming sessions</h1>
    <a href="<?= BASE_PATH ?>/sessions/create" class="btn btn-primary btn-sm">+ New session</a>
</div>

<form method="GET" action="<?= BASE_PATH ?>/sessions" class="flex flex-wrap items-end gap-2 mb-4">

    <?php /* Location combobox */ ?>
    <div class="combobox relative">
        <label for="filter-location" class="sr-only">Location</label>
        <input type="text" id="filter-location" class="cb-input input input-bordered input-sm" placeholder="All locations"
               autocomplete="off" value="<?= htmlspecialchars($selLocation) ?>">
        <input type="hidden" name="location_id" value="<?= htmlspecialchars($filters['location_id'] ?? '') ?>">
        <ul class="cb-list menu bg-base-200 rounded-box absolute z-10 mt-1 max-h-60 overflow-auto hidden w-64">
            <li data-id=""><a>All locations</a></li>
            <?php foreach ($locations as $l): ?>
                <li data-id="<?= $l['id'] ?>"><a><?= htmlspecialchars($l['name'] . ' (' . $l['city'] . ')') ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <?php /* Game combobox */ ?>
    <div class="combobox relative">
        <label for="filter-game" class="sr-only">Game</label>
        <input type="text" id="filter-game" class="cb-input input input-bordered input-sm" placeholder="All games"
               autocomplete="off" value="<?= htmlspecialchars($selGame) ?>">
        <input type="hidden" name="game_id" value="<?= htmlspecialchars($filters['game_id'] ?? '') ?>">
        <ul class="cb-list menu bg-base-200 rounded-box absolute z-10 mt-1 max-h-60 overflow-auto hidden w-64">
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

    <button class="btn btn-sm btn-primary">Filter</button>
    <a href="<?= BASE_PATH ?>/sessions" class="btn btn-sm btn-ghost">Reset</a>
</form>

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
            hidden.value = '';                       // psaní ruší výběr, dokud neklikneš
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

<?php if (empty($sessions)): ?>
    <div class="text-center opacity-70 py-10">No upcoming sessions yet.</div>
<?php else: ?>
    <div class="grid gap-3 md:grid-cols-2">
        <?php foreach ($sessions as $s): ?>
            <a href="<?= BASE_PATH ?>/sessions/<?= $s['id'] ?>" class="card bg-base-100 shadow hover:shadow-md transition">
                <div class="card-body p-4">
                    <h2 class="card-title text-base"><?= htmlspecialchars($s['title']) ?></h2>
                    <p class="text-sm opacity-80">
                        <?= htmlspecialchars($s['game_name'] ?? 'No game selected') ?>
                    </p>
                    <p class="text-sm">
                        📍 <?= htmlspecialchars($s['location_name'] . ', ' . $s['location_city']) ?><br>
                        🕒 <?= date('j.n.Y H:i', strtotime($s['scheduled_at'])) ?>
                    </p>
                    <div class="text-sm">
                        <?php if ($s['max_players'] === null): ?>
                            👥 <?= (int) $s['player_count'] ?> joined (no limit)
                        <?php else: ?>
                            👥 <?= (int) $s['player_count'] ?> / <?= (int) $s['max_players'] ?>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
