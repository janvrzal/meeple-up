<?php
/** @var int    $code */
/** @var string $message */

$presets = [
    400 => ['Hmm, that move’s not allowed',  'The request didn’t make sense.'],
    403 => ['Not your turn',                 'You don’t have access to this one.'],
    404 => ['Lost a meeple?',                'This page rolled off the table.'],
    419 => ['Your session timed out',        'The form token expired — please try again.'],
    500 => ['Something tipped over',         'An unexpected error occurred on our side.'],
];
[$title, $text] = $presets[$code] ?? ['Oops', 'Something went wrong.'];
?>

<div class="flex flex-col items-center text-center py-16">
    <img src="<?= BASE_PATH ?>/assets/mascot.svg" alt=""
         class="w-28 h-28 mb-2 -rotate-12 opacity-90" onerror="this.style.display='none'">

    <div class="text-7xl font-black text-primary leading-none"><?= (int) $code ?></div>
    <h1 class="text-2xl font-bold mt-3"><?= htmlspecialchars($title) ?></h1>
    <p class="opacity-70 mt-1 max-w-sm"><?= htmlspecialchars($message !== '' ? $message : $text) ?></p>

    <div class="flex gap-2 mt-6">
        <a href="<?= BASE_PATH ?>/" class="btn btn-primary gap-1"><i class="ti ti-home"></i> Back home</a>
        <a href="<?= BASE_PATH ?>/sessions" class="btn btn-ghost gap-1"><i class="ti ti-calendar-event"></i> Browse sessions</a>
    </div>
</div>
