<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card" style="--bg-color: #667eea; --bg-color-end: #764ba2;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-white-50 mb-1">إجمالي المستخدمين</h6>
                        <h2 class="mb-0 text-white"><?= number_format($user_stats['total_users']) ?></h2>
                        <small class="text-white-50">
                            <i class="fas fa-arrow-up me-1"></i>
                            +<?= $user_stats['recent_registrations'] ?> هذا الشهر
                        </small>
                    </div>
                    <div class="text-white">
                        <i class="fas fa-users fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card" style="--bg-color: #28a745; --bg-color-end: #20c997;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-white-50 mb-1">الأطباء المعتمدين</h6>
                        <h2 class="mb-0 text-white"><?= number_format($doctor_stats['approved_doctors']) ?></h2>
                        <small class="text-white-50">
                            <?= $doctor_stats['pending_doctors'] ?> في الانتظار
                        </small>
                    </div>
                    <div class="text-white">
                        <i class="fas fa-user-md fa-2x opacity-75"></i>
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
                        <h6 class="card-title text-white-50 mb-1">إجمالي المواعيد</h6>
                        <h2 class="mb-0 text-white"><?= number_format($appointment_stats['total_appointments']) ?></h2>
                        <small class="text-white-50">
                            <?= $appointment_stats['today_appointments'] ?> اليوم
                        </small>
                    </div>
                    <div class="text-white">
                        <i class="fas fa-calendar-check fa-2x opacity-75"></i>
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
                        <h6 class="card-title text-white-50 mb-1">إجمالي الإيرادات</h6>
                        <h2 class="mb-0 text-white"><?= $this->formatCurrency($appointment_stats['total_revenue']) ?></h2>
                        <small class="text-white-50">
                            المواعيد المكتملة
                        </small>
                    </div>
                    <div class="text-white">
                        <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    إحصائيات شهرية
                </h5>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    توزيع المستخدمين
                </h5>
            </div>
            <div class="card-body">
                <canvas id="userDistributionChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities -->
<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="fas fa-clock me-2"></i>
                    المواعيد القادمة
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($recent_appointments)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>المريض</th>
                                    <th>الطبيب</th>
                                    <th>التخصص</th>
                                    <th>التاريخ</th>
                                    <th>الوقت</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_appointments as $appointment): ?>
                                    <tr>
                                        <td><?= $this->escape($appointment['patient_name']) ?></td>
                                        <td><?= $this->escape($appointment['doctor_name']) ?></td>
                                        <td><?= $this->escape($appointment['specialization_name']) ?></td>
                                        <td><?= $this->formatArabicDate($appointment['appointment_date']) ?></td>
                                        <td><?= date('h:i A', strtotime($appointment['appointment_time'])) ?></td>
                                        <td>
                                            <?php
                                            $statusClass = [
                                                'pending' => 'warning',
                                                'confirmed' => 'success',
                                                'completed' => 'primary',
                                                'cancelled' => 'danger'
                                            ];
                                            $statusText = [
                                                'pending' => 'في الانتظار',
                                                'confirmed' => 'مؤكد',
                                                'completed' => 'مكتمل',
                                                'cancelled' => 'ملغي'
                                            ];
                                            ?>
                                            <span class="badge bg-<?= $statusClass[$appointment['status']] ?>">
                                                <?= $statusText[$appointment['status']] ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center">
                        <a href="<?= $this->url('/admin/appointments') ?>" class="btn btn-info">
                            عرض جميع المواعيد
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <p class="text-muted">لا توجد مواعيد قادمة</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">
                    <i class="fas fa-user-clock me-2"></i>
                    أطباء في الانتظار
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($pending_doctors)): ?>
                    <?php foreach ($pending_doctors as $doctor): ?>
                        <div class="d-flex align-items-center mb-3 p-2 border rounded">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?= $this->escape($doctor['name']) ?></h6>
                                <small class="text-muted"><?= $this->escape($doctor['specialization_name']) ?></small>
                                <br>
                                <small class="text-muted"><?= $this->escape($doctor['email']) ?></small>
                            </div>
                            <div>
                                <a href="<?= $this->url('/admin/doctors?status=pending') ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    مراجعة
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="text-center">
                        <a href="<?= $this->url('/admin/doctors?status=pending') ?>" class="btn btn-warning">
                            عرض جميع الطلبات
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <p class="text-muted">لا توجد طلبات في الانتظار</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>
                    إجراءات سريعة
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="<?= $this->url('/admin/users') ?>" class="btn btn-outline-primary w-100">
                            <i class="fas fa-users fa-2x d-block mb-2"></i>
                            إدارة المستخدمين
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= $this->url('/admin/doctors') ?>" class="btn btn-outline-success w-100">
                            <i class="fas fa-user-md fa-2x d-block mb-2"></i>
                            إدارة الأطباء
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= $this->url('/admin/specializations') ?>" class="btn btn-outline-info w-100">
                            <i class="fas fa-stethoscope fa-2x d-block mb-2"></i>
                            إدارة التخصصات
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= $this->url('/admin/analytics') ?>" class="btn btn-outline-warning w-100">
                            <i class="fas fa-chart-bar fa-2x d-block mb-2"></i>
                            التقارير والإحصائيات
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Monthly Statistics Chart
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
const monthlyChart = new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($monthly_stats, 'month_name')) ?>,
        datasets: [{
            label: 'المستخدمين الجدد',
            data: <?= json_encode(array_column($monthly_stats, 'users')) ?>,
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            tension: 0.4
        }, {
            label: 'المواعيد',
            data: <?= json_encode(array_column($monthly_stats, 'appointments')) ?>,
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// User Distribution Chart
const userCtx = document.getElementById('userDistributionChart').getContext('2d');
const userChart = new Chart(userCtx, {
    type: 'doughnut',
    data: {
        labels: ['الأطباء', 'المرضى', 'المديرين'],
        datasets: [{
            data: [
                <?= $user_stats['total_doctors'] ?>,
                <?= $user_stats['total_patients'] ?>,
                <?= $user_stats['total_admins'] ?>
            ],
            backgroundColor: [
                '#28a745',
                '#17a2b8',
                '#ffc107'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    }
});
</script>
