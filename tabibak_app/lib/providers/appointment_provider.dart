import 'package:flutter/material.dart';
import '../models/appointment.dart';
import '../services/appointment_service.dart';

class AppointmentProvider with ChangeNotifier {
  final AppointmentService _appointmentService = AppointmentService();

  List<Appointment> _appointments = [];
  bool _isLoading = false;
  String? _error;
  int _currentPage = 1;
  int _totalPages = 1;

  List<Appointment> get appointments => _appointments;
  bool get isLoading => _isLoading;
  String? get error => _error;
  int get currentPage => _currentPage;
  int get totalPages => _totalPages;
  bool get hasMore => _currentPage < _totalPages;

  Future<void> loadAppointments({String? status, bool refresh = false}) async {
    if (refresh) {
      _currentPage = 1;
      _appointments.clear();
    }

    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final result = await _appointmentService.getAppointments(
        page: _currentPage,
        status: status,
      );

      if (refresh) {
        _appointments = result['appointments'];
      } else {
        _appointments.addAll(result['appointments']);
      }

      _totalPages = result['pages'];
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> loadMore({String? status}) async {
    if (!hasMore || _isLoading) return;

    _currentPage++;
    await loadAppointments(status: status);
  }

  Future<bool> cancelAppointment(int appointmentId, {String? reason}) async {
    try {
      await _appointmentService.cancelAppointment(appointmentId, reason: reason);
      
      // Update local list
      final index = _appointments.indexWhere((a) => a.id == appointmentId);
      if (index != -1) {
        _appointments[index] = Appointment(
          id: _appointments[index].id,
          patientId: _appointments[index].patientId,
          doctorId: _appointments[index].doctorId,
          patientName: _appointments[index].patientName,
          doctorName: _appointments[index].doctorName,
          doctorSpecialization: _appointments[index].doctorSpecialization,
          appointmentDate: _appointments[index].appointmentDate,
          appointmentTime: _appointments[index].appointmentTime,
          status: 'cancelled',
          notes: _appointments[index].notes,
          consultationFee: _appointments[index].consultationFee,
          createdAt: _appointments[index].createdAt,
        );
        notifyListeners();
      }

      return true;
    } catch (e) {
      _error = e.toString();
      notifyListeners();
      return false;
    }
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }

  void reset() {
    _appointments = [];
    _currentPage = 1;
    _totalPages = 1;
    _error = null;
    _isLoading = false;
    notifyListeners();
  }
}

