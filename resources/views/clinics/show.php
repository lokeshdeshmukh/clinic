<div class="panel">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <p class="text-sm uppercase tracking-[0.22em] text-brand-700">Clinic profile</p>
            <h1 class="mt-2 text-3xl font-semibold"><?= e($clinic['name']) ?></h1>
            <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-600"><?= e($clinic['address']) ?></p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm text-slate-600">
            <p>Email: <?= e($clinic['email']) ?></p>
            <p class="mt-2">Phone: <?= e($clinic['phone']) ?></p>
        </div>
    </div>
</div>

<section class="mt-8">
    <div class="mb-4 flex items-end justify-between">
        <div>
            <p class="text-sm uppercase tracking-[0.22em] text-slate-500">Doctors</p>
            <h2 class="text-2xl font-semibold">Choose a doctor</h2>
        </div>
    </div>
    <div class="grid gap-4 lg:grid-cols-2">
        <?php foreach ($doctors as $doctor): ?>
            <article class="panel">
                <div class="flex items-start gap-4">
                    <div class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-2xl bg-brand-100 font-semibold text-brand-700">
                        <?php if ($doctor['profile_photo_path'] !== ''): ?>
                            <img src="<?= e(url($doctor['profile_photo_path'])) ?>" alt="<?= e($doctor['name']) ?>" class="h-full w-full object-cover">
                        <?php else: ?>
                            <?= e($doctor['initial']) ?>
                        <?php endif; ?>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-xl font-semibold"><?= e($doctor['name']) ?></h3>
                        <p class="mt-1 text-sm text-brand-700"><?= e($doctor['specialization']) ?></p>
                        <p class="mt-3 text-sm text-slate-600"><?= e($doctor['bio_display']) ?></p>
                    </div>
                </div>
                <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-700">Fee: INR <?= e($doctor['fee_display']) ?></span>
                    <div class="flex gap-2">
                        <a href="<?= e(url('/doctors/' . $doctor['id'])) ?>" class="btn-secondary px-4 py-2">Profile</a>
                        <a href="<?= e(url('/doctors/' . $doctor['id'] . '/book')) ?>" class="btn-primary px-4 py-2">Book</a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
