<?php $brand = $clinic['name'] ?? config('app.name'); ?>
<div style="font-family:Arial,sans-serif;background:#f4f7fb;padding:32px;color:#14213d;">
    <div style="max-width:560px;margin:0 auto;background:#ffffff;border-radius:18px;padding:32px;border:1px solid #dbe6f6;">
        <p style="margin:0 0 12px;font-size:12px;letter-spacing:.22em;text-transform:uppercase;color:#6b7b93;">Secure sign-in</p>
        <h1 style="margin:0 0 16px;font-size:28px;line-height:1.2;"><?= e($brand) ?> login code</h1>
        <p style="margin:0 0 18px;font-size:16px;line-height:1.7;color:#4b5d79;">Use the one-time code below to continue booking and manage your appointments.</p>
        <div style="margin:20px 0;padding:18px 22px;border-radius:16px;background:#eef4ff;border:1px solid #bfd3ef;text-align:center;">
            <span style="display:block;font-size:34px;font-weight:700;letter-spacing:.35em;color:#1744a7;"><?= e($otp) ?></span>
        </div>
        <p style="margin:0;font-size:14px;line-height:1.7;color:#5b6b83;">This code expires in <?= e((string) $ttlMinutes) ?> minutes. If you did not request it, you can safely ignore this email.</p>
    </div>
</div>
