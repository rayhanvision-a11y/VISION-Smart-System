import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/app_config.dart';
import '../models/ticket.dart';
import '../models/ticket_message.dart';
import '../models/user.dart';
import 'storage_service.dart';

class ApiService {
  static Future<String> getBaseUrl() async {
    final saved = await StorageService.getServerUrl();
    if (saved != null && saved.trim().isNotEmpty) {
      return saved.trim().replaceAll(RegExp(r'/+$'), '');
    }
    return AppConfig.apiBaseUrl;
  }

  static Future<Map<String, String>> _headers() async {
    final token = await StorageService.getToken();
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  // 1. User Login
  static Future<Map<String, dynamic>> login(String email, String password) async {
    try {
      final baseUrl = await getBaseUrl();
      final response = await http.post(
        Uri.parse('$baseUrl/login'),
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: jsonEncode({
          'email': email,
          'password': password,
          'device_name': 'flutter_mobile_app',
        }),
      );

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['token'] != null) {
        final token = data['token'];
        final user = UserModel.fromJson(data['user']);
        await StorageService.saveAuth(token, user);
        return {'success': true, 'user': user, 'token': token};
      }
      return {'success': false, 'message': data['message'] ?? 'Login failed'};
    } catch (e) {
      return {'success': false, 'message': 'Connection error: $e'};
    }
  }

