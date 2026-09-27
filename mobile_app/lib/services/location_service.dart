import 'dart:async';
import 'package:flutter/foundation.dart';
import 'package:geolocator/geolocator.dart';
import 'api_service.dart';
import 'storage_service.dart';

/// Periodically pushes the current user's GPS location to backend.
class LocationService {
  static final LocationService instance = LocationService._();
  LocationService._();

  Timer? _timer;
  bool _running = false;
  bool _sharingEnabled = true;

  static const _defaultInterval = Duration(seconds: 60);

  bool get isRunning => _running;
  bool get isSharing => _sharingEnabled;

  Future<bool> setSharing(bool on) async {
    _sharingEnabled = on;
    if (on) {
      final started = await start();
      try {
        await ApiService.toggleLocationSharing(on);
      } catch (_) {}
      return started;
    } else {
      stop();
      try {
        await ApiService.toggleLocationSharing(on);
      } catch (_) {}
      return true;
    }
  }

  Future<bool> start({Duration interval = _defaultInterval}) async {
    if (_running) return true;
    final user = await StorageService.getUser();
    if (user == null) return false;

    final hasPermission = await _ensurePermission();
    if (!hasPermission) return false;

    _running = true;
    // Push immediately
    _pushOnce();
    _timer = Timer.periodic(interval, (_) => _pushOnce());
    return true;
  }

  void stop() {
    _timer?.cancel();
    _timer = null;
    _running = false;
  }

  Future<bool> requestPermissionExplicitly() async {
    return await _ensurePermission();
  }

  Future<bool> _ensurePermission() async {
    try {
      final serviceOn = await Geolocator.isLocationServiceEnabled();
      if (!serviceOn) {
        debugPrint('Location service is disabled on device');
        // Prompt user to open location settings if service is disabled
        await Geolocator.openLocationSettings();
        return false;
      }

      var perm = await Geolocator.checkPermission();
      if (perm == LocationPermission.denied) {
        perm = await Geolocator.requestPermission();
      }

      if (perm == LocationPermission.deniedForever) {
        debugPrint('Location permission is denied forever. Opening app settings.');
        await Geolocator.openAppSettings();
        return false;
      }

      if (perm == LocationPermission.denied) {
        debugPrint('Location permission denied by user.');
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
      Position? pos;
      try {
        pos = await Geolocator.getCurrentPosition(
          desiredAccuracy: LocationAccuracy.high,
          timeLimit: const Duration(seconds: 15),
        );
      } catch (e) {
        debugPrint('getCurrentPosition failed or timed out: $e. Falling back to last known position.');
        pos = await Geolocator.getLastKnownPosition();
      }

      if (pos == null) {
        debugPrint('Location is null, cannot update.');
        return;
      }

      await ApiService.updateLocation(
        lat: pos.latitude,
        lng: pos.longitude,
        accuracy: pos.accuracy.round(),
        speed: pos.speed,
      );
      debugPrint('LocationService successfully pushed ${pos.latitude},${pos.longitude}');
    } catch (e) {
      debugPrint('LocationService push failed: $e');
    }
  }
}

