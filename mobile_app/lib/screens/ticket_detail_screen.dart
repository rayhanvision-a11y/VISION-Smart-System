import 'package:firebase_database/firebase_database.dart';
import 'package:flutter/material.dart';
import 'package:file_picker/file_picker.dart';
import 'package:image_picker/image_picker.dart';
import '../models/ticket.dart';
import '../models/ticket_message.dart';
import '../models/user.dart';
import '../services/api_service.dart';
import '../services/firebase_realtime_service.dart';
import '../services/storage_service.dart';
import '../theme/app_theme.dart';
import '../widgets/common/status_pill.dart';
import '../widgets/message_bubble.dart';

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
  bool _showInfo = true;
  bool _searchMode = false;
  List<dynamic> _mentionCandidates = [];
  List<dynamic> _allStaff = [];
  int _mentionAnchor = -1;

  /// Built once in initState. Never call into Firebase from build(): if Firebase
  /// is not initialised the call throws, and a throw inside build() renders the
  /// whole screen blank in release builds.
  Stream<DatabaseEvent> _messagesStream = const Stream<DatabaseEvent>.empty();

  void _onMessageChanged(String value) {
    final selection = _messageController.selection;
    if (selection.baseOffset < 0) {
      setState(() {
        _mentionCandidates = [];
        _mentionAnchor = -1;
      });
      return;
    }
    // Find last @ before cursor without whitespace after it
    final upToCursor = value.substring(0, selection.baseOffset);
    final atIdx = upToCursor.lastIndexOf('@');
    if (atIdx < 0) {
      setState(() { _mentionCandidates = []; _mentionAnchor = -1; });
      return;
    }
    final between = upToCursor.substring(atIdx + 1);
    if (between.contains('\n') || between.contains(' ') && between.split(' ').last.length > 15) {
      setState(() { _mentionCandidates = []; _mentionAnchor = -1; });
      return;
    }
    final query = between.split(RegExp(r'\s')).last.toLowerCase();
    final matches = _allStaff.where((s) {
      final n = (s is Map ? s['name'] : null)?.toString().toLowerCase() ?? '';
      return n.isNotEmpty && (query.isEmpty || n.contains(query));
    }).take(6).toList();
    setState(() {
      _mentionCandidates = matches;
      _mentionAnchor = atIdx;
    });
  }

  void _insertMention(Map user) {
    if (_mentionAnchor < 0) return;
    final name = user['name']?.toString() ?? '';
    final text = _messageController.text;
    final cursor = _messageController.selection.baseOffset.clamp(0, text.length);
    final before = text.substring(0, _mentionAnchor);
    final after = text.substring(cursor);
    final newText = '$before@$name $after';
    _messageController.value = TextEditingValue(
      text: newText,
      selection: TextSelection.collapsed(offset: (before + '@$name ').length),
    );
    setState(() {
      _mentionCandidates = [];
      _mentionAnchor = -1;
    });
  }

  Future<void> _loadStaffForMentions() async {
    final staff = await ApiService.getStaff();
    if (mounted) setState(() => _allStaff = staff);
  }

  @override
  void initState() {
    super.initState();
    _ticket = widget.ticket;
    _user = widget.currentUser;
    _activityTabController = TabController(length: 3, vsync: this);
    try {
      _messagesStream = FirebaseRealtimeService.getTicketMessagesStream(_ticket.id);
    } catch (e) {
      debugPrint('Ticket #${_ticket.id} realtime stream unavailable: $e');
    }
    if (_user == null) {
      _loadCurrentUser();
    }
    _fetchTicketDetails();
    _loadStaffForMentions();
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

  Future<void> _pickAttachmentSource() async {
    await showModalBottomSheet(
      context: context,
      builder: (ctx) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              leading: const Icon(Icons.photo_library_rounded, color: AppColors.info),
              title: const Text('Photo from Gallery'),
              onTap: () { Navigator.pop(ctx); _attachImage(); },
            ),
            ListTile(
              leading: const Icon(Icons.insert_drive_file_rounded, color: AppColors.warning),
              title: const Text('PDF / Document / File'),
              onTap: () { Navigator.pop(ctx); _attachAnyFile(); },
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _attachAnyFile() async {
    final result = await FilePicker.platform.pickFiles(
      type: FileType.custom,
      allowedExtensions: ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'jpg', 'jpeg', 'png', 'webp'],
      withData: true,
    );
    if (result == null || result.files.isEmpty || !mounted) return;
    final f = result.files.first;
    if (f.bytes == null) return;
    await _uploadAndPost(bytes: f.bytes!, filename: f.name);
  }

  Future<void> _attachImage() async {
    final picker = ImagePicker();
    final picked = await picker.pickImage(source: ImageSource.gallery, imageQuality: 85, maxWidth: 1600);
    if (picked == null || !mounted) return;
    final bytes = await picked.readAsBytes();
    await _uploadAndPost(bytes: bytes, filename: picked.name);
  }

  Future<void> _uploadAndPost({required List<int> bytes, required String filename}) async {
    setState(() => _isSending = true);
    final upload = await ApiService.uploadTicketAttachment(
      ticketId: _ticket.id,
      bytes: bytes,
      filename: filename,
    );
    if (!mounted) {
      setState(() => _isSending = false);
      return;
    }

    if (upload['success'] == true) {
      final url = upload['data']?['attachment']?['url']?.toString();
      final fname = upload['data']?['attachment']?['file_name']?.toString() ?? filename;
      // Post a message linking to the uploaded image so it appears in chat
      final caption = _messageController.text.trim();
      final body = url != null
          ? (caption.isEmpty ? '📎 $fname\n$url' : '$caption\n\n📎 $fname\n$url')
          : (caption.isEmpty ? '📎 Attached: $fname' : caption);
      _messageController.clear();
      final res = await ApiService.addMessage(
        _ticket.id,
        body,
        isPrivate: _isPrivateReply,
      );
      if (!mounted) return;
      setState(() => _isSending = false);
      if (res['success'] == true) {
        await _fetchTicketDetails();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(res['error']?.toString() ?? 'Failed to post message'), backgroundColor: AppColors.danger),
        );
      }
    } else {
      setState(() => _isSending = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(upload['message']?.toString() ?? 'Upload failed'), backgroundColor: AppColors.danger),
      );
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
                    color: AppColors.statusColor(s),
                    shape: BoxShape.circle,
                  ),
                ),
                title: Text(
                  s.replaceAll('_', ' ').toUpperCase(),
                  style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
                ),
                trailing: _ticket.status == s ? Icon(Icons.check, color: AppColors.primary) : null,
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
                    color: AppColors.priorityColor(p),
                    shape: BoxShape.circle,
                  ),
                ),
                title: Text(
                  p.toUpperCase(),
                  style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
                ),
                trailing: _ticket.priority == p ? Icon(Icons.check, color: AppColors.primary) : null,
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
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
              decoration: BoxDecoration(
                gradient: AppColors.heroGradient,
                borderRadius: BorderRadius.circular(6),
              ),
              child: Text(_ticket.ticketKey.startsWith('#') ? _ticket.ticketKey : '#${_ticket.ticketKey}',
                  style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: Colors.white)),
            ),
            const SizedBox(width: 8),
            Flexible(
              child: Text(
                _ticket.title,
                style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600),
                maxLines: 1, overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: Icon(_searchMode ? Icons.close_rounded : Icons.search_rounded),
            tooltip: 'Search messages',
            onPressed: () => setState(() {
              _searchMode = !_searchMode;
              if (!_searchMode) _messageSearch = '';
            }),
          ),
          IconButton(
            icon: const Icon(Icons.info_outline_rounded),
            tooltip: 'Toggle Details',
            onPressed: () => setState(() => _showInfo = !_showInfo),
          ),
        ],
      ),
      body: Column(
        children: [
          // Search bar (toggleable)
          if (_searchMode)
            Container(
              color: AppColors.card,
              padding: const EdgeInsets.fromLTRB(12, 8, 12, 8),
              child: TextField(
                autofocus: true,
                decoration: InputDecoration(
                  hintText: 'Search messages…',
                  prefixIcon: const Icon(Icons.search_rounded, size: 20),
                  isDense: true,
                ),
                onChanged: (v) => setState(() => _messageSearch = v.trim()),
              ),
            ),
          // Hero header
          Container(
            width: double.infinity,
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 14),
            decoration: BoxDecoration(
              color: AppColors.card,
              border: Border(bottom: BorderSide(color: AppColors.border)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    InkWell(
                      onTap: _showStatusDialog,
                      borderRadius: BorderRadius.circular(AppRadius.sm),
                      child: StatusPill(status: _ticket.status),
                    ),
                    const SizedBox(width: 8),
                    InkWell(
                      onTap: _isAdminOrStaff ? _showPriorityDialog : null,
                      borderRadius: BorderRadius.circular(AppRadius.sm),
                      child: PriorityPill(priority: _ticket.priority),
                    ),
                    const Spacer(),
                    if (_ticket.category != null)
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: AppColors.bg,
                          borderRadius: BorderRadius.circular(AppRadius.sm),
                        ),
                        child: Text(
                          _ticket.category!.replaceAll('_', ' ').toUpperCase(),
                          style: AppText.label.copyWith(fontSize: 9),
                        ),
                      ),
                  ],
                ),
                const SizedBox(height: 12),
                Text(
                  _ticket.title,
                  style: AppText.h2.copyWith(fontSize: 17, height: 1.3),
                ),
                if (_showInfo) ...[
                  if (_ticket.plainDescription.isNotEmpty) ...[
                    const SizedBox(height: 10),
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: AppColors.bg,
                        borderRadius: BorderRadius.circular(AppRadius.md),
                        border: Border.all(color: AppColors.border),
                      ),
                      child: Text(
                        _ticket.plainDescription,
                        style: AppText.bodySm.copyWith(height: 1.5, color: AppColors.textPrimary),
                      ),
                    ),
                  ],
                  const SizedBox(height: 10),
                  // Compact 2x2 meta grid
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(child: _metaTile(Icons.person_outline_rounded, 'Reporter', _ticket.creatorName ?? 'Customer', AppColors.info)),
                      const SizedBox(width: 8),
                      Expanded(
                        child: GestureDetector(
                          onTap: _isAdminOrStaff ? _showAssignStaffDialog : null,
                          child: _metaTile(
                            Icons.assignment_ind_outlined,
                            'Assignee',
                            _ticket.assigneeName ?? 'Unassigned',
                            _ticket.assigneeName != null ? AppColors.primary : AppColors.warning,
                          ),
                        ),
                      ),
                    ],
                  ),
                  if (_ticket.area != null && _ticket.area!.isNotEmpty) ...[
                    const SizedBox(height: 8),
                    _metaTile(Icons.place_rounded, 'Area', _ticket.area!, AppColors.accent),
                  ],
                  const SizedBox(height: 10),
                  // Inline action tiles — Change Status / Priority / Assign
                  Row(
                    children: [
                      Expanded(
                        child: _actionTile(
                          Icons.swap_horiz_rounded,
                          'Change Status',
                          AppColors.info,
                          _showStatusDialog,
                        ),
                      ),
                      if (_isAdminOrStaff) ...[
                        const SizedBox(width: 8),
                        Expanded(
                          child: _actionTile(
                            Icons.flag_outlined,
                            'Change Priority',
                            AppColors.warning,
                            _showPriorityDialog,
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: _actionTile(
                            Icons.person_add_alt_1_outlined,
                            'Assign',
                            AppColors.primary,
                            _showAssignStaffDialog,
                          ),
                        ),
                      ],
                    ],
                  ),
                ],
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
              stream: _messagesStream,
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
                  if (_mentionCandidates.isNotEmpty)
                    Container(
                      margin: const EdgeInsets.only(bottom: 6),
                      constraints: const BoxConstraints(maxHeight: 180),
                      decoration: BoxDecoration(
                        color: AppColors.card,
                        borderRadius: BorderRadius.circular(AppRadius.md),
                        border: Border.all(color: AppColors.border),
                        boxShadow: AppShadows.card,
                      ),
                      child: ListView.builder(
                        shrinkWrap: true,
                        padding: EdgeInsets.zero,
                        itemCount: _mentionCandidates.length,
                        itemBuilder: (_, i) {
                          final u = _mentionCandidates[i] as Map;
                          return ListTile(
                            dense: true,
                            leading: CircleAvatar(
                              backgroundColor: AppColors.primary.withOpacity(0.1),
                              child: Text(
                                (u['name']?.toString() ?? '?')[0].toUpperCase(),
                                style: AppText.body.copyWith(color: AppColors.primary, fontWeight: FontWeight.w700),
                              ),
                            ),
                            title: Text(u['name']?.toString() ?? '—', style: AppText.body.copyWith(fontSize: 13)),
                            subtitle: Text(
                              (u['role']?.toString() ?? '').replaceAll('_', ' ').toUpperCase(),
                              style: AppText.label.copyWith(fontSize: 9),
                            ),
                            onTap: () => _insertMention(Map<String, dynamic>.from(u)),
                          );
                        },
                      ),
                    ),
                  Row(
                    children: [
                      IconButton(
                        onPressed: _isSending ? null : _pickAttachmentSource,
                        tooltip: 'Attach file',
                        icon: Icon(Icons.attach_file_rounded,
                            color: _isSending ? AppColors.textMuted : AppColors.primary),
                      ),
                      Expanded(
                        child: TextField(
                          controller: _messageController,
                          minLines: 1,
                          maxLines: 4,
                          onChanged: _onMessageChanged,
                          decoration: InputDecoration(
                            hintText: _isPrivateReply ? 'Add note (@ mention users)…' : 'Type reply (@ mention users)…',
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

  String _messageSearch = '';

  Future<void> _editMessagePrompt(TicketMessageModel msg) async {
    final controller = TextEditingController(text: msg.plainMessage);
    final result = await showDialog<String>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Edit message'),
        content: TextField(controller: controller, maxLines: 5),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Cancel')),
          ElevatedButton(onPressed: () => Navigator.pop(ctx, controller.text), child: const Text('Save')),
        ],
      ),
    );
    if (result == null || result.trim() == msg.plainMessage) return;
    final res = await ApiService.updateMessage(_ticket.id, msg.id, result.trim());
    if (res['success'] == true) {
      await _fetchTicketDetails();
    } else if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(res['message']?.toString() ?? 'Failed'), backgroundColor: AppColors.danger),
      );
    }
  }

  Future<void> _deleteMessagePrompt(TicketMessageModel msg) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Delete message?'),
        content: const Text('This cannot be undone.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancel')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.danger),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Delete'),
          ),
        ],
      ),
    );
    if (ok != true) return;
    final success = await ApiService.deleteMessage(_ticket.id, msg.id);
    if (success) {
      await _fetchTicketDetails();
    } else if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Failed to delete'), backgroundColor: AppColors.danger),
      );
    }
  }

  void _openMessageMenu(TicketMessageModel msg, bool isMine) {
    showModalBottomSheet(
      context: context,
      builder: (ctx) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              leading: const Icon(Icons.reply_rounded),
              title: const Text('Reply'),
              onTap: () { Navigator.pop(ctx); _setReplyTo(msg); },
            ),
            if (isMine || (_user != null && (_user!.role == 'admin' || _user!.role == 'super_admin'))) ...[
              ListTile(
                leading: const Icon(Icons.edit_outlined, color: AppColors.info),
                title: const Text('Edit message'),
                onTap: () { Navigator.pop(ctx); _editMessagePrompt(msg); },
              ),
              ListTile(
                leading: const Icon(Icons.delete_outline_rounded, color: AppColors.danger),
                title: const Text('Delete message'),
                onTap: () { Navigator.pop(ctx); _deleteMessagePrompt(msg); },
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildMessageList(List<TicketMessageModel> messages) {
    if (_messageSearch.isNotEmpty) {
      final q = _messageSearch.toLowerCase();
      messages = messages.where((m) =>
        m.plainMessage.toLowerCase().contains(q) ||
        m.senderName.toLowerCase().contains(q)
      ).toList();
    }
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
        return GestureDetector(
          onLongPress: () => _openMessageMenu(msg, isMe),
          child: MessageBubble(
            message: msg,
            isMe: isMe,
            onReply: _setReplyTo,
            onReact: _handleReaction,
          ),
        );
      },
    );
  }



  Widget _actionTile(IconData icon, String label, Color accent, VoidCallback onTap) {
    return Material(
      color: accent.withOpacity(0.08),
      borderRadius: BorderRadius.circular(AppRadius.md),
      child: InkWell(
        borderRadius: BorderRadius.circular(AppRadius.md),
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(AppRadius.md),
            border: Border.all(color: accent.withOpacity(0.25)),
          ),
          child: Column(
            children: [
              Icon(icon, size: 18, color: accent),
              const SizedBox(height: 4),
              Text(label,
                  textAlign: TextAlign.center,
                  style: AppText.label.copyWith(color: accent, fontSize: 10, fontWeight: FontWeight.w700),
                  maxLines: 2),
            ],
          ),
        ),
      ),
    );
  }

  Widget _metaTile(IconData icon, String label, String value, Color accent) {
    return Container(
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: accent.withOpacity(0.06),
        borderRadius: BorderRadius.circular(AppRadius.md),
        border: Border.all(color: accent.withOpacity(0.15)),
      ),
      child: Row(
        children: [
          Container(
            width: 30, height: 30,
            decoration: BoxDecoration(
              color: accent.withOpacity(0.15),
              borderRadius: BorderRadius.circular(AppRadius.sm),
            ),
            child: Icon(icon, size: 16, color: accent),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(label, style: AppText.label.copyWith(fontSize: 9, color: AppColors.textMuted)),
                const SizedBox(height: 1),
                Text(value,
                    style: AppText.bodySm.copyWith(color: accent, fontWeight: FontWeight.w700, fontSize: 12),
                    maxLines: 1, overflow: TextOverflow.ellipsis),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

