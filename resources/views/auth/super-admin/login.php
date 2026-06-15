<section class="auth-shell">
    <aside class="auth-aside">
        <span class="eyebrow-pill">Platform access</span>
        <h1>Manage clinics from one secure admin workspace</h1>
        <p>Use the super admin account to add clinics, review clinic slugs for subdomains, and keep provisioning separate from clinic-level operations.</p>
    </aside>

    <div class="auth-card">
        <p class="section-kicker">Platform admin login</p>
        <h2>Sign in</h2>
        <form method="post" action="<?= e(url('/super-admin/login')) ?>" class="auth-form">
            <?= csrf_field() ?>
            <div>
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="<?= e((string) old('email')) ?>" required>
            </div>
            <div>
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required>
            </div>
            <button class="btn-primary w-full" type="submit">Login</button>
        </form>
    </div>
</section>
