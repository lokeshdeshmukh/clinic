<?php

$scopedClinic = current_clinic();
$bookAnotherHref = $scopedClinic ? '/' : '/clinics';
$formatAppointmentDate = static function (?string $value): string {
    if (!$value) {
        return 'Date pending';
    }

    $timestamp = strtotime($value);

    return $timestamp ? date('D, d M Y', $timestamp) : $value;
};
$formatAppointmentTime = static function (?string $value): string {
    if (!$value) {
        return 'Time pending';
    }

    $timestamp = strtotime($value);

    return $timestamp ? date('g:i A', $timestamp) : $value;
};
$nextAppointment = $upcomingAppointments[0] ?? null;
$remainingUpcomingAppointments = array_slice($upcomingAppointments, 1);
$renderUpcomingAppointment = static function (array $appointment) use ($formatAppointmentDate, $formatAppointmentTime): string {
    $appointmentDateValue = (string) ($appointment['appointment_date'] ?? '');
    $appointmentTimeValue = (string) ($appointment['start_time'] ?? '');
    $appointmentDateLabel = $formatAppointmentDate($appointmentDateValue);
    $appointmentTimeLabel = $formatAppointmentTime($appointmentTimeValue);
    $appointmentTimeInput = $appointmentTimeValue !== '' ? substr($appointmentTimeValue, 0, 5) : '';

    ob_start();
    ?>
    <article class="appointment-card">
        <div class="appointment-card__top">
            <div>
                <h3><?= e($appointment['doctor_name']) ?></h3>
                <p><?= e($appointment['specialization']) ?></p>
            </div>
            <div class="appointment-card__schedule">
                <span class="section-badge"><?= e($appointmentDateLabel) ?></span>
                <strong class="appointment-card__time"><?= e($appointmentTimeLabel) ?></strong>
            </div>
        </div>

        <div class="appointment-card__meta">
            <span><?= e($appointment['clinic_name']) ?></span>
            <span><?= e($appointment['clinic_phone']) ?></span>
        </div>

        <div class="appointment-card__actions appointment-card__actions--compact">
            <details class="appointment-card__reschedule">
                <summary class="appointment-card__summary-button">Reschedule</summary>
                <form method="post" action="<?= e(url('/patient/appointments/' . $appointment['id'] . '/reschedule')) ?>" class="appointment-card__form appointment-card__form--compact">
                    <?= csrf_field() ?>
                    <div class="form-grid-2">
                        <input type="date" name="appointment_date" min="<?= e(date('Y-m-d')) ?>" value="<?= e($appointmentDateValue) ?>" required>
                        <input type="time" name="start_time" value="<?= e($appointmentTimeInput) ?>" required>
                    </div>
                    <button class="btn-secondary w-full" type="submit">Update slot</button>
                </form>
            </details>

            <form method="post" action="<?= e(url('/patient/appointments/' . $appointment['id'] . '/cancel')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="reason" value="Cancelled from patient dashboard">
                <button class="danger-button danger-button--compact w-full" type="submit">Cancel appointment</button>
            </form>
        </div>
    </article>
    <?php

    return (string) ob_get_clean();
};
?>
<section class="dashboard-shell dashboard-shell--patient">
    <div class="section-headline section-headline--compact dashboard-shell__heading">
        <div>
            <p class="section-kicker"><?= $nextAppointment ? 'Upcoming next visit' : 'Patient dashboard' ?></p>
            <h1><?= $nextAppointment ? 'Your next visit' : 'Your bookings' ?></h1>
        </div>
    </div>

    <div class="appointment-stack">
        <?php if ($nextAppointment === null): ?>
            <div class="doctor-empty-state">
                <h3>No upcoming appointments yet.</h3>
                <p>Once you confirm a booking it will appear here with clinic details and reschedule actions.</p>
            </div>
        <?php else: ?>
            <?= $renderUpcomingAppointment($nextAppointment) ?>
        <?php endif; ?>

        <a href="<?= e(url($bookAnotherHref)) ?>" class="btn-primary dashboard-shell__cta">Book another</a>
    </div>

    <?php if ($remainingUpcomingAppointments !== []): ?>
        <div class="appointment-stack">
            <div class="section-headline section-headline--compact">
                <div>
                    <p class="section-kicker">More upcoming</p>
                    <h2>Other booked visits</h2>
                </div>
            </div>

            <?php foreach ($remainingUpcomingAppointments as $appointment): ?>
                <?= $renderUpcomingAppointment($appointment) ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="appointment-stack">
        <div class="section-headline section-headline--compact">
            <div>
                <p class="section-kicker">History</p>
                <h2>Past and changed appointments</h2>
            </div>
        </div>

        <?php if ($historyAppointments === []): ?>
            <div class="doctor-empty-state">
                <h3>No past appointments yet.</h3>
                <p>Completed, cancelled, and older visits will appear in this history list.</p>
            </div>
        <?php endif; ?>

        <div class="history-grid">
            <?php foreach ($historyAppointments as $appointment): ?>
                <?php
                $historyDateLabel = $formatAppointmentDate((string) ($appointment['appointment_date'] ?? ''));
                $historyTimeLabel = $formatAppointmentTime((string) ($appointment['start_time'] ?? ''));
                ?>
                <article class="history-card">
                    <div class="history-card__top">
                        <div>
                            <h3><?= e($appointment['doctor_name']) ?></h3>
                            <p><?= e($appointment['clinic_name']) ?></p>
                        </div>
                        <span class="history-card__status"><?= e(ucwords(str_replace('_', ' ', (string) $appointment['status']))) ?></span>
                    </div>
                    <div class="history-card__meta">
                        <span><?= e($historyDateLabel) ?></span>
                        <span><?= e($historyTimeLabel) ?></span>
                        <span><?= e($appointment['specialization']) ?></span>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
