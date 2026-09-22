import 'package:firebase_database/firebase_database.dart';
import '../config/app_config.dart';
import '../models/ticket_message.dart';

class FirebaseRealtimeService {
  static final FirebaseDatabase _db = FirebaseDatabase.instanceFor(
    app: FirebaseDatabase.instance.app,
    databaseURL: AppConfig.firebaseDbUrl,
  );

  // Stream of tickets from Firebase Realtime Database
  static Stream<DatabaseEvent> getTicketsStream() {
    return _db.ref('tickets').onValue;
  }

  // Stream of messages for a specific ticket
  static Stream<DatabaseEvent> getTicketMessagesStream(int ticketId) {
    return _db.ref('tickets/$ticketId/messages').onValue;
  }

  // Convert snapshot map to List of TicketMessageModel
  static List<TicketMessageModel> parseMessages(dynamic value, int ticketId) {
    if (value == null || value is! Map) return [];
    final List<TicketMessageModel> list = [];
    value.forEach((key, val) {
      if (val is Map) {
        list.add(TicketMessageModel.fromFirebase(key.toString(), val));
      }
    });
    list.sort((a, b) => (a.createdAt ?? '').compareTo(b.createdAt ?? ''));
    return list;
  }
}
