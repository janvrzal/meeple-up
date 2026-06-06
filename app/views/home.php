<div class="hero py-10">
    <div class="hero-content text-center flex-col">
        <img src="<?= BASE_PATH ?>/assets/mascot.svg" alt="" class="h-24 w-24 mb-2">
        <h1 class="text-4xl font-bold">Find your next <span class="text-primary">game night</span></h1>
        <p class="py-4 max-w-lg opacity-80">
            Roll Call helps you organize board game sessions, invite friends,
            and find players who share your favorite games.
        </p>
        <div class="flex flex-wrap gap-2 justify-center">
            <a href="<?= BASE_PATH ?>/register" class="btn btn-primary gap-1"><i class="ti ti-user-plus"></i> Get started</a>
            <a href="<?= BASE_PATH ?>/sessions" class="btn btn-ghost gap-1"><i class="ti ti-calendar-event"></i> Browse sessions</a>
        </div>
    </div>
</div>

<div class="grid md:grid-cols-3 gap-4 mt-4">
    <?php
    $features = [
            ['ti-calendar-plus', 'Organize sessions', 'Pick a game, a place and a time. Open it to friends or the whole community.'],
            ['ti-users',         'Find players',      'Join sessions near you and meet people who love the same games.'],
            ['ti-dice',          '30,000+ games',     'Search the BoardGameGeek catalog and attach the exact game you’ll play.'],
    ];
    foreach ($features as [$icon, $title, $text]): ?>
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body items-center text-center">
                <i class="ti <?= $icon ?> text-3xl text-primary"></i>
                <h3 class="font-bold"><?= $title ?></h3>
                <p class="text-sm opacity-70"><?= $text ?></p>
            </div>
        </div>
    <?php endforeach; ?>
</div>