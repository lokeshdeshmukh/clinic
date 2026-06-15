# Hostinger Git Deploy

This project now fits a Hostinger Git deployment flow better than an FTP push workflow.

## Recommended setup

1. Keep the code in GitHub
2. Connect the repository in Hostinger `Advanced -> Git`
3. Deploy the `main` branch to your live app folder
4. Keep live secrets outside the deployed repository

## Clinic subdomain behavior

This application now supports a single-clinic patient experience based on the request host.

Example:

- `appointment.huviena.com` can stay as the central app domain
- `sudarshan-clinic.huviena.com` can open the Sudarshan Clinic patient experience automatically

Current assumption:

- the first subdomain label must match the clinic `slug`

So if the clinic slug is `sudarshan-clinic`, the subdomain should be:

- `sudarshan-clinic.huviena.com`

## Protecting the live env file

This app supports an external server-only environment file.

It looks for env files in this order:

1. `APP_ENV_FILE` server variable if you ever define one
2. `$HOME/.clinicflow.env`
3. `$HOME/.env`
4. one level above the app folder as `.clinicflow.env`
5. one level above the app folder as `.env`
6. `storage/app.env`
7. the repo root `.env`

For Hostinger Git deploy, the safest option is:

- create `.clinicflow.env` one level above the deployed app folder
- keep `.env` out of GitHub

Example idea:

- deployed app folder: `public_html/appointment`
- live env file: `public_html/.clinicflow.env`

This matters because Hostinger Git deploy can replace files inside the deployed app directory during updates. Keeping the live env file one level above that directory avoids accidental overwrites.

## If the site is already installed

1. Open Hostinger File Manager
2. Copy the current repo-root `.env` contents
3. Create a new file one level above the app folder named `.clinicflow.env`
4. Paste the env contents there
5. Delete the repo-root `.env` from the server if you want the external file to be the only source

The application will automatically prefer the external `.clinicflow.env` file.

## Important Git rule

`.env`, `.clinicflow.env`, and `storage/app.env` must never be committed to GitHub.

This repository now includes a GitHub Actions check that fails if any supported env file is tracked.

## Future database changes

When you need a schema change:

1. Add a new SQL file in `database/migrations`
2. Use the next number, for example:

```text
011_add_indexes_to_appointments.sql
```

3. Commit and push

Then apply the updates on the server through your deployment flow and the migration service.
