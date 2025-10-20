class Appointment {
  final int id;
  final int patientId;
  final int doctorId;
  final String patientName;
  final String doctorName;
  final String? doctorSpecialization;
  final DateTime appointmentDate;
  final String appointmentTime;
  final String status;
  final String? notes;
  final double? consultationFee;
  final DateTime createdAt;

  Appointment({
    required this.id,
    required this.patientId,
    required this.doctorId,
    required this.patientName,
    required this.doctorName,
    this.doctorSpecialization,
    required this.appointmentDate,
    required this.appointmentTime,
    required this.status,
    this.notes,
    this.consultationFee,
    required this.createdAt,
  });

  factory Appointment.fromJson(Map<String, dynamic> json) {
    return Appointment(
      id: json['id'] ?? 0,
      patientId: json['patient_id'] ?? 0,
      doctorId: json['doctor_id'] ?? 0,
      patientName: json['patient_name'] ?? '',
      doctorName: json['doctor_name'] ?? '',
      doctorSpecialization: json['doctor_specialization'],
      appointmentDate: DateTime.parse(json['appointment_date']),
      appointmentTime: json['appointment_time'] ?? '',
      status: json['status'] ?? 'pending',
      notes: json['notes'],
      consultationFee: json['consultation_fee'] != null
          ? double.tryParse(json['consultation_fee'].toString())
          : null,
      createdAt: json['created_at'] != null
          ? DateTime.parse(json['created_at'])
          : DateTime.now(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'patient_id': patientId,
      'doctor_id': doctorId,
      'patient_name': patientName,
      'doctor_name': doctorName,
      'doctor_specialization': doctorSpecialization,
      'appointment_date': appointmentDate.toIso8601String().split('T')[0],
      'appointment_time': appointmentTime,
      'status': status,
      'notes': notes,
      'consultation_fee': consultationFee,
      'created_at': createdAt.toIso8601String(),
    };
  }

  String get statusText {
    switch (status) {
      case 'pending':
        return 'قيد الانتظار';
      case 'confirmed':
        return 'مؤكد';
      case 'cancelled':
        return 'ملغي';
      case 'completed':
        return 'مكتمل';
      case 'no_show':
        return 'لم يحضر';
      default:
        return status;
    }
  }

  bool get isPending => status == 'pending';
  bool get isConfirmed => status == 'confirmed';
  bool get isCancelled => status == 'cancelled';
  bool get isCompleted => status == 'completed';
}

