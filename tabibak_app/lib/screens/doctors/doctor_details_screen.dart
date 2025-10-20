import 'package:flutter/material.dart';
import '../../config/app_colors.dart';
import '../../models/doctor.dart';
import '../../services/doctor_service.dart';
import '../appointments/book_appointment_screen.dart';

class DoctorDetailsScreen extends StatefulWidget {
  final int doctorId;

  const DoctorDetailsScreen({
    super.key,
    required this.doctorId,
  });

  @override
  State<DoctorDetailsScreen> createState() => _DoctorDetailsScreenState();
}

class _DoctorDetailsScreenState extends State<DoctorDetailsScreen> {
  final DoctorService _doctorService = DoctorService();
  Doctor? _doctor;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadDoctorDetails();
  }

  Future<void> _loadDoctorDetails() async {
    try {
      final doctor = await _doctorService.getDoctorDetails(widget.doctorId);
      setState(() {
        _doctor = doctor;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _isLoading = false;
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('خطأ في تحميل بيانات الطبيب: $e'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('تفاصيل الطبيب'),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _doctor == null
              ? const Center(child: Text('لم يتم العثور على الطبيب'))
              : SingleChildScrollView(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Header Card
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
                          children: [
                            CircleAvatar(
                              radius: 60,
                              backgroundColor: Colors.white,
                              backgroundImage: _doctor!.avatar != null
                                  ? NetworkImage(_doctor!.avatar!)
                                  : null,
                              child: _doctor!.avatar == null
                                  ? const Icon(
                                      Icons.person,
                                      size: 60,
                                      color: AppColors.primary,
                                    )
                                  : null,
                            ),
                            const SizedBox(height: 16),
                            Text(
                              _doctor!.name,
                              style: const TextStyle(
                                fontSize: 24,
                                fontWeight: FontWeight.bold,
                                color: Colors.white,
                              ),
                              textAlign: TextAlign.center,
                            ),
                            if (_doctor!.specializationName != null) ...[
                              const SizedBox(height: 8),
                              Text(
                                _doctor!.specializationName!,
                                style: const TextStyle(
                                  fontSize: 16,
                                  color: Colors.white70,
                                ),
                              ),
                            ],
                            const SizedBox(height: 16),
                            Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                _InfoChip(
                                  icon: Icons.star,
                                  label: _doctor!.displayRating,
                                  color: AppColors.warning,
                                ),
                                const SizedBox(width: 16),
                                _InfoChip(
                                  icon: Icons.attach_money,
                                  label: _doctor!.displayFee,
                                  color: AppColors.success,
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),

                      const SizedBox(height: 24),

                      // Contact Information
                      if (_doctor!.phone != null || _doctor!.email != null) ...[
                        const Padding(
                          padding: EdgeInsets.symmetric(horizontal: 16),
                          child: Text(
                            'معلومات الاتصال',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                        const SizedBox(height: 12),
                        Card(
                          margin: const EdgeInsets.symmetric(horizontal: 16),
                          child: Padding(
                            padding: const EdgeInsets.all(16),
                            child: Column(
                              children: [
                                if (_doctor!.phone != null)
                                  _InfoRow(
                                    icon: Icons.phone,
                                    label: 'الهاتف',
                                    value: _doctor!.phone!,
                                  ),
                                if (_doctor!.phone != null && _doctor!.email != null)
                                  const Divider(height: 24),
                                if (_doctor!.email != null)
                                  _InfoRow(
                                    icon: Icons.email,
                                    label: 'البريد الإلكتروني',
                                    value: _doctor!.email!,
                                  ),
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(height: 24),
                      ],

                      // Location
                      if (_doctor!.city != null || _doctor!.address != null) ...[
                        const Padding(
                          padding: EdgeInsets.symmetric(horizontal: 16),
                          child: Text(
                            'الموقع',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                        const SizedBox(height: 12),
                        Card(
                          margin: const EdgeInsets.symmetric(horizontal: 16),
                          child: Padding(
                            padding: const EdgeInsets.all(16),
                            child: Column(
                              children: [
                                if (_doctor!.city != null)
                                  _InfoRow(
                                    icon: Icons.location_city,
                                    label: 'المدينة',
                                    value: _doctor!.city!,
                                  ),
                                if (_doctor!.city != null && _doctor!.address != null)
                                  const Divider(height: 24),
                                if (_doctor!.address != null)
                                  _InfoRow(
                                    icon: Icons.location_on,
                                    label: 'العنوان',
                                    value: _doctor!.address!,
                                  ),
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(height: 24),
                      ],

                      // Bio
                      if (_doctor!.biography != null) ...[
                        const Padding(
                          padding: EdgeInsets.symmetric(horizontal: 16),
                          child: Text(
                            'نبذة عن الطبيب',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                        const SizedBox(height: 12),
                        Card(
                          margin: const EdgeInsets.symmetric(horizontal: 16),
                          child: Padding(
                            padding: const EdgeInsets.all(16),
                            child: Text(
                              _doctor!.biography!,
                              style: const TextStyle(fontSize: 14, height: 1.5),
                            ),
                          ),
                        ),
                        const SizedBox(height: 24),
                      ],

                      const SizedBox(height: 80),
                    ],
                  ),
                ),
      bottomNavigationBar: _doctor == null
          ? null
          : Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.1),
                    blurRadius: 10,
                    offset: const Offset(0, -5),
                  ),
                ],
              ),
              child: ElevatedButton(
                onPressed: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (context) => BookAppointmentScreen(
                        doctor: _doctor!,
                      ),
                    ),
                  );
                },
                style: ElevatedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 16),
                ),
                child: const Text(
                  'حجز موعد',
                  style: TextStyle(fontSize: 16),
                ),
              ),
            ),
    );
  }
}

class _InfoChip extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;

  const _InfoChip({
    required this.icon,
    required this.label,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.2),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 18, color: Colors.white),
          const SizedBox(width: 8),
          Text(
            label,
            style: const TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w500,
              color: Colors.white,
            ),
          ),
        ],
      ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;

  const _InfoRow({
    required this.icon,
    required this.label,
    required this.value,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Icon(icon, size: 20, color: AppColors.primary),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: const TextStyle(
                  fontSize: 12,
                  color: AppColors.textSecondary,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                value,
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

