<?php

$quickDates = [];
for ($offset = 0; $offset < 5; $offset++) {
    $timestamp = strtotime('+' . $offset . ' day');
    $quickDates[] = [
        'value' => date('Y-m-d', $timestamp),
        'weekday' => date('D', $timestamp),
        'day' => date('d M', $timestamp),
    ];
}

$scopedClinic = current_clinic();
$returnTarget = $redirectTo ?? '/doctors/' . $doctor['id'] . '/book';
$loginHref = '/patient/login?redirect_to=' . urlencode((string) $returnTarget);
$registerHref = '/patient/register?redirect_to=' . urlencode((string) $returnTarget);
$backHref = $scopedClinic ? '/' : '/doctors/' . $doctor['id'];
?>
<section class="booking-shell">
    <div class="section-headline">
        <div>
            <p class="section-kicker"><?= e($doctor['clinic_name']) ?></p>
            <h1>Book with <?= e($doctor['name']) ?></h1>
            <p class="section-copy"><?= e($doctor['specialization']) ?> · INR <?= e(number_format((float) $doctor['consultation_fee'], 2)) ?> consultation fee.</p>
        </div>
        <a href="<?= e(url($backHref)) ?>" class="btn-secondary">Back</a>
    </div>

    <div class="booking-grid">
        <aside class="booking-sidebar">
            <div class="booking-doctor-card">
                <div class="doctor-card__top">
                    <div class="doctor-card__avatar">
                        <?php if (!empty($doctor['profile_photo_path'])): ?>
                            <img src="<?= e(url((string) $doctor['profile_photo_path'])) ?>" alt="<?= e($doctor['name']) ?>" class="doctor-card__avatar-image">
                        <?php else: ?>
                            <?= e(strtoupper(substr((string) $doctor['name'], 0, 1))) ?>
                        <?php endif; ?>
                    </div>
                    <div class="doctor-card__identity">
                        <h2><?= e($doctor['name']) ?></h2>
                        <p><?= e($doctor['specialization']) ?></p>
                    </div>
                </div>

                <div class="booking-summary-list">
                    <div>
                        <strong>Clinic</strong>
                        <span><?= e($doctor['clinic_name']) ?></span>
                    </div>
                    <div>
                        <strong>Address</strong>
                        <span><?= e($doctor['clinic_address']) ?></span>
                    </div>
                    <div>
                        <strong>Phone</strong>
                        <span><?= e($doctor['clinic_phone']) ?></span>
                    </div>
                    <div>
                        <strong>Slot duration</strong>
                        <span><?= e((string) $doctor['slot_duration_minutes']) ?> minutes</span>
                    </div>
                </div>
            </div>

            <div class="booking-status-card">
                <p class="booking-status-card__label">Booking status</p>
                <?php if ($patientLoggedIn): ?>
                    <h3>Ready to confirm</h3>
                    <p>You are signed in, so once you choose a date and slot you can confirm immediately.</p>
                <?php else: ?>
                    <h3>Sign in before checkout</h3>
                    <p>This clinic link stays on the same doctor after login, so patients can come from WhatsApp and continue booking.</p>
                    <div class="booking-status-card__actions">
                        <a href="<?= e(url($loginHref)) ?>" class="btn-primary">Login</a>
                        <a href="<?= e(url($registerHref)) ?>" class="btn-secondary">Create account</a>
                    </div>
                <?php endif; ?>
            </div>
        </aside>

        <div class="booking-panel">
            <div class="booking-panel__header">
                <div>
                    <p class="section-kicker">Step 1</p>
                    <h2>Select date</h2>
                </div>
                <span class="section-badge">Live availability</span>
            </div>

            <form method="post" action="<?= e(url('/doctors/' . $doctor['id'] . '/book')) ?>" class="booking-form">
                <?= csrf_field() ?>
                <input type="hidden" name="doctor_id" value="<?= e((string) $doctor['id']) ?>" data-slot-doctor>

                <div class="quick-date-grid">
                    <?php foreach ($quickDates as $index => $quickDate): ?>
                        <button
                            type="button"
                            class="quick-date-pill<?= $index === 0 ? ' is-active' : '' ?>"
                            data-quick-date="<?= e($quickDate['value']) ?>"
                        >
                            <span><?= e($quickDate['weekday']) ?></span>
                            <strong><?= e($quickDate['day']) ?></strong>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="booking-form__field">
                    <label for="appointment_date">Choose another date</label>
                    <input
                        id="appointment_date"
                        type="date"
                        name="appointment_date"
                        min="<?= e(date('Y-m-d')) ?>"
                        value="<?= e($quickDates[0]['value']) ?>"
                        data-slot-date
                        required
                    >
                </div>

                <div class="booking-panel__header">
                    <div>
                        <p class="section-kicker">Step 2</p>
                        <h2>Select time</h2>
                    </div>
                    <span class="section-badge section-badge--soft" data-selected-slot-label>Select a slot</span>
                </div>

                <div data-slot-results class="slot-grid"></div>
                <input type="hidden" name="start_time" data-slot-input required>

                <div class="booking-form__field">
                    <label for="notes">Notes for the clinic</label>
                    <textarea id="notes" name="notes" rows="4" placeholder="Optional message, symptoms, or arrival note"></textarea>
                </div>

                <div class="booking-sticky-bar">
                    <div class="booking-sticky-bar__summary">
                        <span data-selected-date-label><?= e(date('D, d M', strtotime($quickDates[0]['value']))) ?></span>
                        <strong data-selected-time-label>Select time</strong>
                    </div>

                    <?php if ($patientLoggedIn): ?>
                        <button class="btn-primary booking-sticky-bar__button" type="submit">Confirm appointment</button>
                    <?php else: ?>
                        <a href="<?= e(url($loginHref)) ?>" class="btn-primary booking-sticky-bar__button">Login to confirm</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</section>
