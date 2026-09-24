import 'package:flutter/material.dart';
import '../config/app_config.dart';
import '../services/api_service.dart';
import '../services/storage_service.dart';
import '../models/user.dart';
import '../models/ticket.dart';
import 'create_ticket_screen.dart';
import 'ticket_detail_screen.dart';
import 'ticket_list_screen.dart';
import 'roster_screen.dart';

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

  void _navigateToFilteredTickets(String? statusFilter) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => Scaffold(
          appBar: AppBar(
            title: Text(
              statusFilter == null
                  ? 'All Tickets'
                  : 'Tickets: ${statusFilter.replaceAll('_', ' ').toUpperCase()}',
              style: const TextStyle(fontWeight: FontWeight.bold),
            ),
          ),
          body: TicketListScreen(initialStatus: statusFilter),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return Scaffold(
        backgroundColor: const Color(0xFFF8FAFC),
        appBar: AppBar(
          title: const Text('VISION Smart System', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
        ),
        body: const Center(child: CircularProgressIndicator()),
      );
    }

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

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'VISION Smart System',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
            ),
            if (_currentUser != null)
              Text(
                '${_currentUser!.name} (${_currentUser!.role.toUpperCase().replaceAll('_', ' ')})',
                style: const TextStyle(fontSize: 11, color: Colors.white70),
              ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadData,
            tooltip: 'Refresh Dashboard',
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        heroTag: 'dashboard_fab_new_ticket',
        onPressed: () async {
          final res = await Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const CreateTicketScreen()),
          );
          if (res == true) _loadData();
        },
        icon: const Icon(Icons.add),
        label: const Text('New Ticket'),
        backgroundColor: const Color(0xFFDC2626),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadData,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  // 1. Welcome Header Banner Card
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFF991B1B), Color(0xFFDC2626)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(16),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFFDC2626).withOpacity(0.3),
                          blurRadius: 10,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: Row(
                      children: [
                        CircleAvatar(
                          radius: 26,
                          backgroundColor: Colors.white.withOpacity(0.2),
                          child: const Icon(Icons.speed_rounded, color: Colors.white, size: 28),
                        ),
                        const SizedBox(width: 14),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'System Overview',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                'Select a button below to view details',
                                style: TextStyle(
                                  color: Colors.white.withOpacity(0.85),
                                  fontSize: 12,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 18),

                  // 2. Interactive Ticket Metric Buttons (Clickable Summary Cards)
                  const Text(
                    'Ticket Overview',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                  ),
                  const SizedBox(height: 10),
                  GridView.count(
                    crossAxisCount: 2,
                    crossAxisSpacing: 10,
                    mainAxisSpacing: 10,
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    childAspectRatio: 1.5,
                    children: [
                      _buildStatCard(
                        'Total Tickets',
                        '${stats['total'] ?? 0}',
                        Icons.confirmation_number_outlined,
                        const Color(0xFF3B82F6),
                        const Color(0xFFEFF6FF),
                        onTap: () => _navigateToFilteredTickets(null),
                      ),
                      _buildStatCard(
                        'In Progress',
                        '${stats['in_progress'] ?? 0}',
                        Icons.pending_actions,
                        const Color(0xFF0284C7),
                        const Color(0xFFE0F2FE),
                        onTap: () => _navigateToFilteredTickets('in_progress'),
                      ),
                      _buildStatCard(
                        'Pending',
                        '${stats['pending'] ?? 0}',
                        Icons.hourglass_top,
                        const Color(0xFFF59E0B),
                        const Color(0xFFFEF3C7),
                        onTap: () => _navigateToFilteredTickets('pending'),
                      ),
                      _buildStatCard(
                        'Waiting Feedback',
                        '${stats['waiting_for_customer_feedback'] ?? 0}',
                        Icons.chat_bubble_outline,
                        const Color(0xFF8B5CF6),
                        const Color(0xFFEDE9FE),
                        onTap: () => _navigateToFilteredTickets('waiting_for_customer_feedback'),
                      ),
                      _buildStatCard(
                        'Resolved',
                        '${stats['resolved'] ?? 0}',
                        Icons.check_circle_outline,
                        const Color(0xFF10B981),
                        const Color(0xFFD1FAE5),
                        onTap: () => _navigateToFilteredTickets('resolved'),
                      ),
                      _buildStatCard(
                        'Urgent / Overdue',
                        '${stats['urgent'] ?? 0} / ${stats['overdue'] ?? 0}',
                        Icons.warning_amber_rounded,
                        const Color(0xFFEF4444),
                        const Color(0xFFFEE2E2),
                        onTap: () => _navigateToFilteredTickets('urgent'),
                      ),
                    ],
                  ),
                  const SizedBox(height: 22),

                  // 3. Quick Action Buttons (MTB App Fintech Style Shortcuts)
                  const Text(
                    'Services & Navigation',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                  ),
                  const SizedBox(height: 10),

                  // Button 1: All Tickets Button Card
                  _buildMenuButton(
                    title: 'All Tickets & History',
                    subtitle: 'View all ${stats['total'] ?? 0} support tickets',
                    icon: Icons.confirmation_number_rounded,
                    iconBgColor: const Color(0xFFEFF6FF),
                    iconColor: const Color(0xFF2563EB),
                    onTap: widget.onSwitchToTickets ?? () => _navigateToFilteredTickets(null),
                  ),
                  const SizedBox(height: 10),

                  // Button 2: My Tickets Button Card
                  _buildMenuButton(
                    title: 'My Assigned Tickets 👤',
                    subtitle: 'Tickets assigned to or created by you',
                    icon: Icons.person_pin_rounded,
                    iconBgColor: const Color(0xFFFEF3C7),
                    iconColor: const Color(0xFFD97706),
                    onTap: () => _navigateToFilteredTickets('my_tickets'),
                  ),
                  const SizedBox(height: 10),

                  // Button 3: Duty Roster Button Card
                  _buildMenuButton(
                    title: 'Active Duty Roster',
                    subtitle: '${dutyTeams.length} active teams shift schedule & contacts',
                    icon: Icons.calendar_month_rounded,
                    iconBgColor: const Color(0xFFD1FAE5),
                    iconColor: const Color(0xFF059669),
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => Scaffold(
                            appBar: AppBar(
                              title: const Text('Duty Roster & Shift Schedule', style: TextStyle(fontWeight: FontWeight.bold)),
                            ),
                            body: const RosterScreen(),
                          ),
                        ),
                      );
                    },
                  ),
                  const SizedBox(height: 10),

                  // Button 4: Create Ticket Button Card
                  _buildMenuButton(
                    title: 'Create New Ticket ➕',
                    subtitle: 'Submit a new support ticket request',
                    icon: Icons.add_circle_outline_rounded,
                    iconBgColor: const Color(0xFFFEE2E2),
                    iconColor: const Color(0xFFDC2626),
                    onTap: () async {
                      final res = await Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const CreateTicketScreen()),
                      );
                      if (res == true) _loadData();
                    },
                  ),
                  const SizedBox(height: 40),
                ],
              ),
            ),
    );
  }

  Widget _buildMenuButton({
    required String title,
    required String subtitle,
    required IconData icon,
    required Color iconBgColor,
    required Color iconColor,
    required VoidCallback onTap,
  }) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(16),
      elevation: 0,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFE2E8F0)),
          ),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: iconBgColor,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(icon, color: iconColor, size: 24),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F172A)),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      subtitle,
                      style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                    ),
                  ],
                ),
              ),
              const Icon(Icons.arrow_forward_ios_rounded, size: 16, color: Color(0xFF94A3B8)),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildStatCard(
    String title,
    String count,
    IconData icon,
    Color color,
    Color bgColor, {
    VoidCallback? onTap,
  }) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(16),
      elevation: 0,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        splashColor: color.withOpacity(0.12),
        highlightColor: color.withOpacity(0.06),
        child: Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFE2E8F0)),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.02),
                blurRadius: 8,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Text(
                      title,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF64748B)),
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.all(6),
                    decoration: BoxDecoration(
                      color: bgColor,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Icon(icon, size: 16, color: color),
                  ),
                ],
              ),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text(
                    count,
                    style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: color),
                  ),
                  Icon(
                    Icons.chevron_right_rounded,
                    size: 18,
                    color: color.withOpacity(0.7),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}

