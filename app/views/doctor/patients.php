<?php
// Doctor Patients List View
?>
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-user-injured me-2"></i>قائمة المرضى</h5>
        <form class="row g-2" method="GET" action="<?= $this->url('/doctor/patients') ?>">
          <div class="col-auto">
            <input type="text" class="form-control" name="q" value="<?= $this->escape($q ?? '') ?>" placeholder="ابحث بالاسم أو الهاتف">
          </div>
          <div class="col-auto">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>بحث</button>
            <a href="<?= $this->url('/doctor/patients') ?>" class="btn btn-outline-secondary">إعادة ضبط</a>
          </div>
        </form>
      </div>
      <div class="card-body">
        <?php if (!empty($patients['data'])): ?>
          <div class="table-responsive">
            <table class="table align-middle">
              <thead>
                <tr>
                  <th>المريض</th>
                  <th>العمر</th>
                  <th>فصيلة الدم</th>
                  <th>سجلات</th>
                  <th>إجراءات</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($patients['data'] as $p): ?>
                <tr>
                  <td>
                    <div class="d-flex align-items-center">
                      <?php if (!empty($p['avatar'])): ?>
                        <img src="<?= $this->asset('uploads/profiles/' . $p['avatar']) ?>" class="rounded-circle me-2" width="36" height="36" alt="">
                      <?php else: ?>
                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;">
                          <i class="fas fa-user text-white"></i>
                        </div>
                      <?php endif; ?>
                      <div>
                        <div class="fw-semibold"><?= $this->escape($p['name'] ?? '—') ?></div>
                        <small class="text-muted"><?= $this->escape($p['phone'] ?? '') ?></small>
                      </div>
                    </div>
                  </td>
                  <td>
                    <?php if (!empty($p['date_of_birth'])): ?>
                      <?= (int)(new DateTime())->diff(new DateTime($p['date_of_birth']))->y ?>
                    <?php else: ?>
                      —
                    <?php endif; ?>
                  </td>
                  <td><?= $this->escape($p['blood_type'] ?? '—') ?></td>
                  <td>
                    <span class="badge bg-info">أعراض: <?= (int)($p['symptom_count'] ?? 0) ?></span>
                    <span class="badge bg-success">زيارات: <?= (int)($p['visit_count'] ?? 0) ?></span>
                  </td>
                  <td>
                    <a class="btn btn-sm btn-outline-info" href="<?= $this->url('/doctor/patients/' . (int)$p['id']) ?>">تفاصيل</a>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="mt-3">
            <?= $this->paginate($patients) ?>
          </div>
        <?php else: ?>
          <div class="text-center py-5">
            <i class="fas fa-users-slash fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">لا توجد نتائج</h5>
            <p class="text-muted mb-0">استخدم مربع البحث بالأعلى للعثور على المرضى</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

