import 'package:flutter/material.dart';

class AppConfig {
  static const String appName = 'VISION Smart System';
  
  // Base URL for Laravel Backend API
  static const String defaultApiBaseUrl = 'https://portal.visiontech.com.bd/api';
  static String apiBaseUrl = defaultApiBaseUrl;
  
  // Firebase Realtime Database URL
  static const String firebaseDbUrl = 'https://vision-smart-systeam-default-rtdb.firebaseio.com';

  // Official VISION logo (matches website)
  static const String logoUrl = 'https://visiontech.com.bd/wp-content/uploads/2017/11/vision-logo.png';

  // App Theme Colors
  static const Color primaryColor = Color(0xFFDC2626); // Vibrant Red
  static const Color primaryDark = Color(0xFF991B1B);
  static const Color accentColor = Color(0xFFF43F5E);
  static const Color backgroundLight = Color(0xFFF8FAFC);
  static const Color cardLight = Colors.white;
  static const Color backgroundDark = Color(0xFF0F172A);
  static const Color cardDark = Color(0xFF1E293B);

  // Status Colors
  static Color statusColor(String? status) {
    switch (status?.toLowerCase()) {
      case 'open':
        return Colors.blue;
      case 'in_progress':
        return Colors.orange;
      case 'on_hold':
        return Colors.purple;
      case 'resolved':
        return Colors.green;
      case 'closed':
        return Colors.grey;
      default:
        return Colors.blueGrey;
    }
  }

  // Priority Colors
  static Color priorityColor(String? priority) {
    switch (priority?.toLowerCase()) {
      case 'urgent':
        return Colors.red;
      case 'high':
        return Colors.deepOrange;
      case 'medium':
        return Colors.amber.shade800;
      case 'low':
        return Colors.teal;
      default:
        return Colors.grey;
    }
  }
}
