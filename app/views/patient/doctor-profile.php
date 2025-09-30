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
        <div class="card-header">المواعيد المتاحة</div>
        <div class="card-body">
          <?php if (empty($available_slots)): ?>
            <div class="text-muted">لا توجد مواعيد متاحة في الأيام القادمة. يرجى المحاولة لاحقًا.</div>
          <?php else: ?>
            <?php
              $daysMap = [
                'Sunday'=>'الأحد','Monday'=>'الاثنين','Tuesday'=>'الثلاثاء','Wednesday'=>'الأربعاء',
                'Thursday'=>'الخميس','Friday'=>'الجمعة','Saturday'=>'السبت'
              ];
              $dates = array_keys($available_slots);
              $firstDate = $dates[0];
            ?>
            <div class="row g-2 align-items-center mb-2">
              <div class="col-auto">
                <label for="slotDate" class="form-label mb-0">اختر اليوم:</label>
              </div>
              <div class="col-auto">
                <select id="slotDate" class="form-select form-select-sm">
                  <?php foreach ($dates as $d): $dt = new DateTime($d); $label = ($daysMap[$dt->format('l')] ?? $dt->format('l')) . ' ' . $dt->format('Y-m-d'); ?>
                    <option value="<?= $d ?>"<?= $d === $firstDate ? ' selected' : '' ?>><?= $label ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div id="slotTimes" class="d-flex flex-wrap gap-2">
              <?php foreach ($available_slots[$firstDate] as $t): ?>
                <button type="button" class="btn btn-outline-primary btn-sm slot-btn" data-date="<?= $firstDate ?>" data-time="<?= $t ?>"><?= $t ?></button>
              <?php endforeach; ?>
            </div>

            <div class="form-text mt-2">بالنقر على الوقت سيتم تعبئة حقول الحجز تلقائيًا.</div>

            <script>
              (function(){
                const slots = <?= json_encode($available_slots) ?>;
                const slotDate = document.getElementById('slotDate');
                const slotTimes = document.getElementById('slotTimes');
                function renderTimes(date){
                  slotTimes.innerHTML = '';
                  (slots[date]||[]).forEach(function(t){
                    const b = document.createElement('button');
                    b.type = 'button'; b.className = 'btn btn-outline-primary btn-sm slot-btn';
                    b.dataset.date = date; b.dataset.time = t; b.textContent = t;
                    b.addEventListener('click', onPick);
                    slotTimes.appendChild(b);
                  });
                  if ((slots[date]||[]).length === 0) {
                    slotTimes.innerHTML = '<div class="text-muted">لا توجد أوقات متاحة لهذا اليوم.</div>';
                  }
                }
                function onPick(e){
                  const d = e.currentTarget.dataset.date;
                  const t = e.currentTarget.dataset.time;
                  const f = document.getElementById('bookForm');
                  if (!f) return;
                  const dateInput = f.querySelector('input[name="appointment_date"]');
                  const timeInput = f.querySelector('input[name="appointment_time"]');
                  dateInput && (dateInput.value = d);
                  timeInput && (timeInput.value = t);
                  // Scroll to form
                  f.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                slotDate && slotDate.addEventListener('change', function(){ renderTimes(this.value); });
                // Initial bind for existing buttons
                slotTimes.querySelectorAll('.slot-btn').forEach(function(btn){ btn.addEventListener('click', onPick); });
              })();
            </script>
          <?php endif; ?>
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

