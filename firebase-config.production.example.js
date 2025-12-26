// Firebase configuration example for production project
// Copy this file to firebase-config.production.js and fill in your actual Firebase config
// DO NOT commit firebase-config.production.js to git - it contains sensitive API keys

// Import the functions you need from the SDKs you need
import { initializeApp } from "firebase/app";
import { getAnalytics } from "firebase/analytics";

// Your web app's Firebase configuration for production project
const firebaseConfig = {
  apiKey: "YOUR_PRODUCTION_API_KEY",
  authDomain: "your-production-project.firebaseapp.com",
  projectId: "your-production-project-id",
  storageBucket: "your-production-project.appspot.com",
  messagingSenderId: "YOUR_MESSAGING_SENDER_ID",
  appId: "YOUR_APP_ID",
  measurementId: "YOUR_MEASUREMENT_ID"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const analytics = getAnalytics(app);

export { app, analytics };

