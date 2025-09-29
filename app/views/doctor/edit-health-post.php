<?php
// Edit Health Post View
?>
<div class="row">
  <div class="col-12 mb-4">
    <div class="d-flex justify-content-between align-items-center">
      <h4 class="mb-0"><i class="fas fa-edit me-2"></i>تعديل المنشور الصحي</h4>
      <a href="<?= $this->url('/doctor/health-posts') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-right me-2"></i>العودة للقائمة
      </a>
    </div>
  </div>

  <div class="col-lg-8 mx-auto">
    <div class="card border-0 shadow-sm">
      <div class="card-body p-4">
        <!-- Status Badge -->
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
        <div class="mb-3">
          <span class="badge bg-<?= $statusClass[$post['status']] ?> fs-6">
            الحالة: <?= $statusText[$post['status']] ?>
          </span>
        </div>

        <form method="POST" action="<?= $this->url('/doctor/health-posts/' . $post['id'] . '/edit') ?>" enctype="multipart/form-data">
          <?= $this->csrfField() ?>

          <!-- Title -->
          <div class="mb-4">
            <label for="title" class="form-label">
              عنوان المنشور <span class="text-danger">*</span>
            </label>
            <input type="text" 
                   class="form-control <?= isset($_SESSION['errors']['title']) ? 'is-invalid' : '' ?>" 
                   id="title" 
                   name="title" 
                   value="<?= htmlspecialchars($this->old('title', $post['title'])) ?>"
                   required>
            <?php if (isset($_SESSION['errors']['title'])): ?>
              <div class="invalid-feedback">
                <?= $_SESSION['errors']['title'][0] ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Category -->
          <div class="mb-4">
            <label for="category" class="form-label">الفئة / التخصص</label>
            <select class="form-select" id="category" name="category">
              <option value="">اختر الفئة (اختياري)</option>
              <?php foreach ($specializations as $spec): ?>
                <option value="<?= htmlspecialchars($spec['name']) ?>"
                        <?= $this->old('category', $post['category']) == $spec['name'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($spec['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Content -->
          <div class="mb-4">
            <label for="content" class="form-label">
              محتوى المنشور <span class="text-danger">*</span>
            </label>
            <textarea class="form-control <?= isset($_SESSION['errors']['content']) ? 'is-invalid' : '' ?>" 
                      id="content" 
                      name="content" 
                      rows="12"
                      required><?= htmlspecialchars($this->old('content', $post['content'])) ?></textarea>
            <?php if (isset($_SESSION['errors']['content'])): ?>
              <div class="invalid-feedback">
                <?= $_SESSION['errors']['content'][0] ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Current Image -->
          <?php if ($post['image_path']): ?>
            <div class="mb-4">
              <label class="form-label">الصورة الحالية</label>
              <div>
                <img src="<?= $this->url('/' . $post['image_path']) ?>" 
                     alt="<?= htmlspecialchars($post['title']) ?>" 
                     class="img-thumbnail" 
                     style="max-width: 300px; max-height: 200px;">
              </div>
            </div>
          <?php endif; ?>

          <!-- Image Upload -->
          <div class="mb-4">
            <label for="image" class="form-label">
              <?= $post['image_path'] ? 'تغيير الصورة (اختياري)' : 'إضافة صورة (اختياري)' ?>
            </label>
            <input type="file" 
                   class="form-control" 
                   id="image" 
                   name="image" 
                   accept="image/jpeg,image/png,image/jpg,image/webp">
            <small class="text-muted">الصيغ المدعومة: JPG, PNG, WEBP - الحد الأقصى: 5MB</small>
            <div id="image-preview" class="mt-3"></div>
          </div>

          <!-- Info Alert -->
          <?php if ($post['status'] == 'approved'): ?>
            <div class="alert alert-warning">
              <i class="fas fa-exclamation-triangle me-2"></i>
              <strong>تنبيه:</strong> تعديل منشور معتمد سيتطلب مراجعة جديدة من الإدارة.
            </div>
          <?php elseif ($post['status'] == 'rejected'): ?>
            <div class="alert alert-danger">
              <i class="fas fa-times-circle me-2"></i>
              <strong>مرفوض:</strong> يمكنك تعديل المنشور وإعادة إرساله للمراجعة.
            </div>
          <?php endif; ?>

          <!-- Submit Buttons -->
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save me-2"></i>حفظ التعديلات
            </button>
            <a href="<?= $this->url('/doctor/health-posts') ?>" class="btn btn-outline-secondary">
              إلغاء
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
// Image preview
document.getElementById('image').addEventListener('change', function(e) {
  const file = e.target.files[0];
  const preview = document.getElementById('image-preview');
  
  if (file) {
    if (file.size > 5 * 1024 * 1024) {
      Doctorna.showToast('حجم الصورة يجب أن يكون أقل من 5MB', 'error');
      e.target.value = '';
      preview.innerHTML = '';
      return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
      preview.innerHTML = `
        <div class="position-relative d-inline-block">
          <img src="${e.target.result}" class="img-thumbnail" style="max-width: 300px; max-height: 200px;">
          <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2" 
                  onclick="clearImagePreview()">
            <i class="fas fa-times"></i>
          </button>
        </div>
      `;
    };
    reader.readAsDataURL(file);
  } else {
    preview.innerHTML = '';
  }
});

function clearImagePreview() {
  document.getElementById('image').value = '';
  document.getElementById('image-preview').innerHTML = '';
}
</script>

<?php
unset($_SESSION['old']);
unset($_SESSION['errors']);
?>

