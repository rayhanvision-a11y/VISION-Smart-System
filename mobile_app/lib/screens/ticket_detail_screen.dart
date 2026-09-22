import 'package:firebase_database/firebase_database.dart';
import 'package:flutter/material.dart';
import '../config/app_config.dart';
import '../models/ticket.dart';
import '../models/ticket_message.dart';
import '../models/user.dart';
import '../services/api_service.dart';
import '../services/firebase_realtime_service.dart';
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
  final _messageController = TextEditingController();
  bool _isSending = false;
  bool _isPrivateReply = false;
  final ScrollController _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    _ticket = widget.ticket;
  }

  Future<void> _sendMessage() async {
    final text = _messageController.text.trim();
    if (text.isEmpty) return;

    setState(() => _isSending = true);
    _messageController.clear();

    final newMsg = await ApiService.addMessage(
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
    final statuses = ['open', 'in_progress', 'on_hold', 'resolved', 'closed'];

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
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0.5,
        title: Text(
          '#${_ticket.ticketKey}',
          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
        ),
        actions: [
          TextButton.icon(
            onPressed: _showStatusDialog,
            icon: const Icon(Icons.edit_outlined, size: 16),
            label: const Text('Status'),
            style: TextButton.styleFrom(foregroundColor: AppConfig.primaryColor),
          ),
        ],
      ),
      body: Column(
        children: [
          // Ticket Summary Card
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    StatusBadge(status: _ticket.status),
                    PriorityBadge(priority: _ticket.priority),
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
                    style: TextStyle(fontSize: 13, color: Colors.grey.shade700, height: 1.4),
                  ),
                ],
              ],
            ),
          ),
          const Divider(height: 1),

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
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  itemCount: messages.length,
                  itemBuilder: (context, index) {
                    final msg = messages[index];
                    final isMe = widget.currentUser != null && msg.senderId == widget.currentUser!.id;
                    return MessageBubble(message: msg, isMe: isMe);
                  },
                );
              },
            ),
          ),

          // Message Input Field
          Container(
            color: Colors.white,
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            child: SafeArea(
              child: Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: _messageController,
                      decoration: InputDecoration(
                        hintText: 'Type a reply...',
                        hintStyle: TextStyle(fontSize: 14, color: Colors.grey.shade400),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(24),
                          borderSide: BorderSide(color: Colors.grey.shade300),
                        ),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                      ),
                      textCapitalization: TextCapitalization.sentences,
                      maxLines: null,
                    ),
                  ),
                  const SizedBox(width: 8),
                  IconButton(
                    icon: _isSending
                        ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(strokeWidth: 2),
                          )
                        : const Icon(Icons.send_rounded, color: AppConfig.primaryColor),
                    onPressed: _isSending ? null : _sendMessage,
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
