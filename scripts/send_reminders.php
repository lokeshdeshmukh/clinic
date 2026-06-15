<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap/app.php';

$pdo = \App\Core\Database::connection();
$statement = $pdo->prepare('SELECT a.*, d.name AS doctor_name, p.email AS patient_email, p.first_name, p.last_name, c.name AS clinic_name
    FROM appointments a
    INNER JOIN doctors d ON d.id = a.doctor_id
    INNER JOIN patients p ON p.id = a.patient_id
    INNER JOIN clinics c ON c.id = a.clinic_id
    WHERE a.deleted_at IS NULL
      AND a.active_booking = 1
      AND a.status IN ("booked", "confirmed")
      AND a.reminder_sent_at IS NULL
      AND TIMESTAMP(a.appointment_date, a.start_time) BETWEEN NOW() + INTERVAL 23 HOUR AND NOW() + INTERVAL 25 HOUR');
$statement->execute();
$appointments = $statement->fetchAll();

$notifications = new \App\Services\NotificationService();
foreach ($appointments as $appointment) {
    $notifications->sendAppointmentReminder($appointment);
    (new \App\Models\Appointment())->updateById((int) $appointment['id'], [
        'reminder_sent_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ]);
    echo 'Reminder sent for appointment #' . $appointment['id'] . PHP_EOL;
}
