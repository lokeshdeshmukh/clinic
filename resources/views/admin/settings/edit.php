<div class="mx-auto max-w-3xl panel">
    <div class="mb-6">
        <p class="text-sm uppercase tracking-[0.22em] text-slate-500">Clinic profile</p>
        <h1 class="text-3xl font-semibold">Settings</h1>
    </div>
    <form method="post" action="<?= e(url('/admin/settings')) ?>" enctype="multipart/form-data" class="grid gap-4">
        <?= csrf_field() ?>
        <div>
            <label for="name">Clinic name</label>
            <input id="name" name="name" value="<?= e($clinic['name']) ?>" required>
        </div>
        <div>
            <label for="address">Address</label>
            <textarea id="address" name="address" rows="3" required><?= e($clinic['address']) ?></textarea>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label for="phone">Phone</label>
                <input id="phone" name="phone" value="<?= e($clinic['phone']) ?>" required>
            </div>
            <div>
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="<?= e($clinic['email']) ?>" required>
            </div>
        </div>
        <div>
            <label for="logo">Logo upload</label>
            <input id="logo" type="file" name="logo" accept="image/*">
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label for="appointment_reminder_hours">Reminder lead time (hours)</label>
                <input id="appointment_reminder_hours" type="number" name="appointment_reminder_hours" value="<?= e((string) ($settings['appointment_reminder_hours'] ?? '24')) ?>">
            </div>
            <div>
                <label for="currency">Currency</label>
                <input id="currency" name="currency" value="<?= e((string) ($settings['currency'] ?? 'INR')) ?>">
            </div>
        </div>
        <div>
            <label for="deploy_token">Deploy token</label>
            <input id="deploy_token" name="deploy_token" value="<?= e($deployToken) ?>" placeholder="Leave as-is, or replace it to rotate the token">
            <p class="mt-2 text-sm text-slate-500">Deploy hook URL: <span class="break-all font-medium text-slate-700"><?= e($deployHookUrl) ?></span></p>
        </div>
        <button class="btn-primary">Save settings</button>
    </form>
</div>
