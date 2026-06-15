<?php $clinic = current_clinic(); ?>
<section class="auth-shell">
    <aside class="auth-aside">
        <span class="eyebrow-pill">Password help</span>
        <h1><?= $clinic ? 'Recover your patient account for ' . e($clinic['name']) : 'Reset your patient password' ?></h1>
        <p>We’ll send a secure reset link to the patient email address you used while registering.</p>
    </aside>

    <div class="auth-card">
        <p class="section-kicker">Password reset</p>
        <h2>Send reset link</h2>
        <form method="post" action="<?= e(url('/patient/forgot-password')) ?>" class="auth-form">
            <?= csrf_field() ?>
            <div>
                <label for="email">Patient email</label>
                <input id="email" type="email" name="email" required>
            </div>
            <button class="btn-primary w-full" type="submit">Send reset link</button>
        </form>
    </div>
</section>
