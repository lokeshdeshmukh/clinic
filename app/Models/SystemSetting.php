<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class SystemSetting extends Model
{
    protected string $table = 'system_settings';

    public function getForClinic(int $clinicId): array
    {
        $sql = 'SELECT * FROM system_settings WHERE (clinic_id = :clinic_id OR clinic_id IS NULL)';
        $statement = $this->db->prepare($sql);
        $statement->execute(['clinic_id' => $clinicId]);
        $rows = $statement->fetchAll();
        $settings = [];

        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        return $settings;
    }

    public function upsert(int $clinicId, string $key, string $value, string $type = 'string', bool $isPublic = false): void
    {
        $sql = 'INSERT INTO system_settings (clinic_id, setting_key, setting_value, setting_type, is_public, created_at, updated_at)
                VALUES (:clinic_id, :setting_key, :setting_value, :setting_type, :is_public, :created_at, :updated_at)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), setting_type = VALUES(setting_type), is_public = VALUES(is_public), updated_at = VALUES(updated_at)';
        $now = date('Y-m-d H:i:s');
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'clinic_id' => $clinicId,
            'setting_key' => $key,
            'setting_value' => $value,
            'setting_type' => $type,
            'is_public' => $isPublic ? 1 : 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
