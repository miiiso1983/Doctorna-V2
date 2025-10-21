import '../config/api_config.dart';
import '../models/appointment.dart';
import 'api_service.dart';

class AppointmentService {
  final ApiService _apiService = ApiService();

  Future<Map<String, dynamic>> getAppointments({
    int page = 1,
    int limit = 10,
    String? status,
  }) async {
    // TEMPORARY: Mock data for development
    print('📅 Mock getAppointments');
    await Future.delayed(const Duration(milliseconds: 500));

    final mockAppointments = [
      Appointment(
        id: 1,
        patientId: 1,
        doctorId: 1,
        patientName: 'مستخدم تجريبي',
        doctorName: 'د. أحمد محمود',
        doctorSpecialization: 'طب القلب',
        appointmentDate: DateTime.now().add(const Duration(days: 2)),
        appointmentTime: '10:00',
        status: 'pending',
        notes: 'فحص دوري',
        consultationFee: 50000.0,
        createdAt: DateTime.now(),
      ),
      Appointment(
        id: 2,
        patientId: 1,
        doctorId: 2,
        patientName: 'مستخدم تجريبي',
        doctorName: 'د. فاطمة علي',
        doctorSpecialization: 'طب الأطفال',
        appointmentDate: DateTime.now().add(const Duration(days: 5)),
        appointmentTime: '14:30',
        status: 'confirmed',
        notes: 'استشارة',
        consultationFee: 40000.0,
        createdAt: DateTime.now().subtract(const Duration(days: 1)),
      ),
    ];

    return {
      'appointments': mockAppointments,
      'total': mockAppointments.length,
      'page': page,
      'pages': 1,
    };

    /* REAL API CODE
    final queryParams = <String, String>{
      'page': page.toString(),
      'limit': limit.toString(),
    };

    if (status != null && status.isNotEmpty) {
      queryParams['status'] = status;
    }

    final response = await _apiService.get(
      ApiConfig.appointmentsList,
      queryParams: queryParams,
      requiresAuth: true,
    );

    final appointments = (response['data']['appointments'] as List)
        .map((json) => Appointment.fromJson(json))
        .toList();

    return {
      'appointments': appointments,
      'total': response['data']['total'] ?? 0,
      'page': response['data']['page'] ?? 1,
      'pages': response['data']['pages'] ?? 1,
    };
    */
  }

  Future<Appointment> getAppointmentDetails(int appointmentId) async {
    // TEMPORARY: Mock data for development
    print('📅 Mock getAppointmentDetails: $appointmentId');
    await Future.delayed(const Duration(milliseconds: 500));

    return Appointment(
      id: appointmentId,
      patientId: 1,
      doctorId: 1,
      patientName: 'مستخدم تجريبي',
      doctorName: 'د. أحمد محمود',
      doctorSpecialization: 'طب القلب',
      appointmentDate: DateTime.now().add(const Duration(days: 2)),
      appointmentTime: '10:00',
      status: 'pending',
      notes: 'فحص دوري',
      consultationFee: 50000.0,
      createdAt: DateTime.now(),
    );

    /* REAL API CODE
    final response = await _apiService.get(
      ApiConfig.appointmentDetails(appointmentId),
      requiresAuth: true,
    );

    return Appointment.fromJson(response['data']);
    */
  }

  Future<Appointment> createAppointment({
    required int doctorId,
    required DateTime appointmentDate,
    required String appointmentTime,
    String? notes,
  }) async {
    // TEMPORARY: Mock data for development
    print('📅 Mock createAppointment: doctor=$doctorId, date=$appointmentDate, time=$appointmentTime');
    await Future.delayed(const Duration(milliseconds: 500));

    return Appointment(
      id: DateTime.now().millisecondsSinceEpoch,
      patientId: 1,
      doctorId: doctorId,
      patientName: 'مستخدم تجريبي',
      doctorName: 'د. أحمد محمود',
      doctorSpecialization: 'طب القلب',
      appointmentDate: appointmentDate,
      appointmentTime: appointmentTime,
      status: 'pending',
      notes: notes,
      consultationFee: 50000.0,
      createdAt: DateTime.now(),
    );

    /* REAL API CODE
    final response = await _apiService.post(
      ApiConfig.createAppointment,
      body: {
        'doctor_id': doctorId,
        'appointment_date': appointmentDate.toIso8601String().split('T')[0],
        'appointment_time': appointmentTime,
        if (notes != null && notes.isNotEmpty) 'notes': notes,
      },
      requiresAuth: true,
    );

    return Appointment.fromJson(response['data']);
    */
  }

  Future<void> cancelAppointment(int appointmentId, {String? reason}) async {
    // TEMPORARY: Mock data for development
    print('📅 Mock cancelAppointment: $appointmentId');
    await Future.delayed(const Duration(milliseconds: 500));

    /* REAL API CODE
    await _apiService.post(
      ApiConfig.cancelAppointment(appointmentId),
      body: {
        if (reason != null && reason.isNotEmpty) 'reason': reason,
      },
      requiresAuth: true,
    );
    */
  }

  Future<void> confirmAppointment(int appointmentId) async {
    // TEMPORARY: Mock data for development
    print('📅 Mock confirmAppointment: $appointmentId');
    await Future.delayed(const Duration(milliseconds: 500));

    /* REAL API CODE
    await _apiService.post(
      ApiConfig.confirmAppointment(appointmentId),
      body: {},
      requiresAuth: true,
    );
    */
  }

  Future<void> completeAppointment(int appointmentId, {String? notes}) async {
    // TEMPORARY: Mock data for development
    print('📅 Mock completeAppointment: $appointmentId');
    await Future.delayed(const Duration(milliseconds: 500));

    /* REAL API CODE
    await _apiService.post(
      ApiConfig.completeAppointment(appointmentId),
      body: {
        if (notes != null && notes.isNotEmpty) 'notes': notes,
      },
      requiresAuth: true,
    );
    */
  }
}

