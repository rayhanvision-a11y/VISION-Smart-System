import 'package:flutter/material.dart';
import '../models/user.dart';
import '../services/storage_service.dart';
import '../services/app_state.dart';
import '../services/location_service.dart';
import '../services/offline_service.dart';
import '../services/push_service.dart';
import '../theme/app_theme.dart';
import 'dashboard_screen.dart';
import 'ticket_list_screen.dart';
import 'roster_screen.dart';
import 'profile_screen.dart';
import 'create_ticket_screen.dart';

class MainNavigationScreen extends StatefulWidget {
  final int initialIndex;
  const MainNavigationScreen({Key? key, this.initialIndex = 0}) : super(key: key);

  @override
  State<MainNavigationScreen> createState() => _MainNavigationScreenState();
}

class _MainNavigationScreenState extends State<MainNavigationScreen> {
  int _currentIndex = 0;
  UserModel? _currentUser;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _currentIndex = widget.initialIndex;
    OfflineService.instance.init();
    _loadUser();
  }

  @override
  void dispose() {
    OfflineService.instance.dispose();
    super.dispose();
  }

  Future<void> _loadUser() async {
    final user = await StorageService.getUser();
    PushService.instance.sendTokenToServer();
    if (mounted) {
      setState(() {
        _currentUser = user;
        _isLoading = false;
      });
    }
    // Auto-start location sharing for logged-in user
    if (user != null) {
      LocationService.instance.start();
    }
  }

  Future<void> _openCreate() async {
    await Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const CreateTicketScreen()),
    );
  }

  bool get _isTechnician => _currentUser?.role.toLowerCase() == 'technician';

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    final pages = <Widget>[
      DashboardScreen(onSwitchToTickets: () => setState(() => _currentIndex = 1)),
      const TicketListScreen(),
      const SizedBox.shrink(),
      const RosterScreen(),
      const ProfileScreen(),
    ];

    return Scaffold(
      extendBody: true,
      body: Column(
        children: [
          // Offline Banner
          ValueListenableBuilder<bool>(
            valueListenable: OfflineService.instance.isOnlineNotifier,
            builder: (_, isOnline, __) {
              if (isOnline) {
                return ValueListenableBuilder<int>(
                  valueListenable: OfflineService.instance.pendingCountNotifier,
                  builder: (_, count, __) {
                    if (count == 0) return const SizedBox.shrink();
                    return Container(
                      width: double.infinity,
                      color: AppColors.info,
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
                      child: SafeArea(
                        bottom: false,
                        child: Row(
                          children: [
                            const Icon(Icons.sync_rounded, color: Colors.white, size: 16),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                '${AppState.instance.t('Offline items pending sync', 'অফলাইন আইটেম সিঙ্ক বাকি')}: $count',
                                style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600),
                              ),
                            ),
                            TextButton(
                              style: TextButton.styleFrom(padding: EdgeInsets.zero, minimumSize: Size.zero),
                              onPressed: () async {
                                final done = await OfflineService.instance.syncPending();
                                if (mounted && done > 0) {
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    SnackBar(
                                      content: Text('Synced $done pending offline actions!'),
                                      backgroundColor: AppColors.success,
                                    ),
                                  );
                                }
                              },
                              child: const Text('SYNC NOW', style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold)),
                            ),
                          ],
                        ),
                      ),
                    );
                  },
                );
              }
              return Container(
                width: double.infinity,
                color: Colors.amber.shade800,
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
                child: SafeArea(
                  bottom: false,
                  child: Row(
                    children: [
                      const Icon(Icons.wifi_off_rounded, color: Colors.white, size: 16),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          AppState.instance.t('Offline Mode - Changes will auto-sync when online', 'অফলাইন মোড - সংযোগ পেলে স্বয়ংক্রিয় সিঙ্ক হবে'),
                          style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w600),
                        ),
                      ),
                    ],
                  ),
                ),
              );
            },
          ),
          Expanded(
            child: IndexedStack(index: _currentIndex, children: pages),
          ),
        ],
      ),
      floatingActionButton: _isTechnician
          ? null
          : FloatingActionButton(
              heroTag: 'main_nav_fab',
              onPressed: _openCreate,
              backgroundColor: AppColors.accent,
              elevation: 4,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.lg)),
              child: const Icon(Icons.add_rounded, color: Colors.white, size: 28),
            ),
      floatingActionButtonLocation: FloatingActionButtonLocation.centerDocked,
      bottomNavigationBar: _buildNav(),
    );
  }

  Widget _buildNav() {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.card,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.06),
            blurRadius: 16,
            offset: const Offset(0, -2),
          ),
        ],
      ),
      child: SafeArea(
        top: false,
        child: SizedBox(
          height: 68,
          child: Row(
            children: [
              _navItem(0, Icons.home_outlined, Icons.home_rounded, AppState.instance.t('Home', 'হোম')),
              _navItem(1, Icons.confirmation_number_outlined, Icons.confirmation_number_rounded, AppState.instance.t('Tickets', 'টিকিট')),
              if (!_isTechnician) const SizedBox(width: 60),
              if (!_isTechnician)
                _navItem(3, Icons.groups_2_outlined, Icons.groups_2_rounded, AppState.instance.t('Team', 'টিম')),
              _navItem(4, Icons.person_outline_rounded, Icons.person_rounded, AppState.instance.t('Profile', 'প্রোফাইল')),
            ],
          ),
        ),
      ),
    );
  }

  Widget _navItem(int index, IconData icon, IconData activeIcon, String label) {
    final selected = _currentIndex == index;
    final color = selected ? AppColors.primary : AppColors.textMuted;
    return Expanded(
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: () => setState(() => _currentIndex = index),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(selected ? activeIcon : icon, color: color, size: 24),
              const SizedBox(height: 4),
              Text(label,
                  style: AppText.label.copyWith(
                    color: color,
                    fontSize: 10,
                    fontWeight: selected ? FontWeight.w700 : FontWeight.w600,
                  )),
              if (selected)
                Container(
                  margin: const EdgeInsets.only(top: 4),
                  width: 16, height: 3,
                  decoration: BoxDecoration(
                    color: AppColors.accent,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }
}
