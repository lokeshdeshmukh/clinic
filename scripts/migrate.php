<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap/app.php';

$result = (new \App\Services\MigrationService())->runPending();

foreach ($result['migrations'] as $migration) {
    echo 'Running ' . $migration . PHP_EOL;
}

foreach ($result['seeds'] as $seed) {
    echo 'Seeding ' . $seed . PHP_EOL;
}

echo 'Done.' . PHP_EOL;
