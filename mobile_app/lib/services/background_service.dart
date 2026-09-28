import 'dart:async';
import 'dart:ui';

import 'package:flutter/foundation.dart';
import 'package:flutter_background_service/flutter_background_service.dart';
import 'package:flutter_background_service_android/flutter_background_service_android.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:geolocator/geolocator.dart';

import 'api_service.dart';
import 'storage_service.dart';

/// Keeps location updates and notification polling alive even when the app
/// is swiped away. Runs a foreground service on Android with a persistent
/// notification (required by Android 10+ for background location).
class BgService {
  static const String _channelId = 'vision_bg_channel';
  static const String _channelName = 'VISION Background Sync';
  static const int _fgNotificationId = 8888;

  /// Interval between location pushes to the backend (5 minutes).
  static const Duration locationInterval = Duration(minutes: 5);

  /// Interval between notification polls (30 seconds — feels realtime).
  static const Duration notifyInterval = Duration(seconds: 30);

  static Future<void> init() async {
    final service = FlutterBackgroundService();

    // Ensure the notification channel exists so the foreground notification
    // shows correctly on Android 8+ (otherwise service silently fails).
    final local = FlutterLocalNotificationsPlugin();
    const androidChannel = AndroidNotificationChannel(
      _channelId,
      _channelName,
      description: 'Keeps location and notifications syncing in the background.',
      importance: Importance.low, // low = silent persistent notification
    );
    try {
      await local
          .resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>()
          ?.createNotificationChannel(androidChannel);
    } catch (_) {}

    await service.configure(
      androidConfiguration: AndroidConfiguration(
        onStart: _onStart,
        autoStart: false,
        isForegroundMode: true,
        notificationChannelId: _channelId,
        initialNotificationTitle: 'VISION Smart System',
        initialNotificationContent: 'Syncing location & notifications…',
        foregroundServiceNotificationId: _fgNotificationId,
      ),
      iosConfiguration: IosConfiguration(
        autoStart: false,
        onForeground: _onStart,
        onBackground: _onIosBackground,
      ),
    );
  }

  static Future<void> start() async {
    final service = FlutterBackgroundService();
    final running = await service.isRunning();
    if (!running) {
      await service.startService();
    }
  }

  static Future<void> stop() async {
    final service = FlutterBackgroundService();
    service.invoke('stopService');
  }

  static Future<bool> isRunning() async {
    return FlutterBackgroundService().isRunning();
  }
}

/// Entry point that runs in a separate isolate. Cannot access UI state.
@pragma('vm:entry-point')
void _onStart(ServiceInstance service) async {
  // Required so plugins that use BackgroundIsolateBinaryMessenger work.
  DartPluginRegistrant.ensureInitialized();

  if (service is AndroidServiceInstance) {
    service.on('setAsForeground').listen((_) => service.setAsForegroundService());
    service.on('setAsBackground').listen((_) => service.setAsBackgroundService());
  }
  service.on('stopService').listen((_) => service.stopSelf());

  // Track last-known IDs so we only show alerts for genuinely new notifications
  final Set<int> knownIds = <int>{};
  bool firstPollDone = false;

  Future<void> pushLocation() async {
    try {
      final user = await StorageService.getUser();
      if (user == null) return;

      final serviceOn = await Geolocator.isLocationServiceEnabled();
      if (!serviceOn) return;

      final perm = await Geolocator.checkPermission();
      if (perm == LocationPermission.denied ||
          perm == LocationPermission.deniedForever) {
        return;
      }

      Position? pos;
      try {
        pos = await Geolocator.getCurrentPosition(
          desiredAccuracy: LocationAccuracy.high,
          timeLimit: const Duration(seconds: 20),
        );
      } catch (_) {
        pos = await Geolocator.getLastKnownPosition();
      }
      if (pos == null) return;

      await ApiService.updateLocation(
        lat: pos.latitude,
        lng: pos.longitude,
        accuracy: pos.accuracy.round(),
        speed: pos.speed,
      );
      debugPrint('[BgService] pushed ${pos.latitude},${pos.longitude}');

      if (service is AndroidServiceInstance) {
        service.setForegroundNotificationInfo(
          title: 'VISION Smart System',
          content: 'Last location sent · ${DateTime.now().toString().substring(11, 16)}',
        );
      }
    } catch (e) {
      debugPrint('[BgService] pushLocation error: $e');
    }
  }

  Future<void> pollNotifications() async {
    try {
      final user = await StorageService.getUser();
      if (user == null) return;

      final data = await ApiService.getNotifications();
      final list = ((data['notifications'] as List?) ?? [])
          .whereType<Map>()
          .map((e) => Map<String, dynamic>.from(e))
          .toList();

      if (!firstPollDone) {
        for (final item in list) {
          final id = item['id'] as int?;
          if (id != null) knownIds.add(id);
        }
        firstPollDone = true;
        return;
      }

      final local = FlutterLocalNotificationsPlugin();
      for (final item in list) {
        final id = item['id'] as int?;
        final isRead = item['is_read'] as bool? ?? false;
        if (id != null && !isRead && !knownIds.contains(id)) {
          knownIds.add(id);
          final message = (item['message'] as String?) ?? 'New ticket update';
          await local.show(
            id,
            'VISION Ticket Alert',
            message,
            const NotificationDetails(
              android: AndroidNotificationDetails(
                'vision_ticket_channel_v3',
                'Ticket Updates',
                channelDescription: 'Ticket assignment, status change, new messages',
                icon: '@mipmap/ic_launcher',
                importance: Importance.max,
                priority: Priority.max,
                playSound: true,
                enableVibration: true,
              ),
            ),
          );
        }
      }
    } catch (e) {
      debugPrint('[BgService] pollNotifications error: $e');
    }
  }

  // Kick off both loops immediately so the very first tick fires now.
  pushLocation();
  pollNotifications();

  Timer.periodic(BgService.locationInterval, (_) => pushLocation());
  Timer.periodic(BgService.notifyInterval, (_) => pollNotifications());
}

@pragma('vm:entry-point')
Future<bool> _onIosBackground(ServiceInstance service) async {
  DartPluginRegistrant.ensureInitialized();
  return true;
}
