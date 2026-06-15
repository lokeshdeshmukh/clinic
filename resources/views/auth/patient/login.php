<div class="mx-auto max-w-xl panel">
    <p class="text-sm uppercase tracking-[0.22em] text-accent-600">Patient login</p>
    <h1 class="mt-2 text-3xl font-semibold">Sign in</h1>
    <form method="post" action="<?= e(url('/patient/login')) ?>" class="mt-6 grid gap-4">
        <?= csrf_field() ?>
        <div>
            <label for="email">Email</label>
            <input id="email" type="email" name="email" required>
        </div>
        <div>
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required>
        </div>
        <button class="btn-primary w-full">Login</button>
    </form>
    <div class="mt-4 flex items-center justify-between text-sm">
        <a class="font-semibold text-brand-700" href="<?= e(url('/patient/forgot-password')) ?>">Forgot password?</a>
        <a class="font-semibold text-slate-600" href="<?= e(url('/patient/register')) ?>">Register</a>
    </div>
</div>
