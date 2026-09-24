import 'package:flutter/material.dart';
import '../services/api_service.dart';

class RosterScreen extends StatefulWidget {
  const RosterScreen({Key? key}) : super(key: key);

  @override
  State<RosterScreen> createState() => _RosterScreenState();
}

class _RosterScreenState extends State<RosterScreen> {
  bool _isLoading = true;
  List<dynamic> _allRoster = [];
  Map<String, dynamic> _teamsMap = {};
  String _selectedTeam = 'all';
  String _searchQuery = '';

  @override
  void initState() {
    super.initState();
    _loadRoster();
  }

  Future<void> _loadRoster() async {
    setState(() => _isLoading = true);
    final data = await ApiService.getRoster();
    if (mounted) {
      final rosterList = data?['roster'] as List? ?? [];
      final List<dynamic> allMembers = [];
      final Map<String, dynamic> teams = {};

      for (var teamItem in rosterList) {
        if (teamItem is Map) {
          final tKey = (teamItem['team_key'] ?? '').toString();
          final tLabel = (teamItem['team_label'] ?? tKey).toString();
          if (tKey.isNotEmpty) {
            teams[tKey] = tLabel;
          }
          final members = teamItem['members'] as List? ?? [];
          for (var m in members) {
            if (m is Map) {
              final memberMap = Map<String, dynamic>.from(m);
              memberMap['team_name'] = tLabel;
              allMembers.add(memberMap);
            }
          }
        }
      }

      setState(() {
        _allRoster = allMembers;
        _teamsMap = teams;
        _isLoading = false;
      });
    }
  }

  List<dynamic> get _filteredRoster {
    return _allRoster.where((member) {
      if (_selectedTeam != 'all') {
        final memberTeamKey = (member['team'] ?? '').toString();
        final memberTeamLabel = (member['team_name'] ?? '').toString();
        final selectedLabel = (_teamsMap[_selectedTeam] ?? '').toString();
        if (memberTeamKey != _selectedTeam && memberTeamLabel != selectedLabel) {
          return false;
        }
      }
      if (_searchQuery.isNotEmpty) {
        final q = _searchQuery.toLowerCase();
        final name = (member['name'] ?? '').toString().toLowerCase();
        final role = (member['role'] ?? '').toString().toLowerCase();
        final team = (member['team_name'] ?? member['team'] ?? '').toString().toLowerCase();
        if (!name.contains(q) && !role.contains(q) && !team.contains(q)) {
          return false;
        }
      }
      return true;
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    final filtered = _filteredRoster;

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('Duty Roster & Shifts', style: TextStyle(fontWeight: FontWeight.bold)),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadRoster,
            tooltip: 'Refresh',
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                // 1. Search Bar
                Padding(
                  padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
                  child: TextField(
                    decoration: InputDecoration(
                      hintText: 'Search staff by name or role...',
                      prefixIcon: const Icon(Icons.search, size: 20),
                      filled: true,
                      fillColor: Colors.white,
                      contentPadding: const EdgeInsets.symmetric(vertical: 0, horizontal: 16),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
                      ),
                      enabledBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
                      ),
                    ),
                    onChanged: (val) => setState(() => _searchQuery = val.trim()),
                  ),
                ),

                // 2. Team Filter Chips
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                  child: Row(
                    children: [
                      _buildFilterChip('All Teams', 'all'),
                      const SizedBox(width: 8),
                      ..._teamsMap.entries.map((e) {
                        return Padding(
                          padding: const EdgeInsets.only(right: 8),
                          child: _buildFilterChip(e.value.toString(), e.key),
                        );
                      }).toList(),
                    ],
                  ),
                ),
                const SizedBox(height: 8),

