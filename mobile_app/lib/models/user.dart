class UserModel {
  final int id;
  final String name;
  final String email;
  final String role;
  final String? team;
  final String? avatar;
  final String? phone;

  UserModel({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    this.team,
    this.avatar,
    this.phone,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      name: json['name'] ?? '',
      email: json['email'] ?? '',
      role: json['role'] ?? '',
      team: json['team'],
      avatar: json['avatar'],
      phone: json['phone'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'role': role,
      'team': team,
      'avatar': avatar,
      'phone': phone,
    };
  }
}
