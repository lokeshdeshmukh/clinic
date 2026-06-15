<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm uppercase tracking-[0.22em] text-slate-500">Public directory</p>
        <h1 class="text-3xl font-semibold">Clinics</h1>
    </div>
    <a href="<?= e(url('/patient/register')) ?>" class="btn-primary">Create Patient Account</a>
</div>

<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
    <?php foreach ($clinics as $clinic): ?>
        <article class="panel">
            <h2 class="text-xl font-semibold"><?= e($clinic['name']) ?></h2>
            <p class="mt-2 text-sm leading-6 text-slate-600"><?= e($clinic['address']) ?></p>
            <div class="mt-4 grid gap-2 text-sm text-slate-500">
                <p>Email: <?= e($clinic['email']) ?></p>
                <p>Phone: <?= e($clinic['phone']) ?></p>
                <p>Doctors: <?= (int) $clinic['doctor_count'] ?></p>
            </div>
            <div class="mt-6">
                <a href="<?= e(url('/clinics/' . $clinic['slug'])) ?>" class="btn-secondary w-full">Open clinic</a>
            </div>
        </article>
    <?php endforeach; ?>
</div>
