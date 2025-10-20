import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/app_colors.dart';
import '../../providers/auth_provider.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('الملف الشخصي'),
        actions: [
          IconButton(
            icon: const Icon(Icons.edit),
            onPressed: () {
              Navigator.pushNamed(context, '/edit-profile');
            },
          ),
        ],
      ),
      body: Consumer<AuthProvider>(
        builder: (context, authProvider, child) {
          final user = authProvider.user;

          if (user == null) {
            return const Center(child: Text('لم يتم تسجيل الدخول'));
          }

          return SingleChildScrollView(
            child: Column(
              children: [
                // Header with avatar
                Container(
                  width: double.infinity,
                  decoration: BoxDecoration(
                    gradient: AppColors.primaryGradient,
                  ),
                  child: Column(
                    children: [
                      const SizedBox(height: 24),
                      CircleAvatar(
                        radius: 60,
                        backgroundColor: Colors.white,
                        child: user.avatar != null
                            ? ClipOval(
                                child: Image.network(
                                  user.avatar!,
                                  width: 120,
                                  height: 120,
                                  fit: BoxFit.cover,
                                  errorBuilder: (context, error, stackTrace) {
                                    return const Icon(
                                      Icons.person,
                                      size: 60,
                                      color: AppColors.primary,
                                    );
                                  },
                                ),
                              )
                            : const Icon(
                                Icons.person,
                                size: 60,
                                color: AppColors.primary,
                              ),
                      ),
                      const SizedBox(height: 16),
                      Text(
                        user.name,
                        style: const TextStyle(
                          fontSize: 24,
                          fontWeight: FontWeight.bold,
                          color: Colors.white,
                        ),
                      ),
                      const SizedBox(height: 8),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 16,
                          vertical: 6,
                        ),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.2),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Text(
                          user.role == 'doctor' ? 'طبيب' : 'مريض',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 14,
                          ),
                        ),
                      ),
                      const SizedBox(height: 24),
                    ],
                  ),
                ),

                const SizedBox(height: 16),

                // Personal Information
                _buildSection(
                  title: 'المعلومات الشخصية',
                  children: [
                    _buildInfoTile(
                      icon: Icons.email,
                      title: 'البريد الإلكتروني',
                      value: user.email,
                    ),
                    if (user.phone != null)
                      _buildInfoTile(
                        icon: Icons.phone,
                        title: 'رقم الهاتف',
                        value: user.phone!,
                      ),
                    if (user.dateOfBirth != null)
                      _buildInfoTile(
                        icon: Icons.cake,
                        title: 'تاريخ الميلاد',
                        value: _formatDate(user.dateOfBirth!),
                      ),
                    if (user.gender != null)
                      _buildInfoTile(
                        icon: Icons.person_outline,
                        title: 'الجنس',
                        value: user.gender == 'male' ? 'ذكر' : 'أنثى',
                      ),
                  ],
                ),

                const SizedBox(height: 16),

                // Settings
                _buildSection(
                  title: 'الإعدادات',
                  children: [
                    _buildActionTile(
                      icon: Icons.lock_outline,
                      title: 'تغيير كلمة المرور',
                      onTap: () {
                        Navigator.pushNamed(context, '/change-password');
                      },
                    ),
                    _buildActionTile(
                      icon: Icons.notifications_outlined,
                      title: 'الإشعارات',
                      onTap: () {
                        Navigator.pushNamed(context, '/notifications');
                      },
                    ),
                    _buildActionTile(
                      icon: Icons.language,
                      title: 'اللغة',
                      trailing: const Text('العربية'),
                      onTap: () {
                        // Language settings
                      },
                    ),
                  ],
                ),

                const SizedBox(height: 16),

                // About
                _buildSection(
                  title: 'حول',
                  children: [
                    _buildActionTile(
                      icon: Icons.info_outline,
                      title: 'عن التطبيق',
                      onTap: () {
                        Navigator.pushNamed(context, '/about');
                      },
                    ),
                    _buildActionTile(
                      icon: Icons.privacy_tip_outlined,
                      title: 'سياسة الخصوصية',
                      onTap: () {
                        Navigator.pushNamed(context, '/privacy');
                      },
                    ),
                    _buildActionTile(
                      icon: Icons.description_outlined,
                      title: 'الشروط والأحكام',
                      onTap: () {
                        Navigator.pushNamed(context, '/terms');
                      },
                    ),
                  ],
                ),

                const SizedBox(height: 16),

                // Logout
                Padding(
                  padding: const EdgeInsets.all(16),
                  child: SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      onPressed: () async {
                        final confirmed = await showDialog<bool>(
                          context: context,
                          builder: (context) => AlertDialog(
                            title: const Text('تسجيل الخروج'),
                            content: const Text('هل أنت متأكد من تسجيل الخروج؟'),
                            actions: [
                              TextButton(
                                onPressed: () => Navigator.of(context).pop(false),
                                child: const Text('إلغاء'),
                              ),
                              TextButton(
                                onPressed: () => Navigator.of(context).pop(true),
                                style: TextButton.styleFrom(
                                  foregroundColor: AppColors.error,
                                ),
                                child: const Text('تسجيل الخروج'),
                              ),
                            ],
                          ),
                        );

                        if (confirmed == true && context.mounted) {
                          await authProvider.logout();
                          if (context.mounted) {
                            Navigator.of(context).pushNamedAndRemoveUntil(
                              '/login',
                              (route) => false,
                            );
                          }
                        }
                      },
                      icon: const Icon(Icons.logout),
                      label: const Text('تسجيل الخروج'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.error,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 16),
                      ),
                    ),
                  ),
                ),

                const SizedBox(height: 16),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildSection({
    required String title,
    required List<Widget> children,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          child: Text(
            title,
            style: const TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: AppColors.textSecondary,
            ),
          ),
        ),
        Card(
          margin: const EdgeInsets.symmetric(horizontal: 16),
          child: Column(children: children),
        ),
      ],
    );
  }

  Widget _buildInfoTile({
    required IconData icon,
    required String title,
    required String value,
  }) {
    return ListTile(
      leading: Icon(icon, color: AppColors.primary),
      title: Text(title, style: const TextStyle(fontSize: 14)),
      subtitle: Text(
        value,
        style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w500),
      ),
    );
  }

  Widget _buildActionTile({
    required IconData icon,
    required String title,
    Widget? trailing,
    required VoidCallback onTap,
  }) {
    return ListTile(
      leading: Icon(icon, color: AppColors.primary),
      title: Text(title),
      trailing: trailing ?? const Icon(Icons.chevron_right),
      onTap: onTap,
    );
  }

  String _formatDate(DateTime date) {
    return '${date.year}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')}';
  }
}

