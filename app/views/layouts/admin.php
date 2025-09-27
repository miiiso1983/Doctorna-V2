<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="لوحة إدارة نظام Doctorna">
    
    <!-- CSRF Token -->
    <?= CSRF::meta() ?>
    
    <title><?= $title ?? 'لوحة الإدارة' ?> - <?= APP_NAME ?></title>
    
    <!-- Bootstrap 5 RTL CSS -->
    <link href="<?= $this->asset('vendor/bootstrap/bootstrap.rtl.min.css') ?>" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts - Arabic -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom CSS -->
    <link href="<?= $this->asset('css/admin.css') ?>" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8f9fa;
        }
        
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--bg-color, #667eea) 0%, var(--bg-color-end, #764ba2) 100%);
            color: white;
        }
        
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .btn {
            border-radius: 8px;
            font-weight: 500;
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
                <i class="fas fa-stethoscope fa-2x text-white me-3"></i>
                <div class="sidebar-text">
                    <h5 class="text-white mb-0">Doctorna</h5>
                    <small class="text-white-50">لوحة الإدارة</small>
                </div>
            </div>
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin') === 0 && strlen($_SERVER['REQUEST_URI']) <= 6 ? 'active' : '' ?>" 
                   href="<?= $this->url('/admin') ?>">
                    <i class="fas fa-tachometer-alt me-3"></i>
                    <span class="sidebar-text">الرئيسية</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/users') !== false ? 'active' : '' ?>" 
                   href="<?= $this->url('/admin/users') ?>">
                    <i class="fas fa-users me-3"></i>
                    <span class="sidebar-text">المستخدمين</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/doctors') !== false ? 'active' : '' ?>" 
                   href="<?= $this->url('/admin/doctors') ?>">
                    <i class="fas fa-user-md me-3"></i>
                    <span class="sidebar-text">الأطباء</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/patients') !== false ? 'active' : '' ?>" 
                   href="<?= $this->url('/admin/patients') ?>">
                    <i class="fas fa-user-injured me-3"></i>
                    <span class="sidebar-text">المرضى</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/appointments') !== false ? 'active' : '' ?>" 
                   href="<?= $this->url('/admin/appointments') ?>">
                    <i class="fas fa-calendar-check me-3"></i>
                    <span class="sidebar-text">المواعيد</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/specializations') !== false ? 'active' : '' ?>" 
                   href="<?= $this->url('/admin/specializations') ?>">
                    <i class="fas fa-stethoscope me-3"></i>
                    <span class="sidebar-text">التخصصات</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/analytics') !== false ? 'active' : '' ?>" 
                   href="<?= $this->url('/admin/analytics') ?>">
                    <i class="fas fa-chart-bar me-3"></i>
                    <span class="sidebar-text">التقارير</span>
                </a>
            </li>
            <li class="nav-item mt-auto">
                <a class="nav-link" href="<?= $this->url('/') ?>">
                    <i class="fas fa-home me-3"></i>
                    <span class="sidebar-text">الموقع الرئيسي</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= $this->url('/logout') ?>">
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
                
                <h4 class="mb-0"><?= $title ?? 'لوحة الإدارة' ?></h4>
                
                <div class="navbar-nav ms-auto">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" 
                           role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle fa-lg me-2"></i>
                            <?= $this->escape($this->user()['name']) ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= $this->url('/admin/profile') ?>">
                                <i class="fas fa-user me-2"></i>الملف الشخصي
                            </a></li>
                            <li><a class="dropdown-item" href="<?= $this->url('/admin/settings') ?>">
                                <i class="fas fa-cog me-2"></i>الإعدادات
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= $this->url('/logout') ?>">
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
