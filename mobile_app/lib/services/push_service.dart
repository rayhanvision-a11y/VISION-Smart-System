import 'dart:async';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import '../models/ticket.dart';
import '../screens/ticket_detail_screen.dart';
import 'api_service.dart';

@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  try {
    await Firebase.initializeApp();
    debugPrint('FCM Background Message Received: ${message.messageId}');
  } catch (_) {}
}

class PushService {
  static final PushService instance = PushService._();
  PushService._();

  final FlutterLocalNotificationsPlugin _local = FlutterLocalNotificationsPlugin();
  static final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();

  static final _channel = AndroidNotificationChannel(
    'vision_ticket_channel_v3',
    'Ticket Updates',
    description: 'Ticket assignment, status change, new messages',
    importance: Importance.max,
    playSound: true,
    enableVibration: true,
    vibrationPattern: Int64List.fromList([0, 500, 250, 500]),
    enableLights: true,
  );

  bool _initialized = false;
  Timer? _pollTimer;
  final Set<int> _knownNotificationIds = {};
  bool _firstPollDone = false;

  Future<void> init() async {
    if (_initialized || kIsWeb) return;
    _initialized = true;

    // Background handler for terminated / background state
    try {
      FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);
    } catch (_) {}

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

    // Android channel & Android 13+ runtime POST_NOTIFICATIONS permission
    try {
      final androidPlugin = _local.resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>();
      if (androidPlugin != null) {
        await androidPlugin.createNotificationChannel(_channel);
        await androidPlugin.requestNotificationsPermission();
      }
    } catch (e) {
      debugPrint('Android notification permission error: $e');
    }

    // Request notification permission with sound, alert, badge (safe if Firebase ready)
    try {
      if (Firebase.apps.isNotEmpty) {
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
        await sendTokenToServer();
        FirebaseMessaging.instance.onTokenRefresh.listen((t) => ApiService.updateFcmToken(t));
      }
    } catch (e) {
      debugPrint('FCM init safe note: $e');
    }

    // Start Real-time In-App / System notification poller
    startNotificationPolling();
  }

  void startNotificationPolling() {
    _pollTimer?.cancel();
    _checkNewNotifications();
    _pollTimer = Timer.periodic(const Duration(seconds: 10), (_) {
      _checkNewNotifications();
    });
  }

  void stopNotificationPolling() {
    _pollTimer?.cancel();
    _pollTimer = null;
  }

  Future<void> _checkNewNotifications() async {
    try {
      final data = await ApiService.getNotifications();
      final list = ((data['notifications'] as List?) ?? [])
          .whereType<Map>()
          .map((e) => Map<String, dynamic>.from(e))
          .toList();

      if (!_firstPollDone) {
        // First run: Seed known IDs so old notifications don't trigger alerts
        for (final item in list) {
          final id = item['id'] as int?;
          if (id != null) _knownNotificationIds.add(id);
        }
        _firstPollDone = true;
        return;
      }

      // Subsequent checks: Find new unread notifications
      for (final item in list) {
        final id = item['id'] as int?;
        final isRead = item['is_read'] as bool? ?? false;
        if (id != null && !isRead && !_knownNotificationIds.contains(id)) {
          _knownNotificationIds.add(id);
          final message = (item['message'] as String?) ?? 'New ticket update received';
          final ticketId = item['ticket_id'] as int?;

          await showNotificationDirectly(
            id,
            'VISION Ticket Alert',
            message,
            ticketId: ticketId,
          );
        }
      }
    } catch (e) {
      debugPrint('Notification poller check note: $e');
    }
  }

  Future<void> showNotificationDirectly(int id, String title, String body, {int? ticketId}) async {
    try {
      HapticFeedback.heavyImpact();
    } catch (_) {}

    await _local.show(
      id,
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
          vibrationPattern: Int64List.fromList([0, 500, 250, 500]),
          enableLights: true,
          fullScreenIntent: true,
        ),
        iOS: const DarwinNotificationDetails(
          presentAlert: true,
          presentBadge: true,
          presentSound: true,
        ),
      ),
      payload: ticketId != null ? 'ticket:$ticketId' : '',
    );
  }

  Future<void> sendTokenToServer() async {
    try {
      if (Firebase.apps.isNotEmpty) {
        final token = await FirebaseMessaging.instance.getToken();
        if (token != null && token.isNotEmpty) {
          await ApiService.updateFcmToken(token);
          debugPrint('FCM Token synced to server: ${token.substring(0, 10)}...');
        }
      }
    } catch (e) {
      debugPrint('sendTokenToServer error: $e');
    }
  }

  Future<void> _onForegroundMessage(RemoteMessage m) async {
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
          vibrationPattern: Int64List.fromList([0, 500, 250, 500]),
          enableLights: true,
          fullScreenIntent: true,
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
