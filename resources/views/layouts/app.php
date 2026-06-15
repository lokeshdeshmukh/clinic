<?php

use App\Core\Auth;
use App\Core\Session;

$currentUser = Auth::user();
$guard = Auth::guard();
$flashSuccess = Session::getFlash('success');
$flashError = Session::getFlash('error');
$scopedClinic = current_clinic();
$isScoped = $scopedClinic !== null;
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$isManagementSurface = str_starts_with($requestPath, '/admin')
    || str_starts_with($requestPath, '/super-admin')
    || str_starts_with($requestPath, '/clinic/');
$isPatientFacingScoped = $isScoped && !$isManagementSurface && $guard !== 'clinic' && $guard !== 'super_admin';
$phoneHref = $isScoped && !empty($scopedClinic['phone'])
    ? 'tel:' . preg_replace('/[^0-9+]/', '', (string) $scopedClinic['phone'])
    : null;
$brandInitial = $isScoped
    ? strtoupper(substr((string) $scopedClinic['name'], 0, 1))
    : 'H';
$currentUserIdentity = $currentUser['email'] ?? $currentUser['phone'] ?? $currentUser['name'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? config('app.name')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/experience.css')) ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
    <script defer src="<?= e(asset('js/app.js')) ?>"></script>
</head>
<body class="site-body<?= $isScoped ? ' is-clinic-scoped' : '' ?>" data-base-url="<?= e(url('')) ?>">
    <div class="site-backdrop"></div>
    <header class="site-header-shell">
        <?php if ($isPatientFacingScoped): ?>
            <div class="site-mobile-topbar">
                <button
                    type="button"
                    class="site-mobile-topbar__menu"
                    data-drawer-toggle
                    aria-expanded="false"
                    aria-controls="siteScopedDrawer"
                    aria-label="Open navigation menu"
                >
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <a href="<?= e(url('/')) ?>" class="site-mobile-topbar__brand">
                    <span class="site-mobile-topbar__eyebrow">Clinic booking</span>
                    <strong><?= e((string) $scopedClinic['name']) ?></strong>
                </a>
                <?php if ($guard === 'patient'): ?>
                    <a href="<?= e(url('/patient/dashboard')) ?>" class="site-mobile-topbar__action">My visits</a>
                <?php elseif ($phoneHref): ?>
                    <a href="<?= e($phoneHref) ?>" class="site-mobile-topbar__action">Call</a>
                <?php else: ?>
                    <a href="<?= e(url('/patient/login')) ?>" class="site-mobile-topbar__action">Login</a>
                <?php endif; ?>
            </div>

            <div class="site-scoped-drawer" id="siteScopedDrawer" data-drawer hidden>
                <button type="button" class="site-scoped-drawer__backdrop" data-drawer-close aria-label="Close navigation menu"></button>
                <aside class="site-scoped-drawer__panel">
                    <div class="site-scoped-drawer__head">
                        <span class="site-brand__mark site-brand__mark--drawer">
                            <?php if (!empty($scopedClinic['logo_path'])): ?>
                                <img src="<?= e(url((string) $scopedClinic['logo_path'])) ?>" alt="<?= e($scopedClinic['name']) ?>" class="site-brand__logo">
                            <?php else: ?>
                                <?= e($brandInitial) ?>
                            <?php endif; ?>
                        </span>
                        <div>
                            <p class="site-mobile-topbar__eyebrow">Clinic booking</p>
                            <strong><?= e((string) $scopedClinic['name']) ?></strong>
                            <p class="site-subheader__meta"><?= e((string) $scopedClinic['phone']) ?></p>
                        </div>
                    </div>
                    <nav class="site-scoped-drawer__nav">
                        <a href="<?= e(url('/')) ?>">Book appointment</a>
                        <a href="<?= e(url('/patient/dashboard')) ?>">My bookings</a>
                        <?php if ($phoneHref): ?>
                            <a href="<?= e($phoneHref) ?>">Call clinic</a>
                        <?php endif; ?>
                        <?php if ($guard === 'patient'): ?>
                            <form method="post" action="<?= e(url('/patient/logout')) ?>">
                                <?= csrf_field() ?>
                                <button type="submit">Logout</button>
                            </form>
                        <?php else: ?>
                            <a href="<?= e(url('/patient/login')) ?>">Patient login</a>
                        <?php endif; ?>
                        <a href="<?= e(url('/clinic/login')) ?>">Clinic admin</a>
                    </nav>
                    <div class="site-scoped-drawer__footer">
                        <span>Build <?= e(config('app.build.version')) ?></span>
                        <span><?= e((string) $scopedClinic['address']) ?></span>
                    </div>
                </aside>
            </div>
        <?php else: ?>
            <div class="site-header">
                <a href="<?= e(url('/')) ?>" class="site-brand">
                    <span class="site-brand__mark">
                        <?php if ($isScoped && !empty($scopedClinic['logo_path'])): ?>
                            <img src="<?= e(url((string) $scopedClinic['logo_path'])) ?>" alt="<?= e($scopedClinic['name']) ?>" class="site-brand__logo">
                        <?php else: ?>
                            <?= e($brandInitial) ?>
                        <?php endif; ?>
                    </span>
                    <span class="site-brand__copy">
                        <span class="site-brand__eyebrow"><?= $isScoped ? 'Clinic booking desk' : 'Huviena clinics' ?></span>
                        <span class="site-brand__name"><?= e($isScoped ? (string) $scopedClinic['name'] : (string) config('app.name')) ?></span>
                    </span>
                </a>

                <nav class="site-nav">
                    <?php if ($isScoped): ?>
                        <?php if ($guard === 'clinic'): ?>
                            <a class="site-nav__link" href="<?= e(url('/admin/dashboard')) ?>">Dashboard</a>
                            <form method="post" action="<?= e(url('/clinic/logout')) ?>" class="inline">
                                <?= csrf_field() ?>
                                <button class="site-nav__button" type="submit">Logout</button>
                            </form>
                        <?php elseif ($guard === 'super_admin'): ?>
                            <a class="site-nav__link" href="<?= e(url('/super-admin/dashboard')) ?>">Platform dashboard</a>
                            <form method="post" action="<?= e(url('/super-admin/logout')) ?>" class="inline">
                                <?= csrf_field() ?>
                                <button class="site-nav__button" type="submit">Logout</button>
                            </form>
                        <?php elseif ($guard === 'patient'): ?>
                            <a class="site-nav__link" href="<?= e(url('/patient/dashboard')) ?>">My bookings</a>
                            <form method="post" action="<?= e(url('/patient/logout')) ?>" class="inline">
                                <?= csrf_field() ?>
                                <button class="site-nav__button" type="submit">Logout</button>
                            </form>
                        <?php else: ?>
                            <?php if ($phoneHref): ?>
                                <a class="site-nav__link" href="<?= e($phoneHref) ?>">Call clinic</a>
                            <?php endif; ?>
                            <a class="site-nav__button" href="<?= e(url('/patient/login')) ?>">Patient login</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a class="site-nav__link" href="<?= e(url('/clinics')) ?>">Clinics</a>
                        <?php if ($guard === 'clinic'): ?>
                            <a class="site-nav__link" href="<?= e(url('/admin/dashboard')) ?>">Dashboard</a>
                            <form method="post" action="<?= e(url('/clinic/logout')) ?>" class="inline">
                                <?= csrf_field() ?>
                                <button class="site-nav__button" type="submit">Logout</button>
                            </form>
                        <?php elseif ($guard === 'super_admin'): ?>
                            <a class="site-nav__link" href="<?= e(url('/super-admin/dashboard')) ?>">Platform dashboard</a>
                            <form method="post" action="<?= e(url('/super-admin/logout')) ?>" class="inline">
                                <?= csrf_field() ?>
                                <button class="site-nav__button" type="submit">Logout</button>
                            </form>
                        <?php elseif ($guard === 'patient'): ?>
                            <a class="site-nav__link" href="<?= e(url('/patient/dashboard')) ?>">My bookings</a>
                            <form method="post" action="<?= e(url('/patient/logout')) ?>" class="inline">
                                <?= csrf_field() ?>
                                <button class="site-nav__button" type="submit">Logout</button>
                            </form>
                        <?php else: ?>
                            <a class="site-nav__link" href="<?= e(url('/patient/login')) ?>">Patient login</a>
                            <a class="site-nav__button" href="<?= e(url('/clinic/login')) ?>">Clinic admin</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </nav>
            </div>

            <?php if ($isScoped): ?>
                <div class="site-subheader">
                    <div>
                        <p class="site-subheader__title"><?= e($scopedClinic['address']) ?></p>
                        <p class="site-subheader__meta"><?= e($scopedClinic['phone']) ?><?php if (!empty($scopedClinic['email'])): ?> · <?= e($scopedClinic['email']) ?><?php endif; ?></p>
                    </div>
                    <div class="site-subheader__badges">
                        <span class="site-pill">Build <?= e(config('app.build.version')) ?></span>
                        <span class="site-pill site-pill--soft">Mobile booking ready</span>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!$isScoped && $currentUser): ?>
            <div class="site-subheader site-subheader--compact">
                <p class="site-subheader__meta">Signed in as <?= e((string) $currentUserIdentity) ?></p>
                <span class="site-pill">Build <?= e(config('app.build.version')) ?></span>
            </div>
        <?php endif; ?>
    </header>

    <main class="site-main">
        <?php if ($flashSuccess): ?>
            <div class="notice notice--success"><?= e($flashSuccess) ?></div>
        <?php endif; ?>
        <?php if ($flashError): ?>
            <div class="notice notice--error"><?= e($flashError) ?></div>
        <?php endif; ?>

        <?= $content ?>
    </main>

    <footer class="site-footer">
        <div class="site-footer__inner">
            <p>
                <?= e($isScoped ? (string) $scopedClinic['name'] : (string) config('app.name')) ?>
                · build <?= e(config('app.build.version')) ?>
                <?php if ((string) config('app.build.commit') !== ''): ?>
                    · commit <?= e(config('app.build.commit')) ?>
                <?php endif; ?>
            </p>
            <p>
                <?php if ((string) config('app.build.deployed_at') !== ''): ?>
                    deployed <?= e((string) config('app.build.deployed_at')) ?>
                <?php else: ?>
                    mobile-first clinic booking by Huviena
                <?php endif; ?>
                <?php if ($isScoped): ?>
                    · <a href="<?= e(url('/clinic/login')) ?>">admin login</a>
                <?php else: ?>
                    · <a href="<?= e(url('/super-admin/login')) ?>">platform admin</a>
                <?php endif; ?>
            </p>
        </div>
    </footer>
</body>
</html>
