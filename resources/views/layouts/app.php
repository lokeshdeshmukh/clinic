<?php

use App\Core\Auth;
use App\Core\Session;

$currentUser = Auth::user();
$guard = Auth::guard();
$flashSuccess = Session::getFlash('success');
$flashError = Session::getFlash('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? config('app.name')) ?></title>
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
    <script defer src="<?= e(asset('js/app.js')) ?>"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900" data-base-url="<?= e(rtrim((string) config('app.url'), '/')) ?>">
    <div class="relative overflow-hidden bg-slate-950 text-white">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(37,99,235,0.35),_transparent_35%),radial-gradient(circle_at_bottom_right,_rgba(8,145,178,0.25),_transparent_30%)]"></div>
        <header class="relative mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <a href="<?= e(url('/')) ?>" class="flex items-center gap-3">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-white/10 text-lg font-bold">CF</span>
                <span>
                    <span class="block text-sm uppercase tracking-[0.32em] text-slate-300">Clinic</span>
                    <span class="block text-lg font-semibold"><?= e(config('app.name')) ?></span>
                </span>
            </a>
            <nav class="flex flex-wrap items-center gap-3 text-sm">
                <a class="rounded-full px-4 py-2 text-slate-200 transition hover:bg-white/10" href="<?= e(url('/clinics')) ?>">Clinics</a>
                <?php if ($guard === 'clinic'): ?>
                    <a class="rounded-full px-4 py-2 text-slate-200 transition hover:bg-white/10" href="<?= e(url('/admin/dashboard')) ?>">Dashboard</a>
                    <form method="post" action="<?= e(url('/clinic/logout')) ?>" class="inline">
                        <?= csrf_field() ?>
                        <button class="rounded-full bg-white px-4 py-2 font-medium text-slate-900 transition hover:bg-slate-100">Logout</button>
                    </form>
                <?php elseif ($guard === 'patient'): ?>
                    <a class="rounded-full px-4 py-2 text-slate-200 transition hover:bg-white/10" href="<?= e(url('/patient/dashboard')) ?>">My Appointments</a>
                    <form method="post" action="<?= e(url('/patient/logout')) ?>" class="inline">
                        <?= csrf_field() ?>
                        <button class="rounded-full bg-white px-4 py-2 font-medium text-slate-900 transition hover:bg-slate-100">Logout</button>
                    </form>
                <?php else: ?>
                    <a class="rounded-full px-4 py-2 text-slate-200 transition hover:bg-white/10" href="<?= e(url('/patient/login')) ?>">Patient Login</a>
                    <a class="rounded-full bg-white px-4 py-2 font-medium text-slate-900 transition hover:bg-slate-100" href="<?= e(url('/clinic/login')) ?>">Clinic Admin</a>
                <?php endif; ?>
            </nav>
        </header>
        <?php if ($currentUser): ?>
            <div class="relative mx-auto max-w-7xl px-4 pb-5 text-sm text-slate-300 sm:px-6 lg:px-8">
                Signed in as <?= e($currentUser['email']) ?>
            </div>
        <?php endif; ?>
    </div>

    <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <?php if ($flashSuccess): ?>
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900"><?= e($flashSuccess) ?></div>
        <?php endif; ?>
        <?php if ($flashError): ?>
            <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900"><?= e($flashError) ?></div>
        <?php endif; ?>
        <?= $content ?>
    </main>
    <footer class="border-t border-slate-200 bg-white/70">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-4 text-xs text-slate-500 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
            <p><?= e(config('app.name')) ?> build <?= e(config('app.build.version')) ?></p>
            <p>
                commit <?= e(config('app.build.commit')) ?>
                <?php if ((string) config('app.build.deployed_at') !== ''): ?>
                    <span class="mx-1">|</span>
                    deployed <?= e((string) config('app.build.deployed_at')) ?>
                <?php endif; ?>
            </p>
        </div>
    </footer>
</body>
</html>
