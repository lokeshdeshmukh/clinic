<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EmailLog;
use App\Core\View;
use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

final class MailerService
{
    public function send(string $to, string $subject, string $template, array $data = [], array $context = []): bool
    {
        if (trim($to) === '') {
            return false;
        }

        $body = View::render('emails/' . $template, $data, null);
        $logModel = new EmailLog();
        $logId = $logModel->insert([
            'clinic_id' => $context['clinic_id'] ?? null,
            'patient_id' => $context['patient_id'] ?? null,
            'appointment_id' => $context['appointment_id'] ?? null,
            'recipient_email' => $to,
            'subject' => $subject,
            'template_key' => $template,
            'status' => 'queued',
            'error_message' => null,
            'sent_at' => null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (!class_exists(PHPMailer::class)) {
            $logModel->updateById($logId, [
                'status' => 'failed',
                'error_message' => 'PHPMailer bundle is missing from the deployment package.',
            ]);
            return false;
        }

        $mailer = new PHPMailer(true);

        try {
            $mailer->isSMTP();
            $mailer->Host = (string) config('mail.host');
            $mailer->Port = (int) config('mail.port');
            $mailer->SMTPAuth = true;
            $mailer->Username = (string) config('mail.username');
            $mailer->Password = (string) config('mail.password');
            $mailer->SMTPSecure = (string) config('mail.encryption');
            $mailer->CharSet = 'UTF-8';
            $mailer->setFrom((string) config('mail.from_address'), (string) config('mail.from_name'));
            $mailer->addAddress($to);
            $mailer->isHTML(true);
            $mailer->Subject = $subject;
            $mailer->Body = $body;
            $mailer->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], PHP_EOL, $body));
            $mailer->send();

            $logModel->updateById($logId, [
                'status' => 'sent',
                'sent_at' => date('Y-m-d H:i:s'),
                'error_message' => null,
            ]);

            return true;
        } catch (MailException $exception) {
            $logModel->updateById($logId, [
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
