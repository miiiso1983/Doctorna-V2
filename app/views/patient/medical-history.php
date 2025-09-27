<?php
// Patient Medical History View
$history = $history ?? ['symptoms' => [], 'appointments' => []];
?>
<div class="row mb-4">
  <div class="col-12">
    <h5 class="mb-3"><i class="fas fa-file-medical me-2"></i>التاريخ الطبي</h5>
  </div>
</div>

<div class="row">
  <div class="col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-header"><strong>الأعراض المسجلة</strong></div>
      <div class="card-body">
        <?php if (!empty($history['symptoms'])): ?>
          <ul class="list-group">
            <?php foreach ($history['symptoms'] as $s): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <div class="fw-bold"><?= $this->escape($s['symptom_name'] ?? 'عرض') ?></div>
                  <small class="text-muted">الفئة: <?= $this->escape($s['category'] ?? '—') ?></small>
                </div>
                <small class="text-muted"><?= $this->timeAgo($s['reported_at'] ?? '') ?></small>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <div class="text-muted">لا توجد أعراض مسجلة</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-header"><strong>التشخيصات والمواعيد المكتملة</strong></div>
      <div class="card-body">
        <?php if (!empty($history['appointments'])): ?>
          <ul class="list-group">
            <?php foreach ($history['appointments'] as $a): ?>
              <li class="list-group-item">
                <div class="d-flex justify-content-between">
                  <div>
                    <div class="fw-bold">د. <?= $this->escape($a['doctor_name'] ?? '—') ?></div>
                    <small class="text-muted">التخصص: <?= $this->escape($a['specialization_name'] ?? '') ?></small>
                  </div>
                  <small class="text-muted"><?= $this->formatArabicDate($a['appointment_date']) ?> <?= $this->escape(substr($a['appointment_time'],0,5)) ?></small>
                </div>
                <?php if (!empty($a['doctor_notes'])): ?>
                  <div class="mt-2 small">ملاحظات الطبيب: <?= $this->escape($a['doctor_notes']) ?></div>
                <?php endif; ?>
                <?php if (!empty($a['prescription'])): ?>
                  <div class="mt-1 small">الوصفة الطبية: <?= $this->escape($a['prescription']) ?></div>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <div class="text-muted">لا توجد مواعيد مكتملة</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

