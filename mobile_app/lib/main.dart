import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'config/app_config.dart';
import 'screens/splash_screen.dart';
import 'services/api_service.dart';

@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
}

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Initialize Firebase (safely handles when google-services.json is attached)
  try {
    await Firebase.initializeApp();
    FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);

    // Request Notification Permissions
    final messaging = FirebaseMessaging.instance;
    await messaging.requestPermission(alert: true, badge: true, sound: true);

    // Get Device FCM Token and update to backend
    final fcmToken = await messaging.getToken();
    if (fcmToken != null) {
      await ApiService.updateFcmToken(fcmToken);
    }
  } catch (e) {
    debugPrint('Firebase initialization note: $e');
  }

  runApp(const VisionSmartApp());
}

class VisionSmartApp extends StatelessWidget {
  const VisionSmartApp({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: AppConfig.appName,
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        useMaterial3: true,
        primaryColor: AppConfig.primaryColor,
        scaffoldBackgroundColor: AppConfig.backgroundLight,
        textTheme: GoogleFonts.interTextTheme(Theme.of(context).textTheme),
        colorScheme: ColorScheme.fromSeed(
          seedColor: AppConfig.primaryColor,
          primary: AppConfig.primaryColor,
        ),
        appBarTheme: const AppBarTheme(
          backgroundColor: Colors.white,
          foregroundColor: Color(0xFF0F172A),
          elevation: 0,
        ),
      ),
      home: const SplashScreen(),
    );
  }
}
