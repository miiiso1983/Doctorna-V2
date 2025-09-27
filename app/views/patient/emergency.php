<?php
// Patient Emergency Info View
?>
<div class="row mb-4">
  <div class="col-12">
    <h5 class="mb-3"><i class="fas fa-ambulance me-2"></i>معلومات الطوارئ</h5>
  </div>
</div>

<form method="post" action="<?= $this->url('/patient/emergency') ?>" class="card p-3">
  <?= $this->csrfField() ?>
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">اسم جهة الاتصال للطوارئ</label>
      <input type="text" name="emergency_contact_name" class="form-control" value="<?= $this->escape($patient['emergency_contact_name'] ?? '') ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">رقم هاتف جهة الاتصال للطوارئ</label>
      <input type="text" name="emergency_contact" class="form-control" value="<?= $this->escape($patient['emergency_contact'] ?? '') ?>">
    </div>

    <div class="col-md-4">
      <label class="form-label">فصيلة الدم</label>
      <input type="text" name="blood_type" class="form-control" value="<?= $this->escape($patient['blood_type'] ?? '') ?>" placeholder="مثال: O+">
    </div>
    <div class="col-md-4">
      <label class="form-label">الحساسية</label>
      <input type="text" name="allergies" class="form-control" value="<?= $this->escape($patient['allergies'] ?? '') ?>" placeholder="مثال: بنسلين">
    </div>
    <div class="col-md-4">
      <label class="form-label">الأدوية الحالية</label>
      <input type="text" name="current_medications" class="form-control" value="<?= $this->escape($patient['current_medications'] ?? '') ?>">
    </div>
  </div>
  <div class="mt-3">
    <button class="btn btn-primary" type="submit">حفظ</button>
    <a href="<?= $this->url('/patient') ?>" class="btn btn-light">إلغاء</a>
  </div>
</form>

