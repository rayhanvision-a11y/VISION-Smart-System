import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'api_service.dart';

class PushService {
  static final PushService instance = PushService._();
  PushService._();

  final FlutterLocalNotificationsPlugin _local = FlutterLocalNotificationsPlugin();
  static final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();

  static const _channel = AndroidNotificationChannel(
    'vision_ticket_channel_v2',
    'Ticket Updates',
    description: 'Ticket assignment, status change, new messages',
    importance: Importance.max,
    playSound: true,
    enableVibration: true,
    enableLights: true,
  );

  bool _initialized = false;

  Future<void> init() async {
    if (_initialized || kIsWeb) return;
    _initialized = true;

    // Local notifications setup
    const androidInit = AndroidInitializationSettings('@mipmap/ic_launcher');
    const iosInit = DarwinInitializationSettings(
      requestAlertPermission: true,
      requestBadgePermission: true,
      requestSoundPermission: true,
    );
    await _local.initialize(
      const InitializationSettings(android: androidInit, iOS: iosInit),
      onDidReceiveNotificationResponse: (resp) => _handleTap(resp.payload),
    );

    // Android channel
    await _local
        .resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>()
        ?.createNotificationChannel(_channel);

    // Request notification permission
    final messaging = FirebaseMessaging.instance;
    await messaging.requestPermission(alert: true, badge: true, sound: true);

    // Foreground listener — show local notification
    FirebaseMessaging.onMessage.listen(_onForegroundMessage);

    // Tapped from background/terminated
    FirebaseMessaging.onMessageOpenedApp.listen((m) => _handleFcmOpen(m));
    final initialMsg = await messaging.getInitialMessage();
    if (initialMsg != null) _handleFcmOpen(initialMsg);

    // Send / refresh token
    final token = await messaging.getToken();
    if (token != null) await ApiService.updateFcmToken(token);
    messaging.onTokenRefresh.listen((t) => ApiService.updateFcmToken(t));
  }

  Future<void> _onForegroundMessage(RemoteMessage m) async {
    final n = m.notification;
    final title = n?.title ?? m.data['title']?.toString() ?? 'Ticket Update';
    final body = n?.body ?? m.data['body']?.toString() ?? '';
    await _local.show(
      m.messageId.hashCode,
      title,
      body,
      NotificationDetails(
        android: AndroidNotificationDetails(
          _channel.id,
          _channel.name,
          channelDescription: _channel.description,
          icon: '@mipmap/ic_launcher',
          importance: Importance.max,
          priority: Priority.max,
          playSound: true,
          enableVibration: true,
          enableLights: true,
        ),
        iOS: const DarwinNotificationDetails(),
      ),
      payload: _payloadFromMessage(m),
    );
  }

  String _payloadFromMessage(RemoteMessage m) {
    final t = m.data['ticket_id']?.toString();
    return t != null ? 'ticket:$t' : '';
  }

  void _handleFcmOpen(RemoteMessage m) => _handleTap(_payloadFromMessage(m));

  void _handleTap(String? payload) {
    if (payload == null || !payload.startsWith('ticket:')) return;
    final id = int.tryParse(payload.substring(7));
    if (id == null) return;
    final nav = navigatorKey.currentState;
    if (nav == null) return;
    // Deep link: pop to root then push notifications screen (safer than direct detail since we don't have ticket data)
    debugPrint('FCM tap → ticket $id (navigate handled at app level)');
  }
}