                // 3. Roster List
                Expanded(
                  child: RefreshIndicator(
                    onRefresh: _loadRoster,
                    child: filtered.isEmpty
                        ? ListView(
                            children: const [
                              SizedBox(height: 80),
                              Center(child: Text('No roster members found', style: TextStyle(color: Colors.grey))),
                            ],
                          )
                        : ListView.builder(
                            padding: const EdgeInsets.fromLTRB(16, 4, 16, 24),
                            itemCount: filtered.length,
                            itemBuilder: (context, index) {
                              final item = filtered[index];
                              return _buildStaffRosterCard(item);
                            },
                          ),
                  ),
                ),
              ],
            ),
    );
  }

  Widget _buildFilterChip(String label, String key) {
    final isSelected = _selectedTeam == key;
    return ChoiceChip(
      label: Text(label, style: TextStyle(color: isSelected ? Colors.white : const Color(0xFF334155), fontSize: 12, fontWeight: FontWeight.w600)),
      selected: isSelected,
      selectedColor: const Color(0xFF2563EB),
      backgroundColor: Colors.white,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(8),
        side: BorderSide(color: isSelected ? const Color(0xFF2563EB) : const Color(0xFFCBD5E1)),
      ),
      onSelected: (_) => setState(() => _selectedTeam = key),
    );
  }

  Widget _buildStaffRosterCard(dynamic member) {
    final name = member['name'] ?? '';
    final email = member['email'] ?? '';
    final role = (member['role'] ?? '').toString().replaceAll('_', ' ').toUpperCase();
    final teamName = member['team_name'] ?? member['team'] ?? 'Unassigned';
    final shift = member['current_shift'] ?? 'day_shift';
    final isOnDuty = member['is_on_duty'] == true;

    Color shiftColor = const Color(0xFF0284C7);
    Color shiftBg = const Color(0xFFE0F2FE);
    String shiftLabel = 'Day Shift';
    if (shift == 'night_shift') {
      shiftColor = const Color(0xFF4F46E5);
      shiftBg = const Color(0xFFEEF2FF);
      shiftLabel = 'Night Shift';
    } else if (shift == 'day_off') {
      shiftColor = const Color(0xFF64748B);
      shiftBg = const Color(0xFFF1F5F9);
      shiftLabel = 'Day Off';
    }

    return Card(
      elevation: 0,
      margin: const EdgeInsets.only(bottom: 10),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: const BorderSide(color: Color(0xFFE2E8F0)),
      ),
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: () => _showStaffDetail(member),
        child: Padding(
        padding: const EdgeInsets.all(12),
        child: Row(
          children: [
            CircleAvatar(
              radius: 22,
              backgroundColor: const Color(0xFF2563EB).withOpacity(0.1),
              child: Text(
                name.isNotEmpty ? name[0].toUpperCase() : '?',
                style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF2563EB)),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    name,
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF1E293B)),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    '$role • $teamName',
                    style: const TextStyle(fontSize: 11, color: Color(0xFF64748B)),
                  ),
                  if (email.isNotEmpty)
                    Text(
                      email,
                      style: const TextStyle(fontSize: 10, color: Color(0xFF94A3B8)),
                    ),
                ],
              ),
            ),
            Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                // Duty Status badge
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: isOnDuty ? const Color(0xFFD1FAE5) : const Color(0xFFF1F5F9),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        Icons.circle,
                        size: 7,
                        color: isOnDuty ? const Color(0xFF10B981) : const Color(0xFF94A3B8),
                      ),
                      const SizedBox(width: 4),
                      Text(
                        isOnDuty ? 'On Duty' : 'Off Duty',
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.bold,
                          color: isOnDuty ? const Color(0xFF047857) : const Color(0xFF64748B),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 4),
                // Shift pill
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                  decoration: BoxDecoration(
                    color: shiftBg,
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: Text(
                    shiftLabel,
                    style: TextStyle(color: shiftColor, fontSize: 10, fontWeight: FontWeight.w600),
                  ),
                ),
              ],
            ),
          ],
        ),
        ),
      ),
    );
  }

  void _showStaffDetail(dynamic member) {
    final name = (member['name'] ?? '').toString();
    final email = (member['email'] ?? '').toString();
    final phone = (member['phone'] ?? '').toString();
    final role = (member['role'] ?? '').toString().replaceAll('_', ' ').toUpperCase();
    final teamName = (member['team_name'] ?? member['team'] ?? 'Unassigned').toString();
    final shift = (member['current_shift'] ?? '').toString().replaceAll('_', ' ');
    final isOnDuty = member['is_on_duty'] == true;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => Container(
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
        padding: const EdgeInsets.fromLTRB(20, 12, 20, 24),
        child: SafeArea(
          top: false,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 40, height: 4,
                  decoration: BoxDecoration(color: const Color(0xFFE2E8F0), borderRadius: BorderRadius.circular(2)),
                ),
              ),
              const SizedBox(height: 20),
              Row(
                children: [
                  CircleAvatar(
                    radius: 30,
                    backgroundColor: const Color(0xFFEFF6FF),
                    child: Text(name.isNotEmpty ? name[0].toUpperCase() : '?', style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: Color(0xFF2563EB))),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(name, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
                        const SizedBox(height: 4),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: isOnDuty ? const Color(0xFFD1FAE5) : const Color(0xFFF1F5F9),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            isOnDuty ? 'On Duty' : 'Off Duty',
                            style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: isOnDuty ? const Color(0xFF047857) : const Color(0xFF64748B)),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 20),
              const Divider(height: 1),
              const SizedBox(height: 16),
              _staffRow(Icons.badge_outlined, 'Role', role),
              _staffRow(Icons.groups_outlined, 'Team', teamName),
              if (shift.isNotEmpty) _staffRow(Icons.schedule_outlined, 'Current Shift', shift),
              if (email.isNotEmpty) _staffRow(Icons.email_outlined, 'Email', email),
              if (phone.isNotEmpty) _staffRow(Icons.phone_outlined, 'Phone', phone),
            ],
          ),
        ),
      ),
    );
  }

  Widget _staffRow(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 18, color: const Color(0xFF64748B)),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(label, style: const TextStyle(fontSize: 11, color: Color(0xFF64748B), fontWeight: FontWeight.w500)),
                const SizedBox(height: 2),
                Text(value, style: const TextStyle(fontSize: 14, color: Color(0xFF0F172A), fontWeight: FontWeight.w600)),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
