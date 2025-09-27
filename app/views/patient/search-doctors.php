<!-- Search Filters -->
<div class="search-filters">
    <form method="GET" action="<?= $this->url('/patient/search-doctors') ?>">
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-3">
                <label class="form-label">البحث</label>
                <input type="text" class="form-control" name="search"
                       value="<?= $this->escape($filters['search'] ?? '') ?>"
                       placeholder="اسم الطبيب أو التخصص">
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <label class="form-label">التخصص</label>
                <select class="form-select" name="specialization">
                    <option value="">جميع التخصصات</option>
                    <?php foreach ($specializations as $spec): ?>
                        <option value="<?= $spec['id'] ?>"
                                <?= ($filters['specialization'] ?? '') == $spec['id'] ? 'selected' : '' ?>>
                            <?= $this->escape($spec['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-lg-2 col-md-6 mb-3">
                <label class="form-label">المدينة</label>
                <select class="form-select" name="city">
                    <option value="">جميع المدن</option>
                    <?php foreach ($cities as $city): ?>
                        <option value="<?= $this->escape($city) ?>"
                                <?= ($filters['city'] ?? '') == $city ? 'selected' : '' ?>>
                            <?= $this->escape($city) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-lg-2 col-md-6 mb-3">
                <label class="form-label">الجنس</label>
                <select class="form-select" name="gender">
                    <option value="">الكل</option>
                    <option value="male" <?= ($filters['gender'] ?? '') == 'male' ? 'selected' : '' ?>>ذكر</option>
                    <option value="female" <?= ($filters['gender'] ?? '') == 'female' ? 'selected' : '' ?>>أنثى</option>
                </select>
            </div>
            
            <div class="col-lg-2 col-md-6 mb-3">
                <label class="form-label">التقييم</label>
                <select class="form-select" name="rating">
                    <option value="">جميع التقييمات</option>
                    <option value="4" <?= $filters['rating'] == '4' ? 'selected' : '' ?>>4+ نجوم</option>
                    <option value="4.5" <?= $filters['rating'] == '4.5' ? 'selected' : '' ?>>4.5+ نجوم</option>
                    <option value="5" <?= $filters['rating'] == '5' ? 'selected' : '' ?>>5 نجوم</option>
                </select>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search me-2"></i>بحث
                </button>
                <a href="<?= $this->url('/patient/search-doctors') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-2"></i>مسح الفلاتر
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Search Results -->
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">
                نتائج البحث 
                <span class="badge bg-primary"><?= $doctors['total'] ?></span>
            </h5>
            
            <div class="d-flex align-items-center">
                <span class="text-muted me-2">عرض:</span>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-secondary active" id="grid-view">
                        <i class="fas fa-th"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="list-view">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($doctors['data'])): ?>
    <!-- Doctors Grid -->
    <div class="row" id="doctors-container">
        <?php foreach ($doctors['data'] as $doctor): ?>
            <div class="col-lg-4 col-md-6 mb-4 doctor-item">
                <div class="card doctor-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start mb-3">
                            <div class="flex-shrink-0 me-3">
                                <?php if (!empty($doctor['avatar'])): ?>
                                    <img src="<?= $this->asset('uploads/profiles/' . $doctor['avatar']) ?>" 
                                         alt="صورة الطبيب" class="rounded-circle" width="60" height="60">
                                <?php else: ?>
                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 60px; height: 60px;">
                                        <i class="fas fa-user-md text-white fa-lg"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-1">د. <?= $this->escape($doctor['name']) ?></h5>
                                <p class="text-muted mb-1">
                                    <i class="fas fa-stethoscope me-1"></i>
                                    <?= $this->escape($doctor['specialization_name']) ?>
                                </p>
                                <p class="text-muted mb-1">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?= $this->escape($doctor['city']) ?>
                                </p>
                                <div class="rating-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?= $i <= $doctor['rating'] ? '' : '-o' ?>"></i>
                                    <?php endfor; ?>
                                    <span class="text-muted ms-1">
                                        (<?= $doctor['total_reviews'] ?> تقييم)
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="row text-center">
                                <div class="col-4">
                                    <small class="text-muted d-block">الخبرة</small>
                                    <strong><?= $doctor['experience_years'] ?> سنة</strong>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">الرسوم</small>
                                    <strong class="text-success"><?= $this->formatCurrency($doctor['consultation_fee']) ?></strong>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">المواعيد</small>
                                    <strong><?= $doctor['total_appointments'] ?></strong>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($doctor['biography'])): ?>
                            <p class="text-muted small mb-3">
                                <?= $this->escape(substr($doctor['biography'], 0, 100)) ?>
                                <?= strlen($doctor['biography']) > 100 ? '...' : '' ?>
                            </p>
                        <?php endif; ?>
                        
                        <div class="d-flex gap-2">
                            <a href="<?= $this->url('/patient/doctor/' . $doctor['id']) ?>" 
                               class="btn btn-primary flex-fill">
                                <i class="fas fa-eye me-2"></i>عرض الملف
                            </a>
                            <button class="btn btn-success" 
                                    onclick="quickBook(<?= $doctor['id'] ?>)">
                                <i class="fas fa-calendar-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($doctors['last_page'] > 1): ?>
        <nav aria-label="صفحات النتائج">
            <ul class="pagination justify-content-center">
                <?php if ($doctors['current_page'] > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= $this->buildUrl('/patient/search-doctors', array_merge($filters, ['page' => $doctors['current_page'] - 1])) ?>">
                            السابق
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = max(1, $doctors['current_page'] - 2); $i <= min($doctors['last_page'], $doctors['current_page'] + 2); $i++): ?>
                    <li class="page-item <?= $i == $doctors['current_page'] ? 'active' : '' ?>">
                        <a class="page-link" href="<?= $this->buildUrl('/patient/search-doctors', array_merge($filters, ['page' => $i])) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($doctors['current_page'] < $doctors['last_page']): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= $this->buildUrl('/patient/search-doctors', array_merge($filters, ['page' => $doctors['current_page'] + 1])) ?>">
                            التالي
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        
        <div class="text-center text-muted">
            عرض <?= $doctors['from'] ?> إلى <?= $doctors['to'] ?> من أصل <?= $doctors['total'] ?> طبيب
        </div>
    <?php endif; ?>
    
