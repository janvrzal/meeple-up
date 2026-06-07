<?php
/** @var array  $s         Session row (joined data) */
/** @var string $cardBadge Optional HTML for the top-right badge */
$accent    = Avatar::color($s['game_name'] ?? $s['title']);
$cancelled = ($s['status'] ?? 'open') === 'cancelled';
?>
<a href="<?= BASE_PATH ?>/sessions/<?= $s['id'] ?>"
   class="card bg-base-100 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition border border-base-200 relative overflow-hidden">

    <div class="card-body p-4 flex-row items-start gap-3 <?= $cancelled ? 'grayscale opacity-50' : '' ?>">

        <?php /* game token — barevná identita hry (ne stav) */ ?>
        <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 text-white"
             style="background-color: <?= $accent ?>">
            <i class="ti ti-dice text-xl"></i>
        </div>

        <div class="min-w-0 flex-1 flex flex-col gap-1">
            <div class="flex items-start justify-between gap-2">
                <h2 class="card-title text-base"><?= htmlspecialchars($s['title']) ?></h2>
                <?= $cardBadge ?? '' ?>
            </div>
            <div class="text-sm opacity-80 truncate"><?= htmlspecialchars($s['game_name'] ?? 'No game selected') ?></div>
            <div class="flex flex-col gap-1 text-sm">
                <span class="flex items-center gap-1"><i class="ti ti-map-pin opacity-60"></i>
                    <?= htmlspecialchars($s['location_name'] . ', ' . $s['location_city']) ?></span>
                <span class="flex items-center gap-1"><i class="ti ti-clock opacity-60"></i>
                    <?= date('j.n.Y H:i', strtotime($s['scheduled_at'])) ?></span>
            </div>
        </div>
    </div>

    <?php if ($cancelled): ?>
        <div class="absolute inset-0 flex items-center justify-center bg-base-300/40">
            <span class="badge badge-error badge-lg gap-1 shadow"><i class="ti ti-ban"></i> Cancelled</span>
        </div>
    <?php endif; ?>
</a>
