<?php
/** @var array $user */
/** @var array $mySessions */
/** @var array $pending */
/** @var array $stats */
?>

<?php /* ===== Pozdrav + statistiky ===== */ ?>
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <h1 class="text-2xl font-bold">Hi, <?= htmlspecialchars($user['username']) ?></h1>
    <div class="stats shadow bg-base-100">
        <div class="stat py-2 px-5">
            <div class="stat-figure text-primary"><i class="ti ti-crown text-2xl"></i></div>
            <div class="stat-title text-xs">Hosted</div>
            <div class="stat-value text-2xl"><?= (int) $stats['hosted'] ?></div>
        </div>
        <div class="stat py-2 px-5">
            <div class="stat-figure text-success"><i class="ti ti-dice text-2xl"></i></div>
            <div class="stat-title text-xs">Joined</div>
            <div class="stat-value text-2xl"><?= (int) $stats['joined'] ?></div>
        </div>
    </div>
</div>

<?php /* ===== Rychlé akce ===== */ ?>
<div class="flex flex-wrap gap-2 mb-8">
    <a href="<?= BASE_PATH ?>/sessions/create" class="btn btn-primary btn-sm gap-1"><i class="ti ti-plus"></i> New session</a>
    <a href="<?= BASE_PATH ?>/sessions" class="btn btn-sm gap-1"><i class="ti ti-search"></i> Browse sessions</a>
    <a href="<?= BASE_PATH ?>/games" class="btn btn-sm gap-1"><i class="ti ti-cards"></i> Favorite games</a>
    <a href="<?= BASE_PATH ?>/account" class="btn btn-sm gap-1"><i class="ti ti-user-cog"></i> Account</a>
</div>

<?php /* ===== Žádosti ke schválení ===== */ ?>
<?php if (!empty($pending)): ?>
    <div class="mb-8">
        <h2 class="text-lg font-bold mb-2 flex items-center gap-2"><i class="ti ti-user-plus"></i> Requests to approve</h2>
        <ul class="space-y-2">
            <?php foreach ($pending as $p): ?>
                <li class="flex items-center gap-2 p-3 bg-base-100 rounded-box shadow-sm">
                    <?= Avatar::html($p['username'], 'w-8 h-8') ?>
                    <span class="mr-auto text-sm min-w-0">
                        <strong><?= htmlspecialchars($p['username']) ?></strong>
                        → <span class="opacity-70"><?= htmlspecialchars($p['title']) ?></span>
                    </span>
                    <form method="POST" action="<?= BASE_PATH ?>/sessions/<?= $p['session_id'] ?>/approve">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="user_id" value="<?= (int) $p['user_id'] ?>">
                        <button class="btn btn-sm btn-circle btn-success" title="Approve"><i class="ti ti-check"></i></button>
                    </form>
                    <form method="POST" action="<?= BASE_PATH ?>/sessions/<?= $p['session_id'] ?>/reject">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="user_id" value="<?= (int) $p['user_id'] ?>">
                        <button class="btn btn-sm btn-circle btn-error btn-outline" title="Reject"><i class="ti ti-x"></i></button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php /* ===== Moje nadcházející sezení ===== */ ?>
<div>
    <h2 class="text-lg font-bold mb-2 flex items-center gap-2"><i class="ti ti-calendar-event"></i> My upcoming sessions</h2>
    <?php if (empty($mySessions)): ?>
        <div class="flex flex-col items-center text-center py-12 opacity-70">
            <i class="ti ti-calendar-off text-5xl mb-3"></i>
            <p>You have no upcoming sessions.</p>
            <a href="<?= BASE_PATH ?>/sessions" class="btn btn-primary btn-sm mt-4 gap-1">
                <i class="ti ti-search"></i> Find one to join
            </a>
        </div>
    <?php else: ?>
        <div class="grid gap-4 md:grid-cols-2">
            <?php foreach ($mySessions as $s): ?>
                <?php
                $cardBadge = ((int) $s['creator_id'] === Auth::id())
                        ? '<span class="badge badge-info badge-sm gap-1"><i class="ti ti-crown"></i> Host</span>'
                        : '<span class="badge badge-success badge-sm gap-1"><i class="ti ti-check"></i> Joined</span>';
                require __DIR__ . '/partials/session-card.php';
                ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
