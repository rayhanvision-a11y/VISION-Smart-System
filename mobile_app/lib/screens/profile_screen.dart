import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../models/user.dart';
import '../services/api_service.dart';
import '../services/storage_service.dart';
import '../services/app_state.dart';
import '../theme/app_theme.dart';
import '../widgets/common/primary_button.dart';
import 'login_screen.dart';
import 'notifications_screen.dart';
import 'edit_profile_screen.dart';
import 'change_password_screen.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({Key? key}) : super(key: key);

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  UserModel? _user;
  String _serverUrl = '';
  bool _isLoading = true;
  bool _isUploading = false;

  @override
  void initState() {
    super.initState();
    _loadProfile();
  }

  Future<void> _loadProfile() async {
    setState(() => _isLoading = true);
    var user = await StorageService.getUser();
    final url = await ApiService.getBaseUrl();
    // Try refresh from server to pick up latest avatar_url
    final refreshed = await ApiService.refreshCurrentUser();
    if (refreshed != null) user = refreshed;
    if (mounted) {
      setState(() {
        _user = user;
        _serverUrl = url;
        _isLoading = false;
      });
    }
  }

  Future<void> _pickAndUploadAvatar() async {
    final picker = ImagePicker();
    final XFile? picked = await picker.pickImage(
      source: ImageSource.gallery,
      imageQuality: 85,
      maxWidth: 800,
    );
    if (picked == null || !mounted) return;

    setState(() => _isUploading = true);
    final bytes = await picked.readAsBytes();
    final result = await ApiService.uploadAvatar(
      bytes: bytes,
      filename: picked.name,
    );

    if (!mounted) return;
    setState(() => _isUploading = false);

    if (result['success'] == true && _user != null) {
      final updated = _user!.copyWith(
        avatar: result['avatar'] as String?,
        avatarUrl: result['avatar_url'] as String?,
      );
      await StorageService.saveUser(updated);
      setState(() => _user = updated);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Profile picture updated'),
          backgroundColor: AppColors.success,
          behavior: SnackBarBehavior.floating,
        ),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result['message']?.toString() ?? 'Upload failed'),
          backgroundColor: AppColors.danger,
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  Future<void> _handleLogout() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.lg)),
        title: Text('Sign out', style: AppText.h3),
        content: Text('Are you sure you want to sign out?', style: AppText.bodySm),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancel')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.danger),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Sign out'),
          ),
        ],
      ),
    );
    if (confirmed == true) {
      await ApiService.logout();
      if (mounted) {
        Navigator.pushAndRemoveUntil(
          context,
          MaterialPageRoute(builder: (_) => const LoginScreen()),
          (r) => false,
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : CustomScrollView(
              slivers: [
                SliverToBoxAdapter(child: _heroHeader()),
                SliverPadding(
                  padding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.lg, AppSpacing.lg, AppSpacing.xxl),
                  sliver: SliverList(
                    delegate: SliverChildListDelegate([
                      _dutyCard(),
                      const SizedBox(height: AppSpacing.md),
                      _menuSection(AppState.instance.t('Account', 'অ্যাকাউন্ট'), [
                        _menuItem(
                          Icons.person_outline_rounded,
                          AppState.instance.t('Edit Profile', 'প্রোফাইল সম্পাদনা'),
                          AppColors.info,
                          () async {
                            final updated = await Navigator.push(context,
                                MaterialPageRoute(builder: (_) => const EditProfileScreen()));
                            if (updated == true) _loadProfile();
                          },
                        ),
                        _menuItem(
                          Icons.lock_outline_rounded,
                          AppState.instance.t('Change Password', 'পাসওয়ার্ড পরিবর্তন'),
                          AppColors.danger,
                          () => Navigator.push(context,
                              MaterialPageRoute(builder: (_) => const ChangePasswordScreen())),
                        ),
                        _menuItem(Icons.email_outlined, 'Email', AppColors.info, () {},
                            trailing: Text(_user?.email ?? '', style: AppText.caption)),
                        if (_user?.phone != null && _user!.phone!.isNotEmpty)
                          _menuItem(Icons.phone_outlined, 'Phone', AppColors.info, () {},
                              trailing: Text(_user!.phone!, style: AppText.caption)),
                      ]),
                      const SizedBox(height: AppSpacing.md),
                      _menuSection('Preferences', [
                        _menuItem(Icons.notifications_none_rounded, 'Notifications', AppColors.warning, () {
                          Navigator.push(context,
                              MaterialPageRoute(builder: (_) => const NotificationsScreen()));
                        }),
                        _menuItem(
                          Icons.language_rounded,
                          AppState.instance.t('Language', 'ভাষা'),
                          AppColors.info,
                          () async {
                            await AppState.instance.toggleLocale();
                            if (mounted) setState(() {});
                          },
                          trailing: Text(
                            AppState.instance.isBengali ? 'বাংলা' : 'English',
                            style: AppText.caption.copyWith(color: AppColors.primary, fontWeight: FontWeight.w600),
                          ),
                        ),
                        _menuItem(
                          AppState.instance.isDark ? Icons.dark_mode_rounded : Icons.dark_mode_outlined,
                          AppState.instance.t('Dark Mode', 'ডার্ক মোড'),
                          const Color(0xFF8B5CF6),
                          () async {
                            await AppState.instance.toggleTheme();
                            if (mounted) setState(() {});
                          },
                          trailing: Switch(
                            value: AppState.instance.isDark,
                            activeColor: AppColors.primary,
                            onChanged: (_) async {
                              await AppState.instance.toggleTheme();
                              if (mounted) setState(() {});
                            },
                          ),
                        ),
                      ]),
                      const SizedBox(height: AppSpacing.md),
                      _menuSection('System', [
                        _menuItem(Icons.dns_outlined, 'Server', AppColors.success, () {},
                            trailing: Text(
                              _serverUrl.length > 26 ? '…${_serverUrl.substring(_serverUrl.length - 22)}' : _serverUrl,
                              style: AppText.caption,
                            )),
                        _menuItem(Icons.info_outline_rounded, 'App Version', AppColors.textSecondary, () {},
                            trailing: Text('v1.0.0', style: AppText.caption)),
                        _menuItem(Icons.verified_user_outlined, 'System Status', AppColors.success, () {},
                            trailing: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Container(
                                  width: 8, height: 8,
                                  decoration: const BoxDecoration(
                                    color: AppColors.success, shape: BoxShape.circle,
                                  ),
                                ),
                                const SizedBox(width: 4),
                                Text('Online', style: AppText.caption.copyWith(color: AppColors.success)),
                              ],
                            )),
                      ]),
                      const SizedBox(height: AppSpacing.xl),
                      PrimaryButton(
                        label: 'Sign Out',
                        icon: Icons.logout_rounded,
                        kind: PrimaryButtonKind.danger,
                        onPressed: _handleLogout,
                      ),
                      const SizedBox(height: AppSpacing.xl),
                      Center(
                        child: Text('VISION Smart System · Made with ❤️',
                            style: AppText.label.copyWith(color: AppColors.textMuted)),
                      ),
                    ]),
                  ),
                ),
              ],
            ),
    );
  }

  Widget _heroHeader() {
    final initials = (_user?.name.isNotEmpty ?? false) ? _user!.name[0].toUpperCase() : 'U';
    final avatarUrl = _user?.avatarUrl;

    return Container(
      padding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.md, AppSpacing.lg, AppSpacing.xl),
      decoration: BoxDecoration(
        gradient: AppColors.heroGradient,
        borderRadius: const BorderRadius.vertical(bottom: Radius.circular(32)),
      ),
      child: SafeArea(
        bottom: false,
        child: Stack(
          clipBehavior: Clip.none,
          children: [
            Positioned(
              right: -20, top: -10,
              child: Container(
                width: 140, height: 140,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: Colors.white.withOpacity(0.06),
                ),
              ),
            ),
            Column(
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                Row(
                  children: [
                    if (Navigator.canPop(context))
                      IconButton(
                        icon: const Icon(Icons.arrow_back_rounded, color: Colors.white),
                        onPressed: () => Navigator.pop(context),
                      ),
                    Text(AppState.instance.t('Profile', 'প্রোফাইল'),
                        style: AppText.h2.copyWith(color: Colors.white)),
                    const Spacer(),
                    IconButton(
                      icon: const Icon(Icons.notifications_none_rounded, color: Colors.white),
                      onPressed: () => Navigator.push(context,
                          MaterialPageRoute(builder: (_) => const NotificationsScreen())),
                    ),
                  ],
                ),
                const SizedBox(height: AppSpacing.md),
                Stack(
                  children: [
                    Container(
                      width: 108, height: 108,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        border: Border.all(color: Colors.white, width: 4),
                        boxShadow: AppShadows.elevated,
                      ),
                      child: ClipOval(
                        child: (avatarUrl != null && avatarUrl.isNotEmpty)
                            ? Image.network(
                                avatarUrl,
                                fit: BoxFit.cover,
                                loadingBuilder: (_, child, p) => p == null
                                    ? child
                                    : Container(
                                        color: AppColors.primaryLight,
                                        alignment: Alignment.center,
                                        child: const CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                                      ),
                                errorBuilder: (_, __, ___) => _avatarInitial(initials),
                              )
                            : _avatarInitial(initials),
                      ),
                    ),
                    Positioned(
                      right: 0, bottom: 0,
                      child: Material(
                        color: AppColors.accent,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(20),
                          side: const BorderSide(color: Colors.white, width: 3),
                        ),
                        child: InkWell(
                          borderRadius: BorderRadius.circular(20),
                          onTap: _isUploading ? null : _pickAndUploadAvatar,
                          child: Padding(
                            padding: const EdgeInsets.all(7),
                            child: _isUploading
                                ? const SizedBox(
                                    width: 16, height: 16,
                                    child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                                  )
                                : const Icon(Icons.camera_alt_rounded, color: Colors.white, size: 16),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: AppSpacing.md),
                Text(_user?.name ?? '',
                    style: AppText.h1.copyWith(color: Colors.white)),
                const SizedBox(height: 4),
                Text(_user?.email ?? '',
                    style: AppText.bodySm.copyWith(color: Colors.white.withOpacity(0.8))),
                const SizedBox(height: AppSpacing.md),
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                      decoration: BoxDecoration(
                        color: AppColors.accent.withOpacity(0.2),
                        borderRadius: BorderRadius.circular(AppRadius.pill),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.verified_rounded, size: 14, color: AppColors.accent),
                          const SizedBox(width: 4),
                          Text((_user?.role ?? '').replaceAll('_', ' ').toUpperCase(),
                              style: AppText.label.copyWith(color: AppColors.accent, fontSize: 11)),
                        ],
                      ),
                    ),
                    if (_user?.team != null && _user!.team!.isNotEmpty) ...[
                      const SizedBox(width: 8),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                        decoration: BoxDecoration(
                          color: Colors.white.withOpacity(0.15),
                          borderRadius: BorderRadius.circular(AppRadius.pill),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.groups_2_rounded, size: 14, color: Colors.white),
                            const SizedBox(width: 4),
                            Text(_user!.team!.toUpperCase(),
                                style: AppText.label.copyWith(color: Colors.white, fontSize: 11)),
                          ],
                        ),
                      ),
                    ],
                  ],
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _avatarInitial(String s) => Container(
        color: AppColors.primaryLight,
        alignment: Alignment.center,
        child: Text(s, style: AppText.displayLg.copyWith(color: Colors.white, fontSize: 40)),
      );

  String? _currentShift;
  bool _isShiftLoading = false;

  Future<void> _setShift(String shift) async {
    setState(() => _isShiftLoading = true);
    final res = await ApiService.updateOwnShift(shift);
    if (!mounted) return;
    setState(() {
      _isShiftLoading = false;
      if (res['success'] == true) {
        _currentShift = res['current_shift']?.toString();
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('${AppState.instance.t('Duty updated:', 'ডিউটি:')} ${_shiftLabel(_currentShift)}'),
            backgroundColor: AppColors.success,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    });
  }

  String _shiftLabel(String? s) {
    switch (s) {
      case 'day_shift': return AppState.instance.t('Day Shift', 'ডে শিফট');
      case 'night_shift': return AppState.instance.t('Night Shift', 'নাইট শিফট');
      case 'day_off': return AppState.instance.t('Day Off', 'ছুটি');
      default: return AppState.instance.t('Unassigned', 'নির্ধারিত না');
    }
  }

  Widget _dutyCard() {
    final options = [
      ('day_shift', '☀️', AppState.instance.t('Day', 'দিন'), AppColors.warning),
      ('night_shift', '🌙', AppState.instance.t('Night', 'রাত'), AppColors.info),
      ('day_off', '🏖️', AppState.instance.t('Off', 'ছুটি'), AppColors.textMuted),
    ];
    return Container(
      padding: const EdgeInsets.all(AppSpacing.md),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.schedule_rounded, color: AppColors.primary, size: 18),
              const SizedBox(width: 8),
              Text(AppState.instance.t('My Duty Shift', 'আমার ডিউটি'),
                  style: AppText.body.copyWith(fontWeight: FontWeight.w700)),
              const Spacer(),
              if (_isShiftLoading)
                const SizedBox(width: 14, height: 14, child: CircularProgressIndicator(strokeWidth: 2))
              else
                Text(_shiftLabel(_currentShift),
                    style: AppText.caption.copyWith(color: AppColors.primary, fontWeight: FontWeight.w600)),
            ],
          ),
          const SizedBox(height: AppSpacing.md),
          Row(
            children: options.map((o) {
              final selected = _currentShift == o.$1;
              return Expanded(
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 3),
                  child: Material(
                    color: selected ? o.$4 : AppColors.bg,
                    borderRadius: BorderRadius.circular(AppRadius.md),
                    child: InkWell(
                      borderRadius: BorderRadius.circular(AppRadius.md),
                      onTap: _isShiftLoading ? null : () => _setShift(o.$1),
                      child: Container(
                        padding: const EdgeInsets.symmetric(vertical: 10),
                        alignment: Alignment.center,
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(AppRadius.md),
                          border: Border.all(color: selected ? o.$4 : AppColors.border),
                        ),
                        child: Column(
                          children: [
                            Text(o.$2, style: const TextStyle(fontSize: 18)),
                            const SizedBox(height: 2),
                            Text(o.$3,
                                style: AppText.label.copyWith(
                                  color: selected ? Colors.white : AppColors.textSecondary,
                                  fontSize: 10,
                                )),
                          ],
                        ),
                      ),
                    ),
                  ),
                ),
              );
            }).toList(),
          ),
        ],
      ),
    );
  }

  Widget _menuSection(String title, List<Widget> items) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.only(bottom: AppSpacing.sm, left: 4),
          child: Text(title, style: AppText.label),
        ),
        Container(
          decoration: BoxDecoration(
            color: AppColors.card,
            borderRadius: BorderRadius.circular(AppRadius.lg),
            border: Border.all(color: AppColors.border),
          ),
          child: Column(
            children: List.generate(items.length * 2 - 1, (i) {
              if (i.isOdd) return const Divider(height: 1, indent: 60);
              return items[i ~/ 2];
            }),
          ),
        ),
      ],
    );
  }

  Widget _menuItem(IconData icon, String label, Color color, VoidCallback onTap, {Widget? trailing}) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: AppSpacing.md, vertical: AppSpacing.md),
          child: Row(
            children: [
              Container(
                width: 36, height: 36,
                decoration: BoxDecoration(
                  color: color.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(AppRadius.sm),
                ),
                child: Icon(icon, size: 18, color: color),
              ),
              const SizedBox(width: AppSpacing.md),
              Expanded(child: Text(label, style: AppText.body.copyWith(fontSize: 14))),
              if (trailing != null) trailing else
                Icon(Icons.chevron_right_rounded, color: AppColors.textMuted),
            ],
          ),
        ),
      ),
    );
  }
}
