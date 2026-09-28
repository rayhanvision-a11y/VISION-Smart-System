import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'config/app_config.dart';
import 'theme/app_theme.dart';
import 'screens/splash_screen.dart';
import 'services/app_state.dart';
import 'services/background_service.dart';
import 'services/push_service.dart';
import 'services/storage_service.dart';

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
      try {
        await Firebase.initializeApp();
        FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);
      } catch (e) {
        debugPrint('Firebase native init safe note: $e');
      }
      await PushService.instance.init();
    }
  } catch (e) {
    debugPrint('Firebase safe fallback note: $e');
  }

  // Register background service (foreground service on Android). Only start
  // it if user is already logged in — otherwise wait until login completes.
  try {
    await BgService.init();
    final token = await StorageService.getToken();
    if (token != null && token.isNotEmpty) {
      await BgService.start();
    }
  } catch (e) {
    debugPrint('BgService init note: $e');
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
        navigatorKey: PushService.navigatorKey,
        home: const SplashScreen(),
      ),
    );
  }
}

