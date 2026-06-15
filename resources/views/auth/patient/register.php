<div class="mx-auto max-w-2xl panel">
    <p class="text-sm uppercase tracking-[0.22em] text-accent-600">Patient account</p>
    <h1 class="mt-2 text-3xl font-semibold">Create your patient profile</h1>
    <form method="post" action="<?= e(url('/patient/register')) ?>" class="mt-6 grid gap-4">
        <?= csrf_field() ?>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label for="first_name">First name</label>
                <input id="first_name" name="first_name" value="<?= e((string) old('first_name')) ?>" required>
            </div>
            <div>
                <label for="last_name">Last name</label>
                <input id="last_name" name="last_name" value="<?= e((string) old('last_name')) ?>" required>
            </div>
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
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label for="date_of_birth">Date of birth</label>
                <input id="date_of_birth" type="date" name="date_of_birth" value="<?= e((string) old('date_of_birth')) ?>">
            </div>
            <div>
                <label for="gender">Gender</label>
                <select id="gender" name="gender">
                    <option value="">Select</option>
                    <option value="male" <?= selected(old('gender'), 'male') ?>>Male</option>
                    <option value="female" <?= selected(old('gender'), 'female') ?>>Female</option>
                    <option value="other" <?= selected(old('gender'), 'other') ?>>Other</option>
                    <option value="prefer_not_to_say" <?= selected(old('gender'), 'prefer_not_to_say') ?>>Prefer not to say</option>
                </select>
            </div>
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
        <button class="btn-primary w-full">Create account</button>
    </form>
    <p class="mt-4 text-sm text-slate-500">Already have an account? <a class="font-semibold text-brand-700" href="<?= e(url('/patient/login')) ?>">Sign in</a></p>
</div>
