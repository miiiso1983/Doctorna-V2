<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $description ?? 'تسجيل الدخول إلى نظام طبيبك' ?>">
    
    <!-- CSRF Token -->
    <?= CSRF::meta() ?>
    
    <title><?= $title ?? 'تسجيل الدخول' ?> - <?= APP_NAME ?></title>
    
    <!-- Bootstrap 5 RTL CSS -->
    <link href="<?= $this->asset('vendor/bootstrap/bootstrap.rtl.min.css') ?>" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts - Arabic -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?= $this->asset('css/auth.css') ?>" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .auth-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            margin: 20px;
        }
        
        .auth-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .auth-body {
            padding: 2rem;
        }
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .text-primary {
            color: #667eea !important;
        }
        
        .auth-links {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .auth-links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .auth-links a:hover {
            text-decoration: underline;
        }
        
        .brand-logo {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .invalid-feedback {
            display: block;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Header -->
        <div class="auth-header">
            <div class="brand-logo">
                <i class="fas fa-stethoscope"></i>
            </div>
            <h2 class="mb-0">طبيبك</h2>
            <p class="mb-0 opacity-75">نظام حجز المواعيد الطبية</p>
        </div>
        
        <!-- Body -->
        <div class="auth-body">
            <!-- Flash Messages -->
            <?php if ($this->hasFlash('success')): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= $this->escape($this->flash('success')) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($this->hasFlash('error')): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= $this->escape($this->flash('error')) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($this->hasFlash('warning')): ?>
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= $this->escape($this->flash('warning')) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($this->hasFlash('info')): ?>
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    <?= $this->escape($this->flash('info')) ?>
                </div>
            <?php endif; ?>
            
            <!-- Content -->
            <?= $content ?>
            
            <!-- Back to Home -->
            <div class="auth-links">
                <a href="<?= $this->url('/') ?>" class="d-inline-flex align-items-center">
                    <i class="fas fa-arrow-right me-2"></i>
                    العودة إلى الصفحة الرئيسية
                </a>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS -->
    <script src="<?= $this->asset('vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Form validation
        $(document).ready(function() {
            // Add Bootstrap validation classes
            $('.form-control').on('blur', function() {
                if ($(this).val().trim() === '') {
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                }
            });
            
            // Email validation
            $('input[type="email"]').on('blur', function() {
                const email = $(this).val();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (email && !emailRegex.test(email)) {
                    $(this).addClass('is-invalid');
                } else if (email) {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                }
            });
            
            // Password confirmation
            $('input[name="password_confirmation"]').on('blur', function() {
                const password = $('input[name="password"]').val();
                const confirmation = $(this).val();
                
                if (confirmation && password !== confirmation) {
                    $(this).addClass('is-invalid');
                } else if (confirmation) {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                }
            });
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
    </script>
    
    <!-- Additional JS -->
    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?= $this->asset($js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
