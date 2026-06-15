<section class="grid gap-6">
    <div class="panel">
        <p class="text-sm uppercase tracking-[0.22em] text-slate-500">Platform dashboard</p>
        <h1 class="mt-2 text-3xl font-semibold">Create and manage clinics</h1>
        <p class="mt-3 max-w-3xl text-slate-600">Each clinic slug can map directly to a subdomain like <strong>slug.huviena.com</strong>. Once created here, the same codebase can render a focused booking surface for that clinic.</p>
    </div>

    <div class="grid gap-6 xl:grid-cols-[0.92fr_1.08fr]">
        <div class="panel">
            <p class="text-sm uppercase tracking-[0.22em] text-slate-500">New clinic</p>
            <h2 class="mt-2 text-2xl font-semibold">Provision clinic admin</h2>
            <form method="post" action="<?= e(url('/super-admin/clinics')) ?>" enctype="multipart/form-data" class="mt-6 grid gap-4">
                <?= csrf_field() ?>
                <div>
                    <label for="name">Clinic name</label>
                    <input id="name" name="name" value="<?= e((string) old('name')) ?>" required>
                </div>
                <div>
                    <label for="slug">Slug</label>
                    <input id="slug" name="slug" value="<?= e((string) old('slug')) ?>" placeholder="Optional. Leave blank to auto-generate from clinic name.">
                </div>
                <div>
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="3" required><?= e((string) old('address')) ?></textarea>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="phone">Phone</label>
                        <input id="phone" name="phone" value="<?= e((string) old('phone')) ?>" required>
                    </div>
                    <div>
                        <label for="email">Admin email</label>
                        <input id="email" type="email" name="email" value="<?= e((string) old('email')) ?>" required>
                    </div>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="password">Temporary password</label>
                        <input id="password" type="password" name="password" required>
                    </div>
                    <div>
                        <label for="password_confirmation">Confirm password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required>
                    </div>
                </div>
                <div>
                    <label for="logo">Clinic logo</label>
                    <input id="logo" type="file" name="logo" accept="image/*">
                </div>
                <button class="btn-primary" type="submit">Create clinic</button>
            </form>
        </div>

        <div class="table-wrap">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Clinic</th>
                    <th class="px-4 py-3 font-medium">Slug</th>
                    <th class="px-4 py-3 font-medium">Doctors</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Admin email</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                <?php foreach ($clinics as $clinic): ?>
                    <tr>
                        <td class="px-4 py-3">
                            <strong class="block text-slate-900"><?= e($clinic['name']) ?></strong>
                            <span class="text-slate-500"><?= e($clinic['phone']) ?></span>
                        </td>
                        <td class="px-4 py-3 font-medium text-slate-700"><?= e($clinic['slug']) ?></td>
                        <td class="px-4 py-3"><?= e((string) $clinic['doctor_count']) ?></td>
                        <td class="px-4 py-3 capitalize"><?= e((string) $clinic['status']) ?></td>
                        <td class="px-4 py-3"><?= e($clinic['email']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
