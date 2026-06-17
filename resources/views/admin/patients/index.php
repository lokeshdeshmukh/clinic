<section class="grid gap-6">
    <div class="panel">
        <p class="text-sm uppercase tracking-[0.22em] text-slate-500">Patient management</p>
        <h1 class="mt-2 text-3xl font-semibold">Patients and visit records</h1>
        <p class="mt-3 max-w-3xl text-slate-600">Open any patient to upload post-visit documents, capture a prescription photo, and save extracted medication suggestions against the clinic timeline.</p>
    </div>

    <?php if ($patients === []): ?>
        <div class="panel text-sm text-slate-500">No patients found yet. Once bookings start coming in, each patient will appear here automatically.</div>
    <?php else: ?>
        <div class="grid gap-4 lg:grid-cols-2">
            <?php foreach ($patients as $patient): ?>
                <article class="panel">
                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div>
                            <p class="text-sm uppercase tracking-[0.18em] text-slate-500">Patient</p>
                            <h2 class="mt-2 text-2xl font-semibold"><?= e(trim(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? ''))) ?></h2>
                            <div class="mt-3 grid gap-2 text-sm text-slate-600">
                                <span><?= e((string) ($patient['phone'] ?? 'No phone')) ?></span>
                                <span><?= e((string) ($patient['email'] ?? 'No email')) ?></span>
                                <span>Last visit: <?= e((string) ($patient['last_visit_date'] ?? 'Not available')) ?></span>
                            </div>
                        </div>
                        <div class="grid gap-2 text-right text-sm">
                            <span class="section-badge section-badge--soft"><?= e((string) ($patient['total_appointments'] ?? 0)) ?> bookings</span>
                            <span class="section-badge section-badge--soft"><?= e((string) ($patient['completed_visits'] ?? 0)) ?> completed</span>
                            <span class="section-badge section-badge--soft"><?= e((string) ($patient['total_records'] ?? 0)) ?> records</span>
                        </div>
                    </div>
                    <div class="mt-5">
                        <a href="<?= e(url('/admin/patients/' . $patient['id'])) ?>" class="btn-primary">Open patient record</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
