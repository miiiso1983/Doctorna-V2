import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/app_colors.dart';
import '../../providers/auth_provider.dart';
import '../../providers/notification_provider.dart';
import '../../providers/theme_provider.dart';
import '../../models/specialization.dart';
import '../../services/doctor_service.dart';
import '../doctors/doctors_list_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  final DoctorService _doctorService = DoctorService();
  List<Specialization> _specializations = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadSpecializations();
  }

  Future<void> _loadSpecializations() async {
    try {
      final specializations = await _doctorService.getSpecializations();
      setState(() {
        _specializations = specializations;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = context.watch<AuthProvider>();
    final notificationProvider = context.watch<NotificationProvider>();
    final user = authProvider.user;

    return Scaffold(
      appBar: AppBar(
        title: const Text('طبيبك'),
        actions: [
          Stack(
            children: [
              IconButton(
                icon: const Icon(Icons.notifications_outlined),
                onPressed: () {
                  Navigator.pushNamed(context, '/notifications');
                },
              ),
              if (notificationProvider.unreadCount > 0)
                Positioned(
                  right: 8,
                  top: 8,
                  child: Container(
                    padding: const EdgeInsets.all(4),
                    decoration: const BoxDecoration(
                      color: Colors.red,
                      shape: BoxShape.circle,
                    ),
                    constraints: const BoxConstraints(
                      minWidth: 16,
                      minHeight: 16,
                    ),
                    child: Text(
                      notificationProvider.unreadCount > 99
                          ? '99+'
                          : '${notificationProvider.unreadCount}',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 10,
                        fontWeight: FontWeight.bold,
                      ),
                      textAlign: TextAlign.center,
                    ),
                  ),
                ),
            ],
          ),
        ],
      ),
      drawer: Drawer(
        child: ListView(
          padding: EdgeInsets.zero,
          children: [
            UserAccountsDrawerHeader(
              decoration: const BoxDecoration(
                gradient: AppColors.primaryGradient,
              ),
              accountName: Text(
                user?.name ?? 'مستخدم',
                style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
              accountEmail: Text(user?.email ?? ''),
              currentAccountPicture: CircleAvatar(
                backgroundColor: Colors.white,
                backgroundImage: user?.avatar != null
                    ? NetworkImage(user!.avatar!)
                    : null,
                child: user?.avatar == null
                    ? const Icon(Icons.person, size: 40, color: AppColors.primary)
                    : null,
              ),
            ),
            ListTile(
              leading: const Icon(Icons.home),
              title: const Text('الرئيسية'),
              onTap: () {
                Navigator.pop(context);
              },
            ),
            ListTile(
              leading: const Icon(Icons.calendar_today),
              title: const Text('مواعيدي'),
              onTap: () {
                Navigator.pop(context);
                Navigator.pushNamed(context, '/appointments');
              },
            ),
            ListTile(
              leading: const Icon(Icons.person),
              title: const Text('الملف الشخصي'),
              onTap: () {
                Navigator.pop(context);
                Navigator.pushNamed(context, '/profile');
              },
            ),
            const Divider(),
            ListTile(
              leading: const Icon(Icons.article),
              title: const Text('المنشورات الصحية'),
              onTap: () {
                Navigator.pop(context);
                Navigator.pushNamed(context, '/health-posts');
              },
            ),
            ListTile(
              leading: const Icon(Icons.notifications),
              title: const Text('الإشعارات'),
              trailing: notificationProvider.unreadCount > 0
                  ? Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.red,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        '${notificationProvider.unreadCount}',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    )
                  : null,
              onTap: () {
                Navigator.pop(context);
                Navigator.pushNamed(context, '/notifications');
              },
            ),
            ListTile(
              leading: const Icon(Icons.chat),
              title: const Text('المحادثات'),
              onTap: () {
                Navigator.pop(context);
                Navigator.pushNamed(context, '/conversations');
              },
            ),
            ListTile(
              leading: Icon(
                context.watch<ThemeProvider>().isDarkMode
                    ? Icons.light_mode
                    : Icons.dark_mode,
              ),
              title: const Text('الوضع الليلي'),
              trailing: Switch(
                value: context.watch<ThemeProvider>().isDarkMode,
                onChanged: (value) {
                  context.read<ThemeProvider>().toggleTheme();
                },
              ),
              onTap: () {
                context.read<ThemeProvider>().toggleTheme();
              },
            ),
            ListTile(
              leading: const Icon(Icons.info),
              title: const Text('عن التطبيق'),
              onTap: () {
                Navigator.pop(context);
                showAboutDialog(
                  context: context,
                  applicationName: 'طبيبك',
                  applicationVersion: '1.0.0',
                  applicationIcon: const Icon(Icons.medical_services, size: 48),
                  children: [
                    const Text('منصة حجز المواعيد الطبية'),
                    const SizedBox(height: 8),
                    const Text('تطبيق شامل لحجز المواعيد مع الأطباء وإدارة الصحة'),
                  ],
                );
              },
            ),
            const Divider(),
            ListTile(
              leading: const Icon(Icons.logout, color: AppColors.error),
              title: const Text('تسجيل الخروج', style: TextStyle(color: AppColors.error)),
              onTap: () async {
                await authProvider.logout();
                if (!mounted) return;
                Navigator.of(context).pushReplacementNamed('/login');
              },
            ),
          ],
        ),
      ),
      body: RefreshIndicator(
        onRefresh: _loadSpecializations,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Welcome Banner
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(24),
                decoration: const BoxDecoration(
                  gradient: AppColors.primaryGradient,
                  borderRadius: BorderRadius.only(
                    bottomLeft: Radius.circular(30),
                    bottomRight: Radius.circular(30),
                  ),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'مرحباً ${user?.name ?? ''}',
                      style: const TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                    ),
                    const SizedBox(height: 8),
                    const Text(
                      'كيف يمكننا مساعدتك اليوم؟',
                      style: TextStyle(
                        fontSize: 16,
                        color: Colors.white70,
                      ),
                    ),
                    const SizedBox(height: 20),
                    // Search Bar
                    Container(
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: TextField(
                        decoration: InputDecoration(
                          hintText: 'ابحث عن طبيب أو تخصص...',
                          prefixIcon: const Icon(Icons.search),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: BorderSide.none,
                          ),
                          filled: true,
                          fillColor: Colors.white,
                        ),
                        onSubmitted: (query) {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (context) => DoctorsListScreen(searchQuery: query),
                            ),
                          );
                        },
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 24),

              // Quick Actions
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                child: Row(
                  children: [
                    Expanded(
                      child: _QuickActionCard(
                        icon: Icons.medical_services,
                        title: 'حجز موعد',
                        color: AppColors.primary,
                        onTap: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (context) => const DoctorsListScreen(),
                            ),
                          );
                        },
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: _QuickActionCard(
                        icon: Icons.calendar_today,
                        title: 'مواعيدي',
                        color: AppColors.secondary,
                        onTap: () {
                          Navigator.pushNamed(context, '/appointments');
                        },
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 24),

              // Specializations
              const Padding(
                padding: EdgeInsets.symmetric(horizontal: 16),
                child: Text(
                  'التخصصات الطبية',
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
              const SizedBox(height: 16),

              if (_isLoading)
                const Center(
                  child: Padding(
                    padding: EdgeInsets.all(32),
                    child: CircularProgressIndicator(),
                  ),
                )
              else if (_specializations.isEmpty)
                const Center(
                  child: Padding(
                    padding: EdgeInsets.all(32),
                    child: Text('لا توجد تخصصات متاحة'),
                  ),
                )
              else
                GridView.builder(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 2,
                    crossAxisSpacing: 12,
                    mainAxisSpacing: 12,
                    childAspectRatio: 1.2,
                  ),
                  itemCount: _specializations.length,
                  itemBuilder: (context, index) {
                    final specialization = _specializations[index];
                    return _SpecializationCard(
                      specialization: specialization,
                      onTap: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (context) => DoctorsListScreen(
                              specializationId: specialization.id,
                              specializationName: specialization.name,
                            ),
                          ),
                        );
                      },
                    );
                  },
                ),

              const SizedBox(height: 24),
            ],
          ),
        ),
      ),
    );
  }
}

class _QuickActionCard extends StatelessWidget {
  final IconData icon;
  final String title;
  final Color color;
  final VoidCallback onTap;

  const _QuickActionCard({
    required this.icon,
    required this.title,
    required this.color,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            children: [
              Icon(icon, size: 40, color: color),
              const SizedBox(height: 8),
              Text(
                title,
                style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w500),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _SpecializationCard extends StatelessWidget {
  final Specialization specialization;
  final VoidCallback onTap;

  const _SpecializationCard({
    required this.specialization,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.local_hospital, size: 40, color: AppColors.primary),
              const SizedBox(height: 8),
              Text(
                specialization.name,
                style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w500),
                textAlign: TextAlign.center,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
              if (specialization.doctorCount > 0) ...[
                const SizedBox(height: 4),
                Text(
                  '${specialization.doctorCount} طبيب',
                  style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}

