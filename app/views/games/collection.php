<?php /** @var array $games */ ?>

<div class="max-w-3xl mx-auto">

    <div class="flex items-center justify-between mb-5">
        <h1 class="text-2xl font-bold flex items-center gap-2">
            <i class="ti ti-cards text-primary"></i> My favorite games
        </h1>
        <?php if (!empty($games)): ?>
            <span class="text-sm opacity-60"><?= count($games) ?> game<?= count($games) === 1 ? '' : 's' ?></span>
        <?php endif; ?>
    </div>

    <?php if (empty($games)): ?>
        <div class="flex flex-col items-center text-center py-16 opacity-70">
            <i class="ti ti-heart text-5xl mb-3"></i>
            <p>You haven’t added any favorite games yet.</p>
            <p class="text-sm">Tap the heart on a game to add it here.</p>
            <a href="<?= BASE_PATH ?>/sessions" class="btn btn-primary btn-sm mt-4 gap-1">
                <i class="ti ti-calendar-event"></i> Browse sessions
            </a>
        </div>
    <?php else: ?>
        <div class="card bg-base-100 shadow-sm">
            <ul class="divide-y divide-base-200">
                <?php foreach ($games as $g): ?>
                    <?php $accent = Avatar::color($g['name']); ?>
                    <li class="group flex items-center gap-3 px-4 py-3 hover:bg-base-200/40 transition">

                        <?php /* game token (identita hry) */ ?>
                        <span class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 text-white"
                              style="background-color: <?= $accent ?>">
                            <i class="ti ti-dice"></i>
                        </span>

                        <div class="mr-auto min-w-0">
                            <a href="<?= BASE_PATH ?>/sessions?game_id=<?= (int) $g['id'] ?>"
                               class="font-medium truncate hover:underline" title="Find sessions with this game">
                                <?= htmlspecialchars($g['name']) ?>
                            </a>
                            <?php if (!empty($g['year_published'])): ?>
                                <div class="text-xs opacity-60"><?= (int) $g['year_published'] ?></div>
                            <?php endif; ?>
                        </div>

                        <form method="POST" action="<?= BASE_PATH ?>/games/<?= (int) $g['id'] ?>/favorite">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="redirect" value="/games">
                            <button class="btn btn-ghost btn-sm btn-circle text-error sm:opacity-0 sm:group-hover:opacity-100 transition"
                                    title="Remove from collection">
                                <i class="ti ti-trash"></i>
                            </button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>
