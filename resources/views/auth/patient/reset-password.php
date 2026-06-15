<section class="auth-shell">
    <aside class="auth-aside">
        <span class="eyebrow-pill">Secure access</span>
        <h1>Choose a new patient password</h1>
        <p>Create a new password, then sign in again to continue booking or managing appointments.</p>
    </aside>

    <div class="auth-card">
        <p class="section-kicker">Update password</p>
        <h2>Set a new password</h2>
        <form method="post" action="<?= e(url('/patient/reset-password')) ?>" class="auth-form">
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
            <button class="btn-primary w-full" type="submit">Update password</button>
        </form>
    </div>
</section>
