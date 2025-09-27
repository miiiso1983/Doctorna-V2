<?php
// Doctor Profile View
?>
<div class="row mb-4">
  <div class="col-12">
    <?php if ($this->hasFlash('success')): ?><div class="alert alert-success"><?= $this->flash('success') ?></div><?php endif; ?>
    <?php if ($this->hasFlash('error')): ?><div class="alert alert-danger"><?= $this->flash('error') ?></div><?php endif; ?>
    <div class="card">
      <div class="card-header"><h5 class="mb-0"><i class="fas fa-id-card me-2"></i>الملف الشخصي</h5></div>
      <div class="card-body">
        <form method="POST" action="<?= $this->url('/doctor/profile') ?>" enctype="multipart/form-data">
          <?= $this->csrfField() ?>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">الاسم الكامل</label>
              <input type="text" class="form-control" name="name" value="<?= $this->escape($doctor['name'] ?? '') ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">البريد الإلكتروني</label>
              <input type="email" class="form-control" name="email" value="<?= $this->escape($doctor['email'] ?? '') ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">رقم الجوال</label>
              <input type="text" class="form-control" name="phone" value="<?= $this->escape($doctor['phone'] ?? '') ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">التخصص</label>
              <select class="form-select" name="specialization_id" required>
                <option value="">اختر التخصص</option>
                <?php foreach ($specializations as $sp): ?>
                  <option value="<?= (int)$sp['id'] ?>" <?= (!empty($doctor['specialization_id']) && (int)$doctor['specialization_id']===(int)$sp['id'])?'selected':'' ?>>
                    <?= $this->escape($sp['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">سنوات الخبرة</label>
              <input type="number" class="form-control" name="experience_years" min="0" max="80" value="<?= (int)($doctor['experience_years'] ?? 0) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">رسوم الاستشارة</label>
              <input type="number" class="form-control" name="consultation_fee" min="0" step="0.01" value="<?= $this->escape($doctor['consultation_fee'] ?? '0') ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">اسم العيادة</label>
              <input type="text" class="form-control" name="clinic_name" value="<?= $this->escape($doctor['clinic_name'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">هاتف العيادة</label>
              <input type="text" class="form-control" name="clinic_phone" value="<?= $this->escape($doctor['clinic_phone'] ?? '') ?>">
            </div>
            <div class="col-12">
              <label class="form-label">عنوان العيادة</label>
              <input type="text" class="form-control" name="clinic_address" value="<?= $this->escape($doctor['clinic_address'] ?? '') ?>">
            </div>
            <div class="col-12">
              <label class="form-label">نبذة تعريفية</label>
              <textarea class="form-control" rows="4" name="biography"><?= $this->escape($doctor['biography'] ?? '') ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">الترخيص المهني</label>
              <input type="text" class="form-control" name="license_number" value="<?= $this->escape($doctor['license_number'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">الصورة الشخصية</label>
              <input type="file" class="form-control" name="avatar" accept="image/png,image/jpeg">
            </div>
            <div class="col-md-6">
              <label class="form-label">المدينة</label>
              <input type="text" class="form-control" name="city" value="<?= $this->escape($doctor['city'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">العنوان</label>
              <input type="text" class="form-control" name="address" value="<?= $this->escape($doctor['address'] ?? '') ?>">
            </div>
          </div>
          <div class="text-end mt-3">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>حفظ التغييرات</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

