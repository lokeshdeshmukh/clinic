<div class="mx-auto max-w-xl panel">
    <h1 class="text-3xl font-semibold">Reset patient password</h1>
    <p class="mt-3 text-sm text-slate-600">We’ll send a secure reset link to your email address.</p>
    <form method="post" action="<?= e(url('/patient/forgot-password')) ?>" class="mt-6 grid gap-4">
        <?= csrf_field() ?>
        <div>
            <label for="email">Patient email</label>
            <input id="email" type="email" name="email" required>
        </div>
        <button class="btn-primary w-full">Send reset link</button>
    </form>
</div>
