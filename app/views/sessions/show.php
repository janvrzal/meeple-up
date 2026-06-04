<?php /** @var array $session */ $s = $session; ?>
<div class="max-w-2xl mx-auto card bg-base-100 shadow p-6">
    <div class="flex items-start justify-between">
        <h1 class="text-2xl font-bold"><?= htmlspecialchars($s['title']) ?></h1>
        <?php if ($s['is_private']): ?>
            <span class="badge badge-warning">Private</span>
        <?php endif; ?>
    </div>

    <p class="mt-2 text-lg"><?= htmlspecialchars($s['game_name'] ?? 'No game selected') ?></p>

    <div class="mt-4 space-y-1">
        <p>📍 <?= htmlspecialchars($s['location_name'] . ', ' . $s['location_city']) ?></p>
        <p>🕒 <?= date('j.n.Y H:i', strtotime($s['scheduled_at'])) ?></p>
        <p>👤 Host: <?= htmlspecialchars($s['creator_name']) ?></p>
        <p>👥 Players:
            <?php if ($s['max_players'] === null): ?>
                <?= (int) $s['player_count'] ?> (no limit)
            <?php else: ?>
                <?= (int) $s['player_count'] ?> / <?= (int) $s['max_players'] ?>
            <?php endif; ?>
        </p>
    </div>

    <?php if (!empty($s['description'])): ?>
        <div class="mt-4 p-3 bg-base-200 rounded">
            <?= nl2br(htmlspecialchars($s['description'])) ?>
        </div>
    <?php endif; ?>

    <?php if (Auth::check() && ((int) $s['creator_id'] === Auth::id() || (Auth::user()['role'] ?? 'user') === 'admin')): ?>
        <form method="POST" action="<?= BASE_PATH ?>/sessions/<?= $s['id'] ?>/delete"
              onsubmit="return confirm('Delete this session?');" class="mt-4">
            <?= Csrf::field() ?>
            <button class="btn btn-sm btn-error">Delete</button>
        </form>
        <a href="<?= BASE_PATH ?>/sessions/<?= $s['id'] ?>/edit" class="btn btn-sm">Edit</a>
    <?php endif; ?>

    <a href="<?= BASE_PATH ?>/sessions" class="link mt-4 inline-block">← Back to list</a>
</div>