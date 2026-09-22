# VISION Smart System - Mobile Application (Flutter)

Cross-platform Mobile Application for **VISION Smart System Helpdesk & ISP Ticketing**.

Built with **Flutter**, **Laravel Sanctum REST API**, and **Firebase Realtime Database / Cloud Messaging (FCM)**.

---

## 🌟 Key Features

1. **2-Way Real-Time Data Sync**:
   - Updates made on the web portal reflect instantly in the mobile app via Firebase.
   - Tickets created, updated, or replied to from the app save to MySQL and update the web portal simultaneously.
2. **Role-Scoped Access**:
   - Super Admin, Admin, NOC, Call Center, and Reseller access rules match the website exactly.
3. **Live Chat & Messaging**:
   - Instant ticket replies, internal notes filter, and status transitions.
4. **Push Notifications (FCM)**:
   - Technicians receive push notifications when assigned a new ticket or receiving customer updates.

---

## 🚀 How to Run & Build

### 1. Prerequisites
- [Flutter SDK](https://docs.flutter.dev/get-started/install) installed (Dart 3+)
- Android Studio / Xcode

### 2. Setup
```bash
cd mobile_app
flutter pub get
```

### 3. Connect Firebase to Mobile App
1. In [Firebase Console](https://console.firebase.google.com/), go to **Project Overview** -> Click **+ Add app** -> Select **Android** (`com.visiontech.tickets`).
2. Download `google-services.json` and place it in `android/app/google-services.json`.
3. (Optional) For iOS, download `GoogleService-Info.plist` and place it in `ios/Runner/GoogleService-Info.plist`.

### 4. Configure Backend URL
In `lib/config/app_config.dart`, update the `apiBaseUrl` to your production domain:
```dart
static const String apiBaseUrl = 'https://portal.visiontech.com.bd/api';
```

### 5. Run Locally on Device or Emulator
```bash
flutter run
```

### 6. Build Android APK
```bash
flutter build apk --release
```
The generated APK will be located at:
`build/app/outputs/flutter-apk/app-release.apk`
