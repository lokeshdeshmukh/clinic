<section class="auth-shell">
    <aside class="auth-aside">
        <span class="eyebrow-pill">Platform access</span>
        <h1>Manage clinics from one secure admin workspace</h1>
        <p>Use the fixed platform admin account to add clinics, switch them on or off, delete unused clinics, and control deployment updates separately from clinic-level settings.</p>
    </aside>

    <div class="auth-card">
        <p class="section-kicker">Platform admin login</p>
        <h2>Sign in</h2>
        <form method="post" action="<?= e(url('/super-admin/login')) ?>" class="auth-form">
            <?= csrf_field() ?>
            <div>
                <label for="identifier">Username</label>
                <input id="identifier" name="identifier" value="<?= e((string) old('identifier', 'admin')) ?>" required>
            </div>
            <div>
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required>
            </div>
            <p class="text-sm text-slate-500">Default username: <strong>admin</strong></p>
            <button class="btn-primary w-full" type="submit">Login</button>
        </form>
    </div>
</section>
