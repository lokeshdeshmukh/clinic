# Installation Guide

## 1. Best option for your hosting

This project now supports **shared hosting without terminal access**.

You can:

1. Create a new subdomain in your hosting panel.
2. Upload the project ZIP into that subdomain folder using File Manager.
3. Extract the ZIP.
4. Open `https://your-subdomain.com/install/`
5. Fill in the installer form.

No Composer command is required on the server.
No Node command is required on the server.
The CSS build is already included.
PHPMailer is already bundled.

## 2. What you need before uploading

- PHP 8.3+
- MySQL 8+
- Apache or LiteSpeed style `.htaccess` support
- SMTP details if you want email sending enabled

Before opening the installer, create these from your hosting panel:

- A MySQL database
- A MySQL user
- The password for that MySQL user

## 3. ZIP upload steps

1. Create a subdomain such as `clinic.yourdomain.com`
2. Note the folder assigned to that subdomain
3. Upload the project ZIP into that folder
4. Extract the ZIP there
5. Make sure the extracted files are directly inside the subdomain folder
6. Visit:

```text
https://clinic.yourdomain.com/install/
```

If your host points the subdomain to the extracted folder root, that is fine. This package includes root routing plus `public/` routing support.

## 4. Browser installer

The installer will:

- Create the environment file
- Save your app URL, database, and SMTP settings
- Run all SQL migrations
- Run the seed data
- Create `storage/installed.lock`

When possible, the installer writes configuration to an external `.clinicflow.env` file one level above the deployed app folder so Git-based redeploys do not overwrite it.

After that, open:

- `https://your-subdomain.com/clinic/register`

That is where the first clinic admin account is created.

## 5. If installation says it cannot write files

The installer needs permission to write:

- the selected env file location
- `storage/logs`
- `storage/installed.lock`
- `public/uploads`

On most shared hosting accounts this works automatically after ZIP extraction. If it does not, create these manually from File Manager:

- an empty `.clinicflow.env` file one level above the app folder, or an empty `.env` file in the app root
- the folder `storage/logs`
- the folder `public/uploads`

Then run the installer again.

If you plan to use Hostinger Git deployment, prefer the external `.clinicflow.env` file one level above the app folder. A repo-root `.env` inside the deployed folder is more likely to be replaced during updates.

## 6. Cron job for reminders

If your hosting panel has a Cron Jobs screen, add:

```text
/usr/bin/php /home/USERNAME/path-to-subdomain/scripts/send_reminders.php
```

Run it every hour.

If you do not know the PHP path, your host usually shows it in the Cron Jobs section.

## 7. Authentication flow

- Clinic admins register first, then verify their email before login
- Patients can register and start booking
- Both clinic admins and patients have forgot-password flows

## 8. Booking protection

Double-booking protection is handled in two layers:

- live slot generation from weekly rules, date overrides, holidays, blocked slots, and active appointments
- MySQL locking plus a unique `booking_guard_key` during booking and rescheduling

## 9. Reports and exports

Available report types:

- Appointment report
- Revenue report
- Doctor performance report

Available export formats:

- CSV
- Excel-compatible `.xls`
- PDF

## 10. REST API

Public API endpoints:

- `GET /api/v1/clinics`
- `GET /api/v1/clinics/{slug}/doctors`
- `GET /api/v1/doctors/{id}`
- `GET /api/v1/doctors/{id}/slots?date=YYYY-MM-DD`

Authenticated endpoints:

- `GET /api/v1/admin/appointments`
- `POST /api/v1/appointments`
- `POST /api/v1/appointments/{id}/cancel`

## 11. Optional terminal-based setup

If you ever move to a VPS or hosting with shell access, you can still use:

```bash
php scripts/migrate.php
```

But it is no longer required for standard deployment.

## 12. Automatic future updates with Git

If you do not want to upload a ZIP every time, use the Hostinger Git deployment flow in [AUTO_DEPLOY.md](AUTO_DEPLOY.md).

That flow:

- keeps the live env file outside the deployed repository
- does not ask for DB or SMTP settings again
- pulls code from GitHub through Hostinger Git deployment
