import 'dart:async';
import 'package:firebase_database/firebase_database.dart';
import 'package:flutter/material.dart';
import '../models/ticket.dart';
import '../models/user.dart';
import '../services/api_service.dart';
import '../services/firebase_realtime_service.dart';
import '../services/storage_service.dart';
import '../services/app_state.dart';
import '../theme/app_theme.dart';
import '../widgets/common/empty_state.dart';
import '../widgets/common/skeleton.dart';
import '../widgets/common/status_pill.dart';
import 'create_ticket_screen.dart';
import 'ticket_detail_screen.dart';

class TicketListScreen extends StatefulWidget {
  final String? initialStatus;
  const TicketListScreen({Key? key, this.initialStatus}) : super(key: key);

  @override
  State<TicketListScreen> createState() => _TicketListScreenState();
}

class _TicketListScreenState extends State<TicketListScreen> {
  UserModel? _currentUser;
  List<TicketModel> _tickets = [];
  bool _isLoading = true;
  late String _selectedFilter;
  String _search = '';
  StreamSubscription<DatabaseEvent>? _firebaseSub;
  final _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _selectedFilter = widget.initialStatus ?? 'all';
    _loadUserAndTickets();
    _listenToFirebaseRealtime();
  }

  @override
  void dispose() {
    _firebaseSub?.cancel();
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadUserAndTickets() async {
    final user = await StorageService.getUser();
    setState(() => _currentUser = user);
    await _refreshTickets();
  }

  Future<void> _refreshTickets() async {
    final list = await ApiService.fetchTickets(
      status: (_selectedFilter == 'all' || _selectedFilter == 'my_tickets') ? null : _selectedFilter,
      search: _search.isEmpty ? null : _search,
    );
    if (!mounted) return;

    List<TicketModel> filtered = list;
    if (_selectedFilter == 'my_tickets' && _currentUser != null) {
      filtered = list.where((t) =>
        t.assignedTo == _currentUser!.id || t.createdBy == _currentUser!.id).toList();
    }

    setState(() {
      _tickets = filtered;
      _isLoading = false;
    });
  }

  void _listenToFirebaseRealtime() {
    try {
      _firebaseSub = FirebaseRealtimeService.getTicketsStream().listen((event) {
        if (event.snapshot.value != null && mounted) _refreshTickets();
      });
    } catch (_) {}
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
            Text(AppState.instance.t('My Tickets', 'আমার টিকিট'), style: AppText.h2),
            Text(AppState.instance.t('${_tickets.length} results', '${_tickets.length} টি ফলাফল'),
                style: AppText.label.copyWith(color: AppColors.textMuted)),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            onPressed: _refreshTickets,
          ),
        ],
      ),
      body: Column(
        children: [
          _searchBar(),
          _filterChips(),
          Expanded(
            child: _isLoading
                ? _skeletonList()
                : _tickets.isEmpty
                    ? EmptyState(
                        icon: Icons.inbox_rounded,
                        title: 'No tickets found',
                        subtitle: _search.isNotEmpty
                            ? 'Try a different search term'
                            : 'Create your first ticket to get started',
                      )
                    : RefreshIndicator(
                        onRefresh: _refreshTickets,
                        color: AppColors.primary,
                        child: ListView.separated(
                          padding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.md, AppSpacing.lg, 100),
                          itemCount: _tickets.length,
                          separatorBuilder: (_, __) => const SizedBox(height: 10),
                          itemBuilder: (_, i) => _ticketCard(_tickets[i]),
                        ),
                      ),
          ),
        ],
      ),
    );
  }

  Widget _searchBar() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.md, AppSpacing.lg, AppSpacing.sm),
      child: TextField(
        controller: _searchController,
        decoration: InputDecoration(
          hintText: AppState.instance.t('Search by title or ticket #', 'খুঁজুন টাইটেল বা টিকিট #'),
          prefixIcon: const Icon(Icons.search_rounded, size: 20),
          suffixIcon: _search.isNotEmpty
              ? IconButton(
                  icon: const Icon(Icons.close_rounded, size: 18),
                  onPressed: () {
                    _searchController.clear();
                    setState(() => _search = '');
                    _refreshTickets();
                  },
                )
              : null,
        ),
        onSubmitted: (v) {
          setState(() => _search = v.trim());
          _refreshTickets();
        },
      ),
    );
  }

  Widget _filterChips() {
    final filters = [
      ('all', 'All', Icons.apps_rounded),
      ('my_tickets', 'Mine', Icons.person_pin_rounded),
      ('in_progress', 'Active', Icons.autorenew_rounded),
      ('pending', 'Pending', Icons.hourglass_top_rounded),
      ('waiting_for_customer_feedback', 'Waiting', Icons.forum_rounded),
      ('resolved', 'Done', Icons.check_circle_rounded),
    ];
    return SizedBox(
      height: 40,
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg),
        itemCount: filters.length,
        itemBuilder: (_, i) {
          final f = filters[i];
          final selected = _selectedFilter == f.$1;
          return Padding(
            padding: const EdgeInsets.only(right: 8),
            child: Material(
              color: selected ? AppColors.primary : AppColors.card,
              borderRadius: BorderRadius.circular(AppRadius.pill),
              child: InkWell(
                borderRadius: BorderRadius.circular(AppRadius.pill),
                onTap: () {
                  setState(() {
                    _selectedFilter = f.$1;
                    _isLoading = true;
                  });
                  _refreshTickets();
                },
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(AppRadius.pill),
                    border: Border.all(color: selected ? AppColors.primary : AppColors.border),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(f.$3, size: 14, color: selected ? Colors.white : AppColors.textSecondary),
                      const SizedBox(width: 5),
                      Text(f.$2,
                          style: AppText.caption.copyWith(
                            color: selected ? Colors.white : AppColors.textSecondary,
                            fontWeight: FontWeight.w600,
                          )),
                    ],
                  ),
                ),
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _skeletonList() {
    return ListView.separated(
      padding: const EdgeInsets.all(AppSpacing.lg),
      itemCount: 5,
      separatorBuilder: (_, __) => const SizedBox(height: 10),
      itemBuilder: (_, __) => const SkeletonCard(height: 110),
    );
  }

  Widget _ticketCard(TicketModel t) {
    final pColor = AppColors.priorityColor(t.priority);
    return Material(
      color: AppColors.card,
      borderRadius: BorderRadius.circular(AppRadius.lg),
      child: InkWell(
        borderRadius: BorderRadius.circular(AppRadius.lg),
        onTap: () async {
          await Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => TicketDetailScreen(ticket: t, currentUser: _currentUser),
            ),
          );
          _refreshTickets();
        },
        child: Container(
          padding: const EdgeInsets.all(AppSpacing.md),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(AppRadius.lg),
            border: Border.all(color: AppColors.border),
            boxShadow: AppShadows.card,
          ),
          child: Row(
            children: [
              Container(
                width: 5, height: 68,
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [pColor, pColor.withOpacity(0.5)],
                    begin: Alignment.topCenter,
                    end: Alignment.bottomCenter,
                  ),
                  borderRadius: BorderRadius.circular(3),
                ),
              ),
              const SizedBox(width: AppSpacing.md),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                          decoration: BoxDecoration(
                            color: AppColors.primary.withOpacity(0.08),
                            borderRadius: BorderRadius.circular(AppRadius.sm),
                          ),
                          child: Text('#${t.ticketKey}',
                              style: AppText.label.copyWith(color: AppColors.primary, fontSize: 10)),
                        ),
                        const SizedBox(width: 6),
                        StatusPill(status: t.status),
                        const SizedBox(width: 6),
                        PriorityPill(priority: t.priority),
                      ],
                    ),
                    const SizedBox(height: 6),
                    Text(t.title,
                        style: AppText.body.copyWith(fontSize: 14, fontWeight: FontWeight.w600),
                        maxLines: 2, overflow: TextOverflow.ellipsis),
                    const SizedBox(height: 6),
                    Row(
                      children: [
                        if (t.area != null && t.area!.isNotEmpty) ...[
                          Icon(Icons.place_outlined, size: 12, color: AppColors.primary),
                          const SizedBox(width: 3),
                          Text(t.area!,
                              style: AppText.caption.copyWith(fontSize: 11, color: AppColors.primary, fontWeight: FontWeight.w600)),
                          const SizedBox(width: 10),
                        ] else if (t.category != null && t.category!.isNotEmpty) ...[
                          Icon(Icons.folder_outlined, size: 12, color: AppColors.textMuted),
                          const SizedBox(width: 3),
                          Text(t.category!.replaceAll('_', ' '),
                              style: AppText.caption.copyWith(fontSize: 11)),
                          const SizedBox(width: 10),
                        ],
                        if (t.assigneeName != null) ...[
                          Icon(Icons.person_outline_rounded, size: 12, color: AppColors.textMuted),
                          const SizedBox(width: 3),
                          Flexible(
                            child: Text(t.assigneeName!,
                                style: AppText.caption.copyWith(fontSize: 11),
                                maxLines: 1, overflow: TextOverflow.ellipsis),
                          ),
                        ] else
                          Text('Unassigned',
                              style: AppText.caption.copyWith(color: AppColors.warning, fontSize: 11, fontWeight: FontWeight.w600)),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 6),
              Container(
                width: 32, height: 32,
                decoration: BoxDecoration(
                  color: AppColors.bg,
                  borderRadius: BorderRadius.circular(AppRadius.sm),
                ),
                child: Icon(Icons.chevron_right_rounded, color: AppColors.textSecondary),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
