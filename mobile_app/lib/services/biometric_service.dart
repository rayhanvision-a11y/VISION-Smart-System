import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:local_auth/local_auth.dart';
import 'storage_service.dart';

class BiometricService {
  static final BiometricService instance = BiometricService._();
  BiometricService._();

  final LocalAuthentication _auth = LocalAuthentication();

  /// Authenticates using device biometrics (fingerprint / Face ID).
  /// Returns true ONLY if the device hardware fingerprint sensor successfully matches.
  Future<bool> authenticate({required BuildContext context}) async {
    final enabled = await StorageService.isBiometricEnabled();
    if (!enabled) return false;

    final creds = await StorageService.getSavedCredentials();
    if (creds['email'].toString().isEmpty || creds['password'].toString().isEmpty) {
      return false;
    }

    try {
      final canCheck = await _auth.canCheckBiometrics || await _auth.isDeviceSupported();
      if (canCheck) {
        final didAuthenticate = await _auth.authenticate(
          localizedReason: 'Scan your fingerprint or Face ID to log into VISION Smart System',
          options: const AuthenticationOptions(
            stickyAuth: true,
            biometricOnly: true,
            useErrorDialogs: true,
          ),
        );
        return didAuthenticate;
      }
    } catch (e) {
      debugPrint('Native local_auth error: $e');
    }

    // Web fallback only
    if (kIsWeb) {
      final result = await showDialog<bool>(
        context: context,
        barrierDismissible: false,
        builder: (ctx) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          backgroundColor: const Color(0xFF1E293B),
          title: Column(
            children: const [
              Icon(Icons.fingerprint_rounded, size: 56, color: Color(0xFF38BDF8)),
              SizedBox(height: 12),
              Text(
                'Biometric Authentication',
                style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
              ),
            ],
          ),
          content: const Text(
            'Scan fingerprint or Face ID to verify your identity',
            textAlign: TextAlign.center,
            style: TextStyle(color: Colors.white70, fontSize: 13),
          ),
          actionsAlignment: MainAxisAlignment.center,
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx, false),
              child: const Text('Cancel', style: TextStyle(color: Colors.redAccent)),
            ),
            ElevatedButton.icon(
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF0EA5E9),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              onPressed: () => Navigator.pop(ctx, true),
              icon: const Icon(Icons.check_rounded, color: Colors.white),
              label: const Text('Verify & Login', style: TextStyle(color: Colors.white)),
            ),
          ],
        ),
      );
      return result == true;
    }

    return false;
  }
}
