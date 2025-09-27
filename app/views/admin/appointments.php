<?php
// Admin Appointments Management View
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0">إدارة المواعيد</h5>
  <form class="row g-2" method="get" action="">
    <div class="col-auto">
      <select name="status" class="form-select">
        <option value="">كل الحالات</option>
        <?php foreach (($statuses ?? []) as $key => $label): ?>
          <option value="<?= $this->escape($key) ?>" <?= (!empty($status) && $status == $key) ? 'selected' : '' ?>><?= $this->escape($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-auto">
      <input type="date" name="date" value="<?= $this->escape($date ?? '') ?>" class="form-control">
    </div>
    <div class="col-auto">
      <button class="btn btn-primary" type="submit">تصفية</button>
    </div>
  </form>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr>
          <th>#</th>
          <th>المريض</th>
          <th>الطبيب</th>
          <th>التخصص</th>
          <th>التاريخ</th>
          <th>الوقت</th>
          <th>الحالة</th>
          <th>الدفع</th>
          <th>الرسوم</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($appointments['data'])): $i = ($appointments['from'] ?? 1) - 1; ?>
          <?php foreach ($appointments['data'] as $a): $i++; ?>
            <tr>
              <td><?= $i ?></td>
              <td><?= $this->escape($a['patient_name'] ?? '') ?> <br><small class="text-muted"><?= $this->escape($a['patient_email'] ?? '') ?></small></td>
              <td><?= $this->escape($a['doctor_name'] ?? '') ?> <br><small class="text-muted"><?= $this->escape($a['doctor_email'] ?? '') ?></small></td>
              <td><?= $this->escape($a['specialization_name'] ?? '') ?></td>
              <td><?= $this->escape($a['appointment_date'] ?? '') ?></td>
              <td><?= substr($this->escape($a['appointment_time'] ?? ''), 0, 5) ?></td>
              <td><span class="badge bg-<?php 
                $st = $a['status'] ?? '';
                echo $st === 'confirmed' ? 'success' : ($st === 'pending' ? 'warning' : ($st === 'completed' ? 'primary' : 'secondary'));
              ?>"><?= $this->escape($statuses[$st] ?? $st) ?></span></td>
              <td><span class="badge bg-<?= ($a['payment_status'] ?? '') === 'paid' ? 'success' : 'secondary' ?>"><?= $this->escape($a['payment_status'] ?? '—') ?></span></td>
              <td><?= number_format((float)($a['fee'] ?? 0)) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="9" class="text-center text-muted">لا توجد نتائج</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="card-body">
    <?= $this->paginate($appointments ?? []) ?>
  </div>
</div>

