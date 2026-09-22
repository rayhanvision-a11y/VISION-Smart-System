class TicketModel {
  final int id;
  final String ticketKey;
  final String title;
  final String description;
  final String priority;
  final String status;
  final String? category;
  final int? createdBy;
  final int? assignedTo;
  final String? assigneeName;
  final String? creatorName;
  final String? createdAt;
  final String? updatedAt;

  TicketModel({
    required this.id,
    required this.ticketKey,
    required this.title,
    required this.description,
    required this.priority,
    required this.status,
    this.category,
    this.createdBy,
    this.assignedTo,
    this.assigneeName,
    this.creatorName,
    this.createdAt,
    this.updatedAt,
  });

  factory TicketModel.fromJson(Map<String, dynamic> json) {
    return TicketModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      ticketKey: json['ticket_key'] ?? json['key'] ?? '',
      title: json['title'] ?? '',
      description: json['description'] ?? '',
      priority: json['priority'] ?? 'medium',
      status: json['status'] ?? 'open',
      category: json['category'] is String ? json['category'] : json['category']?['name'],
      createdBy: json['created_by'],
      assignedTo: json['assigned_to'],
      assigneeName: json['assigned_to'] != null && json['assigned_to'] is Map
          ? json['assigned_to']['name']
          : json['assignee']?['name'],
      creatorName: json['user']?['name'] ?? json['creator']?['name'],
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
    );
  }

  factory TicketModel.fromFirebase(String key, Map<dynamic, dynamic> data) {
    return TicketModel(
      id: data['id'] is int ? data['id'] : int.tryParse(data['id']?.toString() ?? key) ?? 0,
      ticketKey: data['ticket_key']?.toString() ?? key,
      title: data['title']?.toString() ?? '',
      description: data['description']?.toString() ?? '',
      priority: data['priority']?.toString() ?? 'medium',
      status: data['status']?.toString() ?? 'open',
      category: data['category']?.toString(),
      createdBy: data['created_by'] is int ? data['created_by'] : null,
      assignedTo: data['assigned_to'] is int ? data['assigned_to'] : null,
      updatedAt: data['updated_at']?.toString(),
    );
  }
}
