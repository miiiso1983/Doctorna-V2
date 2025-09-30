<?php
// WebRTC room page (scaffold)
?>
<div class="container py-4">
  <h4 class="mb-3"><i class="fas fa-video me-2"></i> غرفة المكالمة - موعد #<?= (int)$appointmentId ?></h4>

  <div class="alert alert-info" role="alert">
    <?php if (REALTIME_DRIVER === 'none'): ?>
      <i class="fas fa-info-circle me-1"></i> لم يتم تفعيل الاتصال الفوري بعد. هذه صفحة تجريبية للواجهة وسيتم تفعيل المكالمة بعد اختيار مزود Realtime.
    <?php else: ?>
      <i class="fas fa-check-circle me-1"></i> الاتصال الفوري مفعل. استخدم الأزرار لبدء/إنهاء المكالمة.
    <?php endif; ?>
  </div>

  <div class="row g-3">
    <div class="col-12 col-lg-6">
      <div class="card">
        <div class="card-header">الفيديو المحلي</div>
        <div class="card-body">
          <video id="localVideo" autoplay playsinline muted style="width:100%;background:#000;border-radius:8px;"></video>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-6">
      <div class="card">
        <div class="card-header">فيديو الطرف الآخر</div>
        <div class="card-body">
          <video id="remoteVideo" autoplay playsinline style="width:100%;background:#000;border-radius:8px;"></video>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2 mt-3">
    <button id="btnStart" class="btn btn-success"><i class="fas fa-play"></i> بدء</button>
    <button id="btnEnd" class="btn btn-danger"><i class="fas fa-stop"></i> إنهاء</button>
    <a href="/patient/appointments" class="btn btn-outline-secondary">رجوع</a>
  </div>

  <div class="small text-muted mt-2">تنويه: هذه واجهة WebRTC أولية؛ سنربط الإشارات (signaling) عبر Realtime عند تفعيل المزود.</div>
</div>

<script>
(function(){
  const appointmentId = <?= (int)$appointmentId ?>;
  const localVideo = document.getElementById('localVideo');
  const remoteVideo = document.getElementById('remoteVideo');
  const btnStart = document.getElementById('btnStart');
  const btnEnd = document.getElementById('btnEnd');

  let pc;
  let localStream;

  async function startCall(){
    try {
      localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
      localVideo.srcObject = localStream;

      pc = new RTCPeerConnection({ iceServers: [{ urls: 'stun:stun.l.google.com:19302' }] });
      localStream.getTracks().forEach(track => pc.addTrack(track, localStream));
      pc.ontrack = (e) => { remoteVideo.srcObject = e.streams[0]; };
      pc.onicecandidate = (e) => {
        if (e.candidate) {
          // TODO: send ICE via realtime signaling
        }
      };

      // Notify backend that call started (for status)
      Doctorna.ajax.post(`/ajax/video/rooms/${appointmentId}/status`, { status: 'ongoing' });

      // TODO: create offer/answer via realtime signaling when provider is configured
    } catch (e) {
      console.error(e);
      alert('تعذر بدء المكالمة: ' + e.message);
    }
  }

  async function endCall(){
    try {
      if (pc) { pc.close(); pc = null; }
      if (localStream) { localStream.getTracks().forEach(t=>t.stop()); localStream = null; }
      Doctorna.ajax.post(`/ajax/video/rooms/${appointmentId}/status`, { status: 'ended' });
    } catch(e) {}
  }

  btnStart.addEventListener('click', startCall);
  btnEnd.addEventListener('click', endCall);
})();
</script>

