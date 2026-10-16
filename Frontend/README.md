# MindEase React UI

The web client for MindEase. It recreates the MindEase screens with React,
React Router, CSS, and Lucide icons, and talks to the Laravel API in
`../../Backend/`.

## Screens

1. Welcome
2. Login
3. Register
4. Home
5. Mental Health Information
6. Self-Screening
7. Mood Tracker
8. Journal
9. Goals
10. Self-Care
11. My Progress
12. Get Support

## Run

```bash
npm install
cp .env.example .env   # skip if .env already exists
npm run dev
```

Then open the local Vite URL (default http://localhost:5173).

## Configuration

The API location comes from `VITE_API_BASE_URL` (defaults to
`http://127.0.0.1:8000/api`). The backend's `CORS_ALLOWED_ORIGINS` must include
this app's origin, e.g. `http://localhost:5173`.

## Notes

- Auth tokens are kept in `localStorage` and sent as `Authorization: Bearer`
  headers (see `src/api.js`).
- The UI is responsive and uses a mobile-phone presentation on larger screens.
- The archived, non-functional prototype lives in the repository's `legacy/`
  directory; do not use it for current work.
