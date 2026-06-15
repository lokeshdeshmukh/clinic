<div class="mx-auto max-w-xl panel">
    <h1 class="text-3xl font-semibold">Reset clinic password</h1>
    <p class="mt-3 text-sm text-slate-600">We’ll email a reset link to the clinic admin account.</p>
    <form method="post" action="<?= e(url('/clinic/forgot-password')) ?>" class="mt-6 grid gap-4">
        <?= csrf_field() ?>
        <div>
            <label for="email">Clinic email</label>
            <input id="email" type="email" name="email" required>
        </div>
        <button class="btn-primary w-full">Send reset link</button>
    </form>
</div>
