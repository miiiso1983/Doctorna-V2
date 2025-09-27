<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="لوحة الطبيب - نظام Doctorna">
    
    <!-- CSRF Token -->
    <?= CSRF::meta() ?>
    
    <title><?= $title ?? 'لوحة الطبيب' ?> - <?= APP_NAME ?></title>
    
    <!-- Bootstrap 5 RTL CSS -->
    <link href="<?= $this->asset('vendor/bootstrap/bootstrap.rtl.min.css') ?>" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts - Arabic -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom CSS -->
    <link href="<?= $this->asset('css/style.css') ?>" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8f9fa;
        }
        
        .sidebar {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            min-height: 100vh;
            position: fixed;
            top: 0;
            right: 0;
            width: 250px;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .sidebar.collapsed {
            width: 70px;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 1rem 1.5rem;
            border-radius: 0;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .main-content {
            margin-right: 250px;
            transition: all 0.3s ease;
        }
        
        .main-content.expanded {
            margin-right: 70px;
        }
        
        .navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .doctor-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--bg-color, #28a745) 0%, var(--bg-color-end, #20c997) 100%);
            color: white;
        }
        
        .appointment-card {
            border-left: 4px solid var(--status-color, #28a745);
            transition: all 0.3s ease;
        }
        
        .appointment-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .main-content {
                margin-right: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="p-3">
            <div class="d-flex align-items-center mb-4">
                <i class="fas fa-user-md fa-2x text-white me-3"></i>
                <div class="sidebar-text">
                    <h5 class="text-white mb-0">Doctorna</h5>
                    <small class="text-white-50">لوحة الطبيب</small>
                </div>
            </div>
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/doctor') === 0 && strlen($_SERVER['REQUEST_URI']) <= 7 ? 'active' : '' ?>"
                   href="/doctor">
                    <i class="fas fa-tachometer-alt me-3"></i>
                    <span class="sidebar-text">الرئيسية</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/doctor/appointments') !== false ? 'active' : '' ?>"
                   href="/doctor/appointments">
                    <i class="fas fa-calendar-check me-3"></i>
                    <span class="sidebar-text">المواعيد</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/doctor/patients') !== false ? 'active' : '' ?>"
                   href="/doctor/patients">
                    <i class="fas fa-user-injured me-3"></i>
                    <span class="sidebar_text">المرضى</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/doctor/schedule') !== false ? 'active' : '' ?>"
                   href="/doctor/schedule">
                    <i class="fas fa-clock me-3"></i>
                    <span class="sidebar-text">الجدول</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/doctor/profile') !== false ? 'active' : '' ?>"
                   href="/doctor/profile">
                    <i class="fas fa-user-edit me-3"></i>
                    <span class="sidebar-text">الملف الشخصي</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/doctor/reviews') !== false ? 'active' : '' ?>"
                   href="/doctor/reviews">
                    <i class="fas fa-star me-3"></i>
                    <span class="sidebar-text">التقييمات</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/doctor/analytics') !== false ? 'active' : '' ?>"
                   href="/doctor/analytics">
                    <i class="fas fa-chart-line me-3"></i>
                    <span class="sidebar-text">الإحصائيات</span>
                </a>
            </li>
            <li class="nav-item mt-auto">
                <a class="nav-link" href="/">
                    <i class="fas fa-home me-3"></i>
                    <span class="sidebar-text">الموقع الرئيسي</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/logout">
                    <i class="fas fa-sign-out-alt me-3"></i>
                    <span class="sidebar-text">تسجيل الخروج</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container-fluid">
                <button class="btn btn-outline-secondary me-3" id="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <h4 class="mb-0"><?= $title ?? 'لوحة الطبيب' ?></h4>
                
                <div class="navbar-nav ms-auto">
                    <!-- Notifications -->
                    <div class="nav-item dropdown me-3">
                        <a class="nav-link position-relative" href="#" id="notificationsDropdown" 
                           role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-bell fa-lg"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                3
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">الإشعارات</h6></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-calendar me-2"></i>موعد جديد من أحمد محمد
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-star me-2"></i>تقييم جديد من فاطمة علي
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-clock me-2"></i>تذكير: موعد خلال ساعة
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-center" href="#">عرض جميع الإشعارات</a></li>
                        </ul>
                    </div>
                    
                    <!-- User Profile -->
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" 
                           role="button" data-bs-toggle="dropdown">
                            <?php if (!empty($this->user()['avatar'])): ?>
                                <img src="<?= $this->asset('uploads/profiles/' . $this->user()['avatar']) ?>" 
                                     alt="الصورة الشخصية" class="doctor-avatar me-2">
                            <?php else: ?>
                                <i class="fas fa-user-circle fa-lg me-2"></i>
                            <?php endif; ?>
                            د. <?= $this->escape($this->user()['name']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/doctor/profile">
                                <i class="fas fa-user me-2"></i>الملف الشخصي
                            </a></li>
                            <li><a class="dropdown-item" href="/doctor/settings">
                                <i class="fas fa-cog me-2"></i>الإعدادات
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout">
                                <i class="fas fa-sign-out-alt me-2"></i>تسجيل الخروج
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Flash Messages -->
        <?php if ($this->hasFlash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?= $this->escape($this->flash('success')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($this->hasFlash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= $this->escape($this->flash('error')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($this->hasFlash('warning')): ?>
            <div class="alert alert-warning alert-dismissible fade show m-3" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?= $this->escape($this->flash('warning')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Page Content -->
        <div class="container-fluid p-4">
            <?= $content ?>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS -->
    <script src="<?= $this->asset('vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?= $this->asset('js/app.js') ?>"></script>
    
    <script>
        // Sidebar toggle
        document.getElementById('sidebar-toggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });
        
        // Auto-hide alerts
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Confirm dialogs
        document.querySelectorAll('[data-confirm]').forEach(function(element) {
            element.addEventListener('click', function(e) {
                if (!confirm(this.getAttribute('data-confirm'))) {
                    e.preventDefault();
                }
            });
        });
        
        // AJAX appointment actions
        function updateAppointmentStatus(appointmentId, action, reason = '') {
            const formData = new FormData();
            formData.append('appointment_id', appointmentId);
            formData.append('reason', reason);
            formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);
            
            fetch(`/doctor/${action}-appointment`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Doctorna.utils.showToast(data.message, 'success');
                    location.reload();
                } else {
                    Doctorna.utils.showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Doctorna.utils.showToast('حدث خطأ غير متوقع', 'error');
            });
        }
    </script>
    
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
