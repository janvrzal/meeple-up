<?php
/** @var array  $s         Session row (joined data) */
/** @var string $cardBadge Optional HTML for the top-right badge */
$accent = Avatar::color($s['game_name'] ?? $s['title']);
?>
<a href="<?= BASE_PATH ?>/sessions/<?= $s['id'] ?>"
   class="card bg-base-100 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition border border-base-200"
   style="border-left: 4px solid <?= $accent ?>">
    <div class="card-body p-4 gap-2">
        <div class="flex items-start justify-between gap-2">
            <h2 class="card-title text-base"><?= htmlspecialchars($s['title']) ?></h2>
            <?= $cardBadge ?? '' ?>
        </div>
        <div class="flex items-center gap-1 text-sm opacity-80">
            <i class="ti ti-dice"></i> <?= htmlspecialchars($s['game_name'] ?? 'No game selected') ?>
        </div>
        <div class="flex flex-col gap-1 text-sm">
            <span class="flex items-center gap-1"><i class="ti ti-map-pin opacity-60"></i>
                <?= htmlspecialchars($s['location_name'] . ', ' . $s['location_city']) ?></span>
            <span class="flex items-center gap-1"><i class="ti ti-clock opacity-60"></i>
                <?= date('j.n.Y H:i', strtotime($s['scheduled_at'])) ?></span>
        </div>
    </div>
</a>