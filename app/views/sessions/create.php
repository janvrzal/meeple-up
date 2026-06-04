<?php
/** @var array $locations */ /** @var array $errors */ /** @var array $old */
?>
<div class="max-w-2xl mx-auto card bg-base-100 shadow p-6">
    <h1 class="text-2xl font-bold mb-4"><?= htmlspecialchars($heading ?? 'Create a session') ?></h1>

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
            <input id="title" name="title" type="text" required
                   value="<?= htmlspecialchars($old['title'] ?? '') ?>"
                   class="input input-bordered w-full" />
        </div>

<!--        Místo sezení-->
        <div class="form-control">
            <label class="label" for="location_id"><span class="label-text">Location</span></label>
            <select id="location_id" name="location_id" class="select select-bordered w-full">
                <option value="">— choose existing —</option>
                <?php foreach ($locations as $loc): ?>
                    <option value="<?= $loc['id'] ?>"
                            <?= (($old['location_id'] ?? '') == $loc['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($loc['name'] . ' (' . $loc['city'] . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="grid grid-cols-2 gap-2">
            <label class="label" for="new_location_name"><span class="label-text">New Location Name</span></label>
            <input id='new_location_name' name="new_location_name" type="text" placeholder="New venue name"
                   value="<?= htmlspecialchars($old['new_location_name'] ?? '') ?>"
                   class="input input-bordered w-full" />
            <label class="label" for="new_location_city"><span class="label-text">New Location City</span></label>
            <input id='new_location_city' name="new_location_city" type="text" placeholder="City"
                   value="<?= htmlspecialchars($old['new_location_city'] ?? '') ?>"
                   class="input input-bordered w-full" />
        </div>

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

        <button class="btn btn-primary w-full"><?= htmlspecialchars($submit ?? 'Create session') ?></button>
    </form>
</div>