<h1>Welcome to <?= e(config('app.name')) ?></h1>
<p>Hello <?= e(trim($patient['first_name'] . ' ' . $patient['last_name'])) ?>,</p>
<p>Your patient account is ready. You can now book appointments online and manage them from your dashboard.</p>
