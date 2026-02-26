# AfyaRescue – Emergency Response & Triage MVP

API-first emergency response and triage platform built with **Laravel 11**, **MySQL** (or SQLite), and **Laravel Sanctum**. Designed so it can later be wrapped in Flutter apps.

## Features

- **Roles**: `patient`, `driver`, `hospital_admin`, `super_admin` (auth via Sanctum + web session)
- **Database**: Users, Hospitals, Ambulances, Emergencies, TriageResponses
- **Patient flow**: Submit emergency with location + triage questionnaire → severity score (rule-based) → nearest available ambulance assigned → driver and hospital notified
- **Status flow**: `requested` → `assigned` → `enroute` → `arrived` → `closed`
- **Hospital dashboard** (Blade): Incoming emergencies, severity, assigned ambulance, status
- **API**: REST under `/api/v1` for Flutter/mobile (token auth)
- **Security**: Role-based middleware, hashed passwords, encrypted sessions, API tokens
- **Optional**: Pusher/WebSockets for live dashboard updates (see below)

## Requirements

- PHP 8.2+
- Composer
- Node.js & npm (for frontend build)
- MySQL 8+ or SQLite

## Setup

1. **Clone and install**
   ```bash
   cd afya-rescue
   composer install
   cp .env.example .env
   php artisan key:generate
   ```

2. **Database (MySQL)**
   - Create a database, e.g. `afya_rescue`
   - In `.env` set:
     ```
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=afya_rescue
     DB_USERNAME=root
     DB_PASSWORD=your_password
     ```
   - Or keep `DB_CONNECTION=sqlite` for quick local testing (default).

3. **Migrations and seed**
   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   ```

4. **Frontend (optional, for Blade UI)**
   ```bash
   npm install && npm run build
   ```

5. **Run**
   ```bash
   php artisan serve
   ```
   - Web: http://127.0.0.1:8000  
   - API base: http://127.0.0.1:8000/api/v1  

## Seeded test users (password: `password`)

| Role           | Email                    |
|----------------|--------------------------|
| Patient        | patient@afyarescue.test  |
| Driver         | driver@afyarescue.test   |
| Hospital Admin | hospital@afyarescue.test |
| Super Admin    | admin@afyarescue.test    |

## Web flows

- **Patient**: Register (role: patient) → Login → “New Emergency” → fill location + triage → submit → see status and assigned ambulance.
- **Hospital**: Login as `hospital@afyarescue.test` → “Hospital Dashboard” → view incoming emergencies, severity, ambulance, status.
- **Driver**: Use API (or future driver UI) to list assigned emergencies and update status to `enroute` / `arrived` / `closed`.

## API (Flutter-ready)

- **Auth**: `POST /api/v1/register`, `POST /api/v1/login` → returns `token` (Bearer).
- **Patient**: `POST /api/v1/emergencies` (body: `hospital_id`, `latitude`, `longitude`, `address_text?`, `triage`), `GET /api/v1/emergencies/my`, `GET /api/v1/emergencies/{id}`.
- **Driver**: `GET /api/v1/driver/emergencies`, `PATCH /api/v1/driver/emergencies/{id}/status` (body: `status`: `enroute`|`arrived`|`closed`).
- **Hospital**: `GET /api/v1/hospital/emergencies`, `GET /api/v1/hospital/emergencies/{id}`.

All authenticated routes use `Authorization: Bearer <token>`.

## Phase 2 enhancements

- **TriageService**: Weighted scoring (chest pain, bleeding, unconsciousness, stroke signs, breathing difficulty); categories **Critical**, **Moderate**, **Mild**; stored in TriageResponses and Emergencies.
- **Ambulance assignment**: Haversine distance, hospital level preference for critical cases, ETA (minutes) stored on emergency; ambulance status Available → Assigned (busy) → released when emergency closed.
- **Hospital dashboard**: Severity color codes (red/amber/green), ETA column, filter by patient name/phone and status, date range; analytics (avg assignment time, avg en route time, by severity, ambulance utilization); **CSV export**.
- **Emergency event log**: All status changes logged in `emergency_event_logs` for auditing.
- **Super Admin**: Manage **Users**, **Hospitals** (with level 1–3), **Ambulances** at `/super-admin/users`, `/super-admin/hospitals`, `/super-admin/ambulances`. Hospital admins have view-only dashboard (ambulances are auto-assigned).
- **Critical alerts**: Set `AFYARESCUE_CRITICAL_ALERT_EMAIL` in `.env` to receive email for Critical emergencies; Pusher still used for real-time when configured.
- **API**: Responses include `severity_category`, `eta_minutes`; all inputs validated; use HTTPS in production.

## Optional: live updates (Pusher)

1. Install Pusher PHP SDK: `composer require pusher/pusher-php-server`
2. In `.env` set `BROADCAST_CONNECTION=pusher` and add `PUSHER_APP_*` keys.
3. In the hospital dashboard view you can subscribe to channel `hospital.{id}` and listen for event `emergency.updated` (see `App\Events\EmergencyStatusChanged`).

## Security notes

- Passwords hashed with bcrypt; sessions and cookies use Laravel encryption.
- Sensitive config in `.env`; do not commit `.env`.
- API protected by Sanctum tokens; web by session + role middleware.
- Use HTTPS in production; consider encrypting sensitive patient data at rest if required.

## License

MIT.
