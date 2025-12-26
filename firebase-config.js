// Firebase configuration - default to Russian project
// This file is kept for backward compatibility
// For specific projects, use:
// - firebase-config.production.js (for printing-studio-app-4e0e6)
// - firebase-config.russian.js (for d-print-electrozavodskaya)

// Re-export from Russian config as default
import { app, analytics } from './firebase-config.russian.js';
export { app, analytics };

