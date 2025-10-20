class User {
  final int id;
  final String name;
  final String email;
  final String? phone;
  final String role;
  final String? avatar;
  final String? address;
  final String? city;
  final String? country;
  final double? latitude;
  final double? longitude;
  final String status;
  final DateTime? createdAt;
  final DateTime? lastLogin;

  User({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    required this.role,
    this.avatar,
    this.address,
    this.city,
    this.country,
    this.latitude,
    this.longitude,
    required this.status,
    this.createdAt,
    this.lastLogin,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] is String ? int.parse(json['id']) : json['id'],
      name: json['name'] ?? '',
      email: json['email'] ?? '',
      phone: json['phone'],
      role: json['role'] ?? 'patient',
      avatar: json['avatar'],
      address: json['address'],
      city: json['city'],
      country: json['country'],
      latitude: json['latitude'] != null ? double.tryParse(json['latitude'].toString()) : null,
      longitude: json['longitude'] != null ? double.tryParse(json['longitude'].toString()) : null,
      status: json['status'] ?? 'active',
      createdAt: json['created_at'] != null ? DateTime.tryParse(json['created_at']) : null,
      lastLogin: json['last_login'] != null ? DateTime.tryParse(json['last_login']) : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'phone': phone,
      'role': role,
      'avatar': avatar,
      'address': address,
      'city': city,
      'country': country,
      'latitude': latitude,
      'longitude': longitude,
      'status': status,
      'created_at': createdAt?.toIso8601String(),
      'last_login': lastLogin?.toIso8601String(),
    };
  }

  bool get isDoctor => role == 'doctor';
  bool get isPatient => role == 'patient';
  bool get isAdmin => role == 'super_admin';
}

