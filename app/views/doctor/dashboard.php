<!-- Welcome Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <?php
                            $__rawName = $this->user()['name'] ?? '';
                            $__hasTitle = preg_match('/^(\s*(د\.|دكتور|Dr\.|Doctor))\s*/u', $__rawName) === 1;
                            $__displayName = $__hasTitle ? $__rawName : ('د. ' . $__rawName);
                        ?>
                        <h4 class="mb-1">مرحباً <?= $this->escape($__displayName) ?></h4>
                        <p class="mb-0">
                            <?= $this->escape($doctor['specialization_name'] ?? 'طبيب عام') ?> | 
                            التقييم: <?= number_format($doctor['rating'], 1) ?> ⭐ 
                            (<?= $doctor['total_reviews'] ?> تقييم)
                        </p>
                    </div>
                    <div class="text-end">
                        <h6 class="mb-1"><?= $this->formatArabicDate(date('Y-m-d')) ?></h6>
                        <p class="mb-0" id="current-time"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card" style="--bg-color: #28a745; --bg-color-end: #20c997;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-white-50 mb-1">مواعيد اليوم</h6>
                        <h2 class="mb-0 text-white"><?= $appointment_stats['today'] ?></h2>
                        <small class="text-white-50">
                            <?= count($today_appointments) ?> موعد مجدول
                        </small>
                    </div>
                    <div class="text-white">
                        <i class="fas fa-calendar-day fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card" style="--bg-color: #ffc107; --bg-color-end: #fd7e14;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-white-50 mb-1">في الانتظار</h6>
                        <h2 class="mb-0 text-white"><?= $appointment_stats['pending'] ?></h2>
                        <small class="text-white-50">
                            يحتاج موافقة
                        </small>
                    </div>
                    <div class="text-white">
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card" style="--bg-color: #17a2b8; --bg-color-end: #6f42c1;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-white-50 mb-1">هذا الأسبوع</h6>
                        <h2 class="mb-0 text-white"><?= $appointment_stats['this_week'] ?></h2>
                        <small class="text-white-50">
                            موعد مجدول
                        </small>
                    </div>
                    <div class="text-white">
                        <i class="fas fa-calendar-week fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card" style="--bg-color: #6f42c1; --bg-color-end: #e83e8c;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-white-50 mb-1">إجمالي المواعيد</h6>
                        <h2 class="mb-0 text-white"><?= $appointment_stats['total'] ?></h2>
                        <small class="text-white-50">
                            <?= $appointment_stats['completed'] ?> مكتمل
                        </small>
                    </div>
                    <div class="text-white">
                        <i class="fas fa-chart-bar fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Today's Appointments -->
