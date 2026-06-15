<div class="mb-6">
    <p class="text-sm uppercase tracking-[0.22em] text-accent-600">Patient dashboard</p>
    <h1 class="mt-2 text-3xl font-semibold">Manage your appointments</h1>
</div>

<section class="grid gap-8">
    <div>
        <div class="mb-4 flex items-end justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.22em] text-slate-500">Upcoming</p>
                <h2 class="text-2xl font-semibold">Upcoming appointments</h2>
            </div>
            <a href="<?= e(url('/clinics')) ?>" class="btn-secondary px-4 py-2">Book another</a>
        </div>
        <div class="grid gap-4">
            <?php if ($upcomingAppointments === []): ?>
                <div class="panel text-sm text-slate-500">No upcoming appointments yet.</div>
            <?php endif; ?>
            <?php foreach ($upcomingAppointments as $appointment): ?>
                <article class="panel">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <h3 class="text-xl font-semibold"><?= e($appointment['doctor_name']) ?></h3>
                            <p class="mt-1 text-sm text-brand-700"><?= e($appointment['specialization']) ?></p>
                            <p class="mt-3 text-sm text-slate-600"><?= e($appointment['clinic_name']) ?>, <?= e($appointment['clinic_phone']) ?></p>
                            <p class="mt-2 text-sm text-slate-500"><?= e($appointment['appointment_date']) ?> at <?= e($appointment['start_time']) ?></p>
                        </div>
                        <div class="w-full max-w-md space-y-3">
                            <form method="post" action="<?= e(url('/patient/appointments/' . $appointment['id'] . '/reschedule')) ?>" class="grid gap-3 rounded-3xl bg-slate-50 p-4">
                                <?= csrf_field() ?>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="date" name="appointment_date" min="<?= e(date('Y-m-d')) ?>" required>
                                    <input type="time" name="start_time" required>
                                </div>
                                <button class="btn-secondary w-full">Reschedule</button>
                            </form>
                            <form method="post" action="<?= e(url('/patient/appointments/' . $appointment['id'] . '/cancel')) ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="reason" value="Cancelled from patient dashboard">
                                <button class="w-full rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">Cancel appointment</button>
                            </form>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>

    <div>
        <div class="mb-4">
            <p class="text-sm uppercase tracking-[0.22em] text-slate-500">History</p>
            <h2 class="text-2xl font-semibold">Past and changed appointments</h2>
        </div>
        <div class="table-wrap">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Date</th>
                    <th class="px-4 py-3 font-medium">Doctor</th>
                    <th class="px-4 py-3 font-medium">Clinic</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                <?php foreach ($historyAppointments as $appointment): ?>
                    <tr>
                        <td class="px-4 py-3"><?= e($appointment['appointment_date']) ?> <?= e($appointment['start_time']) ?></td>
                        <td class="px-4 py-3"><?= e($appointment['doctor_name']) ?></td>
                        <td class="px-4 py-3"><?= e($appointment['clinic_name']) ?></td>
                        <td class="px-4 py-3 capitalize"><?= e(str_replace('_', ' ', (string) $appointment['status'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
