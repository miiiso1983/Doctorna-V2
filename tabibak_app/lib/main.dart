import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:provider/provider.dart';
import 'config/app_colors.dart';
import 'providers/auth_provider.dart';
import 'providers/appointment_provider.dart';
import 'providers/health_post_provider.dart';
import 'providers/notification_provider.dart';
import 'providers/theme_provider.dart';
import 'providers/chat_provider.dart';
import 'screens/auth/login_screen.dart';
import 'screens/auth/register_screen.dart';
import 'screens/home/home_screen.dart';
import 'screens/appointments/appointments_screen.dart';
import 'screens/profile/profile_screen.dart';
import 'screens/profile/edit_profile_screen.dart';
import 'screens/health_posts/health_posts_screen.dart';
import 'screens/health_posts/health_post_details_screen.dart';
import 'screens/notifications/notifications_screen.dart';
import 'screens/reviews/doctor_reviews_screen.dart';
import 'screens/reviews/add_review_screen.dart';
import 'screens/chat/conversations_screen.dart';
import 'screens/chat/chat_screen.dart';
import 'services/api_service.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await ApiService().init();
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => ThemeProvider()),
        ChangeNotifierProvider(create: (_) => AuthProvider()..init()),
        ChangeNotifierProvider(create: (_) => AppointmentProvider()),
        ChangeNotifierProvider(create: (_) => HealthPostProvider()),
        ChangeNotifierProvider(create: (_) => NotificationProvider()),
        ChangeNotifierProvider(create: (_) => ChatProvider()),
      ],
      child: Consumer<ThemeProvider>(
        builder: (context, themeProvider, child) {
          return MaterialApp(
            title: 'طبيبك',
            debugShowCheckedModeBanner: false,
            locale: const Locale('ar', 'IQ'),
            supportedLocales: const [Locale('ar', 'IQ'), Locale('en', 'US')],
            localizationsDelegates: const [
              GlobalMaterialLocalizations.delegate,
              GlobalWidgetsLocalizations.delegate,
              GlobalCupertinoLocalizations.delegate,
            ],
            theme: themeProvider.lightTheme,
            darkTheme: themeProvider.darkTheme,
            themeMode: themeProvider.isDarkMode ? ThemeMode.dark : ThemeMode.light,
            initialRoute: '/',
            routes: {
              '/': (context) => const SplashScreen(),
              '/login': (context) => const LoginScreen(),
              '/register': (context) => const RegisterScreen(),
              '/home': (context) => const HomeScreen(),
              '/appointments': (context) => const AppointmentsScreen(),
              '/profile': (context) => const ProfileScreen(),
              '/edit-profile': (context) => const EditProfileScreen(),
              '/health-posts': (context) => const HealthPostsScreen(),
              '/notifications': (context) => const NotificationsScreen(),
              '/conversations': (context) => const ConversationsScreen(),
            },
            onGenerateRoute: (settings) {
              if (settings.name == '/health-post-details') {
                final postId = settings.arguments as int;
                return MaterialPageRoute(
                  builder: (context) => HealthPostDetailsScreen(postId: postId),
                );
              }
              if (settings.name == '/doctor-reviews') {
                final args = settings.arguments as Map<String, dynamic>;
                return MaterialPageRoute(
                  builder: (context) => DoctorReviewsScreen(
                    doctorId: args['doctorId'],
                    doctorName: args['doctorName'],
                  ),
                );
              }
              if (settings.name == '/add-review') {
                final args = settings.arguments as Map<String, dynamic>;
                return MaterialPageRoute(
                  builder: (context) => AddReviewScreen(
                    doctorId: args['doctorId'],
                    doctorName: args['doctorName'],
                  ),
                );
              }
              if (settings.name == '/chat') {
                final args = settings.arguments as Map<String, dynamic>;
                return MaterialPageRoute(
                  builder: (context) => ChatScreen(
                    conversationId: args['conversationId'],
                    userId: args['userId'],
                    userName: args['userName'],
                    userAvatar: args['userAvatar'],
                  ),
                );
              }
              return null;
            },
          );
        },
      ),
    );
  }
}

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});
  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  @override
  void initState() {
    super.initState();
    _checkAuth();
  }

  Future<void> _checkAuth() async {
    await Future.delayed(const Duration(seconds: 2));
    if (!mounted) return;

    final authProvider = context.read<AuthProvider>();

    if (authProvider.isAuthenticated) {
      Navigator.of(context).pushReplacementNamed('/home');
    } else {
      Navigator.of(context).pushReplacementNamed('/login');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(gradient: AppColors.primaryGradient),
        child: const Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.medical_services, size: 100, color: Colors.white),
              SizedBox(height: 24),
              Text('طبيبك', style: TextStyle(fontSize: 32, fontWeight: FontWeight.bold, color: Colors.white)),
              SizedBox(height: 8),
              Text('منصة حجز المواعيد الطبية', style: TextStyle(fontSize: 16, color: Colors.white70)),
              SizedBox(height: 40),
              CircularProgressIndicator(valueColor: AlwaysStoppedAnimation<Color>(Colors.white)),
            ],
          ),
        ),
      ),
    );
  }
}
