<?php /** @var string $error */ /** @var string $email */ ?>
<div class="max-w-md mx-auto card bg-base-100 shadow-lg mt-6">
    <div class="card-body">
        <h1 class="text-2xl font-bold flex items-center gap-2 mb-2">
            <i class="ti ti-login-2 text-primary"></i> Login
        </h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error text-sm py-2">
                <i class="ti ti-alert-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_PATH ?>/login" method="POST" class="space-y-3">
            <?= Csrf::field() ?>

            <div class="form-control">
                <label class="label" for="email"><span class="label-text">E-mail</span></label>
                <div class="relative">
                    <i class="ti ti-mail absolute left-3 top-1/2 -translate-y-1/2 opacity-50"></i>
                    <input id="email" name="email" type="email" required
                           value="<?= htmlspecialchars($email ?? '') ?>"
                           class="input input-bordered w-full pl-9">
                </div>
            </div>

            <div class="form-control">
                <label class="label" for="password"><span class="label-text">Password</span></label>
                <div class="relative">
                    <i class="ti ti-lock absolute left-3 top-1/2 -translate-y-1/2 opacity-50"></i>
                    <input id="password" name="password" type="password" required
                           class="input input-bordered w-full pl-9">
                </div>
            </div>

            <button class="btn btn-primary w-full gap-1"><i class="ti ti-login-2"></i> Login</button>
        </form>

        <a href="<?= BASE_PATH ?>/register" class="link text-sm mt-2 inline-block">Create an account</a>
    </div>
</div>