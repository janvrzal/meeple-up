<?php
/** @var array $user */
/** @var array $mySessions */
/** @var array $pending */
/** @var array $stats */
?>

<?php /* ===== Pozdrav + statistiky ===== */ ?>
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <h1 class="text-2xl font-bold">Hi, <?= htmlspecialchars($user['username']) ?> 👋</h1>
    <div class="flex gap-4 text-sm">
        <span class="badge badge-lg">Hosted: <?= (int) $stats['hosted'] ?></span>
        <span class="badge badge-lg">Joined: <?= (int) $stats['joined'] ?></span>
    </div>
</div>

<?php /* ===== Rychlé akce ===== */ ?>
<div class="flex flex-wrap gap-2 mb-8">
    <a href="<?= BASE_PATH ?>/sessions/create" class="btn btn-primary btn-sm">+ New session</a>
    <a href="<?= BASE_PATH ?>/sessions" class="btn btn-sm">Browse sessions</a>
    <span class="btn btn-sm btn-disabled">Manage games <span class="badge badge-xs ml-1">soon</span></span>
    <span class="btn btn-sm btn-disabled">Account <span class="badge badge-xs ml-1">soon</span></span>
</div>

<?php /* ===== Žádosti ke schválení ===== */ ?>
<?php if (!empty($pending)): ?>
    <div class="mb-8">
        <h2 class="text-lg font-bold mb-2">Requests to approve</h2>
        <ul class="space-y-2">
            <?php foreach ($pending as $p): ?>
                <li class="flex items-center gap-2 p-3 bg-base-100 rounded shadow-sm">
                    <span class="mr-auto">
                        <strong><?= htmlspecialchars($p['username']) ?></strong>
                        wants to join <em><?= htmlspecialchars($p['title']) ?></em>
                    </span>
                    <form method="POST" action="<?= BASE_PATH ?>/sessions/<?= $p['session_id'] ?>/approve">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="user_id" value="<?= (int) $p['user_id'] ?>">
                        <button class="btn btn-xs btn-success">Approve</button>
                    </form>
                    <form method="POST" action="<?= BASE_PATH ?>/sessions/<?= $p['session_id'] ?>/reject">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="user_id" value="<?= (int) $p['user_id'] ?>">
                        <button class="btn btn-xs btn-error">Reject</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php /* ===== Moje nadcházející sezení ===== */ ?>
<div>
    <h2 class="text-lg font-bold mb-2">My upcoming sessions</h2>
    <?php if (empty($mySessions)): ?>
        <p class="opacity-70">You have no upcoming sessions.
            <a href="<?= BASE_PATH ?>/sessions" class="link">Find one to join</a>.</p>
    <?php else: ?>
        <div class="grid gap-3 md:grid-cols-2">
            <?php foreach ($mySessions as $s): ?>
                <a href="<?= BASE_PATH ?>/sessions/<?= $s['id'] ?>"
                   class="card bg-base-100 shadow hover:shadow-md transition">
                    <div class="card-body p-4">
                        <div class="flex items-center justify-between">
                            <h3 class="card-title text-base"><?= htmlspecialchars($s['title']) ?></h3>
                            <?php if ((int) $s['creator_id'] === Auth::id()): ?>
                                <span class="badge badge-info badge-sm">Host</span>
                            <?php else: ?>
                                <span class="badge badge-success badge-sm">Joined</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-sm opacity-80"><?= htmlspecialchars($s['game_name'] ?? 'No game') ?></p>
                        <p class="text-sm">
                            📍 <?= htmlspecialchars($s['location_name'] . ', ' . $s['location_city']) ?><br>
                            🕒 <?= date('j.n.Y H:i', strtotime($s['scheduled_at'])) ?>
                        </p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>