import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../services/app_state.dart';

class AppColors {
  static const primary = Color(0xFF1B3A6B);
  static const primaryDark = Color(0xFF0F2547);
  static const primaryLight = Color(0xFF2E5695);
  static const accent = Color(0xFFD4A02A);
  static const accentSoft = Color(0xFFF5E4B4);
  static const coral = Color(0xFFFF6B6B);

  static const success = Color(0xFF10B981);
  static const warning = Color(0xFFF59E0B);
  static const danger = Color(0xFFEF4444);
  static const info = Color(0xFF3B82F6);

  static const _bgLight = Color(0xFFF5F7FA);
  static const _cardLight = Color(0xFFFFFFFF);
  static const _borderLight = Color(0xFFE5E9F0);
  static const _dividerLight = Color(0xFFEEF1F6);
  static const _textPrimaryLight = Color(0xFF0F172A);
  static const _textSecondaryLight = Color(0xFF64748B);
  static const _textMutedLight = Color(0xFF94A3B8);

  static const bgDark = Color(0xFF0B1220);
  static const cardDark = Color(0xFF1B2438);
  static const _borderDark = Color(0xFF2A3448);
  static const _dividerDark = Color(0xFF232D42);
  static const _textPrimaryDark = Color(0xFFF1F5F9);
  static const _textSecondaryDark = Color(0xFFCBD5E1);
  static const _textMutedDark = Color(0xFF94A3B8);

  static bool get _dark => AppState.instance.isDark;

  static Color get bg => _dark ? bgDark : _bgLight;
  static Color get card => _dark ? cardDark : _cardLight;
  static Color get border => _dark ? _borderDark : _borderLight;
  static Color get divider => _dark ? _dividerDark : _dividerLight;
  static Color get textPrimary => _dark ? _textPrimaryDark : _textPrimaryLight;
  static Color get textSecondary => _dark ? _textSecondaryDark : _textSecondaryLight;
  static Color get textMuted => _dark ? _textMutedDark : _textMutedLight;

  static Color statusColor(String? s) {
    switch (s?.toLowerCase()) {
      case 'resolved':
        return success;
      case 'in_progress':
        return info;
      case 'pending':
        return warning;
      case 'waiting_for_customer_feedback':
        return const Color(0xFF8B5CF6);
      case 'closed':
        return textMuted;
      default:
        return primaryLight;
    }
  }

  static Color priorityColor(String? p) {
    switch (p?.toLowerCase()) {
      case 'urgent':
        return danger;
      case 'high':
        return const Color(0xFFF97316);
      case 'medium':
        return warning;
      case 'low':
        return success;
      default:
        return textMuted;
    }
  }

  static LinearGradient get heroGradient => const LinearGradient(
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
        colors: [primary, primaryDark],
      );

  static LinearGradient get goldGradient => const LinearGradient(
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
        colors: [Color(0xFFE5B94A), accent],
      );
}

class AppRadius {
  static const sm = 8.0;
  static const md = 12.0;
  static const lg = 16.0;
  static const xl = 24.0;
  static const pill = 999.0;
}

class AppSpacing {
  static const xs = 4.0;
  static const sm = 8.0;
  static const md = 12.0;
  static const lg = 16.0;
  static const xl = 20.0;
  static const xxl = 28.0;
}

class AppShadows {
  static const card = [
    BoxShadow(
      color: Color(0x0F1B3A6B),
      blurRadius: 10,
      offset: Offset(0, 2),
    ),
  ];

  static const elevated = [
    BoxShadow(
      color: Color(0x1A1B3A6B),
      blurRadius: 18,
      offset: Offset(0, 6),
    ),
  ];
}

class AppText {
  static TextStyle get displayLg => GoogleFonts.inter(
        fontSize: 28,
        fontWeight: FontWeight.w700,
        color: AppColors.textPrimary,
        height: 1.2,
      );

  static TextStyle get h1 => GoogleFonts.inter(
        fontSize: 22,
        fontWeight: FontWeight.w700,
        color: AppColors.textPrimary,
      );

  static TextStyle get h2 => GoogleFonts.inter(
        fontSize: 18,
        fontWeight: FontWeight.w700,
        color: AppColors.textPrimary,
      );

