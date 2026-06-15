<?php

$scopedClinic = current_clinic();
$backHref = $scopedClinic ? '/' : '/clinics/' . $doctor['clinic_slug'];
?>
<section class="doctor-profile-shell">
    <div class="section-headline">
        <div>
            <p class="section-kicker"><?= e($doctor['clinic_name']) ?></p>
            <h1><?= e($doctor['name']) ?></h1>
            <p class="section-copy"><?= e($doctor['specialization']) ?> consultation with direct mobile booking and live availability.</p>
        </div>
        <a href="<?= e(url($backHref)) ?>" class="btn-secondary">Back to clinic</a>
    </div>

    <div class="doctor-profile-card">
        <div class="doctor-profile-card__media">
            <div class="doctor-profile-card__portrait">
                <?php if (!empty($doctor['profile_photo_path'])): ?>
                    <img src="<?= e(url((string) $doctor['profile_photo_path'])) ?>" alt="<?= e($doctor['name']) ?>" class="doctor-profile-card__portrait-image">
                <?php else: ?>
                    <span><?= e(strtoupper(substr((string) $doctor['name'], 0, 1))) ?></span>
                <?php endif; ?>
            </div>
            <div class="doctor-profile-card__chips">
                <span>INR <?= e(number_format((float) $doctor['consultation_fee'], 2)) ?></span>
                <span><?= e((string) $doctor['slot_duration_minutes']) ?> min slots</span>
                <span>Online booking</span>
            </div>
        </div>

        <div class="doctor-profile-card__content">
            <div class="doctor-profile-card__summary">
                <h2><?= e($doctor['name']) ?></h2>
                <p><?= e($doctor['specialization']) ?></p>
            </div>

            <div class="doctor-profile-card__about">
                <h3>About this doctor</h3>
                <p><?= e($doctor['bio'] ?: 'The clinic has not added a long profile yet, but online appointment booking is available.') ?></p>
            </div>

            <div class="doctor-profile-card__clinic">
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
            </div>

            <div class="doctor-profile-card__actions">
                <a href="<?= e(url('/doctors/' . $doctor['id'] . '/book')) ?>" class="btn-primary">Book appointment</a>
                <a href="<?= e(url($backHref)) ?>" class="btn-secondary">Browse more doctors</a>
            </div>
        </div>
    </div>
</section>
