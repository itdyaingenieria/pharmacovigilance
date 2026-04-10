# Pharmacovigilance Alert System

Professional prototype for a technical test focused on identifying and notifying customers affected by a medication lot (`951357`) in a configurable date range (default: last 30 days).

## Stack

- Backend: Laravel 12 + PHP 8.2+
- Frontend: Vue 3 + Vite
- Database: MySQL
- Auth/API: JWT (`php-open-source-saver/jwt-auth`) + REST endpoints
- Mail: Laravel Mail + dedicated mailable/template

## Core Features

- Username/password login for pharmacovigilance module
- Medication search by lot number (required) and optional date filters
- Orders retrieval filtered by lot and date range
- Orders table with actions:
    - View order details
    - Alert buyer by email
    - View customer details
- Alert confirmation modal
- Alert audit logging (`alerts` table)
- Bulk alerting from selected orders
- CSV export for filtered affected orders
- Admin-only role-based access for pharmacovigilance API operations

## Architecture Summary

Backend layers:

- Controllers: transport concern only
- Form Requests: input validation
- Services: business logic
- Models + relations: persistence
- API Resources: consistent output payloads
- Mailables: email notification flow

Frontend layers:

- Vue page/app shell for pharmacovigilance module
- Reusable components:
    - LoginPanel
    - SearchFilters
    - OrdersTable
    - OrderDetailsPanel
    - CustomerDetailsPanel
    - AlertModal
- API wrapper with JWT token store/headers

## API Endpoints

Required endpoints implemented:

- `POST /api/login`
- `GET /api/medications/search?lot=951357&start_date=&end_date=`
- `GET /api/orders?lot=951357&start_date=&end_date=`
- `GET /api/orders/{id}`
- `GET /api/customers/{id}`
- `POST /api/alerts/send`
- `POST /api/alerts/send-bulk`
- `GET /api/orders/export/csv?lot=951357&start_date=&end_date=`

### Standard JSON envelope

```json
{
    "status": true,
    "message": "Success message",
    "errors": null,
    "data": {}
}
```

## Database Design

Main entities:

- `users`
- `customers`
- `medications`
- `orders`
- `order_items`
- `alerts` (audit)

Important indexing decisions:

- `medications.lot_number` indexed
- `orders.purchase_date` indexed
- FK columns indexed by design
- composite index in `medications` (`lot_number`, `name`)

## Local Setup

1. Install backend dependencies

```bash
composer install
```

2. Install frontend dependencies

```bash
npm install
```

3. Configure environment

```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

4. Configure database connection in `.env` (`DB_*` variables)

5. Run migrations + seed

```bash
php artisan migrate:fresh --seed
```

6. Run development flow (Laravel + queue + Vite)

```bash
composer run dev
```

7. Open module UI

- `http://localhost:8000/pharmacovigilance/login`

## Docker Setup

1. Build and run containers

```bash
docker compose up --build
```

2. Run migrations and seeders inside app container

```bash
docker compose exec app php artisan migrate:fresh --seed
```

3. Access app and Vite

- App: `http://localhost:8000/pharmacovigilance/login`
- Vite (dev server): `http://localhost:5173`

## Demo Credentials

Documented seeded account for test/demo:

- Username: `pv_admin`
- Password: `123456`

## Email Notes

Email alerts are sent using `PharmacovigilanceAlertMail` and include:

- warning context
- medication details
- affected lot number
- recommended action

For local testing, configure mailer in `.env`.

## Assumptions and Tradeoffs

- JWT token authentication is used for module APIs.
- Frontend is implemented as one module page with component-based state, not a multi-route SPA.
- Bulk alerts are implemented through `/api/alerts/send-bulk`.
- CSV export is implemented through `/api/orders/export/csv`.
- Duplicate alerts for same `order_id + customer_id + lot_number` are prevented by default and logged as `skipped_duplicate`.
- Pharmacovigilance operational endpoints require `admin` role (`auth:api` + `role:admin`).
- Alert logging is included through a lightweight `alerts` table.

## Bonus Features Status

- Bulk alerting: Implemented
- Export results (CSV): Implemented
- Role-based access: Implemented (admin-only)
- Unit/Feature tests: Implemented
- Docker setup: Implemented

## 📝 Licencia

Este proyecto está bajo la licencia MIT.

## 📧 Contacto

Para preguntas o soporte, contacta a diegoyamaa@gmail.com.

🎉 ¡Prueba Laravel 2026-abril!
