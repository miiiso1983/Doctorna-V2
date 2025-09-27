<?php
// Admin Specializations Management View
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0">إدارة التخصصات</h5>
  <form class="d-flex" method="get" action="">
    <input type="text" name="search" class="form-control me-2" placeholder="بحث بالتخصص" value="<?= $this->escape($search ?? '') ?>">
    <button class="btn btn-primary" type="submit">بحث</button>
  </form>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr>
          <th>#</th>
          <th>الاسم</th>
          <th>الوصف</th>
          <th>عدد الأطباء</th>
          <th>الحالة</th>
          <th>الترتيب</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($specializations)): $i = 0; ?>
          <?php foreach ($specializations as $s): $i++; ?>
            <tr>
              <td><?= $i ?></td>
              <td>
                <div class="d-flex align-items-center">
                  <?php if (!empty($s['icon'])): ?>
                    <img src="<?= $this->asset('icons/' . $this->escape($s['icon'])) ?>" alt="" width="24" height="24" class="me-2">
                  <?php endif; ?>
                  <div>
                    <div><?= $this->escape($s['name']) ?></div>
                    <?php if (!empty($s['name_en'])): ?><small class="text-muted"><?= $this->escape($s['name_en']) ?></small><?php endif; ?>
                  </div>
                </div>
              </td>
              <td><small class="text-muted d-inline-block" style="max-width:320px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                <?= $this->escape($s['description'] ?? '') ?>
              </small></td>
              <td><?= (int)($s['doctor_count'] ?? 0) ?></td>
              <td><span class="badge bg-<?= ($s['is_active'] ?? 0) ? 'success' : 'secondary' ?>"><?= ($s['is_active'] ?? 0) ? 'مفعل' : 'موقوف' ?></span></td>
              <td><?= (int)($s['sort_order'] ?? 0) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" class="text-center text-muted">لا توجد تخصصات</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

