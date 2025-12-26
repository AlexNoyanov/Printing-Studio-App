// Firebase configuration for Russian project (d-print-electrozavodskaya)
// This is the new Russian version project
// Import the functions you need from the SDKs you need
import { initializeApp } from "firebase/app";
import { getAnalytics } from "firebase/analytics";

// Your web app's Firebase configuration
// For Firebase JS SDK v7.20.0 and later, measurementId is optional
const firebaseConfig = {
  apiKey: "AIzaSyBANwQPBUtI-hnTDuaG-DJnF8QKTPShvFY",
  authDomain: "d-print-electrozavodskaya.firebaseapp.com",
  projectId: "d-print-electrozavodskaya",
  storageBucket: "d-print-electrozavodskaya.firebasestorage.app",
  messagingSenderId: "322819333872",
  appId: "1:322819333872:web:0f59e42be9e3d850ed2c35",
  measurementId: "G-NZK52XS288"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const analytics = getAnalytics(app);

export { app, analytics };

