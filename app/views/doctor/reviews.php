<?php
// Doctor Reviews List View
?>
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-star me-2"></i>التقييمات</h5>
      </div>
      <div class="card-body">
        <?php if (!empty($reviews['data'])): ?>
          <div class="list-group">
            <?php foreach ($reviews['data'] as $r): ?>
              <div class="list-group-item">
                <div class="d-flex">
                  <?php if (!empty($r['patient_avatar'])): ?>
                    <img src="<?= $this->asset('uploads/profiles/' . $r['patient_avatar']) ?>" class="rounded-circle me-3" width="40" height="40" alt="">
                  <?php else: ?>
                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px;">
                      <i class="fas fa-user text-white"></i>
                    </div>
                  <?php endif; ?>
                  <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center">
                      <strong><?= $this->escape($r['patient_name'] ?? '—') ?></strong>
                      <small class="text-muted"><?= $this->timeAgo($r['created_at'] ?? '') ?></small>
                    </div>
                    <div class="text-warning my-1">
                      <?php for ($i=1;$i<=5;$i++): ?>
                        <i class="fas fa-star<?= $i <= (int)$r['rating'] ? '' : '-o' ?>"></i>
                      <?php endfor; ?>
                    </div>
                    <div class="text-muted small mb-1">الخدمة: <?= $this->formatCurrency($doctor['consultation_fee'] ?? 0) ?></div>
                    <p class="mb-0"><?= $this->escape($r['comment'] ?? '') ?></p>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="mt-3">
            <?= $this->paginate($reviews) ?>
          </div>
        <?php else: ?>
          <div class="text-center py-5">
            <i class="fas fa-star-half-alt fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">لا توجد تقييمات حتى الآن</h5>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

