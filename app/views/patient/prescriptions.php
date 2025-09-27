<?php
// Patient Prescriptions View
?>
<div class="row mb-4">
  <div class="col-12">
    <h5 class="mb-3"><i class="fas fa-prescription-bottle me-2"></i>الوصفات الطبية</h5>
  </div>
</div>
<?php
// Filters bar
$doctorFilter = $_GET['doctor'] ?? '';
$dateFrom = $_GET['from'] ?? '';
$dateTo = $_GET['to'] ?? '';
?>
<form class="row g-3 align-items-end mb-3" method="get" action="">
  <div class="col-md-4">
    <label class="form-label">بحث باسم الطبيب</label>
    <input type="text" name="doctor" value="<?= $this->escape($doctorFilter) ?>" class="form-control" placeholder="مثال: أحمد">
  </div>
  <div class="col-md-3">
    <label class="form-label">من تاريخ</label>
    <input type="date" name="from" value="<?= $this->escape($dateFrom) ?>" class="form-control">
  </div>
  <div class="col-md-3">
    <label class="form-label">إلى تاريخ</label>
    <input type="date" name="to" value="<?= $this->escape($dateTo) ?>" class="form-control">
  </div>
  <div class="col-md-2">
    <button class="btn btn-primary w-100" type="submit"><i class="fas fa-filter me-1"></i>تصفية</button>
  </div>
</form>


<?php if (!empty($prescriptions)): ?>
  <div class="list-group">
    <?php foreach ($prescriptions as $p): ?>
      <div class="list-group-item">
        <div class="d-flex">
          <?php if (!empty($p['doctor_avatar'])): ?>
            <img src="<?= $this->asset('uploads/profiles/' . $p['doctor_avatar']) ?>" class="rounded-circle me-3" width="48" height="48" alt="">

          <?php else: ?>
            <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;">
              <i class="fas fa-user-md text-white"></i>
            </div>
          <?php endif; ?>
          <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <strong>د. <?= $this->escape($p['doctor_name'] ?? '—') ?></strong>
                <div class="text-muted small">
                  <?= $this->escape($p['specialization_name'] ?? '') ?>
                </div>
              </div>
              <small class="text-muted"><?= $this->formatArabicDate($p['appointment_date']) ?> <?= $this->escape(substr($p['appointment_time'],0,5)) ?></small>
            </div>
            <div class="mt-2">
              <div class="fw-bold">الوصفة</div>
              <pre class="mb-0" style="white-space:pre-wrap; font-family:inherit; background:#f8f9fa; padding:0.75rem; border-radius:8px;">
<?= $this->escape($p['prescription'] ?? '') ?>
              </pre>
              <?php if (!empty($p['doctor_notes'])): ?>
                <div class="small text-muted mt-2">ملاحظات: <?= $this->escape($p['doctor_notes']) ?></div>
              <?php endif; ?>
            </div>
          </div>
            <div class="mt-2 d-flex gap-2">
              <a class="btn btn-outline-secondary btn-sm" target="_blank" href="<?= $this->url('/patient/prescriptions/' . (int)$p['id'] . '/print') ?>">
                <i class="fas fa-file-pdf me-1"></i>تحميل PDF
              </a>
              <button class="btn btn-outline-primary btn-sm" type="button" onclick="requestRefill(<?= (int)$p['id'] ?>)">
                <i class="fas fa-sync-alt me-1"></i>إعادة صرف
              </button>
            </div>

        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php else: ?>
<script>
function requestRefill(appointmentId){
  const formData = new FormData();
  formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);
  fetch('<?= $this->url('/patient/prescriptions') ?>/'+appointmentId+'/refill', {
    method: 'POST',
    body: formData
  }).then(r=>r.json()).then(d=>{
    if(d.success){
      Doctorna?.utils?.showToast?.(d.message,'success');
      alert(d.message);
    } else {
      alert(d.message || 'تعذر إرسال الطلب');
    }
  }).catch(()=>alert('تعذر إرسال الطلب'));
}
</script>

  <div class="text-center py-5">
    <i class="fas fa-prescription-bottle fa-3x text-muted mb-3"></i>
    <h6 class="text-muted">لا توجد وصفات طبية حتى الآن</h6>
  </div>
<?php endif; ?>

