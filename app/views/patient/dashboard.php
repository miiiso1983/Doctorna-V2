<!-- Welcome Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h4 class="mb-1">مرحباً <?= $this->escape($this->user()['name']) ?></h4>
                        <p class="mb-0">
                            نتمنى لك دوام الصحة والعافية. يمكنك البحث عن الأطباء وحجز المواعيد بسهولة.
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

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <a href="<?= $this->url('/patient/search-doctors') ?>" class="text-decoration-none">
            <div class="card stat-card h-100" style="--bg-color: #28a745; --bg-color-end: #20c997;">
                <div class="card-body text-center">
                    <i class="fas fa-search fa-3x mb-3"></i>
                    <h5 class="mb-2">البحث عن طبيب</h5>
                    <p class="mb-0 small">ابحث عن الطبيب المناسب لحالتك</p>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <a href="<?= $this->url('/patient/appointments') ?>" class="text-decoration-none">
            <div class="card stat-card h-100" style="--bg-color: #17a2b8; --bg-color-end: #6f42c1;">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-check fa-3x mb-3"></i>
                    <h5 class="mb-2">مواعيدي</h5>
                    <p class="mb-0 small"><?= $appointment_stats['upcoming'] ?> موعد قادم</p>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <a href="<?= $this->url('/patient/medical-history') ?>" class="text-decoration-none">
            <div class="card stat-card h-100" style="--bg-color: #ffc107; --bg-color-end: #fd7e14;">
                <div class="card-body text-center">
                    <i class="fas fa-file-medical fa-3x mb-3"></i>
                    <h5 class="mb-2">التاريخ الطبي</h5>
                    <p class="mb-0 small">عرض السجل الطبي الكامل</p>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <a href="<?= $this->url('/patient/emergency') ?>" class="text-decoration-none">
            <div class="card stat-card h-100" style="--bg-color: #dc3545; --bg-color-end: #e83e8c;">
                <div class="card-body text-center">
                    <i class="fas fa-ambulance fa-3x mb-3"></i>
                    <h5 class="mb-2">الطوارئ</h5>
                    <p class="mb-0 small">أرقام الطوارئ والمساعدة</p>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">إجمالي المواعيد</h6>
                        <h2 class="mb-0 text-primary"><?= $appointment_stats['total'] ?></h2>
                    </div>
                    <div class="text-primary">
                        <i class="fas fa-calendar fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">في الانتظار</h6>
                        <h2 class="mb-0 text-warning"><?= $appointment_stats['pending'] ?></h2>
                    </div>
                    <div class="text-warning">
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">مؤكدة</h6>
                        <h2 class="mb-0 text-success"><?= $appointment_stats['confirmed'] ?></h2>
                    </div>
                    <div class="text-success">
                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">مكتملة</h6>
                        <h2 class="mb-0 text-info"><?= $appointment_stats['completed'] ?></h2>
                    </div>
                    <div class="text-info">
                        <i class="fas fa-check-double fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Appointments -->
