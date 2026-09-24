import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';

enum PrimaryButtonKind { primary, gold, outline, danger }

class PrimaryButton extends StatelessWidget {
  final String label;
  final VoidCallback? onPressed;
  final IconData? icon;
  final bool loading;
  final bool expand;
  final PrimaryButtonKind kind;

  const PrimaryButton({
    Key? key,
    required this.label,
    this.onPressed,
    this.icon,
    this.loading = false,
    this.expand = true,
    this.kind = PrimaryButtonKind.primary,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final Color bg;
    final Color fg;
    final BorderSide? side;
    switch (kind) {
      case PrimaryButtonKind.gold:
        bg = AppColors.accent;
        fg = Colors.white;
        side = null;
        break;
      case PrimaryButtonKind.outline:
        bg = Colors.transparent;
        fg = AppColors.primary;
        side = const BorderSide(color: AppColors.primary, width: 1.4);
        break;
      case PrimaryButtonKind.danger:
        bg = AppColors.danger;
        fg = Colors.white;
        side = null;
        break;
      case PrimaryButtonKind.primary:
        bg = AppColors.primary;
        fg = Colors.white;
        side = null;
    }

    final btn = ElevatedButton(
      onPressed: loading ? null : onPressed,
      style: ElevatedButton.styleFrom(
        backgroundColor: bg,
        foregroundColor: fg,
        elevation: 0,
        padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 14),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(AppRadius.md),
          side: side ?? BorderSide.none,
        ),
        textStyle: AppText.button,
      ),
      child: loading
          ? SizedBox(
              width: 18,
              height: 18,
              child: CircularProgressIndicator(strokeWidth: 2, color: fg),
            )
          : Row(
              mainAxisSize: expand ? MainAxisSize.max : MainAxisSize.min,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                if (icon != null) ...[
                  Icon(icon, size: 18, color: fg),
                  const SizedBox(width: 8),
                ],
                Text(label),
              ],
            ),
    );

    return expand ? SizedBox(width: double.infinity, child: btn) : btn;
  }
}
