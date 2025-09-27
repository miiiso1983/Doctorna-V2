<?php
// Print-friendly prescription page
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">وصفة طبية</h5>
    <button class="btn btn-outline-secondary" onclick="window.print()"><i class="fas fa-print me-1"></i>طباعة</button>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <div><strong>الطبيب:</strong> د. <?= $this->escape($appointment['doctor_name'] ?? '—') ?></div>
          <div><strong>التخصص:</strong> <?= $this->escape($appointment['specialization_name'] ?? '') ?></div>
        </div>
        <div class="col-md-6 text-md-end">
          <div><strong>التاريخ:</strong> <?= $this->formatArabicDate($appointment['appointment_date']) ?> <?= $this->escape(substr($appointment['appointment_time'],0,5)) ?></div>
          <div><strong>المريض:</strong> <?= $this->escape($patient['name'] ?? '') ?></div>
        </div>
      </div>

      <hr>

      <div class="mb-3">
        <div class="fw-bold mb-2">الوصفة</div>
        <pre style="white-space:pre-wrap; font-family:inherit; background:#f8f9fa; padding:1rem; border-radius:8px;">
<?= $this->escape($appointment['prescription'] ?? '') ?>
        </pre>
      </div>

      <?php if (!empty($appointment['doctor_notes'])): ?>
        <div class="mb-3">
          <div class="fw-bold mb-2">ملاحظات الطبيب</div>
          <div><?= $this->escape($appointment['doctor_notes']) ?></div>
        </div>
      <?php endif; ?>

      <div class="text-muted small">تم إنشاء هذا المستند من نظام دكتورنا</div>
    </div>
  </div>
</div>

