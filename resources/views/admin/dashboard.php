<?php
$bookingLabels = array_map(static fn (array $row): string => $row['period'], $dashboard['booking_series']);
$bookingValues = array_map(static fn (array $row): int => (int) $row['total'], $dashboard['booking_series']);
$revenueLabels = array_map(static fn (array $row): string => $row['period'], $dashboard['revenue_series']);
$revenueValues = array_map(static fn (array $row): float => (float) $row['total'], $dashboard['revenue_series']);
?>
<section class="panel mb-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm uppercase tracking-[0.22em] text-brand-700">Admin dashboard</p>
            <h1 class="mt-2 text-3xl font-semibold"><?= e($clinic['name']) ?></h1>
            <p class="mt-2 text-sm text-slate-600">Monitor bookings, update clinic timings, and control which doctor slots patients can see.</p>
        </div>
    </div>
    <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <a href="<?= e(url('/admin/doctors/create')) ?>" class="btn-primary">Add doctor</a>
        <a href="<?= e(url('/admin/appointments')) ?>" class="btn-secondary">Manage appointments</a>
        <a href="<?= e(url('/admin/patients')) ?>" class="btn-secondary">Patient records</a>
        <a href="<?= e(url('/admin/settings')) ?>" class="btn-secondary">Clinic timings</a>
        <a href="<?= e(url('/admin/availability')) ?>" class="btn-secondary">Doctor schedule</a>
    </div>
    <p class="mt-4 text-sm text-slate-500">Patients only see slots that fall inside the weekly hours, blocked slots, holidays, and date overrides you configure here.</p>
</section>

<section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    <div class="metric-card">
        <p class="text-sm text-slate-500">Today's Appointments</p>
        <p class="mt-3 text-3xl font-semibold"><?= e((string) $dashboard['metrics']['today_appointments']) ?></p>
    </div>
    <div class="metric-card">
        <p class="text-sm text-slate-500">Total Doctors</p>
        <p class="mt-3 text-3xl font-semibold"><?= e((string) $dashboard['metrics']['total_doctors']) ?></p>
    </div>
    <div class="metric-card">
        <p class="text-sm text-slate-500">Total Patients</p>
        <p class="mt-3 text-3xl font-semibold"><?= e((string) $dashboard['metrics']['total_patients']) ?></p>
    </div>
    <div class="metric-card">
        <p class="text-sm text-slate-500">Monthly Appointments</p>
        <p class="mt-3 text-3xl font-semibold"><?= e((string) $dashboard['metrics']['monthly_appointments']) ?></p>
    </div>
    <div class="metric-card">
        <p class="text-sm text-slate-500">Monthly Revenue</p>
        <p class="mt-3 text-3xl font-semibold">INR <?= e(number_format((float) $dashboard['metrics']['monthly_revenue'], 2)) ?></p>
    </div>
    <div class="metric-card">
        <p class="text-sm text-slate-500">Upcoming Appointments</p>
        <p class="mt-3 text-3xl font-semibold"><?= e((string) count($dashboard['upcoming_appointments'])) ?></p>
    </div>
</section>

<section class="mt-8 grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
    <div class="panel">
        <div class="mb-4 flex items-end justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.22em] text-slate-500">Appointment chart</p>
                <h2 class="text-2xl font-semibold">Monthly bookings</h2>
            </div>
        </div>
        <div class="h-72">
            <canvas data-chart="line" data-chart-label="Bookings" data-labels='<?= e(json_encode($bookingLabels)) ?>' data-values='<?= e(json_encode($bookingValues)) ?>'></canvas>
        </div>
    </div>
    <div class="panel">
        <div class="mb-4">
            <p class="text-sm uppercase tracking-[0.22em] text-slate-500">Revenue chart</p>
            <h2 class="text-2xl font-semibold">Monthly revenue</h2>
        </div>
        <div class="h-72">
            <canvas data-chart="bar" data-chart-label="Revenue" data-labels='<?= e(json_encode($revenueLabels)) ?>' data-values='<?= e(json_encode($revenueValues)) ?>'></canvas>
        </div>
    </div>
</section>

<section class="mt-8 grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
    <div class="panel">
        <div class="mb-4">
            <p class="text-sm uppercase tracking-[0.22em] text-slate-500">Calendar</p>
            <h2 class="text-2xl font-semibold">Upcoming schedule</h2>
        </div>
        <div data-calendar data-events='<?= e(json_encode($dashboard['calendar_events'])) ?>'></div>
    </div>
    <div class="grid gap-6">
        <div class="panel">
            <div class="mb-4">
                <p class="text-sm uppercase tracking-[0.22em] text-slate-500">Top performing doctors</p>
                <h2 class="text-2xl font-semibold">Most booked</h2>
            </div>
            <div class="space-y-3">
                <?php foreach ($dashboard['top_doctors'] as $doctor): ?>
                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3 text-sm">
                        <span class="font-medium text-slate-800"><?= e($doctor['name']) ?></span>
                        <span class="text-slate-500"><?= e((string) $doctor['total_appointments']) ?> bookings</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="panel">
            <div class="mb-4">
                <p class="text-sm uppercase tracking-[0.22em] text-slate-500">Patient trends</p>
                <h2 class="text-2xl font-semibold">Retention snapshot</h2>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-3xl bg-slate-50 p-5">
                    <p class="text-sm text-slate-500">New patients</p>
                    <p class="mt-2 text-3xl font-semibold"><?= e((string) $dashboard['metrics']['new_patients']) ?></p>
                </div>
                <div class="rounded-3xl bg-slate-50 p-5">
                    <p class="text-sm text-slate-500">Returning patients</p>
                    <p class="mt-2 text-3xl font-semibold"><?= e((string) $dashboard['metrics']['returning_patients']) ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mt-8">
    <div class="mb-4">
        <p class="text-sm uppercase tracking-[0.22em] text-slate-500">Upcoming list</p>
        <h2 class="text-2xl font-semibold">Next appointments</h2>
    </div>
    <div class="table-wrap">
        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50 text-slate-500">
            <tr>
                <th class="px-4 py-3 font-medium">Date</th>
                <th class="px-4 py-3 font-medium">Doctor</th>
                <th class="px-4 py-3 font-medium">Patient</th>
                <th class="px-4 py-3 font-medium">Phone</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
            <?php foreach ($dashboard['upcoming_appointments'] as $appointment): ?>
                <tr>
                    <td class="px-4 py-3"><?= e($appointment['appointment_date']) ?> <?= e($appointment['start_time']) ?></td>
                    <td class="px-4 py-3"><?= e($appointment['doctor_name']) ?></td>
                    <td class="px-4 py-3"><?= e(trim($appointment['first_name'] . ' ' . $appointment['last_name'])) ?></td>
                    <td class="px-4 py-3"><?= e($appointment['patient_phone']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
