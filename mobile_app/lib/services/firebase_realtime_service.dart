import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_database/firebase_database.dart';
import 'package:flutter/foundation.dart';
import '../config/app_config.dart';
import '../models/ticket_message.dart';

/// Realtime Database access.
///
/// Firebase is optional: the Android build ships without `google-services.json`,
/// so `Firebase.initializeApp()` in main.dart can fail and leave `Firebase.apps`
/// empty. Every accessor here must therefore degrade to an empty stream instead
/// of throwing — a throw from here used to happen inside a widget `build()` and
/// blanked the whole ticket detail screen in release builds.
class FirebaseRealtimeService {
  static bool get isAvailable => Firebase.apps.isNotEmpty;

  static FirebaseDatabase? get _db {
    try {
      if (Firebase.apps.isEmpty) {
        return null;
      }
      return FirebaseDatabase.instanceFor(
        app: Firebase.app(),
        databaseURL: AppConfig.firebaseDbUrl,
      );
    } catch (e) {
      debugPrint('FirebaseRealtimeService DB init note: $e');
      return null;
    }
  }

  // Stream of tickets from Firebase Realtime Database
  static Stream<DatabaseEvent> getTicketsStream() {
    try {
      final db = _db;
      if (db == null) return const Stream<DatabaseEvent>.empty();
      return db.ref('tickets').onValue.handleError(
            (Object e) => debugPrint('Firebase tickets stream error: $e'),
          );
    } catch (e) {
      debugPrint('Firebase getTicketsStream note: $e');
      return const Stream<DatabaseEvent>.empty();
    }
  }

  // Stream of messages for a specific ticket
  static Stream<DatabaseEvent> getTicketMessagesStream(int ticketId) {
    try {
      final db = _db;
      if (db == null) return const Stream<DatabaseEvent>.empty();
      return db.ref('tickets/$ticketId/messages').onValue.handleError(
            (Object e) => debugPrint('Firebase messages stream error: $e'),
          );
    } catch (e) {
      debugPrint('Firebase getTicketMessagesStream note: $e');
      return const Stream<DatabaseEvent>.empty();
    }
  }

  // Convert snapshot map to List of TicketMessageModel
  static List<TicketMessageModel> parseMessages(dynamic value, int ticketId) {
    if (value == null || value is! Map) return [];
    final List<TicketMessageModel> list = [];
    try {
      value.forEach((key, val) {
        if (val is Map) {
          list.add(TicketMessageModel.fromFirebase(key.toString(), val));
        }
      });
      list.sort((a, b) => (a.createdAt ?? '').compareTo(b.createdAt ?? ''));
    } catch (e) {
      debugPrint('Firebase parseMessages note: $e');
    }
    return list;
  }
}
