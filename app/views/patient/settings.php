<?php
/**
 * Patient Settings View
 */
?>
<div class="row">
  <div class="col-12 col-lg-8">
    <div class="card shadow-sm">
      <div class="card-header bg-white">
        <h5 class="mb-0">الإعدادات</h5>
      </div>
      <div class="card-body">
        <form action="/patient/settings" method="post">
          <?= $this->csrfField() ?>

          <div class="mb-3">
            <label class="form-label">اللغة</label>
            <select name="language" class="form-select">
              <option value="ar" <?= ($settings['language'] ?? 'ar') === 'ar' ? 'selected' : '' ?>>العربية</option>
              <option value="en" <?= ($settings['language'] ?? '') === 'en' ? 'selected' : '' ?>>English</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">التنبيهات</label>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="notify_email" id="notify_email" value="1" <?= !empty($settings['notify_email']) ? 'checked' : '' ?>>
              <label class="form-check-label" for="notify_email">استلام تنبيهات عبر البريد الإلكتروني</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="notify_sms" id="notify_sms" value="1" <?= !empty($settings['notify_sms']) ? 'checked' : '' ?>>
              <label class="form-check-label" for="notify_sms">استلام تنبيهات عبر الرسائل النصية</label>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">المنطقة الزمنية</label>
            <input type="text" class="form-control" name="timezone" value="<?= $this->escape($settings['timezone'] ?? APP_TIMEZONE) ?>">
          </div>

          <button type="submit" class="btn btn-primary">حفظ الإعدادات</button>
          <a href="/patient" class="btn btn-outline-secondary">رجوع</a>
        </form>
      </div>
    </div>
  </div>
</div>

