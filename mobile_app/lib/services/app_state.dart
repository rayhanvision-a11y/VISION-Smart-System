import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

class AppState extends ChangeNotifier {
  static final AppState instance = AppState._();
  AppState._();

  static const _kTheme = 'app_theme_mode';
  static const _kLocale = 'app_locale';

  ThemeMode _themeMode = ThemeMode.light;
  Locale _locale = const Locale('en');

  ThemeMode get themeMode => _themeMode;
  Locale get locale => _locale;
  bool get isDark => _themeMode == ThemeMode.dark;
  bool get isBengali => _locale.languageCode == 'bn';

  Future<void> load() async {
    final prefs = await SharedPreferences.getInstance();
    final t = prefs.getString(_kTheme);
    if (t == 'dark') _themeMode = ThemeMode.dark;
    final l = prefs.getString(_kLocale);
    if (l == 'bn') _locale = const Locale('bn');
    notifyListeners();
  }

  Future<void> toggleTheme() async {
    _themeMode = _themeMode == ThemeMode.dark ? ThemeMode.light : ThemeMode.dark;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_kTheme, _themeMode == ThemeMode.dark ? 'dark' : 'light');
    notifyListeners();
  }

  Future<void> toggleLocale() async {
    _locale = _locale.languageCode == 'bn' ? const Locale('en') : const Locale('bn');
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_kLocale, _locale.languageCode);
    notifyListeners();
  }

  String t(String en, String bn) => isBengali ? bn : en;
}
