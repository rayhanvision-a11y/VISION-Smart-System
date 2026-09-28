import 'package:flutter/material.dart';
import '../config/app_config.dart';
import '../services/api_service.dart';
import '../services/storage_service.dart';
import '../services/app_state.dart';
import '../services/background_service.dart';
import '../services/biometric_service.dart';
import '../theme/app_theme.dart';
import '../widgets/common/primary_button.dart';
import 'main_navigation_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({Key? key}) : super(key: key);

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _isLoading = false;
  bool _obscurePassword = true;
  bool _rememberMe = true;
  bool _hasSavedCreds = false;
  bool _isBiometricEnabled = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _loadSaved();
  }

  Future<void> _loadSaved() async {
    final creds = await StorageService.getSavedCredentials();
    final bioEnabled = await StorageService.isBiometricEnabled();
    if (!mounted) return;
    setState(() {
      _rememberMe = creds['remember'] as bool? ?? true;
      final savedEmail = creds['email'] as String? ?? '';
      final savedPass = creds['password'] as String? ?? '';
      if (savedEmail.isNotEmpty && savedPass.isNotEmpty) {
        _emailController.text = savedEmail;
        _passwordController.text = savedPass;
        _hasSavedCreds = true;
      }
      _isBiometricEnabled = bioEnabled && _hasSavedCreds;
    });
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });
    final res = await ApiService.login(
      _emailController.text.trim(),
      _passwordController.text,
    );
    if (!mounted) return;
    setState(() => _isLoading = false);
    if (res['success'] == true) {
      await StorageService.saveCredentials(
        _emailController.text.trim(),
        _passwordController.text,
        _rememberMe,
      );
      try { await BgService.start(); } catch (_) {}
      if (!mounted) return;
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => const MainNavigationScreen()),
      );
    } else {
      setState(() {
        _errorMessage = res['message'] ?? 'Login failed. Check your credentials.';
      });
    }
  }

  Future<void> _biometricLogin() async {
    final creds = await StorageService.getSavedCredentials();
    final savedEmail = (creds['email'] as String? ?? '').isNotEmpty
        ? creds['email'] as String
        : _emailController.text.trim();
    final savedPass = (creds['password'] as String? ?? '').isNotEmpty
        ? creds['password'] as String
        : _passwordController.text;

    if (savedEmail.isEmpty || savedPass.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(AppState.instance.t(
            'Please login once with Email & Password to activate Fingerprint login.',
            'ফিঙ্গারপ্রিন্ট অ্যাক্টিভ করতে প্রথমে একবার ইমেইল ও পাসওয়ার্ড দিয়ে লগইন করুন।',
          )),
          backgroundColor: AppColors.info,
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    _emailController.text = savedEmail;
    _passwordController.text = savedPass;

    final ok = await BiometricService.instance.authenticate(
      context: context,
      reason: AppState.instance.t('Log in using fingerprint', 'ফিঙ্গারপ্রিন্ট দিয়ে লগইন করুন'),
    );
    if (ok && mounted) {
      _submit();
    }
  }

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;

    return Scaffold(
      backgroundColor: AppColors.bg,
      body: SafeArea(
        child: Column(
          children: [
            // Top hero
            Container(
              width: double.infinity,
              padding: const EdgeInsets.fromLTRB(AppSpacing.xl, AppSpacing.md, AppSpacing.xl, 60),
              decoration: BoxDecoration(
                gradient: AppColors.heroGradient,
                borderRadius: const BorderRadius.only(
                  bottomLeft: Radius.circular(40),
                  bottomRight: Radius.circular(40),
                ),
              ),
              child: Stack(
                clipBehavior: Clip.none,
                children: [
                  Positioned(
                    right: -30, top: -10,
                    child: Container(
                      width: 140, height: 140,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        color: Colors.white.withOpacity(0.06),
                      ),
                    ),
                  ),
                  Positioned(
                    left: 40, bottom: -20,
                    child: Container(
                      width: 60, height: 60,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        color: AppColors.accent.withOpacity(0.15),
                      ),
                    ),
                  ),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                            decoration: BoxDecoration(
                              color: Colors.white.withOpacity(0.15),
                              borderRadius: BorderRadius.circular(AppRadius.pill),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                const Icon(Icons.verified_rounded, size: 14, color: AppColors.accent),
                                const SizedBox(width: 4),
                                Text('VISION Portal',
                                    style: AppText.label.copyWith(color: Colors.white, fontSize: 11)),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: AppSpacing.xl),
                      Text(AppState.instance.t('Welcome\nBack 👋', 'স্বাগতম 👋'),
                          style: AppText.displayLg.copyWith(color: Colors.white, fontSize: 32, height: 1.2)),
                      const SizedBox(height: AppSpacing.sm),
                      Text(AppState.instance.t('Sign in to continue managing tickets', 'টিকিট ব্যবস্থাপনা চালিয়ে যেতে সাইন-ইন করুন'),
                          style: AppText.bodySm.copyWith(color: Colors.white.withOpacity(0.85))),
                    ],
                  ),
                ],
              ),
            ),

            // Form card
            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.symmetric(horizontal: AppSpacing.xl),
                child: Transform.translate(
                  offset: const Offset(0, -40),
                  child: Container(
                    padding: const EdgeInsets.all(AppSpacing.xl),
                    decoration: BoxDecoration(
                      color: AppColors.card,
                      borderRadius: BorderRadius.circular(AppRadius.xl),
                      boxShadow: AppShadows.elevated,
                    ),
                    child: Form(
                      key: _formKey,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          Text(AppState.instance.t('Sign In', 'সাইন-ইন'), style: AppText.h2),
                          const SizedBox(height: 4),
                          Text(AppState.instance.t('Enter your credentials', 'আপনার তথ্য প্রবেশ করুন'),
                              style: AppText.bodySm),
                          const SizedBox(height: AppSpacing.xl),

                          if (_errorMessage != null) ...[
                            Container(
                              padding: const EdgeInsets.all(AppSpacing.md),
                              decoration: BoxDecoration(
                                color: AppColors.danger.withOpacity(0.08),
                                borderRadius: BorderRadius.circular(AppRadius.md),
                                border: Border.all(color: AppColors.danger.withOpacity(0.3)),
                              ),
                              child: Row(
                                children: [
                                  const Icon(Icons.error_outline_rounded, color: AppColors.danger, size: 18),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: Text(_errorMessage!,
                                        style: AppText.bodySm.copyWith(color: AppColors.danger)),
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(height: AppSpacing.md),
                          ],

                          Text(AppState.instance.t('Email Address', 'ইমেইল'), style: AppText.label),
                          const SizedBox(height: 6),
                          TextFormField(
                            controller: _emailController,
                            keyboardType: TextInputType.emailAddress,
                            decoration: const InputDecoration(
                              hintText: 'you@example.com',
                              prefixIcon: Icon(Icons.email_outlined, size: 20),
                            ),
                            validator: (v) => (v == null || !v.contains('@')) ? 'Valid email required' : null,
                          ),
                          const SizedBox(height: AppSpacing.md),

                          Text(AppState.instance.t('Password', 'পাসওয়ার্ড'), style: AppText.label),
                          const SizedBox(height: 6),
                          TextFormField(
                            controller: _passwordController,
                            obscureText: _obscurePassword,
                            decoration: InputDecoration(
                              hintText: 'Your password',
                              prefixIcon: const Icon(Icons.lock_outline_rounded, size: 20),
                              suffixIcon: IconButton(
                                icon: Icon(
                                  _obscurePassword ? Icons.visibility_off_outlined : Icons.visibility_outlined,
                                  size: 20,
                                ),
                                onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                              ),
                            ),
                            validator: (v) => (v == null || v.isEmpty) ? 'Password is required' : null,
                          ),

                          const SizedBox(height: AppSpacing.sm),
                          Row(
                            children: [
                              SizedBox(
                                width: 24,
                                height: 24,
                                child: Checkbox(
                                  value: _rememberMe,
                                  activeColor: AppColors.primary,
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(4)),
                                  onChanged: (v) => setState(() => _rememberMe = v ?? true),
                                ),
                              ),
                              const SizedBox(width: 8),
                              Text(
                                AppState.instance.t('Remember Me', 'তথ্য সংরক্ষণ রাখুন'),
                                style: AppText.bodySm.copyWith(fontSize: 13),
                              ),
                            ],
                          ),

                          const SizedBox(height: AppSpacing.lg),
                          Row(
                            children: [
                              Expanded(
                                child: PrimaryButton(
                                  label: AppState.instance.t('Sign In', 'সাইন-ইন'),
                                  icon: Icons.login_rounded,
                                  kind: PrimaryButtonKind.primary,
                                  loading: _isLoading,
                                  onPressed: _submit,
                                ),
                              ),
                              if (_isBiometricEnabled) ...[
                                const SizedBox(width: 10),
                                Tooltip(
                                  message: AppState.instance.t('Biometric / Fingerprint Login', 'ফিঙ্গারপ্রিন্ট লগইন'),
                                  child: Material(
                                    color: AppColors.primary.withOpacity(0.1),
                                    borderRadius: BorderRadius.circular(AppRadius.lg),
                                    child: InkWell(
                                      onTap: _biometricLogin,
                                      borderRadius: BorderRadius.circular(AppRadius.lg),
                                      child: Container(
                                        width: 52,
                                        height: 52,
                                        decoration: BoxDecoration(
                                          borderRadius: BorderRadius.circular(AppRadius.lg),
                                          border: Border.all(color: AppColors.primary, width: 1.5),
                                        ),
                                        child: const Icon(
                                          Icons.fingerprint_rounded,
                                          color: AppColors.primary,
                                          size: 32,
                                        ),
                                      ),
                                    ),
                                  ),
                                ),
                              ],
                            ],
                          ),
                          if (_isBiometricEnabled) ...[
                            const SizedBox(height: AppSpacing.md),
                            Center(
                              child: InkWell(
                                onTap: _biometricLogin,
                                borderRadius: BorderRadius.circular(AppRadius.md),
                                child: Padding(
                                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                  child: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      const Icon(Icons.fingerprint_rounded, size: 20, color: AppColors.primary),
                                      const SizedBox(width: 6),
                                      Text(
                                        AppState.instance.t('Quick Biometric / Fingerprint Login', 'ফিঙ্গারপ্রিন্ট দিয়ে দ্রুত লগইন করুন'),
                                        style: AppText.bodySm.copyWith(color: AppColors.primary, fontWeight: FontWeight.w600),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
                  ),
                ),
              ),
            ),

            Padding(
              padding: const EdgeInsets.only(bottom: 12),
              child: Text('VISION Smart System v${size.width < 400 ? "1.0" : "1.0.0"}',
                  style: AppText.label.copyWith(color: AppColors.textMuted)),
            ),
          ],
        ),
      ),
    );
  }
}

