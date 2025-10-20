import 'package:flutter/material.dart';
import '../../models/video_call.dart';
import '../../services/video_call_service.dart';
import 'video_call_screen.dart';

class IncomingCallScreen extends StatefulWidget {
  final VideoCall call;

  const IncomingCallScreen({
    super.key,
    required this.call,
  });

  @override
  State<IncomingCallScreen> createState() => _IncomingCallScreenState();
}

class _IncomingCallScreenState extends State<IncomingCallScreen> {
  final VideoCallService _videoCallService = VideoCallService();
  bool _isProcessing = false;

  Future<void> _acceptCall() async {
    if (_isProcessing) return;
    
    setState(() {
      _isProcessing = true;
    });

    try {
      final updatedCall = await _videoCallService.acceptCall(widget.call.id);
      
      if (mounted) {
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(
            builder: (context) => VideoCallScreen(
              call: updatedCall,
              isCaller: false,
            ),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('فشل قبول المكالمة: $e'),
            backgroundColor: Colors.red,
          ),
        );
        setState(() {
          _isProcessing = false;
        });
      }
    }
  }

  Future<void> _rejectCall() async {
    if (_isProcessing) return;
    
    setState(() {
      _isProcessing = true;
    });

    try {
      await _videoCallService.rejectCall(widget.call.id);
      
      if (mounted) {
        Navigator.of(context).pop();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('فشل رفض المكالمة: $e'),
            backgroundColor: Colors.red,
          ),
        );
        setState(() {
          _isProcessing = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      body: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [
              Colors.blue.shade900,
              Colors.black,
            ],
          ),
        ),
        child: SafeArea(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              // Top section
              Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  children: [
                    const SizedBox(height: 40),
                    const Text(
                      'مكالمة فيديو واردة',
                      style: TextStyle(
                        color: Colors.white70,
                        fontSize: 18,
                      ),
                    ),
                    const SizedBox(height: 40),
                    // Caller avatar
                    Container(
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        border: Border.all(
                          color: Colors.white.withOpacity(0.3),
                          width: 3,
                        ),
                      ),
                      child: CircleAvatar(
                        radius: 80,
                        backgroundImage: widget.call.callerAvatar != null
                            ? NetworkImage(widget.call.callerAvatar!)
                            : null,
                        child: widget.call.callerAvatar == null
                            ? const Icon(
                                Icons.person,
                                size: 80,
                                color: Colors.white,
                              )
                            : null,
                      ),
                    ),
                    const SizedBox(height: 30),
                    // Caller name
                    Text(
                      widget.call.callerName,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 28,
                        fontWeight: FontWeight.bold,
                      ),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 10),
                    const Text(
                      'يريد إجراء مكالمة فيديو معك',
                      style: TextStyle(
                        color: Colors.white70,
                        fontSize: 16,
                      ),
                      textAlign: TextAlign.center,
                    ),
                  ],
                ),
              ),

              // Bottom buttons
              Padding(
                padding: const EdgeInsets.all(40),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                  children: [
                    // Reject button
                    Column(
                      children: [
                        Container(
                          decoration: BoxDecoration(
                            color: Colors.red,
                            shape: BoxShape.circle,
                            boxShadow: [
                              BoxShadow(
                                color: Colors.red.withOpacity(0.5),
                                blurRadius: 20,
                                spreadRadius: 5,
                              ),
                            ],
                          ),
                          child: IconButton(
                            icon: const Icon(Icons.call_end),
                            color: Colors.white,
                            iconSize: 40,
                            padding: const EdgeInsets.all(20),
                            onPressed: _isProcessing ? null : _rejectCall,
                          ),
                        ),
                        const SizedBox(height: 10),
                        const Text(
                          'رفض',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 16,
                          ),
                        ),
                      ],
                    ),

                    // Accept button
                    Column(
                      children: [
                        Container(
                          decoration: BoxDecoration(
                            color: Colors.green,
                            shape: BoxShape.circle,
                            boxShadow: [
                              BoxShadow(
                                color: Colors.green.withOpacity(0.5),
                                blurRadius: 20,
                                spreadRadius: 5,
                              ),
                            ],
                          ),
                          child: IconButton(
                            icon: const Icon(Icons.videocam),
                            color: Colors.white,
                            iconSize: 40,
                            padding: const EdgeInsets.all(20),
                            onPressed: _isProcessing ? null : _acceptCall,
                          ),
                        ),
                        const SizedBox(height: 10),
                        const Text(
                          'قبول',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 16,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

