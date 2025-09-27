<?php
// Patient Appointments View
?>
<div class="row mb-4">
  <div class="col-12 d-flex justify-content-between align-items-center">
    <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>مواعيدي</h5>
    <div>
      <a href="<?= $this->buildUrl('/patient/appointments', ['status' => '']) ?>" class="btn btn-sm <?= empty($status) ? 'btn-primary' : 'btn-outline-primary' ?>">الكل</a>
      <a href="<?= $this->buildUrl('/patient/appointments', ['status' => 'pending']) ?>" class="btn btn-sm <?= $status==='pending' ? 'btn-primary' : 'btn-outline-primary' ?>">في الانتظار</a>
      <a href="<?= $this->buildUrl('/patient/appointments', ['status' => 'confirmed']) ?>" class="btn btn-sm <?= $status==='confirmed' ? 'btn-primary' : 'btn-outline-primary' ?>">مؤكد</a>
      <a href="<?= $this->buildUrl('/patient/appointments', ['status' => 'completed']) ?>" class="btn btn-sm <?= $status==='completed' ? 'btn-primary' : 'btn-outline-primary' ?>">مكتمل</a>
      <a href="<?= $this->buildUrl('/patient/appointments', ['status' => 'cancelled']) ?>" class="btn btn-sm <?= $status==='cancelled' ? 'btn-primary' : 'btn-outline-primary' ?>">ملغي</a>
    </div>
  </div>
</div>

<?php if (!empty($appointments['data'])): ?>
  <div class="list-group">
    <?php foreach ($appointments['data'] as $a): ?>
      <div class="list-group-item">
        <div class="d-flex">
          <?php if (!empty($a['doctor_avatar'])): ?>
            <img src="<?= $this->asset('uploads/profiles/' . $a['doctor_avatar']) ?>" class="rounded-circle me-3" width="48" height="48" alt="">
          <?php else: ?>
            <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;">
              <i class="fas fa-user-md text-white"></i>
            </div>
          <?php endif; ?>
          <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <strong>د. <?= $this->escape($a['doctor_name'] ?? '—') ?></strong>
                <div class="text-muted small">
                  <?= $this->escape($a['specialization_name'] ?? '') ?>
                </div>
              </div>
              <span class="badge bg-<?= $this->statusBadgeClass($a['status']) ?>">
                <?= $this->statusLabel($a['status']) ?>
              </span>
            </div>
            <div class="mt-2 text-muted">
              <i class="far fa-calendar-alt me-1"></i><?= $this->formatArabicDate($a['appointment_date']) ?>
              <i class="far fa-clock me-1 ms-3"></i><?= $this->escape(substr($a['appointment_time'],0,5)) ?>
              <span class="ms-3"><i class="fas fa-dollar-sign me-1"></i><?= $this->formatCurrency($a['fee'] ?? 0) ?></span>
            </div>
            <?php if (!empty($a['clinic_name'])): ?>
              <div class="small text-muted mt-1">
                <i class="fas fa-hospital me-1"></i><?= $this->escape($a['clinic_name']) ?> — <?= $this->escape($a['clinic_address'] ?? '') ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if ($appointments['last_page'] > 1): ?>
    <nav class="mt-3" aria-label="صفحات المواعيد">
      <ul class="pagination justify-content-center">
        <?php if ($appointments['current_page'] > 1): ?>
          <li class="page-item">
            <a class="page-link" href="<?= $this->buildUrl('/patient/appointments', ['status' => $status, 'page' => $appointments['current_page'] - 1]) ?>">السابق</a>
          </li>
        <?php endif; ?>
        <?php for ($i=max(1,$appointments['current_page']-2); $i<=min($appointments['last_page'],$appointments['current_page']+2); $i++): ?>
          <li class="page-item <?= $i==$appointments['current_page']?'active':'' ?>">
            <a class="page-link" href="<?= $this->buildUrl('/patient/appointments', ['status' => $status, 'page' => $i]) ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
        <?php if ($appointments['current_page'] < $appointments['last_page']): ?>
          <li class="page-item">
            <a class="page-link" href="<?= $this->buildUrl('/patient/appointments', ['status' => $status, 'page' => $appointments['current_page'] + 1]) ?>">التالي</a>
          </li>
        <?php endif; ?>
      </ul>
    </nav>
  <?php endif; ?>

<?php else: ?>
  <div class="text-center py-5">
    <i class="far fa-calendar-times fa-3x text-muted mb-3"></i>
    <h6 class="text-muted">لا توجد مواعيد</h6>
  </div>
<?php endif; ?>

