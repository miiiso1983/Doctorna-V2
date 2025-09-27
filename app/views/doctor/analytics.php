<?php
// Doctor Analytics View
?>
<div class="row mb-4">
  <div class="col-12">
    <div class="row g-3">
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <div class="text-muted">إجمالي المواعيد</div>
            <div class="h4 mb-0"><?= (int)($kpis['total'] ?? 0) ?></div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <div class="text-muted">المكتملة</div>
            <div class="h4 mb-0 text-primary"><?= (int)($kpis['completed'] ?? 0) ?></div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <div class="text-muted">المؤكدة</div>
            <div class="h4 mb-0 text-success"><?= (int)($kpis['confirmed'] ?? 0) ?></div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <div class="text-muted">الملغاة</div>
            <div class="h4 mb-0 text-danger"><?= (int)($kpis['cancelled'] ?? 0) ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row mb-4">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header"><strong>الإيرادات التقريبية</strong></div>
      <div class="card-body">
        <div class="display-6 text-success"><?= $this->formatCurrency($kpis['revenue'] ?? 0) ?></div>
        <small class="text-muted">حساب تقريبي بناءً على رسوم الاستشارة وعدد المواعيد المكتملة</small>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card">
      <div class="card-header"><strong>الاتجاه خلال 14 يومًا</strong></div>
      <div class="card-body">
        <?php if (!empty($trend)): ?>
          <ul class="list-group">
            <?php foreach ($trend as $t): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <span><?= $this->formatArabicDate($t['d']) ?></span>
                <span class="badge bg-info rounded-pill"><?= (int)$t['c'] ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <div class="text-muted">لا توجد بيانات كافية بعد</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

