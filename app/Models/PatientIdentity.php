<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class PatientIdentity extends Model
{
    protected string $table = 'patient_identities';

    public function findByProviderUserId(string $provider, string $providerUserId): ?array
    {
        $sql = 'SELECT * FROM patient_identities
                WHERE provider = :provider
                  AND provider_user_id = :provider_user_id
                  AND deleted_at IS NULL
                LIMIT 1';
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'provider' => $provider,
            'provider_user_id' => $providerUserId,
        ]);

        return $statement->fetch() ?: null;
    }

    public function findByProviderEmail(string $provider, string $email): ?array
    {
        $sql = 'SELECT * FROM patient_identities
                WHERE provider = :provider
                  AND provider_email = :provider_email
                  AND deleted_at IS NULL
                LIMIT 1';
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'provider' => $provider,
            'provider_email' => normalize_email($email),
        ]);

        return $statement->fetch() ?: null;
    }

    public function findForPatient(int $patientId, string $provider): ?array
    {
        $sql = 'SELECT * FROM patient_identities
                WHERE patient_id = :patient_id
                  AND provider = :provider
                  AND deleted_at IS NULL
                LIMIT 1';
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'patient_id' => $patientId,
            'provider' => $provider,
        ]);

        return $statement->fetch() ?: null;
    }
}
