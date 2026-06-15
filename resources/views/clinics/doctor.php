<section class="grid gap-6 lg:grid-cols-[1.15fr_0.85fr]">
    <div class="panel">
        <div class="flex items-start gap-4">
            <div class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-3xl bg-brand-100 text-2xl font-semibold text-brand-700">
                <?php if (!empty($doctor['profile_photo_path'])): ?>
                    <img src="<?= e(url($doctor['profile_photo_path'])) ?>" alt="<?= e($doctor['name']) ?>" class="h-full w-full object-cover">
                <?php else: ?>
                    <?= e(substr((string) $doctor['name'], 0, 1)) ?>
                <?php endif; ?>
            </div>
            <div>
                <p class="text-sm uppercase tracking-[0.22em] text-brand-700"><?= e($doctor['clinic_name']) ?></p>
                <h1 class="mt-2 text-3xl font-semibold"><?= e($doctor['name']) ?></h1>
                <p class="mt-2 text-base font-medium text-slate-600"><?= e($doctor['specialization']) ?></p>
            </div>
        </div>
        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div class="rounded-3xl bg-slate-50 p-4">
                <p class="text-sm text-slate-500">Consultation fee</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">INR <?= e(number_format((float) $doctor['consultation_fee'], 2)) ?></p>
            </div>
            <div class="rounded-3xl bg-slate-50 p-4">
                <p class="text-sm text-slate-500">Slot duration</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900"><?= e((string) $doctor['slot_duration_minutes']) ?> min</p>
            </div>
        </div>
        <div class="mt-6">
            <h2 class="text-xl font-semibold">About the doctor</h2>
            <p class="mt-3 text-sm leading-7 text-slate-600"><?= e($doctor['bio'] ?: 'The clinic has not added a long profile yet.') ?></p>
        </div>
    </div>
    <div class="panel">
        <p class="text-sm uppercase tracking-[0.22em] text-slate-500">Book online</p>
        <h2 class="mt-2 text-2xl font-semibold">Ready to schedule?</h2>
        <p class="mt-3 text-sm leading-6 text-slate-600">View live slot availability before confirming. Patients can manage changes later from their dashboard.</p>
        <div class="mt-6 space-y-3 rounded-3xl bg-slate-50 p-5 text-sm text-slate-600">
            <p>Clinic: <?= e($doctor['clinic_name']) ?></p>
            <p>Address: <?= e($doctor['clinic_address']) ?></p>
            <p>Phone: <?= e($doctor['clinic_phone']) ?></p>
        </div>
        <a href="<?= e(url('/doctors/' . $doctor['id'] . '/book')) ?>" class="btn-primary mt-6 w-full">Book appointment</a>
    </div>
</section>
