<section class="auth-shell">
    <aside class="auth-aside">
        <span class="eyebrow-pill">Platform bootstrap</span>
        <h1>Create the first Huviena platform admin</h1>
        <p>This one-time setup unlocks clinic provisioning from the web UI, which is especially helpful on shared hosting where you are deploying by ZIP or Git.</p>
    </aside>

    <div class="auth-card">
        <p class="section-kicker">Setup</p>
        <h2>First super admin</h2>
        <form method="post" action="<?= e(url('/super-admin/setup')) ?>" class="auth-form">
            <?= csrf_field() ?>
            <div>
                <label for="name">Full name</label>
                <input id="name" name="name" value="<?= e((string) old('name')) ?>" required>
            </div>
            <div>
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="<?= e((string) old('email')) ?>" required>
            </div>
            <div>
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required>
            </div>
            <div>
                <label for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required>
            </div>
            <button class="btn-primary w-full" type="submit">Create super admin</button>
        </form>
    </div>
</section>
