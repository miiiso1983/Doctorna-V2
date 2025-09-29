<?php
// Create Health Post View
?>
<div class="row">
  <div class="col-12 mb-4">
    <div class="d-flex justify-content-between align-items-center">
      <h4 class="mb-0"><i class="fas fa-plus-circle me-2"></i>إنشاء منشور صحي جديد</h4>
      <a href="<?= $this->url('/doctor/health-posts') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-right me-2"></i>العودة للقائمة
      </a>
    </div>
  </div>

  <div class="col-lg-8 mx-auto">
    <div class="card border-0 shadow-sm">
      <div class="card-body p-4">
        <form method="POST" action="<?= $this->url('/doctor/health-posts/create') ?>" enctype="multipart/form-data">
          <?= $this->csrfField() ?>>

          <!-- Title -->
          <div class="mb-4">
            <label for="title" class="form-label">
              عنوان المنشور <span class="text-danger">*</span>
            </label>
            <input type="text" 
                   class="form-control <?= isset($_SESSION['errors']['title']) ? 'is-invalid' : '' ?>" 
                   id="title" 
                   name="title" 
                   value="<?= htmlspecialchars($this->old('title')) ?>"
                   placeholder="مثال: نصائح للوقاية من أمراض القلب"
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
                        <?= $this->old('category') == $spec['name'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($spec['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <small class="text-muted">اختر التخصص المناسب لتصنيف المنشور</small>
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
                      placeholder="اكتب محتوى المنشور الصحي هنا... (50 حرف على الأقل)"
                      required><?= htmlspecialchars($this->old('content')) ?></textarea>
            <?php if (isset($_SESSION['errors']['content'])): ?>
              <div class="invalid-feedback">
                <?= $_SESSION['errors']['content'][0] ?>
              </div>
            <?php endif; ?>
            <small class="text-muted">يجب أن يكون المحتوى 50 حرف على الأقل</small>
          </div>

          <!-- Image Upload -->
          <div class="mb-4">
            <label for="image" class="form-label">صورة المنشور (اختياري)</label>
            <input type="file" 
                   class="form-control" 
                   id="image" 
                   name="image" 
                   accept="image/jpeg,image/png,image/jpg,image/webp">
            <small class="text-muted">الصيغ المدعومة: JPG, PNG, WEBP - الحد الأقصى: 5MB</small>
            <div id="image-preview" class="mt-3"></div>
          </div>

          <!-- Info Alert -->
          <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>ملاحظة:</strong> سيتم مراجعة المنشور من قبل الإدارة قبل نشره للمرضى.
          </div>

          <!-- Submit Buttons -->
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-paper-plane me-2"></i>إرسال للمراجعة
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
    // Check file size (5MB max)
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
// Clear old input and errors
unset($_SESSION['old']);
unset($_SESSION['errors']);
?>

