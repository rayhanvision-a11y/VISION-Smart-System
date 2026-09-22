import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/app_config.dart';
import '../models/ticket.dart';
import '../models/ticket_message.dart';
import '../models/user.dart';
import 'storage_service.dart';

class ApiService {
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
      final response = await http.post(
        Uri.parse('${AppConfig.apiBaseUrl}/login'),
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
      var uri = Uri.parse('${AppConfig.apiBaseUrl}/tickets').replace(queryParameters: {
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
      final response = await http.post(
        Uri.parse('${AppConfig.apiBaseUrl}/tickets'),
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
      final response = await http.post(
        Uri.parse('${AppConfig.apiBaseUrl}/tickets/$ticketId/status'),
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
      final response = await http.post(
        Uri.parse('${AppConfig.apiBaseUrl}/tickets/$ticketId/messages'),
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
      await http.post(
        Uri.parse('${AppConfig.apiBaseUrl}/user/fcm-token'),
        headers: await _headers(),
        body: jsonEncode({'fcm_token': fcmToken}),
      );
    } catch (_) {}
  }

  // 7. Logout
  static Future<void> logout() async {
    try {
      await http.post(
        Uri.parse('${AppConfig.apiBaseUrl}/logout'),
        headers: await _headers(),
      );
    } catch (_) {}
    await StorageService.clearAuth();
  }
}
