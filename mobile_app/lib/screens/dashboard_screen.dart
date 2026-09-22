import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../services/storage_service.dart';
import '../models/user.dart';
import '../models/ticket.dart';
import 'create_ticket_screen.dart';
import 'ticket_detail_screen.dart';

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

    final stats = (_dashboardData?['stats'] is Map) ? (_dashboardData!['stats'] as Map) : {};
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
        onPressed: () async {
          final res = await Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const CreateTicketScreen()),
          );
          if (res == true) _loadData();
        },
        icon: const Icon(Icons.add),
        label: const Text('New Ticket'),
        backgroundColor: const Color(0xFF2563EB),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadData,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  // 1. Welcome Card
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFF1E3A8A), Color(0xFF2563EB)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(16),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFF2563EB).withOpacity(0.3),
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
                          child: const Icon(Icons.speed, color: Colors.white, size: 28),
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
                                'Live Tickets & Active Duty Roster',
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

                  // 2. Metrics Grid
                  const Text(
                    'Ticket Summary',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                  ),
                  const SizedBox(height: 10),
                  GridView.count(
                    crossAxisCount: 2,
                    crossAxisSpacing: 10,
                    mainAxisSpacing: 10,
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    childAspectRatio: 1.6,
                    children: [
                      _buildStatCard('Total Tickets', '${stats['total'] ?? 0}', Icons.confirmation_number_outlined, const Color(0xFF3B82F6), const Color(0xFFEFF6FF)),
                      _buildStatCard('In Progress', '${stats['in_progress'] ?? 0}', Icons.pending_actions, const Color(0xFF0284C7), const Color(0xFFE0F2FE)),
                      _buildStatCard('Pending', '${stats['pending'] ?? 0}', Icons.hourglass_top, const Color(0xFFF59E0B), const Color(0xFFFEF3C7)),
                      _buildStatCard('Waiting Feedback', '${stats['waiting_for_customer_feedback'] ?? 0}', Icons.chat_bubble_outline, const Color(0xFF8B5CF6), const Color(0xFFEDE9FE)),
                      _buildStatCard('Resolved', '${stats['resolved'] ?? 0}', Icons.check_circle_outline, const Color(0xFF10B981), const Color(0xFFD1FAE5)),
                      _buildStatCard('Urgent / Overdue', '${stats['urgent'] ?? 0} / ${stats['overdue'] ?? 0}', Icons.warning_amber_rounded, const Color(0xFFEF4444), const Color(0xFFFEE2E2)),
                    ],
                  ),
                  const SizedBox(height: 22),

                  // 3. 4-Team Duty Roster (Matching Web Design with Day/Night/Off)
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'Active Duty Teams',
                        style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: const Color(0xFF10B981).withOpacity(0.12),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: const Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(Icons.circle, color: Color(0xFF10B981), size: 8),
                            SizedBox(width: 4),
                            Text('Live Roster', style: TextStyle(color: Color(0xFF047857), fontSize: 11, fontWeight: FontWeight.w600)),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 10),
                  if (dutyTeams.isEmpty)
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFFE2E8F0)),
                      ),
                      child: const Center(child: Text('No duty roster data available', style: TextStyle(color: Colors.grey))),
                    )
                  else
                    ...dutyTeams.map((team) => _buildTeamDutyCard(team)).toList(),

                  const SizedBox(height: 22),

                  // 4. Recent Tickets
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'Recent Tickets',
                        style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                      ),
                      if (widget.onSwitchToTickets != null)
                        TextButton(
                          onPressed: widget.onSwitchToTickets,
                          child: const Text('View All', style: TextStyle(fontWeight: FontWeight.bold)),
                        ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  if (recentTickets.isEmpty)
                    Container(
                      padding: const EdgeInsets.all(20),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFFE2E8F0)),
                      ),
                      child: const Center(
                        child: Text('No recent tickets found', style: TextStyle(color: Colors.grey)),
                      ),
                    )
                  else
                    ...recentTickets.map((t) => _buildRecentTicketCard(t)).toList(),

                  const SizedBox(height: 40),
                ],
              ),
            ),
    );
  }

  Widget _buildStatCard(String title, String count, IconData icon, Color color, Color bgColor) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.02),
            blurRadius: 6,
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
              Text(
                title,
                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF64748B)),
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
          Text(
            count,
            style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: color),
          ),
        ],
      ),
    );
  }

  Widget _buildTeamDutyCard(dynamic team) {
    final label = team['team_label'] ?? team['team_key'] ?? '';
    final total = team['total_members'] ?? 0;
    final active = team['active_members'] ?? 0;
    final dayShift = team['day_shift'] ?? 0;
    final nightShift = team['night_shift'] ?? 0;
    final dayOff = team['day_off'] ?? 0;
    final pct = team['active_percentage'] ?? 0;

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.02),
            blurRadius: 4,
            offset: const Offset(0, 1),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                label,
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF1E293B)),
              ),
              Text(
                '$active / $total On Duty ($pct%)',
                style: TextStyle(
                  fontWeight: FontWeight.bold,
                  fontSize: 12,
                  color: active > 0 ? const Color(0xFF10B981) : const Color(0xFF64748B),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          ClipRRect(
            borderRadius: BorderRadius.circular(4),
            child: LinearProgressIndicator(
              value: total > 0 ? (active / total).clamp(0.0, 1.0) : 0.0,
              backgroundColor: const Color(0xFFF1F5F9),
              color: const Color(0xFF3B82F6),
              minHeight: 5,
            ),
          ),
          const SizedBox(height: 10),
          Row(
            children: [
              _buildShiftPill('Day Shift: $dayShift', const Color(0xFF0284C7), const Color(0xFFE0F2FE)),
              const SizedBox(width: 6),
              _buildShiftPill('Night Shift: $nightShift', const Color(0xFF4F46E5), const Color(0xFFEEF2FF)),
              const SizedBox(width: 6),
              _buildShiftPill('Off: $dayOff', const Color(0xFF64748B), const Color(0xFFF1F5F9)),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildShiftPill(String text, Color textColor, Color bgColor) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(6),
      ),
      child: Text(
        text,
        style: TextStyle(color: textColor, fontSize: 10, fontWeight: FontWeight.w600),
      ),
    );
  }

  Widget _buildRecentTicketCard(TicketModel ticket) {
    return Card(
      elevation: 0,
      margin: const EdgeInsets.only(bottom: 8),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: const BorderSide(color: Color(0xFFE2E8F0)),
      ),
      child: ListTile(
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => TicketDetailScreen(ticket: ticket),
            ),
          ).then((_) => _loadData());
        },
        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
              decoration: BoxDecoration(
                color: const Color(0xFFF1F5F9),
                borderRadius: BorderRadius.circular(4),
              ),
              child: Text(
                ticket.ticketKey,
                style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFF475569)),
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                ticket.title,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
              ),
            ),
          ],
        ),
        subtitle: Padding(
          padding: const EdgeInsets.only(top: 4),
          child: Row(
            children: [
              Text(
                'Status: ${ticket.status.replaceAll('_', ' ')}',
                style: const TextStyle(fontSize: 11, color: Color(0xFF64748B)),
              ),
              if (ticket.assigneeName != null) ...[
                const Text(' • ', style: TextStyle(color: Color(0xFFCBD5E1))),
                Expanded(
                  child: Text(
                    'Assigned: ${ticket.assigneeName}',
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontSize: 11, color: Color(0xFF64748B)),
                  ),
                ),
              ],
            ],
          ),
        ),
        trailing: const Icon(Icons.chevron_right, color: Color(0xFF94A3B8), size: 18),
      ),
    );
  }
}
