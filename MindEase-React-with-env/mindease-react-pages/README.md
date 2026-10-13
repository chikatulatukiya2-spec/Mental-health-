# MindEase React UI

A cleaned and fixed React/Vite frontend for the MindEase mental-health screening, mood tracking, journal, self-care, support, progress, and AI wellness assistant concept.

## Included screens
- Welcome
- Login
- Home
- Mental Health Information
- Depression Screening
- Mood Tracker
- Journal
- Self-Care
- My Progress
- Get Support
- MindEase AI Assistant

## Run locally

Requirements: Node.js 18+ and npm.

```bash
npm install
npm run dev
```

For a production build:

```bash
npm run build
npm run preview
```

## Important notes
- This package intentionally does not include `node_modules`; run `npm install` after extracting it.
- Login is currently a frontend demo. Connect the submit handler to your backend authentication API when the API is ready.
- The AI assistant is a local demo conversation and does not call a real AI service.
- Screening content is for demonstration and is not a diagnosis or emergency service.
- Replace demo dates, profile name, support contact details, and content with verified project data before deployment.
