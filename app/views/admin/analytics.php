<?php
// Admin Analytics & Reports View
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0">التقارير والإحصائيات</h5>
  <form class="row g-2" method="get" action="">
    <div class="col-auto">
      <input type="date" name="start_date" value="<?= $this->escape($start_date ?? '') ?>" class="form-control">
    </div>
    <div class="col-auto">
      <input type="date" name="end_date" value="<?= $this->escape($end_date ?? '') ?>" class="form-control">
    </div>
    <div class="col-auto">
      <button class="btn btn-primary" type="submit">تحديث</button>
    </div>
  </form>
</div>

<div class="row g-3">
  <div class="col-md-4">
    <div class="card stat-card h-100" style="--bg-color: #667eea; --bg-color-end: #764ba2;">
      <div class="card-body text-white">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fs-6">إجمالي المواعيد</div>
            <div class="fs-3 fw-bold"><?= (int)($appointment_stats['total_appointments'] ?? 0) ?></div>
          </div>
          <i class="fas fa-calendar-check fa-2x"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat-card h-100" style="--bg-color: #28a745; --bg-color-end: #20c997;">
      <div class="card-body text-white">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fs-6">المواعيد المكتملة</div>
            <div class="fs-3 fw-bold"><?= (int)($appointment_stats['completed_appointments'] ?? 0) ?></div>
          </div>
          <i class="fas fa-check-circle fa-2x"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat-card h-100" style="--bg-color: #ffc107; --bg-color-end: #fd7e14;">
      <div class="card-body text-white">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fs-6">الإيرادات</div>
            <div class="fs-3 fw-bold"><?= number_format((float)($appointment_stats['total_revenue'] ?? 0)) ?></div>
          </div>
          <i class="fas fa-coins fa-2x"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card mt-4">
  <div class="card-header">التخصصات الأكثر طلبًا</div>
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead>
        <tr>
          <th>#</th>
          <th>التخصص</th>
          <th>عدد المواعيد</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($popular_specializations)): $i = 0; ?>
          <?php foreach ($popular_specializations as $sp): $i++; ?>
            <tr>
              <td><?= $i ?></td>
              <td><?= $this->escape($sp['name'] ?? '') ?></td>
              <td><?= (int)($sp['appointment_count'] ?? 0) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="3" class="text-center text-muted">لا توجد بيانات</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card mt-4">
  <div class="card-header">أفضل الأطباء</div>
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead>
        <tr>
          <th>#</th>
          <th>الاسم</th>
          <th>التخصص</th>
          <th>التقييم</th>
          <th>المراجعات</th>
          <th>المواعيد</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($top_doctors)): $i = 0; ?>
          <?php foreach ($top_doctors as $d): $i++; ?>
            <tr>
              <td><?= $i ?></td>
              <td><?= $this->escape($d['name'] ?? '') ?></td>
              <td><?= $this->escape($d['specialization_name'] ?? '') ?></td>
              <td><?= number_format((float)($d['rating'] ?? 0), 2) ?></td>
              <td><?= (int)($d['total_reviews'] ?? 0) ?></td>
              <td><?= (int)($d['total_appointments'] ?? 0) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" class="text-center text-muted">لا توجد بيانات</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

