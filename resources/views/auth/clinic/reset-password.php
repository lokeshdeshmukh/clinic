<div class="mx-auto max-w-xl panel">
    <h1 class="text-3xl font-semibold">Choose a new clinic password</h1>
    <form method="post" action="<?= e(url('/clinic/reset-password')) ?>" class="mt-6 grid gap-4">
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <div>
            <label for="password">New password</label>
            <input id="password" type="password" name="password" required>
        </div>
        <div>
            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required>
        </div>
        <button class="btn-primary w-full">Update password</button>
    </form>
</div>