<div class="row mb-4">
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-day me-2"></i>
                    مواعيد اليوم
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($today_appointments)): ?>
                    <div class="row">
                        <?php foreach ($today_appointments as $appointment): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card appointment-card" 
                                     style="--status-color: <?= $this->getStatusColor($appointment['status']) ?>">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0"><?= $this->escape($appointment['patient_name']) ?></h6>
                                            <span class="badge bg-<?= $this->getStatusBadge($appointment['status']) ?>">
                                                <?= $this->getStatusText($appointment['status']) ?>
                                            </span>
                                        </div>
                                        <p class="text-muted mb-2">
                                            <i class="fas fa-clock me-1"></i>
                                            <?= date('h:i A', strtotime($appointment['appointment_time'])) ?>
                                        </p>
                                        <?php if (!empty($appointment['symptoms'])): ?>
                                            <p class="small mb-2">
                                                <strong>الأعراض:</strong> <?= $this->escape($appointment['symptoms']) ?>
                                            </p>
                                        <?php endif; ?>
                                        <div class="d-flex gap-2">
                                            <?php if ($appointment['status'] === 'pending'): ?>
                                                <button class="btn btn-sm btn-success" 
                                                        onclick="updateAppointmentStatus(<?= $appointment['id'] ?>, 'accept')">
                                                    قبول
                                                </button>
                                                <button class="btn btn-sm btn-danger" 
                                                        onclick="rejectAppointment(<?= $appointment['id'] ?>)">
                                                    رفض
                                                </button>
                                            <?php elseif ($appointment['status'] === 'confirmed'): ?>
                                                <button class="btn btn-sm btn-primary" 
                                                        onclick="completeAppointment(<?= $appointment['id'] ?>)">
                                                    إكمال
                                                </button>
                                            <?php endif; ?>
                                            <a href="<?= $this->url('/doctor/appointments/' . $appointment['id']) ?>" 
                                               class="btn btn-sm btn-outline-info">
                                                تفاصيل
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-center">
                        <a href="<?= $this->url('/doctor/appointments') ?>" class="btn btn-success">
                            عرض جميع المواعيد
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">لا توجد مواعيد اليوم</h5>
                        <p class="text-muted">استمتع بيومك!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Upcoming Appointments -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>
                    المواعيد القادمة
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($upcoming_appointments)): ?>
                    <?php foreach (array_slice($upcoming_appointments, 0, 5) as $appointment): ?>
                        <div class="d-flex align-items-center mb-3 p-2 border rounded">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?= $this->escape($appointment['patient_name']) ?></h6>
                                <small class="text-muted">
                                    <?= $this->formatArabicDate($appointment['appointment_date']) ?>
                                    في <?= date('h:i A', strtotime($appointment['appointment_time'])) ?>
                                </small>
                            </div>
                            <div>
                                <span class="badge bg-<?= $this->getStatusBadge($appointment['status']) ?>">
                                    <?= $this->getStatusText($appointment['status']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="text-center">
                        <a href="<?= $this->url('/doctor/appointments') ?>" class="btn btn-info btn-sm">
                            عرض المزيد
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                        <p class="text-muted">لا توجد مواعيد قادمة</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Reviews -->
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">
                    <i class="fas fa-star me-2"></i>
                    آخر التقييمات
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($recent_reviews)): ?>
                    <?php foreach ($recent_reviews as $review): ?>
                        <div class="d-flex mb-3 p-3 border rounded">
                            <div class="flex-shrink-0 me-3">
                                <?php if (!empty($review['patient_avatar'])): ?>
                                    <img src="<?= $this->asset('uploads/profiles/' . $review['patient_avatar']) ?>" 
                                         alt="صورة المريض" class="rounded-circle" width="40" height="40">
                                <?php else: ?>
                                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="mb-0"><?= $this->escape($review['patient_name']) ?></h6>
                                    <div class="text-warning">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?= $i <= $review['rating'] ? '' : '-o' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="mb-1 small"><?= $this->escape($review['comment']) ?></p>
                                <small class="text-muted">
                                    <?= $this->timeAgo($review['created_at']) ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="text-center">
                        <a href="<?= $this->url('/doctor/reviews') ?>" class="btn btn-warning">
                            عرض جميع التقييمات
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-star fa-3x text-muted mb-3"></i>
                        <p class="text-muted">لا توجد تقييمات بعد</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>
                    إجراءات سريعة
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 mb-3">
                        <a href="<?= $this->url('/doctor/appointments') ?>" class="btn btn-outline-primary w-100">
                            <i class="fas fa-calendar-check fa-2x d-block mb-2"></i>
                            إدارة المواعيد
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="<?= $this->url('/doctor/schedule') ?>" class="btn btn-outline-success w-100">
                            <i class="fas fa-clock fa-2x d-block mb-2"></i>
                            تحديث الجدول
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="<?= $this->url('/doctor/profile') ?>" class="btn btn-outline-info w-100">
                            <i class="fas fa-user-edit fa-2x d-block mb-2"></i>
                            تحديث الملف
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="<?= $this->url('/doctor/patients') ?>" class="btn btn-outline-warning w-100">
                            <i class="fas fa-user-injured fa-2x d-block mb-2"></i>
                            قائمة المرضى
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<!-- Reject Appointment Modal -->
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

<!-- Complete Appointment Modal -->
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إكمال الموعد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="completeForm">
                    <input type="hidden" id="completeAppointmentId">
                    <div class="mb-3">
                        <label for="doctorNotes" class="form-label">ملاحظات الطبيب</label>
                        <textarea class="form-control" id="doctorNotes" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="prescription" class="form-label">الوصفة الطبية</label>
                        <textarea class="form-control" id="prescription" rows="4"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" onclick="confirmComplete()">إكمال الموعد</button>
            </div>
        </div>
    </div>
</div>

<script>
// Update current time
function updateTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('ar-SA', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
    document.getElementById('current-time').textContent = timeString;
}

// Update time every second
setInterval(updateTime, 1000);
updateTime();

// Reject appointment
function rejectAppointment(appointmentId) {
    document.getElementById('rejectAppointmentId').value = appointmentId;
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function confirmReject() {
    const appointmentId = document.getElementById('rejectAppointmentId').value;
    const reason = document.getElementById('rejectReason').value;
    
    if (!reason.trim()) {
        alert('يرجى إدخال سبب الرفض');
        return;
    }
    
    updateAppointmentStatus(appointmentId, 'reject', reason);
    bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
}

// Complete appointment
function completeAppointment(appointmentId) {
    document.getElementById('completeAppointmentId').value = appointmentId;
    new bootstrap.Modal(document.getElementById('completeModal')).show();
}

function confirmComplete() {
    const appointmentId = document.getElementById('completeAppointmentId').value;
    const notes = document.getElementById('doctorNotes').value;
    const prescription = document.getElementById('prescription').value;
    
    const formData = new FormData();
    formData.append('appointment_id', appointmentId);
    formData.append('notes', notes);
    formData.append('prescription', prescription);
    formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);
    
    fetch('/doctor/complete-appointment', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Doctorna.utils.showToast(data.message, 'success');
            location.reload();
        } else {
            Doctorna.utils.showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Doctorna.utils.showToast('حدث خطأ غير متوقع', 'error');
    });
    
    bootstrap.Modal.getInstance(document.getElementById('completeModal')).hide();
}
</script>
