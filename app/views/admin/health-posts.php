<?php
// Admin Health Posts Management View
?>
<div class="row">
  <div class="col-12 mb-4">
    <h4 class="mb-0"><i class="fas fa-notes-medical me-2"></i>إدارة المنشورات الصحية</h4>
    <p class="text-muted">مراجعة والموافقة على المنشورات الصحية من الأطباء</p>
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

  <!-- Filter -->
  <div class="col-12 mb-4">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex flex-wrap gap-2">
          <a href="<?= $this->url('/admin/health-posts') ?>" 
             class="btn btn-sm <?= !$selected_status ? 'btn-primary' : 'btn-outline-primary' ?>">
            الكل
          </a>
          <a href="<?= $this->url('/admin/health-posts?status=pending') ?>" 
             class="btn btn-sm <?= $selected_status == 'pending' ? 'btn-warning' : 'btn-outline-warning' ?>">
            في الانتظار
          </a>
          <a href="<?= $this->url('/admin/health-posts?status=approved') ?>" 
             class="btn btn-sm <?= $selected_status == 'approved' ? 'btn-success' : 'btn-outline-success' ?>">
            معتمدة
          </a>
          <a href="<?= $this->url('/admin/health-posts?status=rejected') ?>" 
             class="btn btn-sm <?= $selected_status == 'rejected' ? 'btn-danger' : 'btn-outline-danger' ?>">
            مرفوضة
          </a>
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
            <p class="text-muted">لا توجد منشورات</p>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th style="width: 40%;">المنشور</th>
                  <th>الطبيب</th>
                  <th>الفئة</th>
                  <th>الحالة</th>
                  <th>التاريخ</th>
                  <th>الإجراءات</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($posts['data'] as $post): ?>
                  <tr>
                    <td>
                      <div class="d-flex align-items-start">
                        <?php if ($post['image_path']): ?>
                          <img src="<?= $this->url('/' . $post['image_path']) ?>" 
                               alt="<?= htmlspecialchars($post['title']) ?>" 
                               class="rounded me-2" 
                               style="width: 60px; height: 60px; object-fit: cover;">
                        <?php endif; ?>
                        <div>
                          <strong><?= htmlspecialchars($post['title']) ?></strong>
                          <br>
                          <small class="text-muted">
                            <?= mb_substr(strip_tags($post['content']), 0, 80) ?>...
                          </small>
                        </div>
                      </div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center">
                        <?php if ($post['doctor_avatar']): ?>
                          <img src="<?= $this->url('/' . $post['doctor_avatar']) ?>" 
                               class="rounded-circle me-2" 
                               style="width: 32px; height: 32px; object-fit: cover;">
                        <?php endif; ?>
                        <div>
                          <small>د. <?= htmlspecialchars($post['doctor_name']) ?></small>
                          <br>
                          <small class="text-muted"><?= htmlspecialchars($post['specialization_name'] ?? '') ?></small>
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
                        <button type="button" 
                                class="btn btn-outline-info" 
                                onclick="viewPost(<?= $post['id'] ?>)" 
                                title="عرض">
                          <i class="fas fa-eye"></i>
                        </button>
                        <?php if ($post['status'] != 'approved'): ?>
                          <button type="button" 
                                  class="btn btn-outline-success" 
                                  onclick="approvePost(<?= $post['id'] ?>)" 
                                  title="موافقة">
                            <i class="fas fa-check"></i>
                          </button>
                        <?php endif; ?>
                        <?php if ($post['status'] != 'rejected'): ?>
                          <button type="button" 
                                  class="btn btn-outline-warning" 
                                  onclick="rejectPost(<?= $post['id'] ?>)" 
                                  title="رفض">
                            <i class="fas fa-times"></i>
                          </button>
                        <?php endif; ?>
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
                <?php
                $statusParam = $selected_status ? '&status=' . $selected_status : '';
                for ($i = 1; $i <= $posts['last_page']; $i++):
                ?>
                  <li class="page-item <?= $i == $posts['current_page'] ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?><?= $statusParam ?>"><?= $i ?></a>
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

<!-- Post Modal -->
<div class="modal fade" id="postModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="postModalTitle"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="postModalBody"></div>
    </div>
  </div>
</div>

<script>
const posts = <?= json_encode($posts['data']) ?>;

function viewPost(postId) {
  const post = posts.find(p => p.id == postId);
  if (!post) return;

  const modal = new bootstrap.Modal(document.getElementById('postModal'));
  document.getElementById('postModalTitle').textContent = post.title;
  document.getElementById('postModalBody').innerHTML = `
    ${post.image_path ? `<img src="/${post.image_path}" class="img-fluid rounded mb-3">` : ''}
    ${post.category ? `<span class="badge bg-info mb-3">${post.category}</span>` : ''}
    <div style="white-space: pre-wrap; line-height: 1.8;">${post.content}</div>
    <hr class="my-4">
    <div class="d-flex align-items-center">
      ${post.doctor_avatar ? `<img src="/${post.doctor_avatar}" class="rounded-circle me-3" style="width: 48px; height: 48px;">` : ''}
      <div>
        <strong>د. ${post.doctor_name}</strong><br>
        <small class="text-muted">${post.specialization_name || ''}</small>
      </div>
    </div>
  `;
  modal.show();
}

function approvePost(postId) {
  if (!confirm('هل أنت متأكد من الموافقة على هذا المنشور؟')) return;
  
  fetch(`/admin/health-posts/${postId}/approve`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '<?= $this->csrf() ?>',
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(r => r.json())
  .then(data => {
    Doctorna.showToast(data.message, data.success ? 'success' : 'error');
    if (data.success) setTimeout(() => location.reload(), 1000);
  });
}

function rejectPost(postId) {
  if (!confirm('هل أنت متأكد من رفض هذا المنشور؟')) return;
  
  fetch(`/admin/health-posts/${postId}/reject`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '<?= $this->csrf() ?>',
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(r => r.json())
  .then(data => {
    Doctorna.showToast(data.message, data.success ? 'success' : 'error');
    if (data.success) setTimeout(() => location.reload(), 1000);
  });
}

function deletePost(postId) {
  if (!confirm('هل أنت متأكد من حذف هذا المنشور نهائياً؟')) return;
  
  fetch(`/admin/health-posts/${postId}/delete`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '<?= $this->csrf() ?>',
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(r => r.json())
  .then(data => {
    Doctorna.showToast(data.message, data.success ? 'success' : 'error');
    if (data.success) setTimeout(() => location.reload(), 1000);
  });
}
</script>

