<?php
$patientName = trim(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? ''));
$recordTypeOptions = [
    'visit_note' => 'Visit note',
    'prescription' => 'Prescription',
    'lab_report' => 'Lab report',
    'discharge_summary' => 'Discharge summary',
    'other' => 'Other',
];
$selectedAppointment = (string) old('appointment_id', (string) $selectedAppointmentId);
?>
<section class="grid gap-6">
    <div class="panel">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.22em] text-slate-500">Patient profile</p>
                <h1 class="mt-2 text-3xl font-semibold"><?= e($patientName !== '' ? $patientName : 'Patient') ?></h1>
                <p class="mt-3 max-w-3xl text-slate-600">Manage completed-visit documents, upload prescriptions from camera, and keep a structured medical record history for this clinic.</p>
            </div>
            <div class="grid gap-2 text-sm text-slate-600">
                <span><?= e((string) ($patient['phone'] ?? 'No phone')) ?></span>
                <span><?= e((string) ($patient['email'] ?? 'No email')) ?></span>
                <span><?= e((string) ($patient['total_appointments'] ?? 0)) ?> visits • <?= e((string) ($patient['total_records'] ?? 0)) ?> saved records</span>
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[0.92fr_1.08fr]">
        <div class="panel">
            <p class="text-sm uppercase tracking-[0.22em] text-slate-500">New visit record</p>
            <h2 class="mt-2 text-2xl font-semibold">Upload after-visit documents</h2>
            <p class="mt-3 text-sm text-slate-500">Prescription photos can be captured directly from a mobile camera. If OCR is available, the app will suggest detected medications automatically. You can always paste prescription text manually for cleaner results.</p>
            <form method="post" action="<?= e(url('/admin/patients/' . $patient['id'] . '/records')) ?>" enctype="multipart/form-data" class="mt-6 grid gap-4">
                <?= csrf_field() ?>
                <div>
                    <label for="appointment_id">Completed appointment</label>
                    <select id="appointment_id" name="appointment_id">
                        <option value="">General clinic record</option>
                        <?php foreach ($completedAppointments as $appointment): ?>
                            <option value="<?= e((string) $appointment['id']) ?>" <?= selected($selectedAppointment, (string) $appointment['id']) ?>>
                                <?= e($appointment['appointment_date'] . ' • ' . $appointment['doctor_name'] . ' • ' . $appointment['start_time']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="record_type">Record type</label>
                        <select id="record_type" name="record_type" required>
                            <?php $selectedType = (string) old('record_type', 'prescription'); ?>
                            <?php foreach ($recordTypeOptions as $value => $label): ?>
                                <option value="<?= e($value) ?>" <?= selected($selectedType, $value) ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="recorded_at">Recorded at</label>
                        <input id="recorded_at" type="datetime-local" name="recorded_at" value="<?= e((string) old('recorded_at', date('Y-m-d\TH:i'))) ?>">
                    </div>
                </div>
                <div>
                    <label for="title">Title</label>
                    <input id="title" name="title" value="<?= e((string) old('title')) ?>" placeholder="Optional. Leave blank to auto-name from record type and visit date.">
                </div>
                <div>
                    <label for="visit_summary">Visit summary</label>
                    <textarea id="visit_summary" name="visit_summary" rows="4" placeholder="Symptoms, diagnosis, test outcomes, or any important notes"><?= e((string) old('visit_summary')) ?></textarea>
                </div>
                <div>
                    <label for="transcribed_text">Prescription text (optional)</label>
                    <textarea id="transcribed_text" name="transcribed_text" rows="5" placeholder="Paste or correct prescription text here if you want cleaner medication extraction"><?= e((string) old('transcribed_text')) ?></textarea>
                </div>
                <div>
                    <label for="document">Document or camera photo</label>
                    <input id="document" type="file" name="document" accept="image/*,application/pdf" capture="environment">
                    <p class="mt-2 text-xs text-slate-500">Accepted: JPG, PNG, WEBP, HEIC, HEIF, PDF. Files are stored privately and only available through authenticated clinic access.</p>
                    <?php if (!$ocrEnabled): ?>
                        <p class="mt-2 text-xs text-amber-700">Hosted OCR is currently off. If the server does not have Tesseract installed, paste the prescription text manually to extract medications.</p>
                    <?php endif; ?>
                </div>
                <button class="btn-primary" type="submit">Save visit record</button>
            </form>
        </div>

        <div class="panel">
            <p class="text-sm uppercase tracking-[0.22em] text-slate-500">Visit timeline</p>
            <h2 class="mt-2 text-2xl font-semibold">Clinic appointments for this patient</h2>
            <div class="mt-6 grid gap-3">
                <?php if ($appointments === []): ?>
                    <div class="rounded-3xl bg-slate-50 p-4 text-sm text-slate-500">No appointments found for this patient in the current clinic.</div>
                <?php endif; ?>
                <?php foreach ($appointments as $appointment): ?>
                    <div class="rounded-3xl border border-slate-200 bg-white p-4">
                        <div class="flex flex-wrap items-center gap-3">
                            <strong class="text-slate-900"><?= e($appointment['doctor_name']) ?></strong>
                            <span class="section-badge section-badge--soft"><?= e((string) $appointment['status']) ?></span>
                        </div>
                        <p class="mt-2 text-sm text-slate-500"><?= e($appointment['appointment_date']) ?> at <?= e($appointment['start_time']) ?> • <?= e($appointment['specialization']) ?></p>
                        <?php if ((string) $appointment['status'] === 'completed'): ?>
                            <div class="mt-3">
                                <a href="<?= e(url('/admin/patients/' . $patient['id'] . '?appointment=' . $appointment['id'])) ?>" class="btn-secondary">Attach record to this visit</a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="panel">
        <p class="text-sm uppercase tracking-[0.22em] text-slate-500">Saved records</p>
        <h2 class="mt-2 text-2xl font-semibold">Documents, notes, and extracted medications</h2>
        <div class="mt-6 grid gap-4">
            <?php if ($records === []): ?>
                <div class="rounded-3xl bg-slate-50 p-4 text-sm text-slate-500">No visit records saved yet for this patient.</div>
            <?php endif; ?>
            <?php foreach ($records as $record): ?>
                <article class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-xl font-semibold"><?= e($record['title']) ?></h3>
                                <span class="section-badge section-badge--soft"><?= e(ucwords(str_replace('_', ' ', (string) $record['record_type']))) ?></span>
                                <span class="section-badge" data-tone="<?= e($record['ocr_status'] === 'completed' || $record['ocr_status'] === 'manual' ? 'success' : ($record['ocr_status'] === 'failed' ? 'warning' : 'neutral')) ?>">
                                    <?= e(ucwords(str_replace('_', ' ', (string) $record['ocr_status']))) ?>
                                </span>
                            </div>
                            <p class="mt-2 text-sm text-slate-500">
                                <?= e(date('d M Y, g:i A', strtotime((string) $record['recorded_at']))) ?>
                                <?php if (!empty($record['doctor_name'])): ?>
                                    • <?= e($record['doctor_name']) ?>
                                <?php endif; ?>
                                <?php if (!empty($record['appointment_date'])): ?>
                                    • Visit <?= e($record['appointment_date']) ?> <?= e((string) ($record['start_time'] ?? '')) ?>
                                <?php endif; ?>
                            </p>
                            <?php if (!empty($record['visit_summary'])): ?>
                                <p class="mt-4 text-sm leading-7 text-slate-700"><?= nl2br(e((string) $record['visit_summary'])) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <?php if (!empty($record['document_path'])): ?>
                                <a href="<?= e(url('/admin/patient-records/' . $record['id'] . '/download')) ?>" class="btn-secondary">Open document</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($record['medications'])): ?>
                        <div class="mt-5">
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Suggested medication</p>
                            <div class="mt-3 grid gap-3 md:grid-cols-2">
                                <?php foreach ($record['medications'] as $medication): ?>
                                    <div class="rounded-2xl bg-slate-50 p-4">
                                        <strong class="block text-slate-900"><?= e((string) ($medication['name'] ?? 'Medication')) ?></strong>
                                        <?php if (!empty($medication['dosage'])): ?>
                                            <span class="mt-2 block text-sm text-slate-600"><?= e((string) $medication['dosage']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($medication['instructions'])): ?>
                                            <p class="mt-2 text-sm text-slate-500"><?= e((string) $medication['instructions']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($record['extracted_text'])): ?>
                        <div class="mt-5">
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Extracted text</p>
                            <pre class="mt-3 overflow-auto rounded-3xl bg-slate-50 p-4 text-sm leading-6 text-slate-700"><?= e((string) $record['extracted_text']) ?></pre>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($record['ocr_error'])): ?>
                        <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                            <?= e((string) $record['ocr_error']) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($record['document_path']) && $record['is_image']): ?>
                        <div class="mt-5">
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Image preview</p>
                            <img src="<?= e(url('/admin/patient-records/' . $record['id'] . '/download')) ?>" alt="<?= e($record['title']) ?>" class="mt-3 max-h-96 w-full rounded-3xl border border-slate-200 object-contain bg-slate-50">
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
