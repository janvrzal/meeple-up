<?php /** @var string $error */ /** @var string $email */ ?>
<div class="max-w-md mx-auto card bg-base-100 shadow p-6">
    <h1 class="text-2xl font-bold mb-4">Login</h1>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error mb-4"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="<?= BASE_PATH ?>/login" method="POST" class="space-y-3">
        <?= Csrf::field() ?>

        <div class="form-control">
            <label class="label" for="email">
                <span class="label-text">E-mail</span>
            </label>
            <input id="email" name="email" type="email" required
                   value="<?= htmlspecialchars($email ?? '') ?>"
                   class="input input-bordered w-full" />
        </div>

        <div class="form-control">
            <label class="label" for="password">
                <span class="label-text">Password</span>
            </label>
            <input id="password" name="password" type="password" required
                   class="input input-bordered w-full" />
        </div>

        <button class="btn btn-primary w-full">Login</button>
    </form>

    <a href="<?= BASE_PATH ?>/register" class="link mt-3 inline-block">Create an account</a>
</div>