  static TextStyle get h3 => GoogleFonts.inter(
        fontSize: 16,
        fontWeight: FontWeight.w600,
        color: AppColors.textPrimary,
      );

  static TextStyle get body => GoogleFonts.inter(
        fontSize: 14,
        fontWeight: FontWeight.w500,
        color: AppColors.textPrimary,
      );

  static TextStyle get bodySm => GoogleFonts.inter(
        fontSize: 13,
        fontWeight: FontWeight.w500,
        color: AppColors.textSecondary,
      );

  static TextStyle get caption => GoogleFonts.inter(
        fontSize: 12,
        fontWeight: FontWeight.w500,
        color: AppColors.textSecondary,
      );

  static TextStyle get label => GoogleFonts.inter(
        fontSize: 11,
        fontWeight: FontWeight.w600,
        color: AppColors.textMuted,
        letterSpacing: 0.4,
      );

  static TextStyle get button => GoogleFonts.inter(
        fontSize: 15,
        fontWeight: FontWeight.w600,
      );
}

ThemeData buildDarkTheme() {
  final base = ThemeData.dark(useMaterial3: true);
  return base.copyWith(
    scaffoldBackgroundColor: AppColors.bgDark,
    colorScheme: ColorScheme.fromSeed(
      seedColor: AppColors.primary,
      brightness: Brightness.dark,
      primary: AppColors.primaryLight,
      secondary: AppColors.accent,
      surface: AppColors.cardDark,
    ),
    textTheme: GoogleFonts.interTextTheme(base.textTheme).apply(
      bodyColor: Colors.white,
      displayColor: Colors.white,
    ),
    appBarTheme: const AppBarTheme(
      backgroundColor: AppColors.cardDark,
      foregroundColor: Colors.white,
      elevation: 0,
      centerTitle: false,
    ),
    cardTheme: CardThemeData(
      color: AppColors.cardDark,
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(AppRadius.lg),
        side: const BorderSide(color: Color(0xFF334155)),
      ),
    ),
  );
}

ThemeData buildAppTheme() {
  final base = ThemeData.light(useMaterial3: true);
  return base.copyWith(
    scaffoldBackgroundColor: AppColors.bg,
    colorScheme: ColorScheme.fromSeed(
      seedColor: AppColors.primary,
      primary: AppColors.primary,
      secondary: AppColors.accent,
      surface: AppColors.card,
    ),
    textTheme: GoogleFonts.interTextTheme(base.textTheme).apply(
      bodyColor: AppColors.textPrimary,
      displayColor: AppColors.textPrimary,
    ),
    appBarTheme: AppBarTheme(
      backgroundColor: AppColors.card,
      foregroundColor: AppColors.textPrimary,
      elevation: 0,
      centerTitle: false,
      titleTextStyle: AppText.h3,
      iconTheme: IconThemeData(color: AppColors.textPrimary),
    ),
    cardTheme: CardThemeData(
      color: AppColors.card,
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(AppRadius.lg),
        side: BorderSide(color: AppColors.border),
      ),
    ),
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: AppColors.card,
      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(AppRadius.md),
        borderSide: BorderSide(color: AppColors.border),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(AppRadius.md),
        borderSide: BorderSide(color: AppColors.border),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(AppRadius.md),
        borderSide: const BorderSide(color: AppColors.primary, width: 1.5),
      ),
      hintStyle: AppText.bodySm.copyWith(color: AppColors.textMuted),
    ),
    elevatedButtonTheme: ElevatedButtonThemeData(
      style: ElevatedButton.styleFrom(
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        elevation: 0,
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(AppRadius.md),
        ),
        textStyle: AppText.button,
      ),
    ),
    dividerTheme: DividerThemeData(
      color: AppColors.divider,
      thickness: 1,
      space: 1,
    ),
    chipTheme: ChipThemeData(
      backgroundColor: AppColors.card,
      selectedColor: AppColors.primary,
      side: BorderSide(color: AppColors.border),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(AppRadius.sm),
      ),
      labelStyle: AppText.caption,
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
    ),
  );
}
