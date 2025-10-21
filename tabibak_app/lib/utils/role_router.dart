import 'package:flutter/material.dart';
import '../models/user.dart';
import '../screens/admin/admin_dashboard_screen.dart';
import '../screens/doctor/doctor_dashboard_screen.dart';
import '../screens/patient/patient_dashboard_screen.dart';

class RoleRouter {
  /// Get the appropriate dashboard screen based on user role
  static Widget getDashboardForRole(User user) {
    switch (user.role) {
      case 'super_admin':
        return const AdminDashboardScreen();
      case 'doctor':
        return const DoctorDashboardScreen();
      case 'patient':
      default:
        return const PatientDashboardScreen();
    }
  }

  /// Check if user has admin role
  static bool isAdmin(User? user) {
    return user?.role == 'super_admin';
  }

  /// Check if user has doctor role
  static bool isDoctor(User? user) {
    return user?.role == 'doctor';
  }

  /// Check if user has patient role
  static bool isPatient(User? user) {
    return user?.role == 'patient';
  }

  /// Get role display name in Arabic
  static String getRoleDisplayName(String role) {
    switch (role) {
      case 'super_admin':
        return 'مدير النظام';
      case 'doctor':
        return 'طبيب';
      case 'patient':
        return 'مريض';
      default:
        return 'مستخدم';
    }
  }

  /// Get role icon
  static IconData getRoleIcon(String role) {
    switch (role) {
      case 'super_admin':
        return Icons.admin_panel_settings;
      case 'doctor':
        return Icons.medical_services;
      case 'patient':
        return Icons.person;
      default:
        return Icons.person_outline;
    }
  }

  /// Get role color
  static Color getRoleColor(String role) {
    switch (role) {
      case 'super_admin':
        return Colors.red;
      case 'doctor':
        return Colors.blue;
      case 'patient':
        return Colors.green;
      default:
        return Colors.grey;
    }
  }
}

