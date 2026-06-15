<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\MigrationService;

final class DeployController extends Controller
{
    public function __construct(private readonly MigrationService $migrations = new MigrationService())
    {
    }

    public function runUpdates(Request $request): never
    {
        $expectedToken = (string) env('DEPLOY_TOKEN', '');
        $queryToken = (string) $request->query('token', '');
        $headerToken = (string) ($request->server['HTTP_X_DEPLOY_TOKEN'] ?? '');
        $providedToken = $queryToken !== '' ? $queryToken : $headerToken;

        if ($expectedToken === '' || !hash_equals($expectedToken, $providedToken)) {
            $this->json([
                'ok' => false,
                'message' => 'Deploy token is invalid.',
            ], 403);
        }

        try {
            $result = $this->migrations->runPending();
        } catch (\Throwable $exception) {
            $this->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }

        $this->json([
            'ok' => true,
            'message' => 'Deployment updates completed.',
            'applied' => $result,
        ]);
    }
}
