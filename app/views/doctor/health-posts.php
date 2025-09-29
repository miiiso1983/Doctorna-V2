<?php
// Doctor Health Posts Management View
?>
<div class="row">
  <div class="col-12 mb-4">
    <div class="d-flex justify-content-between align-items-center">
      <h4 class="mb-0"><i class="fas fa-notes-medical me-2"></i>منشوراتي الصحية</h4>
      <a href="<?= $this->url('/doctor/health-posts/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>إنشاء منشور جديد
      </a>
    </div>
  </div>

  <!-- Statistics Cards -->
  <div class="col-md-3 mb-4">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="flex-shrink-0">
            <i class="fas fa-file-alt fa-2x text-primary"></i>
          </div>
          <div class="flex-grow-1 ms-3">
            <h6 class="text-muted mb-1">إجمالي المنشورات</h6>
            <h3 class="mb-0"><?= $stats['total'] ?? 0 ?></h3>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3 mb-4">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="flex-shrink-0">
            <i class="fas fa-clock fa-2x text-warning"></i>
          </div>
          <div class="flex-grow-1 ms-3">
            <h6 class="text-muted mb-1">في الانتظار</h6>
            <h3 class="mb-0"><?= $stats['pending'] ?? 0 ?></h3>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3 mb-4">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="flex-shrink-0">
            <i class="fas fa-check-circle fa-2x text-success"></i>
          </div>
          <div class="flex-grow-1 ms-3">
            <h6 class="text-muted mb-1">معتمدة</h6>
            <h3 class="mb-0"><?= $stats['approved'] ?? 0 ?></h3>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3 mb-4">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="flex-shrink-0">
            <i class="fas fa-times-circle fa-2x text-danger"></i>
          </div>
          <div class="flex-grow-1 ms-3">
            <h6 class="text-muted mb-1">مرفوضة</h6>
            <h3 class="mb-0"><?= $stats['rejected'] ?? 0 ?></h3>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Posts List -->
  <div class="col-12">
    <div class="card border-0 shadow-sm">
      <div class="card-header">
        <h5 class="mb-0">قائمة المنشورات</h5>
      </div>
      <div class="card-body">
        <?php if (empty($posts['data'])): ?>
          <div class="text-center py-5">
            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
            <p class="text-muted">لا توجد منشورات حتى الآن</p>
            <a href="<?= $this->url('/doctor/health-posts/create') ?>" class="btn btn-primary">
              إنشاء منشور جديد
            </a>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>العنوان</th>
                  <th>الفئة</th>
                  <th>الحالة</th>
                  <th>تاريخ الإنشاء</th>
                  <th>الإجراءات</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($posts['data'] as $post): ?>
                  <tr>
                    <td>
                      <div class="d-flex align-items-center">
                        <?php if ($post['image_path']): ?>
                          <img src="<?= $this->url('/' . $post['image_path']) ?>" 
                               alt="<?= htmlspecialchars($post['title']) ?>" 
                               class="rounded me-2" 
                               style="width: 50px; height: 50px; object-fit: cover;">
                        <?php endif; ?>
                        <div>
                          <strong><?= htmlspecialchars($post['title']) ?></strong>
                          <br>
                          <small class="text-muted">
                            <?= mb_substr(strip_tags($post['content']), 0, 60) ?>...
                          </small>
                        </div>
                      </div>
                    </td>
                    <td>
                      <?php if ($post['category']): ?>
                        <span class="badge bg-info"><?= htmlspecialchars($post['category']) ?></span>
                      <?php else: ?>
                        <span class="text-muted">-</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php
                      $statusClass = [
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger'
                      ];
                      $statusText = [
                        'pending' => 'في الانتظار',
                        'approved' => 'معتمد',
                        'rejected' => 'مرفوض'
                      ];
                      ?>
                      <span class="badge bg-<?= $statusClass[$post['status']] ?>">
                        <?= $statusText[$post['status']] ?>
                      </span>
                    </td>
                    <td>
                      <small><?= date('Y-m-d', strtotime($post['created_at'])) ?></small>
                    </td>
                    <td>
                      <div class="btn-group btn-group-sm">
                        <a href="<?= $this->url('/doctor/health-posts/' . $post['id'] . '/edit') ?>" 
                           class="btn btn-outline-primary" 
                           title="تعديل">
                          <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" 
                                class="btn btn-outline-danger" 
                                onclick="deletePost(<?= $post['id'] ?>)" 
                                title="حذف">
                          <i class="fas fa-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <?php if ($posts['last_page'] > 1): ?>
            <nav class="mt-4">
              <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $posts['last_page']; $i++): ?>
                  <li class="page-item <?= $i == $posts['current_page'] ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                  </li>
                <?php endfor; ?>
              </ul>
            </nav>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
function deletePost(postId) {
  if (!confirm('هل أنت متأكد من حذف هذا المنشور؟')) {
    return;
  }

  fetch(`/doctor/health-posts/${postId}/delete`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '<?= $this->csrf() ?>',
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showToast(data.message, 'success');
      setTimeout(() => location.reload(), 1000);
    } else {
      showToast(data.message, 'error');
    }
  })
  .catch(error => {
    showToast('حدث خطأ أثناء حذف المنشور', 'error');
  });
}
</script>

