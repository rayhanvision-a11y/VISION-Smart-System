import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
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
import 'live_map_screen.dart';
import 'package:url_launcher/url_launcher.dart';
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
  bool _showRecent = false;
  bool _showDuty = false;
  bool _showFieldTeams = true;
  bool _showLeaderboard = true;
  late final PageController _leaderboardPageController;
  int _leaderboardPageIndex = 0;

  @override
  void initState() {
    super.initState();
    _leaderboardPageController = PageController(viewportFraction: 0.94);
    _loadData();
  }

  @override
  void dispose() {
    _leaderboardPageController.dispose();
    super.dispose();
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
        builder: (_) => TicketListScreen(initialStatus: statusFilter),
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
    final summaryToday = (_dashboardData?['summary_today'] is Map)
        ? Map<String, dynamic>.from(_dashboardData!['summary_today'] as Map)
        : ((_dashboardData?['daily_summary'] is Map)
            ? Map<String, dynamic>.from(_dashboardData!['daily_summary'] as Map)
            : <String, dynamic>{});
    final summaryPrev = (_dashboardData?['summary_prev'] is Map)
        ? Map<String, dynamic>.from(_dashboardData!['summary_prev'] as Map)
        : <String, dynamic>{};
    final summaryMonth = (_dashboardData?['summary_month'] is Map)
        ? Map<String, dynamic>.from(_dashboardData!['summary_month'] as Map)
        : <String, dynamic>{};

    final dutyTeams = (_dashboardData?['duty_teams'] as List?) ?? [];
    final myTechTeam = (_dashboardData?['my_technician_team'] is Map)
        ? Map<String, dynamic>.from(_dashboardData!['my_technician_team'] as Map)
        : null;
    final todayTechTeams = (_dashboardData?['today_technician_teams'] as List?) ?? [];
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
      body: Column(
        children: [
          AppHeader(
            user: _currentUser,
            notificationCount: (stats['unread_notifications'] ?? 0) as int? ?? 0,
            onSearchTap: () => _openTickets(null),
            onNotificationTap: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const NotificationsScreen()),
            ),
            onAvatarTap: _openProfile,
          ),
          Expanded(
            child: RefreshIndicator(
              onRefresh: _loadData,
              color: AppColors.primary,
              child: CustomScrollView(
                slivers: [
                  SliverPadding(
                    padding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.sm, AppSpacing.lg, AppSpacing.xxl),
                    sliver: SliverList(
                      delegate: SliverChildListDelegate([
                        _greetingLine(),
                        const SizedBox(height: AppSpacing.md),
                        if (myTechTeam != null) ...[
                          _myFieldTeamCard(myTechTeam),
                          const SizedBox(height: AppSpacing.md),
                        ],
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
                  const SizedBox(height: AppSpacing.md),
                  _dailyPerformanceSection(summaryToday, summaryPrev, summaryMonth),
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
                  if (todayTechTeams.isNotEmpty) ...[
                    _toggleHeader(
                      title: AppState.instance.t("Today's Field Teams", 'আজকের ফিল্ড স্কোয়াড'),
                      expanded: _showFieldTeams,
                      onTap: () => setState(() => _showFieldTeams = !_showFieldTeams),
                      trailingLabel: '${todayTechTeams.length}',
                    ),
                    if (_showFieldTeams) _fieldSquadsList(todayTechTeams),
                  ],
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
    ),
    ],
    ),
    );
  }

  Widget _greetingLine() {
    final name = _currentUser?.name.split(' ').first ?? '';
    final now = DateTime.now();
    final dateStr = DateFormat('EEE, d MMM').format(now);
    return Padding(
      padding: const EdgeInsets.only(top: 4),
      child: Row(
        children: [
          Expanded(
            child: Wrap(
              crossAxisAlignment: WrapCrossAlignment.center,
              spacing: 8,
              runSpacing: 4,
              children: [
                Text(
                  AppState.instance.isBengali
                      ? 'শুভেচ্ছা${name.isNotEmpty ? ", $name" : ""} ✨'
                      : '$_greeting${name.isNotEmpty ? ", $name" : ""} ✨',
                  style: AppText.bodySm.copyWith(color: AppColors.textSecondary),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                  decoration: BoxDecoration(
                    color: AppColors.surface,
                    borderRadius: BorderRadius.circular(AppRadius.xs),
                    border: Border.all(color: AppColors.border),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.calendar_today_rounded, size: 11, color: AppColors.primary),
                      const SizedBox(width: 4),
                      Text(
                        dateStr,
                        style: AppText.label.copyWith(fontSize: 10, color: AppColors.textSecondary),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
            decoration: BoxDecoration(
              color: AppColors.success.withOpacity(0.12),
              borderRadius: BorderRadius.circular(AppRadius.pill),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(Icons.signal_cellular_alt_rounded, size: 12, color: AppColors.success),
                const SizedBox(width: 4),
                Text(
                  AppState.instance.t('Online', 'অনলাইন'),
                  style: AppText.label.copyWith(color: AppColors.success, fontSize: 10),
                ),
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
                  child: Icon(Icons.expand_more_rounded, color: AppColors.textMuted),
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
                    color: const Color(0xFFFBBF24).withOpacity(0.12),
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
                        child: const Icon(Icons.dashboard_rounded, color: Color(0xFFFBBF24), size: 18),
                      ),
                      const SizedBox(width: AppSpacing.md),
                      Text('Live Overview',
                          style: AppText.bodySm.copyWith(color: Colors.white.withOpacity(0.9))),
                      const Spacer(),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: const Color(0xFFFBBF24).withOpacity(0.2),
                          borderRadius: BorderRadius.circular(AppRadius.pill),
                        ),
                        child: Text('$resolutionRate% resolved',
                            style: AppText.label.copyWith(color: const Color(0xFFFBBF24), fontSize: 10)),
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
                style: AppText.label.copyWith(color: const Color(0xFFFBBF24))),
          ],
        ),
        const SizedBox(height: 6),
        ClipRRect(
          borderRadius: BorderRadius.circular(AppRadius.pill),
          child: LinearProgressIndicator(
            value: ratio.clamp(0.0, 1.0),
            minHeight: 6,
            backgroundColor: Colors.white.withOpacity(0.15),
            valueColor: const AlwaysStoppedAnimation(Color(0xFFFBBF24)),
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
    final isAdmin = ['admin', 'super_admin'].contains(role);
    final items = [
      (Icons.add_circle_rounded, AppState.instance.t('New\nTicket', 'নতুন\nটিকিট'), AppColors.accent, _openCreate),
      (Icons.person_pin_circle_rounded, AppState.instance.t('My\nTickets', 'আমার\nটিকিট'), AppColors.info,
          () => _openTickets('my_tickets')),
      if (isSupervisorish)
        (Icons.place_rounded, AppState.instance.t('Area\nAssign', 'এলাকা\nনিয়োগ'), AppColors.success,
            () => Navigator.push(context, MaterialPageRoute(builder: (_) => const AreaAssignScreen())))
      else
        (Icons.groups_2_rounded, AppState.instance.t('Team\nRoster', 'টিম\nরোস্টার'), AppColors.success, _openRoster),
      if (isAdmin)
        (Icons.map_rounded, AppState.instance.t('Live\nMap', 'লাইভ\nম্যাপ'), AppColors.info,
            () => Navigator.push(context, MaterialPageRoute(builder: (_) => const LiveMapScreen())))
      else
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
      crossAxisCount: 3,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      crossAxisSpacing: 8,
      mainAxisSpacing: 8,
      childAspectRatio: 1.05,
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
            padding: const EdgeInsets.all(10),
            child: Stack(
              children: [
                Positioned(
                  right: -6,
                  bottom: -6,
                  child: Icon(it.icon, size: 44, color: it.color.withOpacity(0.06)),
                ),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Container(
                      padding: const EdgeInsets.all(5),
                      decoration: BoxDecoration(
                        color: it.color.withOpacity(0.12),
                        borderRadius: BorderRadius.circular(AppRadius.sm),
                      ),
                      child: Icon(it.icon, size: 13, color: it.color),
                    ),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(it.value, style: AppText.h1.copyWith(color: it.color, fontSize: 20)),
                        Text(it.label, style: AppText.caption.copyWith(fontSize: 10), maxLines: 1, overflow: TextOverflow.ellipsis),
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
      crossAxisCount: 3,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      crossAxisSpacing: 8,
      mainAxisSpacing: 8,
      childAspectRatio: 1.05,
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

  void _callUser(String? phone) async {
    if (phone == null || phone.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(AppState.instance.t('Phone number not available', 'ফোন নম্বর পাওয়া যায়নি')),
          duration: const Duration(seconds: 2),
        ),
      );
      return;
    }
    final cleanPhone = phone.replaceAll(RegExp(r'[^\d+]'), '');
    final uri = Uri.parse('tel:$cleanPhone');
    try {
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri);
      } else {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      }
    } catch (_) {}
  }

  Widget _myFieldTeamCard(Map<String, dynamic> team) {
    final category = team['category'] as String? ?? 'complain';
    final categoryLabel = team['category_label'] as String? ?? 'Field Team';
    final teamName = team['team_name'] as String? ?? 'Field Squad';
    final area = team['area'] as String?;
    final vehicleNo = team['vehicle_no'] as String?;
    final isLeader = team['is_leader'] == true;
    final leader = team['leader'] as Map<String, dynamic>?;
    final member1 = team['member_1'] as Map<String, dynamic>?;
    final member2 = team['member_2'] as Map<String, dynamic>?;

    Color badgeColor;
    IconData catIcon;
    if (category == 'complain') {
      badgeColor = const Color(0xFFE11D48);
      catIcon = Icons.report_problem_rounded;
    } else if (category == 'new_connection') {
      badgeColor = const Color(0xFF2563EB);
      catIcon = Icons.add_link_rounded;
    } else {
      badgeColor = const Color(0xFFD97706);
      catIcon = Icons.swap_horiz_rounded;
    }

    return Container(
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(color: badgeColor.withOpacity(0.4), width: 1.5),
        boxShadow: [
          BoxShadow(
            color: badgeColor.withOpacity(0.08),
            blurRadius: 14,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Bar
          Container(
            padding: const EdgeInsets.symmetric(horizontal: AppSpacing.md, vertical: 10),
            decoration: BoxDecoration(
              color: badgeColor.withOpacity(0.08),
              borderRadius: const BorderRadius.vertical(top: Radius.circular(AppRadius.lg - 1)),
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(6),
                  decoration: BoxDecoration(
                    color: badgeColor,
                    shape: BoxShape.circle,
                  ),
                  child: Icon(catIcon, color: Colors.white, size: 14),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        categoryLabel,
                        style: TextStyle(
                          color: badgeColor,
                          fontWeight: FontWeight.w800,
                          fontSize: 12,
                        ),
                      ),
                      Text(
                        teamName,
                        style: AppText.h3.copyWith(fontSize: 14),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
                if (isLeader)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF59E0B),
                      borderRadius: BorderRadius.circular(AppRadius.pill),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: const [
                        Text('👑', style: TextStyle(fontSize: 11)),
                        SizedBox(width: 3),
                        Text(
                          'Team Leader',
                          style: TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w800,
                            fontSize: 10,
                          ),
                        ),
                      ],
                    ),
                  )
                else
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: AppColors.primary.withOpacity(0.12),
                      borderRadius: BorderRadius.circular(AppRadius.pill),
                    ),
                    child: Text(
                      AppState.instance.t('Member', 'সদস্য'),
                      style: TextStyle(
                        color: AppColors.primary,
                        fontWeight: FontWeight.w700,
                        fontSize: 10,
                      ),
                    ),
                  ),
              ],
            ),
          ),

          Padding(
            padding: const EdgeInsets.all(AppSpacing.md),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if ((area != null && area.isNotEmpty) || (vehicleNo != null && vehicleNo.isNotEmpty)) ...[
                  Wrap(
                    spacing: 8,
                    runSpacing: 6,
                    children: [
                      if (area != null && area.isNotEmpty)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: AppColors.bg,
                            borderRadius: BorderRadius.circular(AppRadius.xs),
                            border: Border.all(color: AppColors.border),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.location_on_rounded, size: 12, color: Colors.redAccent),
                              const SizedBox(width: 4),
                              Text(area, style: AppText.caption.copyWith(fontSize: 11)),
                            ],
                          ),
                        ),
                      if (vehicleNo != null && vehicleNo.isNotEmpty)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: AppColors.bg,
                            borderRadius: BorderRadius.circular(AppRadius.xs),
                            border: Border.all(color: AppColors.border),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.two_wheeler_rounded, size: 12, color: AppColors.primary),
                              const SizedBox(width: 4),
                              Text(vehicleNo, style: AppText.caption.copyWith(fontSize: 11)),
                            ],
                          ),
                        ),
                    ],
                  ),
                  const SizedBox(height: AppSpacing.sm),
                ],

                // Notification summary message
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                  decoration: BoxDecoration(
                    color: AppColors.bg,
                    borderRadius: BorderRadius.circular(AppRadius.sm),
                  ),
                  child: Row(
                    children: [
                      Icon(Icons.info_outline_rounded, size: 15, color: AppColors.textMuted),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          isLeader
                              ? AppState.instance.t(
                                  'You are the Team Leader today.',
                                  'আপনি আজকের এই টিমের লিডার। সদস্যদ্বয় আপনার সাথে কাজ করবেন।',
                                )
                              : AppState.instance.t(
                                  'Leader: ${leader?['name'] ?? 'N/A'}',
                                  'আপনি টিম লিডার "${leader?['name'] ?? 'N/A'}" এর সাথে $categoryLabel-এ আছেন।',
                                ),
                          style: AppText.caption.copyWith(
                            color: AppColors.textSecondary,
                            fontWeight: FontWeight.w600,
                            fontSize: 11,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: AppSpacing.md),
                Text(
                  AppState.instance.t('Field Squad (3 Persons)', 'ফিল্ড স্কোয়াড (৩ জন)'),
                  style: AppText.label.copyWith(fontSize: 11, color: AppColors.textMuted),
                ),
                const SizedBox(height: 6),

                _memberRow(
                  roleTag: AppState.instance.t('👑 Leader', '👑 লিডার'),
                  name: leader?['name'] ?? 'N/A',
                  phone: leader?['phone'] as String?,
                  isLeader: true,
                ),
                const SizedBox(height: 6),
                _memberRow(
                  roleTag: AppState.instance.t('1 Member', '১ সদস্য'),
                  name: member1?['name'] ?? 'N/A',
                  phone: member1?['phone'] as String?,
                  isLeader: false,
                ),
                const SizedBox(height: 6),
                _memberRow(
                  roleTag: AppState.instance.t('2 Member', '২ সদস্য'),
                  name: member2?['name'] ?? 'N/A',
                  phone: member2?['phone'] as String?,
                  isLeader: false,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _memberRow({
    required String roleTag,
    required String name,
    required String? phone,
    required bool isLeader,
  }) {
    final hasPhone = phone != null && phone.trim().isNotEmpty;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
      decoration: BoxDecoration(
        color: isLeader ? const Color(0xFFFFFBEB) : AppColors.bg,
        borderRadius: BorderRadius.circular(AppRadius.sm),
        border: Border.all(
          color: isLeader ? const Color(0xFFFDE68A) : AppColors.border.withOpacity(0.6),
        ),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
            decoration: BoxDecoration(
              color: isLeader ? const Color(0xFFF59E0B) : AppColors.textMuted.withOpacity(0.15),
              borderRadius: BorderRadius.circular(AppRadius.xs),
            ),
            child: Text(
              roleTag,
              style: TextStyle(
                color: isLeader ? Colors.white : AppColors.textSecondary,
                fontSize: 9,
                fontWeight: FontWeight.w700,
              ),
            ),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              name,
              style: AppText.bodySm.copyWith(
                fontWeight: isLeader ? FontWeight.w700 : FontWeight.w500,
                color: isLeader ? const Color(0xFF92400E) : AppColors.textPrimary,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ),
          if (hasPhone)
            InkWell(
              onTap: () => _callUser(phone),
              borderRadius: BorderRadius.circular(AppRadius.xs),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: AppColors.success.withOpacity(0.15),
                  borderRadius: BorderRadius.circular(AppRadius.xs),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.phone_rounded, size: 12, color: AppColors.success),
                    const SizedBox(width: 4),
                    Text(
                      AppState.instance.t('Call', 'কল'),
                      style: AppText.caption.copyWith(
                        color: AppColors.success,
                        fontWeight: FontWeight.w700,
                        fontSize: 10,
                      ),
                    ),
                  ],
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _dailyPerformanceSection(
    Map<String, dynamic> today,
    Map<String, dynamic> prev,
    Map<String, dynamic> month,
  ) {
    final int todayTotal = ((today['day_total'] ?? 0) as num).toInt();
    final int prevTotal = ((prev['day_total'] ?? 0) as num).toInt();
    final int monthTotal = ((month['total'] ?? 0) as num).toInt();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _toggleHeader(
          title: AppState.instance.t('Daily Performance', 'দৈনিক পারফরম্যান্স ও প্রতিযোগিতা'),
          expanded: _showLeaderboard,
          onTap: () => setState(() => _showLeaderboard = !_showLeaderboard),
          trailingLabel: '$todayTotal ${AppState.instance.t('Today', 'আজ')}',
        ),
        if (_showLeaderboard) ...[
          // Segmented Tabs: Today | Previous Day | Month to Date
          Container(
            margin: const EdgeInsets.only(bottom: AppSpacing.sm),
            padding: const EdgeInsets.all(3),
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(AppRadius.pill),
              border: Border.all(color: AppColors.border),
            ),
            child: Row(
              children: [
                _tabPill(0, AppState.instance.t('Today', 'আজকের দিন')),
                _tabPill(1, AppState.instance.t('Previous Day', 'গতকালের দিন')),
                _tabPill(2, AppState.instance.t('Month to Date', 'চলতি মাস')),
              ],
            ),
          ),

          // Paged Cards matching web reference & image screenshot
          SizedBox(
            height: 250,
            child: PageView(
              controller: _leaderboardPageController,
              onPageChanged: (idx) => setState(() => _leaderboardPageIndex = idx),
              children: [
                _performanceDayCard(
                  title: AppState.instance.t('Today', 'আজকের দিন'),
                  icon: Icons.assignment_outlined,
                  accentColor: const Color(0xFF6366F1),
                  dateStr: today['formatted_date'] as String? ?? DateFormat('dd · MMM · yyyy').format(DateTime.now()),
                  dayTotal: todayTotal,
                  categories: (today['categories'] as List?) ?? [],
                ),
                _performanceDayCard(
                  title: AppState.instance.t('Previous Day', 'গতকালের দিন'),
                  icon: Icons.assignment_outlined,
                  accentColor: const Color(0xFF64748B),
                  dateStr: prev['formatted_date'] as String? ?? DateFormat('dd · MMM · yyyy').format(DateTime.now().subtract(const Duration(days: 1))),
                  dayTotal: prevTotal,
                  categories: (prev['categories'] as List?) ?? [],
                ),
                _performanceMonthCard(
                  title: AppState.instance.t('Month to Date', 'চলতি মাস'),
                  icon: Icons.calendar_month_rounded,
                  accentColor: const Color(0xFF10B981),
                  monthLabel: month['label'] as String? ?? DateFormat('MMM / yyyy').format(DateTime.now()),
                  monthTotal: monthTotal,
                ),
              ],
            ),
          ),
          const SizedBox(height: 8),

          // Smooth Dots Indicator
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: List.generate(3, (i) => AnimatedContainer(
              duration: const Duration(milliseconds: 200),
              margin: const EdgeInsets.symmetric(horizontal: 3),
              width: _leaderboardPageIndex == i ? 20 : 6,
              height: 6,
              decoration: BoxDecoration(
                color: _leaderboardPageIndex == i ? AppColors.primary : AppColors.border,
                borderRadius: BorderRadius.circular(3),
              ),
            )),
          ),
          const SizedBox(height: AppSpacing.md),
        ],
      ],
    );
  }

  Widget _tabPill(int index, String label) {
    final isSelected = _leaderboardPageIndex == index;
    return Expanded(
      child: InkWell(
        onTap: () {
          _leaderboardPageController.animateToPage(
            index,
            duration: const Duration(milliseconds: 250),
            curve: Curves.easeInOut,
          );
        },
        borderRadius: BorderRadius.circular(AppRadius.pill),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          padding: const EdgeInsets.symmetric(vertical: 6),
          decoration: BoxDecoration(
            color: isSelected ? AppColors.primary : Colors.transparent,
            borderRadius: BorderRadius.circular(AppRadius.pill),
          ),
          child: Text(
            label,
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 11,
              fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
              color: isSelected ? Colors.white : AppColors.textSecondary,
            ),
          ),
        ),
      ),
    );
  }

  Widget _performanceDayCard({
    required String title,
    required IconData icon,
    required Color accentColor,
    required String dateStr,
    required int dayTotal,
    required List categories,
  }) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 4),
      padding: const EdgeInsets.all(AppSpacing.md),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(color: AppColors.border),
        boxShadow: AppShadows.card,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header: Icon + Title + Date pill + DAY TOTAL
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 38,
                height: 38,
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [accentColor, accentColor.withOpacity(0.7)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(AppRadius.md),
                ),
                child: Icon(icon, color: Colors.white, size: 20),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Text(title, style: AppText.h3.copyWith(fontSize: 14)),
                        const SizedBox(width: 6),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: accentColor.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(AppRadius.pill),
                            border: Border.all(color: accentColor.withOpacity(0.2)),
                          ),
                          child: Text(
                            '📅 $dateStr',
                            style: TextStyle(
                              color: accentColor,
                              fontSize: 9,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 2),
                    Text(
                      AppState.instance.t('Resolved tickets per technician', 'টেকনিশিয়ান প্রতি সলভ টিকিট'),
                      style: AppText.caption.copyWith(fontSize: 10),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text(
                    AppState.instance.t('DAY TOTAL', 'মোট সলভ'),
                    style: AppText.label.copyWith(fontSize: 9, color: AppColors.textSecondary),
                  ),
                  Text(
                    '$dayTotal',
                    style: AppText.h1.copyWith(fontSize: 22, height: 1.1),
                  ),
                ],
              ),
            ],
          ),

          const SizedBox(height: 10),
          const Divider(height: 1),
          const SizedBox(height: 8),

          // Body: Category boxes or Empty state
          Expanded(
            child: categories.isEmpty
                ? Container(
                    width: double.infinity,
                    decoration: BoxDecoration(
                      color: AppColors.surface.withOpacity(0.5),
                      borderRadius: BorderRadius.circular(AppRadius.md),
                      border: Border.all(
                        color: AppColors.border,
                        style: BorderStyle.solid,
                      ),
                    ),
                    alignment: Alignment.center,
                    child: Text(
                      AppState.instance.t('No resolved tickets on this date.', 'এই তারিখে কোনো সলভ টিকিট নেই।'),
                      style: AppText.caption.copyWith(fontSize: 11),
                      textAlign: TextAlign.center,
                    ),
                  )
                : ListView.builder(
                    padding: EdgeInsets.zero,
                    itemCount: categories.length,
                    itemBuilder: (context, catIdx) {
                      final cat = categories[catIdx] as Map<String, dynamic>;
                      final label = cat['label'] as String? ?? 'TEAM';
                      final total = cat['total'] ?? 0;
                      final items = (cat['items'] as List?) ?? [];

                      final catColors = [
                        const Color(0xFFE11D48),
                        const Color(0xFF4F46E5),
                        const Color(0xFF059669),
                        const Color(0xFFD97706),
                      ];
                      final boxColor = catColors[catIdx % catColors.length];

                      return Container(
                        margin: const EdgeInsets.only(bottom: 6),
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: boxColor.withOpacity(0.06),
                          borderRadius: BorderRadius.circular(AppRadius.md),
                          border: Border.all(color: boxColor.withOpacity(0.3)),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Row(
                                  children: [
                                    Icon(Icons.build_rounded, size: 12, color: boxColor),
                                    const SizedBox(width: 4),
                                    Text(
                                      label,
                                      style: TextStyle(
                                        color: boxColor,
                                        fontSize: 10,
                                        fontWeight: FontWeight.w900,
                                        letterSpacing: 0.3,
                                      ),
                                    ),
                                  ],
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
                                  decoration: BoxDecoration(
                                    color: AppColors.card,
                                    borderRadius: BorderRadius.circular(AppRadius.pill),
                                  ),
                                  child: Text(
                                    'Total: $total',
                                    style: TextStyle(
                                      color: boxColor,
                                      fontSize: 9,
                                      fontWeight: FontWeight.w800,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 6),
                            Column(
                              children: items.asMap().entries.map((entry) {
                                final i = entry.key;
                                final it = entry.value as Map<String, dynamic>;
                                final name = it['name'] as String? ?? '—';
                                final count = ((it['count'] ?? 0) as num).toInt();
                                final countStr = count < 10 ? '0$count' : '$count';

                                return Padding(
                                  padding: const EdgeInsets.symmetric(vertical: 2),
                                  child: Row(
                                    children: [
                                      Text(
                                        '${i + 1}. ',
                                        style: TextStyle(
                                          fontSize: 11,
                                          fontWeight: FontWeight.w700,
                                          color: AppColors.textSecondary,
                                        ),
                                      ),
                                      Expanded(
                                        child: Text(
                                          name,
                                          style: AppText.bodySm.copyWith(
                                            fontSize: 11,
                                            fontWeight: FontWeight.w600,
                                          ),
                                          maxLines: 1,
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                      ),
                                      Text(
                                        countStr,
                                        style: TextStyle(
                                          fontSize: 12,
                                          fontWeight: FontWeight.w900,
                                          color: boxColor,
                                        ),
                                      ),
                                    ],
                                  ),
                                );
                              }).toList(),
                            ),
                          ],
                        ),
                      );
                    },
                  ),
          ),
        ],
      ),
    );
  }

  Widget _performanceMonthCard({
    required String title,
    required IconData icon,
    required Color accentColor,
    required String monthLabel,
    required int monthTotal,
  }) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 4),
      padding: const EdgeInsets.all(AppSpacing.md),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(color: AppColors.border),
        boxShadow: AppShadows.card,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 38,
                height: 38,
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [accentColor, accentColor.withOpacity(0.7)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(AppRadius.md),
                ),
                child: Icon(icon, color: Colors.white, size: 20),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Text(title, style: AppText.h3.copyWith(fontSize: 14)),
                        const SizedBox(width: 6),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: accentColor.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(AppRadius.pill),
                            border: Border.all(color: accentColor.withOpacity(0.2)),
                          ),
                          child: Text(
                            '📅 $monthLabel',
                            style: TextStyle(
                              color: accentColor,
                              fontSize: 9,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 2),
                    Text(
                      AppState.instance.t('Total resolved tickets so far this month', 'চলতি মাসে সর্বমোট সলভ টিকিট'),
                      style: AppText.caption.copyWith(fontSize: 10),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text(
                    AppState.instance.t('TOTAL', 'সর্বমোট'),
                    style: AppText.label.copyWith(fontSize: 9, color: AppColors.textSecondary),
                  ),
                  Text(
                    '$monthTotal',
                    style: AppText.h1.copyWith(fontSize: 22, height: 1.1, color: accentColor),
                  ),
                ],
              ),
            ],
          ),

          const SizedBox(height: 10),
          const Divider(height: 1),

          Expanded(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                ShaderMask(
                  shaderCallback: (bounds) => LinearGradient(
                    colors: [accentColor, const Color(0xFF0284C7)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ).createShader(bounds),
                  child: Text(
                    '$monthTotal',
                    style: const TextStyle(
                      fontSize: 54,
                      fontWeight: FontWeight.w900,
                      color: Colors.white,
                      height: 1.0,
                    ),
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  AppState.instance.t('RESOLVED THIS MONTH', 'চলতি মাসে সর্বমোট সলভ'),
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                    letterSpacing: 0.5,
                    color: AppColors.textSecondary,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  monthLabel,
                  style: AppText.caption.copyWith(fontSize: 10),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _fieldSquadsList(List teams) {
    if (teams.isEmpty) {
      return Container(
        padding: const EdgeInsets.all(AppSpacing.md),
        decoration: BoxDecoration(
          color: AppColors.card,
          borderRadius: BorderRadius.circular(AppRadius.lg),
          border: Border.all(color: AppColors.border),
        ),
        child: Row(
          children: [
            Icon(Icons.people_outline_rounded, color: AppColors.textMuted),
            const SizedBox(width: AppSpacing.md),
            Text(AppState.instance.t('No field teams created today', 'আজকে কোনো ফিল্ড টিম নেই'), style: AppText.bodySm),
          ],
        ),
      );
    }

    return Column(
      children: teams.asMap().entries.map((entry) {
        final idx = entry.key;
        final item = entry.value;
        final t = (item is Map) ? Map<String, dynamic>.from(item) : <String, dynamic>{};
        final rank = (t['rank'] as num?)?.toInt() ?? (idx + 1);
        final isChampion = t['is_champion'] == true || (rank == 1 && ((t['team_done_count'] ?? 0) as num) > 0);
        final teamDone = ((t['team_done_count'] ?? 0) as num).toInt();
        final teamOpen = ((t['team_open_count'] ?? 0) as num).toInt();
        final leaderDone = ((t['leader_done_count'] ?? 0) as num).toInt();
        final isMyTeam = t['is_leader'] == true || t['is_member'] == true;

        final category = t['category'] as String? ?? 'complain';
        final isBn = AppState.instance.isBengali;
        final categoryLabel = isBn
            ? (t['category_label_bn'] as String? ?? t['category_label'] as String? ?? 'টিম')
            : (t['category_label'] as String? ?? 'Team');
        final teamName = t['team_name'] as String? ?? (isBn ? 'ফিল্ড স্কোয়াড' : 'Field Squad');
        final area = t['area'] as String?;
        final vehicleNo = t['vehicle_no'] as String?;
        final leader = t['leader'] as Map<String, dynamic>?;
        final member1 = t['member_1'] as Map<String, dynamic>?;
        final member2 = t['member_2'] as Map<String, dynamic>?;

        Color bColor;
        if (category == 'complain') {
          bColor = const Color(0xFFE11D48);
        } else if (category == 'new_connection') {
          bColor = const Color(0xFF2563EB);
        } else {
          bColor = const Color(0xFFD97706);
        }

        return Container(
          margin: const EdgeInsets.only(bottom: AppSpacing.sm),
          decoration: BoxDecoration(
            color: AppColors.card,
            borderRadius: BorderRadius.circular(AppRadius.lg),
            border: Border.all(
              color: isChampion
                  ? const Color(0xFFF59E0B)
                  : (isMyTeam ? AppColors.primary.withOpacity(0.5) : AppColors.border),
              width: isChampion || isMyTeam ? 1.5 : 1.0,
            ),
            boxShadow: isChampion
                ? [
                    BoxShadow(
                      color: const Color(0xFFF59E0B).withOpacity(0.12),
                      blurRadius: 12,
                      offset: const Offset(0, 3),
                    )
                  ]
                : AppShadows.card,
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header: Rank + Category + Team Name + Solved count
              Container(
                padding: const EdgeInsets.symmetric(horizontal: AppSpacing.md, vertical: 8),
                decoration: BoxDecoration(
                  color: isChampion
                      ? const Color(0xFFF59E0B).withOpacity(0.08)
                      : (bColor.withOpacity(0.06)),
                  borderRadius: const BorderRadius.vertical(top: Radius.circular(AppRadius.lg - 1)),
                ),
                child: Row(
                  children: [
                    // Rank Badge
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                      decoration: BoxDecoration(
                        gradient: rank == 1
                            ? const LinearGradient(colors: [Color(0xFFF59E0B), Color(0xFFD97706)])
                            : (rank == 2
                                ? const LinearGradient(colors: [Color(0xFF94A3B8), Color(0xFF64748B)])
                                : (rank == 3
                                    ? const LinearGradient(colors: [Color(0xFFD97706), Color(0xFFB45309)])
                                    : null)),
                        color: rank > 3 ? AppColors.surface : null,
                        borderRadius: BorderRadius.circular(AppRadius.xs),
                        border: rank > 3 ? Border.all(color: AppColors.border) : null,
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          if (rank == 1) ...[
                            const Text('🏆', style: TextStyle(fontSize: 10)),
                            const SizedBox(width: 3),
                          ] else if (rank == 2) ...[
                            const Text('🥈', style: TextStyle(fontSize: 10)),
                            const SizedBox(width: 2),
                          ] else if (rank == 3) ...[
                            const Text('🥉', style: TextStyle(fontSize: 10)),
                            const SizedBox(width: 2),
                          ],
                          Text(
                            rank == 1 ? 'TOP #1' : '#$rank',
                            style: TextStyle(
                              color: rank <= 3 ? Colors.white : AppColors.textSecondary,
                              fontWeight: FontWeight.w900,
                              fontSize: 10,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 6),

                    // Category Pill
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: bColor.withOpacity(0.12),
                        borderRadius: BorderRadius.circular(AppRadius.xs),
                      ),
                      child: Text(
                        categoryLabel,
                        style: TextStyle(
                          color: bColor,
                          fontWeight: FontWeight.w700,
                          fontSize: 10,
                        ),
                      ),
                    ),
                    const SizedBox(width: 6),

                    // Team Name
                    Expanded(
                      child: Text(
                        teamName,
                        style: AppText.h3.copyWith(fontSize: 13),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),

                    if (isMyTeam) ...[
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: AppColors.primary.withOpacity(0.15),
                          borderRadius: BorderRadius.circular(AppRadius.pill),
                        ),
                        child: Text(
                          AppState.instance.t('My Team', 'আমার টিম'),
                          style: AppText.label.copyWith(color: AppColors.primary, fontSize: 9, fontWeight: FontWeight.w800),
                        ),
                      ),
                      const SizedBox(width: 6),
                    ],

                    if (area != null && area.isNotEmpty)
                      Text('📍 $area', style: AppText.caption.copyWith(fontSize: 10)),
                  ],
                ),
              ),

              // Competition Stats Bar (Done today vs Open)
              Padding(
                padding: const EdgeInsets.fromLTRB(AppSpacing.md, 8, AppSpacing.md, 4),
                child: Row(
                  children: [
                    // Solved Count Badge
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: AppColors.success.withOpacity(0.12),
                        borderRadius: BorderRadius.circular(AppRadius.pill),
                        border: Border.all(color: AppColors.success.withOpacity(0.3)),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.check_circle_rounded, size: 12, color: AppColors.success),
                          const SizedBox(width: 4),
                          Text(
                            '$teamDone ${AppState.instance.t('Solved Today', 'আজকের সলভ')}',
                            style: AppText.caption.copyWith(
                              color: AppColors.success,
                              fontWeight: FontWeight.w800,
                              fontSize: 10,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 6),

                    if (teamOpen > 0)
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                        decoration: BoxDecoration(
                          color: AppColors.warning.withOpacity(0.12),
                          borderRadius: BorderRadius.circular(AppRadius.pill),
                          border: Border.all(color: AppColors.warning.withOpacity(0.3)),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.hourglass_top_rounded, size: 11, color: AppColors.warning),
                            const SizedBox(width: 3),
                            Text(
                              '$teamOpen ${AppState.instance.t('Active', 'চলমান')}',
                              style: AppText.caption.copyWith(
                                color: AppColors.warning,
                                fontWeight: FontWeight.w700,
                                fontSize: 10,
                              ),
                            ),
                          ],
                        ),
                      ),

                    const Spacer(),

                    if (vehicleNo != null && vehicleNo.isNotEmpty)
                      Text('🛵 $vehicleNo', style: AppText.caption.copyWith(fontSize: 10)),
                  ],
                ),
              ),

              // Leader & Members
              Padding(
                padding: const EdgeInsets.fromLTRB(AppSpacing.md, 4, AppSpacing.md, AppSpacing.md),
                child: Column(
                  children: [
                    Row(
                      children: [
                        const Text('👑', style: TextStyle(fontSize: 12)),
                        const SizedBox(width: 4),
                        Text(AppState.instance.t('Leader: ', 'লিডার: '), style: AppText.caption.copyWith(fontWeight: FontWeight.w700, fontSize: 11)),
                        Expanded(
                          child: Row(
                            children: [
                              Flexible(
                                child: Text(
                                  leader?['name'] ?? 'N/A',
                                  style: AppText.bodySm.copyWith(fontWeight: FontWeight.w600, fontSize: 11),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                              if (leaderDone > 0) ...[
                                const SizedBox(width: 4),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
                                  decoration: BoxDecoration(
                                    color: AppColors.success.withOpacity(0.1),
                                    borderRadius: BorderRadius.circular(3),
                                  ),
                                  child: Text(
                                    '$leaderDone✓',
                                    style: TextStyle(color: AppColors.success, fontSize: 9, fontWeight: FontWeight.w800),
                                  ),
                                ),
                              ],
                            ],
                          ),
                        ),
                        if (leader?['phone'] != null)
                          InkWell(
                            onTap: () => _callUser(leader!['phone'] as String?),
                            child: const Padding(
                              padding: EdgeInsets.symmetric(horizontal: 4),
                              child: Icon(Icons.phone_rounded, size: 14, color: AppColors.success),
                            ),
                          ),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        const Text('👥', style: TextStyle(fontSize: 12)),
                        const SizedBox(width: 4),
                        Text(AppState.instance.t('Members: ', 'সদস্য: '), style: AppText.caption.copyWith(fontWeight: FontWeight.w700, fontSize: 11)),
                        Expanded(
                          child: Text(
                            '${member1?['name'] ?? 'N/A'}, ${member2?['name'] ?? 'N/A'}',
                            style: AppText.caption.copyWith(fontSize: 11),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
        );
      }).toList(),
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
