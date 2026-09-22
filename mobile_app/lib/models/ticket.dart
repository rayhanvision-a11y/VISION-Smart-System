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

  factory TicketModel.fromJson(Map<dynamic, dynamic> json) {
    int? parseId(dynamic val) {
      if (val == null) return null;
      if (val is int) return val;
      if (val is Map) return val['id'] is int ? val['id'] : int.tryParse(val['id']?.toString() ?? '');
      return int.tryParse(val.toString());
    }

    String? parseName(dynamic val) {
      if (val == null) return null;
      if (val is String) return val;
      if (val is Map) return val['name']?.toString();
      return null;
    }

    return TicketModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? '') ?? 0,
      ticketKey: json['ticket_key']?.toString() ?? json['key']?.toString() ?? '',
      title: json['title']?.toString() ?? '',
      description: json['description']?.toString() ?? '',
      priority: json['priority']?.toString() ?? 'medium',
      status: json['status']?.toString() ?? 'in_progress',
      category: json['category'] is String
          ? json['category']
          : (json['category'] is Map ? json['category']['name']?.toString() : null),
      createdBy: parseId(json['created_by']),
      assignedTo: parseId(json['assigned_to']),
      assigneeName: parseName(json['assigned_to']) ?? parseName(json['assignee']),
      creatorName: parseName(json['user']) ?? parseName(json['creator']),
      createdAt: json['created_at']?.toString(),
      updatedAt: json['updated_at']?.toString(),
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
