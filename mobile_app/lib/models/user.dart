class UserModel {
  final int id;
  final String name;
  final String email;
  final String role;
  final String? team;
  final String? avatar;
  final String? avatarUrl;
  final String? phone;
  final String? currentShift;

  UserModel({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    this.team,
    this.avatar,
    this.avatarUrl,
    this.phone,
    this.currentShift,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    // API may wrap user in 'user' key
    final j = (json['user'] is Map)
        ? Map<String, dynamic>.from(json['user'] as Map)
        : json;
    return UserModel(
      id: j['id'] is int ? j['id'] : int.tryParse(j['id']?.toString() ?? '') ?? 0,
      name: j['name']?.toString() ?? '',
      email: j['email']?.toString() ?? '',
      role: j['role']?.toString() ?? '',
      team: j['team']?.toString(),
      avatar: j['avatar']?.toString(),
      avatarUrl: j['avatar_url']?.toString() ?? j['avatarUrl']?.toString(),
      phone: j['phone']?.toString(),
      currentShift: j['current_shift']?.toString(),
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'email': email,
        'role': role,
        'team': team,
        'avatar': avatar,
        'avatar_url': avatarUrl,
        'phone': phone,
      };

  UserModel copyWith({String? avatar, String? avatarUrl, String? name, String? phone}) {
    return UserModel(
      id: id,
      name: name ?? this.name,
      email: email,
      role: role,
      team: team,
      avatar: avatar ?? this.avatar,
      avatarUrl: avatarUrl ?? this.avatarUrl,
      phone: phone ?? this.phone,
    );
  }
}
