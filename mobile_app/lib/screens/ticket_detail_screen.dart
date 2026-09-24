import 'package:firebase_database/firebase_database.dart';
import 'package:flutter/material.dart';
import '../config/app_config.dart';
import '../models/ticket.dart';
import '../models/ticket_message.dart';
import '../models/user.dart';
import '../services/api_service.dart';
import '../services/firebase_realtime_service.dart';
import '../services/storage_service.dart';
import '../theme/app_theme.dart';
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

class _TicketDetailScreenState extends State<TicketDetailScreen> with SingleTickerProviderStateMixin {
  late TicketModel _ticket;
  UserModel? _user;
  final _messageController = TextEditingController();
  bool _isSending = false;
  bool _isPrivateReply = false;
  late TabController _activityTabController;
  List<TicketMessageModel> _apiMessages = [];
  bool _isLoadingApi = true;

  @override
  void initState() {
    super.initState();
    _ticket = widget.ticket;
    _user = widget.currentUser;
    _activityTabController = TabController(length: 3, vsync: this);
    if (_user == null) {
      _loadCurrentUser();
    }
    _fetchTicketDetails();
  }

  Future<void> _fetchTicketDetails() async {
    final details = await ApiService.getTicketDetails(_ticket.id);
    if (!mounted) return;
    if (details != null) {
      final fetchedTicket = details['ticket'];
      final msgs = details['messages'];
      setState(() {
        if (fetchedTicket is TicketModel) {
          _ticket = fetchedTicket;
        }
        if (msgs is List<TicketMessageModel>) {
          _apiMessages = msgs;
        } else if (msgs is List) {
          _apiMessages = msgs.whereType<TicketMessageModel>().toList();
        }
        _isLoadingApi = false;
      });
      debugPrint('Ticket #${_ticket.id} loaded ${_apiMessages.length} messages');
    } else {
      setState(() => _isLoadingApi = false);
      debugPrint('Ticket #${_ticket.id} details fetch returned null');
    }
  }

  @override
  void dispose() {
    _activityTabController.dispose();
    _messageController.dispose();
    super.dispose();
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
    return r != 'reseller' && r != 'technician';
  }

  bool get _isTechnician => _user?.role.toLowerCase() == 'technician';

  TicketMessageModel? _replyToMessage;

  void _setReplyTo(TicketMessageModel msg) {
    setState(() => _replyToMessage = msg);
  }

  void _cancelReplyTo() {
    setState(() => _replyToMessage = null);
  }

  Future<void> _handleReaction(TicketMessageModel msg, String emoji) async {
    final ok = await ApiService.toggleReaction(_ticket.id, msg.id, emoji);
    if (ok) {
      await _fetchTicketDetails();
    }
  }