<div class="row mb-4">
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>
                    المواعيد القادمة
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($upcoming_appointments)): ?>
                    <div class="row">
                        <?php foreach ($upcoming_appointments as $appointment): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card appointment-card" 
                                     style="--status-color: <?= $this->getStatusColor($appointment['status']) ?>">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0">د. <?= $this->escape($appointment['doctor_name']) ?></h6>
                                            <span class="badge bg-<?= $this->getStatusBadge($appointment['status']) ?>">
                                                <?= $this->getStatusText($appointment['status']) ?>
                                            </span>
                                        </div>
                                        <p class="text-muted mb-2">
                                            <i class="fas fa-stethoscope me-1"></i>
                                            <?= $this->escape($appointment['specialization_name']) ?>
                                        </p>
                                        <p class="text-muted mb-2">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= $this->formatArabicDate($appointment['appointment_date']) ?>
                                        </p>
                                        <p class="text-muted mb-2">
                                            <i class="fas fa-clock me-1"></i>
                                            <?= date('h:i A', strtotime($appointment['appointment_time'])) ?>
                                        </p>
                                        <div class="d-flex gap-2">
                                            <a href="<?= $this->url('/patient/appointments/' . $appointment['id']) ?>" 
                                               class="btn btn-sm btn-outline-info">
                                                تفاصيل
                                            </a>
                                            <?php if ($appointment['status'] !== 'completed'): ?>
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        onclick="cancelAppointment(<?= $appointment['id'] ?>)">
                                                    إلغاء
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-center">
                        <a href="<?= $this->url('/patient/appointments') ?>" class="btn btn-success">
                            عرض جميع المواعيد
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">لا توجد مواعيد قادمة</h5>
                        <p class="text-muted mb-3">ابحث عن طبيب واحجز موعدك الآن</p>
                        <a href="<?= $this->url('/patient/search-doctors') ?>" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>البحث عن طبيب
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Recommended Doctors -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="fas fa-star me-2"></i>
                    أطباء مميزون
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($recommended_doctors)): ?>
                    <?php foreach (array_slice($recommended_doctors, 0, 3) as $doctor): ?>
                        <div class="d-flex align-items-center mb-3 p-2 border rounded">
                            <div class="flex-shrink-0 me-3">
                                <?php if (!empty($doctor['avatar'])): ?>
                                    <img src="<?= $this->asset('uploads/profiles/' . $doctor['avatar']) ?>" 
                                         alt="صورة الطبيب" class="rounded-circle" width="50" height="50">
                                <?php else: ?>
                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 50px; height: 50px;">
                                        <i class="fas fa-user-md text-white"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">د. <?= $this->escape($doctor['name']) ?></h6>
                                <small class="text-muted d-block"><?= $this->escape($doctor['specialization_name']) ?></small>
                                <div class="rating-stars small">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?= $i <= $doctor['rating'] ? '' : '-o' ?>"></i>
                                    <?php endfor; ?>
                                    <span class="text-muted ms-1">(<?= $doctor['total_reviews'] ?>)</span>
                                </div>
                            </div>
                            <div>
                                <a href="<?= $this->url('/patient/doctor/' . $doctor['id']) ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    عرض
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="text-center">
                        <a href="<?= $this->url('/patient/search-doctors') ?>" class="btn btn-info btn-sm">
                            عرض المزيد
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-user-md fa-3x text-muted mb-3"></i>
                        <p class="text-muted">لا توجد توصيات حالياً</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Medical History -->
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i>
                    آخر المواعيد
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($appointment_history)): ?>
                    <?php foreach ($appointment_history as $appointment): ?>
                        <div class="d-flex mb-3 p-3 border rounded">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="mb-0">د. <?= $this->escape($appointment['doctor_name']) ?></h6>
                                    <small class="text-muted">
                                        <?= $this->formatArabicDate($appointment['appointment_date']) ?>
                                    </small>
                                </div>
                                <p class="mb-1 small text-muted">
                                    <?= $this->escape($appointment['specialization_name']) ?>
                                </p>
                                <?php if (!empty($appointment['doctor_notes'])): ?>
                                    <p class="mb-1 small">
                                        <strong>ملاحظات الطبيب:</strong> 
                                        <?= $this->escape(substr($appointment['doctor_notes'], 0, 100)) ?>
                                        <?= strlen($appointment['doctor_notes']) > 100 ? '...' : '' ?>
                                    </p>
                                <?php endif; ?>
                                <span class="badge bg-<?= $this->getStatusBadge($appointment['status']) ?>">
                                    <?= $this->getStatusText($appointment['status']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="text-center">
                        <a href="<?= $this->url('/patient/medical-history') ?>" class="btn btn-warning">
                            عرض التاريخ الكامل
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-file-medical fa-3x text-muted mb-3"></i>
                        <p class="text-muted">لا يوجد تاريخ طبي بعد</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Health Tips -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="fas fa-lightbulb me-2"></i>
                    نصائح صحية
                </h5>
            </div>
            <div class="card-body">
                <div class="health-tip mb-3 p-3 bg-light rounded">
                    <h6 class="text-primary">💧 اشرب الماء بانتظام</h6>
                    <p class="mb-0 small">احرص على شرب 8 أكواب من الماء يومياً للحفاظ على صحة جسمك.</p>
                </div>
                
                <div class="health-tip mb-3 p-3 bg-light rounded">
                    <h6 class="text-success">🏃‍♂️ مارس الرياضة</h6>
                    <p class="mb-0 small">30 دقيقة من المشي يومياً تحسن من صحة القلب والدورة الدموية.</p>
                </div>
                
                <div class="health-tip mb-3 p-3 bg-light rounded">
                    <h6 class="text-info">😴 نم جيداً</h6>
                    <p class="mb-0 small">احصل على 7-8 ساعات من النوم الجيد لتجديد طاقة جسمك.</p>
                </div>
                
                <div class="health-tip p-3 bg-light rounded">
                    <h6 class="text-warning">🥗 تناول طعاماً صحياً</h6>
                    <p class="mb-0 small">أكثر من الخضروات والفواكه وقلل من الأطعمة المصنعة.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Emergency Contacts -->
<div class="row">
    <div class="col-12">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">
                    <i class="fas fa-phone-alt me-2"></i>
                    أرقام الطوارئ
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center mb-3">
                        <div class="emergency-contact">
                            <i class="fas fa-ambulance fa-2x text-danger mb-2"></i>
                            <h6>الإسعاف</h6>
                            <a href="tel:997" class="btn btn-danger btn-sm">997</a>
                        </div>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <div class="emergency-contact">
                            <i class="fas fa-shield-alt fa-2x text-primary mb-2"></i>
                            <h6>الشرطة</h6>
                            <a href="tel:999" class="btn btn-primary btn-sm">999</a>
                        </div>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <div class="emergency-contact">
                            <i class="fas fa-fire fa-2x text-warning mb-2"></i>
                            <h6>الإطفاء</h6>
                            <a href="tel:998" class="btn btn-warning btn-sm">998</a>
                        </div>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <div class="emergency-contact">
                            <i class="fas fa-hospital fa-2x text-success mb-2"></i>
                            <h6>المستشفى</h6>
                            <a href="tel:920027778" class="btn btn-success btn-sm">920027778</a>
                        </div>
                    </div>
                </div>
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
</script>
