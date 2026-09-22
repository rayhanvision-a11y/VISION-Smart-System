class TicketMessageModel {
  final int id;
  final int ticketId;
  final int senderId;
  final String senderName;
  final String? senderAvatar;
  final String message;
  final bool isPrivate;
  final String? createdAt;

  TicketMessageModel({
    required this.id,
    required this.ticketId,
    required this.senderId,
    required this.senderName,
    this.senderAvatar,
    required this.message,
    required this.isPrivate,
    this.createdAt,
  });

  factory TicketMessageModel.fromJson(Map<String, dynamic> json) {
    return TicketMessageModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      ticketId: json['ticket_id'] is int ? json['ticket_id'] : int.tryParse(json['ticket_id'].toString()) ?? 0,
      senderId: json['sender_id'] is int ? json['sender_id'] : int.tryParse(json['sender_id'].toString()) ?? 0,
      senderName: json['sender']?['name'] ?? 'User',
      senderAvatar: json['sender']?['avatar'],
      message: json['message'] ?? '',
      isPrivate: json['is_private'] == true || json['is_private'] == 1,
      createdAt: json['created_at'],
    );
  }

  factory TicketMessageModel.fromFirebase(String key, Map<dynamic, dynamic> data) {
    return TicketMessageModel(
      id: data['id'] is int ? data['id'] : int.tryParse(data['id']?.toString() ?? key) ?? 0,
      ticketId: data['ticket_id'] is int ? data['ticket_id'] : int.tryParse(data['ticket_id']?.toString() ?? '0') ?? 0,
      senderId: data['sender_id'] is int ? data['sender_id'] : int.tryParse(data['sender_id']?.toString() ?? '0') ?? 0,
      senderName: data['sender_name']?.toString() ?? 'User',
      message: data['message']?.toString() ?? '',
      isPrivate: data['is_private'] == true,
      createdAt: data['created_at']?.toString(),
    );
  }
}
