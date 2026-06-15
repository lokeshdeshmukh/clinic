<h1>Appointment reminder</h1>
<p>This is a reminder for your upcoming appointment with <?= e($appointment['doctor_name']) ?>.</p>
<p>Date: <?= e($appointment['appointment_date']) ?></p>
<p>Time: <?= e($appointment['start_time']) ?> - <?= e($appointment['end_time']) ?></p>
<p>Clinic: <?= e($appointment['clinic_name']) ?></p>
