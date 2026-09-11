# Tvarkaraštis (Timetable)

A small PHP + MySQL web app that shows an authenticated user their own class
schedule. Frontend markup/styling is carried over from the static prototype;
data now comes from a PHP API backed by MySQL.

## Stack

- Plain PHP (no framework) with PDO + prepared statements
- MySQL, migrations via [Phinx](https://book.cakephp.org/phinx/0/en/index.html)
- Server-side sessions for auth (see `timetable-requirements.md` §6)
- Deploy: GitHub Actions → FTP to Hostinger shared hosting

## Project layout

Everything under `public/` is the web root that gets deployed as-is to
`public_html/timetable` on the server (shared hosting is FTP-only, so the
whole app — including `src/` and `config/` — lives inside `public/`,
protected from direct access by `.htaccess`).

```
public/
  index.php          Timetable page (requires login)
  login.php           Login form + handler
  logout.php
  style.css / script.js
  api/schedule.php    GET current user's schedule (JSON, 401 if not logged in)
  includes/bootstrap.php   Session setup, autoload src/
  src/                 Auth.php, Database.php (access denied via .htaccess)
  config/               config.php.dist (committed) + config.php (generated, gitignored)
db/
  migrations/           Phinx migrations (users, schedule_entries)
  seeds/                DemoDataSeeder.php — one demo user + a full week of lessons
```

## Local development

1. Install dependencies: `composer install`
2. Create a local MySQL database, e.g. `timetable_local`
3. Copy `public/config/config.php.dist` to `public/config/config.php` and
   fill in real local values (`app_env` should be `local`)
4. Export the same DB credentials as env vars and run migrations:
   ```
   export DB_HOST=127.0.0.1 DB_DATABASE=timetable_local DB_USER=root DB_PASSWORD=secret PHINX_ENVIRONMENT=local
   vendor/bin/phinx migrate
   vendor/bin/phinx seed:run   # optional demo data (user: demo / demo1234)
   ```
5. Serve the app: `php -S localhost:8000 -t public`

## Deployment

`.github/workflows/deploy.yml` runs on every push to `main`:

1. `composer install --no-dev`
2. Runs Phinx migrations against production MySQL
3. Generates `public/config/config.php` from `public/config/config.php.dist` using secrets
4. FTP-uploads `public/` to `public_html/timetable`

### Required GitHub Secrets

| Secret | Purpose |
|---|---|
| `DB_HOST`, `DB_DATABASE`, `DB_USER`, `DB_PASSWORD` | MySQL connection, used for migrations and written into `config.php` |
| `FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD` | Hostinger FTP credentials |

Real credentials are never committed — see `.gitignore` and
`public/config/config.php.dist`.
