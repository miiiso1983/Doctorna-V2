import 'package:flutter/material.dart';
import '../../config/app_colors.dart';
import '../../models/doctor.dart';
import '../../services/doctor_service.dart';
import '../../widgets/doctor_card.dart';
import 'doctor_details_screen.dart';

class DoctorsListScreen extends StatefulWidget {
  final int? specializationId;
  final String? specializationName;
  final String? searchQuery;

  const DoctorsListScreen({
    super.key,
    this.specializationId,
    this.specializationName,
    this.searchQuery,
  });

  @override
  State<DoctorsListScreen> createState() => _DoctorsListScreenState();
}

class _DoctorsListScreenState extends State<DoctorsListScreen> {
  final DoctorService _doctorService = DoctorService();
  final ScrollController _scrollController = ScrollController();
  final TextEditingController _searchController = TextEditingController();

  List<Doctor> _doctors = [];
  bool _isLoading = true;
  bool _isLoadingMore = false;
  int _currentPage = 1;
  int _totalPages = 1;
  String? _searchQuery;

  @override
  void initState() {
    super.initState();
    _searchQuery = widget.searchQuery;
    if (_searchQuery != null) {
      _searchController.text = _searchQuery!;
    }
    _loadDoctors();
    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
    _scrollController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollController.position.pixels >=
        _scrollController.position.maxScrollExtent - 200) {
      if (!_isLoadingMore && _currentPage < _totalPages) {
        _loadMore();
      }
    }
  }

  Future<void> _loadDoctors() async {
    setState(() {
      _isLoading = true;
      _currentPage = 1;
    });

    try {
      final result = await _doctorService.getDoctors(
        page: 1,
        specializationId: widget.specializationId,
        search: _searchQuery,
      );

      setState(() {
        _doctors = result['doctors'];
        _totalPages = result['pages'];
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _isLoading = false;
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('خطأ في تحميل الأطباء: $e'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }

  Future<void> _loadMore() async {
    setState(() {
      _isLoadingMore = true;
    });

    try {
      final result = await _doctorService.getDoctors(
        page: _currentPage + 1,
        specializationId: widget.specializationId,
        search: _searchQuery,
      );

      setState(() {
        _doctors.addAll(result['doctors']);
        _currentPage++;
        _isLoadingMore = false;
      });
    } catch (e) {
      setState(() {
        _isLoadingMore = false;
      });
    }
  }

  void _performSearch(String query) {
    setState(() {
      _searchQuery = query.isEmpty ? null : query;
    });
    _loadDoctors();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.specializationName ?? 'الأطباء'),
      ),
      body: Column(
        children: [
          // Search Bar
          Padding(
            padding: const EdgeInsets.all(16),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'ابحث عن طبيب...',
                prefixIcon: const Icon(Icons.search),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear),
                        onPressed: () {
                          _searchController.clear();
                          _performSearch('');
                        },
                      )
                    : null,
              ),
              onSubmitted: _performSearch,
            ),
          ),

          // Doctors List
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _doctors.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(
                              Icons.person_search,
                              size: 80,
                              color: Colors.grey[400],
                            ),
                            const SizedBox(height: 16),
                            Text(
                              'لا يوجد أطباء',
                              style: TextStyle(
                                fontSize: 18,
                                color: Colors.grey[600],
                              ),
                            ),
                          ],
                        ),
                      )
                    : RefreshIndicator(
                        onRefresh: _loadDoctors,
                        child: ListView.builder(
                          controller: _scrollController,
                          itemCount: _doctors.length + (_isLoadingMore ? 1 : 0),
                          itemBuilder: (context, index) {
                            if (index == _doctors.length) {
                              return const Center(
                                child: Padding(
                                  padding: EdgeInsets.all(16),
                                  child: CircularProgressIndicator(),
                                ),
                              );
                            }

                            final doctor = _doctors[index];
                            return DoctorCard(
                              doctor: doctor,
                              onTap: () {
                                Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                    builder: (context) => DoctorDetailsScreen(
                                      doctorId: doctor.id,
                                    ),
                                  ),
                                );
                              },
                            );
                          },
                        ),
                      ),
          ),
        ],
      ),
    );
  }
}

