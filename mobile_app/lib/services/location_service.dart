import 'dart:async';
import 'package:flutter/foundation.dart';
import 'package:geolocator/geolocator.dart';
import 'api_service.dart';
import 'storage_service.dart';

/// Periodically pushes the current user's GPS location to backend.
/// Runs only while the app is in the foreground (background service is
/// platform-specific and deferred to a follow-up batch).
class LocationService {
  static final LocationService instance = LocationService._();
  LocationService._();

  Timer? _timer;
  bool _running = false;
  bool _sharingEnabled = true;

  static const _defaultInterval = Duration(seconds: 60);

  bool get isRunning => _running;
  bool get isSharing => _sharingEnabled;

  Future<void> setSharing(bool on) async {
    _sharingEnabled = on;
    if (on) {
      await start();
    } else {
      stop();
    }
    try {
      await ApiService.toggleLocationSharing(on);
    } catch (_) {}
  }

  Future<void> start({Duration interval = _defaultInterval}) async {
    if (kIsWeb) return; // browser geolocation deferred
    if (_running) return;
    final user = await StorageService.getUser();
    if (user == null) return;
    // Only technicians (and staff who opt-in) share by default; supervisors don't.
    final role = user.role.toLowerCase();
    if (role != 'technician' && !_sharingEnabled) return;

    if (! await _ensurePermission()) return;

    _running = true;
    // Push immediately
    _pushOnce();
    _timer = Timer.periodic(interval, (_) => _pushOnce());
  }

  void stop() {
    _timer?.cancel();
    _timer = null;
    _running = false;
  }

  Future<bool> _ensurePermission() async {
    try {
      final serviceOn = await Geolocator.isLocationServiceEnabled();
      if (!serviceOn) return false;
      var perm = await Geolocator.checkPermission();
      if (perm == LocationPermission.denied) {
        perm = await Geolocator.requestPermission();
      }
      if (perm == LocationPermission.deniedForever || perm == LocationPermission.denied) {
        return false;
      }
      return true;
    } catch (e) {
      debugPrint('Location permission error: $e');
      return false;
    }
  }

  Future<void> _pushOnce() async {
    if (!_sharingEnabled) return;
    try {
      final pos = await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.high,
        timeLimit: const Duration(seconds: 20),
      );
      await ApiService.updateLocation(
        lat: pos.latitude,
        lng: pos.longitude,
        accuracy: pos.accuracy.round(),
        speed: pos.speed,
      );
      debugPrint('LocationService pushed ${pos.latitude},${pos.longitude}');
    } catch (e) {
      debugPrint('LocationService push failed: $e');
    }
  }
}
