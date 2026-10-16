# Legacy

This directory holds archived code kept for reference only. Nothing here is
part of the active application and it is not wired to the backend.

## `mindease-react-pages/`

An early, static prototype of the MindEase UI. All screens use local component
state and hard-coded/mock data — it makes no API calls, has no auth, and is
missing pages that exist in the active app (Register, Goals).

The active frontend lives in `MindEase-React-with-env/frontend/` and talks to
the Laravel API in `Backend/`. Prefer that app for all current work.
