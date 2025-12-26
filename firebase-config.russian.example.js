// Firebase configuration example for Russian project
// Copy this file to firebase-config.russian.js and fill in your actual Firebase config
// DO NOT commit firebase-config.russian.js to git - it contains sensitive API keys

// Import the functions you need from the SDKs you need
import { initializeApp } from "firebase/app";
import { getAnalytics } from "firebase/analytics";

// Your web app's Firebase configuration for Russian project
const firebaseConfig = {
  apiKey: "YOUR_RUSSIAN_API_KEY",
  authDomain: "your-russian-project.firebaseapp.com",
  projectId: "your-russian-project-id",
  storageBucket: "your-russian-project.appspot.com",
  messagingSenderId: "YOUR_MESSAGING_SENDER_ID",
  appId: "YOUR_APP_ID",
  measurementId: "YOUR_MEASUREMENT_ID"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const analytics = getAnalytics(app);

export { app, analytics };

