<section class="clinic-list-shell">
    <div class="section-headline">
        <div>
            <p class="section-kicker">Public directory</p>
            <h1>Choose a clinic</h1>
            <p class="section-copy">Open a clinic page to view doctors, see consultation fees, and start booking from a mobile-friendly flow.</p>
        </div>
        <a href="<?= e(url('/patient/register')) ?>" class="btn-primary">Create patient account</a>
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
                        <h2><?= e($clinic['name']) ?></h2>
                        <p><?= e($clinic['address']) ?></p>
                    </div>
                </div>
                <div class="clinic-tile__meta">
                    <span><?= e($clinic['email']) ?></span>
                    <span><?= e($clinic['phone']) ?></span>
                    <span><?= (int) $clinic['doctor_count'] ?> doctors</span>
                </div>
                <div class="clinic-tile__actions">
                    <a href="<?= e(url('/clinics/' . $clinic['slug'])) ?>" class="btn-primary">Open clinic</a>
                    <a href="<?= e(url('/patient/login')) ?>" class="btn-secondary">Patient login</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
