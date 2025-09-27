<?php
// Admin Doctors Management View
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0">إدارة الأطباء</h5>
  <form class="row g-2" method="get" action="">
    <div class="col-auto">
      <input type="text" name="search" class="form-control" placeholder="بحث بالاسم/البريد/الترخيص" value="<?= $this->escape($search ?? '') ?>">
    </div>
    <div class="col-auto">
      <select name="status" class="form-select">
        <option value="">كل الحالات</option>
        <?php foreach (($statuses ?? []) as $key => $label): ?>
          <option value="<?= $this->escape($key) ?>" <?= (!empty($status) && $status == $key) ? 'selected' : '' ?>><?= $this->escape($label) ?></option>
        <?php endforeach; ?>
      </select>
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
          <th>الاسم</th>
          <th>البريد</th>
          <th>الهاتف</th>
          <th>التخصص</th>
          <th>المدينة</th>
          <th>الرسوم</th>
          <th>الحالة</th>
          <th>تاريخ الإضافة</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($doctors['data'])): $i = ($doctors['from'] ?? 1) - 1; ?>
          <?php foreach ($doctors['data'] as $d): $i++; ?>
            <tr>
              <td><?= $i ?></td>
              <td><?= $this->escape($d['name'] ?? '') ?></td>
              <td><?= $this->escape($d['email'] ?? '') ?></td>
              <td><?= $this->escape($d['phone'] ?? '') ?></td>
              <td><?= $this->escape($d['specialization_name'] ?? '') ?></td>
              <td><?= $this->escape($d['city'] ?? '') ?></td>
              <td><?= number_format((float)($d['consultation_fee'] ?? 0)) ?></td>
              <td><span class="badge bg-<?php 
                echo ($d['status'] ?? '') === 'approved' ? 'success' : ((($d['status'] ?? '') === 'pending') ? 'warning' : 'secondary');
              ?>"><?= $this->escape($statuses[$d['status']] ?? $d['status'] ?? '') ?></span></td>
              <td><small><?= $this->escape($d['created_at'] ?? '') ?></small></td>
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
    <?= $this->paginate($doctors ?? []) ?>
  </div>
</div>

