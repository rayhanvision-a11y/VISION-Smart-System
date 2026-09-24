import 'package:flutter/material.dart';
import '../models/ticket.dart';
import '../services/api_service.dart';
import '../services/app_state.dart';
import '../theme/app_theme.dart';
import '../widgets/common/empty_state.dart';
import '../widgets/common/skeleton.dart';
import '../widgets/common/status_pill.dart';
import 'ticket_detail_screen.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({Key? key}) : super(key: key);

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  bool _isLoading = true;
  List<TicketModel> _tickets = [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _isLoading = true);
    final list = await ApiService.fetchTickets();
    if (!mounted) return;
    setState(() {
      _tickets = list;
      _isLoading = false;
    });
  }

  Map<String, List<TicketModel>> _groupByDay(List<TicketModel> tickets) {
    final Map<String, List<TicketModel>> groups = {};
    final now = DateTime.now();
    for (final t in tickets) {
      DateTime? dt;
      if (t.updatedAt != null) {
        dt = DateTime.tryParse(t.updatedAt!);
      }
      dt ??= t.createdAt != null ? DateTime.tryParse(t.createdAt!) : null;
      dt ??= now;
      final diff = now.difference(dt).inDays;
      String key;
      if (diff == 0) {
        key = 'Today';
      } else if (diff == 1) {
        key = 'Yesterday';
      } else if (diff < 7) {
        key = 'This week';
      } else {
        key = 'Earlier';
      }
      groups.putIfAbsent(key, () => []).add(t);
    }
    return groups;
  }

  @override
  Widget build(BuildContext context) {
    final groups = _groupByDay(_tickets);
    final order = ['Today', 'Yesterday', 'This week', 'Earlier'];
    String tr(String k) => AppState.instance.isBengali
        ? {'Today': 'আজ', 'Yesterday': 'গতকাল', 'This week': 'এই সপ্তাহ', 'Earlier': 'পূর্বে'}[k] ?? k
        : k;

    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(AppState.instance.t('Notifications', 'নোটিফিকেশন'), style: AppText.h2),
            Text(AppState.instance.t('${_tickets.length} recent updates', '${_tickets.length} টি সাম্প্রতিক আপডেট'),
                style: AppText.label.copyWith(color: AppColors.textMuted)),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            onPressed: _load,
          ),
        ],
      ),
      body: _isLoading
          ? ListView.separated(
              padding: const EdgeInsets.all(AppSpacing.lg),
              itemCount: 5,
              separatorBuilder: (_, __) => const SizedBox(height: 10),
              itemBuilder: (_, __) => const SkeletonCard(height: 90),
            )
          : _tickets.isEmpty
              ? EmptyState(
                  icon: Icons.notifications_off_rounded,
                  title: AppState.instance.t('No notifications yet', 'কোনো নোটিফিকেশন নেই'),
                  subtitle: AppState.instance.t('Ticket updates will appear here', 'টিকিট আপডেট এখানে দেখাবে'),
                )
              : RefreshIndicator(
                  onRefresh: _load,
                  color: AppColors.primary,
                  child: ListView(
                    padding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.md, AppSpacing.lg, AppSpacing.xxl),
                    children: [
                      for (final label in order)
                        if (groups[label] != null) ...[
                          Padding(
                            padding: const EdgeInsets.symmetric(vertical: AppSpacing.sm),
                            child: Row(
                              children: [
                                Text(tr(label), style: AppText.label),
                                const SizedBox(width: 8),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 1),
                                  decoration: BoxDecoration(
                                    color: AppColors.primary.withOpacity(0.1),
                                    borderRadius: BorderRadius.circular(AppRadius.pill),
                                  ),
                                  child: Text('${groups[label]!.length}',
                                      style: AppText.label.copyWith(color: AppColors.primary, fontSize: 10)),
                                ),
                              ],
                            ),
                          ),
                          ...groups[label]!.map(_notifCard),
                          const SizedBox(height: AppSpacing.sm),
                        ],
                    ],
                  ),
                ),
    );
  }

  Widget _notifCard(TicketModel t) {
    final sColor = AppColors.statusColor(t.status);
    IconData icon;
    String heading;
    switch (t.status.toLowerCase()) {
      case 'resolved':
        icon = Icons.check_circle_rounded;
        heading = 'Ticket resolved';
        break;
      case 'pending':
        icon = Icons.hourglass_top_rounded;
        heading = 'Ticket pending';
        break;
      case 'waiting_for_customer_feedback':
        icon = Icons.forum_rounded;
        heading = 'Waiting for feedback';
        break;
      default:
        icon = Icons.autorenew_rounded;
        heading = 'Ticket updated';
    }

    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Material(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        child: InkWell(
          borderRadius: BorderRadius.circular(AppRadius.lg),
          onTap: () => Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => TicketDetailScreen(ticket: t)),
          ),
          child: Container(
            padding: const EdgeInsets.all(AppSpacing.md),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(AppRadius.lg),
              border: Border.all(color: AppColors.border),
            ),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  width: 40, height: 40,
                  decoration: BoxDecoration(
                    color: sColor.withOpacity(0.12),
                    borderRadius: BorderRadius.circular(AppRadius.md),
                  ),
                  child: Icon(icon, color: sColor, size: 20),
                ),
                const SizedBox(width: AppSpacing.md),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Expanded(
                            child: Text(heading,
                                style: AppText.body.copyWith(fontWeight: FontWeight.w700)),
                          ),
                          Text('#${t.ticketKey}',
                              style: AppText.label.copyWith(color: AppColors.textMuted, fontSize: 10)),
                        ],
                      ),
                      const SizedBox(height: 3),
                      Text(t.title,
                          style: AppText.bodySm, maxLines: 2, overflow: TextOverflow.ellipsis),
                      const SizedBox(height: 6),
                      Row(
                        children: [
                          StatusPill(status: t.status),
                          const SizedBox(width: 6),
                          PriorityPill(priority: t.priority),
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
