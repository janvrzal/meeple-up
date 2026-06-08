<?php
/** @var array $tournament */
/** @var array $sessions */
/** @var bool  $isMember */
/** @var int   $memberCount */
$t = $tournament;
$isMember    = $isMember ?? false;
$memberCount = $memberCount ?? 0;
$isCreator = Auth::check() && (int) $t['creator_id'] === Auth::id();
$isAdmin   = Auth::check() && (Auth::user()['role'] ?? 'user') === 'admin';
?>

<?php $fallback = BASE_PATH . '/tournaments'; $label = 'Back'; require __DIR__ . '/../partials/back-link.php'; ?>

<div class="card bg-base-100 shadow-lg mb-6">
    <div class="card-body p-6">
        <div class="flex items-start gap-4">
            <div class="w-14 h-14 rounded-lg flex items-center justify-center shrink-0 bg-primary text-primary-content">
                <i class="ti ti-trophy text-3xl"></i>
            </div>
            <div class="min-w-0 mr-auto">
                <h1 class="text-2xl font-bold break-words"><?= htmlspecialchars($t['name']) ?></h1>
                <p class="text-sm opacity-70 flex items-center gap-1 mt-1 min-w-0">
                    <i class="ti ti-dice shrink-0"></i> <span class="truncate"><?= htmlspecialchars($t['game_name']) ?></span>
                </p>
                <p class="text-xs opacity-60 flex items-center gap-1 flex-wrap">
                    <i class="ti ti-user"></i> by <?= htmlspecialchars($t['creator_name']) ?>
                    · <?= (int) $t['session_count'] ?> session<?= (int) $t['session_count'] === 1 ? '' : 's' ?>
                    · <?= (int) $memberCount ?> member<?= (int) $memberCount === 1 ? '' : 's' ?>
                </p>
            </div>

            <div class="flex flex-col items-end gap-2 shrink-0">
                <?php if ($isCreator): ?>
                    <span class="badge badge-info gap-1"><i class="ti ti-crown"></i> Host</span>
                <?php endif; ?>

                <?php if (!Auth::check()): ?>
                    <a href="<?= BASE_PATH ?>/login" class="btn btn-sm btn-primary">Log in to join</a>
                <?php elseif ($isMember): ?>
                    <span class="badge badge-success gap-1"><i class="ti ti-check"></i> Joined</span>
                    <form method="POST" action="<?= BASE_PATH ?>/tournaments/<?= $t['id'] ?>/leave">
                        <?= Csrf::field() ?>
                        <button class="btn btn-xs btn-ghost gap-1"><i class="ti ti-logout"></i> Leave</button>
                    </form>
                <?php else: ?>
                    <form method="POST" action="<?= BASE_PATH ?>/tournaments/<?= $t['id'] ?>/join">
                        <?= Csrf::field() ?>
                        <button class="btn btn-sm btn-primary gap-1"><i class="ti ti-plus"></i> Join tournament</button>
                    </form>
                <?php endif; ?>

                <?php if ($isCreator || $isAdmin): ?>
                    <form method="POST" action="<?= BASE_PATH ?>/tournaments/<?= $t['id'] ?>/delete"
                          onsubmit="return confirm('Delete this tournament? Its sessions will be detached, not deleted.');">
                        <?= Csrf::field() ?>
                        <button class="btn btn-sm btn-error btn-outline gap-1"><i class="ti ti-trash"></i> Delete</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($t['description'])): ?>
            <div class="mt-4 p-3 bg-base-200 rounded-box text-sm">
                <?= nl2br(htmlspecialchars($t['description'])) ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="flex items-center justify-between mb-3">
    <h2 class="text-lg font-bold flex items-center gap-2"><i class="ti ti-calendar-event"></i> Sessions</h2>
    <?php if ($isCreator): ?>
        <a href="<?= BASE_PATH ?>/sessions/create?tournament_id=<?= $t['id'] ?>"
           class="btn btn-primary btn-sm gap-1"><i class="ti ti-plus"></i> Add session</a>
    <?php endif; ?>
</div>

<?php if (empty($sessions)): ?>
    <div class="flex flex-col items-center text-center py-12 opacity-70">
        <i class="ti ti-calendar-off text-5xl mb-3"></i>
        <p>No sessions in this tournament yet.</p>
        <?php if ($isCreator): ?>
            <a href="<?= BASE_PATH ?>/sessions/create?tournament_id=<?= $t['id'] ?>"
               class="btn btn-primary btn-sm mt-4 gap-1"><i class="ti ti-plus"></i> Add the first session</a>
        <?php endif; ?>
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
