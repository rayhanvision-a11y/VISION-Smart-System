import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:http_parser/http_parser.dart';
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
  static Future<List<TicketModel>> fetchTickets({String? status, String? search, String? area}) async {
    try {
      final uri = await _buildUri('/tickets', {
        if (status != null && status.isNotEmpty) 'status': status,
        if (search != null && search.isNotEmpty) 'search': search,
        if (area != null && area.isNotEmpty) 'area': area,
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
    int? popOfficeId,
    int? assignedTo,
    DateTime? dueAt,
    String? area,
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
          if (popOfficeId != null) 'pop_office_id': popOfficeId,
          if (assignedTo != null) 'assigned_to': assignedTo,
          if (dueAt != null) 'due_at': dueAt.toIso8601String(),
          if (area != null && area.isNotEmpty) 'area': area,
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

  // 3b. Upload attachment to a ticket
  static Future<Map<String, dynamic>> uploadTicketAttachment({
    required int ticketId,
    required List<int> bytes,
    required String filename,
  }) async {
    try {
      final uri = await _buildUri('/tickets/$ticketId/attachments');
      final token = await StorageService.getToken();
      final req = http.MultipartRequest('POST', uri);
      req.headers.addAll({
        'Accept': 'application/json',
        if (token != null && token.isNotEmpty) ...{
          'Authorization': 'Bearer $token',
          'X-Authorization': 'Bearer $token',
          'X-Api-Token': token,
        },
      });
      req.files.add(http.MultipartFile.fromBytes('file', bytes, filename: filename));
      final resp = await req.send();
      final body = await resp.stream.bytesToString();
      if (resp.statusCode == 201) {
        return {'success': true, 'data': jsonDecode(body)};
      }
      return {'success': false, 'message': 'Upload failed (${resp.statusCode})'};
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  // Location tracking
  static Future<bool> updateLocation({
    required double lat,
    required double lng,
    int? accuracy,
    int? battery,
    double? speed,
  }) async {
    try {
      final uri = await _buildUri('/user/location');
      final r = await http.post(uri, headers: await _headers(), body: jsonEncode({
        'lat': lat,
        'lng': lng,
        if (accuracy != null) 'accuracy': accuracy,
        if (battery != null) 'battery': battery,
        if (speed != null) 'speed': speed,
      }));
      return r.statusCode == 200;
    } catch (_) { return false; }
  }

  static Future<bool> toggleLocationSharing(bool on) async {
    try {
      final uri = await _buildUri('/user/location/toggle');
      final r = await http.post(uri, headers: await _headers(), body: jsonEncode({'sharing': on}));
      return r.statusCode == 200;
    } catch (_) { return false; }
  }

  static Future<List<Map<String, dynamic>>> getAllLocations({String? role}) async {
    try {
      final uri = await _buildUri('/locations/all', {if (role != null && role.isNotEmpty) 'role': role});
      final r = await http.get(uri, headers: await _headers());
      if (r.statusCode == 200) {
        final data = jsonDecode(r.body);
        if (data is Map && data['locations'] is List) {
          return (data['locations'] as List)
              .whereType<Map>()
              .map((e) => Map<String, dynamic>.from(e))
              .toList();
        }
      }
    } catch (_) {}
    return [];
  }

  // 3f. Admin: update another user's shift
  static Future<Map<String, dynamic>> updateUserShift(int userId, String shift) async {
    try {
      final uri = await _buildUri('/roster/user/$userId/shift');
      final response = await http.post(uri, headers: await _headers(), body: jsonEncode({'shift': shift}));
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return {'success': true, 'current_shift': data['current_shift'], 'is_on_duty': data['is_on_duty']};
      }
      final data = jsonDecode(response.body);
      return {'success': false, 'message': data['message'] ?? data['error'] ?? 'Failed'};
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  // 3e. Update own shift/duty
  static Future<Map<String, dynamic>> updateOwnShift(String shift) async {
    try {
      final uri = await _buildUri('/user/shift');
      final response = await http.post(uri, headers: await _headers(), body: jsonEncode({'shift': shift}));
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return {'success': true, 'current_shift': data['current_shift'], 'is_on_duty': data['is_on_duty']};
      }
      final data = jsonDecode(response.body);
      return {'success': false, 'message': data['message'] ?? 'Failed'};
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  // 3c. Update user profile (name/phone)
  static Future<Map<String, dynamic>> updateProfile({required String name, String? phone}) async {
    try {
      final uri = await _buildUri('/user/profile');
      final response = await http.post(
        uri,
        headers: await _headers(),
        body: jsonEncode({'name': name, if (phone != null) 'phone': phone}),
      );
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['user'] is Map) {
          final user = UserModel.fromJson(Map<String, dynamic>.from(data['user']));
          await StorageService.saveUser(user);
          return {'success': true, 'user': user};
        }
      }
      final data = jsonDecode(response.body);
      return {'success': false, 'message': data['message'] ?? 'Update failed'};
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  // 3d. Change password
  static Future<Map<String, dynamic>> changePassword({
    required String currentPassword,
    required String newPassword,
  }) async {
    try {
      final uri = await _buildUri('/user/password');
      final response = await http.post(
        uri,
        headers: await _headers(),
        body: jsonEncode({
          'current_password': currentPassword,
          'new_password': newPassword,
          'new_password_confirmation': newPassword,
        }),
      );
      if (response.statusCode == 200) return {'success': true};
      final data = jsonDecode(response.body);
      return {'success': false, 'message': data['message'] ?? 'Password change failed'};
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

  // 15a. Areas autocomplete
  static Future<List<String>> getAreas({String? search}) async {
    try {
      final uri = await _buildUri('/areas', {if (search != null && search.isNotEmpty) 'search': search});
      final response = await http.get(uri, headers: await _headers());
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data is Map && data['areas'] is List) {
          return (data['areas'] as List).map((e) => e.toString()).toList();
        }
      }
    } catch (_) {}
    return [];
  }

  // 15a2. Areas with active ticket counts (sorted busiest first)
  static Future<List<Map<String, dynamic>>> getAreasWithCounts({String? search}) async {
    try {
      final uri = await _buildUri('/areas', {if (search != null && search.isNotEmpty) 'search': search});
      final response = await http.get(uri, headers: await _headers());
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data is Map && data['areas_with_counts'] is List) {
          return (data['areas_with_counts'] as List)
              .whereType<Map>()
              .map((e) => Map<String, dynamic>.from(e))
              .toList();
        }
        // fallback if server hasn't been updated
        if (data is Map && data['areas'] is List) {
          return (data['areas'] as List)
              .map((n) => {'name': n.toString(), 'active_count': 0})
              .toList();
        }
      }
    } catch (_) {}
    return [];
  }

  // 15c. Technicians with workload
  static Future<List<dynamic>> getTechnicians() async {
    try {
      final uri = await _buildUri('/technicians');
      final response = await http.get(uri, headers: await _headers());
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return (data['technicians'] as List?) ?? [];
      }
    } catch (_) {}
    return [];
  }

  // 15d. Bulk assign tickets
  static Future<Map<String, dynamic>> bulkAssign({
    required List<int> ticketIds,
    required int assignedTo,
  }) async {
    try {
      final uri = await _buildUri('/tickets/bulk-assign');
      final response = await http.post(
        uri,
        headers: await _headers(),
        body: jsonEncode({'ticket_ids': ticketIds, 'assigned_to': assignedTo}),
      );
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return {'success': true, 'count': data['count'] ?? 0};
      }
      final data = jsonDecode(response.body);
      return {'success': false, 'message': data['message'] ?? data['error'] ?? 'Failed'};
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  // 15b. Upload user avatar (multipart). `bytes` is required (works on web + mobile).
  static Future<Map<String, dynamic>> uploadAvatar({
    required List<int> bytes,
    required String filename,
  }) async {
    try {
      final uri = await _buildUri('/user/avatar');
      final token = await StorageService.getToken();
      final req = http.MultipartRequest('POST', uri);
      req.headers.addAll({
        'Accept': 'application/json',
        if (token != null && token.isNotEmpty) ...{
          'Authorization': 'Bearer $token',
          'X-Authorization': 'Bearer $token',
          'X-Api-Token': token,
        },
      });
      final ext = filename.split('.').last.toLowerCase();
      final mime = (ext == 'png')
          ? MediaType('image', 'png')
          : (ext == 'webp')
              ? MediaType('image', 'webp')
              : MediaType('image', 'jpeg');
      req.files.add(http.MultipartFile.fromBytes(
        'avatar',
        bytes,
        filename: filename,
        contentType: mime,
      ));
      final resp = await req.send();
      final body = await resp.stream.bytesToString();
      if (resp.statusCode == 200) {
        final data = jsonDecode(body);
        return {'success': true, 'avatar_url': data['avatar_url'], 'avatar': data['avatar']};
      }
      debugPrint('uploadAvatar ${resp.statusCode}: $body');
      return {'success': false, 'message': 'Upload failed (${resp.statusCode})'};
    } catch (e) {
      debugPrint('uploadAvatar exception: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  // Notifications
  static Future<Map<String, dynamic>> getNotifications() async {
    try {
      final uri = await _buildUri('/notifications');
      final response = await http.get(uri, headers: await _headers());
      if (response.statusCode == 200) return jsonDecode(response.body) as Map<String, dynamic>;
    } catch (_) {}
    return {'notifications': [], 'unread_count': 0};
  }

  static Future<bool> markNotificationRead(int id) async {
    try {
      final uri = await _buildUri('/notifications/$id/read');
      final r = await http.post(uri, headers: await _headers());
      return r.statusCode == 200;
    } catch (_) { return false; }
  }

  static Future<bool> markAllNotificationsRead() async {
    try {
      final uri = await _buildUri('/notifications/read-all');
      final r = await http.post(uri, headers: await _headers());
      return r.statusCode == 200;
    } catch (_) { return false; }
  }

  // Message edit / delete
  static Future<Map<String, dynamic>> updateMessage(int ticketId, int messageId, String text) async {
    try {
      final uri = await _buildUri('/tickets/$ticketId/messages/$messageId');
      final r = await http.patch(uri, headers: await _headers(), body: jsonEncode({'message': text}));
      if (r.statusCode == 200) return {'success': true};
      return {'success': false, 'message': jsonDecode(r.body)['error'] ?? 'Failed'};
    } catch (e) { return {'success': false, 'message': e.toString()}; }
  }

  static Future<bool> deleteMessage(int ticketId, int messageId) async {
    try {
      final uri = await _buildUri('/tickets/$ticketId/messages/$messageId');
      final r = await http.delete(uri, headers: await _headers());
      return r.statusCode == 200;
    } catch (_) { return false; }
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
