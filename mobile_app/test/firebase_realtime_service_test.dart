import 'package:flutter_test/flutter_test.dart';
import 'package:vision_smart_system/services/firebase_realtime_service.dart';

/// Regression guard for the blank ticket detail screen.
///
/// The Android build ships without `google-services.json`, so
/// `Firebase.initializeApp()` fails on device and `Firebase.apps` stays empty.
/// The old service built its FirebaseDatabase in a `static final` initialiser,
/// which threw `[core/no-app]`. That throw happened inside
/// `TicketDetailScreen.build()`, and a throw in build() renders a blank screen
/// in release builds.
///
/// No Firebase is initialised in this test environment, so it reproduces the
/// exact on-device condition: these accessors must return empty streams instead
/// of throwing.
void main() {
  group('FirebaseRealtimeService without an initialised Firebase app', () {
    test('getTicketMessagesStream returns an empty stream instead of throwing', () async {
      late Stream stream;
      expect(() => stream = FirebaseRealtimeService.getTicketMessagesStream(123), returnsNormally);
      expect(await stream.isEmpty, isTrue);
    });

    test('getTicketsStream returns an empty stream instead of throwing', () async {
      late Stream stream;
      expect(() => stream = FirebaseRealtimeService.getTicketsStream(), returnsNormally);
      expect(await stream.isEmpty, isTrue);
    });

    test('isAvailable reports false', () {
      expect(FirebaseRealtimeService.isAvailable, isFalse);
    });

    test('parseMessages tolerates junk payloads', () {
      expect(FirebaseRealtimeService.parseMessages(null, 1), isEmpty);
      expect(FirebaseRealtimeService.parseMessages('not-a-map', 1), isEmpty);
      expect(FirebaseRealtimeService.parseMessages({'a': 'not-a-map'}, 1), isEmpty);
    });
  });
}
