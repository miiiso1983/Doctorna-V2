<?php
// Patient Profile View
$patient = $patient ?? [];
$user = $patient; // contains user fields joined in Patient::getByUserId
?>
<div class="row mb-4">
  <div class="col-12 d-flex align-items-center">
    <div class="me-3">
      <?php if (!empty($user['avatar'])): ?>
        <img src="<?= $this->asset('uploads/profiles/' . $user['avatar']) ?>" class="rounded-circle" width="72" height="72" alt="">
      <?php else: ?>
        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width:72px;height:72px;">
          <i class="fas fa-user text-white"></i>
        </div>
      <?php endif; ?>
    </div>
    <div>
      <h5 class="mb-0">الملف الشخصي</h5>
      <small class="text-muted">قم بتحديث بياناتك الشخصية والطبية</small>
    </div>
  </div>
</div>

<form method="post" enctype="multipart/form-data" action="<?= $this->url('/patient/profile') ?>" class="card p-3">
  <?= $this->csrfField() ?>
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">الاسم</label>
      <input type="text" name="name" class="form-control" value="<?= $this->escape($user['name'] ?? '') ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">البريد الإلكتروني</label>
      <input type="email" name="email" class="form-control" value="<?= $this->escape($user['email'] ?? '') ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">الهاتف</label>
      <input type="text" name="phone" class="form-control" value="<?= $this->escape($user['phone'] ?? '') ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">المدينة</label>
      <input type="text" name="city" class="form-control" value="<?= $this->escape($user['city'] ?? '') ?>">
    </div>
    <div class="col-md-12">
      <label class="form-label">العنوان</label>
      <input type="text" name="address" class="form-control" value="<?= $this->escape($user['address'] ?? '') ?>">
    </div>

    <div class="col-md-4">
      <label class="form-label">الجنس</label>
      <select name="gender" class="form-select">
        <option value="">—</option>
        <option value="male" <?= (isset($user['gender']) && $user['gender']==='male')?'selected':'' ?>>ذكر</option>
        <option value="female" <?= (isset($user['gender']) && $user['gender']==='female')?'selected':'' ?>>أنثى</option>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">تاريخ الميلاد</label>
      <input type="date" name="date_of_birth" class="form-control" value="<?= $this->escape($patient['date_of_birth'] ?? '') ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">فصيلة الدم</label>
      <input type="text" name="blood_type" class="form-control" value="<?= $this->escape($patient['blood_type'] ?? '') ?>" placeholder="مثال: O+">
    </div>

    <div class="col-md-12">
      <label class="form-label">الحساسية</label>
      <textarea name="allergies" class="form-control" rows="2"><?= $this->escape($patient['allergies'] ?? '') ?></textarea>
    </div>
    <div class="col-md-12">
      <label class="form-label">الأدوية الحالية</label>
      <textarea name="current_medications" class="form-control" rows="2"><?= $this->escape($patient['current_medications'] ?? '') ?></textarea>
    </div>
    <div class="col-md-12">
      <label class="form-label">التاريخ الطبي</label>
      <textarea name="medical_history" class="form-control" rows="3"><?= $this->escape($patient['medical_history'] ?? '') ?></textarea>
    </div>

    <div class="col-md-6">
      <label class="form-label">شخص للطوارئ</label>
      <input type="text" name="emergency_contact" class="form-control" value="<?= $this->escape($patient['emergency_contact'] ?? '') ?>">
    </div>

    <div class="col-md-6">
      <label class="form-label">الصورة الشخصية</label>
      <input type="file" name="avatar" class="form-control" accept="image/*">
    </div>
  </div>

  <div class="mt-3 d-flex gap-2">
    <button class="btn btn-primary" type="submit">حفظ</button>
    <a href="<?= $this->url('/patient') ?>" class="btn btn-light">إلغاء</a>
  </div>
</form>

