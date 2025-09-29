<!-- Hero Section -->
<section class="hero-section bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center min-vh-75">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">
                    احجز موعدك مع أفضل الأطباء
                </h1>
                <p class="lead mb-4">
                    نظام متطور لحجز المواعيد الطبية يربطك بأفضل الأطباء المتخصصين
                    في جميع أنحاء العراق
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="<?= $this->url('/register') ?>" class="btn btn-light btn-lg px-4">
                        <i class="fas fa-user-plus me-2"></i>
                        إنشاء حساب جديد
                    </a>
                    <a href="<?= $this->url('/search-doctors') ?>" class="btn btn-outline-light btn-lg px-4">
                        <i class="fas fa-search me-2"></i>
                        البحث عن طبيب
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <img src="<?= $this->asset('images/hero-doctor.svg') ?>" alt="طبيب" class="img-fluid" style="max-height: 400px;">
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-primary mb-3">
                            <i class="fas fa-user-md fa-3x"></i>
                        </div>
                        <h3 class="fw-bold text-primary"><?= number_format($statistics['total_doctors']) ?></h3>
                        <p class="text-muted mb-0">طبيب متخصص</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-success mb-3">
                            <i class="fas fa-users fa-3x"></i>
                        </div>
                        <h3 class="fw-bold text-success"><?= number_format($statistics['total_patients']) ?></h3>
                        <p class="text-muted mb-0">مريض مسجل</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-info mb-3">
                            <i class="fas fa-calendar-check fa-3x"></i>
                        </div>
                        <h3 class="fw-bold text-info"><?= number_format($statistics['total_appointments']) ?></h3>
                        <p class="text-muted mb-0">موعد مكتمل</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-warning mb-3">
                            <i class="fas fa-star fa-3x"></i>
                        </div>
                        <h3 class="fw-bold text-warning"><?= number_format($statistics['average_rating'], 1) ?></h3>
                        <p class="text-muted mb-0">متوسط التقييم</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Specializations Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">التخصصات الطبية</h2>
            <p class="text-muted">اختر التخصص المناسب لحالتك الصحية</p>
        </div>
        
        <div class="row">
            <?php foreach (array_slice($specializations, 0, 8) as $specialization): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100 specialization-card">
                        <div class="card-body text-center">
                            <div class="mb-3" style="color: <?= $specialization['color'] ?>">
                                <i class="<?= $specialization['icon'] ?> fa-3x"></i>
                            </div>
                            <h5 class="card-title fw-bold"><?= $this->escape($specialization['name']) ?></h5>
                            <p class="text-muted small mb-3"><?= $this->escape($specialization['description']) ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-light text-dark">
                                    <?= $specialization['doctor_count'] ?> طبيب
                                </span>
                                <a href="<?= $this->url('/services?specialization=' . $specialization['id']) ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    عرض الأطباء
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="<?= $this->url('/services') ?>" class="btn btn-primary btn-lg">
                عرض جميع التخصصات
                <i class="fas fa-arrow-left ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Featured Doctors Section -->
<?php if (!empty($featured_doctors)): ?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">أطباء مميزون</h2>
            <p class="text-muted">تعرف على أفضل الأطباء المتخصصين</p>
        </div>
        
        <div class="row">
            <?php foreach (array_slice($featured_doctors, 0, 4) as $doctor): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100 doctor-card">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <?php if ($doctor['avatar']): ?>
                                    <img src="<?= $this->asset($doctor['avatar']) ?>" 
                                         alt="<?= $this->escape($doctor['name']) ?>"
                                         class="rounded-circle" width="80" height="80">
                                <?php else: ?>
                                    <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" 
                                         style="width: 80px; height: 80px;">
                                        <i class="fas fa-user-md fa-2x text-white"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <h5 class="card-title fw-bold"><?= $this->escape($doctor['name']) ?></h5>
                            <p class="text-muted small"><?= $this->escape($doctor['specialization_name']) ?></p>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-center align-items-center">
                                    <div class="text-warning me-2">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?= $i <= $doctor['rating'] ? '' : '-o' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="small text-muted">(<?= $doctor['total_reviews'] ?>)</span>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-primary fw-bold"><?= $this->formatCurrency($doctor['consultation_fee']) ?></span>
                                <a href="<?= $this->url('/patient/book?doctor=' . $doctor['id']) ?>" 
                                   class="btn btn-sm btn-primary">
                                    احجز موعد
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">لماذا تختار طبيبك؟</h2>
            <p class="text-muted">نوفر لك أفضل تجربة لحجز المواعيد الطبية</p>
        </div>
        
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="text-center">
                    <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-clock fa-2x text-white"></i>
                    </div>
                    <h4 class="fw-bold">حجز سريع وسهل</h4>
                    <p class="text-muted">احجز موعدك في دقائق معدودة مع أفضل الأطباء</p>
                </div>
            </div>
            
            <div class="col-lg-4 mb-4">
                <div class="text-center">
                    <div class="bg-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-shield-alt fa-2x text-white"></i>
                    </div>
                    <h4 class="fw-bold">آمن وموثوق</h4>
                    <p class="text-muted">نحافظ على خصوصية بياناتك الطبية بأعلى معايير الأمان</p>
                </div>
            </div>
            
            <div class="col-lg-4 mb-4">
                <div class="text-center">
                    <div class="bg-info rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-headset fa-2x text-white"></i>
                    </div>
                    <h4 class="fw-bold">دعم على مدار الساعة</h4>
                    <p class="text-muted">فريق الدعم متاح لمساعدتك في أي وقت</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-primary text-white">
    <div class="container text-center">
        <h2 class="fw-bold mb-4">ابدأ رحلتك الصحية اليوم</h2>
        <p class="lead mb-4">انضم إلى آلاف المرضى الذين يثقون في خدماتنا</p>
        <a href="<?= $this->url('/register') ?>" class="btn btn-light btn-lg px-5">
            <i class="fas fa-user-plus me-2"></i>
            إنشاء حساب مجاني
        </a>
    </div>
</section>

<style>
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.min-vh-75 {
    min-height: 75vh;
}

.specialization-card:hover,
.doctor-card:hover {
    transform: translateY(-5px);
    transition: all 0.3s ease;
}

.card {
    transition: all 0.3s ease;
}
</style>
