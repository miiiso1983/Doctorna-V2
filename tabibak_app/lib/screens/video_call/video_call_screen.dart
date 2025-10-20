import 'dart:async';
import 'package:flutter/material.dart';
import 'package:agora_rtc_engine/agora_rtc_engine.dart';
import 'package:permission_handler/permission_handler.dart';
import '../../models/video_call.dart';
import '../../services/video_call_service.dart';

class VideoCallScreen extends StatefulWidget {
  final VideoCall call;
  final bool isCaller;

  const VideoCallScreen({
    super.key,
    required this.call,
    required this.isCaller,
  });

  @override
  State<VideoCallScreen> createState() => _VideoCallScreenState();
}

class _VideoCallScreenState extends State<VideoCallScreen> {
  final VideoCallService _videoCallService = VideoCallService();
  
  // Agora App ID - يجب استبداله بـ App ID الخاص بك من Agora
  static const String appId = 'YOUR_AGORA_APP_ID';
  
  RtcEngine? _engine;
  int? _remoteUid;
  bool _localUserJoined = false;
  bool _isMuted = false;
  bool _isCameraOff = false;
  bool _isSpeakerOn = true;
  Timer? _callTimer;
  int _callDuration = 0;

  @override
  void initState() {
    super.initState();
    _initAgora();
  }

  Future<void> _initAgora() async {
    // Request permissions
    await [Permission.microphone, Permission.camera].request();

    // Create Agora engine
    _engine = createAgoraRtcEngine();
    await _engine!.initialize(const RtcEngineContext(
      appId: appId,
      channelProfile: ChannelProfileType.channelProfileCommunication,
    ));

    _engine!.registerEventHandler(
      RtcEngineEventHandler(
        onJoinChannelSuccess: (RtcConnection connection, int elapsed) {
          debugPrint('Local user ${connection.localUid} joined');
          setState(() {
            _localUserJoined = true;
          });
          _startCallTimer();
        },
        onUserJoined: (RtcConnection connection, int remoteUid, int elapsed) {
          debugPrint('Remote user $remoteUid joined');
          setState(() {
            _remoteUid = remoteUid;
          });
        },
        onUserOffline: (RtcConnection connection, int remoteUid,
            UserOfflineReasonType reason) {
          debugPrint('Remote user $remoteUid left channel');
          setState(() {
            _remoteUid = null;
          });
          _endCall();
        },
        onLeaveChannel: (RtcConnection connection, RtcStats stats) {
          setState(() {
            _localUserJoined = false;
            _remoteUid = null;
          });
        },
      ),
    );

    await _engine!.setClientRole(role: ClientRoleType.clientRoleBroadcaster);
    await _engine!.enableVideo();
    await _engine!.startPreview();

    // Join channel
    await _engine!.joinChannel(
      token: widget.call.token,
      channelId: widget.call.channelName,
      uid: 0,
      options: const ChannelMediaOptions(),
    );
  }

  void _startCallTimer() {
    _callTimer = Timer.periodic(const Duration(seconds: 1), (timer) {
      setState(() {
        _callDuration++;
      });
    });
  }

  String _formatDuration(int seconds) {
    final hours = seconds ~/ 3600;
    final minutes = (seconds % 3600) ~/ 60;
    final secs = seconds % 60;
    
    if (hours > 0) {
      return '${hours.toString().padLeft(2, '0')}:${minutes.toString().padLeft(2, '0')}:${secs.toString().padLeft(2, '0')}';
    }
    return '${minutes.toString().padLeft(2, '0')}:${secs.toString().padLeft(2, '0')}';
  }

  Future<void> _toggleMute() async {
    setState(() {
      _isMuted = !_isMuted;
    });
    await _engine?.muteLocalAudioStream(_isMuted);
  }

  Future<void> _toggleCamera() async {
    setState(() {
      _isCameraOff = !_isCameraOff;
    });
    await _engine?.muteLocalVideoStream(_isCameraOff);
  }

  Future<void> _switchCamera() async {
    await _engine?.switchCamera();
  }

  Future<void> _toggleSpeaker() async {
    setState(() {
      _isSpeakerOn = !_isSpeakerOn;
    });
    await _engine?.setEnableSpeakerphone(_isSpeakerOn);
  }

