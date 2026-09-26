import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'package:url_launcher/url_launcher.dart';
import '../services/api_service.dart';
import '../services/app_state.dart';
import '../theme/app_theme.dart';
import '../widgets/common/empty_state.dart';
import '../widgets/common/skeleton.dart';

class LiveMapScreen extends StatefulWidget {
  const LiveMapScreen({Key? key}) : super(key: key);

  @override
  State<LiveMapScreen> createState() => _LiveMapScreenState();
}

class _LiveMapScreenState extends State<LiveMapScreen> {
  final _map = MapController();
  Timer? _refreshTimer;
  bool _isLoading = true;
  List<Map<String, dynamic>> _locations = [];
  String _roleFilter = 'technician';

  @override
  void initState() {
    super.initState();
    _load();
    _refreshTimer = Timer.periodic(const Duration(seconds: 30), (_) => _load());
  }

  @override
  void dispose() {
    _refreshTimer?.cancel();
    super.dispose();
  }

  Future<void> _load() async {
    final data = await ApiService.getAllLocations(role: _roleFilter == 'all' ? null : _roleFilter);
    if (!mounted) return;
    setState(() {
      _locations = data;
      _isLoading = false;
    });
    if (data.isNotEmpty) _fitBounds();
  }

  void _fitBounds() {
    if (_locations.length < 2) {
      if (_locations.length == 1) {
        _map.move(
          LatLng(_locations.first['latitude'], _locations.first['longitude']),
          15,
        );
      }
      return;
    }
    final points = _locations
        .map((l) => LatLng(l['latitude'] as double, l['longitude'] as double))
        .toList();
    final bounds = LatLngBounds.fromPoints(points);
    _map.fitCamera(CameraFit.bounds(bounds: bounds, padding: const EdgeInsets.all(60)));
  }

  Color _statusColor(String s) {
    switch (s) {
      case 'online': return AppColors.success;
      case 'idle': return AppColors.warning;
      case 'offline': return AppColors.textMuted;
    }
    return AppColors.textMuted;
  }

  IconData _statusIcon(String s) {
    switch (s) {
      case 'online': return Icons.circle;
      case 'idle': return Icons.access_time_rounded;
      case 'offline': return Icons.wifi_off_rounded;
    }
    return Icons.help_outline_rounded;
  }

