import 'package:flutter/material.dart';
import '../../models/user.dart';
import '../../theme/app_theme.dart';

class AppHeader extends StatelessWidget {
  final UserModel? user;
  final int notificationCount;
  final VoidCallback? onSearchTap;
  final VoidCallback? onNotificationTap;
  final VoidCallback? onAvatarTap;

  const AppHeader({
    Key? key,
    this.user,
    this.notificationCount = 0,
    this.onSearchTap,
    this.onNotificationTap,
    this.onAvatarTap,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final name = user?.name ?? 'Welcome';
    final role = (user?.role ?? '').replaceAll('_', ' ').toUpperCase();
    final initial = name.isNotEmpty ? name[0].toUpperCase() : 'V';

    return Container(
      padding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.md, AppSpacing.lg, AppSpacing.md),
      decoration: BoxDecoration(color: AppColors.card),
      child: SafeArea(
        bottom: false,
        child: Row(
          children: [
            GestureDetector(
              onTap: onAvatarTap,
              child: Container(
                width: 42,
                height: 42,
                decoration: BoxDecoration(
                  gradient: AppColors.heroGradient,
                  shape: BoxShape.circle,
                  border: Border.all(color: AppColors.accent.withOpacity(0.6), width: 2),
                ),
                child: ClipOval(
                  child: (user?.avatarUrl != null && user!.avatarUrl!.isNotEmpty)
                      ? Image.network(
                          user!.avatarUrl!,
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => Container(
                            alignment: Alignment.center,
                            child: Text(initial, style: AppText.h3.copyWith(color: Colors.white)),
                          ),
                        )
                      : Container(
                          alignment: Alignment.center,
                          child: Text(initial, style: AppText.h3.copyWith(color: Colors.white)),
                        ),
                ),
              ),
            ),
            const SizedBox(width: AppSpacing.md),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(name,
                      style: AppText.h3, maxLines: 1, overflow: TextOverflow.ellipsis),
                  if (role.isNotEmpty)
                    Text(role, style: AppText.label.copyWith(color: AppColors.primary)),
                ],
              ),
            ),
            _iconBtn(Icons.search_rounded, onTap: onSearchTap),
            const SizedBox(width: 6),
            Stack(
              clipBehavior: Clip.none,
              children: [
                _iconBtn(Icons.notifications_none_rounded, onTap: onNotificationTap),
                if (notificationCount > 0)
                  Positioned(
                    right: -2,
                    top: -2,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
                      decoration: BoxDecoration(
                        color: AppColors.danger,
                        borderRadius: BorderRadius.circular(AppRadius.pill),
                        border: Border.all(color: AppColors.card, width: 1.5),
                      ),
                      constraints: const BoxConstraints(minWidth: 18, minHeight: 18),
                      alignment: Alignment.center,
                      child: Text(
                        notificationCount > 9 ? '9+' : '$notificationCount',
                        style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w700),
                      ),
                    ),
                  ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _iconBtn(IconData icon, {VoidCallback? onTap}) {
    return InkResponse(
      onTap: onTap,
      radius: 22,
      child: Container(
        width: 40,
        height: 40,
        decoration: BoxDecoration(
          color: AppColors.bg,
          borderRadius: BorderRadius.circular(AppRadius.md),
          border: Border.all(color: AppColors.border),
        ),
        child: Icon(icon, size: 20, color: AppColors.textPrimary),
      ),
    );
  }
}
