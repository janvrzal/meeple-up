<?php
/** @var array $locations */ /** @var array $errors */ /** @var array $old */
?>
<div class="max-w-2xl mx-auto card bg-base-100 shadow">
    <div class="card-body">
    <h1 class="text-2xl font-bold mb-4 flex items-center gap-2">
        <i class="ti ti-calendar-plus text-primary"></i><?= htmlspecialchars($heading ?? 'Create a session') ?>
    </h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error mb-4">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form action="<?= $action ?? (BASE_PATH . '/sessions') ?>" method="POST" class="space-y-3">
        <?= Csrf::field() ?>

<!--        Název sezení-->
        <div class="form-control">
            <label class="label" for="title"><span class="label-text">Title</span></label>
            <input id="title" name="title" type="text" placeholder="Friday night magic..." required
                   value="<?= htmlspecialchars($old['title'] ?? '') ?>"
                   class="input input-bordered w-full" />
        </div>

<!--        Výběr hry-->
        <div class="form-control">
            <label class="label" for="game-search"><span class="label-text">Game (optional)</span></label>
            <input type="text" id="game-search" name="game_name" autocomplete="off" placeholder="Search a game..."
                   class="input input-bordered w-full"
                   value="<?= htmlspecialchars($old['game_name'] ?? '') ?>">
            <input type="hidden" name="bgg_id" id="bgg-id"
                   value="<?= htmlspecialchars($old['bgg_id'] ?? '') ?>">
            <ul id="game-results" class="menu bg-base-200 rounded-box mt-1 hidden"></ul>
        </div>

        <script>
            (function () {
                const base   = '<?= BASE_PATH ?>';
                const input  = document.getElementById('game-search');
                const hidden = document.getElementById('bgg-id');
                const list   = document.getElementById('game-results');
                let timer;
                let controller;                 // pro rušení rozdělaných requestů
                const cache = {};               // jednoduchá paměť výsledků

                input.addEventListener('input', () => {
                    hidden.value = '';
                    clearTimeout(timer);
                    const q = input.value.trim();
                    if (q.length < 2) { list.classList.add('hidden'); list.innerHTML = ''; return; }

                    timer = setTimeout(async () => {
                        // 1) z cache okamžitě
                        if (cache[q]) { render(cache[q]); return; }

                        // 2) zruš předchozí nedokončený dotaz
                        if (controller) controller.abort();
                        controller = new AbortController();

                        try {
                            const res = await fetch(base + '/games/search?q=' + encodeURIComponent(q),
                                { signal: controller.signal });
                            const games = await res.json();
                            cache[q] = games;
                            render(games);
                        } catch (e) { /* abortnutý request – ignoruj */ }
                    }, 150);     // ← zkráceno z 250 na 150 ms
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

<!--        Místo sezení-->
        <!-- Místo sezení -->
        <div class="grid sm:grid-cols-2 gap-3">
            <div id="loc-existing" class="form-control transition">
                <label class="label" for="location_id"><span class="label-text">Choose a venue</span></label>
                <select id="location_id" name="location_id" class="select select-bordered w-full">
                    <option value="">— select existing —</option>
                    <?php foreach ($locations as $loc): ?>
                        <option value="<?= $loc['id'] ?>"
                                <?= (($old['location_id'] ?? '') == $loc['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($loc['name'] . ' (' . $loc['city'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="loc-new" class="form-control transition">
                <label class="label"><span class="label-text">…or add a new one</span></label>
                <div class="space-y-2">
                    <label for="new_location_name" class="sr-only">New venue name</label>
                    <input id="new_location_name" name="new_location_name" type="text" placeholder="Venue name"
                           value="<?= htmlspecialchars($old['new_location_name'] ?? '') ?>"
                           class="input input-bordered w-full" />
                    <label for="new_location_city" class="sr-only">New venue city</label>
                    <input id="new_location_city" name="new_location_city" type="text" placeholder="City"
                           value="<?= htmlspecialchars($old['new_location_city'] ?? '') ?>"
                           class="input input-bordered w-full" />
                </div>
            </div>
        </div>

        <script>
            (function () {
                const sel  = document.getElementById('location_id');
                const name = document.getElementById('new_location_name');
                const city = document.getElementById('new_location_city');
                const boxExisting = document.getElementById('loc-existing');
                const boxNew      = document.getElementById('loc-new');

                function refresh() {
                    const usingExisting = sel.value !== '';
                    const usingNew = name.value.trim() !== '' || city.value.trim() !== '';
                    boxNew.classList.toggle('opacity-40', usingExisting);
                    boxExisting.classList.toggle('opacity-40', usingNew && !usingExisting);
                }

                sel.addEventListener('change', () => {
                    if (sel.value !== '') { name.value = ''; city.value = ''; }   // výběr existující → vyčisti novou
                    refresh();
                });
                [name, city].forEach(el => el.addEventListener('input', () => {
                    if (name.value.trim() !== '' || city.value.trim() !== '') sel.value = '';  // psaní nové → zruš výběr
                    refresh();
                }));

                refresh();   // počáteční stav (i pro edit s předvyplněním)
            })();
        </script>

<!--        Čas sezení-->
        <div class="grid grid-cols-2 gap-2">
            <div class="form-control">
                <label class="label" for="date"><span class="label-text">Date</span></label>
                <input id="date" name="date" type="date" required
                       value="<?= htmlspecialchars($old['date'] ?? '') ?>"
                       class="input input-bordered w-full" />
            </div>
            <div class="form-control">
                <label class="label" for="time"><span class="label-text">Time</span></label>
                <input id="time" name="time" type="time" required
                       value="<?= htmlspecialchars($old['time'] ?? '') ?>"
                       class="input input-bordered w-full" />
            </div>
        </div>

<!--        Max počt hráčů-->
        <div class="form-control">
            <label class="label" for="max_players"><span class="label-text">Max players (optional)</span></label>
            <input id="max_players" name="max_players" type="number" min="2" max="255"
                   value="<?= htmlspecialchars($old['max_players'] ?? '') ?>"
                   placeholder="Leave empty for no limit"
                   class="input input-bordered w-full" />
        </div>

<!--        Veřejné x privátní sezení-->
        <div class="form-control">
            <label class="label cursor-pointer justify-start gap-3">
                <input name="is_private" type="checkbox" class="checkbox"
                    <?= !empty($old['is_private']) ? 'checked' : '' ?> />
                <span class="label-text">Private session (approval required)</span>
            </label>
        </div>

<!--        Popis-->
        <div class="form-control">
            <label class="label" for="description"><span class="label-text">Description</span></label>
            <textarea id="description" name="description" rows="3"
                      class="textarea textarea-bordered w-full"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
        </div>

        <button class="btn btn-primary w-full gap-1"><i class="ti ti-check"></i> <?= htmlspecialchars($submit ?? 'Create session') ?></button>
    </form>
    </div>
</div>