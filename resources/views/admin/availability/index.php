<section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
    <div class="panel">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.22em] text-slate-500">Doctor schedule</p>
                <h1 class="mt-2 text-3xl font-semibold">Overrides, holidays, and blocked slots</h1>
                <p class="mt-2 text-sm text-slate-500">Use this page for one-off schedule changes after you set the weekly doctor hours inside clinic timings.</p>
            </div>
            <a class="btn-secondary" href="<?= e(url('/admin/settings')) ?>">Open clinic timings</a>
        </div>
        <form method="post" action="<?= e(url('/admin/availability')) ?>" class="mt-6 grid gap-4">
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
                <label for="rule_type">Rule type</label>
                <select id="rule_type" name="rule_type" required>
                    <option value="weekly">Weekly Schedule</option>
                    <option value="date_override">Date Override</option>
                    <option value="holiday">Holiday</option>
                    <option value="blocked_slot">Blocked Slot</option>
                </select>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="weekday">Weekday</label>
                    <select id="weekday" name="weekday">
                        <option value="">Only for weekly schedule</option>
                        <option value="0">Sunday</option>
                        <option value="1">Monday</option>
                        <option value="2">Tuesday</option>
                        <option value="3">Wednesday</option>
                        <option value="4">Thursday</option>
                        <option value="5">Friday</option>
                        <option value="6">Saturday</option>
                    </select>
                </div>
                <div>
                    <label for="specific_date">Specific date</label>
                    <input id="specific_date" type="date" name="specific_date">
                </div>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="start_time">Start time</label>
                    <input id="start_time" type="time" name="start_time">
                </div>
                <div>
                    <label for="end_time">End time</label>
                    <input id="end_time" type="time" name="end_time">
                </div>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="slot_interval_minutes">Slot interval</label>
                    <input id="slot_interval_minutes" type="number" name="slot_interval_minutes" placeholder="Defaults to doctor slot duration">
                </div>
                <div>
                    <label for="reason">Reason</label>
                    <input id="reason" name="reason" placeholder="Optional note">
                </div>
            </div>
            <button class="btn-primary w-full">Save rule</button>
        </form>
    </div>
    <div class="grid gap-6">
        <div class="panel">
            <p class="text-sm uppercase tracking-[0.22em] text-slate-500">Calendar</p>
            <h2 class="mt-2 text-2xl font-semibold">Date-based rules</h2>
            <div class="mt-5" data-calendar data-events='<?= e(json_encode($events)) ?>'></div>
        </div>
        <div class="table-wrap">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Doctor</th>
                    <th class="px-4 py-3 font-medium">Type</th>
                    <th class="px-4 py-3 font-medium">When</th>
                    <th class="px-4 py-3 font-medium">Time</th>
                    <th class="px-4 py-3 font-medium">Action</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                <?php foreach ($rules as $rule): ?>
                    <tr>
                        <td class="px-4 py-3"><?= e($rule['doctor_name']) ?></td>
                        <td class="px-4 py-3 capitalize"><?= e(str_replace('_', ' ', (string) $rule['rule_type'])) ?></td>
                        <td class="px-4 py-3"><?= e($rule['specific_date'] ?: ('Weekday ' . $rule['weekday'])) ?></td>
                        <td class="px-4 py-3"><?= e(trim(($rule['start_time'] ?: '-') . ' - ' . ($rule['end_time'] ?: '-'))) ?></td>
                        <td class="px-4 py-3">
                            <form method="post" action="<?= e(url('/admin/availability/' . $rule['id'] . '/delete')) ?>">
                                <?= csrf_field() ?>
                                <button class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
