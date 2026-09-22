<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Credentials & Real-Time Sync Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Firebase Cloud Firestore, Realtime Database, and FCM
    | Push Notifications.
    |
    */

    'project_id' => env('FIREBASE_PROJECT_ID', ''),
    'database_url' => env('FIREBASE_DATABASE_URL', ''),
    'api_key' => env('FIREBASE_API_KEY', ''),
    'server_key' => env('FIREBASE_SERVER_KEY', ''),
    'credentials_file' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase-credentials.json')),
    'enabled' => env('FIREBASE_ENABLED', false),
];