  Future<void> _endCall() async {
    _callTimer?.cancel();
    
    try {
      await _videoCallService.endCall(widget.call.id);
    } catch (e) {
      debugPrint('Error ending call: $e');
    }

    if (mounted) {
      Navigator.of(context).pop();
    }
  }

  @override
  void dispose() {
    _callTimer?.cancel();
    _dispose();
    super.dispose();
  }

  Future<void> _dispose() async {
    await _engine?.leaveChannel();
    await _engine?.release();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      body: Stack(
        children: [
          // Remote video
          Center(
            child: _remoteUid != null
                ? AgoraVideoView(
                    controller: VideoViewController.remote(
                      rtcEngine: _engine!,
                      canvas: VideoCanvas(uid: _remoteUid),
                      connection: RtcConnection(channelId: widget.call.channelName),
                    ),
                  )
                : Container(
                    color: Colors.black,
                    child: Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          CircleAvatar(
                            radius: 60,
                            backgroundImage: widget.isCaller && widget.call.receiverAvatar != null
                                ? NetworkImage(widget.call.receiverAvatar!)
                                : !widget.isCaller && widget.call.callerAvatar != null
                                    ? NetworkImage(widget.call.callerAvatar!)
                                    : null,
                            child: (widget.isCaller && widget.call.receiverAvatar == null) ||
                                    (!widget.isCaller && widget.call.callerAvatar == null)
                                ? const Icon(Icons.person, size: 60, color: Colors.white)
                                : null,
                          ),
                          const SizedBox(height: 20),
                          Text(
                            widget.isCaller ? widget.call.receiverName : widget.call.callerName,
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 24,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 10),
                          Text(
                            _remoteUid == null ? 'في انتظار الاتصال...' : '',
                            style: const TextStyle(
                              color: Colors.white70,
                              fontSize: 16,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
          ),

          // Local video (small preview)
          if (_localUserJoined && !_isCameraOff)
            Positioned(
              top: 50,
              right: 20,
              child: ClipRRect(
                borderRadius: BorderRadius.circular(12),
                child: SizedBox(
                  width: 120,
                  height: 160,
                  child: AgoraVideoView(
                    controller: VideoViewController(
                      rtcEngine: _engine!,
                      canvas: const VideoCanvas(uid: 0),
                    ),
                  ),
                ),
              ),
            ),

          // Top bar with duration
          Positioned(
            top: 0,
            left: 0,
            right: 0,
            child: Container(
              padding: const EdgeInsets.symmetric(vertical: 40, horizontal: 20),
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [
                    Colors.black.withOpacity(0.7),
                    Colors.transparent,
                  ],
                ),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    _formatDuration(_callDuration),
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.flip_camera_ios, color: Colors.white),
                    onPressed: _switchCamera,
                  ),
                ],
              ),
            ),
          ),

          // Bottom controls
          Positioned(
            bottom: 0,
            left: 0,
            right: 0,
            child: Container(
              padding: const EdgeInsets.symmetric(vertical: 30, horizontal: 20),
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.bottomCenter,
                  end: Alignment.topCenter,
                  colors: [
                    Colors.black.withOpacity(0.7),
                    Colors.transparent,
                  ],
                ),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                children: [
                  _buildControlButton(
                    icon: _isMuted ? Icons.mic_off : Icons.mic,
                    onPressed: _toggleMute,
                    isActive: !_isMuted,
                  ),
                  _buildControlButton(
                    icon: _isCameraOff ? Icons.videocam_off : Icons.videocam,
                    onPressed: _toggleCamera,
                    isActive: !_isCameraOff,
                  ),
                  _buildControlButton(
                    icon: _isSpeakerOn ? Icons.volume_up : Icons.volume_off,
                    onPressed: _toggleSpeaker,
                    isActive: _isSpeakerOn,
                  ),
                  _buildControlButton(
                    icon: Icons.call_end,
                    onPressed: _endCall,
                    backgroundColor: Colors.red,
                    iconColor: Colors.white,
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildControlButton({
    required IconData icon,
    required VoidCallback onPressed,
    bool isActive = true,
    Color? backgroundColor,
    Color? iconColor,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: backgroundColor ?? (isActive ? Colors.white.withOpacity(0.3) : Colors.white.withOpacity(0.1)),
        shape: BoxShape.circle,
      ),
      child: IconButton(
        icon: Icon(icon),
        color: iconColor ?? Colors.white,
        iconSize: 30,
        onPressed: onPressed,
      ),
    );
  }
}

