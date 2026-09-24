import 'package:flutter/material.dart';
import '../config/app_config.dart';
import '../services/api_service.dart';
import '../services/storage_service.dart';
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
  String? _errorMessage;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _showServerSettings() async {
    final currentUrl = await ApiService.getBaseUrl();
    final urlController = TextEditingController(text: currentUrl);
    if (!mounted) return;

    await showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.lg)),
        title: Text('Server URL', style: AppText.h3),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Enter your Laravel API base URL', style: AppText.caption),
            const SizedBox(height: AppSpacing.md),
            TextField(
              controller: urlController,
              decoration: const InputDecoration(hintText: 'https://your-domain.com/api'),
              keyboardType: TextInputType.url,
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () async {
              final messenger = ScaffoldMessenger.of(ctx);
              await StorageService.setServerUrl(AppConfig.defaultApiBaseUrl);
              if (!ctx.mounted) return;
              Navigator.pop(ctx);
              messenger.showSnackBar(const SnackBar(content: Text('Reset to default')));
            },
            child: const Text('Reset'),
          ),
          ElevatedButton(
            onPressed: () async {
              final u = urlController.text.trim();
              if (u.isEmpty) return;
              final messenger = ScaffoldMessenger.of(ctx);
              await StorageService.setServerUrl(u);
              if (!ctx.mounted) return;
              Navigator.pop(ctx);
              messenger.showSnackBar(SnackBar(content: Text('Saved: $u')));
            },
            child: const Text('Save'),
          ),
        ],
      ),
    );
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
                          IconButton(
                            icon: const Icon(Icons.settings_outlined, color: Colors.white),
                            onPressed: _showServerSettings,
                          ),
                        ],
                      ),
                      const SizedBox(height: AppSpacing.xl),
                      Text('Welcome\nBack 👋',
                          style: AppText.displayLg.copyWith(color: Colors.white, fontSize: 32, height: 1.2)),
                      const SizedBox(height: AppSpacing.sm),
                      Text('Sign in to continue managing tickets',
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
                          Text('Sign In', style: AppText.h2),
                          const SizedBox(height: 4),
                          Text('Enter your credentials',
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

                          Text('Email Address', style: AppText.label),
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

                          Text('Password', style: AppText.label),
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

                          const SizedBox(height: AppSpacing.xl),
                          PrimaryButton(
                            label: 'Sign In',
                            icon: Icons.login_rounded,
                            kind: PrimaryButtonKind.primary,
                            loading: _isLoading,
                            onPressed: _submit,
                          ),
                          const SizedBox(height: AppSpacing.md),
                          Center(
                            child: TextButton.icon(
                              onPressed: _showServerSettings,
                              icon: const Icon(Icons.dns_outlined, size: 16, color: AppColors.textMuted),
                              label: Text('Server settings',
                                  style: AppText.caption.copyWith(color: AppColors.textMuted)),
                            ),
                          ),
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
