class ApiConfig {
  // Base URL - غيّر هذا إلى رابط السيرفر الخاص بك
  static const String baseUrl = 'https://phpstack-1510634-5888136.cloudwaysapps.com';
  static const String apiUrl = '$baseUrl/api';
  
  // API Endpoints
  static const String login = '$apiUrl/auth/login';
  static const String register = '$apiUrl/auth/register';
  static const String logout = '$apiUrl/auth/logout';
  static const String refreshToken = '$apiUrl/auth/refresh';
  static const String me = '$apiUrl/auth/me';
  
  // Users
  static const String userProfile = '$apiUrl/users/profile';
  static const String updateProfile = '$apiUrl/users/update';
  static const String changePassword = '$apiUrl/users/change-password';
  static const String uploadAvatar = '$apiUrl/users/upload-avatar';
  
  // Doctors
  static const String doctorsList = '$apiUrl/doctors/list';
  static const String doctorsSearch = '$apiUrl/doctors/search';
  static const String doctorsSpecializations = '$apiUrl/doctors/specializations';
  static String doctorDetails(int id) => '$apiUrl/doctors/$id';
  static String doctorAvailability(int id) => '$apiUrl/doctors/availability/$id';
  static const String specializations = '$apiUrl/doctors/specializations';
  
  // Appointments
  static const String appointmentsList = '$apiUrl/appointments/list';
  static const String createAppointment = '$apiUrl/appointments/create';
  static String appointmentDetails(int id) => '$apiUrl/appointments/details/$id';
  static String cancelAppointment(int id) => '$apiUrl/appointments/cancel/$id';
  static String confirmAppointment(int id) => '$apiUrl/appointments/confirm/$id';
  static String completeAppointment(int id) => '$apiUrl/appointments/complete/$id';
  
  // Health Posts
  static const String healthPostsList = '$apiUrl/health-posts/list';
  static String healthPostDetails(int id) => '$apiUrl/health-posts/$id';
  static const String createHealthPost = '$apiUrl/health-posts/create';
  static String updateHealthPost(int id) => '$apiUrl/health-posts/update/$id';
  static String deleteHealthPost(int id) => '$apiUrl/health-posts/delete/$id';
  
  // Notifications
  static const String notificationsList = '$apiUrl/notifications/list';
  static const String unreadCount = '$apiUrl/notifications/unread-count';
  static String markAsRead(int id) => '$apiUrl/notifications/$id/mark-read';
  static const String markAllAsRead = '$apiUrl/notifications/mark-all-read';

  // Reviews
  static String doctorReviews(int doctorId) => '$apiUrl/reviews/doctor/$doctorId';
  static String doctorRatingSummary(int doctorId) => '$apiUrl/reviews/doctor/$doctorId/summary';
  static const String addReview = '$apiUrl/reviews/add';
  static String deleteReview(int id) => '$apiUrl/reviews/delete/$id';
  static String myReview(int doctorId) => '$apiUrl/reviews/my-review/$doctorId';

  // Chat
  static const String chatConversations = '$apiUrl/chat/conversations';
  static String chatMessages(int conversationId) => '$apiUrl/chat/messages/$conversationId';
  static const String chatSend = '$apiUrl/chat/send';
  static String chatMarkRead(int conversationId) => '$apiUrl/chat/mark-read/$conversationId';
  static const String chatUnreadCount = '$apiUrl/chat/unread-count';
  static const String chatUpload = '$apiUrl/chat/upload';

  // Video Call
  static const String videoCallInitiate = '$apiUrl/video-call/initiate';
  static String videoCallAccept(int callId) => '$apiUrl/video-call/accept/$callId';
  static String videoCallReject(int callId) => '$apiUrl/video-call/reject/$callId';
  static String videoCallEnd(int callId) => '$apiUrl/video-call/end/$callId';
  static String videoCallDetails(int callId) => '$apiUrl/video-call/details/$callId';
  static const String videoCallHistory = '$apiUrl/video-call/history';

  // Timeouts
  static const Duration connectionTimeout = Duration(seconds: 30);
  static const Duration receiveTimeout = Duration(seconds: 30);
}

