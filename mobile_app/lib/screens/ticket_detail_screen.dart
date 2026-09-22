import 'package:firebase_database/firebase_database.dart';
import 'package:flutter/material.dart';
import '../config/app_config.dart';
import '../models/ticket.dart';
import '../models/ticket_message.dart';
import '../models/user.dart';
import '../services/api_service.dart';
import '../services/firebase_realtime_service.dart';
import '../services/storage_service.dart';
import '../widgets/message_bubble.dart';
import '../widgets/priority_badge.dart';
import '../widgets/status_badge.dart';

class TicketDetailScreen extends StatefulWidget {
  final TicketModel ticket;
  final UserModel? currentUser;

  const TicketDetailScreen({
    Key? key,
    required this.ticket,
    this.currentUser,
  }) : super(key: key);

  @override
  State<TicketDetailScreen> createState() => _TicketDetailScreenState();
}

class _TicketDetailScreenState extends State<TicketDetailScreen> {
  late TicketModel _ticket;
  UserModel? _user;
  final _messageController = TextEditingController();
  bool _isSending = false;
  bool _isPrivateReply = false;
  final ScrollController _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    _ticket = widget.ticket;
    _user = widget.currentUser;
    if (_user == null) {
      _loadCurrentUser();
    }
  }

  Future<void> _loadCurrentUser() async {
    final u = await StorageService.getUser();
    if (mounted) {
      setState(() => _user = u);
    }
  }

  bool get _isAdminOrStaff {
    if (_user == null) return false;
    final r = _user!.role.toLowerCase();
    return r != 'reseller';
  }

  Future<void> _sendMessage() async {
    final text = _messageController.text.trim();
    if (text.isEmpty) return;

    setState(() => _isSending = true);
    _messageController.clear();

    await ApiService.addMessage(
      _ticket.id,
      text,
      isPrivate: _isPrivateReply,
    );

    if (mounted) {
      setState(() => _isSending = false);
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent + 80,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      }
    }
  }

  Future<void> _showStatusDialog() async {
    final statuses = ['in_progress', 'pending', 'waiting_for_customer_feedback', 'resolved'];

    final selected = await showModalBottomSheet<String>(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
      ),
      builder: (ctx) => Padding(
        padding: const EdgeInsets.symmetric(vertical: 20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Padding(
              padding: EdgeInsets.symmetric(horizontal: 20),
              child: Text(
                'Change Ticket Status',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
            ),
            const SizedBox(height: 10),
            ...statuses.map(
              (s) => ListTile(
                leading: Container(
                  width: 12,
                  height: 12,
                  decoration: BoxDecoration(
                    color: AppConfig.statusColor(s),
                    shape: BoxShape.circle,
                  ),
                ),
                title: Text(
                  s.replaceAll('_', ' ').toUpperCase(),
                  style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
                ),
                trailing: _ticket.status == s ? const Icon(Icons.check, color: AppConfig.primaryColor) : null,
                onTap: () => Navigator.pop(ctx, s),
              ),
            ),
          ],
        ),
      ),
    );

    if (selected != null && selected != _ticket.status) {
      final success = await ApiService.updateTicketStatus(_ticket.id, selected);
      if (success && mounted) {
        setState(() {
          _ticket = TicketModel(
            id: _ticket.id,
            ticketKey: _ticket.ticketKey,
            title: _ticket.title,
            description: _ticket.description,
            priority: _ticket.priority,
            status: selected,
            category: _ticket.category,
            createdBy: _ticket.createdBy,
            assignedTo: _ticket.assignedTo,
            assigneeName: _ticket.assigneeName,
            creatorName: _ticket.creatorName,
            createdAt: _ticket.createdAt,
            updatedAt: _ticket.updatedAt,
          );
        });
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Status updated to ${selected.replaceAll('_', ' ').toUpperCase()}')),
        );
      }
    }
  }

  Future<void> _showPriorityDialog() async {
    final priorities = ['low', 'medium', 'high', 'urgent'];

    final selected = await showModalBottomSheet<String>(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
      ),
      builder: (ctx) => Padding(
        padding: const EdgeInsets.symmetric(vertical: 20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Padding(
              padding: EdgeInsets.symmetric(horizontal: 20),
              child: Text(
                'Change Ticket Priority',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
            ),
            const SizedBox(height: 10),
            ...priorities.map(
              (p) => ListTile(
                leading: Container(
                  width: 12,
                  height: 12,
                  decoration: BoxDecoration(
                    color: AppConfig.priorityColor(p),
                    shape: BoxShape.circle,
                  ),
                ),
                title: Text(
                  p.toUpperCase(),
                  style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
                ),
                trailing: _ticket.priority == p ? const Icon(Icons.check, color: AppConfig.primaryColor) : null,
                onTap: () => Navigator.pop(ctx, p),
              ),
            ),
          ],
        ),
      ),
    );

    if (selected != null && selected != _ticket.priority) {
      final success = await ApiService.updateTicketPriority(_ticket.id, selected);
      if (success && mounted) {
        setState(() {
          _ticket = TicketModel(
            id: _ticket.id,
            ticketKey: _ticket.ticketKey,
            title: _ticket.title,
            description: _ticket.description,
            priority: selected,
            status: _ticket.status,
            category: _ticket.category,
            createdBy: _ticket.createdBy,
            assignedTo: _ticket.assignedTo,
            assigneeName: _ticket.assigneeName,
            creatorName: _ticket.creatorName,
            createdAt: _ticket.createdAt,
            updatedAt: _ticket.updatedAt,
          );
        });
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Priority updated to ${selected.toUpperCase()}')),
        );
      }
    }
  }

  Future<void> _showAssignStaffDialog() async {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => const Center(child: CircularProgressIndicator()),
    );

    final staffList = await ApiService.getStaff();
    if (mounted) Navigator.pop(context); // close loader

    if (!mounted) return;

    final selected = await showModalBottomSheet<dynamic>(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
      ),
      builder: (ctx) => Container(
        height: MediaQuery.of(context).size.height * 0.6,
        padding: const EdgeInsets.symmetric(vertical: 20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Padding(
              padding: EdgeInsets.symmetric(horizontal: 20),
              child: Text(
                'Assign Staff Member',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
            ),
            const SizedBox(height: 10),
            Expanded(
              child: staffList.isEmpty
                  ? const Center(child: Text('No eligible staff members found'))
                  : ListView.builder(
                      itemCount: staffList.length,
                      itemBuilder: (c, i) {
                        final s = staffList[i];
                        final id = s['id'];
                        final name = s['name'] ?? '';
                        final role = (s['role'] ?? '').toString().replaceAll('_', ' ').toUpperCase();
                        final isAssigned = _ticket.assignedTo == id;

                        return ListTile(
                          leading: CircleAvatar(
                            backgroundColor: const Color(0xFFEFF6FF),
                            child: Text(
                              name.isNotEmpty ? name[0].toUpperCase() : 'S',
                              style: const TextStyle(color: Color(0xFF2563EB), fontWeight: FontWeight.bold),
                            ),
                          ),
                          title: Text(name, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                          subtitle: Text(role, style: const TextStyle(fontSize: 11, color: Colors.grey)),
                          trailing: isAssigned ? const Icon(Icons.check_circle, color: Color(0xFF10B981)) : null,
                          onTap: () => Navigator.pop(ctx, s),
                        );
                      },
                    ),
            ),
          ],
        ),
      ),
    );

    if (selected != null && selected['id'] != null) {
      final staffId = selected['id'] as int;
      final staffName = selected['name'] ?? 'Staff';
      final success = await ApiService.assignTicket(_ticket.id, staffId);
      if (success && mounted) {
        setState(() {
          _ticket = TicketModel(
            id: _ticket.id,
            ticketKey: _ticket.ticketKey,
            title: _ticket.title,
            description: _ticket.description,
            priority: _ticket.priority,
            status: _ticket.status,
            category: _ticket.category,
            createdBy: _ticket.createdBy,
            assignedTo: staffId,
            assigneeName: staffName,
            creatorName: _ticket.creatorName,
            createdAt: _ticket.createdAt,
            updatedAt: _ticket.updatedAt,
          );
        });
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Ticket assigned to $staffName')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: Text(
          '#${_ticket.ticketKey}',
          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.swap_horiz),
            tooltip: 'Change Status',
            onPressed: _showStatusDialog,
          ),
          if (_isAdminOrStaff) ...[
            IconButton(
              icon: const Icon(Icons.flag_outlined),
              tooltip: 'Change Priority',
              onPressed: _showPriorityDialog,
            ),
            IconButton(
              icon: const Icon(Icons.person_add_alt_1_outlined),
              tooltip: 'Assign Staff',
              onPressed: _showAssignStaffDialog,
            ),
          ],
        ],
      ),
      body: Column(
        children: [
          // Ticket Header Card
          Container(
            padding: const EdgeInsets.all(16),
            decoration: const BoxDecoration(
              color: Colors.white,
              border: Border(bottom: BorderSide(color: Color(0xFFE2E8F0))),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    InkWell(
                      onTap: _showStatusDialog,
                      borderRadius: BorderRadius.circular(6),
                      child: StatusBadge(status: _ticket.status),
                    ),
                    InkWell(
                      onTap: _isAdminOrStaff ? _showPriorityDialog : null,
                      borderRadius: BorderRadius.circular(6),
                      child: PriorityBadge(priority: _ticket.priority),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                Text(
                  _ticket.title,
                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                ),
                if (_ticket.description.isNotEmpty) ...[
                  const SizedBox(height: 8),
                  Text(
                    _ticket.description,
                    style: const TextStyle(fontSize: 13, color: Color(0xFF475569), height: 1.4),
                  ),
                ],
                const SizedBox(height: 12),
                Wrap(
                  spacing: 12,
                  runSpacing: 6,
                  children: [
                    if (_ticket.category != null)
                      _buildMetaChip(Icons.category_outlined, _ticket.category!),
                    InkWell(
                      onTap: _isAdminOrStaff ? _showAssignStaffDialog : null,
                      borderRadius: BorderRadius.circular(4),
                      child: _buildMetaChip(
                        Icons.person_outline,
                        _ticket.assigneeName != null ? 'Assigned: ${_ticket.assigneeName}' : 'Unassigned (Tap to assign)',
                        color: _ticket.assigneeName != null ? const Color(0xFF2563EB) : const Color(0xFFD97706),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),

          // Real-time Chat Messages Stream
          Expanded(
            child: StreamBuilder<DatabaseEvent>(
              stream: FirebaseRealtimeService.getTicketMessagesStream(_ticket.id),
              builder: (context, snapshot) {
                if (snapshot.hasError) {
                  return Center(child: Text('Error loading messages: ${snapshot.error}'));
                }

                List<TicketMessageModel> messages = [];
                if (snapshot.hasData && snapshot.data!.snapshot.value != null) {
                  messages = FirebaseRealtimeService.parseMessages(
                    snapshot.data!.snapshot.value,
                    _ticket.id,
                  );
                }

                if (messages.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.chat_bubble_outline, size: 48, color: Colors.grey.shade300),
                        const SizedBox(height: 8),
                        Text(
                          'No messages yet. Start the conversation!',
                          style: TextStyle(color: Colors.grey.shade500, fontSize: 13),
                        ),
                      ],
                    ),
                  );
                }

                return ListView.builder(
                  controller: _scrollController,
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 16),
                  itemCount: messages.length,
                  itemBuilder: (context, index) {
                    final msg = messages[index];
                    final isMe = _user != null && msg.senderId == _user!.id;
                    return MessageBubble(message: msg, isMe: isMe);
                  },
                );
              },
            ),
          ),

          // Message Input Bar
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            decoration: BoxDecoration(
              color: Colors.white,
              border: Border(top: BorderSide(color: Colors.grey.shade200)),
            ),
            child: SafeArea(
              child: Column(
                children: [
                  if (_isAdminOrStaff)
                    Row(
                      children: [
                        Checkbox(
                          value: _isPrivateReply,
                          onChanged: (val) => setState(() => _isPrivateReply = val ?? false),
                          materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        ),
                        const Text('Internal Note (Visible only to staff)', style: TextStyle(fontSize: 11, color: Colors.grey)),
                      ],
                    ),
                  Row(
                    children: [
                      Expanded(
                        child: TextField(
                          controller: _messageController,
                          minLines: 1,
                          maxLines: 4,
                          decoration: InputDecoration(
                            hintText: _isPrivateReply ? 'Add internal note...' : 'Type a reply...',
                            hintStyle: const TextStyle(fontSize: 13),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                            filled: true,
                            fillColor: const Color(0xFFF8FAFC),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(20),
                              borderSide: BorderSide(color: Colors.grey.shade300),
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      IconButton(
                        onPressed: _isSending ? null : _sendMessage,
                        icon: _isSending
                            ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2))
                            : const Icon(Icons.send, color: Color(0xFF2563EB)),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMetaChip(IconData icon, String text, {Color? color}) {
    final c = color ?? const Color(0xFF64748B);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: c.withOpacity(0.08),
        borderRadius: BorderRadius.circular(6),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 13, color: c),
          const SizedBox(width: 4),
          Text(text, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: c)),
        ],
      ),
    );
  }
}
