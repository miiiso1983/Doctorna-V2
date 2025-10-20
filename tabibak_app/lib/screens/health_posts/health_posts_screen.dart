import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/app_colors.dart';
import '../../providers/health_post_provider.dart';
import '../../widgets/health_post_card.dart';

class HealthPostsScreen extends StatefulWidget {
  const HealthPostsScreen({super.key});

  @override
  State<HealthPostsScreen> createState() => _HealthPostsScreenState();
}

class _HealthPostsScreenState extends State<HealthPostsScreen> {
  final ScrollController _scrollController = ScrollController();
  String? _selectedCategory;

  final List<Map<String, String>> _categories = [
    {'value': '', 'label': 'الكل'},
    {'value': 'general', 'label': 'عام'},
    {'value': 'nutrition', 'label': 'تغذية'},
    {'value': 'fitness', 'label': 'لياقة'},
    {'value': 'mental_health', 'label': 'صحة نفسية'},
    {'value': 'diseases', 'label': 'أمراض'},
    {'value': 'prevention', 'label': 'وقاية'},
  ];

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_onScroll);
    
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<HealthPostProvider>().loadPosts(refresh: true);
    });
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollController.position.pixels >=
        _scrollController.position.maxScrollExtent - 200) {
      final provider = context.read<HealthPostProvider>();
      if (!provider.isLoading && provider.hasMore) {
        provider.loadMore();
      }
    }
  }

  void _onCategoryChanged(String? category) {
    setState(() {
      _selectedCategory = category == '' ? null : category;
    });
    context.read<HealthPostProvider>().loadPosts(
      category: _selectedCategory,
      refresh: true,
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('المنشورات الصحية'),
      ),
      body: Column(
        children: [
          // Category Filter
          Container(
            height: 50,
            padding: const EdgeInsets.symmetric(vertical: 8),
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 16),
              itemCount: _categories.length,
              itemBuilder: (context, index) {
                final category = _categories[index];
                final isSelected = _selectedCategory == category['value'] ||
                    (_selectedCategory == null && category['value'] == '');

                return Padding(
                  padding: const EdgeInsets.only(left: 8),
                  child: FilterChip(
                    label: Text(category['label']!),
                    selected: isSelected,
                    onSelected: (_) => _onCategoryChanged(category['value']),
                    backgroundColor: Colors.grey[200],
                    selectedColor: AppColors.primary,
                    labelStyle: TextStyle(
                      color: isSelected ? Colors.white : AppColors.textPrimary,
                    ),
                  ),
                );
              },
            ),
          ),

          const Divider(height: 1),

          // Posts List
          Expanded(
            child: Consumer<HealthPostProvider>(
              builder: (context, provider, child) {
                if (provider.isLoading && provider.posts.isEmpty) {
                  return const Center(child: CircularProgressIndicator());
                }

                if (provider.error != null && provider.posts.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.error_outline, size: 64, color: Colors.grey[400]),
                        const SizedBox(height: 16),
                        Text(
                          'حدث خطأ',
                          style: TextStyle(fontSize: 18, color: Colors.grey[600]),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          provider.error!,
                          style: TextStyle(fontSize: 14, color: Colors.grey[500]),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: () {
                            provider.loadPosts(refresh: true);
                          },
                          child: const Text('إعادة المحاولة'),
                        ),
                      ],
                    ),
                  );
                }

                if (provider.posts.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.article_outlined,
                          size: 80,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'لا توجد منشورات',
                          style: TextStyle(fontSize: 18, color: Colors.grey[600]),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          'لم يتم نشر أي مقالات بعد',
                          style: TextStyle(fontSize: 14, color: Colors.grey[500]),
                        ),
                      ],
                    ),
                  );
                }

                return RefreshIndicator(
                  onRefresh: () async {
                    await provider.loadPosts(
                      category: _selectedCategory,
                      refresh: true,
                    );
                  },
                  child: ListView.builder(
                    controller: _scrollController,
                    itemCount: provider.posts.length + (provider.isLoading ? 1 : 0),
                    itemBuilder: (context, index) {
                      if (index == provider.posts.length) {
                        return const Center(
                          child: Padding(
                            padding: EdgeInsets.all(16),
                            child: CircularProgressIndicator(),
                          ),
                        );
                      }

                      final post = provider.posts[index];
                      return HealthPostCard(
                        post: post,
                        onTap: () {
                          Navigator.pushNamed(
                            context,
                            '/health-post-details',
                            arguments: post.id,
                          );
                        },
                      );
                    },
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}

