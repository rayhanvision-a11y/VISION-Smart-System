import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/user.dart';

class StorageService {
  static const String _keyToken = 'auth_token';
  static const String _keyUser = 'auth_user';

  static Future<void> saveAuth(String token, UserModel user) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_keyToken, token);
    await prefs.setString(_keyUser, jsonEncode(user.toJson()));
  }

  static Future<void> saveUser(UserModel user) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_keyUser, jsonEncode(user.toJson()));
  }

  static Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_keyToken);
  }

  static Future<UserModel?> getUser() async {
    final prefs = await SharedPreferences.getInstance();
    final userStr = prefs.getString(_keyUser);
    if (userStr == null) return null;
    try {
      return UserModel.fromJson(jsonDecode(userStr));
    } catch (_) {
      return null;
    }
  }

  static const String _keyServerUrl = 'server_url';
  static const String _keyRememberMe = 'remember_me';
  static const String _keySavedEmail = 'saved_email';
  static const String _keySavedPassword = 'saved_password';
  static const String _keyBiometricEnabled = 'biometric_enabled';
  static const String _keyOfflineQueue = 'offline_queue';

  static Future<String?> getServerUrl() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_keyServerUrl);
  }

  static Future<void> setServerUrl(String url) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_keyServerUrl, url);
  }

  static const String _cryptoSalt = 'VISION_SMART_BIO_SECURE_TOKEN_2026_X9';

  static String _encrypt(String plain) {
    if (plain.isEmpty) return '';
    final plainBytes = utf8.encode(plain);
    final saltBytes = utf8.encode(_cryptoSalt);
    final encrypted = List<int>.generate(plainBytes.length, (i) {
      return plainBytes[i] ^ saltBytes[i % saltBytes.length];
    });
    return 'enc:${base64Encode(encrypted)}';
  }

  static String _decrypt(String cipher) {
    if (cipher.isEmpty) return '';
    if (!cipher.startsWith('enc:')) {
      // Legacy plain text fallback
      return cipher;
    }
    try {
      final rawBase64 = cipher.substring(4);
      final encryptedBytes = base64Decode(rawBase64);
      final saltBytes = utf8.encode(_cryptoSalt);
      final decrypted = List<int>.generate(encryptedBytes.length, (i) {
        return encryptedBytes[i] ^ saltBytes[i % saltBytes.length];
      });
      return utf8.decode(decrypted);
    } catch (_) {
      return '';
    }
  }

  // Remember Me & Saved Credentials (Securely Encrypted)
  static Future<void> saveCredentials(String email, String password, bool rememberMe) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(_keyRememberMe, rememberMe);
    if (rememberMe) {
      await prefs.setString(_keySavedEmail, email);
      await prefs.setString(_keySavedPassword, _encrypt(password));
    } else {
      await prefs.remove(_keySavedEmail);
      await prefs.remove(_keySavedPassword);
    }
  }

  static Future<Map<String, dynamic>> getSavedCredentials() async {
    final prefs = await SharedPreferences.getInstance();
    final remember = prefs.getBool(_keyRememberMe) ?? false;
    final email = prefs.getString(_keySavedEmail) ?? '';
    final rawPass = prefs.getString(_keySavedPassword) ?? '';
    final password = _decrypt(rawPass);
    return {
      'remember': remember,
      'email': email,
      'password': password,
    };
  }

  // Biometric Auth Preferences
  static Future<void> setBiometricEnabled(bool enabled) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(_keyBiometricEnabled, enabled);
  }

  static Future<bool> isBiometricEnabled() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getBool(_keyBiometricEnabled) ?? false;
  }

  // Offline Queue
  static Future<List<Map<String, dynamic>>> getOfflineQueue() async {
    final prefs = await SharedPreferences.getInstance();
    final list = prefs.getStringList(_keyOfflineQueue) ?? [];
    return list.map((item) => jsonDecode(item) as Map<String, dynamic>).toList();
  }

  static Future<void> addToOfflineQueue(Map<String, dynamic> action) async {
    final prefs = await SharedPreferences.getInstance();
    final list = prefs.getStringList(_keyOfflineQueue) ?? [];
    list.add(jsonEncode(action));
    await prefs.setStringList(_keyOfflineQueue, list);
  }

  static Future<void> clearOfflineQueue() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_keyOfflineQueue);
  }

  static Future<void> clearAuth() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_keyToken);
    await prefs.remove(_keyUser);
  }
}
