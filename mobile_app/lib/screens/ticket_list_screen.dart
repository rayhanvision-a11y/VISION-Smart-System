import 'dart:async';
import 'package:firebase_database/firebase_database.dart';
import 'package:flutter/material.dart';
import '../config/app_config.dart';
import '../models/ticket.dart';
import '../models/user.dart';
import '../services/api_service.dart';
import '../services/firebase_realtime_service.dart';
import '../services/storage_service.dart';
import '../widgets/priority_badge.dart';
import '../widgets/status_badge.dart';
import 'create_ticket_screen.dart';
import 'login_screen.dart';
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
  StreamSubscription<DatabaseEvent>? _firebaseSub;

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
    );
    if (!mounted) return;

    List<TicketModel> filteredList = list;
    if (_selectedFilter == 'my_tickets' && _currentUser != null) {
      filteredList = list.where((t) {
        return t.assignedTo == _currentUser!.id || t.createdBy == _currentUser!.id;
      }).toList();
    }

    setState(() {
      _tickets = filteredList;
      _isLoading = false;
    });
  }

  // Real-time synchronization listener
  void _listenToFirebaseRealtime() {
    try {
      _firebaseSub = FirebaseRealtimeService.getTicketsStream().listen((event) {
        if (event.snapshot.value != null && mounted) {
          // Trigger smooth local refresh when website pushes any change to Firebase
          _refreshTickets();
        }
      });
    } catch (_) {}
  }

  Future<void> _logout() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Sign Out'),
        content: const Text('Are you sure you want to log out?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancel')),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Sign Out', style: TextStyle(color: Colors.red)),
          ),
        ],
      ),
    );

    if (confirm == true) {
      await ApiService.logout();
      if (!mounted) return;
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => const LoginScreen()),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF1F5F9),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0.5,
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              AppConfig.appName,
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
            ),
            if (_currentUser != null)
              Text(
                '${_currentUser!.name} (${_currentUser!.role.toUpperCase()})',
                style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
              ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh, color: AppConfig.primaryColor),
            onPressed: _refreshTickets,
          ),
          IconButton(
            icon: const Icon(Icons.logout_rounded, color: Colors.grey),
            onPressed: _logout,
          ),
        ],
      ),
      body: Column(
        children: [
          // Filter Tabs
          Container(
            color: Colors.white,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  _filterChip('all', 'All'),
                  _filterChip('my_tickets', 'My Tickets 👤'),
                  _filterChip('in_progress', 'In Progress'),
                  _filterChip('pending', 'Pending'),
                  _filterChip('waiting_for_customer_feedback', 'Waiting'),
                  _filterChip('resolved', 'Resolved'),
                ],
              ),
            ),
          ),
          const Divider(height: 1),

          // Tickets List
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _tickets.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.inbox_outlined, size: 56, color: Colors.grey.shade400),
                            const SizedBox(height: 12),
                            Text(
                              'No tickets found',
                              style: TextStyle(color: Colors.grey.shade600, fontSize: 15),
                            ),
                          ],
                        ),
                      )
                    : RefreshIndicator(
                        onRefresh: _refreshTickets,
                        child: ListView.separated(
                          padding: const EdgeInsets.all(12),
                          itemCount: _tickets.length,
                          separatorBuilder: (_, __) => const SizedBox(height: 10),
                          itemBuilder: (context, index) {
                            final ticket = _tickets[index];
                            return _buildTicketCard(ticket);
                          },
                        ),
                      ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        heroTag: 'ticket_list_fab_new_ticket',
        backgroundColor: AppConfig.primaryColor,
        icon: const Icon(Icons.add_rounded, color: Colors.white),
        label: const Text('New Ticket', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
        onPressed: () async {
          final created = await Navigator.push<bool>(
            context,
            MaterialPageRoute(builder: (_) => const CreateTicketScreen()),
          );
          if (created == true) {
            _refreshTickets();
          }
        },
      ),
    );
  }

  Widget _filterChip(String key, String label) {
    final isSelected = _selectedFilter == key;
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: ChoiceChip(
        label: Text(label),
        selected: isSelected,
        selectedColor: AppConfig.primaryColor,
        labelStyle: TextStyle(
          color: isSelected ? Colors.white : Colors.grey.shade700,
          fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
          fontSize: 12,
        ),
        onSelected: (selected) {
          if (selected) {
            setState(() {
              _selectedFilter = key;
              _isLoading = true;
            });
            _refreshTickets();
          }
        },
      ),
    );
  }

  Widget _buildTicketCard(TicketModel ticket) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(14),
      elevation: 0,
      child: InkWell(
        borderRadius: BorderRadius.circular(14),
        onTap: () async {
          await Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => TicketDetailScreen(ticket: ticket, currentUser: _currentUser),
            ),
          );
          _refreshTickets();
        },
        child: Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
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
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: const Color(0xFFEFF6FF),
                      borderRadius: BorderRadius.circular(6),
                      border: Border.all(color: const Color(0xFFBFDBFE)),
                    ),
                    child: Text(
                      '#${ticket.ticketKey}',
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 11,
                        color: Color(0xFF1D4ED8),
                      ),
                    ),
                  ),
                  Row(
                    children: [
                      StatusBadge(status: ticket.status),
                      const SizedBox(width: 4),
                      const Icon(Icons.chevron_right_rounded, color: Color(0xFF94A3B8), size: 18),
                    ],
                  ),
                ],
              ),
              const SizedBox(height: 10),
              Text(
                ticket.title,
                style: const TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF0F172A),
                ),
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  PriorityBadge(priority: ticket.priority),
                  if (ticket.category != null && ticket.category!.isNotEmpty) ...[
                    const SizedBox(width: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF1F5F9),
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: Text(
                        ticket.category!.replaceAll('_', ' ').toUpperCase(),
                        style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: Color(0xFF475569)),
                      ),
                    ),
                  ],
                  const Spacer(),
                  if (ticket.assigneeName != null || ticket.creatorName != null)
                    Row(
                      children: [
                        const Icon(Icons.person_outline, size: 14, color: Color(0xFF64748B)),
                        const SizedBox(width: 4),
                        Text(
                          ticket.assigneeName ?? ticket.creatorName ?? '',
                          style: const TextStyle(fontSize: 11, color: Color(0xFF64748B), fontWeight: FontWeight.w500),
                        ),
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
}
