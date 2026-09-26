import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../services/api_service.dart';
import '../services/app_state.dart';
import '../theme/app_theme.dart';
import '../widgets/common/empty_state.dart';
import '../widgets/common/skeleton.dart';
import 'ticket_detail_screen.dart';
import '../models/ticket.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({Key? key}) : super(key: key);

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  bool _isLoading = true;
  List<Map<String, dynamic>> _items = [];
  int _unread = 0;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _isLoading = true);
    final data = await ApiService.getNotifications();
    if (!mounted) return;
    setState(() {
      _items = ((data['notifications'] as List?) ?? [])
          .whereType<Map>()
          .map((e) => Map<String, dynamic>.from(e))
          .toList();
      _unread = (data['unread_count'] as int?) ?? 0;
      _isLoading = false;
    });
  }

  Future<void> _markAll() async {
    final ok = await ApiService.markAllNotificationsRead();
    if (ok) _load();
  }

  Future<void> _openTicket(int notifId, int? ticketId, String? message) async {
    await ApiService.markNotificationRead(notifId);
    if (!mounted) return;

    if (ticketId != null) {
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (_) => const Center(child: CircularProgressIndicator()),
      );

      Map<String, dynamic>? details;
      try {
        details = await ApiService.getTicketDetails(ticketId);
      } catch (e) {
        debugPrint('Error loading ticket details: $e');
      }

      if (mounted) Navigator.pop(context); // Dismiss loading dialog
      if (!mounted) return;

      TicketModel ticketToOpen;
      if (details != null && details['ticket'] is TicketModel) {
        ticketToOpen = details['ticket'] as TicketModel;
      } else {
        // Fallback ticket model so user can always open the ticket
        ticketToOpen = TicketModel(
          id: ticketId,
          ticketKey: '#$ticketId',
          title: message ?? 'Ticket #$ticketId',
          description: message ?? '',
          priority: 'medium',
          status: 'in_progress',
        );
      }

      Navigator.push(context, MaterialPageRoute(
        builder: (_) => TicketDetailScreen(ticket: ticketToOpen),
      )).then((_) => _load());
      return;
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(message ?? AppState.instance.t('Notification marked as read', 'নোটিফিকেশন পঠিত হিসেবে চিহ্নিত করা হয়েছে')),
        ),
      );
    }
    _load();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(AppState.instance.t('Notifications', 'নোটিফিকেশন'), style: AppText.h2),
            Text(
              _unread == 0
                  ? AppState.instance.t('All caught up', 'সব দেখা হয়েছে')
                  : '$_unread ${AppState.instance.t('unread', 'অপঠিত')}',
              style: AppText.label.copyWith(color: _unread > 0 ? AppColors.danger : AppColors.textMuted),
            ),
          ],
        ),
        actions: [
          if (_unread > 0)
            TextButton.icon(
              onPressed: _markAll,
              icon: const Icon(Icons.done_all_rounded, size: 18),
              label: Text(AppState.instance.t('Mark all', 'সব পড়ুন')),
            ),
          IconButton(icon: const Icon(Icons.refresh_rounded), onPressed: _load),
        ],
      ),
      body: _isLoading
          ? ListView.separated(
              padding: const EdgeInsets.all(AppSpacing.lg),
              itemCount: 5,
              separatorBuilder: (_, __) => const SizedBox(height: 10),
              itemBuilder: (_, __) => const SkeletonCard(height: 72),
            )
          : _items.isEmpty
              ? EmptyState(
                  icon: Icons.notifications_off_rounded,
                  title: AppState.instance.t('No notifications yet', 'কোনো নোটিফিকেশন নেই'),
                  subtitle: AppState.instance.t('Updates will appear here', 'আপডেট এখানে দেখাবে'),
                )
              : RefreshIndicator(
                  onRefresh: _load,
                  color: AppColors.primary,
                  child: ListView.separated(
                    padding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.md, AppSpacing.lg, AppSpacing.xxl),
                    itemCount: _items.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 8),
                    itemBuilder: (_, i) => _notifTile(_items[i]),
                  ),
                ),
    );
  }

  Widget _notifTile(Map<String, dynamic> n) {
    final isRead = n['is_read'] == true;
    int? ticketId;
    if (n['ticket_id'] != null) {
      ticketId = int.tryParse(n['ticket_id'].toString());
    }
    if (ticketId == null && n['message'] != null) {
      final msg = n['message'].toString();
      final match = RegExp(r'#(\d+)').firstMatch(msg);
      if (match != null) {
        ticketId = int.tryParse(match.group(1)!);
      }
    }
    final createdAt = n['created_at']?.toString();
    String timeText = '';
    if (createdAt != null) {
      try {
        final dt = DateTime.parse(createdAt).toLocal();
        timeText = DateFormat('MMM d, h:mm a').format(dt);
      } catch (_) {}
    }
    return Material(
      color: isRead ? AppColors.card : AppColors.primary.withOpacity(0.06),
      borderRadius: BorderRadius.circular(AppRadius.md),
      child: InkWell(
        borderRadius: BorderRadius.circular(AppRadius.md),
        onTap: () { final nid = int.tryParse(n['id']?.toString() ?? '') ?? 0; _openTicket(nid, ticketId, n['message']?.toString()); },
        child: Container(
          padding: const EdgeInsets.all(AppSpacing.md),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(AppRadius.md),
            border: Border.all(color: isRead ? AppColors.border : AppColors.primary.withOpacity(0.3)),
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 36, height: 36,
                decoration: BoxDecoration(
                  color: isRead ? AppColors.bg : AppColors.primary.withOpacity(0.15),
                  borderRadius: BorderRadius.circular(AppRadius.sm),
                ),
                child: Icon(
                  isRead ? Icons.notifications_none_rounded : Icons.notifications_active_rounded,
                  color: isRead ? AppColors.textMuted : AppColors.primary,
                  size: 18,
                ),
              ),
              const SizedBox(width: AppSpacing.md),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      n['message']?.toString() ?? '',
                      style: AppText.body.copyWith(
                        fontSize: 13,
                        fontWeight: isRead ? FontWeight.w500 : FontWeight.w700,
                      ),
                    ),
                    if (timeText.isNotEmpty) ...[
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          Icon(Icons.calendar_today_rounded, size: 10, color: AppColors.textMuted),
                          const SizedBox(width: 4),
                          Text(timeText, style: AppText.label.copyWith(fontSize: 10)),
                        ],
                      ),
                    ],
                  ],
                ),
              ),
              if (!isRead)
                Container(width: 8, height: 8, decoration: BoxDecoration(
                  color: AppColors.danger, shape: BoxShape.circle,
                )),
            ],
          ),
        ),
      ),
    );
  }
}

