# Class Schedule / Timetable — Project Requirements

## 1. Overview
Web application that shows an authenticated user their own class schedule (timetable). 
Existing static HTML/CSS/JS prototype at `/Users/justas/Workspace/taurius/html-projektai/tvarkarastis` defines the frontend look — reuse its markup/styles, 
replace static data with dynamic data from a PHP backend.

## 2. Scope

### 2.1 In scope
- User authentication (login/logout, session handling)
- Each user sees only their own schedule
- Schedule data stored in MySQL, served via a PHP API
- Automated DB schema migrations (version-controlled)
- CI/CD via GitHub Actions, deploying to shared hosting over FTP

### 2.2 Out of scope for now
- User self-registration 
- Editing schedules via the UI
- Password reset flow
- Multi-tenant / multi-school support

## 3. Functional Requirements

| ID | Requirement |
|----|-------------|
| FR1 | User logs in with username/email + password |
| FR2 | Invalid credentials show an error; no user enumeration (same error for "unknown user" and "wrong password") |
| FR3 | Session persists across requests (cookie-based session or JWT — decide, see §6) |
| FR4 | Logout invalidates the session |
| FR5 | Authenticated user requests `GET /api/schedule` (or similar) and receives only their own timetable entries |
| FR6 | Schedule entries include at minimum: day/date, start time, end time, subject/class name, room, teacher (confirm exact fields against the static prototype's data model) |
| FR7 | Unauthenticated requests to any schedule endpoint return 401, not partial/empty data |
| FR8 | Frontend renders the schedule using the existing static design, populated from the API instead of hardcoded data |

## 4. Non-Functional Requirements
- **Security**: passwords hashed with `password_hash()`/bcrypt (never stored plain or reversibly encrypted); parameterized queries only (PDO prepared statements) — no raw string-built SQL; HTTPS enforced in production; session cookies `HttpOnly` + `Secure` + `SameSite`
- **Hosting constraints**: shared Hostinger hosting reached via FTP only — no SSH/composer-on-server assumed unless confirmed. This affects how PHP dependencies get onto the server (see §7).
- **Performance**: single user's schedule query should be a single indexed lookup (index on `user_id` at minimum)
- **Portability**: DB credentials and other secrets never committed to the repo; supplied via environment variables / GitHub Secrets

## 5. Data Model (draft — confirm against prototype)
```
users
  id, username/email, password_hash, created_at

schedule_entries
  id, user_id (FK -> users.id), day_of_week or date,
  start_time, end_time, subject, room, teacher, created_at
```
Timetable a fixed weekly template (Mon–Fri recurring)

## 6. Authentication — decision needed
Two reasonable approaches for a plain-PHP app on shared hosting:
- **Server-side sessions** (`session_start()`, session ID in an HttpOnly cookie) — simplest, standard for this hosting setup, sessions stored server-side (files or DB-backed if multiple PHP-FPM workers need shared state).
- **JWT** — stateless, useful if you'll add a mobile/SPA client later, but adds complexity (token expiry/refresh) that isn't needed for a single server-rendered or same-origin app.

Recommendation: server-side sessions unless there's a concrete reason (separate frontend domain, future mobile app) to need JWT.

## 7. Database Migrations (MySQL, version-controlled)

| Tool | Notes |
|---|---|
| **Phinx** (`robmorgan/phinx`) | PHP-native, Composer package, migrations written in PHP, closest DX to a PHP project; widely used, framework-agnostic |

Migrations against the production DB (`srv505.hstgr.io` / `195.35.59.90`) as a deploy step, using the `DB_DATABASE`/`DB_USER`/`DB_PASSWORD` GitHub Secrets.

## 8. CI/CD — GitHub Actions
Pipeline (on push to `main`, or on tag):
1. Checkout code
2. Install PHP dependencies (`composer install --no-dev`)
3. Run DB migrations against production MySQL (if remote access is available — see open question above)
4. Deploy application files via FTP to `public_html/timetable` on `45.84.204.241`, port 21, user `u798759962.taurius.lt`

Use an existing FTP-deploy action (e.g. `SamKirkland/FTP-Deploy-Action`) rather than hand-rolling FTP in a shell script. Needed secrets:
- `FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD` (FTP password isn't listed yet — needs adding to GitHub Secrets)
- `DB_DATABASE`, `DB_USER`, `DB_PASSWORD` (already noted as added)

## 9. Environment Config
- `.env` (or equivalent) for local dev, excluded via `.gitignore`
- Production values injected at deploy time from GitHub Secrets — since it's plain FTP file upload (no server-side env injection like you'd get with SSH/a real deploy target), decide how the deployed PHP actually receives `DB_*` values: e.g. GitHub Actions writes a generated `config.php` (from a template + secrets) into the build output before the FTP upload step, rather than committing real credentials.
