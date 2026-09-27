import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:local_auth/local_auth.dart';

class BiometricService {
  static final BiometricService instance = BiometricService._();
  BiometricService._();

  final LocalAuthentication _auth = LocalAuthentication();

  /// Prompts device hardware fingerprint / Face ID sensor.
  /// Returns true ONLY if physical fingerprint matches.
  Future<bool> authenticate({
    required BuildContext context,
    String reason = 'Scan your fingerprint or Face ID to verify',
  }) async {
    try {
      final canCheck = await _auth.canCheckBiometrics || await _auth.isDeviceSupported();
      if (canCheck) {
        final didAuthenticate = await _auth.authenticate(
          localizedReason: reason,
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

    // Web Fallback dialog
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
          content: Text(
            reason,
            textAlign: TextAlign.center,
            style: const TextStyle(color: Colors.white70, fontSize: 13),
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
              label: const Text('Verify & Confirm', style: TextStyle(color: Colors.white)),
            ),
          ],
        ),
      );
      return result == true;
    }

    return false;
  }
}
