<?php
/** @var array $errors */ /** @var string $username */ /** @var string $email */
?>
<div class="max-w-md mx-auto card bg-base-100 shadow p-6">
     <h1 class="text2xl font-bold mb-4">Registration</h1>
     <?php if(!empty($errors)): ?>
        <div class="alert alert-error mb-4">
            <ul>
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
            <input id="username" name="username" type="text" required
                   value="<?= htmlspecialchars($username ?? '') ?>"
                   class="input input-bordered w-full" />
        </div>

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
            <input id="password" name="password" type="password" required minlength="8"
                   class="input input-bordered w-full" />
        </div>

        <button class="btn btn-primary w-full">Register</button>
    </form>

    <a href="<?= BASE_PATH ?>/login" class="link mt-3 inline-block">I already have an account</a>
</div>