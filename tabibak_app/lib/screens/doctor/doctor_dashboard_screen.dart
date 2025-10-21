import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/appointment_provider.dart';

class DoctorDashboardScreen extends StatefulWidget {
  const DoctorDashboardScreen({super.key});

  @override
  State<DoctorDashboardScreen> createState() => _DoctorDashboardScreenState();
}

class _DoctorDashboardScreenState extends State<DoctorDashboardScreen> {
  int _selectedIndex = 0;

  final List<Widget> _screens = [
    const DoctorHomeTab(),
    const DoctorAppointmentsTab(),
    const DoctorPatientsTab(),
    const DoctorPostsTab(),
    const DoctorProfileTab(),
  ];

  @override
  Widget build(BuildContext context) {
    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('لوحة تحكم الطبيب'),
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
              icon: Icon(Icons.calendar_today),
              label: 'المواعيد',
            ),
            NavigationDestination(
              icon: Icon(Icons.people),
              label: 'المرضى',
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

// Doctor Home Tab - Dashboard with statistics
class DoctorHomeTab extends StatelessWidget {
  const DoctorHomeTab({super.key});

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'مرحباً بك',
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
                'مواعيد اليوم',
                '8',
                Icons.calendar_today,
                Colors.blue,
              ),
              _buildStatCard(
                context,
                'المرضى',
                '156',
                Icons.people,
                Colors.green,
              ),
              _buildStatCard(
                context,
                'التقييمات',
                '4.8',
                Icons.star,
                Colors.orange,
              ),
              _buildStatCard(
                context,
                'المنشورات',
                '12',
                Icons.article,
                Colors.purple,
              ),
            ],
          ),
          const SizedBox(height: 24),
          Text(
            'مواعيد اليوم',
            style: Theme.of(context).textTheme.titleLarge?.copyWith(
                  fontWeight: FontWeight.bold,
                ),
          ),
          const SizedBox(height: 16),
          _buildTodayAppointmentsList(),
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
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTodayAppointmentsList() {
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
            title: Text('مريض ${index + 1}'),
            subtitle: Text('${10 + index}:00 صباحاً'),
            trailing: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                IconButton(
                  icon: const Icon(Icons.check, color: Colors.green),
                  onPressed: () {
                    // TODO: Confirm appointment
                  },
                ),
                IconButton(
                  icon: const Icon(Icons.close, color: Colors.red),
                  onPressed: () {
                    // TODO: Cancel appointment
                  },
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}

// Doctor Appointments Tab
class DoctorAppointmentsTab extends StatelessWidget {
  const DoctorAppointmentsTab({super.key});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.all(16),
          child: SegmentedButton<String>(
            segments: const [
              ButtonSegment(value: 'pending', label: Text('قيد الانتظار')),
              ButtonSegment(value: 'confirmed', label: Text('مؤكدة')),
              ButtonSegment(value: 'completed', label: Text('مكتملة')),
            ],
            selected: const {'pending'},
            onSelectionChanged: (Set<String> newSelection) {
              // TODO: Filter appointments
            },
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
                  title: Text('مريض ${index + 1}'),
                  subtitle: const Text('2024-12-25 - 10:00 صباحاً'),
                  trailing: Chip(
                    label: const Text('قيد الانتظار'),
                    backgroundColor: Colors.orange.shade100,
                  ),
                  onTap: () {
                    // TODO: View appointment details
                  },
                ),
              );
            },
          ),
        ),
      ],
    );
  }
}

// Doctor Patients Tab
class DoctorPatientsTab extends StatelessWidget {
  const DoctorPatientsTab({super.key});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.all(16),
          child: TextField(
            decoration: InputDecoration(
              hintText: 'البحث عن مريض...',
              prefixIcon: const Icon(Icons.search),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
              ),
            ),
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
                  title: Text('مريض ${index + 1}'),
                  subtitle: const Text('آخر زيارة: 2024-12-20'),
                  trailing: const Icon(Icons.arrow_forward_ios),
                  onTap: () {
                    // TODO: View patient details
                  },
                ),
              );
            },
          ),
        ),
      ],
    );
  }
}

// Doctor Posts Tab
class DoctorPostsTab extends StatelessWidget {
  const DoctorPostsTab({super.key});

  @override
  Widget build(BuildContext context) {
    return const Center(
      child: Text('منشوراتي الصحية'),
    );
  }
}

// Doctor Profile Tab
class DoctorProfileTab extends StatelessWidget {
  const DoctorProfileTab({super.key});

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
          authProvider.user?.name ?? 'الطبيب',
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
          leading: const Icon(Icons.edit),
          title: const Text('تعديل الملف الشخصي'),
          trailing: const Icon(Icons.arrow_forward_ios),
          onTap: () {
            // TODO: Navigate to edit profile
          },
        ),
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

