import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'config/app_config.dart';
import 'screens/splash_screen.dart';
import 'screens/login_screen.dart';
import 'screens/main_navigation_screen.dart';
import 'services/api_service.dart';

@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
}

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Safely attempt Firebase initialization without stopping the app if it fails
  try {
    if (kIsWeb) {
      await Firebase.initializeApp(
        options: const FirebaseOptions(
          apiKey: "demo-key",
          appId: "1:1234567890:web:1234567890",
          messagingSenderId: "1234567890",
          projectId: "vision-smart-system",
        ),
      );
    } else {
      await Firebase.initializeApp();
      FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);

      final messaging = FirebaseMessaging.instance;
      await messaging.requestPermission(alert: true, badge: true, sound: true);

      final fcmToken = await messaging.getToken();
      if (fcmToken != null) {
        await ApiService.updateFcmToken(fcmToken);
      }
    }
  } catch (e) {
    debugPrint('Firebase safe fallback note: $e');
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

      // 💡 Android Studio Preview Tip:
      // আপনি যেই স্ক্রিনের সরাসরি প্রিভিউ দেখতে চান, নিচে সেটি সেট করুন:
      // 1. Full App Flow: const SplashScreen()
      // 2. Direct Login Page: const LoginScreen()
      // 3. Direct Dashboard Page: const MainNavigationScreen()
      home: const SplashScreen(),
    );
  }
}

