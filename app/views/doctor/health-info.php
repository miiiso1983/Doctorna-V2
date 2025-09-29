<?php
// Health Info View (Approved Posts)
?>
<div class="row">
  <div class="col-12 mb-4">
    <h4 class="mb-0"><i class="fas fa-heartbeat me-2"></i>معلومات صحية</h4>
    <p class="text-muted">مقالات ونصائح صحية من أطباء متخصصين</p>
  </div>

  <!-- Category Filter -->
  <?php if (!empty($categories)): ?>
    <div class="col-12 mb-4">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <div class="d-flex flex-wrap gap-2">
            <a href="<?= $this->url('/doctor/health-info') ?>" 
               class="btn btn-sm <?= !$selected_category ? 'btn-primary' : 'btn-outline-primary' ?>">
              الكل
            </a>
            <?php foreach ($categories as $cat): ?>
              <a href="<?= $this->url('/doctor/health-info?category=' . urlencode($cat)) ?>" 
                 class="btn btn-sm <?= $selected_category == $cat ? 'btn-primary' : 'btn-outline-primary' ?>">
                <?= htmlspecialchars($cat) ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Posts Grid -->
  <?php if (empty($posts['data'])): ?>
    <div class="col-12">
      <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
          <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
          <p class="text-muted">لا توجد منشورات صحية متاحة حالياً</p>
        </div>
      </div>
    </div>
  <?php else: ?>
    <?php foreach ($posts['data'] as $post): ?>
      <div class="col-md-6 col-lg-4 mb-4">
        <div class="card border-0 shadow-sm h-100">
          <?php if ($post['image_path']): ?>
            <img src="<?= $this->url('/' . $post['image_path']) ?>" 
                 class="card-img-top" 
                 alt="<?= htmlspecialchars($post['title']) ?>"
                 style="height: 200px; object-fit: cover;">
          <?php else: ?>
            <div class="card-img-top bg-gradient-primary d-flex align-items-center justify-content-center" 
                 style="height: 200px;">
              <i class="fas fa-notes-medical fa-3x text-white opacity-50"></i>
            </div>
          <?php endif; ?>
          
          <div class="card-body d-flex flex-column">
            <?php if ($post['category']): ?>
              <span class="badge bg-info mb-2 align-self-start">
                <?= htmlspecialchars($post['category']) ?>
              </span>
            <?php endif; ?>
            
            <h5 class="card-title"><?= htmlspecialchars($post['title']) ?></h5>
            
            <p class="card-text text-muted flex-grow-1">
              <?= mb_substr(strip_tags($post['content']), 0, 120) ?>...
            </p>
            
            <div class="d-flex align-items-center justify-content-between mt-3 pt-3 border-top">
              <div class="d-flex align-items-center">
                <?php if ($post['doctor_avatar']): ?>
                  <img src="<?= $this->url('/' . $post['doctor_avatar']) ?>" 
                       class="rounded-circle me-2" 
                       style="width: 32px; height: 32px; object-fit: cover;"
                       alt="<?= htmlspecialchars($post['doctor_name']) ?>">
                <?php else: ?>
                  <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" 
                       style="width: 32px; height: 32px;">
                    <i class="fas fa-user-md"></i>
                  </div>
                <?php endif; ?>
                <div>
                  <small class="text-muted d-block">د. <?= htmlspecialchars($post['doctor_name']) ?></small>
                  <small class="text-muted"><?= htmlspecialchars($post['specialization_name'] ?? '') ?></small>
                </div>
              </div>
              <button type="button" 
                      class="btn btn-sm btn-outline-primary" 
                      onclick="viewPost(<?= $post['id'] ?>)">
                قراءة المزيد
              </button>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

    <!-- Pagination -->
    <?php if ($posts['last_page'] > 1): ?>
      <div class="col-12">
        <nav>
          <ul class="pagination justify-content-center">
            <?php
            $categoryParam = $selected_category ? '&category=' . urlencode($selected_category) : '';
            ?>
            <?php if ($posts['current_page'] > 1): ?>
              <li class="page-item">
                <a class="page-link" href="?page=<?= $posts['current_page'] - 1 ?><?= $categoryParam ?>">السابق</a>
              </li>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $posts['last_page']; $i++): ?>
              <li class="page-item <?= $i == $posts['current_page'] ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?><?= $categoryParam ?>"><?= $i ?></a>
              </li>
            <?php endfor; ?>
            
            <?php if ($posts['current_page'] < $posts['last_page']): ?>
              <li class="page-item">
                <a class="page-link" href="?page=<?= $posts['current_page'] + 1 ?><?= $categoryParam ?>">التالي</a>
              </li>
            <?php endif; ?>
          </ul>
        </nav>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<!-- Post Modal -->
<div class="modal fade" id="postModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="postModalTitle"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="postModalBody">
        <div class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">جاري التحميل...</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function viewPost(postId) {
  const modal = new bootstrap.Modal(document.getElementById('postModal'));
  const modalTitle = document.getElementById('postModalTitle');
  const modalBody = document.getElementById('postModalBody');
  
  // Show loading
  modalBody.innerHTML = `
    <div class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">جاري التحميل...</span>
      </div>
    </div>
  `;
  
  modal.show();
  
  // Fetch post details (we'll use the data we already have)
  const posts = <?= json_encode($posts['data']) ?>;
  const post = posts.find(p => p.id == postId);
  
  if (post) {
    modalTitle.textContent = post.title;
    modalBody.innerHTML = `
      ${post.image_path ? `<img src="/${post.image_path}" class="img-fluid rounded mb-3" alt="${post.title}">` : ''}
      ${post.category ? `<span class="badge bg-info mb-3">${post.category}</span>` : ''}
      <div class="post-content" style="white-space: pre-wrap; line-height: 1.8;">
        ${post.content}
      </div>
      <hr class="my-4">
      <div class="d-flex align-items-center">
        ${post.doctor_avatar ? 
          `<img src="/${post.doctor_avatar}" class="rounded-circle me-3" style="width: 48px; height: 48px; object-fit: cover;">` :
          `<div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
            <i class="fas fa-user-md"></i>
          </div>`
        }
        <div>
          <strong>د. ${post.doctor_name}</strong>
          <br>
          <small class="text-muted">${post.specialization_name || ''}</small>
        </div>
      </div>
      <small class="text-muted d-block mt-3">
        <i class="fas fa-calendar me-1"></i>
        ${new Date(post.created_at).toLocaleDateString('ar-IQ')}
      </small>
    `;
  }
}
</script>

