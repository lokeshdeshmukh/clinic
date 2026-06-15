<div class="mx-auto max-w-2xl panel">
    <p class="text-sm uppercase tracking-[0.22em] text-brand-700">Clinic onboarding</p>
    <h1 class="mt-2 text-3xl font-semibold">Register your clinic</h1>
    <form method="post" action="<?= e(url('/clinic/register')) ?>" enctype="multipart/form-data" class="mt-6 grid gap-4">
        <?= csrf_field() ?>
        <div>
            <label for="name">Clinic name</label>
            <input id="name" name="name" value="<?= e((string) old('name')) ?>" required>
        </div>
        <div>
            <label for="address">Address</label>
            <textarea id="address" name="address" rows="3" required><?= e((string) old('address')) ?></textarea>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label for="phone">Phone number</label>
                <input id="phone" name="phone" value="<?= e((string) old('phone')) ?>" required>
            </div>
            <div>
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="<?= e((string) old('email')) ?>" required>
            </div>
        </div>
        <div>
            <label for="logo">Logo</label>
            <input id="logo" type="file" name="logo" accept="image/*">
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required>
            </div>
            <div>
                <label for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required>
            </div>
        </div>
        <button class="btn-primary w-full">Create clinic account</button>
    </form>
    <p class="mt-4 text-sm text-slate-500">Already registered? <a class="font-semibold text-brand-700" href="<?= e(url('/clinic/login')) ?>">Sign in</a></p>
</div>
