class TicketMessageModel {
  final int id;
  final int ticketId;
  final int senderId;
  final String senderName;
  final String? senderAvatar;
  final String message;
  final bool isPrivate;
  final String? createdAt;

  final String? replyToSenderName;
  final String? replyToText;
  final Map<String, int> reactions;

  TicketMessageModel({
    required this.id,
    required this.ticketId,
    required this.senderId,
    required this.senderName,
    this.senderAvatar,
    required this.message,
    required this.isPrivate,
    this.createdAt,
    this.replyToSenderName,
    this.replyToText,
    this.reactions = const {},
  });

  factory TicketMessageModel.fromJson(Map<String, dynamic> json) {
    Map<String, int> rxMap = {};
    if (json['reactions'] is Map) {
      json['reactions'].forEach((k, v) {
        if (v is Map && v['count'] != null) {
          rxMap[k.toString()] = int.tryParse(v['count'].toString()) ?? 1;
        } else if (v is int) {
          rxMap[k.toString()] = v;
        }
      });
    }

    String? rSender;
    String? rText;
    if (json['reply_to'] is Map) {
      rSender = json['reply_to']?['sender']?['name']?.toString();
      rText = json['reply_to']?['message']?.toString();
    } else if (json['replyTo'] is Map) {
      rSender = json['replyTo']?['senderName']?.toString();
      rText = json['replyTo']?['preview']?.toString();
    }

    String sName = 'User';
    String? sAvatar;
    if (json['sender'] is Map) {
      sName = json['sender']['name']?.toString() ?? 'User';
      sAvatar = json['sender']['avatar']?.toString();
    } else if (json['sender_name'] != null) {
      sName = json['sender_name'].toString();
    } else if (json['sender'] is String) {
      sName = json['sender'].toString();
    }

    return TicketMessageModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? '') ?? 0,
      ticketId: json['ticket_id'] is int ? json['ticket_id'] : int.tryParse(json['ticket_id']?.toString() ?? '') ?? 0,
      senderId: json['sender_id'] is int ? json['sender_id'] : int.tryParse(json['sender_id']?.toString() ?? '') ?? 0,
      senderName: sName,
      senderAvatar: sAvatar,
      message: json['message']?.toString() ?? json['note']?.toString() ?? '',
      isPrivate: json['is_private'] == true || json['is_private'] == 1 || json['is_private'] == '1' || json['is_private'] == 'true',
      createdAt: json['created_at']?.toString(),
      replyToSenderName: rSender,
      replyToText: rText,
      reactions: rxMap,
    );
  }

  factory TicketMessageModel.fromFirebase(String key, Map<dynamic, dynamic> data) {
    Map<String, int> rxMap = {};
    if (data['reactions'] is Map) {
      data['reactions'].forEach((k, v) {
        if (v is Map && v['count'] != null) {
          rxMap[k.toString()] = int.tryParse(v['count'].toString()) ?? 1;
        } else if (v is int) {
          rxMap[k.toString()] = v;
        }
      });
    }

    return TicketMessageModel(
      id: data['id'] is int ? data['id'] : int.tryParse(data['id']?.toString() ?? key) ?? 0,
      ticketId: data['ticket_id'] is int ? data['ticket_id'] : int.tryParse(data['ticket_id']?.toString() ?? '0') ?? 0,
      senderId: data['sender_id'] is int ? data['sender_id'] : int.tryParse(data['sender_id']?.toString() ?? '0') ?? 0,
      senderName: data['sender_name']?.toString() ?? 'User',
      message: data['message']?.toString() ?? '',
      isPrivate: data['is_private'] == true || data['is_private'] == 1 || data['is_private'] == '1' || data['is_private'] == 'true',
      createdAt: data['created_at']?.toString(),
      replyToSenderName: data['reply_to_sender']?.toString(),
      replyToText: data['reply_to_text']?.toString(),
      reactions: rxMap,
    );
  }
}
