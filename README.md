# AfyaRescue

Emergency response and triage platform that connects patients, ambulance drivers, hospitals, and system administrators. Patients can request help (with or without an account), symptoms are scored through automated triage, ambulances are matched and assigned, and every role gets a tailored dashboard to coordinate care in real time.

## Features

- **Public emergency requests** — Anyone can submit an emergency at `/emergency`; registered patients can track their request after login.
- **Automated triage** — Weighted symptom scoring categorizes cases as Critical, Moderate, or Mild and drives dispatch priority.
- **Smart ambulance assignment** — Nearest available ambulance is selected using distance, hospital level, ambulance type, and driver skill; critical cases prefer ICU/advanced units.
- **Status workflow** — Emergencies progress through `requested → assigned → enroute → arrived → closed` with event logging and audit trails.
- **Role-based dashboards**
  - **Patient** — Request and track emergencies
  - **Driver** — View assignments, update status, share live location
  - **Hospital admin** — Monitor incoming emergencies, export reports, view patient history
  - **Super admin** — Manage users, hospitals, ambulances, system health, and metrics
- **REST API (`/api/v1`)** — Sanctum-authenticated endpoints for mobile clients (e.g. Flutter).
- **Notifications** — Queued jobs for new emergencies and status updates; optional SMS and critical-alert email.
- **Analytics & reporting** — Hospital CSV/report exports and super-admin metrics.

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP 8.2+, Laravel 11 |
| Auth (API) | Laravel Sanctum |
| Frontend | Blade, Tailwind CSS, Vite |
| Database | SQLite (default) or MySQL |
| Queue | Database driver |
| CI | Jenkins |

## Requirements

- PHP 8.2 or higher with common extensions (`pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`)
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) 18+ and npm
- SQLite (bundled with PHP) or MySQL

## Getting Started

### 1. Clone and install dependencies

```bash
git clone https://github.com/David-IT2/AfyaRescue.git
cd AfyaRescue

composer install
npm install
```

### 2. Environment

```bash
cp .env.example .env
php artisan key:generate
```

By default the app uses SQLite. Create the database file and run migrations:

```bash
touch database/database.sqlite
php artisan migrate
```

For MySQL, set `DB_CONNECTION=mysql` and the related variables in `.env`, then run `php artisan migrate`.

### 3. Seed demo data

```bash
php artisan db:seed
```

This creates two hospitals, test users (see below), ambulances, and sample emergencies.

### 4. Run the application

**All-in-one dev environment** (web server, queue worker, log tail, and Vite):

```bash
composer dev
```

Or run services separately:

```bash
php artisan serve          # http://localhost:8000
php artisan queue:listen   # required for notifications
npm run dev                # frontend assets
```

Build production assets with `npm run build`.

## Demo Accounts

All seeded accounts use the password `password`.

| Role | Email |
|------|-------|
| Patient | `patient@afyarescue.test` |
| Driver | `driver@afyarescue.test` |
| Hospital admin | `hospital@afyarescue.test` |
| Super admin | `admin@afyarescue.test` |
| Driver (second hospital) | `driver2@afyarescue.test` |

After login, `/dashboard` redirects each role to the appropriate area.

## Configuration

Optional settings in `.env` (see `config/afyarescue.php`):

| Variable | Description |
|----------|-------------|
| `GOOGLE_MAPS_API_KEY` | Google Maps API key for maps and ETA |
| `GOOGLE_MAPS_ETA_ENABLED` | Enable ETA calculation via Google Maps (`true`/`false`) |
| `AFYARESCUE_SMS_ENABLED` | Enable SMS notifications |
| `AFYARESCUE_SMS_DRIVER` | SMS driver (`log` logs to file instead of sending) |
| `AFYARESCUE_CRITICAL_ALERT_EMAIL` | Email address for critical emergency alerts |
| `AFYARESCUE_QUEUE_NOTIFICATIONS` | Queue notification jobs (`true` by default) |
| `OPENROUTER_API_KEY` | API key for the `php artisan ai` CLI helper |

Mail defaults to the `log` driver in development. Set `MAIL_*` variables for real email delivery.

## API Overview

Base URL: `/api/v1`

| Method | Endpoint | Auth | Role |
|--------|----------|------|------|
| POST | `/login` | — | — |
| POST | `/register` | — | — |
| POST | `/logout` | Sanctum | any |
| GET | `/user` | Sanctum | any |
| POST | `/emergencies` | Sanctum | patient |
| GET | `/emergencies/my` | Sanctum | patient |
| GET | `/emergencies/{id}` | Sanctum | any |
| GET | `/driver/emergencies` | Sanctum | driver |
| PATCH | `/driver/emergencies/{id}/status` | Sanctum | driver |
| PUT | `/driver/location` | Sanctum | driver |
| GET | `/hospital/emergencies` | Sanctum | hospital_admin, super_admin |
| GET | `/hospital/emergencies/{id}` | Sanctum | hospital_admin, super_admin |
| PATCH | `/hospital/emergencies/{id}/notes` | Sanctum | hospital_admin, super_admin |

Authenticate API requests with a Bearer token returned from `/login` or `/register`.

## Testing

```bash
php artisan test
```

PHPUnit is configured in `phpunit.xml`. Jenkins runs `composer install`, `npm ci`, `npm run build`, and `php artisan test` on each build (see `Jenkinsfile`).

## Project Structure

```
app/
├── Http/Controllers/     # Web and API controllers
├── Models/               # User, Emergency, Hospital, Ambulance, etc.
├── Services/             # Triage, emergency flow, assignment, notifications
├── Jobs/                 # Queued notification jobs
└── Events/               # Real-time broadcast events

resources/views/          # Blade templates (role dashboards, emergency forms)
routes/
├── web.php               # Web routes
└── api.php               # API v1 routes

database/
├── migrations/
└── seeders/              # Hospitals, users, ambulances, sample emergencies
```

## License

This project is open-sourced software 
