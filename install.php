<?php
/**
 * Doctorna Installation Script
 * Automated setup for the Doctorna appointment booking system
 */

// Prevent running if already installed
if (file_exists('.env') && file_exists('config/installed.lock')) {
    die('System is already installed. Delete .env and config/installed.lock to reinstall.');
}

// Check PHP version
if (version_compare(PHP_VERSION, '8.0.0', '<')) {
    die('PHP 8.0 or higher is required. Current version: ' . PHP_VERSION);
}

// Check required extensions
$required_extensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'curl', 'gd'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}

if (!empty($missing_extensions)) {
    die('Missing required PHP extensions: ' . implode(', ', $missing_extensions));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate input
        $required_fields = ['db_host', 'db_name', 'db_user', 'app_name', 'admin_name', 'admin_email', 'admin_password'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Field {$field} is required");
            }
        }
        
        // Test database connection
        $dsn = "mysql:host={$_POST['db_host']};dbname={$_POST['db_name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $_POST['db_user'], $_POST['db_pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        
        // Create .env file
        $env_content = "# Doctorna Environment Configuration\n\n";
        $env_content .= "# Application\n";
        $env_content .= "APP_NAME=\"{$_POST['app_name']}\"\n";
        $env_content .= "APP_URL=\"{$_POST['app_url']}\"\n";
        $env_content .= "APP_ENV=production\n";
        $env_content .= "APP_DEBUG=false\n\n";
        
        $env_content .= "# Database\n";
        $env_content .= "DB_HOST={$_POST['db_host']}\n";
        $env_content .= "DB_NAME={$_POST['db_name']}\n";
        $env_content .= "DB_USER={$_POST['db_user']}\n";
        $env_content .= "DB_PASS={$_POST['db_pass']}\n\n";
        
        $env_content .= "# Mail Configuration\n";
        $env_content .= "MAIL_HOST={$_POST['mail_host']}\n";
        $env_content .= "MAIL_PORT={$_POST['mail_port']}\n";
        $env_content .= "MAIL_USERNAME={$_POST['mail_username']}\n";
        $env_content .= "MAIL_PASSWORD={$_POST['mail_password']}\n";
        $env_content .= "MAIL_FROM_EMAIL={$_POST['mail_from_email']}\n";
        $env_content .= "MAIL_FROM_NAME=\"{$_POST['app_name']}\"\n\n";
        
        $env_content .= "# Google Maps API\n";
        $env_content .= "GOOGLE_MAPS_API_KEY={$_POST['google_maps_key']}\n\n";
        
        $env_content .= "# Security\n";
        $env_content .= "APP_KEY=" . bin2hex(random_bytes(32)) . "\n";
        $env_content .= "JWT_SECRET=" . bin2hex(random_bytes(32)) . "\n";
        
        file_put_contents('.env', $env_content);
        
        // Import database schema
        $schema = file_get_contents('database/schema.sql');
        $pdo->exec($schema);
        
        // Create admin user
        $admin_password = password_hash($_POST['admin_password'], PASSWORD_DEFAULT);
        $admin_sql = "INSERT INTO users (name, email, password, role, email_verified_at, created_at) 
                      VALUES (?, ?, ?, 'super_admin', NOW(), NOW())";
        $pdo->prepare($admin_sql)->execute([
            $_POST['admin_name'],
            $_POST['admin_email'],
            $admin_password
        ]);
        
        // Create directories
        $directories = [
            'public/uploads',
            'public/uploads/profiles',
            'public/uploads/documents',
            'logs',
            'cache'
        ];
        
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
        
        // Create installation lock file
        if (!is_dir('config')) {
            mkdir('config', 0755, true);
        }
        file_put_contents('config/installed.lock', date('Y-m-d H:i:s'));
        
        $success = true;
        $message = "Installation completed successfully!";
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تثبيت نظام Doctorna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .install-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            text-align: center;
            padding: 2rem;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 600;
        }
        .requirements {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 2rem;
        }
        .requirement {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        .requirement i {
            margin-left: 0.5rem;
        }
        .text-success { color: #28a745 !important; }
        .text-danger { color: #dc3545 !important; }
    </style>
</head>
<body>
    <div class="container install-container">
        <?php if (isset($success) && $success): ?>
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-check-circle"></i> تم التثبيت بنجاح!</h2>
                </div>
                <div class="card-body text-center">
                    <div class="alert alert-success">
                        <h4>مبروك! تم تثبيت نظام Doctorna بنجاح</h4>
                        <p>يمكنك الآن الوصول إلى النظام باستخدام بيانات المدير التي أدخلتها.</p>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-4 mb-3">
                            <a href="/" class="btn btn-primary w-100">
                                <i class="fas fa-home"></i> الصفحة الرئيسية
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="/admin" class="btn btn-success w-100">
                                <i class="fas fa-tachometer-alt"></i> لوحة الإدارة
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="/login" class="btn btn-info w-100">
                                <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
                            </a>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning mt-4">
                        <strong>مهم:</strong> لأسباب أمنية، يرجى حذف ملف install.php من الخادم.
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-stethoscope"></i> تثبيت نظام Doctorna</h2>
                    <p class="mb-0">نظام حجز المواعيد الطبية الشامل</p>
                </div>
                <div class="card-body">
                    <!-- System Requirements -->
                    <div class="requirements">
                        <h5><i class="fas fa-list-check"></i> متطلبات النظام</h5>
                        <div class="requirement">
                            <i class="fas fa-check text-success"></i>
                            PHP <?= PHP_VERSION ?> (مطلوب 8.0+)
                        </div>
                        <?php foreach ($required_extensions as $ext): ?>
                            <div class="requirement">
                                <i class="fas fa-<?= extension_loaded($ext) ? 'check text-success' : 'times text-danger' ?>"></i>
                                PHP Extension: <?= $ext ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <!-- Application Settings -->
                        <h5><i class="fas fa-cog"></i> إعدادات التطبيق</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">اسم التطبيق</label>
                                <input type="text" class="form-control" name="app_name" value="Doctorna" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">رابط التطبيق</label>
                                <input type="url" class="form-control" name="app_url" value="<?= 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] ?>" required>
                            </div>
                        </div>
                        
                        <!-- Database Settings -->
                        <h5><i class="fas fa-database"></i> إعدادات قاعدة البيانات</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">خادم قاعدة البيانات</label>
                                <input type="text" class="form-control" name="db_host" value="localhost" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">اسم قاعدة البيانات</label>
                                <input type="text" class="form-control" name="db_name" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">اسم المستخدم</label>
                                <input type="text" class="form-control" name="db_user" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">كلمة المرور</label>
                                <input type="password" class="form-control" name="db_pass">
                            </div>
                        </div>
                        
                        <!-- Admin Account -->
                        <h5><i class="fas fa-user-shield"></i> حساب المدير</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">اسم المدير</label>
                                <input type="text" class="form-control" name="admin_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">البريد الإلكتروني</label>
                                <input type="email" class="form-control" name="admin_email" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">كلمة المرور</label>
                            <input type="password" class="form-control" name="admin_password" required minlength="6">
                        </div>
                        
                        <!-- Email Settings -->
                        <h5><i class="fas fa-envelope"></i> إعدادات البريد الإلكتروني</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">خادم SMTP</label>
                                <input type="text" class="form-control" name="mail_host" value="smtp.gmail.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">منفذ SMTP</label>
                                <input type="number" class="form-control" name="mail_port" value="587">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">اسم المستخدم</label>
                                <input type="email" class="form-control" name="mail_username">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">كلمة المرور</label>
                                <input type="password" class="form-control" name="mail_password">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">البريد المرسل</label>
                            <input type="email" class="form-control" name="mail_from_email">
                        </div>
                        
                        <!-- Google Maps -->
                        <h5><i class="fas fa-map"></i> Google Maps API</h5>
                        <div class="mb-3">
                            <label class="form-label">مفتاح Google Maps API</label>
                            <input type="text" class="form-control" name="google_maps_key">
                            <small class="form-text text-muted">اختياري - مطلوب لميزة الخرائط والموقع الجغرافي</small>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-download"></i> تثبيت النظام
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
