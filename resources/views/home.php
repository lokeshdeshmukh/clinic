<section class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
    <div class="panel overflow-hidden bg-slate-950 text-white">
        <div class="absolute"></div>
        <div class="mb-4 flex flex-wrap items-center gap-3">
            <p class="text-sm uppercase tracking-[0.28em] text-slate-300">Mobile-first clinic booking</p>
            <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-cyan-100">
                Version <?= e(config('app.build.version')) ?>
            </span>
        </div>
        <h1 class="max-w-2xl text-4xl font-semibold text-white sm:text-5xl">Appointment scheduling that feels simple for patients and reliable for clinics.</h1>
        <p class="mt-5 max-w-2xl text-base leading-7 text-slate-300">ClinicFlow helps small clinics manage doctors, availability, appointments, reminders, revenue, and reporting from one responsive workflow.</p>
        <p class="mt-4 text-sm text-slate-400">
            Live build <?= e(config('app.build.version')) ?>
            <?php if ((string) config('app.build.commit') !== ''): ?>
                <span class="text-slate-500">/</span> commit <?= e(config('app.build.commit')) ?>
            <?php endif; ?>
            <?php if ((string) config('app.build.deployed_at') !== ''): ?>
                <span class="text-slate-500">/</span> deployed <?= e((string) config('app.build.deployed_at')) ?>
            <?php endif; ?>
        </p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="<?= e(url('/clinics')) ?>" class="btn-primary">Browse Clinics</a>
            <a href="<?= e(url('/clinic/register')) ?>" class="btn-secondary border-white/20 bg-white/10 text-white hover:bg-white/20">Register Clinic</a>
        </div>
        <div class="mt-10 grid gap-4 sm:grid-cols-3">
            <div class="rounded-3xl border border-white/10 bg-white/5 p-4">
                <p class="text-3xl font-semibold text-white">24h</p>
                <p class="mt-2 text-sm text-slate-300">Automated reminder flow</p>
            </div>
            <div class="rounded-3xl border border-white/10 bg-white/5 p-4">
                <p class="text-3xl font-semibold text-white">0</p>
                <p class="mt-2 text-sm text-slate-300">Double-booking tolerance with guarded slot keys</p>
            </div>
            <div class="rounded-3xl border border-white/10 bg-white/5 p-4">
                <p class="text-3xl font-semibold text-white">3</p>
                <p class="mt-2 text-sm text-slate-300">Export formats: PDF, Excel, CSV</p>
            </div>
        </div>
    </div>
    <div class="grid gap-4">
        <div class="panel">
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-brand-700">For clinics</p>
            <h2 class="mt-3 text-2xl font-semibold">Admin dashboard, doctor schedules, revenue and reports.</h2>
            <p class="mt-3 text-sm leading-6 text-slate-600">Manage weekly schedules, date overrides, holidays, blocked slots, and appointment status changes from a single workflow.</p>
        </div>
        <div class="panel">
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-accent-600">For patients</p>
            <h2 class="mt-3 text-2xl font-semibold">Search clinics, view live slots, and book online.</h2>
            <p class="mt-3 text-sm leading-6 text-slate-600">Patients can register, book, reschedule, cancel, and review their appointment history across devices.</p>
        </div>
    </div>
</section>

<section class="mt-8">
    <div class="mb-4 flex items-end justify-between">
        <div>
            <p class="text-sm uppercase tracking-[0.22em] text-slate-500">Featured clinics</p>
            <h2 class="text-2xl font-semibold">Start by selecting a clinic</h2>
        </div>
        <a href="<?= e(url('/clinics')) ?>" class="text-sm font-semibold text-brand-700">View all clinics</a>
    </div>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <?php foreach ($clinics as $clinic): ?>
            <article class="panel">
                <div class="flex items-start gap-4">
                    <div class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-2xl bg-brand-100 text-xl font-semibold text-brand-700">
                        <?php if (!empty($clinic['logo_path'])): ?>
                            <img src="<?= e(url($clinic['logo_path'])) ?>" alt="<?= e($clinic['name']) ?>" class="h-full w-full object-cover">
                        <?php else: ?>
                            <?= e(substr((string) $clinic['name'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-lg font-semibold"><?= e($clinic['name']) ?></h3>
                        <p class="mt-1 text-sm text-slate-600"><?= e($clinic['address']) ?></p>
                        <p class="mt-2 text-sm text-slate-500"><?= (int) $clinic['doctor_count'] ?> doctors</p>
                    </div>
                </div>
                <div class="mt-5 flex items-center justify-between">
                    <span class="text-sm text-slate-500"><?= e($clinic['phone']) ?></span>
                    <a href="<?= e(url('/clinics/' . $clinic['slug'])) ?>" class="btn-secondary px-4 py-2">View doctors</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
