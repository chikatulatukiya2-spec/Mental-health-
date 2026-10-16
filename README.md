# MindEase

MindEase is a mental-health self-care app: a **Laravel 13 REST API** with a
**React (Vite) web client**. It lets people check in on their mood, journal,
track goals, take a PHQ-9 self-screening, and find self-care and support
resources.

> **MindEase is not a medical device and does not provide diagnosis or
> treatment.** It is a self-help tool only. If you or someone else is in
> immediate danger, contact your local emergency number or a crisis line
> (the app's **Get Support** screen lists resources).

## Repository layout

| Path | What it is |
| --- | --- |
| `Backend/` | Laravel 13 API (PHP 8.3+) with token auth via Laravel Sanctum. |
| `MindEase-React-with-env/frontend/` | The active React + Vite client. Talks to `Backend/`. |
| `legacy/` | Archived early prototype, kept for reference only. Not wired to the API. |

## Features

- **Accounts** — register, log in, log out. Bearer-token auth (Laravel Sanctum).
- **Self-screening** — PHQ-9 questionnaire with scoring, severity label, and a
  self-harm flag that surfaces crisis guidance.
- **Mood tracker** — log how you feel; see the trend on the Progress screen.
- **Journal** — private, per-user entries you can edit and delete.
- **Goals** — create goals and mark them complete.
- **Progress** — a 7-day wellbeing score derived from recent moods, plus goal
  and screening summaries.
- **AI chat** — a rule-based companion that also detects crisis language and
  points to emergency help.
- **Resources** — mental-health information, self-care exercises, and support
  contacts.

Every user-owned record is scoped to its owner; requests for someone else's data
return `404`.

## Requirements

- **PHP 8.3+** with the `mbstring`, `dom`/`xml`, `pdo`, `openssl`, `tokenizer`,
  and `curl` extensions. Add `pdo_mysql` **or** `pdo_sqlite` depending on your
  database choice.
- **Composer 2**
- **Node.js 18+** and npm
- **MySQL/MariaDB** or **SQLite**

## Getting started

### 1. Backend API

```bash
cd Backend
composer install
cp .env.example .env
php artisan key:generate
```

Configure the database in `Backend/.env`:

- **SQLite (default, simplest):** leave `DB_CONNECTION=sqlite` and create the
  file once:
  ```bash
  touch database/database.sqlite
  ```
- **MySQL/MariaDB:** set
  ```dotenv
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=mindease
  DB_USERNAME=your_user
  DB_PASSWORD=your_password
  ```

Then run the migrations and start the server:

```bash
php artisan migrate
php artisan serve
```

The API is now served at **http://127.0.0.1:8000/api**.

### 2. Frontend client

```bash
cd MindEase-React-with-env/frontend
npm install
cp .env.example .env   # skip if .env already exists
npm run dev
```

The dev server runs at **http://localhost:5173**. It reads the API location from
`VITE_API_BASE_URL` (defaults to `http://127.0.0.1:8000/api`).

### 3. Connect the two

The backend only accepts browser requests from origins listed in
`CORS_ALLOWED_ORIGINS` (comma-separated), so make sure it includes the frontend
origin:

```dotenv
CORS_ALLOWED_ORIGINS=http://localhost:5173,http://127.0.0.1:5173
```

Restrict this to your real origin(s) in production.

## Environment variables

Key backend variables (see `Backend/.env.example` for the full list):

| Variable | Purpose |
| --- | --- |
| `APP_KEY` | Application encryption key (`php artisan key:generate`). |
| `APP_URL` | Public base URL of the API. |
| `DB_CONNECTION` / `DB_HOST` / `DB_PORT` / `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | Database connection. |
| `CORS_ALLOWED_ORIGINS` | Comma-separated browser origins allowed to call the API. |
| `SESSION_DRIVER` | Session store (defaults to `database`). |

Key frontend variables (see `MindEase-React-with-env/frontend/.env.example`):

| Variable | Purpose |
| --- | --- |
| `VITE_API_BASE_URL` | Base URL of the API, e.g. `http://127.0.0.1:8000/api`. |
| `VITE_APP_URL` | Public URL of the frontend. |

## Running the tests

The API has a feature-test suite:

```bash
cd Backend
php artisan test
# or
vendor/bin/phpunit
```

By default the suite runs against an **in-memory SQLite** database
(`Backend/phpunit.xml`) and uses a throwaway test `APP_KEY`, so it needs no
local `.env`. The tests cover authentication, ownership/isolation for every
user-owned resource, scoring logic, and the public endpoints.

## API overview

Base path: `/api`. Authenticated routes expect an
`Authorization: Bearer <token>` header.

**Public**

| Method | Path | Description |
| --- | --- | --- |
| `POST` | `/auth/register` | Create an account, returns a token. |
| `POST` | `/auth/login` | Exchange credentials for a token. |
| `GET` | `/information` | Mental-health topics (`?topic=` to filter). |
| `GET` | `/support` | Support and crisis resources. |
| `GET` | `/self-care` | Self-care resources (`?type=` to filter). |
| `GET` | `/self-care/{id}` | A single self-care resource. |
| `GET` | `/screening/questions` | The PHQ-9 questionnaire. |

**Authenticated**

| Method | Path | Description |
| --- | --- | --- |
| `GET` | `/auth/me` | The current user. |
| `POST` | `/auth/logout` | Revoke the current token. |
| `GET` / `POST` | `/moods` | List / create mood entries. |
| `GET` / `DELETE` | `/moods/{id}` | View / delete a mood entry. |
| `GET` / `POST` | `/journals` | List (paginated) / create journal entries. |
| `GET` / `PUT` / `DELETE` | `/journals/{id}` | View / update / delete an entry. |
| `GET` / `POST` | `/screenings` | List / submit a PHQ-9 screening. |
| `GET` | `/screenings/{id}` | View a screening result. |
| `GET` / `POST` | `/goals` | List / create goals. |
| `PUT` / `DELETE` | `/goals/{id}` | Update / delete a goal. |
| `GET` | `/progress` | Wellbeing score, mood series, and summaries. |
| `GET` | `/ai/history` | Chat history. |
| `POST` | `/ai/chat` | Send a message and get a reply. |

## License

Released for educational use. See the repository owner for licensing details.