  Future<void> _sendMessage() async {
    final text = _messageController.text.trim();
    if (text.isEmpty) return;

    final replyId = _replyToMessage?.id;
    setState(() {
      _isSending = true;
      _replyToMessage = null;
    });
    _messageController.clear();

    final result = await ApiService.addMessage(
      _ticket.id,
      text,
      isPrivate: _isPrivateReply,
      replyToId: replyId,
    );

    if (!mounted) return;
    setState(() => _isSending = false);

    if (result['success'] == true) {
      await _fetchTicketDetails();
    } else {
      // Restore user's text so they don't lose it, and surface the error
      _messageController.text = text;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          backgroundColor: Colors.red.shade700,
          content: Row(
            children: [
              const Icon(Icons.error_outline, color: Colors.white, size: 18),
              const SizedBox(width: 8),
              Expanded(child: Text(result['error']?.toString() ?? 'Failed to send message')),
            ],
          ),
          action: SnackBarAction(
            label: 'Retry',
            textColor: Colors.white,
            onPressed: _sendMessage,
          ),
        ),
      );
    }
  }

  Future<void> _showStatusDialog() async {
    final statuses = _isTechnician
        ? ['pending', 'resolved']
        : ['in_progress', 'pending', 'waiting_for_customer_feedback', 'resolved'];

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
      backgroundColor: AppColors.bg,
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
          // Expanded Ticket Info Card (Website Matching Layout)
          Container(
            padding: const EdgeInsets.all(16),
            decoration: const BoxDecoration(
              color: Colors.white,
              border: Border(bottom: BorderSide(color: Color(0xFFE2E8F0))),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Title and Badges
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
                  style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                ),
                if (_ticket.plainDescription.isNotEmpty) ...[
                  const SizedBox(height: 8),
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF8FAFC),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
                    ),
                    child: Text(
                      _ticket.plainDescription,
                      style: const TextStyle(fontSize: 13, color: Color(0xFF334155), height: 1.4),
                    ),
                  ),
                ],
                const SizedBox(height: 12),

                // Website-Matching Details Grid (Reporter, Assignee, Category)
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF1F5F9),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Column(
                    children: [
                      Row(
                        children: [
                          const Icon(Icons.person_outline, size: 16, color: Color(0xFF64748B)),
                          const SizedBox(width: 6),
                          const Text('Reporter: ', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF475569))),
                          Expanded(
                            child: Text(
                              _ticket.creatorName ?? 'Customer',
                              style: const TextStyle(fontSize: 12, color: Color(0xFF0F172A)),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 6),
                      Row(
                        children: [
                          const Icon(Icons.assignment_ind_outlined, size: 16, color: Color(0xFF64748B)),
                          const SizedBox(width: 6),
                          const Text('Assigned Staff: ', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF475569))),
                          Expanded(
                            child: InkWell(
                              onTap: _isAdminOrStaff ? _showAssignStaffDialog : null,
                              child: Text(
                                _ticket.assigneeName ?? 'Unassigned (Tap to assign)',
                                style: TextStyle(
                                  fontSize: 12,
                                  fontWeight: FontWeight.w600,
                                  color: _ticket.assigneeName != null ? const Color(0xFF2563EB) : const Color(0xFFD97706),
                                ),
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ),
                        ],
                      ),
                      if (_ticket.category != null) ...[
                        const SizedBox(height: 6),
                        Row(
                          children: [
                            const Icon(Icons.folder_outlined, size: 16, color: Color(0xFF64748B)),
                            const SizedBox(width: 6),
                            const Text('Category: ', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF475569))),
                            Text(
                              _ticket.category!.replaceAll('_', ' ').toUpperCase(),
                              style: const TextStyle(fontSize: 12, color: Color(0xFF0F172A)),
                            ),
                          ],
                        ),
                      ],
                      if (_ticket.area != null && _ticket.area!.isNotEmpty) ...[
                        const SizedBox(height: 6),
                        Row(
                          children: [
                            Icon(Icons.place_rounded, size: 16, color: AppColors.primary),
                            const SizedBox(width: 6),
                            const Text('Area: ', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF475569))),
                            Expanded(
                              child: Text(
                                _ticket.area!,
                                style: TextStyle(fontSize: 12, color: AppColors.primary, fontWeight: FontWeight.w600),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ],
                  ),
                ),
              ],
            ),
          ),

          // Activity Tabs (All, Comments, Internal Notes)
          Container(
            color: Colors.white,
            child: TabBar(
              controller: _activityTabController,
              labelColor: const Color(0xFF2563EB),
              unselectedLabelColor: const Color(0xFF64748B),
              indicatorColor: const Color(0xFF2563EB),
              labelStyle: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
              tabs: const [
                Tab(text: 'All Activity'),
                Tab(text: 'Public Comments'),
                Tab(text: 'Internal Notes 🔒'),
              ],
            ),
          ),

          // Real-time Chat Messages Stream (API + Firebase Fallback)
          Expanded(
            child: StreamBuilder<DatabaseEvent>(
              stream: FirebaseRealtimeService.getTicketMessagesStream(_ticket.id),
              builder: (context, snapshot) {
                if (snapshot.hasError) {
                  debugPrint('Firebase stream error: ${snapshot.error}');
                }

                List<TicketMessageModel> allMessages = List.from(_apiMessages);
                if (snapshot.hasData && snapshot.data!.snapshot.value != null) {
                  final fbMessages = FirebaseRealtimeService.parseMessages(
                    snapshot.data!.snapshot.value,
                    _ticket.id,
                  );
                  if (fbMessages.isNotEmpty) {
                    final existingIds = allMessages.map((m) => m.id).toSet();
                    for (var fm in fbMessages) {
                      if (!existingIds.contains(fm.id)) {
                        allMessages.add(fm);
                      }
                    }
                  }
                }

                if (_isLoadingApi && allMessages.isEmpty) {
                  return const Center(child: CircularProgressIndicator());
                }

                return TabBarView(
                  controller: _activityTabController,
                  children: [
                    // Tab 1: All Activity
                    _buildMessageList(allMessages),
                    // Tab 2: Public Comments Only
                    _buildMessageList(allMessages.where((m) => !m.isPrivate).toList()),
                    // Tab 3: Internal Notes Only
                    _buildMessageList(allMessages.where((m) => m.isPrivate).toList()),
                  ],
                );
              },
            ),
          ),

          // Message Input Bar (Matching Web Composer with Quoted Reply & Reactions)
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            decoration: BoxDecoration(
              color: Colors.white,
              border: Border(top: BorderSide(color: Colors.grey.shade200)),
            ),
            child: SafeArea(
              child: Column(
                children: [
                  // Quoted Reply Preview Bar
                  if (_replyToMessage != null)
                    Container(
                      margin: const EdgeInsets.only(bottom: 6),
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                      decoration: BoxDecoration(
                        color: const Color(0xFFEFF6FF),
                        borderRadius: BorderRadius.circular(8),
                        border: const Border(left: BorderSide(color: Color(0xFF2563EB), width: 3)),
                      ),
                      child: Row(
                        children: [
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Replying to ${_replyToMessage!.senderName}',
                                  style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF2563EB)),
                                ),
                                Text(
                                  _replyToMessage!.message,
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: const TextStyle(fontSize: 11, color: Color(0xFF64748B)),
                                ),
                              ],
                            ),
                          ),
                          IconButton(
                            icon: const Icon(Icons.close, size: 16, color: Color(0xFF64748B)),
                            onPressed: _cancelReplyTo,
                            padding: EdgeInsets.zero,
                            constraints: const BoxConstraints(),
                          ),
                        ],
                      ),
                    ),
                  if (_isAdminOrStaff)
                    Padding(
                      padding: const EdgeInsets.only(bottom: 6),
                      child: Row(
                        children: [
                          ChoiceChip(
                            label: const Text('🌐 Public Reply', style: TextStyle(fontSize: 11)),
                            selected: !_isPrivateReply,
                            selectedColor: const Color(0xFF2563EB),
                            labelStyle: TextStyle(color: !_isPrivateReply ? Colors.white : Colors.black87),
                            onSelected: (_) => setState(() => _isPrivateReply = false),
                          ),
                          const SizedBox(width: 8),
                          ChoiceChip(
                            label: const Text('🔒 Internal Note', style: TextStyle(fontSize: 11)),
                            selected: _isPrivateReply,
                            selectedColor: const Color(0xFFD97706),
                            labelStyle: TextStyle(color: _isPrivateReply ? Colors.white : Colors.black87),
                            onSelected: (_) => setState(() => _isPrivateReply = true),
                          ),
                        ],
                      ),
                    ),
                  Row(
                    children: [
                      Expanded(
                        child: TextField(
                          controller: _messageController,
                          minLines: 1,
                          maxLines: 4,
                          decoration: InputDecoration(
                            hintText: _isPrivateReply ? 'Add staff internal note...' : 'Type a public reply...',
                            hintStyle: const TextStyle(fontSize: 13),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                            filled: true,
                            fillColor: _isPrivateReply ? const Color(0xFFFEF3C7) : const Color(0xFFF8FAFC),
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
                            : Icon(Icons.send, color: _isPrivateReply ? const Color(0xFFD97706) : const Color(0xFF2563EB)),
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

  Widget _buildMessageList(List<TicketMessageModel> messages) {
    if (messages.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.chat_bubble_outline, size: 48, color: Colors.grey.shade300),
            const SizedBox(height: 8),
            Text(
              'No messages found in this view',
              style: TextStyle(color: Colors.grey.shade500, fontSize: 13),
            ),
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 16),
      itemCount: messages.length,
      itemBuilder: (context, index) {
        final msg = messages[index];
        final isMe = _user != null && msg.senderId == _user!.id;
        return MessageBubble(
          message: msg,
          isMe: isMe,
          onReply: _setReplyTo,
          onReact: _handleReaction,
        );
      },
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
