<?php
// Doctor Appointment Details View
?>
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>تفاصيل الموعد</h5>
        <a href="<?= $this->url('/doctor/appointments') ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-right me-1"></i>رجوع</a>
      </div>
      <div class="card-body">
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <div class="mb-2"><strong>المريض:</strong> <?= $this->escape($appointment['patient_name'] ?? '—') ?></div>
            <div class="mb-2"><strong>الهاتف:</strong> <?= $this->escape($appointment['patient_phone'] ?? '—') ?></div>
            <div class="mb-2"><strong>البريد:</strong> <?= $this->escape($appointment['patient_email'] ?? '—') ?></div>
          </div>
          <div class="col-md-6">
            <div class="mb-2"><strong>التاريخ:</strong> <?= $this->formatArabicDate($appointment['appointment_date']) ?></div>
            <div class="mb-2"><strong>الوقت:</strong> <?= date('h:i A', strtotime($appointment['appointment_time'])) ?></div>
            <div class="mb-2"><strong>الحالة:</strong> <span class="badge bg-<?= $this->getStatusBadge($appointment['status']) ?>"><?= $this->getStatusText($appointment['status']) ?></span></div>
          </div>
        </div>

        <?php if (!empty($appointment['symptoms'])): ?>
          <div class="mb-3">
            <strong>الأعراض المبلغ عنها:</strong>
            <div class="text-muted"><?= nl2br($this->escape($appointment['symptoms'])) ?></div>
          </div>
        <?php endif; ?>
        <?php if (!empty($appointment['notes'])): ?>
          <div class="mb-3">
            <strong>ملاحظات المريض:</strong>
            <div class="text-muted"><?= nl2br($this->escape($appointment['notes'])) ?></div>
          </div>
        <?php endif; ?>
        <?php if (!empty($appointment['doctor_notes'])): ?>
          <div class="mb-3">
            <strong>ملاحظات الطبيب:</strong>
            <div class="text-muted"><?= nl2br($this->escape($appointment['doctor_notes'])) ?></div>
          </div>
        <?php endif; ?>
        <?php if (!empty($appointment['prescription'])): ?>
          <div class="mb-3">
            <strong>الوصفة الطبية:</strong>
            <div class="text-muted"><?= nl2br($this->escape($appointment['prescription'])) ?></div>
          </div>
        <?php endif; ?>

        <div class="d-flex flex-wrap gap-2 mt-3">
          <?php if ($appointment['status'] === 'pending'): ?>
            <button class="btn btn-success" onclick="updateAppointmentStatus(<?= (int)$appointment['id'] ?>, 'accept')"><i class="fas fa-check me-1"></i>قبول</button>
            <button class="btn btn-danger" onclick="(function(id){document.getElementById('rejectAppointmentId').value=id;new bootstrap.Modal(document.getElementById('rejectModal')).show();})(<?= (int)$appointment['id'] ?>)"><i class="fas fa-times me-1"></i>رفض</button>
          <?php elseif ($appointment['status'] === 'confirmed'): ?>
            <button class="btn btn-primary" onclick="(function(id){const f=new FormData();f.append('appointment_id',id);f.append('csrf_token',document.querySelector('meta[name=\"csrf-token\"]').content);fetch('<?= $this->url('/doctor/complete-appointment') ?>',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'},body:f}).then(r=>r.json()).then(d=>{if(d.success) location.reload(); else alert(d.message||'خطأ');}).catch(()=>alert('حدث خطأ غير متوقع'));})(<?= (int)$appointment['id'] ?>)"><i class="fas fa-clipboard-check me-1"></i>إكمال</button>
          <?php endif; ?>
          <button class="btn btn-outline-secondary" onclick="Doctorna.chat.open(<?= (int)$appointment['id'] ?>)"><i class="fas fa-comments me-1"></i>دردشة</button>
          <button class="btn btn-outline-primary" onclick="Doctorna.video.open(<?= (int)$appointment['id'] ?>)"><i class="fas fa-video me-1"></i>مكالمة فيديو</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Reuse reject modal -->
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
        <button type="button" class="btn btn-danger" onclick="(function(){const id=document.getElementById('rejectAppointmentId').value;const reason=document.getElementById('rejectReason').value;if(!reason.trim()){alert('يرجى إدخال سبب الرفض');return;}updateAppointmentStatus(id,'reject',reason);bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();})();">رفض الموعد</button>
      </div>
    </div>
  </div>
</div>

