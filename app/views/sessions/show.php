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

// --- Derived flags & filtered lists (computed once, used in template below) ---
$isCreator = Auth::check() && (int) $s['creator_id'] === Auth::id();
$isAdmin   = Auth::check() && (Auth::user()['role'] ?? 'user') === 'admin';
$approved  = array_filter($participants, fn($p) => $p['status'] === 'approved');
$pending   = array_filter($participants, fn($p) => $p['status'] === 'pending');

// Messages are visible only to people involved in the session:
// the host, an admin, or an approved participant.
$canViewMessages = $isCreator || $isAdmin
    || ($mine !== null && $mine['status'] === 'approved');
?>
<div class="max-w-2xl mx-auto card bg-base-100 shadow p-6">

    <?php /* ===================== HEADER: title + private badge ===================== */ ?>
    <div class="flex items-start justify-between">
        <h1 class="text-2xl font-bold"><?= htmlspecialchars($s['title']) ?></h1>
        <?php if ($s['is_private']): ?>
            <span class="badge badge-warning">Private</span>
        <?php endif; ?>
    </div>

    <?php /* ===================== INFO: game, location, time, players ===================== */ ?>
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

    <div class="flex gap-2 mt-4">
        <a href="<?= BASE_PATH ?>/sessions/<?= $s['id'] ?>/calendar" class="btn btn-sm btn-outline">
            📅 Add to calendar
        </a>
        <a href="<?= htmlspecialchars($googleUrl) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline">
            Add to Google Calendar
        </a>
    </div>

    <?php /* ===================== OWNER ACTIONS: edit / delete (creator or admin) ===================== */ ?>
    <?php if ($isCreator || $isAdmin): ?>
        <div class="flex gap-2 mt-4">
            <a href="<?= BASE_PATH ?>/sessions/<?= $s['id'] ?>/edit" class="btn btn-sm">Edit</a>
            <form method="POST" action="<?= BASE_PATH ?>/sessions/<?= $s['id'] ?>/delete"
                  onsubmit="return confirm('Delete this session?');">
                <?= Csrf::field() ?>
                <button class="btn btn-sm btn-error">Delete</button>
            </form>
        </div>
    <?php endif; ?>

    <?php /* ===================== PLAYERS: approved list + join/leave control ===================== */ ?>
    <div class="mt-6">
        <h2 class="text-lg font-bold mb-2">Players</h2>

        <?php if (empty($approved)): ?>
            <p class="opacity-70">No one has joined yet.</p>
        <?php else: ?>
            <ul class="flex flex-wrap gap-2">
                <?php foreach ($approved as $p): ?>
                    <li class="badge badge-outline"><?= htmlspecialchars($p['username']) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php /* Join/leave button — depends on who's viewing and their participation status */ ?>
        <div class="mt-4">
            <?php if ($isCreator): ?>
                <span class="badge badge-info">You're the host</span>

            <?php elseif (!Auth::check()): ?>
                <a href="<?= BASE_PATH ?>/login" class="btn btn-sm">Log in to join</a>

            <?php elseif ($mine === null): ?>
                <?php $isFull = $s['max_players'] !== null
                        && (int) $s['player_count'] >= (int) $s['max_players']; ?>
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

            <?php else: /* approved */ ?>
                <span class="badge badge-success">You're in</span>
                <form method="POST" action="<?= BASE_PATH ?>/sessions/<?= $s['id'] ?>/leave" class="mt-2">
                    <?= Csrf::field() ?>
                    <button class="btn btn-sm">Leave</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php /* ===================== PENDING REQUESTS: approve/reject (creator only, private sessions) ===================== */ ?>
    <?php if ($isCreator && !empty($pending)): ?>
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

    <?php /* ===================== MESSAGES: comment list + post form (participants only) ===================== */ ?>
    <?php if ($canViewMessages): ?>
    <div class="mt-6">
        <h2 class="text-lg font-bold mb-2">Messages</h2>

        <?php if (empty($comments)): ?>
            <p class="opacity-70">No messages yet.</p>
        <?php else: ?>
            <ul class="space-y-2">
                <?php foreach ($comments as $c): ?>
                    <li class="p-3 bg-base-200 rounded">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-sm"><?= htmlspecialchars($c['username']) ?></span>
                            <span class="text-xs opacity-60"><?= date('j.n.Y H:i', strtotime($c['created_at'])) ?></span>
                        </div>
                        <p class="text-sm mt-1"><?= nl2br(htmlspecialchars($c['body'])) ?></p>

                        <?php /* Delete — only comment author or admin */ ?>
                        <?php if (Auth::check() && ((int) $c['user_id'] === Auth::id() || $isAdmin)): ?>
                            <form method="POST" action="<?= BASE_PATH ?>/comments/<?= $c['id'] ?>/delete" class="mt-1">
                                <?= Csrf::field() ?>
                                <button class="btn btn-xs btn-ghost text-error">Delete</button>
                            </form>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php /* Post form — logged-in users only */ ?>
        <?php if (Auth::check()): ?>
            <form method="POST" action="<?= BASE_PATH ?>/sessions/<?= $s['id'] ?>/comments" class="mt-3">
                <?= Csrf::field() ?>
                <label for="comment-body" class="sr-only">Your message</label>
                <textarea id="comment-body" name="body" rows="2" required placeholder="Leave a message..."
                          class="textarea textarea-bordered w-full"></textarea>
                <button class="btn btn-sm btn-primary mt-2">Post message</button>
            </form>
        <?php else: ?>
            <p class="mt-3 text-sm opacity-70">
                <a href="<?= BASE_PATH ?>/login" class="link">Log in</a> to leave a message.
            </p>
        <?php endif; ?>
    </div>
    <?php endif; /* canViewMessages */ ?>

    <a href="<?= BASE_PATH ?>/sessions" class="link mt-4 inline-block">← Back to list</a>

</div>
