<?php
/** @var array $session */
/** @var array $participants */
/** @var array|null $mine */
$s = $session;
?>
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

    <div class="mt-6">
        <h2 class="text-lg font-bold mb-2">Players</h2>

        <?php
        $approved = array_filter($participants, fn($p) => $p['status'] === 'approved');
        ?>
        <?php if (empty($approved)): ?>
            <p class="opacity-70">No one has joined yet.</p>
        <?php else: ?>
            <ul class="flex flex-wrap gap-2">
                <?php foreach ($approved as $p): ?>
                    <li class="badge badge-outline"><?= htmlspecialchars($p['username']) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <div class="mt-4">
            <?php if (Auth::check() && (int) $s['creator_id'] === Auth::id()): ?>
                <span class="badge badge-info">You're the host</span>

            <?php elseif (!Auth::check()): ?>
                <a href="<?= BASE_PATH ?>/login" class="btn btn-sm">Log in to join</a>

            <?php elseif ($mine === null): ?>
                <?php
                $isFull = $s['max_players'] !== null
                        && (int) $s['player_count'] >= (int) $s['max_players'];
                ?>
                <?php if ($isFull): ?>
                    <span class="badge badge-error">Session full</span>
                <?php else: ?>
                    <form method="POST" action="<?= BASE_PATH ?>/sessions/<?= $s['id'] ?>/join">
                        <?= Csrf::field() ?>
                        <button class="btn btn-sm btn-primary">Join</button>
                    </form>
                <?php endif; ?>

            <?php elseif ($mine['status'] === 'pending'): ?>
                <span class="badge badge-warning">Pending approval</span>
                <form method="POST" action="<?= BASE_PATH ?>/sessions/<?= $s['id'] ?>/leave" class="mt-2">
                    <?= Csrf::field() ?>
                    <button class="btn btn-sm">Cancel request</button>
                </form>

            <?php else: ?>
                <span class="badge badge-success">You're in</span>
                <form method="POST" action="<?= BASE_PATH ?>/sessions/<?= $s['id'] ?>/leave" class="mt-2">
                    <?= Csrf::field() ?>
                    <button class="btn btn-sm">Leave</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php if (Auth::check() && (int) $s['creator_id'] === Auth::id()):
        $pending = array_filter($participants, fn($p) => $p['status'] === 'pending');
        ?>
        <?php if (!empty($pending)): ?>
        <div class="mt-6">
            <h2 class="text-lg font-bold mb-2">Pending requests</h2>
            <ul class="space-y-2">
                <?php foreach ($pending as $p): ?>
                    <li class="flex items-center gap-2">
                        <span class="mr-auto"><?= htmlspecialchars($p['username']) ?></span>
                        <form method="POST" action="<?= BASE_PATH ?>/sessions/<?= $s['id'] ?>/approve">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="user_id" value="<?= (int) $p['user_id'] ?>">
                            <button class="btn btn-xs btn-success">Approve</button>
                        </form>
                        <form method="POST" action="<?= BASE_PATH ?>/sessions/<?= $s['id'] ?>/reject">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="user_id" value="<?= (int) $p['user_id'] ?>">
                            <button class="btn btn-xs btn-error">Reject</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
<?php endif; ?>

    <a href="<?= BASE_PATH ?>/sessions" class="link mt-4 inline-block">← Back to list</a>
</div>