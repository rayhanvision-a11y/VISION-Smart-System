import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../services/storage_service.dart';
import '../models/user.dart';
import '../models/ticket.dart';
import '../theme/app_theme.dart';
import '../widgets/common/app_header.dart';
import '../widgets/common/section_header.dart';
import '../widgets/common/skeleton.dart';
import '../widgets/common/status_pill.dart';
import 'create_ticket_screen.dart';
import 'ticket_detail_screen.dart';
import 'ticket_list_screen.dart';
import 'roster_screen.dart';
import 'profile_screen.dart';
import 'notifications_screen.dart';
import 'area_assign_screen.dart';
import '../services/app_state.dart';

class DashboardScreen extends StatefulWidget {
  final VoidCallback? onSwitchToTickets;
  const DashboardScreen({Key? key, this.onSwitchToTickets}) : super(key: key);

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  bool _isLoading = true;
  UserModel? _currentUser;
  Map<String, dynamic>? _dashboardData;
  bool _showRecent = true;
  bool _showDuty = true;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    final user = await StorageService.getUser();
    final data = await ApiService.getDashboard();
    if (mounted) {
      setState(() {
        _currentUser = user;
        _dashboardData = data;
        _isLoading = false;
      });
    }
  }

  String get _greeting {
    final h = DateTime.now().hour;
    if (h < 12) return 'Good Morning';
    if (h < 17) return 'Good Afternoon';
    if (h < 21) return 'Good Evening';
    return 'Good Night';
  }

  void _openTickets(String? statusFilter) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => Scaffold(
          appBar: AppBar(
            title: Text(
              statusFilter == null
                  ? 'All Tickets'
                  : 'Tickets: ${statusFilter.replaceAll('_', ' ').toUpperCase()}',
            ),
          ),
          body: TicketListScreen(initialStatus: statusFilter),
        ),
      ),
    );
  }

  Future<void> _openCreate() async {
    final res = await Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const CreateTicketScreen()),
    );
    if (res == true) _loadData();
  }

  void _openRoster() {
    Navigator.push(context, MaterialPageRoute(builder: (_) => const RosterScreen()));
  }

  void _openProfile() {
    Navigator.push(context, MaterialPageRoute(builder: (_) => const ProfileScreen()));
  }

  @override
  Widget build(BuildContext context) {
    final Map<String, dynamic> stats = (_dashboardData?['stats'] is Map)
        ? Map<String, dynamic>.from(_dashboardData!['stats'] as Map)
        : <String, dynamic>{};
    final dutyTeams = (_dashboardData?['duty_teams'] as List?) ?? [];
    final recentTicketsJson = (_dashboardData?['recent_tickets'] as List?) ?? [];
    final List<TicketModel> recentTickets = [];
    for (var j in recentTicketsJson) {
      if (j is Map) {
        try {
          recentTickets.add(TicketModel.fromJson(j));
        } catch (_) {}
      }
    }

    final int inProgress = ((stats['in_progress'] ?? 0) as num).toInt();
    final int pending = ((stats['pending'] ?? 0) as num).toInt();
    final int waiting = ((stats['waiting_for_customer_feedback'] ?? 0) as num).toInt();
    final int resolved = ((stats['resolved'] ?? 0) as num).toInt();
    final int urgent = ((stats['urgent'] ?? 0) as num).toInt();
    final int overdue = ((stats['overdue'] ?? 0) as num).toInt();
    final int total = ((stats['total'] ?? 0) as num).toInt();
    final int totalActive = inProgress + pending + waiting;

    return Scaffold(
      backgroundColor: AppColors.bg,
      body: RefreshIndicator(
        onRefresh: _loadData,
        color: AppColors.primary,
        child: CustomScrollView(
          slivers: [
            SliverToBoxAdapter(
              child: AppHeader(
                user: _currentUser,
                notificationCount: (stats['unread_notifications'] ?? 0) as int? ?? 0,
                onSearchTap: () => _openTickets(null),
                onNotificationTap: () => Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const NotificationsScreen()),
                ),
                onAvatarTap: _openProfile,
              ),
            ),
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.sm, AppSpacing.lg, AppSpacing.xxl),
              sliver: SliverList(
                delegate: SliverChildListDelegate([
                  _greetingLine(),
                  const SizedBox(height: AppSpacing.md),
                  _heroCard(totalActive, resolved, total),
                  const SizedBox(height: AppSpacing.lg),
                  _quickActions(),
                  const SizedBox(height: AppSpacing.md),
                  SectionHeader(title: AppState.instance.t('Ticket Overview', 'টিকিট সংক্ষিপ্ত')),
                  _isLoading
                      ? _skeletonStatsGrid()
                      : _statsGrid(inProgress, pending, waiting, resolved, urgent, overdue),
                  if (overdue > 0) ...[
                    const SizedBox(height: AppSpacing.md),
                    _slaBanner(overdue),
                  ],
                  const SizedBox(height: AppSpacing.sm),
                  _promoCard(),
                  _toggleHeader(
                    title: AppState.instance.t('Recent Tickets', 'সাম্প্রতিক টিকিট'),
                    expanded: _showRecent,
                    onTap: () => setState(() => _showRecent = !_showRecent),
                    trailingLabel: '${recentTickets.length}',
                  ),
                  if (_showRecent)
                    _isLoading
                        ? Column(children: const [SkeletonCard(), SizedBox(height: 10), SkeletonCard()])
                        : _recentList(recentTickets),
                  _toggleHeader(
                    title: AppState.instance.t('On Duty Today', 'আজকের ডিউটি'),
                    expanded: _showDuty,
                    onTap: () => setState(() => _showDuty = !_showDuty),
                    trailingLabel: '${dutyTeams.length}',
                  ),
                  if (_showDuty) _dutyCard(dutyTeams),
                  const SizedBox(height: 80),
                ]),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _greetingLine() {
    final name = _currentUser?.name.split(' ').first ?? '';
    return Padding(
      padding: const EdgeInsets.only(top: 4),
      child: Row(
        children: [
          Text(AppState.instance.isBengali
                  ? 'শুভেচ্ছা${name.isNotEmpty ? ", $name" : ""} ✨'
                  : '$_greeting${name.isNotEmpty ? ", $name" : ""} ✨',
              style: AppText.bodySm.copyWith(color: AppColors.textSecondary)),
          const Spacer(),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
            decoration: BoxDecoration(
              color: AppColors.success.withOpacity(0.12),
              borderRadius: BorderRadius.circular(AppRadius.pill),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  width: 6, height: 6,
                  decoration: const BoxDecoration(color: AppColors.success, shape: BoxShape.circle),
                ),
                const SizedBox(width: 5),
                Text(AppState.instance.t('System Online', 'সিস্টেম চালু'),
                    style: AppText.label.copyWith(color: AppColors.success, fontSize: 10)),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _toggleHeader({
    required String title,
    required bool expanded,
    required VoidCallback onTap,
    String? trailingLabel,
  }) {
    return Padding(
      padding: const EdgeInsets.only(top: AppSpacing.md, bottom: AppSpacing.sm),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(AppRadius.sm),
          onTap: onTap,
          child: Padding(
            padding: const EdgeInsets.symmetric(vertical: 4),
            child: Row(
              children: [
                Text(title, style: AppText.h3),
                const SizedBox(width: 8),
                if (trailingLabel != null)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(
                      color: AppColors.primary.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(AppRadius.pill),
                    ),
                    child: Text(trailingLabel,
                        style: AppText.label.copyWith(color: AppColors.primary)),
                  ),
                const Spacer(),
                AnimatedRotation(
                  turns: expanded ? 0.5 : 0,
                  duration: const Duration(milliseconds: 200),
                  child: const Icon(Icons.expand_more_rounded, color: AppColors.textMuted),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _heroCard(int active, int resolved, int total) {
    final resolutionRate = total > 0 ? (resolved / total * 100).toStringAsFixed(0) : '0';

    return Material(
      color: Colors.transparent,
      borderRadius: BorderRadius.circular(AppRadius.xl),
      child: InkWell(
        borderRadius: BorderRadius.circular(AppRadius.xl),
        onTap: () => _openTickets(null),
        child: Container(
          padding: const EdgeInsets.all(AppSpacing.xl),
          decoration: BoxDecoration(
            gradient: AppColors.heroGradient,
            borderRadius: BorderRadius.circular(AppRadius.xl),
            boxShadow: AppShadows.elevated,
          ),
          child: Stack(
            children: [
              Positioned(
                right: -20,
                top: -20,
                child: Container(
                  width: 140,
                  height: 140,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: Colors.white.withOpacity(0.05),
                  ),
                ),
              ),
              Positioned(
                right: 30,
                bottom: -40,
                child: Container(
                  width: 100,
                  height: 100,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: AppColors.accent.withOpacity(0.15),
                  ),
                ),
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(AppSpacing.sm),
                        decoration: BoxDecoration(
                          color: Colors.white.withOpacity(0.15),
                          borderRadius: BorderRadius.circular(AppRadius.md),
                        ),
                        child: const Icon(Icons.dashboard_rounded, color: AppColors.accent, size: 18),
                      ),
                      const SizedBox(width: AppSpacing.md),
                      Text('Live Overview',
                          style: AppText.bodySm.copyWith(color: Colors.white.withOpacity(0.9))),
                      const Spacer(),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: AppColors.accent.withOpacity(0.2),
                          borderRadius: BorderRadius.circular(AppRadius.pill),
                        ),
                        child: Text('$resolutionRate% resolved',
                            style: AppText.label.copyWith(color: AppColors.accent, fontSize: 10)),
                      ),
                    ],
                  ),
                  const SizedBox(height: AppSpacing.lg),
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Text('$active',
                          style: AppText.displayLg.copyWith(color: Colors.white, fontSize: 48)),
                      const SizedBox(width: AppSpacing.md),
                      Padding(
                        padding: const EdgeInsets.only(bottom: 12),
                        child: Text('active tickets',
                            style: AppText.bodySm.copyWith(color: Colors.white.withOpacity(0.85))),
                      ),
                    ],
                  ),
                  const SizedBox(height: AppSpacing.md),
                  _progressBar(active, total),
                  const SizedBox(height: AppSpacing.md),
                  Row(
                    children: [
                      _heroPill('Total', '$total'),
                      const SizedBox(width: 8),
                      _heroPill('Resolved', '$resolved'),
                      const SizedBox(width: 8),
                      _heroPill('Active', '$active'),
                    ],
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _progressBar(int active, int total) {
    final ratio = total > 0 ? (total - active) / total : 0.0;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Text('Progress',
                style: AppText.label.copyWith(color: Colors.white.withOpacity(0.8))),
            const Spacer(),
            Text('${(ratio * 100).toStringAsFixed(0)}%',
                style: AppText.label.copyWith(color: AppColors.accent)),
          ],
        ),
        const SizedBox(height: 6),
        ClipRRect(
          borderRadius: BorderRadius.circular(AppRadius.pill),
          child: LinearProgressIndicator(
            value: ratio.clamp(0.0, 1.0),
            minHeight: 6,
            backgroundColor: Colors.white.withOpacity(0.15),
            valueColor: const AlwaysStoppedAnimation(AppColors.accent),
          ),
        ),
      ],
    );
  }

  Widget _heroPill(String label, String value) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 10),
        decoration: BoxDecoration(
          color: Colors.white.withOpacity(0.1),
          borderRadius: BorderRadius.circular(AppRadius.md),
          border: Border.all(color: Colors.white.withOpacity(0.15)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(label,
                style: AppText.label.copyWith(color: Colors.white.withOpacity(0.75), fontSize: 10)),
            const SizedBox(height: 2),
            Text(value,
                style: AppText.h3.copyWith(color: Colors.white, fontSize: 16)),
          ],
        ),
      ),
    );
  }

  Widget _quickActions() {
    final role = _currentUser?.role.toLowerCase() ?? '';
    final isSupervisorish = ['supervisor', 'senior_supervisor', 'admin', 'super_admin', 'noc'].contains(role);
    final items = [
      (Icons.add_circle_rounded, AppState.instance.t('New\nTicket', 'নতুন\nটিকিট'), AppColors.accent, _openCreate),
      (Icons.person_pin_circle_rounded, AppState.instance.t('My\nTickets', 'আমার\nটিকিট'), AppColors.info,
          () => _openTickets('my_tickets')),
      if (isSupervisorish)
        (Icons.place_rounded, AppState.instance.t('Area\nAssign', 'এলাকা\nনিয়োগ'), AppColors.success,
            () => Navigator.push(context, MaterialPageRoute(builder: (_) => const AreaAssignScreen())))
      else
        (Icons.groups_2_rounded, AppState.instance.t('Team\nRoster', 'টিম\nরোস্টার'), AppColors.success, _openRoster),
      (Icons.local_fire_department_rounded, AppState.instance.t('Urgent\nOnly', 'জরুরি\nমাত্র'), AppColors.danger,
          () => _openTickets('urgent')),
    ];

    return Row(
      children: items
          .map((it) => Expanded(
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 4),
                  child: _actionTile(it.$1, it.$2, it.$3, it.$4),
                ),
              ))
          .toList(),
    );
  }

  Widget _actionTile(IconData icon, String label, Color color, VoidCallback onTap) {
    return Material(
      color: AppColors.card,
      borderRadius: BorderRadius.circular(AppRadius.lg),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        splashColor: color.withOpacity(0.15),
        child: Ink(
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(AppRadius.lg),
            border: Border.all(color: AppColors.border),
            boxShadow: AppShadows.card,
          ),
          child: Padding(
            padding: const EdgeInsets.symmetric(vertical: AppSpacing.lg, horizontal: 6),
            child: Column(
              children: [
                Container(
                  width: 48,
                  height: 48,
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: [color.withOpacity(0.18), color.withOpacity(0.08)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(AppRadius.md),
                  ),
                  child: Icon(icon, size: 24, color: color),
                ),
                const SizedBox(height: AppSpacing.sm),
                Text(label,
                    textAlign: TextAlign.center,
                    style: AppText.caption.copyWith(fontWeight: FontWeight.w600, height: 1.2)),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _statsGrid(int inProg, int pend, int wait, int res, int urgent, int overdue) {
    final items = [
      (_StatItem('In Progress', '$inProg', Icons.autorenew_rounded, AppColors.info, 'in_progress')),
      (_StatItem('Pending', '$pend', Icons.hourglass_top_rounded, AppColors.warning, 'pending')),
      (_StatItem('Waiting FB', '$wait', Icons.forum_rounded, const Color(0xFF8B5CF6), 'waiting_for_customer_feedback')),
      (_StatItem('Resolved', '$res', Icons.check_circle_rounded, AppColors.success, 'resolved')),
      (_StatItem('Urgent', '$urgent', Icons.priority_high_rounded, AppColors.danger, 'urgent')),
      (_StatItem('Overdue', '$overdue', Icons.schedule_rounded, const Color(0xFFEA580C), null)),
    ];

    return GridView.count(
      crossAxisCount: 2,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      crossAxisSpacing: 10,
      mainAxisSpacing: 10,
      childAspectRatio: 1.75,
      children: items.map((it) => _statCard(it)).toList(),
    );
  }

  Widget _statCard(_StatItem it) {
    return Material(
      color: AppColors.card,
      borderRadius: BorderRadius.circular(AppRadius.lg),
      child: InkWell(
        onTap: () => _openTickets(it.filter),
        borderRadius: BorderRadius.circular(AppRadius.lg),
        splashColor: it.color.withOpacity(0.15),
        child: Ink(
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(AppRadius.lg),
            border: Border.all(color: AppColors.border),
            boxShadow: AppShadows.card,
          ),
          child: Padding(
            padding: const EdgeInsets.all(AppSpacing.md),
            child: Stack(
              children: [
                Positioned(
                  right: -8,
                  bottom: -8,
                  child: Icon(it.icon, size: 60, color: it.color.withOpacity(0.06)),
                ),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Container(
                      padding: const EdgeInsets.all(7),
                      decoration: BoxDecoration(
                        color: it.color.withOpacity(0.12),
                        borderRadius: BorderRadius.circular(AppRadius.sm),
                      ),
                      child: Icon(it.icon, size: 16, color: it.color),
                    ),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(it.value, style: AppText.h1.copyWith(color: it.color, fontSize: 24)),
                        Text(it.label, style: AppText.caption),
                      ],
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _skeletonStatsGrid() {
    return GridView.count(
      crossAxisCount: 2,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      crossAxisSpacing: 10,
      mainAxisSpacing: 10,
      childAspectRatio: 1.75,
      children: List.generate(6, (_) => const SkeletonCard(height: 90)),
    );
  }

  Widget _slaBanner(int overdue) {
    return Material(
      color: Colors.transparent,
      borderRadius: BorderRadius.circular(AppRadius.lg),
      child: InkWell(
        onTap: () => _openTickets(null),
        borderRadius: BorderRadius.circular(AppRadius.lg),
        child: Container(
          padding: const EdgeInsets.all(AppSpacing.md),
          decoration: BoxDecoration(
            gradient: LinearGradient(
              colors: [AppColors.danger.withOpacity(0.12), AppColors.danger.withOpacity(0.04)],
            ),
            borderRadius: BorderRadius.circular(AppRadius.lg),
            border: Border.all(color: AppColors.danger.withOpacity(0.3)),
          ),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(AppSpacing.sm),
                decoration: BoxDecoration(
                  color: AppColors.danger.withOpacity(0.15),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.warning_amber_rounded, color: AppColors.danger, size: 20),
              ),
              const SizedBox(width: AppSpacing.md),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('SLA Breach Alert',
                        style: AppText.body.copyWith(color: AppColors.danger, fontWeight: FontWeight.w700)),
                    const SizedBox(height: 2),
                    Text('$overdue tickets need immediate attention',
                        style: AppText.caption),
                  ],
                ),
              ),
              const Icon(Icons.chevron_right_rounded, color: AppColors.danger),
            ],
          ),
        ),
      ),
    );
  }

  Widget _promoCard() {
    return Material(
      color: Colors.transparent,
      borderRadius: BorderRadius.circular(AppRadius.lg),
      child: InkWell(
        onTap: _openCreate,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        child: Container(
          padding: const EdgeInsets.all(AppSpacing.lg),
          decoration: BoxDecoration(
            gradient: LinearGradient(
              colors: [AppColors.accent.withOpacity(0.14), AppColors.accent.withOpacity(0.02)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(AppRadius.lg),
            border: Border.all(color: AppColors.accent.withOpacity(0.3)),
          ),
          child: Row(
            children: [
              Container(
                width: 46, height: 46,
                decoration: BoxDecoration(
                  gradient: AppColors.goldGradient,
                  borderRadius: BorderRadius.circular(AppRadius.md),
                ),
                child: const Icon(Icons.rocket_launch_rounded, color: Colors.white),
              ),
              const SizedBox(width: AppSpacing.md),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Need help? Create a ticket',
                        style: AppText.body.copyWith(fontWeight: FontWeight.w700)),
                    const SizedBox(height: 2),
                    Text('Our team responds within minutes',
                        style: AppText.caption),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                decoration: BoxDecoration(
                  color: AppColors.accent,
                  borderRadius: BorderRadius.circular(AppRadius.md),
                ),
                child: Row(
                  children: [
                    const Text('Start',
                        style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 12)),
                    const SizedBox(width: 4),
                    const Icon(Icons.arrow_forward_rounded, color: Colors.white, size: 14),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _recentList(List<TicketModel> tickets) {
    if (tickets.isEmpty) {
      return Container(
        padding: const EdgeInsets.all(AppSpacing.xl),
        decoration: BoxDecoration(
          color: AppColors.card,
          borderRadius: BorderRadius.circular(AppRadius.lg),
          border: Border.all(color: AppColors.border),
        ),
        child: Column(
          children: [
            Icon(Icons.inbox_rounded, size: 40, color: AppColors.textMuted.withOpacity(0.5)),
            const SizedBox(height: 8),
            Text('No recent tickets', style: AppText.bodySm),
          ],
        ),
      );
    }
    return Column(
      children: tickets.take(5).map(_recentCard).toList(),
    );
  }

  Widget _recentCard(TicketModel t) {
    final pColor = AppColors.priorityColor(t.priority);
    return Container(
      margin: const EdgeInsets.only(bottom: AppSpacing.sm),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(color: AppColors.border),
        boxShadow: AppShadows.card,
      ),
      child: Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        child: InkWell(
          borderRadius: BorderRadius.circular(AppRadius.lg),
          onTap: () => Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => TicketDetailScreen(ticket: t)),
          ),
          child: Padding(
            padding: const EdgeInsets.all(AppSpacing.md),
            child: Row(
              children: [
                Container(
                  width: 5, height: 52,
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: [pColor, pColor.withOpacity(0.6)],
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
                          Text('#${t.ticketKey}',
                              style: AppText.label.copyWith(color: AppColors.textMuted)),
                          const SizedBox(width: 6),
                          StatusPill(status: t.status),
                          const SizedBox(width: 6),
                          PriorityPill(priority: t.priority),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Text(t.title,
                          style: AppText.body, maxLines: 1, overflow: TextOverflow.ellipsis),
                      if (t.assigneeName != null) ...[
                        const SizedBox(height: 3),
                        Row(
                          children: [
                            Icon(Icons.person_outline_rounded, size: 12, color: AppColors.textMuted),
                            const SizedBox(width: 4),
                            Text(t.assigneeName!,
                                style: AppText.caption.copyWith(fontSize: 11)),
                          ],
                        ),
                      ],
                    ],
                  ),
                ),
                Container(
                  width: 32, height: 32,
                  decoration: BoxDecoration(
                    color: AppColors.bg,
                    borderRadius: BorderRadius.circular(AppRadius.sm),
                  ),
                  child: Icon(Icons.chevron_right_rounded, color: AppColors.textSecondary, size: 20),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _dutyCard(List teams) {
    if (teams.isEmpty) {
      return Container(
        padding: const EdgeInsets.all(AppSpacing.lg),
        decoration: BoxDecoration(
          color: AppColors.card,
          borderRadius: BorderRadius.circular(AppRadius.lg),
          border: Border.all(color: AppColors.border),
        ),
        child: Row(
          children: [
            Icon(Icons.groups_rounded, color: AppColors.textMuted),
            const SizedBox(width: AppSpacing.md),
            Text('No teams on duty right now', style: AppText.bodySm),
          ],
        ),
      );
    }

    return Container(
      padding: const EdgeInsets.all(AppSpacing.md),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(color: AppColors.border),
        boxShadow: AppShadows.card,
      ),
      child: Column(
        children: teams.take(4).map<Widget>((t) {
          if (t is! Map) return const SizedBox.shrink();
          final label = t['team_label']?.toString() ?? t['team_key']?.toString() ?? 'Team';
          final members = (t['members'] as List?) ?? [];
          final onDuty = members.where((m) => m is Map && m['is_on_duty'] == true).length;
          final pct = members.isEmpty ? 0.0 : onDuty / members.length;

          return Material(
            color: Colors.transparent,
            child: InkWell(
              onTap: _openRoster,
              borderRadius: BorderRadius.circular(AppRadius.sm),
              child: Padding(
                padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 4),
                child: Row(
                  children: [
                    Container(
                      width: 34, height: 34,
                      decoration: BoxDecoration(
                        color: AppColors.primary.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(AppRadius.sm),
                      ),
                      alignment: Alignment.center,
                      child: Text(
                        label.isNotEmpty ? label[0].toUpperCase() : 'T',
                        style: AppText.h3.copyWith(color: AppColors.primary),
                      ),
                    ),
                    const SizedBox(width: AppSpacing.md),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(label, style: AppText.body.copyWith(fontSize: 13)),
                          const SizedBox(height: 5),
                          ClipRRect(
                            borderRadius: BorderRadius.circular(AppRadius.pill),
                            child: LinearProgressIndicator(
                              value: pct,
                              minHeight: 4,
                              backgroundColor: AppColors.border,
                              valueColor: AlwaysStoppedAnimation(
                                pct > 0.5 ? AppColors.success : AppColors.warning,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: AppSpacing.md),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Text('$onDuty/${members.length}',
                            style: AppText.h3.copyWith(fontSize: 14, color: AppColors.success)),
                        Text('on duty', style: AppText.label.copyWith(fontSize: 9)),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          );
        }).toList(),
      ),
    );
  }
}

class _StatItem {
  final String label;
  final String value;
  final IconData icon;
  final Color color;
  final String? filter;
  _StatItem(this.label, this.value, this.icon, this.color, this.filter);
}
