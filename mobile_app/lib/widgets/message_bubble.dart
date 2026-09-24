import 'package:flutter/material.dart';
import '../theme/app_theme.dart';
import '../models/ticket_message.dart';

class MessageBubble extends StatelessWidget {
  final TicketMessageModel message;
  final bool isMe;
  final Function(TicketMessageModel)? onReply;
  final Function(TicketMessageModel, String)? onReact;

  const MessageBubble({
    Key? key,
    required this.message,
    required this.isMe,
    this.onReply,
    this.onReact,
  }) : super(key: key);

  String _stripHtml(String s) {
    var t = s.replaceAll(RegExp(r'<[^>]+>'), '');
    return t.replaceAll('&nbsp;', ' ').replaceAll('&amp;', '&').trim();
  }

  void _showEmojiPicker(BuildContext context) {
    final emojis = ['👍', '❤️', '😂', '😮', '😢', '🎉', '🔥', '💯', '✅', '👏'];
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'React to message',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Color(0xFF0F172A)),
            ),
            const SizedBox(height: 16),
            Wrap(
              spacing: 12,
              runSpacing: 12,
              children: emojis.map((e) {
                return InkWell(
                  onTap: () {
                    Navigator.pop(ctx);
                    if (onReact != null) onReact!(message, e);
                  },
                  borderRadius: BorderRadius.circular(12),
                  child: Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF1F5F9),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(e, style: const TextStyle(fontSize: 24)),
                  ),
                );
              }).toList(),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final initials = message.senderName.isNotEmpty ? message.senderName[0].toUpperCase() : 'U';

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: isMe ? MainAxisAlignment.end : MainAxisAlignment.start,
        children: [
          // Left Avatar (for other users)
          if (!isMe) ...[
            CircleAvatar(
              radius: 16,
              backgroundColor: message.isPrivate ? const Color(0xFFFEF3C7) : const Color(0xFFEFF6FF),
              child: Text(
                initials,
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.bold,
                  color: message.isPrivate ? const Color(0xFFD97706) : const Color(0xFF2563EB),
                ),
              ),
            ),
            const SizedBox(width: 8),
          ],

          // Bubble Container & Action Toolbar
          Flexible(
            child: Column(
              crossAxisAlignment: isMe ? CrossAxisAlignment.end : CrossAxisAlignment.start,
              children: [
                // Header (Sender Name, Role, Time, Private Tag)
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(
                      message.senderName,
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF475569),
                      ),
                    ),
                    if (message.isPrivate) ...[
                      const SizedBox(width: 6),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: const Color(0xFFFEF3C7),
                          borderRadius: BorderRadius.circular(4),
                          border: Border.all(color: const Color(0xFFFDE68A)),
                        ),
                        child: const Text(
                          '🔒 STAFF NOTE',
                          style: TextStyle(fontSize: 9, color: Color(0xFF92400E), fontWeight: FontWeight.bold),
                        ),
                      ),
                    ],
                  ],
                ),
                const SizedBox(height: 4),

                // Main Message Box (with Quoted Reply if any)
                Container(
                  constraints: BoxConstraints(
                    maxWidth: MediaQuery.of(context).size.width * 0.76,
                  ),
                  decoration: BoxDecoration(
                    color: isMe
                        ? AppColors.primary
                        : (message.isPrivate ? const Color(0xFFFFFBEB) : Colors.white),
                    borderRadius: BorderRadius.only(
                      topLeft: const Radius.circular(16),
                      topRight: const Radius.circular(16),
                      bottomLeft: Radius.circular(isMe ? 16 : 2),
                      bottomRight: Radius.circular(isMe ? 2 : 16),
                    ),
                    border: Border.all(
                      color: message.isPrivate
                          ? const Color(0xFFFDE68A)
                          : (isMe ? AppColors.primary : const Color(0xFFE2E8F0)),
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.03),
                        blurRadius: 6,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Quoted Reply Preview
                      if (message.replyToText != null && message.replyToText!.isNotEmpty)
                        Container(
                          width: double.infinity,
                          margin: const EdgeInsets.all(8),
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: isMe ? Colors.white.withOpacity(0.15) : const Color(0xFFF1F5F9),
                            borderRadius: BorderRadius.circular(8),
                            border: Border(
                              left: BorderSide(
                                color: isMe ? Colors.white : const Color(0xFF2563EB),
                                width: 3,
                              ),
                            ),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                message.replyToSenderName ?? 'Replied Message',
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                  color: isMe ? Colors.white : const Color(0xFF2563EB),
                                ),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                _stripHtml(message.replyToText!),
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                                style: TextStyle(
                                  fontSize: 11,
                                  color: isMe ? Colors.white70 : const Color(0xFF64748B),
                                ),
                              ),
                            ],
                          ),
                        ),

                      // Text Content
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                        child: Text(
                          message.plainMessage,
                          style: TextStyle(
                            color: isMe
                                ? Colors.white
                                : (message.isPrivate ? const Color(0xFF78350F) : const Color(0xFF0F172A)),
                            fontSize: 14,
                            height: 1.4,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),

                // Emoji Reactions Row (e.g. 👍 2, ❤️ 1)
                if (message.reactions.isNotEmpty)
                  Padding(
                    padding: const EdgeInsets.only(top: 4),
                    child: Wrap(
                      spacing: 4,
                      children: message.reactions.entries.map((entry) {
                        return InkWell(
                          onTap: () {
                            if (onReact != null) onReact!(message, entry.key);
                          },
                          borderRadius: BorderRadius.circular(12),
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                            decoration: BoxDecoration(
                              color: const Color(0xFFEFF6FF),
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(color: const Color(0xFFBFDBFE)),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Text(entry.key, style: const TextStyle(fontSize: 12)),
                                const SizedBox(width: 4),
                                Text(
                                  '${entry.value}',
                                  style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFF1E40AF)),
                                ),
                              ],
                            ),
                          ),
                        );
                      }).toList(),
                    ),
                  ),

                // Action Bar Toolbar (Reply ↩, React 👍, Picker 😊)
                Padding(
                  padding: const EdgeInsets.only(top: 2),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      InkWell(
                        onTap: () {
                          if (onReply != null) onReply!(message);
                        },
                        child: const Padding(
                          padding: EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                          child: Row(
                            children: [
                              Icon(Icons.reply_rounded, size: 14, color: Color(0xFF64748B)),
                              SizedBox(width: 2),
                              Text('Reply', style: TextStyle(fontSize: 11, color: Color(0xFF64748B))),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      InkWell(
                        onTap: () {
                          if (onReact != null) onReact!(message, '👍');
                        },
                        child: const Padding(
                          padding: EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                          child: Row(
                            children: [
                              Text('👍', style: TextStyle(fontSize: 12)),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(width: 4),
                      InkWell(
                        onTap: () => _showEmojiPicker(context),
                        child: const Padding(
                          padding: EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                          child: Icon(Icons.add_reaction_outlined, size: 14, color: Color(0xFF64748B)),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // Right Avatar (for current user)
          if (isMe) ...[
            const SizedBox(width: 8),
            CircleAvatar(
              radius: 16,
              backgroundColor: AppColors.primary,
              child: Text(
                initials,
                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.white),
              ),
            ),
          ],
        ],
      ),
    );
  }
}
