<?php
// Admin Users Management View
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0">إدارة المستخدمين</h5>
  <form class="d-flex" method="get" action="">
    <input type="text" name="search" class="form-control me-2" placeholder="بحث بالاسم أو البريد" value="<?= $this->escape($search ?? '') ?>">
    <select name="role" class="form-select me-2" style="width:180px">
      <option value="">كل الأدوار</option>
      <?php foreach (($roles ?? []) as $key => $label): ?>
        <option value="<?= $this->escape($key) ?>" <?= (!empty($role) && $role == $key) ? 'selected' : '' ?>><?= $this->escape($label) ?></option>
      <?php endforeach; ?>
    </select>
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
          <th>الدور</th>
          <th>الحالة</th>
          <th>الهاتف</th>
          <th>المدينة</th>
          <th>تاريخ التسجيل</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($users['data'])): $i = ($users['from'] ?? 1) - 1; ?>
          <?php foreach ($users['data'] as $u): $i++; ?>
            <tr>
              <td><?= $i ?></td>
              <td><?= $this->escape($u['name']) ?></td>
              <td><?= $this->escape($u['email']) ?></td>
              <td><?= $this->escape($roles[$u['role']] ?? $u['role']) ?></td>
              <td><span class="badge bg-<?= $u['status']==='active'?'success':'secondary' ?>"><?= $this->escape($u['status']) ?></span></td>
              <td><?= $this->escape($u['phone'] ?? '') ?></td>
              <td><?= $this->escape($u['city'] ?? '') ?></td>
              <td><small><?= $this->escape($u['created_at'] ?? '') ?></small></td>
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
    <?= $this->paginate($users ?? []) ?>
  </div>
</div>

