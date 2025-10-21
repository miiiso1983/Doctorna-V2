import '../config/api_config.dart';
import '../models/doctor.dart';
import '../models/specialization.dart';
import 'api_service.dart';

class DoctorService {
  final ApiService _apiService = ApiService();

  Future<Map<String, dynamic>> getDoctors({
    int page = 1,
    int limit = 10,
    int? specializationId,
    String? city,
    double? minRating,
    String? search,
  }) async {
    // TEMPORARY: Mock data for development
    print('🏥 Mock getDoctors');
    await Future.delayed(const Duration(milliseconds: 500));

    final mockDoctors = [
      Doctor(
        id: 1,
        userId: 2,
        name: 'د. أحمد محمود',
        email: 'ahmed@doctorna.com',
        phone: '07701234567',
        specializationId: 1,
        specializationName: 'طب القلب',
        specializationNameEn: 'Cardiology',
        specializationIcon: '❤️',
        specializationColor: '#F44336',
        biography: 'استشاري أمراض القلب والأوعية الدموية مع خبرة 15 عاماً',
        experienceYears: 15,
        consultationFee: 50000.0,
        rating: 4.8,
        totalReviews: 124,
        city: 'بغداد',
        address: 'شارع الكندي، الكرادة',
        avatar: 'https://ui-avatars.com/api/?name=Ahmed+Mahmoud&background=4CAF50&color=fff&size=200',
        clinicName: 'عيادة القلب المتقدمة',
        clinicAddress: 'شارع الكندي، الكرادة، بغداد',
        clinicPhone: '07701234567',
        status: 'active',
      ),
      Doctor(
        id: 2,
        userId: 3,
        name: 'د. فاطمة علي',
        email: 'fatima@doctorna.com',
        phone: '07709876543',
        specializationId: 2,
        specializationName: 'طب الأطفال',
        specializationNameEn: 'Pediatrics',
        specializationIcon: '👶',
        specializationColor: '#2196F3',
        biography: 'أخصائية طب الأطفال وحديثي الولادة',
        experienceYears: 10,
        consultationFee: 40000.0,
        rating: 4.9,
        totalReviews: 98,
        city: 'بغداد',
        address: 'شارع فلسطين، المنصور',
        avatar: 'https://ui-avatars.com/api/?name=Fatima+Ali&background=2196F3&color=fff&size=200',
        clinicName: 'عيادة الأطفال المتخصصة',
        clinicAddress: 'شارع فلسطين، المنصور، بغداد',
        clinicPhone: '07709876543',
        status: 'active',
      ),
      Doctor(
        id: 3,
        userId: 4,
        name: 'د. محمد حسن',
        email: 'mohammed@doctorna.com',
        phone: '07705555555',
        specializationId: 3,
        specializationName: 'طب الأسنان',
        specializationNameEn: 'Dentistry',
        specializationIcon: '🦷',
        specializationColor: '#FF9800',
        biography: 'استشاري تجميل وزراعة الأسنان',
        experienceYears: 12,
        consultationFee: 45000.0,
        rating: 4.7,
        totalReviews: 156,
        city: 'البصرة',
        address: 'شارع الجمهورية، البصرة',
        avatar: 'https://ui-avatars.com/api/?name=Mohammed+Hassan&background=FF9800&color=fff&size=200',
        clinicName: 'عيادة الأسنان الحديثة',
        clinicAddress: 'شارع الجمهورية، البصرة',
        clinicPhone: '07705555555',
        status: 'active',
      ),
    ];

    return {
      'doctors': mockDoctors,
      'total': mockDoctors.length,
      'page': page,
      'pages': 1,
    };

    /* REAL API CODE - Uncomment when API is working
    final queryParams = <String, String>{
      'page': page.toString(),
      'limit': limit.toString(),
    };

    if (specializationId != null) {
      queryParams['specialization_id'] = specializationId.toString();
    }
    if (city != null && city.isNotEmpty) {
      queryParams['city'] = city;
    }
    if (minRating != null) {
      queryParams['min_rating'] = minRating.toString();
    }
    if (search != null && search.isNotEmpty) {
      queryParams['search'] = search;
    }

    final response = await _apiService.get(
      ApiConfig.doctorsList,
      queryParams: queryParams,
      requiresAuth: false,
    );

    final doctors = (response['data']['doctors'] as List)
        .map((json) => Doctor.fromJson(json))
        .toList();

    return {
      'doctors': doctors,
      'total': response['data']['total'] ?? 0,
      'page': response['data']['page'] ?? 1,
      'pages': response['data']['pages'] ?? 1,
    };
    */
  }

