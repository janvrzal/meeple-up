<?php
/** @var array $errors */
/** @var array $old */
$errors = $errors ?? [];
$old    = $old ?? [];
?>
<div class="max-w-xl mx-auto card bg-base-100 shadow p-6">
    <h1 class="text-2xl font-bold mb-4 flex items-center gap-2">
        <i class="ti ti-trophy text-primary"></i> New tournament
    </h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error mb-4">
            <ul class="list-disc list-inside">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?= BASE_PATH ?>/tournaments" method="POST" class="space-y-3">
        <?= Csrf::field() ?>

        <div class="form-control">
            <label class="label" for="name"><span class="label-text">Tournament name</span></label>
            <input id="name" name="name" type="text" required
                   value="<?= htmlspecialchars($old['name'] ?? '') ?>"
                   class="input input-bordered w-full" />
        </div>

        <div class="form-control">
            <label class="label" for="game-search"><span class="label-text">Game</span></label>
            <input type="text" id="game-search" name="game_name" autocomplete="off" placeholder="Search a game..."
                   class="input input-bordered w-full"
                   value="<?= htmlspecialchars($old['game_name'] ?? '') ?>">
            <input type="hidden" name="bgg_id" id="bgg-id" value="<?= htmlspecialchars($old['bgg_id'] ?? '') ?>">
            <ul id="game-results" class="menu bg-base-200 rounded-box mt-1 hidden"></ul>
        </div>

        <div class="form-control">
            <label class="label" for="description"><span class="label-text">Description (optional)</span></label>
            <textarea id="description" name="description" rows="3"
                      class="textarea textarea-bordered w-full"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
        </div>

        <?php /* na čerstvém formuláři zaškrtnuto; při chybě (name odeslán) respektuj odeslanou hodnotu */ ?>
        <?php $joinChecked = !isset($old['name']) || !empty($old['join_self']); ?>
        <div class="form-control">
            <label class="label cursor-pointer justify-start gap-3">
                <input name="join_self" type="checkbox" class="checkbox"
                    <?= $joinChecked ? 'checked' : '' ?> />
                <span class="label-text">Join this tournament as a member</span>
            </label>
        </div>

        <button class="btn btn-primary w-full gap-1"><i class="ti ti-check"></i> Create tournament</button>
    </form>
</div>

<script>
(function () {
    const base   = '<?= BASE_PATH ?>';
    const input  = document.getElementById('game-search');
    const hidden = document.getElementById('bgg-id');
    const list   = document.getElementById('game-results');
    let timer, controller;
    const cache = {};

    input.addEventListener('input', () => {
        hidden.value = '';
        clearTimeout(timer);
        const q = input.value.trim();
        if (q.length < 2) { list.classList.add('hidden'); list.innerHTML = ''; return; }
        timer = setTimeout(async () => {
            if (cache[q]) { render(cache[q]); return; }
            if (controller) controller.abort();
            controller = new AbortController();
            try {
                const res = await fetch(base + '/games/search?q=' + encodeURIComponent(q), { signal: controller.signal });
                const games = await res.json();
                cache[q] = games;
                render(games);
            } catch (e) {}
        }, 150);
    });

    function render(games) {
        list.innerHTML = '';
        games.forEach(g => {
            const li = document.createElement('li');
            const a  = document.createElement('a');
            a.textContent = g.name + (g.year_published ? ' (' + g.year_published + ')' : '');
            a.addEventListener('click', () => {
                input.value  = g.name;
                hidden.value = g.bgg_id;
                list.classList.add('hidden');
            });
            li.appendChild(a);
            list.appendChild(li);
        });
        list.classList.remove('hidden');
    }
})();
</script>
