<?php /** @var array $tournaments */ ?>

<div class="flex items-center justify-between mb-5">
    <h1 class="text-2xl font-bold flex items-center gap-2">
        <i class="ti ti-trophy text-primary"></i> Tournaments
    </h1>
    <?php if (Auth::check()): ?>
        <a href="<?= BASE_PATH ?>/tournaments/create" class="btn btn-primary btn-sm gap-1">
            <i class="ti ti-plus"></i> New tournament
        </a>
    <?php endif; ?>
</div>

<?php if (empty($tournaments)): ?>
    <div class="flex flex-col items-center text-center py-16 opacity-70">
        <i class="ti ti-trophy-off text-5xl mb-3"></i>
        <p>No tournaments yet.</p>
        <?php if (Auth::check()): ?>
            <a href="<?= BASE_PATH ?>/tournaments/create" class="btn btn-primary btn-sm mt-4 gap-1">
                <i class="ti ti-plus"></i> Create one
            </a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="grid gap-4 md:grid-cols-2">
        <?php foreach ($tournaments as $t): ?>
            <a href="<?= BASE_PATH ?>/tournaments/<?= $t['id'] ?>"
               class="card bg-base-100 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition border border-base-200">
                <div class="card-body p-4 flex-row items-center gap-3">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center shrink-0 bg-primary text-primary-content">
                        <i class="ti ti-trophy text-2xl"></i>
                    </div>
                    <div class="min-w-0 mr-auto">
                        <h2 class="card-title text-base truncate"><?= htmlspecialchars($t['name']) ?></h2>
                        <p class="text-sm opacity-70 truncate flex items-center gap-1">
                            <i class="ti ti-dice"></i> <?= htmlspecialchars($t['game_name']) ?>
                        </p>
                        <p class="text-xs opacity-60">by <?= htmlspecialchars($t['creator_name']) ?></p>
                    </div>
                    <span class="badge badge-ghost gap-1">
                        <i class="ti ti-calendar-event"></i> <?= (int) $t['session_count'] ?>
                    </span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
