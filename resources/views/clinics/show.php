<?php

$clinicLogo = !empty($clinic['logo_path']) ? url((string) $clinic['logo_path']) : null;
$isScoped = clinic_is_scoped();
$patientLoggedIn = \App\Core\Auth::check('patient');
$primaryBookHref = $doctors !== [] ? '/doctors/' . $doctors[0]['id'] . '/book' : '/patient/login';
$primaryBrowseHref = $doctors !== [] ? '#clinic-doctors' : '/patient/login';
$phoneHref = !empty($clinic['phone']) ? 'tel:' . preg_replace('/[^0-9+]/', '', (string) $clinic['phone']) : null;
?>
<section class="clinic-landing">
    <div class="clinic-hero">
        <div class="clinic-hero__copy">
            <span class="eyebrow-pill"><?= $isScoped ? 'Direct clinic booking link' : 'Clinic profile' ?></span>
            <h1><?= e($clinic['name']) ?></h1>
            <p class="clinic-hero__lead">Patients can sign in, choose a doctor, and confirm an appointment from this single clinic page without browsing the wider directory.</p>

            <div class="clinic-hero__stats">
                <div class="clinic-stat-card">
                    <strong><?= count($doctors) ?></strong>
                    <span>Available doctors</span>
                </div>
                <div class="clinic-stat-card">
                    <strong><?= e($clinic['phone']) ?></strong>
                    <span>Call for support</span>
                </div>
                <div class="clinic-stat-card">
                    <strong>24h</strong>
                    <span>Reminder schedule</span>
                </div>
            </div>

            <div class="hero-actions">
                <a href="<?= e(url($primaryBookHref)) ?>" class="btn-primary"><?= $patientLoggedIn ? 'Book now' : 'Login to book' ?></a>
                <a href="<?= e($primaryBrowseHref) ?>" class="btn-secondary">Choose doctor</a>
                <?php if ($phoneHref): ?>
                    <a href="<?= e($phoneHref) ?>" class="btn-secondary">Call clinic</a>
                <?php endif; ?>
            </div>
        </div>

        <aside class="clinic-hero__panel">
            <div class="clinic-badge-card">
                <div class="clinic-badge-card__brand">
                    <div class="clinic-badge-card__logo">
                        <?php if ($clinicLogo): ?>
                            <img src="<?= e($clinicLogo) ?>" alt="<?= e($clinic['name']) ?>" class="clinic-tile__logo-image">
                        <?php else: ?>
                            <?= e(strtoupper(substr((string) $clinic['name'], 0, 1))) ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <p class="clinic-badge-card__label">Online desk</p>
                        <h2><?= e($clinic['name']) ?></h2>
                    </div>
                </div>
                <div class="clinic-badge-card__details">
                    <p><?= e($clinic['address']) ?></p>
                    <p><?= e($clinic['email']) ?></p>
                    <p><?= e($clinic['phone']) ?></p>
                </div>
                <div class="clinic-badge-card__footer">
                    <span>Build <?= e(config('app.build.version')) ?></span>
                    <span><?= $patientLoggedIn ? 'Patient signed in' : 'Guest booking flow' ?></span>
                </div>
            </div>
        </aside>
    </div>

    <div class="trust-strip">
        <span>Designed for mobile booking links shared on WhatsApp</span>
        <span>Live slot availability</span>
        <span>Patient login, reschedule, and reminder support</span>
    </div>
</section>

<section class="doctor-section" id="clinic-doctors">
    <div class="section-headline">
        <div>
            <p class="section-kicker">Doctors</p>
            <h2>Choose who you want to consult</h2>
            <p class="section-copy">Each doctor card leads directly into the mobile booking flow with live availability.</p>
        </div>
    </div>

    <?php if ($doctors === []): ?>
        <div class="doctor-empty-state">
            <h3>Doctor profiles will appear here soon.</h3>
            <p>The clinic is active, but online doctor listings are not published yet.</p>
        </div>
    <?php else: ?>
        <div class="doctor-grid">
            <?php foreach ($doctors as $doctor): ?>
                <article class="doctor-card">
                    <div class="doctor-card__top">
                        <div class="doctor-card__avatar">
                            <?php if ($doctor['profile_photo_path'] !== ''): ?>
                                <img src="<?= e(url((string) $doctor['profile_photo_path'])) ?>" alt="<?= e($doctor['name']) ?>" class="doctor-card__avatar-image">
                            <?php else: ?>
                                <?= e($doctor['initial']) ?>
                            <?php endif; ?>
                        </div>
                        <div class="doctor-card__identity">
                            <h3><?= e($doctor['name']) ?></h3>
                            <p><?= e($doctor['specialization']) ?></p>
                        </div>
                        <span class="doctor-card__fee">INR <?= e($doctor['fee_display']) ?></span>
                    </div>

                    <p class="doctor-card__bio"><?= e($doctor['bio_display']) ?></p>

                    <div class="doctor-card__meta">
                        <span><?= e((string) $doctor['slot_duration_minutes']) ?> minute slots</span>
                        <span>Online booking available</span>
                    </div>

                    <div class="doctor-card__actions">
                        <a href="<?= e(url('/doctors/' . $doctor['id'])) ?>" class="btn-secondary">View profile</a>
                        <a href="<?= e(url('/doctors/' . $doctor['id'] . '/book')) ?>" class="btn-primary">Book appointment</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
