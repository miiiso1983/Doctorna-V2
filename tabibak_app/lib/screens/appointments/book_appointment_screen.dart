import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../config/app_colors.dart';
import '../../models/doctor.dart';
import '../../services/doctor_service.dart';
import '../../services/appointment_service.dart';

class BookAppointmentScreen extends StatefulWidget {
  final Doctor doctor;

  const BookAppointmentScreen({
    super.key,
    required this.doctor,
  });

  @override
  State<BookAppointmentScreen> createState() => _BookAppointmentScreenState();
}

class _BookAppointmentScreenState extends State<BookAppointmentScreen> {
  final DoctorService _doctorService = DoctorService();
  final AppointmentService _appointmentService = AppointmentService();
  final TextEditingController _notesController = TextEditingController();

  DateTime _selectedDate = DateTime.now();
  String? _selectedTime;
  List<String> _availableSlots = [];
  bool _isLoadingSlots = false;
  bool _isBooking = false;

  @override
  void initState() {
    super.initState();
    _loadAvailableSlots();
  }

  @override
  void dispose() {
    _notesController.dispose();
    super.dispose();
  }

  Future<void> _loadAvailableSlots() async {
    setState(() {
      _isLoadingSlots = true;
      _selectedTime = null;
    });

    try {
      final slots = await _doctorService.getDoctorAvailability(
        widget.doctor.id,
        _selectedDate,
      );
      setState(() {
        _availableSlots = slots;
        _isLoadingSlots = false;
      });
    } catch (e) {
      setState(() {
        _isLoadingSlots = false;
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('خطأ في تحميل المواعيد المتاحة: $e'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }

  Future<void> _selectDate() async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate,
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 90)),
      locale: const Locale('ar', 'IQ'),
    );

    if (picked != null && picked != _selectedDate) {
      setState(() {
        _selectedDate = picked;
      });
      _loadAvailableSlots();
    }
  }

  Future<void> _bookAppointment() async {
    if (_selectedTime == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('الرجاء اختيار وقت الموعد'),
          backgroundColor: AppColors.error,
        ),
      );
      return;
    }

    setState(() {
      _isBooking = true;
    });

    try {
      await _appointmentService.createAppointment(
        doctorId: widget.doctor.id,
        appointmentDate: _selectedDate,
        appointmentTime: _selectedTime!,
        notes: _notesController.text.trim().isEmpty
            ? null
            : _notesController.text.trim(),
      );

      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('تم حجز الموعد بنجاح'),
          backgroundColor: AppColors.success,
        ),
      );

      Navigator.of(context).pop();
      Navigator.of(context).pop();
    } catch (e) {
      setState(() {
        _isBooking = false;
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('خطأ في حجز الموعد: $e'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final dateFormat = DateFormat('EEEE، d MMMM yyyy', 'ar');

    return Scaffold(
      appBar: AppBar(
        title: const Text('حجز موعد'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Doctor Info Card
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Row(
                  children: [
                    CircleAvatar(
                      radius: 30,
                      backgroundColor: AppColors.primary.withOpacity(0.1),
                      backgroundImage: widget.doctor.avatar != null
                          ? NetworkImage(widget.doctor.avatar!)
                          : null,
                      child: widget.doctor.avatar == null
                          ? const Icon(Icons.person, color: AppColors.primary)
                          : null,
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            widget.doctor.name,
                            style: const TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          if (widget.doctor.specializationName != null) ...[
                            const SizedBox(height: 4),
                            Text(
                              widget.doctor.specializationName!,
                              style: TextStyle(
                                fontSize: 14,
                                color: Colors.grey[600],
                              ),
                            ),
                          ],
                          if (widget.doctor.consultationFee != null) ...[
                            const SizedBox(height: 4),
                            Text(
                              widget.doctor.displayFee,
                              style: const TextStyle(
                                fontSize: 14,
                                fontWeight: FontWeight.w500,
                                color: AppColors.success,
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 24),

            // Date Selection
            const Text(
              'اختر التاريخ',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 12),
            Card(
              child: InkWell(
                onTap: _selectDate,
                borderRadius: BorderRadius.circular(12),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Row(
                    children: [
                      const Icon(Icons.calendar_today, color: AppColors.primary),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Text(
                          dateFormat.format(_selectedDate),
                          style: const TextStyle(fontSize: 16),
                        ),
                      ),
                      const Icon(Icons.arrow_drop_down),
                    ],
                  ),
                ),
              ),
            ),

            const SizedBox(height: 24),

            // Time Selection
            const Text(
              'اختر الوقت',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 12),

            if (_isLoadingSlots)
              const Center(
                child: Padding(
                  padding: EdgeInsets.all(32),
                  child: CircularProgressIndicator(),
                ),
              )
            else if (_availableSlots.isEmpty)
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(32),
                  child: Center(
                    child: Column(
                      children: [
                        Icon(
                          Icons.event_busy,
                          size: 48,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'لا توجد مواعيد متاحة في هذا اليوم',
                          style: TextStyle(
                            fontSize: 16,
                            color: Colors.grey[600],
                          ),
                          textAlign: TextAlign.center,
                        ),
                      ],
                    ),
                  ),
                ),
              )
            else
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: _availableSlots.map((slot) {
                  final isSelected = _selectedTime == slot;
                  return ChoiceChip(
                    label: Text(slot),
                    selected: isSelected,
                    onSelected: (selected) {
                      setState(() {
                        _selectedTime = selected ? slot : null;
                      });
                    },
                    selectedColor: AppColors.primary,
                    labelStyle: TextStyle(
                      color: isSelected ? Colors.white : Colors.black,
                      fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                    ),
                  );
                }).toList(),
              ),

            const SizedBox(height: 24),

            // Notes
            const Text(
              'ملاحظات (اختياري)',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _notesController,
              maxLines: 4,
              decoration: const InputDecoration(
                hintText: 'أضف أي ملاحظات أو أعراض تريد إخبار الطبيب بها...',
              ),
            ),

            const SizedBox(height: 32),

            // Book Button
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _isBooking ? null : _bookAppointment,
                style: ElevatedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 16),
                ),
                child: _isBooking
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                        ),
                      )
                    : const Text(
                        'تأكيد الحجز',
                        style: TextStyle(fontSize: 16),
                      ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