  Future<Doctor> getDoctorDetails(int doctorId) async {
    // TEMPORARY: Mock data for development
    print('🏥 Mock getDoctorDetails: $doctorId');
    await Future.delayed(const Duration(milliseconds: 500));

    return Doctor(
      id: doctorId,
      userId: doctorId + 1,
      name: 'د. أحمد محمود',
      email: 'ahmed@doctorna.com',
      phone: '07701234567',
      specializationId: 1,
      specializationName: 'طب القلب',
      specializationNameEn: 'Cardiology',
      specializationIcon: '❤️',
      specializationColor: '#F44336',
      biography: 'استشاري أمراض القلب والأوعية الدموية مع خبرة 15 عاماً في تشخيص وعلاج أمراض القلب',
      experienceYears: 15,
      education: 'بكالوريوس طب وجراحة - جامعة بغداد\nماجستير أمراض القلب - جامعة لندن',
      certifications: 'البورد العراقي في أمراض القلب\nالزمالة الأوروبية في أمراض القلب',
      languages: 'العربية، الإنجليزية',
      consultationFee: 50000.0,
      rating: 4.8,
      totalReviews: 124,
      city: 'بغداد',
      address: 'شارع الكندي، الكرادة',
      avatar: 'https://ui-avatars.com/api/?name=Ahmed+Mahmoud&background=4CAF50&color=fff&size=200',
      clinicName: 'عيادة القلب المتقدمة',
      clinicAddress: 'شارع الكندي، الكرادة، بغداد',
      clinicPhone: '07701234567',
      licenseNumber: 'IQ-DOC-12345',
      status: 'active',
    );

    /* REAL API CODE
    final response = await _apiService.get(
      ApiConfig.doctorDetails(doctorId),
      requiresAuth: false,
    );

    return Doctor.fromJson(response['data']);
    */
  }

  Future<List<String>> getDoctorAvailability(int doctorId, DateTime date) async {
    // TEMPORARY: Mock data for development
    print('🏥 Mock getDoctorAvailability: $doctorId, $date');
    await Future.delayed(const Duration(milliseconds: 500));

    return [
      '09:00',
      '09:30',
      '10:00',
      '10:30',
      '11:00',
      '14:00',
      '14:30',
      '15:00',
      '15:30',
      '16:00',
    ];

    /* REAL API CODE
    final dateStr = date.toIso8601String().split('T')[0];
    final response = await _apiService.get(
      '${ApiConfig.doctorAvailability(doctorId)}?date=$dateStr',
      requiresAuth: false,
    );

    return (response['data']['available_slots'] as List)
        .map((slot) => slot.toString())
        .toList();
    */
  }

  Future<List<Specialization>> getSpecializations() async {
    // TEMPORARY: Mock data for development
    print('🏥 Mock getSpecializations');
    await Future.delayed(const Duration(milliseconds: 500));

    return [
      Specialization(
        id: 1,
        name: 'طب القلب',
        description: 'تشخيص وعلاج أمراض القلب والأوعية الدموية',
        doctorCount: 15,
      ),
      Specialization(
        id: 2,
        name: 'طب الأطفال',
        description: 'رعاية صحة الأطفال وحديثي الولادة',
        doctorCount: 20,
      ),
      Specialization(
        id: 3,
        name: 'طب الأسنان',
        description: 'علاج وتجميل الأسنان',
        doctorCount: 25,
      ),
      Specialization(
        id: 4,
        name: 'الجراحة العامة',
        description: 'العمليات الجراحية المختلفة',
        doctorCount: 12,
      ),
      Specialization(
        id: 5,
        name: 'طب العيون',
        description: 'تشخيص وعلاج أمراض العيون',
        doctorCount: 18,
      ),
    ];

    /* REAL API CODE
    final response = await _apiService.get(
      ApiConfig.doctorsSpecializations,
      requiresAuth: false,
    );

    return (response['data'] as List)
        .map((json) => Specialization.fromJson(json))
        .toList();
    */
  }

  Future<List<Doctor>> searchDoctors(String query) async {
    // TEMPORARY: Mock data - return all doctors
    print('🏥 Mock searchDoctors: $query');
    final result = await getDoctors();
    return result['doctors'] as List<Doctor>;

    /* REAL API CODE
    final response = await _apiService.get(
      '${ApiConfig.doctorsSearch}?q=$query',
      requiresAuth: false,
    );

    return (response['data'] as List)
        .map((json) => Doctor.fromJson(json))
        .toList();
    */
  }
}

