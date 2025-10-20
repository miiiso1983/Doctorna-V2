class Specialization {
  final int id;
  final String name;
  final String? description;
  final int doctorCount;

  Specialization({
    required this.id,
    required this.name,
    this.description,
    this.doctorCount = 0,
  });

  factory Specialization.fromJson(Map<String, dynamic> json) {
    return Specialization(
      id: json['id'] ?? 0,
      name: json['name'] ?? '',
      description: json['description'],
      doctorCount: json['doctor_count'] ?? 0,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'description': description,
      'doctor_count': doctorCount,
    };
  }
}

