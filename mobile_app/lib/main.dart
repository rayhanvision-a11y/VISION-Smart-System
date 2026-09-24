import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'config/app_config.dart';
import 'theme/app_theme.dart';
import 'screens/splash_screen.dart';
import 'services/api_service.dart';
import 'services/app_state.dart';

@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
}

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await AppState.instance.load();

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
    return AnimatedBuilder(
      animation: AppState.instance,
      builder: (_, __) => MaterialApp(
        title: AppConfig.appName,
        debugShowCheckedModeBanner: false,
        theme: buildAppTheme(),
        darkTheme: buildDarkTheme(),
        themeMode: AppState.instance.themeMode,
        locale: AppState.instance.locale,
        home: const SplashScreen(),
      ),
    );
  }
}
