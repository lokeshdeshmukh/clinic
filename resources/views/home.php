<section class="directory-shell">
    <div class="directory-hero">
        <div class="directory-hero__copy">
            <span class="eyebrow-pill">Built for clinic links shared on WhatsApp</span>
            <h3>One booking system for every clinic, with a patient experience that feels like a dedicated app.</h3>
            <p>Each clinic can use the same platform while getting its own mobile-first booking surface, doctor listing, patient login, reminders, and appointment flow.</p>
            <div class="hero-actions">
                <a href="<?= e(url('/clinics')) ?>" class="btn-primary">Browse clinics</a>
                <a href="<?= e(url('/clinic/register')) ?>" class="btn-secondary">Register clinic</a>
            </div>
        </div>
        <div class="directory-preview">
            <div class="preview-phone">
                <div class="preview-phone__top">
                    <span>Clinic microsite</span>
                    <span>v<?= e(config('app.build.version')) ?></span>
                </div>
                <div class="preview-phone__card preview-phone__card--brand">
                    <p class="preview-phone__label">Patient first</p>
                    <h2>Book a consultation in under a minute.</h2>
                    <p>Subdomain-based clinic pages, live slots, and login-aware booking journeys.</p>
                </div>
                <div class="preview-phone__grid">
                    <div class="preview-phone__card">
                        <strong>Live slots</strong>
                        <span>Doctor availability stays protected from double booking.</span>
                    </div>
                    <div class="preview-phone__card">
                        <strong>Clinic identity</strong>
                        <span>Each clinic gets its own logo, name, phone, and direct booking link.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="feature-band">
        <article class="feature-band__item">
            <span class="feature-band__number">01</span>
            <h3>Single codebase, clinic-specific UI</h3>
            <p>Use one deployment directory and route the visitor into the correct clinic experience based on the subdomain.</p>
        </article>
        <article class="feature-band__item">
            <span class="feature-band__number">02</span>
            <h3>Optimized for mobile booking</h3>
            <p>Patients can land from a WhatsApp link, sign in, pick a doctor, choose a slot, and confirm quickly.</p>
        </article>
        <article class="feature-band__item">
            <span class="feature-band__number">03</span>
            <h3>Reliable clinic operations</h3>
            <p>Doctor schedules, reminders, revenue tracking, and reports stay available in the admin dashboard.</p>
        </article>
    </div>
</section>

<section class="clinic-list-shell">
    <div class="section-headline">
        <div>
            <p class="section-kicker">Featured clinics</p>
            <h2>Open the direct booking experience</h2>
        </div>
        <span class="section-badge">Build <?= e(config('app.build.version')) ?></span>
    </div>

    <div class="clinic-list-grid">
        <?php foreach ($clinics as $clinic): ?>
            <article class="clinic-tile">
                <div class="clinic-tile__top">
                    <div class="clinic-tile__logo">
                        <?php if (!empty($clinic['logo_path'])): ?>
                            <img src="<?= e(url((string) $clinic['logo_path'])) ?>" alt="<?= e($clinic['name']) ?>" class="clinic-tile__logo-image">
                        <?php else: ?>
                            <?= e(strtoupper(substr((string) $clinic['name'], 0, 1))) ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h3><?= e($clinic['name']) ?></h3>
                        <p><?= e($clinic['address']) ?></p>
                    </div>
                </div>
                <div class="clinic-tile__meta">
                    <span><?= (int) $clinic['doctor_count'] ?> doctors</span>
                    <span><?= e($clinic['phone']) ?></span>
                </div>
                <div class="clinic-tile__actions">
                    <a href="<?= e(url('/clinics/' . $clinic['slug'])) ?>" class="btn-primary">Open clinic</a>
                    <a href="<?= e(url('/patient/register')) ?>" class="btn-secondary">Patient signup</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
