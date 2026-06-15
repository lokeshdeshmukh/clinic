<section class="grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
    <div class="panel">
        <p class="text-sm uppercase tracking-[0.22em] text-brand-700"><?= e($doctor['clinic_name']) ?></p>
        <h1 class="mt-2 text-3xl font-semibold">Book with <?= e($doctor['name']) ?></h1>
        <p class="mt-3 text-sm leading-6 text-slate-600"><?= e($doctor['specialization']) ?> with a consultation fee of INR <?= e(number_format((float) $doctor['consultation_fee'], 2)) ?>.</p>
        <div class="mt-6 rounded-3xl bg-slate-50 p-5 text-sm text-slate-600">
            <p>Clinic: <?= e($doctor['clinic_name']) ?></p>
            <p class="mt-2">Address: <?= e($doctor['clinic_address']) ?></p>
            <p class="mt-2">Phone: <?= e($doctor['clinic_phone']) ?></p>
            <p class="mt-2">Slot duration: <?= e((string) $doctor['slot_duration_minutes']) ?> minutes</p>
        </div>
    </div>
    <div class="panel">
        <p class="text-sm uppercase tracking-[0.22em] text-slate-500">Live availability</p>
        <h2 class="mt-2 text-2xl font-semibold">Choose your time slot</h2>
        <?php if (!$patientLoggedIn): ?>
            <div class="mt-5 rounded-3xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
                Please <a href="<?= e(url('/patient/login')) ?>" class="font-semibold underline">sign in as a patient</a> before confirming the booking.
            </div>
        <?php endif; ?>
        <form method="post" action="<?= e(url('/doctors/' . $doctor['id'] . '/book')) ?>" class="mt-6 grid gap-4">
            <?= csrf_field() ?>
            <div>
                <label for="doctor_id">Doctor</label>
                <select id="doctor_id" name="doctor_id" data-slot-doctor>
                    <option value="<?= e((string) $doctor['id']) ?>"><?= e($doctor['name']) ?> (<?= e($doctor['specialization']) ?>)</option>
                </select>
            </div>
            <div>
                <label for="appointment_date">Appointment date</label>
                <input id="appointment_date" type="date" name="appointment_date" min="<?= e(date('Y-m-d')) ?>" data-slot-date required>
            </div>
            <div>
                <label>Available slots</label>
                <div data-slot-results class="grid gap-3 sm:grid-cols-2"></div>
                <input type="hidden" name="start_time" data-slot-input required>
            </div>
            <div>
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" rows="3" placeholder="Optional note for the clinic"></textarea>
            </div>
            <button class="btn-primary w-full" <?= $patientLoggedIn ? '' : 'disabled' ?>>Confirm appointment</button>
        </form>
    </div>
</section>