  // 2. Fetch Tickets list from Laravel API
  static Future<List<TicketModel>> fetchTickets({String? status, String? search}) async {
    try {
      final baseUrl = await getBaseUrl();
      var uri = Uri.parse('$baseUrl/tickets').replace(queryParameters: {
        if (status != null && status.isNotEmpty) 'status': status,
        if (search != null && search.isNotEmpty) 'search': search,
      });

      final response = await http.get(uri, headers: await _headers());
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final list = data['data'] as List? ?? [];
        return list.map((json) => TicketModel.fromJson(json)).toList();
      }
    } catch (_) {}
    return [];
  }

  // 3. Create Ticket
  static Future<Map<String, dynamic>> createTicket({
    required String title,
    required String description,
    required String priority,
    String? category,
  }) async {
    try {
      final baseUrl = await getBaseUrl();
      final response = await http.post(
        Uri.parse('$baseUrl/tickets'),
        headers: await _headers(),
        body: jsonEncode({
          'title': title,
          'description': description,
          'priority': priority,
          'category': category ?? 'other',
        }),
      );

      final data = jsonDecode(response.body);
      if (response.statusCode == 201) {
        return {'success': true, 'ticket': TicketModel.fromJson(data['ticket'])};
      }
      return {'success': false, 'message': data['message'] ?? 'Failed to create ticket'};
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  // 4. Update Ticket Status
  static Future<bool> updateTicketStatus(int ticketId, String status) async {
    try {
      final baseUrl = await getBaseUrl();
      final response = await http.post(
        Uri.parse('$baseUrl/tickets/$ticketId/status'),
        headers: await _headers(),
        body: jsonEncode({'status': status}),
      );
      return response.statusCode == 200;
    } catch (_) {
      return false;
    }
  }

  // 5. Add Message / Reply
  static Future<TicketMessageModel?> addMessage(int ticketId, String message, {bool isPrivate = false}) async {
    try {
      final baseUrl = await getBaseUrl();
      final response = await http.post(
        Uri.parse('$baseUrl/tickets/$ticketId/messages'),
        headers: await _headers(),
        body: jsonEncode({
          'message': message,
          'is_private': isPrivate,
        }),
      );

      if (response.statusCode == 201) {
        final data = jsonDecode(response.body);
        return TicketMessageModel.fromJson(data['data']);
      }
    } catch (_) {}
    return null;
  }

  // 6. Update Device FCM Token
  static Future<void> updateFcmToken(String fcmToken) async {
    try {
      final baseUrl = await getBaseUrl();
      await http.post(
        Uri.parse('$baseUrl/user/fcm-token'),
        headers: await _headers(),
        body: jsonEncode({'fcm_token': fcmToken}),
      );
    } catch (_) {}
  }

  // 7. Dashboard Stats & Duty Teams
  static Future<Map<String, dynamic>?> getDashboard() async {
    try {
      final baseUrl = await getBaseUrl();
      final response = await http.get(
        Uri.parse('$baseUrl/dashboard'),
        headers: await _headers(),
      );
      if (response.statusCode == 200) {
        return jsonDecode(response.body) as Map<String, dynamic>;
      }
    } catch (_) {}
    return null;
  }

  // 8. Duty Roster List
  static Future<Map<String, dynamic>?> getRoster() async {
    try {
      final baseUrl = await getBaseUrl();
      final response = await http.get(
        Uri.parse('$baseUrl/roster'),
        headers: await _headers(),
      );
      if (response.statusCode == 200) {
        return jsonDecode(response.body) as Map<String, dynamic>;
      }
    } catch (_) {}
    return null;
  }

  // 9. Users Directory (Admin / Super Admin)
  static Future<List<dynamic>> getUsers({String? search, String? role, String? team}) async {
    try {
      final baseUrl = await getBaseUrl();
      var uri = Uri.parse('$baseUrl/users').replace(queryParameters: {
        if (search != null && search.isNotEmpty) 'search': search,
        if (role != null && role.isNotEmpty) 'role': role,
        if (team != null && team.isNotEmpty) 'team': team,
      });
      final response = await http.get(uri, headers: await _headers());
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return data['users'] as List? ?? [];
      }
    } catch (_) {}
    return [];
  }

  // 10. Metadata: Ticket Categories
  static Future<List<dynamic>> getCategories() async {
    try {
      final baseUrl = await getBaseUrl();
      final response = await http.get(Uri.parse('$baseUrl/categories'), headers: await _headers());
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return data['categories'] as List? ?? [];
      }
    } catch (_) {}
    return [];
  }

  // 11. Metadata: POP Offices
  static Future<List<dynamic>> getPopOffices() async {
    try {
      final baseUrl = await getBaseUrl();
      final response = await http.get(Uri.parse('$baseUrl/pop-offices'), headers: await _headers());
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return data['pop_offices'] as List? ?? [];
      }
    } catch (_) {}
    return [];
  }

  // 12. Metadata: Staff Members for Assignment
  static Future<List<dynamic>> getStaff() async {
    try {
      final baseUrl = await getBaseUrl();
      final response = await http.get(Uri.parse('$baseUrl/staff'), headers: await _headers());
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return data['staff'] as List? ?? [];
      }
    } catch (_) {}
    return [];
  }

  // 13. Assign Ticket
  static Future<bool> assignTicket(int ticketId, int assignedToId) async {
    try {
      final baseUrl = await getBaseUrl();
      final response = await http.post(
        Uri.parse('$baseUrl/tickets/$ticketId/assign'),
        headers: await _headers(),
        body: jsonEncode({'assigned_to': assignedToId}),
      );
      return response.statusCode == 200;
    } catch (_) {
      return false;
    }
  }

  // 14. Update Ticket Priority
  static Future<bool> updateTicketPriority(int ticketId, String priority) async {
    try {
      final baseUrl = await getBaseUrl();
      final response = await http.post(
        Uri.parse('$baseUrl/tickets/$ticketId/priority'),
        headers: await _headers(),
        body: jsonEncode({'priority': priority}),
      );
      return response.statusCode == 200;
    } catch (_) {
      return false;
    }
  }

  // 15. Logout
  static Future<void> logout() async {
    try {
      final baseUrl = await getBaseUrl();
      await http.post(
        Uri.parse('$baseUrl/logout'),
        headers: await _headers(),
      );
    } catch (_) {}
    await StorageService.clearAuth();
  }
}
