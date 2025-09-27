<?php
/**
 * Register View
 */
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <h3 class="text-center mb-4 fw-bold"><?= $title ?></h3>
        <form method="POST" action="<?= $this->url('/register') ?>" class="card border-0 shadow-sm p-4" novalidate>
            <?= $this->csrfField() ?>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">الاسم الكامل</label>
                    <input type="text" name="name" class="form-control <?= $this->hasError('name') ? 'is-invalid' : '' ?>" value="<?= $this->escape($this->old('name')) ?>" required>
                    <?php if ($this->hasError('name')): ?><div class="invalid-feedback"><?= implode('<br>', $this->error('name')) ?></div><?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" class="form-control <?= $this->hasError('email') ? 'is-invalid' : '' ?>" value="<?= $this->escape($this->old('email')) ?>" required>
                    <?php if ($this->hasError('email')): ?><div class="invalid-feedback"><?= implode('<br>', $this->error('email')) ?></div><?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">الهاتف</label>
                    <input type="text" name="phone" class="form-control <?= $this->hasError('phone') ? 'is-invalid' : '' ?>" value="<?= $this->escape($this->old('phone')) ?>" required>
                    <?php if ($this->hasError('phone')): ?><div class="invalid-feedback"><?= implode('<br>', $this->error('phone')) ?></div></div><?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">الدور</label>
                    <select name="role" class="form-select <?= $this->hasError('role') ? 'is-invalid' : '' ?>" required>
                        <option value="<?= ROLE_PATIENT ?>" <?= ($this->old('role', ROLE_PATIENT) === ROLE_PATIENT) ? 'selected' : '' ?>>مريض</option>
                        <option value="<?= ROLE_DOCTOR ?>" <?= ($this->old('role') === ROLE_DOCTOR) ? 'selected' : '' ?>>طبيب</option>
                    </select>
                    <?php if ($this->hasError('role')): ?><div class="invalid-feedback"><?= implode('<br>', $this->error('role')) ?></div><?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">كلمة المرور</label>
                    <input type="password" name="password" class="form-control <?= $this->hasError('password') ? 'is-invalid' : '' ?>" required>
                    <?php if ($this->hasError('password')): ?><div class="invalid-feedback"><?= implode('<br>', $this->error('password')) ?></div><?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">تأكيد كلمة المرور</label>
                    <input type="password" name="password_confirmation" class="form-control <?= $this->hasError('password_confirmation') ? 'is-invalid' : '' ?>" required>
                    <?php if ($this->hasError('password_confirmation')): ?><div class="invalid-feedback"><?= implode('<br>', $this->error('password_confirmation')) ?></div><?php endif; ?>
                </div>
            </div>

            <div id="doctor-fields" style="display: none;">
                <hr>
                <h5 class="fw-bold mb-3">بيانات الطبيب</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">التخصص</label>
                        <select name="specialization_id" class="form-select <?= $this->hasError('specialization_id') ? 'is-invalid' : '' ?>">
                            <option value="">اختر التخصص</option>
                            <?php foreach ($specializations as $spec): ?>
                                <option value="<?= $spec['id'] ?>" <?= ($this->old('specialization_id') == $spec['id']) ? 'selected' : '' ?>>
                                    <?= $this->escape($spec['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($this->hasError('specialization_id')): ?><div class="invalid-feedback"><?= implode('<br>', $this->error('specialization_id')) ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">رقم الترخيص</label>
                        <input type="text" name="license_number" class="form-control <?= $this->hasError('license_number') ? 'is-invalid' : '' ?>" value="<?= $this->escape($this->old('license_number')) ?>">
                        <?php if ($this->hasError('license_number')): ?><div class="invalid-feedback"><?= implode('<br>', $this->error('license_number')) ?></div><?php endif; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">سنوات الخبرة</label>
                        <input type="number" name="experience_years" min="0" class="form-control" value="<?= $this->escape($this->old('experience_years')) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">الرسوم الاستشارية (ر.س)</label>
                        <input type="number" step="0.01" name="consultation_fee" class="form-control" value="<?= $this->escape($this->old('consultation_fee')) ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">نبذة عن الطبيب</label>
                    <textarea name="biography" rows="3" class="form-control"><?= $this->escape($this->old('biography')) ?></textarea>
                </div>
            </div>

            <div class="mb-3 form-check">
                <input class="form-check-input" type="checkbox" id="terms" name="terms" value="1" <?= $this->old('terms') ? 'checked' : '' ?> required>
                <label class="form-check-label" for="terms">
                    أوافق على <a href="<?= $this->url('/terms') ?>" target="_blank">الشروط والأحكام</a>
                </label>
                <?php if ($this->hasError('terms')): ?><div class="text-danger small"><?= implode('<br>', $this->error('terms')) ?></div><?php endif; ?>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-user-plus me-2"></i>
                    إنشاء الحساب
                </button>
            </div>
        </form>

        <p class="text-center text-muted mt-3">
            لديك حساب بالفعل؟ <a href="<?= $this->url('/login') ?>" class="fw-bold">تسجيل الدخول</a>
        </p>
    </div>
</div>

<script>
function toggleDoctorFields() {
    const role = document.querySelector('select[name="role"]').value;
    document.getElementById('doctor-fields').style.display = (role === 'doctor') ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.querySelector('select[name="role"]');
    roleSelect.addEventListener('change', toggleDoctorFields);
    toggleDoctorFields();
});
</script>

