<h1>Appointment rescheduled</h1>
<p>Your appointment with <?= e($appointment['doctor_name']) ?> has been updated.</p>
<p>Previous time: <?= e($oldDate) ?> at <?= e($oldTime) ?></p>
<p>New time: <?= e($appointment['appointment_date']) ?> at <?= e($appointment['start_time']) ?></p>
