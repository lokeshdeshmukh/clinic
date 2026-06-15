<div class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
    <div>
        <p class="text-sm uppercase tracking-[0.22em] text-slate-500">Appointment management</p>
        <h1 class="text-3xl font-semibold">Appointments</h1>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="<?= e(url('/admin/appointments?view=upcoming')) ?>" class="<?= $activeView === 'upcoming' ? 'btn-primary' : 'btn-secondary' ?>">Upcoming</a>
        <a href="<?= e(url('/admin/appointments?view=today')) ?>" class="<?= $activeView === 'today' ? 'btn-primary' : 'btn-secondary' ?>">Today</a>
        <a href="<?= e(url('/admin/appointments?view=completed')) ?>" class="<?= $activeView === 'completed' ? 'btn-primary' : 'btn-secondary' ?>">Completed</a>
    </div>
</div>

<div class="grid gap-4">
    <?php if ($appointments === []): ?>
        <div class="panel text-sm text-slate-500">No appointments found for this view.</div>
    <?php endif; ?>
    <?php foreach ($appointments as $appointment): ?>
        <article class="panel">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <h2 class="text-xl font-semibold"><?= e($appointment['doctor_name']) ?></h2>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-600 capitalize"><?= e($appointment['status']) ?></span>
                    </div>
                    <p class="mt-2 text-sm text-slate-600"><?= e(trim($appointment['first_name'] . ' ' . $appointment['last_name'])) ?>, <?= e($appointment['patient_phone']) ?></p>
                    <p class="mt-2 text-sm text-slate-500"><?= e($appointment['appointment_date']) ?> at <?= e($appointment['start_time']) ?></p>
                </div>
                <div class="grid w-full max-w-lg gap-3">
                    <?php if ($appointment['status'] !== 'completed' && $appointment['status'] !== 'cancelled'): ?>
                        <form method="post" action="<?= e(url('/admin/appointments/' . $appointment['id'] . '/reschedule')) ?>" class="grid gap-3 rounded-3xl bg-slate-50 p-4 md:grid-cols-[1fr_1fr_auto]">
                            <?= csrf_field() ?>
                            <input type="date" name="appointment_date" min="<?= e(date('Y-m-d')) ?>" required>
                            <input type="time" name="start_time" required>
                            <button class="btn-secondary">Reschedule</button>
                        </form>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <form method="post" action="<?= e(url('/admin/appointments/' . $appointment['id'] . '/complete')) ?>">
                                <?= csrf_field() ?>
                                <button class="w-full rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">Mark completed</button>
                            </form>
                            <form method="post" action="<?= e(url('/admin/appointments/' . $appointment['id'] . '/cancel')) ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="reason" value="Cancelled from admin dashboard">
                                <button class="w-full rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">Cancel appointment</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="rounded-3xl bg-slate-50 p-4 text-sm text-slate-500">This appointment is already <?= e($appointment['status']) ?>.</div>
                    <?php endif; ?>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
</div>
