<?php
// Patient - Doctor profile view (minimal)
?>
<div class="container py-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h3 class="mb-0">ملف الطبيب</h3>
    <a href="/patient/search-doctors" class="btn btn-outline-secondary">عودة</a>
  </div>

  <?php if (!empty($doctor)): ?>
    <div class="card mb-3">
      <div class="card-body d-flex gap-3 align-items-center">
        <img src="<?= $this->asset($doctor['avatar'] ?? 'images/default-avatar.png') ?>" alt="" class="rounded" style="width:64px;height:64px;object-fit:cover;">
        <div>
          <div class="h5 mb-1 mb-0"><?= $this->escape($doctor['name'] ?? '') ?></div>
          <div class="text-muted small">
            الاختصاص: <?= $this->escape($doctor['specialization_name'] ?? '—') ?> · المدينة: <?= $this->escape($doctor['city'] ?? '—') ?>
          </div>
          <div class="text-warning small mt-1">
            التقييم: <?= number_format((float)($doctor['rating'] ?? 0), 1) ?> (<?= (int)($review_stats['total_reviews'] ?? $doctor['total_reviews'] ?? 0) ?> مراجعة)
          </div>
          <div class="small mt-1">أجور الاستشارة: <?= $this->formatCurrency($doctor['consultation_fee'] ?? 0) ?></div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <div class="row g-3">
    <div class="col-12 col-lg-7">
      <div class="card mb-3">
        <div class="card-header">المواعيد المتاحة (قريبًا)</div>
        <div class="card-body">
          <div class="text-muted">سيُعرض التقويم وخيارات المواعيد هنا.</div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">المراجعات</div>
        <div class="card-body">
          <?php
            $rows = isset($reviews['data']) ? $reviews['data'] : (is_array($reviews) ? $reviews : []);
          ?>
          <?php if (!empty($rows)): ?>
            <?php foreach ($rows as $r): ?>
              <div class="border-bottom py-3">
                <div class="d-flex align-items-center gap-2 mb-1">
                  <strong><?= $this->escape($r['patient_name'] ?? 'مريض') ?></strong>
                  <small class="text-muted"><?= $this->timeAgo($r['created_at'] ?? '') ?></small>
                </div>
                <div class="text-warning mb-1">
                  <?php for ($i=1;$i<=5;$i++): ?>
                    <i class="fas fa-star<?= $i <= (int)($r['rating'] ?? 0) ? '' : '-o' ?>"></i>
                  <?php endfor; ?>
                </div>
                <div class="text-muted"><?= nl2br($this->escape($r['review'] ?? '')) ?></div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="text-center text-muted py-4">لا توجد مراجعات بعد.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-5">
      <div class="card">
        <div class="card-header">حجز موعد</div>
        <div class="card-body">
          <form id="bookForm" method="post" action="/patient/appointments/book" onsubmit="return Doctorna.forms.submit(this)">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
            <input type="hidden" name="doctor_id" value="<?= (int)($doctor['id'] ?? 0) ?>">
            <div class="mb-2">
              <label class="form-label">تاريخ الموعد</label>
              <input type="date" name="appointment_date" class="form-control" required>
            </div>
            <div class="mb-2">
              <label class="form-label">وقت الموعد</label>
              <input type="time" name="appointment_time" class="form-control" required>
            </div>
            <div class="mb-2">
              <label class="form-label">الأعراض</label>
              <textarea name="symptoms" class="form-control" rows="3" required></textarea>
            </div>
            <div class="mb-2">
              <label class="form-label">ملاحظات</label>
              <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>
            <button class="btn btn-primary w-100">إرسال طلب الحجز</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

