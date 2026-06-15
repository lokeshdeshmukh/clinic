<h1>Verify your clinic account</h1>
<p>Hello <?= e($clinic['name']) ?>,</p>
<p>Thanks for registering with <?= e(config('app.name')) ?>. Please verify your email to activate the clinic admin account.</p>
<p><a href="<?= e($verificationUrl) ?>">Verify clinic email</a></p>
