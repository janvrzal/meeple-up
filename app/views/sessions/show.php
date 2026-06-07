<?php
/**
 * Session detail view.
 *
 * @var array      $session       Session data (joined with location, game, creator, player_count)
 * @var array      $participants  All participations (with username + status)
 * @var array|null $mine          Current user's participation, or null
 * @var array      $comments      Comments/messages for this session (with username)
 * @var string     $googleUrl
 */
$s = $session;

$isCreator = Auth::check() && (int) $s['creator_id'] === Auth::id();
$isAdmin   = Auth::check() && (Auth::user()['role'] ?? 'user') === 'admin';
$approved  = array_filter($participants, fn($p) => $p['status'] === 'approved');
$pending   = array_filter($participants, fn($p) => $p['status'] === 'pending');

$canViewMessages = $isCreator || $isAdmin
    || ($mine !== null && $mine['status'] === 'approved');

$currentUsername = Auth::check() ? Auth::user()['username'] : null;
$isFull = $s['max_players'] !== null && (int) $s['player_count'] >= (int) $s['max_players'];
?>

<?php $fallback = BASE_PATH . '/sessions'; $label = 'Back'; require __DIR__ . '/../partials/back-link.php'; ?>

<div class="max-w-2xl mx-auto card bg-base-100 shadow-lg">

    <?php if ($s['status'] === 'cancelled'): ?>
        <div class="bg-error text-error-content px-6 py-2 text-sm font-medium flex items-center gap-2">
            <i class="ti ti-ban"></i> This session has been cancelled.
        </div>
    <?php endif; ?>

    <?php /* ===================== HEADER ===================== */ ?>
    <div class="px-6 pt-5">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <h1 class="text-3xl font-bold leading-tight flex items-center gap-2 flex-wrap">
                    <?= htmlspecialchars($s['title']) ?>
                    <?php if ($s['is_private']): ?>
                        <span class="badge badge-warning badge-sm" title="Private session"><i class="ti ti-lock"></i></span>
                    <?php endif; ?>
                </h1>
                <div class="flex items-center gap-2 mt-2 text-lg font-medium">
                    <span class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 bg-primary text-primary-content">
                        <i class="ti ti-dice text-base"></i>
                    </span>
                    <?= htmlspecialchars($s['game_name'] ?? 'No game selected') ?>

                    <?php if (Auth::check() && $s['game_id']):
                        $isFav = (new Favorite())->isFavorite(Auth::id(), (int) $s['game_id']); ?>
                        <form method="POST" action="<?= BASE_PATH ?>/games/<?= (int) $s['game_id'] ?>/favorite">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="redirect" value="/sessions/<?= (int) $s['id'] ?>">
                            <button class="btn btn-ghost btn-sm btn-circle <?= $isFav ? 'text-error' : '' ?>"
                                    title="<?= $isFav ? 'Remove from favorites' : 'Add to favorites' ?>">
                                <?php if ($isFav): ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M6.979 3.074a6 6 0 0 1 4.988 1.425l.037 .033l.034 -.03a6 6 0 0 1 4.733 -1.44l.246 .036a6 6 0 0 1 3.364 10.008l-.18 .185l-.048 .041l-7.45 7.379a1 1 0 0 1 -1.313 .082l-.094 -.082l-7.493 -7.422a6 6 0 0 1 3.176 -10.26z"/>
                                    </svg>
                                <?php else: ?>
                                    <i class="ti ti-heart text-lg"></i>
                                <?php endif; ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <?php if (!empty($s['tournament_name'])): ?>
                    <a href="<?= BASE_PATH ?>/tournaments/<?= (int) $s['tournament_id'] ?>"
                       class="badge badge-outline gap-1 mt-2 hover:badge-primary">
                        <i class="ti ti-trophy"></i> <?= htmlspecialchars($s['tournament_name']) ?>
                    </a>
                <?php endif; ?>
            </div>

            <?php /* ----- primary status / join-leave action (top-right) ----- */ ?>
            <div class="flex flex-col items-end gap-2 shrink-0">
                <?php if ($s['status'] === 'cancelled'): ?>
                    <span class="badge badge-error badge-lg gap-1"><i class="ti ti-ban"></i> Cancelled</span>

                <?php elseif ($isCreator): ?>
                    <span class="badge badge-info badge-lg gap-1"><i class="ti ti-crown"></i> You're the host</span>

                <?php elseif (!Auth::check()): ?>
                    <a href="<?= BASE_PATH ?>/login" class="btn btn-sm btn-primary">Log in to join</a>

                <?php elseif ($mine === null): ?>
                    <?php if ($isFull): ?>
                        <span class="badge badge-error badge-lg gap-1"><i class="ti ti-ban"></i> Full</span>
                    <?php else: ?>
                        <form method="POST" action="<?= BASE_PATH ?>/sessions/<?= $s['id'] ?>/join">
                            <?= Csrf::field() ?>
                            <button class="btn btn-sm btn-primary gap-1"><i class="ti ti-plus"></i> Join</button>
                        </form>
                    <?php endif; ?>

                <?php elseif ($mine['status'] === 'pending'): ?>
                    <span class="badge badge-warning badge-lg gap-1"><i class="ti ti-hourglass"></i> Pending</span>
                    <form method="POST" action="<?= BASE_PATH ?>/sessions/<?= $s['id'] ?>/leave">
                        <?= Csrf::field() ?>
                        <button class="btn btn-xs btn-ghost gap-1"><i class="ti ti-x"></i> Cancel request</button>
                    </form>

                <?php else: /* approved */ ?>
                    <form method="POST" action="<?= BASE_PATH ?>/sessions/<?= $s['id'] ?>/leave">
                        <?= Csrf::field() ?>
                        <button class="btn btn-sm btn-error btn-circle" title="Leave session"><i class="ti ti-logout"></i></button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-4 grid sm:grid-cols-2 gap-2 text-sm">
            <span class="flex items-center gap-2"><i class="ti ti-map-pin opacity-60"></i>
                <?= htmlspecialchars($s['location_name'] . ', ' . $s['location_city']) ?></span>
            <span class="flex items-center gap-2"><i class="ti ti-clock opacity-60"></i>
                <?= date('j.n.Y H:i', strtotime($s['scheduled_at'])) ?></span>
            <span class="flex items-center gap-2"><i class="ti ti-user opacity-60"></i>
                Host: <?= htmlspecialchars($s['creator_name']) ?></span>
            <span class="flex items-center gap-2"><i class="ti ti-users opacity-60"></i>
                <?php if ($s['max_players'] === null): ?>
                    <?= (int) $s['player_count'] ?> players · no limit
                <?php else: ?>
                    <?= (int) $s['player_count'] ?> / <?= (int) $s['max_players'] ?> players
                <?php endif; ?>
            </span>
        </div>
    </div>

    <?php /* ===================== BODY ===================== */ ?>
    <div class="p-6">

        <?php if (!empty($s['description'])): ?>
            <div class="p-3 bg-base-200 rounded-box text-sm mb-4">
                <?= nl2br(htmlspecialchars($s['description'])) ?>
            </div>
        <?php endif; ?>

        <div class="flex flex-wrap gap-2">
            <a href="<?= BASE_PATH ?>/sessions/<?= $s['id'] ?>/calendar" class="btn btn-sm btn-outline gap-1">
                <i class="ti ti-calendar-plus"></i> Add to calendar
            </a>
            <a href="<?= htmlspecialchars($googleUrl) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline gap-1">
                <i class="ti ti-brand-google"></i> Google Calendar
            </a>

            <?php if ($isCreator || $isAdmin): ?>
                <div class="ml-auto flex gap-2">
                    <a href="<?= BASE_PATH ?>/sessions/<?= $s['id'] ?>/edit" class="btn btn-sm gap-1">
                        <i class="ti ti-edit"></i> Edit
                    </a>
                    <?php if ($s['status'] === 'open'): ?>
                        <form method="POST" action="<?= BASE_PATH ?>/sessions/<?= $s['id'] ?>/cancel"
                              onsubmit="return confirm('Cancel this session? Players will see it as cancelled.');">
                            <?= Csrf::field() ?>
                            <button class="btn btn-sm btn-warning btn-outline gap-1"><i class="ti ti-ban"></i> Cancel</button>
                        </form>
                    <?php elseif ($s['status'] === 'cancelled'): ?>
                        <form method="POST" action="<?= BASE_PATH ?>/sessions/<?= $s['id'] ?>/reopen">
                            <?= Csrf::field() ?>
                            <button class="btn btn-sm btn-success btn-outline gap-1"><i class="ti ti-rotate"></i> Reopen</button>
                        </form>
                    <?php endif; ?>
                    <form method="POST" action="<?= BASE_PATH ?>/sessions/<?= $s['id'] ?>/delete"
                          onsubmit="return confirm('Delete this session?');">
                        <?= Csrf::field() ?>
                        <button class="btn btn-sm btn-error btn-outline gap-1"><i class="ti ti-trash"></i> Delete</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <div class="divider my-5"></div>

        <?php /* ===================== PLAYERS ===================== */ ?>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-bold flex items-center gap-2"><i class="ti ti-users text-primary"></i> Players</h2>
            <span class="text-sm opacity-60">
                <?= count($approved) ?><?= $s['max_players'] !== null ? ' / ' . (int) $s['max_players'] : '' ?>
            </span>
        </div>

        <?php if (empty($approved)): ?>
            <p class="opacity-70 text-sm">No one has joined yet — be the first!</p>
        <?php else: ?>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($approved as $p): ?>
                    <div class="flex items-center gap-2 pl-1 pr-3 py-1 bg-base-200 rounded-full">
                        <?= Avatar::html($p['username'], 'w-7 h-7') ?>
                        <span class="text-sm font-medium"><?= htmlspecialchars($p['username']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php /* ===================== PENDING REQUESTS ===================== */ ?>
        <?php if ($isCreator && !empty($pending)): ?>
            <div class="mt-6">
                <h2 class="text-lg font-bold mb-3 flex items-center gap-2"><i class="ti ti-user-plus text-primary"></i> Pending requests</h2>
                <ul class="space-y-2">
                    <?php foreach ($pending as $p): ?>
                        <li class="flex items-center gap-2 p-2 bg-base-200 rounded-box">
                            <?= Avatar::html($p['username'], 'w-8 h-8') ?>
                            <span class="mr-auto text-sm font-medium"><?= htmlspecialchars($p['username']) ?></span>
                            <form method="POST" action="<?= BASE_PATH ?>/sessions/<?= $s['id'] ?>/approve">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="user_id" value="<?= (int) $p['user_id'] ?>">
                                <button class="btn btn-sm btn-circle btn-success" title="Approve"><i class="ti ti-check"></i></button>
                            </form>
                            <form method="POST" action="<?= BASE_PATH ?>/sessions/<?= $s['id'] ?>/reject">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="user_id" value="<?= (int) $p['user_id'] ?>">
                                <button class="btn btn-sm btn-circle btn-error btn-outline" title="Reject"><i class="ti ti-x"></i></button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php /* ===================== MESSAGES (chat) ===================== */ ?>
        <?php if ($canViewMessages): ?>
            <div class="divider my-5"></div>
            <h2 class="text-lg font-bold mb-3 flex items-center gap-2"><i class="ti ti-messages text-primary"></i> Messages</h2>

            <?php if (empty($comments)): ?>
                <p class="opacity-70 text-sm">No messages yet. Start the conversation!</p>
            <?php else: ?>
                <div id="chat-scroll" class="space-y-1 max-h-96 overflow-y-auto pr-1">
                    <?php foreach ($comments as $c): ?>
                        <?php
                        $mineMsg = $currentUsername === $c['username'];
                        $canDelete = Auth::check() && ((int) $c['user_id'] === Auth::id() || $isAdmin);

                        // hover actions (copy / delete), echoed on the inner side of the bubble
                        ob_start(); ?>
                        <div class="flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition">
                            <button type="button" class="copy-btn btn btn-ghost btn-xs btn-circle"
                                    data-text="<?= htmlspecialchars($c['body']) ?>" title="Copy">
                                <i class="ti ti-copy"></i>
                            </button>
                            <?php if ($canDelete): ?>
                                <form method="POST" action="<?= BASE_PATH ?>/comments/<?= $c['id'] ?>/delete"
                                      onsubmit="return confirm('Delete message?');">
                                    <?= Csrf::field() ?>
                                    <button class="btn btn-ghost btn-xs btn-circle text-error" title="Delete"><i class="ti ti-trash"></i></button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <?php $actions = ob_get_clean(); ?>

                        <div class="flex items-end gap-2 <?= $mineMsg ? 'justify-end' : 'justify-start' ?>">
                            <?php if (!$mineMsg): ?><?= Avatar::html($c['username']) ?><?php endif; ?>

                            <div class="max-w-[75%]">
                                <div class="text-xs opacity-60 mb-0.5 <?= $mineMsg ? 'text-right' : '' ?>">
                                    <?= htmlspecialchars($c['username']) ?> · <?= date('j.n. H:i', strtotime($c['created_at'])) ?>
                                </div>
                                <div class="flex items-center gap-1 group <?= $mineMsg ? 'flex-row-reverse' : '' ?>">
                                    <div class="px-3 py-2 rounded-2xl text-sm break-words <?= $mineMsg ? 'bg-primary text-primary-content' : 'bg-base-200' ?>">
                                        <?= nl2br(htmlspecialchars($c['body'])) ?>
                                    </div>
                                    <?= $actions ?>
                                </div>
                            </div>

                            <?php if ($mineMsg): ?><?= Avatar::html($c['username']) ?><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (Auth::check()): ?>
                <form method="POST" action="<?= BASE_PATH ?>/sessions/<?= $s['id'] ?>/comments" class="mt-4 flex gap-2 items-end">
                    <?= Csrf::field() ?>
                    <label for="comment-body" class="sr-only">Your message</label>
                    <textarea id="comment-body" name="body" rows="1" required placeholder="Leave a message..."
                              class="textarea textarea-bordered w-full"></textarea>
                    <button class="btn btn-primary gap-1"><i class="ti ti-send"></i> Send</button>
                </form>
            <?php else: ?>
                <p class="mt-3 text-sm opacity-70">
                    <a href="<?= BASE_PATH ?>/login" class="link">Log in</a> to leave a message.
                </p>
            <?php endif; ?>
        <?php endif; /* canViewMessages */ ?>

    </div>
</div>

<script>
    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            navigator.clipboard.writeText(btn.dataset.text);
            if (document.activeElement) document.activeElement.blur();
        });
    });

    (function () {
        const sc = document.getElementById('chat-scroll');
        if (sc) sc.scrollTop = sc.scrollHeight;
    })();
</script>
