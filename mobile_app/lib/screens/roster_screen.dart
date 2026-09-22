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
      setState(() {
        _allRoster = data?['roster'] as List? ?? [];
        _teamsMap = data?['teams'] as Map<String, dynamic>? ?? {};
        _isLoading = false;
      });
    }
  }

  List<dynamic> get _filteredRoster {
    return _allRoster.where((member) {
      if (_selectedTeam != 'all' && member['team'] != _selectedTeam) {
        return false;
      }
      if (_searchQuery.isNotEmpty) {
        final q = _searchQuery.toLowerCase();
        final name = (member['name'] ?? '').toString().toLowerCase();
        final role = (member['role'] ?? '').toString().toLowerCase();
        final team = (member['team'] ?? '').toString().toLowerCase();
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
    );
  }
}
