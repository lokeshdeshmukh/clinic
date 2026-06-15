<?php

$clinic = current_clinic();
$redirectTo = $redirectTo ?? (string) old('redirect_to');
?>
<section class="auth-shell">
    <aside class="auth-aside">
        <span class="eyebrow-pill"><?= $clinic ? 'New patient onboarding' : 'Create patient account' ?></span>
        <h1><?= $clinic ? 'Create your account and continue booking at ' . e($clinic['name']) : 'Set up your patient profile once and book across clinics' ?></h1>
        <p><?= $clinic ? 'This keeps the patient flow focused on one clinic, one booking link, and one simple mobile experience.' : 'Your account stores appointment history, lets you reschedule later, and keeps your booking details ready for future visits.' ?></p>

        <?php if ($clinic): ?>
            <div class="auth-clinic-card">
                <strong><?= e($clinic['name']) ?></strong>
                <span><?= e($clinic['address']) ?></span>
                <span><?= e($clinic['phone']) ?></span>
            </div>
        <?php endif; ?>
    </aside>

    <div class="auth-card auth-card--wide">
        <p class="section-kicker">Patient registration</p>
        <h2>Create your profile</h2>
        <form method="post" action="<?= e(url('/patient/register')) ?>" class="auth-form">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect_to" value="<?= e((string) $redirectTo) ?>">
            <div class="form-grid-2">
                <div>
                    <label for="first_name">First name</label>
                    <input id="first_name" name="first_name" value="<?= e((string) old('first_name')) ?>" required>
                </div>
                <div>
                    <label for="last_name">Last name</label>
                    <input id="last_name" name="last_name" value="<?= e((string) old('last_name')) ?>" required>
                </div>
            </div>
            <div class="form-grid-2">
                <div>
                    <label for="phone">Phone number</label>
                    <input id="phone" name="phone" value="<?= e((string) old('phone')) ?>" required>
                </div>
                <div>
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="<?= e((string) old('email')) ?>" required>
                </div>
            </div>
            <div class="form-grid-2">
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
            <div class="form-grid-2">
                <div>
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" required>
                </div>
                <div>
                    <label for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required>
                </div>
            </div>
            <button class="btn-primary w-full" type="submit">Create account</button>
        </form>
        <div class="auth-card__footer">
            <a href="<?= e(url('/patient/login' . ($redirectTo !== '' ? '?redirect_to=' . urlencode((string) $redirectTo) : ''))) ?>">Already have an account?</a>
        </div>
    </div>
</section>
