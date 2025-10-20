import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/app_colors.dart';
import '../../providers/chat_provider.dart';
import '../../models/chat_message.dart';
import 'chat_screen.dart';

class ConversationsScreen extends StatefulWidget {
  const ConversationsScreen({super.key});

  @override
  State<ConversationsScreen> createState() => _ConversationsScreenState();
}

class _ConversationsScreenState extends State<ConversationsScreen> {
  final ScrollController _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_onScroll);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<ChatProvider>().loadConversations(refresh: true);
      context.read<ChatProvider>().loadUnreadCount();
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
      context.read<ChatProvider>().loadMoreConversations();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('المحادثات'),
      ),
      body: Consumer<ChatProvider>(
        builder: (context, provider, child) {
          if (provider.isLoadingConversations && provider.conversations.isEmpty) {
            return const Center(child: CircularProgressIndicator());
          }

          if (provider.conversationsError != null && provider.conversations.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.error_outline, size: 64, color: Colors.grey[400]),
                  const SizedBox(height: 16),
                  Text('حدث خطأ', style: TextStyle(fontSize: 18, color: Colors.grey[600])),
                  const SizedBox(height: 8),
                  Text(
                    provider.conversationsError!,
                    style: TextStyle(fontSize: 14, color: Colors.grey[500]),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: () => provider.loadConversations(refresh: true),
                    child: const Text('إعادة المحاولة'),
                  ),
                ],
              ),
            );
          }

          if (provider.conversations.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.chat_bubble_outline, size: 64, color: Colors.grey[400]),
                  const SizedBox(height: 16),
                  Text(
                    'لا توجد محادثات بعد',
                    style: TextStyle(fontSize: 16, color: Colors.grey[600]),
                  ),
                ],
              ),
            );
          }

          return RefreshIndicator(
            onRefresh: () => provider.loadConversations(refresh: true),
            child: ListView.builder(
              controller: _scrollController,
              itemCount: provider.conversations.length +
                  (provider.isLoadingConversations ? 1 : 0),
              itemBuilder: (context, index) {
                if (index >= provider.conversations.length) {
                  return const Padding(
                    padding: EdgeInsets.all(16),
                    child: Center(child: CircularProgressIndicator()),
                  );
                }

                final conversation = provider.conversations[index];
                return _buildConversationCard(conversation);
              },
            ),
          );
        },
      ),
    );
  }

  Widget _buildConversationCard(Conversation conversation) {
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      child: ListTile(
        leading: Stack(
          children: [
            CircleAvatar(
              radius: 28,
              backgroundColor: AppColors.primaryLight,
              backgroundImage: conversation.userAvatar != null
                  ? NetworkImage(conversation.userAvatar!)
                  : null,
              child: conversation.userAvatar == null
                  ? const Icon(Icons.person, color: Colors.white, size: 28)
                  : null,
            ),
            if (conversation.unreadCount > 0)
              Positioned(
                right: 0,
                top: 0,
                child: Container(
                  padding: const EdgeInsets.all(4),
                  decoration: const BoxDecoration(
                    color: AppColors.error,
                    shape: BoxShape.circle,
                  ),
                  constraints: const BoxConstraints(
                    minWidth: 20,
                    minHeight: 20,
                  ),
                  child: Text(
                    conversation.unreadCount > 9 ? '9+' : '${conversation.unreadCount}',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 10,
                      fontWeight: FontWeight.bold,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ),
              ),
          ],
        ),
        title: Text(
          conversation.userName,
          style: TextStyle(
            fontWeight: conversation.unreadCount > 0
                ? FontWeight.bold
                : FontWeight.normal,
          ),
        ),
        subtitle: Text(
          conversation.lastMessage ?? 'لا توجد رسائل',
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
          style: TextStyle(
            color: conversation.unreadCount > 0
                ? AppColors.textPrimary
                : AppColors.textSecondary,
            fontWeight: conversation.unreadCount > 0
                ? FontWeight.w500
                : FontWeight.normal,
          ),
        ),
        trailing: Text(
          conversation.formattedLastMessageTime,
          style: TextStyle(
            fontSize: 12,
            color: conversation.unreadCount > 0
                ? AppColors.primary
                : AppColors.textSecondary,
            fontWeight: conversation.unreadCount > 0
                ? FontWeight.bold
                : FontWeight.normal,
          ),
        ),
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => ChatScreen(
                conversationId: conversation.id,
                userId: conversation.userId,
                userName: conversation.userName,
                userAvatar: conversation.userAvatar,
              ),
            ),
          );
        },
      ),
    );
  }
}

