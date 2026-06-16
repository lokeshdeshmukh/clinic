<div class="mx-auto max-w-3xl panel">
    <div class="mb-6">
        <p class="text-sm uppercase tracking-[0.22em] text-slate-500">Clinic settings</p>
        <h1 class="text-3xl font-semibold">Clinic profile and timings</h1>
        <p class="mt-2 text-sm text-slate-500">Update clinic details, reminder preferences, and weekly doctor hours from one place.</p>
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
        <button class="btn-primary">Save settings</button>
    </form>
</div>

<div class="mx-auto mt-6 max-w-5xl panel">
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-sm uppercase tracking-[0.22em] text-slate-500">Clinic timings</p>
            <h2 class="text-2xl font-semibold">Weekly doctor hours</h2>
            <p class="mt-2 max-w-3xl text-sm text-slate-500">These weekly hours directly control which slots patients can see when booking. Use the detailed schedule page for holidays, blocked slots, and one-off date overrides.</p>
        </div>
        <a class="btn-secondary" href="<?= e(url('/admin/availability')) ?>">Open detailed schedule</a>
    </div>

    <div class="grid gap-6 xl:grid-cols-[0.88fr_1.12fr]">
        <form method="post" action="<?= e(url('/admin/settings/doctor-hours')) ?>" class="grid gap-4">
            <?= csrf_field() ?>
            <div>
                <label for="doctor_id">Doctor</label>
                <select id="doctor_id" name="doctor_id" required>
                    <option value="">Select doctor</option>
                    <?php foreach ($doctors as $doctor): ?>
                        <option value="<?= e((string) $doctor['id']) ?>"><?= e($doctor['name']) ?> (<?= e($doctor['specialization']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="weekday">Day</label>
                <select id="weekday" name="weekday" required>
                    <option value="">Select day</option>
                    <option value="0">Sunday</option>
                    <option value="1">Monday</option>
                    <option value="2">Tuesday</option>
                    <option value="3">Wednesday</option>
                    <option value="4">Thursday</option>
                    <option value="5">Friday</option>
                    <option value="6">Saturday</option>
                </select>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="start_time">Start time</label>
                    <input id="start_time" type="time" name="start_time" required>
                </div>
                <div>
                    <label for="end_time">End time</label>
                    <input id="end_time" type="time" name="end_time" required>
                </div>
            </div>
            <div>
                <label for="slot_interval_minutes">Slot interval (minutes)</label>
                <input id="slot_interval_minutes" type="number" name="slot_interval_minutes" placeholder="Leave blank to use doctor default slot duration">
            </div>
            <button class="btn-primary" type="submit">Save weekly hours</button>
        </form>

        <div class="table-wrap">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Doctor</th>
                    <th class="px-4 py-3 font-medium">Day</th>
                    <th class="px-4 py-3 font-medium">Hours</th>
                    <th class="px-4 py-3 font-medium">Interval</th>
                    <th class="px-4 py-3 font-medium">Action</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                <?php foreach ($weeklyRules as $rule): ?>
                    <tr>
                        <td class="px-4 py-3"><?= e($rule['doctor_name']) ?></td>
                        <td class="px-4 py-3">
                            <?php
                            $weekdays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                            echo e($weekdays[(int) $rule['weekday']] ?? ('Day ' . $rule['weekday']));
                            ?>
                        </td>
                        <td class="px-4 py-3"><?= e(date('g:i A', strtotime((string) $rule['start_time'])) . ' - ' . date('g:i A', strtotime((string) $rule['end_time']))) ?></td>
                        <td class="px-4 py-3"><?= e((string) ($rule['slot_interval_minutes'] ?: 'Default')) ?></td>
                        <td class="px-4 py-3">
                            <form method="post" action="<?= e(url('/admin/settings/doctor-hours/' . $rule['id'] . '/delete')) ?>">
                                <?= csrf_field() ?>
                                <button class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
