<?php
// Doctor Settings View
?>
<div class="row">
  <div class="col-12 mb-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0"><i class="fas fa-cog me-2"></i>الإعدادات</h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4">
            <div class="p-3 border rounded h-100">
              <div class="d-flex align-items-center mb-2">
                <i class="fas fa-user fa-lg text-primary me-2"></i>
                <strong>الملف الشخصي</strong>
              </div>
              <p class="text-muted small mb-3">تحديث معلوماتك الشخصية والرسوم والتخصص.</p>
              <a href="<?= $this->url('/doctor/profile') ?>" class="btn btn-sm btn-primary">
                فتح الملف الشخصي
              </a>
            </div>
          </div>
          <div class="col-md-4">
            <div class="p-3 border rounded h-100">
              <div class="d-flex align-items-center mb-2">
                <i class="fas fa-calendar-alt fa-lg text-success me-2"></i>
                <strong>الجدول والمواعيد</strong>
              </div>
              <p class="text-muted small mb-3">إدارة أوقات العمل وعدد المواعيد اليومية.</p>
              <a href="<?= $this->url('/doctor/schedule') ?>" class="btn btn-sm btn-success">
                إدارة الجدول
              </a>
            </div>
          </div>
          <div class="col-md-4">
            <div class="p-3 border rounded h-100">
              <div class="d-flex align-items-center mb-2">
                <i class="fas fa-lock fa-lg text-warning me-2"></i>
                <strong>الأمان</strong>
              </div>
              <p class="text-muted small mb-3">تغيير كلمة المرور عبر رابط إعادة التعيين.</p>
              <a href="<?= $this->url('/forgot-password') ?>" class="btn btn-sm btn-warning">
                تغيير كلمة المرور
              </a>
            </div>
          </div>
        </div>
        <hr class="my-4">
        <div class="row g-3">
          <div class="col-md-6">
            <div class="p-3 border rounded h-100">
              <div class="d-flex align-items-center mb-2">
                <i class="fas fa-bell fa-lg text-info me-2"></i>
                <strong>الإشعارات</strong>
              </div>
              <p class="text-muted small mb-3">ستتوفر إعدادات الإشعارات لاحقاً.</p>
              <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                قريباً
              </button>
            </div>
          </div>
          <div class="col-md-6">
            <div class="p-3 border rounded h-100">
              <div class="d-flex align-items-center mb-2">
                <i class="fas fa-map-marker-alt fa-lg text-danger me-2"></i>
                <strong>الموقع والخرائط</strong>
              </div>
              <p class="text-muted small mb-3">لتحسين الظهور في البحث بالخريطة، حدّث العنوان من الملف الشخصي.</p>
              <a href="<?= $this->url('/doctor/profile') ?>" class="btn btn-sm btn-outline-primary">
                تحديث العنوان
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

