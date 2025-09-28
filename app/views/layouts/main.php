<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $description ?? 'نظام متطور لحجز المواعيد الطبية مع الأطباء المتخصصين' ?>">
    <meta name="keywords" content="طبيب, موعد, حجز, استشارة, صحة">
    <meta name="author" content="Doctorna Team">
    
    <!-- CSRF Token -->
    <?= CSRF::meta() ?>
    
    <title><?= $title ?? 'Doctorna' ?> - <?= APP_NAME ?></title>
    
    <!-- Bootstrap 5 RTL CSS -->
    <link href="<?= $this->asset('vendor/bootstrap/bootstrap.rtl.min.css') ?>" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts - Arabic -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?= $this->asset('css/style.css') ?>" rel="stylesheet">
    
    <!-- Additional CSS -->
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link href="<?= $this->asset($css) ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="<?= $this->url('/') ?>">
                <i class="fas fa-stethoscope me-2"></i>
                Doctorna
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $this->url('/') ?>">الرئيسية</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $this->url('/search-doctors') ?>">البحث عن طبيب</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $this->url('/services') ?>">الخدمات</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $this->url('/about') ?>">عن النظام</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $this->url('/contact') ?>">اتصل بنا</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if ($this->auth()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i>
                                <?= $this->escape($this->user()['name']) ?>
                            </a>
                            <ul class="dropdown-menu">
                                <?php if ($this->hasRole(ROLE_SUPER_ADMIN)): ?>
                                    <li><a class="dropdown-item" href="<?= $this->url('/admin') ?>">
                                        <i class="fas fa-tachometer-alt me-2"></i>لوحة الإدارة
                                    </a></li>
                                <?php elseif ($this->hasRole(ROLE_DOCTOR)): ?>
                                    <li><a class="dropdown-item" href="<?= $this->url('/doctor') ?>">
                                        <i class="fas fa-user-md me-2"></i>لوحة الطبيب
                                    </a></li>
                                <?php elseif ($this->hasRole(ROLE_PATIENT)): ?>
                                    <li><a class="dropdown-item" href="<?= $this->url('/patient') ?>">
                                        <i class="fas fa-user me-2"></i>لوحة المريض
                                    </a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= $this->url('/logout') ?>">
                                    <i class="fas fa-sign-out-alt me-2"></i>تسجيل الخروج
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $this->url('/login') ?>">تسجيل الدخول</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary btn-sm" href="<?= $this->url('/register') ?>">إنشاء حساب</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Flash Messages -->
    <?php if ($this->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show m-0" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= $this->escape($this->flash('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($this->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show m-0" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= $this->escape($this->flash('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($this->hasFlash('warning')): ?>
        <div class="alert alert-warning alert-dismissible fade show m-0" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= $this->escape($this->flash('warning')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($this->hasFlash('info')): ?>
        <div class="alert alert-info alert-dismissible fade show m-0" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <?= $this->escape($this->flash('info')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main>
        <?= $content ?>
    </main>
    
    <!-- Footer -->
    <footer class="bg-dark text-light py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-stethoscope me-2"></i>
                        Doctorna
                    </h5>
                    <p class="text-muted">
                        نظام متطور لحجز المواعيد الطبية يربط المرضى بأفضل الأطباء المتخصصين
                        لتوفير رعاية صحية عالية الجودة.
                    </p>
                    <div class="social-links">
                        <a href="#" class="text-light me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">روابط سريعة</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?= $this->url('/') ?>" class="text-muted text-decoration-none">الرئيسية</a></li>
                        <li><a href="<?= $this->url('/services') ?>" class="text-muted text-decoration-none">الخدمات</a></li>
                        <li><a href="<?= $this->url('/about') ?>" class="text-muted text-decoration-none">عن النظام</a></li>
                        <li><a href="<?= $this->url('/contact') ?>" class="text-muted text-decoration-none">اتصل بنا</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">الحساب</h6>
                    <ul class="list-unstyled">
                        <?php if (!$this->auth()): ?>
                            <li><a href="<?= $this->url('/login') ?>" class="text-muted text-decoration-none">تسجيل الدخول</a></li>
                            <li><a href="<?= $this->url('/register') ?>" class="text-muted text-decoration-none">إنشاء حساب</a></li>
                        <?php endif; ?>
                        <li><a href="<?= $this->url('/privacy') ?>" class="text-muted text-decoration-none">سياسة الخصوصية</a></li>
                        <li><a href="<?= $this->url('/terms') ?>" class="text-muted text-decoration-none">شروط الاستخدام</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <h6 class="fw-bold mb-3">تواصل معنا</h6>
                    <div class="contact-info">
                        <p class="text-muted mb-2">
                            <i class="fas fa-envelope me-2"></i>
                            info@doctorna.com
                        </p>
                        <p class="text-muted mb-2">
                            <i class="fas fa-phone me-2"></i>
                            +964 770 000 0000
                        </p>
                        <p class="text-muted mb-2">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            بغداد، العراق
                        </p>
                    </div>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-muted mb-0">
                        &copy; <?= date('Y') ?> Doctorna. جميع الحقوق محفوظة.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">
                        تم التطوير بواسطة <a href="#" class="text-light text-decoration-none">Doctorna Team</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap 5 JS -->
    <script src="<?= $this->asset('vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- Custom JS -->
    <script src="<?= $this->asset('js/app.js') ?>"></script>
    <script src="<?= $this->asset('js/dynamic-features.js') ?>"></script>

    <!-- Additional JS -->
    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?= $this->asset($js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Inline Scripts -->
    <?php if (isset($inline_scripts)): ?>
        <script>
            <?= $inline_scripts ?>
        </script>
    <?php endif; ?>
</body>
</html>
