// Firebase configuration for production project (printing-studio-app-4e0e6)
// This is the original/English version project
// Import the functions you need from the SDKs you need
import { initializeApp } from "firebase/app";
import { getAnalytics } from "firebase/analytics";

// Your web app's Firebase configuration for production project
// TODO: Replace with actual production Firebase config if different
const firebaseConfig = {
  // Add your production Firebase config here
  // apiKey: "YOUR_PRODUCTION_API_KEY",
  // authDomain: "printing-studio-app-4e0e6.firebaseapp.com",
  // projectId: "printing-studio-app-4e0e6",
  // storageBucket: "printing-studio-app-4e0e6.appspot.com",
  // messagingSenderId: "YOUR_MESSAGING_SENDER_ID",
  // appId: "YOUR_APP_ID",
  // measurementId: "YOUR_MEASUREMENT_ID"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const analytics = getAnalytics(app);

export { app, analytics };

