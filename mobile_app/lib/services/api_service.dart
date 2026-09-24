import 'dart:convert';
import 'package:flutter/foundation.dart';
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
      if (token != null && token.isNotEmpty) ...{
        'Authorization': 'Bearer $token',
        'X-Authorization': 'Bearer $token',
        'X-Api-Token': token,
      },
    };
  }

  static Future<Uri> _buildUri(String path, [Map<String, String?>? queryParams]) async {
    final baseUrl = await getBaseUrl();
    final token = await StorageService.getToken();
    final params = <String, String>{};
    if (queryParams != null) {
      queryParams.forEach((k, v) {
        if (v != null && v.isNotEmpty) {
          params[k] = v;
        }
      });
    }
    // Also include token in query parameters as a bulletproof fallback for Apache/cPanel FastCGI
    if (token != null && token.isNotEmpty) {
      params['token'] = token;
    }
    final base = Uri.parse('$baseUrl$path');
    return base.replace(queryParameters: params.isNotEmpty ? params : null);
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
      final uri = await _buildUri('/tickets', {
        if (status != null && status.isNotEmpty) 'status': status,
        if (search != null && search.isNotEmpty) 'search': search,
      });

      final response = await http.get(uri, headers: await _headers());
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final list = (data is Map && data['data'] is List)
            ? (data['data'] as List)
            : (data is List ? data : []);
        final List<TicketModel> results = [];
        for (var item in list) {
          if (item is Map) {
            try {
              results.add(TicketModel.fromJson(item));
            } catch (e) {
              debugPrint('TicketModel parse error: $e');
            }
          }
        }
        return results;
      } else {
        debugPrint('fetchTickets error ${response.statusCode}: ${response.body}');
      }
    } catch (e) {
      debugPrint('fetchTickets exception: $e');
    }
    return [];
  }

  // 2b. Fetch single ticket details & comments from Laravel API
  static Future<Map<String, dynamic>?> getTicketDetails(int ticketId) async {
    try {
      final uri = await _buildUri('/tickets/$ticketId');
      final response = await http.get(uri, headers: await _headers());
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        Map? ticketJson;
        if (data is Map && data['ticket'] is Map) {
          ticketJson = data['ticket'] as Map;
        } else if (data is Map) {
          ticketJson = data;
        }
        if (ticketJson != null) {
          TicketModel? ticket;
          try {
            ticket = TicketModel.fromJson(Map<String, dynamic>.from(ticketJson));
          } catch (e) {
            debugPrint('TicketModel parse error on details: $e');
          }
          final List<TicketMessageModel> messages = [];
          final rawMsgs = ticketJson['messages'];
          if (rawMsgs is List) {
            for (var m in rawMsgs) {
              if (m is Map) {
                try {
                  messages.add(TicketMessageModel.fromJson(Map<String, dynamic>.from(m)));
                } catch (e) {
                  debugPrint('TicketMessage parse error: $e');
                }
              }
            }
          }
          debugPrint('getTicketDetails: parsed ${messages.length} messages for ticket $ticketId');
          return {'ticket': ticket, 'messages': messages};
        }
      } else {
        debugPrint('getTicketDetails HTTP ${response.statusCode}: ${response.body}');
      }
    } catch (e) {
      debugPrint('getTicketDetails exception: $e');
    }
    return null;
  }

  // 2c. Refresh currently authenticated user (keeps role/preferences in sync)
  static Future<UserModel?> refreshCurrentUser() async {
    try {
      final uri = await _buildUri('/user');
      final response = await http.get(uri, headers: await _headers());
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data is Map<String, dynamic>) {
          final user = UserModel.fromJson(data);
          await StorageService.saveUser(user);
          return user;
        }
      }
    } catch (e) {
      debugPrint('refreshCurrentUser exception: $e');
    }
    return null;
  }

  // 3. Create Ticket
  static Future<Map<String, dynamic>> createTicket({
    required String title,
    required String description,
    required String priority,
    String? category,
  }) async {
    try {
      final uri = await _buildUri('/tickets');
      final response = await http.post(
        uri,
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
      final uri = await _buildUri('/tickets/$ticketId/status');
      final response = await http.post(
        uri,
        headers: await _headers(),
        body: jsonEncode({'status': status}),
      );
      return response.statusCode == 200;
    } catch (_) {
      return false;
    }
  }

  // 5. Add Message / Reply
  static Future<Map<String, dynamic>> addMessage(int ticketId, String message, {bool isPrivate = false, int? replyToId}) async {
    try {
      final uri = await _buildUri('/tickets/$ticketId/messages');
      final response = await http.post(
        uri,
        headers: await _headers(),
        body: jsonEncode({
          'message': message,
          'is_private': isPrivate,
          if (replyToId != null) 'reply_to_id': replyToId,
        }),
      );

      if (response.statusCode == 201) {
        final data = jsonDecode(response.body);
        return {
          'success': true,
          'message': TicketMessageModel.fromJson(data['data']),
        };
      }
      String errMsg = 'Failed to send message (${response.statusCode})';
      try {
        final data = jsonDecode(response.body);
        if (data is Map && data['message'] != null) errMsg = data['message'].toString();
      } catch (_) {}
      return {'success': false, 'error': errMsg};
    } catch (e) {
      return {'success': false, 'error': 'Network error: $e'};
    }
  }

  // 5b. Toggle Emoji Reaction on a Comment
  static Future<bool> toggleReaction(int ticketId, int messageId, String emoji) async {
    try {
      final uri = await _buildUri('/tickets/$ticketId/messages/$messageId/react');
      final response = await http.post(
        uri,
        headers: await _headers(),
        body: jsonEncode({'emoji': emoji}),
      );
      return response.statusCode == 200;
    } catch (_) {
      return false;
    }
  }

  // 6. Update Device FCM Token
  static Future<void> updateFcmToken(String fcmToken) async {
    try {
      final uri = await _buildUri('/user/fcm-token');
      await http.post(
        uri,
        headers: await _headers(),
        body: jsonEncode({'fcm_token': fcmToken}),
      );
    } catch (_) {}
  }

  // 7. Dashboard Stats & Duty Teams
  static Future<Map<String, dynamic>?> getDashboard() async {
    try {
      final uri = await _buildUri('/dashboard');
      final response = await http.get(
        uri,
        headers: await _headers(),
      );
      if (response.statusCode == 200) {
        return jsonDecode(response.body) as Map<String, dynamic>;
      } else {
        debugPrint('getDashboard error ${response.statusCode}: ${response.body}');
      }
    } catch (e) {
      debugPrint('getDashboard exception: $e');
    }
    return null;
  }

  // 8. Duty Roster List
  static Future<Map<String, dynamic>?> getRoster() async {
    try {
      final uri = await _buildUri('/roster');
      final response = await http.get(
        uri,
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
      final uri = await _buildUri('/users', {
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
      final uri = await _buildUri('/categories');
      final response = await http.get(uri, headers: await _headers());
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
      final uri = await _buildUri('/pop-offices');
      final response = await http.get(uri, headers: await _headers());
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
      final uri = await _buildUri('/staff');
      final response = await http.get(uri, headers: await _headers());
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
      final uri = await _buildUri('/tickets/$ticketId/assign');
      final response = await http.post(
        uri,
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
      final uri = await _buildUri('/tickets/$ticketId/priority');
      final response = await http.post(
        uri,
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
      final uri = await _buildUri('/logout');
      await http.post(
        uri,
        headers: await _headers(),
      );
    } catch (_) {}
    await StorageService.clearAuth();
  }
}
