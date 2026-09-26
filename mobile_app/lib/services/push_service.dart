import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import '../models/ticket.dart';
import '../screens/ticket_detail_screen.dart';
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

    // Request notification permission with sound, alert, badge
    final messaging = FirebaseMessaging.instance;
    await messaging.requestPermission(
      alert: true,
      announcement: true,
      badge: true,
      carPlay: false,
      criticalAlert: false,
      provisional: false,
      sound: true,
    );

    // Foreground listener — show local notification & trigger vibration
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
    // Physical device vibration on notification arrival
    try {
      HapticFeedback.vibrate();
    } catch (_) {}

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
          vibrationPattern: Int64List.fromList([0, 400, 200, 400]),
          enableLights: true,
        ),
        iOS: const DarwinNotificationDetails(
          presentAlert: true,
          presentBadge: true,
          presentSound: true,
        ),
      ),
      payload: _payloadFromMessage(m),
    );
  }

  String _payloadFromMessage(RemoteMessage m) {
    final t = m.data['ticket_id']?.toString();
    return t != null ? 'ticket:$t' : '';
  }

  void _handleFcmOpen(RemoteMessage m) => _handleTap(_payloadFromMessage(m));

  void _handleTap(String? payload) async {
    if (payload == null || !payload.startsWith('ticket:')) return;
    final id = int.tryParse(payload.substring(7));
    if (id == null) return;
    final nav = navigatorKey.currentState;
    if (nav == null) return;

    debugPrint('Push tap → opening ticket $id');
    try {
      final details = await ApiService.getTicketDetails(id);
      if (details != null && details['ticket'] is TicketModel) {
        nav.push(MaterialPageRoute(
          builder: (_) => TicketDetailScreen(ticket: details['ticket'] as TicketModel),
        ));
      } else {
        nav.push(MaterialPageRoute(
          builder: (_) => TicketDetailScreen(
            ticket: TicketModel(
              id: id,
              ticketKey: '#$id',
              title: 'Ticket #$id',
              description: '',
              priority: 'medium',
              status: 'in_progress',
            ),
          ),
        ));
      }
    } catch (e) {
      debugPrint('Error navigating to ticket from push tap: $e');
    }
  }
}
