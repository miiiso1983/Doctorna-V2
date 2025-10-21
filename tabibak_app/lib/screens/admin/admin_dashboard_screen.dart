import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';

class AdminDashboardScreen extends StatefulWidget {
  const AdminDashboardScreen({super.key});

  @override
  State<AdminDashboardScreen> createState() => _AdminDashboardScreenState();
}

class _AdminDashboardScreenState extends State<AdminDashboardScreen> {
  int _selectedIndex = 0;

  final List<Widget> _screens = [
    const AdminHomeTab(),
    const AdminDoctorsTab(),
    const AdminPatientsTab(),
    const AdminAppointmentsTab(),
    const AdminPostsTab(),
    const AdminProfileTab(),
  ];

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);
    final user = authProvider.user;

    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('لوحة تحكم المدير'),
          actions: [
            IconButton(
              icon: const Icon(Icons.notifications),
              onPressed: () {
                // TODO: Navigate to notifications
              },
            ),
          ],
        ),
        body: _screens[_selectedIndex],
        bottomNavigationBar: NavigationBar(
          selectedIndex: _selectedIndex,
          onDestinationSelected: (index) {
            setState(() {
              _selectedIndex = index;
            });
          },
          destinations: const [
            NavigationDestination(
              icon: Icon(Icons.dashboard),
              label: 'الرئيسية',
            ),
            NavigationDestination(
              icon: Icon(Icons.medical_services),
              label: 'الأطباء',
            ),
            NavigationDestination(
              icon: Icon(Icons.people),
              label: 'المرضى',
            ),
            NavigationDestination(
              icon: Icon(Icons.calendar_today),
              label: 'المواعيد',
            ),
            NavigationDestination(
              icon: Icon(Icons.article),
              label: 'المنشورات',
            ),
            NavigationDestination(
              icon: Icon(Icons.person),
              label: 'الملف الشخصي',
            ),
          ],
        ),
      ),
    );
  }
}

// Admin Home Tab - Dashboard with statistics
class AdminHomeTab extends StatelessWidget {
  const AdminHomeTab({super.key});

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'الإحصائيات',
            style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                  fontWeight: FontWeight.bold,
                ),
          ),
          const SizedBox(height: 16),
          GridView.count(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            crossAxisCount: 2,
            crossAxisSpacing: 16,
            mainAxisSpacing: 16,
            children: [
              _buildStatCard(
                context,
                'الأطباء',
                '45',
                Icons.medical_services,
                Colors.blue,
              ),
              _buildStatCard(
                context,
                'المرضى',
                '1,234',
                Icons.people,
                Colors.green,
              ),
              _buildStatCard(
                context,
                'المواعيد',
                '567',
                Icons.calendar_today,
                Colors.orange,
              ),
              _buildStatCard(
                context,
                'المنشورات',
                '89',
                Icons.article,
                Colors.purple,
              ),
            ],
          ),
          const SizedBox(height: 24),
          Text(
            'المواعيد الأخيرة',
            style: Theme.of(context).textTheme.titleLarge?.copyWith(
                  fontWeight: FontWeight.bold,
                ),
          ),
          const SizedBox(height: 16),
          _buildRecentAppointmentsList(),
        ],
      ),
    );
  }

  Widget _buildStatCard(
    BuildContext context,
    String title,
    String value,
    IconData icon,
    Color color,
  ) {
    return Card(
      elevation: 2,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, size: 48, color: color),
            const SizedBox(height: 8),
            Text(
              value,
              style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                    fontWeight: FontWeight.bold,
                    color: color,
                  ),
            ),
            const SizedBox(height: 4),
            Text(
              title,
              style: Theme.of(context).textTheme.bodyLarge,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildRecentAppointmentsList() {
    return ListView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: 5,
      itemBuilder: (context, index) {
        return Card(
          margin: const EdgeInsets.only(bottom: 8),
          child: ListTile(
            leading: const CircleAvatar(
              child: Icon(Icons.person),
            ),
            title: Text('د. أحمد محمود - مريض ${index + 1}'),
            subtitle: const Text('2024-12-25 - 10:00 صباحاً'),
            trailing: Chip(
              label: const Text('قيد الانتظار'),
              backgroundColor: Colors.orange.shade100,
            ),
          ),
        );
      },
    );
  }
}

// Admin Doctors Tab
class AdminDoctorsTab extends StatelessWidget {
  const AdminDoctorsTab({super.key});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              Expanded(
                child: TextField(
                  decoration: InputDecoration(
                    hintText: 'البحث عن طبيب...',
                    prefixIcon: const Icon(Icons.search),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              FilledButton.icon(
                onPressed: () {
                  // TODO: Add new doctor
                },
                icon: const Icon(Icons.add),
                label: const Text('إضافة'),
              ),
            ],
          ),
        ),
        Expanded(
          child: ListView.builder(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            itemCount: 10,
            itemBuilder: (context, index) {
              return Card(
                margin: const EdgeInsets.only(bottom: 12),
                child: ListTile(
                  leading: const CircleAvatar(
                    child: Icon(Icons.person),
                  ),
                  title: Text('د. طبيب ${index + 1}'),
                  subtitle: const Text('طب القلب'),
                  trailing: PopupMenuButton(
                    itemBuilder: (context) => [
                      const PopupMenuItem(
                        value: 'view',
                        child: Text('عرض'),
                      ),
                      const PopupMenuItem(
                        value: 'edit',
                        child: Text('تعديل'),
                      ),
                      const PopupMenuItem(
                        value: 'delete',
                        child: Text('حذف'),
                      ),
                    ],
                  ),
                ),
              );
            },
          ),
        ),
      ],
    );
  }
}

// Admin Patients Tab
class AdminPatientsTab extends StatelessWidget {
  const AdminPatientsTab({super.key});

  @override
  Widget build(BuildContext context) {
    return const Center(
      child: Text('إدارة المرضى'),
    );
  }
}

// Admin Appointments Tab
class AdminAppointmentsTab extends StatelessWidget {
  const AdminAppointmentsTab({super.key});

  @override
  Widget build(BuildContext context) {
    return const Center(
      child: Text('إدارة المواعيد'),
    );
  }
}

// Admin Posts Tab
class AdminPostsTab extends StatelessWidget {
  const AdminPostsTab({super.key});

  @override
  Widget build(BuildContext context) {
    return const Center(
      child: Text('إدارة المنشورات'),
    );
  }
}

// Admin Profile Tab
class AdminProfileTab extends StatelessWidget {
  const AdminProfileTab({super.key});

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        const CircleAvatar(
          radius: 50,
          child: Icon(Icons.person, size: 50),
        ),
        const SizedBox(height: 16),
        Text(
          authProvider.user?.name ?? 'المدير',
          textAlign: TextAlign.center,
          style: Theme.of(context).textTheme.headlineSmall,
        ),
        Text(
          authProvider.user?.email ?? '',
          textAlign: TextAlign.center,
          style: Theme.of(context).textTheme.bodyMedium,
        ),
        const SizedBox(height: 24),
        ListTile(
          leading: const Icon(Icons.logout),
          title: const Text('تسجيل الخروج'),
          onTap: () {
            authProvider.logout();
          },
        ),
      ],
    );
  }
}

