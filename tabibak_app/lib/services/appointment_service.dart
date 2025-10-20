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
  }

  Future<Appointment> getAppointmentDetails(int appointmentId) async {
    final response = await _apiService.get(
      ApiConfig.appointmentDetails(appointmentId),
      requiresAuth: true,
    );

    return Appointment.fromJson(response['data']);
  }

  Future<Appointment> createAppointment({
    required int doctorId,
    required DateTime appointmentDate,
    required String appointmentTime,
    String? notes,
  }) async {
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
  }

  Future<void> cancelAppointment(int appointmentId, {String? reason}) async {
    await _apiService.post(
      ApiConfig.cancelAppointment(appointmentId),
      body: {
        if (reason != null && reason.isNotEmpty) 'reason': reason,
      },
      requiresAuth: true,
    );
  }

  Future<void> confirmAppointment(int appointmentId) async {
    await _apiService.post(
      ApiConfig.confirmAppointment(appointmentId),
      body: {},
      requiresAuth: true,
    );
  }

  Future<void> completeAppointment(int appointmentId, {String? notes}) async {
    await _apiService.post(
      ApiConfig.completeAppointment(appointmentId),
      body: {
        if (notes != null && notes.isNotEmpty) 'notes': notes,
      },
      requiresAuth: true,
    );
  }
}

