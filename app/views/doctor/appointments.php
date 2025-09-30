<?php
// Doctor Appointments View
?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>إدارة المواعيد</h5>
                <form class="row g-2" method="GET" action="<?= $this->url('/doctor/appointments') ?>">
                    <div class="col-auto">
                        <select class="form-select" name="status">
                            <option value="">كل الحالات</option>
                            <?php foreach ($statuses as $key => $label): ?>
                                <option value="<?= $key ?>" <?= ($status === $key ? 'selected' : '') ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <input type="date" class="form-control" name="date" value="<?= $this->escape($date ?? '') ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i>تصفية
                        </button>
                        <a href="<?= $this->url('/doctor/appointments') ?>" class="btn btn-outline-secondary">
                            إعادة ضبط
                        </a>
                    </div>
                </form>
            </div>
            <div class="card-body">
                <?php if (!empty($appointments['data'])): ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>المريض</th>
                                    <th>التاريخ</th>
                                    <th>الوقت</th>
                                    <th>الحالة</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments['data'] as $appointment): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($appointment['patient_avatar'])): ?>
                                                    <img src="<?= $this->asset('uploads/profiles/' . $appointment['patient_avatar']) ?>" class="rounded-circle me-2" width="36" height="36" alt="">
                                                <?php else: ?>
                                                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;">
                                                        <i class="fas fa-user text-white"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <div class="fw-semibold"><?= $this->escape($appointment['patient_name'] ?? '—') ?></div>
                                                    <?php if (!empty($appointment['medical_history'])): ?>
                                                        <small class="text-muted"><?= $this->truncate($appointment['medical_history'], 40) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= $this->formatArabicDate($appointment['appointment_date']) ?></td>
                                        <td><?= date('h:i A', strtotime($appointment['appointment_time'])) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $this->getStatusBadge($appointment['status']) ?>">
                                                <?= $this->getStatusText($appointment['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <?php if ($appointment['status'] === 'pending'): ?>
                                                    <button class="btn btn-success" onclick="updateAppointmentStatus(<?= (int)$appointment['id'] ?>, 'accept')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button class="btn btn-danger" onclick="rejectAppointment(<?= (int)$appointment['id'] ?>)">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                <?php elseif ($appointment['status'] === 'confirmed'): ?>
                                                    <button class="btn btn-primary" onclick="completeAppointment(<?= (int)$appointment['id'] ?>)">
                                                        <i class="fas fa-clipboard-check"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <button class="btn btn-outline-secondary" title="دردشة" onclick="Doctorna.chat.open(<?= (int)$appointment['id'] ?>)">
                                                    <i class="fas fa-comments"></i>
                                                </button>
                                                <button class="btn btn-outline-primary" title="مكالمة فيديو" onclick="Doctorna.video.open(<?= (int)$appointment['id'] ?>)">
                                                    <i class="fas fa-video"></i>
                                                </button>
                                                <a class="btn btn-outline-info" href="<?= $this->url('/doctor/appointments/' . (int)$appointment['id']) ?>">
                                                    تفاصيل
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <?= $this->paginate($appointments) ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">لا توجد مواعيد مطابقة للفلتر الحالي</h5>
                        <p class="text-muted mb-0">جرّب تغيير عوامل التصفية بالأعلى</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modals reused from dashboard -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">رفض الموعد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="rejectForm">
                    <input type="hidden" id="rejectAppointmentId">
                    <div class="mb-3">
                        <label for="rejectReason" class="form-label">سبب الرفض</label>
                        <textarea class="form-control" id="rejectReason" rows="3" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-danger" onclick="confirmReject()">رفض الموعد</button>
            </div>
        </div>
    </div>
</div>

<script>
function rejectAppointment(appointmentId) {
    document.getElementById('rejectAppointmentId').value = appointmentId;
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}
function confirmReject() {
    const appointmentId = document.getElementById('rejectAppointmentId').value;
    const reason = document.getElementById('rejectReason').value;
    if (!reason.trim()) { alert('يرجى إدخال سبب الرفض'); return; }
    updateAppointmentStatus(appointmentId, 'reject', reason);
    bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
}
function completeAppointment(appointmentId) {
    // Reuse modal from dashboard if present; otherwise send directly without notes
    const notes = '';
    const prescription = '';
    const formData = new FormData();
    formData.append('appointment_id', appointmentId);
    formData.append('notes', notes);
    formData.append('prescription', prescription);

    formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);
    fetch('<?= $this->url('/doctor/complete-appointment') ?>', { method: 'POST', body: formData })
        .then(r => r.json()).then(d => { if (d.success) location.reload(); else alert(d.message||'خطأ'); })
        .catch(() => alert('حدث خطأ غير متوقع'));
}
</script>



<!-- Chat Modal -->
<div class="modal fade" id="chatModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-comments me-2"></i>الدردشة</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="chatMessages" style="max-height:50vh; overflow:auto;"></div>
      </div>
      <div class="modal-footer">
        <form id="chatForm" class="w-100 d-flex gap-2" onsubmit="return Doctorna.chat.send(event)">
          <input type="hidden" id="chatAppointmentId">
          <input type="text" id="chatInput" class="form-control" placeholder="اكتب رسالتك..." required>
          <button class="btn btn-primary" type="submit"><i class="fas fa-paper-plane"></i></button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Video Modal -->
<div class="modal fade" id="videoModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-video me-2"></i>مكالمة فيديو</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="videoInfo" class="small text-muted">سيتم إنشاء غرفة المكالمة...</div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-success" id="btnStartCall" onclick="Doctorna.video.start()"><i class="fas fa-play"></i> بدء</button>
        <button class="btn btn-danger" id="btnEndCall" onclick="Doctorna.video.end()"><i class="fas fa-stop"></i> إنهاء</button>
      </div>
    </div>
  </div>
</div>
