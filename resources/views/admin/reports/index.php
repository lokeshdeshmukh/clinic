<div class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
    <div>
        <p class="text-sm uppercase tracking-[0.22em] text-slate-500">Reports</p>
        <h1 class="text-3xl font-semibold">Operational exports</h1>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="<?= e(url('/admin/reports?type=appointments')) ?>" class="<?= $type === 'appointments' ? 'btn-primary' : 'btn-secondary' ?>">Appointments</a>
        <a href="<?= e(url('/admin/reports?type=revenue')) ?>" class="<?= $type === 'revenue' ? 'btn-primary' : 'btn-secondary' ?>">Revenue</a>
        <a href="<?= e(url('/admin/reports?type=doctor-performance')) ?>" class="<?= $type === 'doctor-performance' ? 'btn-primary' : 'btn-secondary' ?>">Doctor Performance</a>
    </div>
</div>

<div class="panel">
    <div class="flex flex-wrap gap-3">
        <a href="<?= e(url('/admin/reports/export?type=' . urlencode($type) . '&format=csv')) ?>" class="btn-secondary">Export CSV</a>
        <a href="<?= e(url('/admin/reports/export?type=' . urlencode($type) . '&format=excel')) ?>" class="btn-secondary">Export Excel</a>
        <a href="<?= e(url('/admin/reports/export?type=' . urlencode($type) . '&format=pdf')) ?>" class="btn-secondary">Export PDF</a>
    </div>
</div>

<div class="mt-6 table-wrap">
    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
        <thead class="bg-slate-50 text-slate-500">
        <tr>
            <?php if ($rows !== []): ?>
                <?php foreach (array_keys($rows[0]) as $header): ?>
                    <th class="px-4 py-3 font-medium"><?= e((string) $header) ?></th>
                <?php endforeach; ?>
            <?php endif; ?>
        </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
        <?php foreach ($rows as $row): ?>
            <tr>
                <?php foreach ($row as $value): ?>
                    <td class="px-4 py-3"><?= e((string) $value) ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