<?php else: ?>
    <!-- No Results -->
    <div class="text-center py-5">
        <i class="fas fa-search fa-4x text-muted mb-4"></i>
        <h4 class="text-muted mb-3">لم يتم العثور على أطباء</h4>
        <p class="text-muted mb-4">جرب تغيير معايير البحث أو إزالة بعض الفلاتر</p>
        <a href="<?= $this->url('/patient/search-doctors') ?>" class="btn btn-primary">
            <i class="fas fa-refresh me-2"></i>مسح الفلاتر
        </a>
    </div>
<?php endif; ?>

<!-- Quick Book Modal -->
<div class="modal fade" id="quickBookModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">حجز سريع</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="quick-book-content">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">جاري التحميل...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// View toggle
document.getElementById('grid-view').addEventListener('click', function() {
    document.getElementById('doctors-container').className = 'row';
    this.classList.add('active');
    document.getElementById('list-view').classList.remove('active');
});

document.getElementById('list-view').addEventListener('click', function() {
    document.getElementById('doctors-container').className = 'row';
    // Add list view classes
    document.querySelectorAll('.doctor-item').forEach(item => {
        item.className = 'col-12 mb-3 doctor-item';
    });
    this.classList.add('active');
    document.getElementById('grid-view').classList.remove('active');
});

// Quick book function
function quickBook(doctorId) {
    const modal = new bootstrap.Modal(document.getElementById('quickBookModal'));
    modal.show();
    
    // Load doctor's available slots
    fetch(`/patient/doctor/${doctorId}/quick-slots`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('quick-book-content').innerHTML = data.html;
            } else {
                document.getElementById('quick-book-content').innerHTML = 
                    '<div class="alert alert-warning">لا توجد مواعيد متاحة حالياً</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('quick-book-content').innerHTML = 
                '<div class="alert alert-danger">حدث خطأ في تحميل المواعيد</div>';
        });
}

// Search on type
let searchTimeout;
document.querySelector('input[name="search"]').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        this.form.submit();
    }, 500);
});

// Auto-submit on filter change
document.querySelectorAll('select').forEach(select => {
    select.addEventListener('change', function() {
        this.form.submit();
    });
});

// Smooth scroll to results
if (window.location.search) {
    setTimeout(() => {
        document.getElementById('doctors-container').scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }, 100);
}
</script>

<style>
.doctor-card {
    transition: all 0.3s ease;
}

.doctor-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.15) !important;
}

.rating-stars {
    color: #ffc107;
}

.search-filters {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    margin-bottom: 2rem;
}

.btn-group .btn.active {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
}

@media (max-width: 768px) {
    .search-filters .row > div {
        margin-bottom: 1rem;
    }
    
    .doctor-card .d-flex.gap-2 {
        flex-direction: column;
    }
    
    .doctor-card .d-flex.gap-2 .btn {
        margin-bottom: 0.5rem;
    }
}
</style>
