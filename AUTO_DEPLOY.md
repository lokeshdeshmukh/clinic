# Auto Deploy With GitHub

This project supports automatic shared-hosting deployment without server terminal access.

## How it works

1. Your code lives in GitHub.
2. Every push to `main` triggers GitHub Actions.
3. GitHub uploads changed files to your hosting account over FTP.
4. GitHub calls a secure deploy hook on your site.
5. The site runs only new SQL migrations automatically.

Your database, SMTP settings, app URL, and session settings stay safe because they remain inside the server-side `.env` file and are not overwritten by deployment.

## One-time setup

### 1. Put this project in GitHub

Create a GitHub repository and push this project to it.

### 2. Install the site first

Upload once by ZIP and complete:

```text
https://your-subdomain.com/install/
```

The installer creates `.env` and shows you a deploy token and a ready deploy hook URL.

If the site is already installed, you can also find or rotate the deploy token inside:

`Admin Dashboard -> Settings`

### 3. Add GitHub secrets

In your GitHub repository:

`Settings` -> `Secrets and variables` -> `Actions`

Add:

- `FTP_SERVER`
- `FTP_USERNAME`
- `FTP_PASSWORD`
- `FTP_REMOTE_DIR`
- `DEPLOY_HOOK_URL`

### 4. Secret values

- `FTP_SERVER`
  Example: `ftp.yourdomain.com`

- `FTP_USERNAME`
  Your hosting FTP username

- `FTP_PASSWORD`
  Your hosting FTP password

- `FTP_REMOTE_DIR`
  The subdomain folder on the server
  Example: `/public_html/clinic/`

- `DEPLOY_HOOK_URL`
  Example:

```text
https://clinic.yourdomain.com/deploy/run-updates?token=YOUR_DEPLOY_TOKEN
```

Use the exact URL shown by the installer.

## What happens on future updates

When you change code and push to `main`:

- the site files update automatically
- `.env` stays untouched
- `public/uploads` stays untouched
- `storage/logs` stays untouched
- new database migrations run automatically

## Important rule for future schema changes

When you need a database change:

1. Add a new SQL file in `database/migrations`
2. Give it the next number, for example:

```text
011_add_indexes_to_appointments.sql
```

3. Commit and push

The deploy hook applies only migrations that have not already been recorded.

## If your host supports Git directly

Some cPanel hosts include `Git Version Control`.

That can work too, but it often still needs a manual pull or custom hook. The included GitHub Actions workflow is usually the easiest auto-update path on shared hosting without terminal access.
