<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm uppercase tracking-[0.22em] text-slate-500">Doctor management</p>
        <h1 class="text-3xl font-semibold">Doctors</h1>
    </div>
    <a href="<?= e(url('/admin/doctors/create')) ?>" class="btn-primary">Add doctor</a>
</div>

<div class="table-wrap">
    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
        <thead class="bg-slate-50 text-slate-500">
        <tr>
            <th class="px-4 py-3 font-medium">Doctor</th>
            <th class="px-4 py-3 font-medium">Specialization</th>
            <th class="px-4 py-3 font-medium">Fee</th>
            <th class="px-4 py-3 font-medium">Slot</th>
            <th class="px-4 py-3 font-medium">Status</th>
            <th class="px-4 py-3 font-medium">Actions</th>
        </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
        <?php foreach ($doctors as $doctor): ?>
            <tr>
                <td class="px-4 py-3 font-medium"><?= e($doctor['name']) ?></td>
                <td class="px-4 py-3"><?= e($doctor['specialization']) ?></td>
                <td class="px-4 py-3">INR <?= e(number_format((float) $doctor['consultation_fee'], 2)) ?></td>
                <td class="px-4 py-3"><?= e((string) $doctor['slot_duration_minutes']) ?> min</td>
                <td class="px-4 py-3 capitalize"><?= e($doctor['status']) ?></td>
                <td class="px-4 py-3">
                    <div class="flex flex-wrap gap-2">
                        <a href="<?= e(url('/admin/doctors/' . $doctor['id'] . '/edit')) ?>" class="btn-secondary px-4 py-2">Edit</a>
                        <form method="post" action="<?= e(url('/admin/doctors/' . $doctor['id'] . '/delete')) ?>">
                            <?= csrf_field() ?>
                            <button class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
