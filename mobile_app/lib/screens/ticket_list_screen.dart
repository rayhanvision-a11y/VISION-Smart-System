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
  const TicketListScreen({Key? key}) : super(key: key);

  @override
  State<TicketListScreen> createState() => _TicketListScreenState();
}

class _TicketListScreenState extends State<TicketListScreen> {
  UserModel? _currentUser;
  List<TicketModel> _tickets = [];
  bool _isLoading = true;
  String _selectedFilter = 'all';
  StreamSubscription<DatabaseEvent>? _firebaseSub;

  @override
  void initState() {
    super.initState();
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
      status: _selectedFilter == 'all' ? null : _selectedFilter,
    );
    if (!mounted) return;
    setState(() {
      _tickets = list;
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
                  _filterChip('open', 'Open'),
                  _filterChip('in_progress', 'In Progress'),
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
      borderRadius: BorderRadius.circular(12),
      elevation: 0.5,
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: () async {
          await Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => TicketDetailScreen(ticket: ticket, currentUser: _currentUser),
            ),
          );
          _refreshTickets();
        },
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    '#${ticket.ticketKey}',
                    style: const TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 13,
                      color: AppConfig.primaryColor,
                    ),
                  ),
                  StatusBadge(status: ticket.status),
                ],
              ),
              const SizedBox(height: 8),
              Text(
                ticket.title,
                style: const TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.w600,
                  color: Color(0xFF1E293B),
                ),
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  PriorityBadge(priority: ticket.priority),
                  const Spacer(),
                  if (ticket.assigneeName != null)
                    Row(
                      children: [
                        Icon(Icons.person_outline, size: 14, color: Colors.grey.shade600),
                        const SizedBox(width: 4),
                        Text(
                          ticket.assigneeName!,
                          style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
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
