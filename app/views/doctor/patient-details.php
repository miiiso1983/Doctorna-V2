<?php
// Doctor Patient Details View
?>
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-user me-2"></i>تفاصيل المريض</h5>
        <a href="<?= $this->url('/doctor/patients') ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-right me-1"></i>رجوع</a>
      </div>
      <div class="card-body">
        <div class="d-flex align-items-center mb-3">
          <?php if (!empty($patient['avatar'])): ?>
            <img src="<?= $this->asset('uploads/profiles/' . $patient['avatar']) ?>" class="rounded-circle me-3" width="64" height="64" alt="">
          <?php else: ?>
            <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-3" style="width:64px;height:64px;">
              <i class="fas fa-user text-white fa-lg"></i>
            </div>
          <?php endif; ?>
          <div>
            <h5 class="mb-1"><?= $this->escape($patient['name'] ?? '—') ?></h5>
            <div class="text-muted small">
              <i class="fas fa-phone me-1"></i><?= $this->escape($patient['phone'] ?? '—') ?>
              <span class="mx-2">|</span>
              <i class="fas fa-envelope me-1"></i><?= $this->escape($patient['email'] ?? '—') ?>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="mb-2"><strong>الجنس:</strong> <?= $this->escape($patient['gender'] ?? '—') ?></div>
            <div class="mb-2"><strong>تاريخ الميلاد:</strong> <?= !empty($patient['date_of_birth']) ? $this->formatArabicDate($patient['date_of_birth']) : '—' ?></div>
            <div class="mb-2"><strong>فصيلة الدم:</strong> <?= $this->escape($patient['blood_type'] ?? '—') ?></div>
          </div>
          <div class="col-md-6">
            <div class="mb-2"><strong>العنوان:</strong> <?= $this->escape(($patient['address'] ?? '—')) ?></div>
            <div class="mb-2"><strong>المدينة:</strong> <?= $this->escape(($patient['city'] ?? '—')) ?></div>
            <div class="mb-2"><strong>الدولة:</strong> <?= $this->escape(($patient['country'] ?? '—')) ?></div>
          </div>
        </div>
        <?php if (!empty($appointments)): ?>
          <hr>
          <h6 class="mb-3">آخر المواعيد</h6>
          <div class="table-responsive">
            <table class="table align-middle">
              <thead><tr><th>التاريخ</th><th>الوقت</th><th>الحالة</th></tr></thead>
              <tbody>
                <?php foreach ($appointments as $a): ?>
                <tr>
                  <td><?= $this->formatArabicDate($a['appointment_date']) ?></td>
                  <td><?= date('h:i A', strtotime($a['appointment_time'])) ?></td>
                  <td><span class="badge bg-<?= $this->getStatusBadge($a['status']) ?>"><?= $this->getStatusText($a['status']) ?></span></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