  void _showDetail(Map<String, dynamic> loc) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (ctx) => Container(
        padding: const EdgeInsets.all(AppSpacing.lg),
        decoration: BoxDecoration(
          color: AppColors.card,
          borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
        ),
        child: SafeArea(
          top: false,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  CircleAvatar(
                    radius: 24,
                    backgroundColor: AppColors.primary.withOpacity(0.12),
                    child: Text(
                      (loc['name']?.toString() ?? '?')[0].toUpperCase(),
                      style: AppText.h2.copyWith(color: AppColors.primary),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(loc['name']?.toString() ?? '—', style: AppText.h3),
                        Row(
                          children: [
                            Icon(_statusIcon(loc['status'] as String), size: 12, color: _statusColor(loc['status'] as String)),
                            const SizedBox(width: 4),
                            Text((loc['status'] as String).toUpperCase(),
                                style: AppText.label.copyWith(color: _statusColor(loc['status'] as String))),
                            const SizedBox(width: 8),
                            Text('${loc['role']}',
                                style: AppText.label.copyWith(color: AppColors.textMuted)),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: AppSpacing.lg),
              _detailRow(Icons.circle, '${loc['active_ticket_count']} ${AppState.instance.t('active tickets', 'সক্রিয় টিকিট')}', AppColors.warning),
              _detailRow(Icons.schedule_rounded,
                  loc['last_seen_minutes'] == null
                      ? AppState.instance.t('Never seen', 'কখনো দেখা যায়নি')
                      : '${AppState.instance.t('Last seen', 'শেষ দেখা')} ${loc['last_seen_minutes']} min ${AppState.instance.t('ago', 'আগে')}',
                  AppColors.info),
              if (loc['battery_level'] != null)
                _detailRow(Icons.battery_full_rounded, '${AppState.instance.t('Battery', 'ব্যাটারি')}: ${loc['battery_level']}%',
                    (loc['battery_level'] as int) < 20 ? AppColors.danger : AppColors.success),
              if (loc['accuracy_meters'] != null)
                _detailRow(Icons.gps_fixed_rounded, '${AppState.instance.t('Accuracy', 'নির্ভুলতা')}: ±${loc['accuracy_meters']}m', AppColors.textSecondary),
              const SizedBox(height: AppSpacing.md),
              Row(
                children: [
                  if (loc['phone'] != null && (loc['phone'] as String).isNotEmpty)
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: () => launchUrl(Uri.parse('tel:${loc['phone']}')),
                        icon: const Icon(Icons.phone_rounded),
                        label: Text(AppState.instance.t('Call', 'ফোন')),
                        style: ElevatedButton.styleFrom(backgroundColor: AppColors.success),
                      ),
                    ),
                  if (loc['phone'] != null && (loc['phone'] as String).isNotEmpty)
                    const SizedBox(width: 8),
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: () {
                        Navigator.pop(ctx);
                        _map.move(LatLng(loc['latitude'] as double, loc['longitude'] as double), 17);
                      },
                      icon: const Icon(Icons.navigation_rounded),
                      label: Text(AppState.instance.t('Zoom to', 'জুম')),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _detailRow(IconData icon, String text, Color color) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        children: [
          Icon(icon, color: color, size: 16),
          const SizedBox(width: 10),
          Expanded(child: Text(text, style: AppText.bodySm)),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(AppState.instance.t('Live Map', 'লাইভ ম্যাপ'), style: AppText.h2),
            Text('${_locations.length} ${AppState.instance.t('staff tracked', 'কর্মী ট্র্যাক করা হচ্ছে')}',
                style: AppText.label.copyWith(color: AppColors.textMuted)),
          ],
        ),
        actions: [
          PopupMenuButton<String>(
            icon: const Icon(Icons.filter_list_rounded),
            initialValue: _roleFilter,
            onSelected: (v) {
              setState(() => _roleFilter = v);
              _load();
            },
            itemBuilder: (_) => const [
              PopupMenuItem(value: 'technician', child: Text('👷 Technicians')),
              PopupMenuItem(value: 'noc', child: Text('🔧 NOC')),
              PopupMenuItem(value: 'supervisor', child: Text('👀 Supervisors')),
              PopupMenuItem(value: 'all', child: Text('👥 All Staff')),
            ],
          ),
          IconButton(icon: const Icon(Icons.refresh_rounded), onPressed: _load),
        ],
      ),
      body: _isLoading
          ? Center(child: const CircularProgressIndicator(color: AppColors.primary))
          : _locations.isEmpty
              ? EmptyState(
                  icon: Icons.location_off_rounded,
                  title: AppState.instance.t('No live locations', 'কোনো লাইভ লোকেশন নেই'),
                  subtitle: AppState.instance.t('Technicians must enable location sharing in Profile', 'টেকনিশিয়ানদের প্রোফাইল থেকে লোকেশন শেয়ার চালু করতে হবে'),
                )
              : Stack(
                  children: [
                    FlutterMap(
                      mapController: _map,
                      options: MapOptions(
                        initialCenter: LatLng(
                          _locations.first['latitude'] as double,
                          _locations.first['longitude'] as double,
                        ),
                        initialZoom: 12,
                      ),
                      children: [
                        TileLayer(
                          urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                          userAgentPackageName: 'com.visiontech.smartsystem',
                        ),
                        MarkerLayer(
                          markers: _locations.map((l) {
                            final status = l['status'] as String;
                            final color = _statusColor(status);
                            return Marker(
                              width: 44, height: 44,
                              point: LatLng(l['latitude'] as double, l['longitude'] as double),
                              child: GestureDetector(
                                onTap: () => _showDetail(l),
                                child: Container(
                                  decoration: BoxDecoration(
                                    color: color.withOpacity(0.25),
                                    shape: BoxShape.circle,
                                  ),
                                  padding: const EdgeInsets.all(4),
                                  child: Container(
                                    decoration: BoxDecoration(
                                      color: color,
                                      shape: BoxShape.circle,
                                      border: Border.all(color: Colors.white, width: 2),
                                      boxShadow: [
                                        BoxShadow(color: Colors.black26, blurRadius: 4, offset: const Offset(0, 2)),
                                      ],
                                    ),
                                    child: Center(
                                      child: Text(
                                        (l['name']?.toString() ?? '?')[0].toUpperCase(),
                                        style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 12),
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
                    // Legend
                    Positioned(
                      left: 10, bottom: 10,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                        decoration: BoxDecoration(
                          color: AppColors.card.withOpacity(0.95),
                          borderRadius: BorderRadius.circular(AppRadius.md),
                          boxShadow: AppShadows.card,
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            _legendDot(AppColors.success, 'Online'),
                            const SizedBox(width: 8),
                            _legendDot(AppColors.warning, 'Idle'),
                            const SizedBox(width: 8),
                            _legendDot(AppColors.textMuted, 'Offline'),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
    );
  }

  Widget _legendDot(Color c, String label) {
    return Row(
      children: [
        Container(width: 8, height: 8, decoration: BoxDecoration(color: c, shape: BoxShape.circle)),
        const SizedBox(width: 4),
        Text(label, style: AppText.label.copyWith(fontSize: 9)),
      ],
    );
  }
}
