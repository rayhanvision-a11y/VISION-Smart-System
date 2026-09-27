import 'dart:async';
import 'package:flutter/foundation.dart';
import 'api_service.dart';
import 'storage_service.dart';

class OfflineService {
  static final OfflineService instance = OfflineService._();
  OfflineService._();

  final ValueNotifier<bool> isOnlineNotifier = ValueNotifier<bool>(true);
  final ValueNotifier<int> pendingCountNotifier = ValueNotifier<int>(0);

  Timer? _checkTimer;
  bool _isSyncing = false;

  void init() {
    _updatePendingCount();
    _checkConnectivity();
    _checkTimer?.cancel();
    _checkTimer = Timer.periodic(const Duration(seconds: 12), (_) => _checkConnectivity());
  }

  void dispose() {
    _checkTimer?.cancel();
  }

  Future<void> _updatePendingCount() async {
    final queue = await StorageService.getOfflineQueue();
    pendingCountNotifier.value = queue.length;
  }

  Future<void> _checkConnectivity() async {
    try {
      final res = await ApiService.refreshCurrentUser();
      if (!isOnlineNotifier.value) {
        isOnlineNotifier.value = true;
        // Network restored, attempt auto sync
        syncPending();
      }
    } catch (_) {
      isOnlineNotifier.value = false;
    }
  }

  Future<void> enqueueAction({
    required String type, // e.g. 'update_ticket_status', 'reply_ticket', 'create_ticket'
    required Map<String, dynamic> data,
    required String description,
  }) async {
    final action = {
      'id': DateTime.now().millisecondsSinceEpoch.toString(),
      'type': type,
      'data': data,
      'description': description,
      'timestamp': DateTime.now().toIso8601String(),
    };
    await StorageService.addToOfflineQueue(action);
    await _updatePendingCount();
  }

  Future<int> syncPending() async {
    if (_isSyncing) return 0;
    _isSyncing = true;

    final queue = await StorageService.getOfflineQueue();
    if (queue.isEmpty) {
      _isSyncing = false;
      return 0;
    }

    int syncedCount = 0;
    final remaining = <Map<String, dynamic>>[];

    for (final action in queue) {
      final type = action['type'] as String?;
      final data = action['data'] as Map<String, dynamic>? ?? {};

      bool success = false;
      try {
        if (type == 'update_ticket_status') {
          final ticketId = data['ticket_id'] as int;
          final status = data['status'] as String;
          success = await ApiService.updateTicketStatus(ticketId, status);
        } else if (type == 'reply_ticket') {
          final ticketId = data['ticket_id'] as int;
          final message = data['message'] as String;
          final res = await ApiService.addMessage(ticketId, message);
          success = res['success'] == true;
        }
      } catch (e) {
        debugPrint('Failed to sync offline item: $e');
        success = false;
      }

      if (success) {
        syncedCount++;
      } else {
        remaining.add(action);
      }
    }

    await StorageService.clearOfflineQueue();
    for (final rem in remaining) {
      await StorageService.addToOfflineQueue(rem);
    }

    await _updatePendingCount();
    _isSyncing = false;
    return syncedCount;
  }
}
