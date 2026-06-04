<?php
/** @var array $sessions */
/** @var array $locations */
/** @var array $filters */
?>

<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold">Upcoming sessions</h1>
    <a href="<?= BASE_PATH ?>/sessions/create" class="btn btn-primary btn-sm">+ New session</a>
</div>

<form method="GET" action="<?= BASE_PATH ?>/sessions"
      class="flex flex-wrap items-end gap-2 mb-4">
    <label for="location_id" class="sr-only">Location</label>
    <select id="location_id" name="location_id" class="select select-bordered select-sm">
        <option value="">All locations</option>
        <?php foreach ($locations as $loc): ?>
            <option value="<?= $loc['id'] ?>"
                    <?= (($filters['location_id'] ?? '') == $loc['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($loc['name'] . ' (' . $loc['city'] . ')') ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label class="label cursor-pointer gap-2">
        <input type="checkbox" name="free_only" value="1" class="checkbox checkbox-sm"
                <?= !empty($filters['free_only']) ? 'checked' : '' ?>>
        <span class="label-text">Free seats only</span>
    </label>

    <button class="btn btn-sm btn-primary">Filter</button>
    <a href="<?= BASE_PATH ?>/sessions" class="btn btn-sm btn-ghost">Reset</a>
</form>

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
