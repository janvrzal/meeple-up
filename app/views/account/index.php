<?php
/** @var array $user */
/** @var array|null $flash */
/** @var array $cities */
?>

<div class="max-w-xl mx-auto">

    <?php /* ===== Hlavička ===== */ ?>
    <div class="flex items-center gap-3 mb-5">
        <?= Avatar::html($user['username'], 'w-12 h-12') ?>
        <div>
            <h1 class="text-2xl font-bold"><?= htmlspecialchars($user['username']) ?></h1>
            <p class="text-sm opacity-60"><?= htmlspecialchars($user['email']) ?></p>
        </div>
    </div>

    <?php /* ===== Flash zpráva ===== */ ?>
    <?php if ($flash): ?>
        <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-error' ?> text-sm py-2 mb-4">
            <i class="ti <?= $flash['type'] === 'success' ? 'ti-circle-check' : 'ti-alert-circle' ?>"></i>
            <?= htmlspecialchars($flash['msg']) ?>
        </div>
    <?php endif; ?>

    <?php /* ===== Profil ===== */ ?>
    <div class="card bg-base-100 shadow-sm mb-4">
        <div class="card-body">
            <h2 class="font-bold flex items-center gap-2 mb-2"><i class="ti ti-user text-primary"></i> Profile</h2>

            <form action="<?= BASE_PATH ?>/account/profile" method="POST" class="space-y-3">
                <?= Csrf::field() ?>

                <div class="form-control">
                    <label class="label" for="username"><span class="label-text">Username</span></label>
                    <input id="username" type="text" value="<?= htmlspecialchars($user['username']) ?>"
                           class="input input-bordered w-full" disabled>
                </div>

                <div class="form-control">
                    <label class="label" for="email"><span class="label-text">E-mail</span></label>
                    <input id="email" type="email" value="<?= htmlspecialchars($user['email']) ?>"
                           class="input input-bordered w-full" disabled>
                </div>

                <div class="form-control">
                    <label class="label" for="city"><span class="label-text">City</span></label>
                    <?php
                    $userCity = $user['city'] ?? '';
                    // kdyby uživatel měl uložené město, co není v aktuální sadě (starší data), přidej ho
                    if ($userCity !== '' && !in_array($userCity, $cities, true)) {
                        $cities[] = $userCity;
                        sort($cities);
                    }
                    ?>
                    <select id="city" name="city" class="select select-bordered w-full">
                        <option value="">— not set —</option>
                        <?php foreach ($cities as $c): ?>
                            <option value="<?= htmlspecialchars($c) ?>" <?= $userCity === $c ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label class="label">
                        <span class="label-text-alt opacity-60">Cities come from existing venues. Add a session venue to see more.</span>
                    </label>
                </div>

                <button class="btn btn-primary btn-sm gap-1"><i class="ti ti-device-floppy"></i> Save profile</button>
            </form>
        </div>
    </div>

    <?php /* ===== Změna hesla ===== */ ?>
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="font-bold flex items-center gap-2 mb-2"><i class="ti ti-lock text-primary"></i> Change password</h2>

            <form action="<?= BASE_PATH ?>/account/password" method="POST" class="space-y-3">
                <?= Csrf::field() ?>

                <div class="form-control">
                    <label class="label" for="current_password"><span class="label-text">Current password</span></label>
                    <input id="current_password" name="current_password" type="password" required
                           class="input input-bordered w-full">
                </div>

                <div class="grid sm:grid-cols-2 gap-3">
                    <div class="form-control">
                        <label class="label" for="new_password"><span class="label-text">New password</span></label>
                        <input id="new_password" name="new_password" type="password" required minlength="8"
                               class="input input-bordered w-full">
                    </div>
                    <div class="form-control">
                        <label class="label" for="confirm_password"><span class="label-text">Confirm new</span></label>
                        <input id="confirm_password" name="confirm_password" type="password" required minlength="8"
                               class="input input-bordered w-full">
                    </div>
                </div>

                <button class="btn btn-primary btn-sm gap-1"><i class="ti ti-key"></i> Change password</button>
            </form>
        </div>
    </div>
</div>
