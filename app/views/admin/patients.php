<?php
// Admin Patients Management View
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0">إدارة المرضى</h5>
  <form class="d-flex" method="get" action="">
    <input type="text" name="search" class="form-control me-2" placeholder="بحث بالاسم أو البريد أو الهاتف" value="<?= $this->escape($search ?? '') ?>">
    <button class="btn btn-primary" type="submit">تصفية</button>
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
          <th>المدينة</th>
          <th>فصيلة الدم</th>
          <th>تاريخ الميلاد</th>
          <th>تاريخ التسجيل</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($patients['data'])): $i = ($patients['from'] ?? 1) - 1; ?>
          <?php foreach ($patients['data'] as $p): $i++; ?>
            <tr>
              <td><?= $i ?></td>
              <td><?= $this->escape($p['name'] ?? '') ?></td>
              <td><?= $this->escape($p['email'] ?? '') ?></td>
              <td><?= $this->escape($p['phone'] ?? '') ?></td>
              <td><?= $this->escape($p['city'] ?? '') ?></td>
              <td><?= $this->escape($p['blood_type'] ?? '—') ?></td>
              <td><small><?= $this->escape($p['date_of_birth'] ?? '—') ?></small></td>
              <td><small><?= $this->escape($p['registration_date'] ?? '') ?></small></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="8" class="text-center text-muted">لا توجد نتائج</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="card-body">
    <?= $this->paginate($patients ?? []) ?>
  </div>
</div>

