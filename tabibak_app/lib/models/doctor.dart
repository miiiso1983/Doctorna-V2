class Doctor {
  final int id;
  final int userId;
  final String name;
  final String? email;
  final String? phone;
  final String? avatar;
  final String? address;
  final String? city;
  final int? specializationId;
  final String? specializationName;
  final String? specializationNameEn;
  final String? specializationIcon;
  final String? specializationColor;
  final String? licenseNumber;
  final int? experienceYears;
  final String? biography;
  final String? education;
  final String? certifications;
  final String? languages;
  final double? consultationFee;
  final String? clinicName;
  final String? clinicAddress;
  final String? clinicPhone;
  final double? rating;
  final int? totalReviews;
  final String status;

  Doctor({
    required this.id,
    required this.userId,
    required this.name,
    this.email,
    this.phone,
    this.avatar,
    this.address,
    this.city,
    this.specializationId,
    this.specializationName,
    this.specializationNameEn,
    this.specializationIcon,
    this.specializationColor,
    this.licenseNumber,
    this.experienceYears,
    this.biography,
    this.education,
    this.certifications,
    this.languages,
    this.consultationFee,
    this.clinicName,
    this.clinicAddress,
    this.clinicPhone,
    this.rating,
    this.totalReviews,
    required this.status,
  });

  factory Doctor.fromJson(Map<String, dynamic> json) {
    return Doctor(
      id: json['id'] is String ? int.parse(json['id']) : json['id'],
      userId: json['user_id'] is String ? int.parse(json['user_id']) : json['user_id'],
      name: json['name'] ?? '',
      email: json['email'],
      phone: json['phone'],
      avatar: json['avatar'],
      address: json['address'],
      city: json['city'],
      specializationId: json['specialization_id'] is String 
          ? int.tryParse(json['specialization_id']) 
          : json['specialization_id'],
      specializationName: json['specialization_name'],
      specializationNameEn: json['specialization_name_en'],
      specializationIcon: json['specialization_icon'],
      specializationColor: json['specialization_color'],
      licenseNumber: json['license_number'],
      experienceYears: json['experience_years'] is String 
          ? int.tryParse(json['experience_years']) 
          : json['experience_years'],
      biography: json['biography'],
      education: json['education'],
      certifications: json['certifications'],
      languages: json['languages'],
      consultationFee: json['consultation_fee'] != null 
          ? double.tryParse(json['consultation_fee'].toString()) 
          : null,
      clinicName: json['clinic_name'],
      clinicAddress: json['clinic_address'],
      clinicPhone: json['clinic_phone'],
      rating: json['rating'] != null 
          ? double.tryParse(json['rating'].toString()) 
          : null,
      totalReviews: json['total_reviews'] is String 
          ? int.tryParse(json['total_reviews']) 
          : json['total_reviews'],
      status: json['status'] ?? 'pending',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'name': name,
      'email': email,
      'phone': phone,
      'avatar': avatar,
      'address': address,
      'city': city,
      'specialization_id': specializationId,
      'specialization_name': specializationName,
      'license_number': licenseNumber,
      'experience_years': experienceYears,
      'biography': biography,
      'consultation_fee': consultationFee,
      'clinic_name': clinicName,
      'clinic_address': clinicAddress,
      'rating': rating,
      'total_reviews': totalReviews,
      'status': status,
    };
  }

  String get displayRating => rating != null ? rating!.toStringAsFixed(1) : '0.0';
  String get displayFee => consultationFee != null ? '${consultationFee!.toStringAsFixed(0)} IQD' : 'غير محدد';
}

