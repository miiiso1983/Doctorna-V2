class Payment {
  final int id;
  final int appointmentId;
  final int userId;
  final double amount;
  final String currency;
  final String status;
  final String paymentMethod;
  final String? transactionId;
  final String? paymentGateway;
  final DateTime createdAt;
  final DateTime? paidAt;

  Payment({
    required this.id,
    required this.appointmentId,
    required this.userId,
    required this.amount,
    required this.currency,
    required this.status,
    required this.paymentMethod,
    this.transactionId,
    this.paymentGateway,
    required this.createdAt,
    this.paidAt,
  });

  factory Payment.fromJson(Map<String, dynamic> json) {
    return Payment(
      id: json['id'] is String ? int.parse(json['id']) : json['id'],
      appointmentId: json['appointment_id'] is String 
          ? int.parse(json['appointment_id']) 
          : json['appointment_id'],
      userId: json['user_id'] is String ? int.parse(json['user_id']) : json['user_id'],
      amount: json['amount'] is String 
          ? double.parse(json['amount']) 
          : (json['amount'] as num).toDouble(),
      currency: json['currency'] ?? 'IQD',
      status: json['status'] ?? 'pending',
      paymentMethod: json['payment_method'] ?? '',
      transactionId: json['transaction_id'],
      paymentGateway: json['payment_gateway'],
      createdAt: json['created_at'] != null 
          ? DateTime.parse(json['created_at']) 
          : DateTime.now(),
      paidAt: json['paid_at'] != null ? DateTime.parse(json['paid_at']) : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'appointment_id': appointmentId,
      'user_id': userId,
      'amount': amount,
      'currency': currency,
      'status': status,
      'payment_method': paymentMethod,
      'transaction_id': transactionId,
      'payment_gateway': paymentGateway,
      'created_at': createdAt.toIso8601String(),
      'paid_at': paidAt?.toIso8601String(),
    };
  }

  bool get isPending => status == 'pending';
  bool get isPaid => status == 'paid';
  bool get isFailed => status == 'failed';
  bool get isRefunded => status == 'refunded';

  String get statusText {
    switch (status) {
      case 'pending':
        return 'قيد الانتظار';
      case 'paid':
        return 'مدفوع';
      case 'failed':
        return 'فشل';
      case 'refunded':
        return 'مسترد';
      default:
        return status;
    }
  }

  String get formattedAmount {
    return '${amount.toStringAsFixed(0)} $currency';
  }
}

