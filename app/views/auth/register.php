<?php
/** @var array $errors */ /** @var string $username */ /** @var string $email */ /** @var array $cities */
?>
<div class="max-w-md mx-auto card bg-base-100 shadow-lg mt-6">
    <div class="card-body">
        <h1 class="text-2xl font-bold flex items-center gap-2 mb-2">
            <i class="ti ti-user-plus text-primary"></i> Registration
        </h1>

     <?php if(!empty($errors)): ?>
        <div class="alert alert-error text-sm py-2">
            <ul class="list-disc list-inside">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
     <?php endif; ?>

    <form action="<?= BASE_PATH ?>/register" method="POST" class="space-y-3">
        <?= Csrf::field() ?>

        <div class="form-control">
            <label class="label" for="username">
                <span class="label-text">Username</span>
            </label>
            <div class="relative">
                <i class="ti ti-user absolute left-3 top-1/2 -translate-y-1/2 opacity-50"></i>
                <input id="username" name="username" type="text" required
                       value="<?= htmlspecialchars($username ?? '') ?>"
                       class="input input-bordered w-full pl-9">
            </div>
        </div>

        <div class="form-control">
            <label class="label" for="email">
                <span class="label-text">E-mail</span>
            </label>
            <div class="relative">
                <i class="ti ti-mail absolute left-3 top-1/2 -translate-y-1/2 opacity-50"></i>
                <input id="email" name="email" type="email" required
                       value="<?= htmlspecialchars($email ?? '') ?>"
                       class="input input-bordered w-full pl-9">
            </div>
        </div>

        <div class="form-control">
            <label class="label" for="password">
                <span class="label-text">Password</span>
            </label>
            <div class="relative">
                <i class="ti ti-lock absolute left-3 top-1/2 -translate-y-1/2 opacity-50"></i>
                <input id="password" name="password" type="password" required
                       class="input input-bordered w-full pl-9">
            </div>
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

        <button class="btn btn-primary w-full gap-1"><i class="ti ti-user-plus"></i>Register</button>
    </form>

    <a href="<?= BASE_PATH ?>/login" class="link mt-3 inline-block">I already have an account</a>
    </div>
</div>