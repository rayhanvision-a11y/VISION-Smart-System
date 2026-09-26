import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:vision_smart_system/models/ticket.dart';
import 'package:vision_smart_system/screens/ticket_detail_screen.dart';

/// The reported bug: after installing the release APK, opening a ticket (from
/// the list or by tapping a notification) showed a completely blank screen.
///
/// Cause: the screen called into Firebase from inside build(), and Firebase is
/// never initialised on device (no google-services.json), so the call threw.
/// A throw inside build() renders a blank screen in release builds.
///
/// Firebase is not initialised in this test environment either, so pumping the
/// screen here reproduces the on-device condition exactly.
void main() {
  setUp(() {
    SharedPreferences.setMockInitialValues({});
  });

  testWidgets('ticket detail renders without Firebase and is not blank', (tester) async {
    final ticket = TicketModel(
      id: 42,
      ticketKey: '260920001',
      title: 'Fiber cut in Mirpur',
      description: '<p>Customer reports <b>no link</b> since 9am.</p>',
      priority: 'high',
      status: 'in_progress',
      creatorName: 'Reseller One',
    );

    await tester.pumpWidget(MaterialApp(home: TicketDetailScreen(ticket: ticket)));
    await tester.pump();

    // The screen built at all — no exception escaped build().
    expect(tester.takeException(), isNull);

    // And it actually painted the ticket, rather than an empty container.
    expect(find.text('Fiber cut in Mirpur'), findsWidgets);
    expect(find.text('#260920001'), findsOneWidget);
    expect(find.textContaining('no link since 9am'), findsOneWidget);
    expect(find.text('Reseller One'), findsOneWidget);
  });

  testWidgets('repeated rebuilds do not resubscribe or throw', (tester) async {
    final ticket = TicketModel(
      id: 7,
      ticketKey: '260920007',
      title: 'Router replacement',
      description: 'Swap ONU',
      priority: 'medium',
      status: 'pending',
    );

    await tester.pumpWidget(MaterialApp(home: TicketDetailScreen(ticket: ticket)));
    await tester.pump();

    // Toggle the details panel a few times to force rebuilds; the message
    // stream must stay the one built in initState.
    for (var i = 0; i < 3; i++) {
      await tester.tap(find.byTooltip('Toggle Details'));
      await tester.pump();
    }

    expect(tester.takeException(), isNull);
    expect(find.text('Router replacement'), findsWidgets);
  });
}
