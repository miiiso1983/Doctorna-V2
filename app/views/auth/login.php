<div class="row justify-content-center">
    <div class="col-md-6">
        <h3 class="text-center mb-4 fw-bold"><?= $title ?></h3>
        
        <form method="POST" action="<?= $this->url('/login') ?>" novalidate>
            <?= $this->csrfField() ?>
            
            <div class="mb-3">
                <label for="email" class="form-label fw-bold">
                    <i class="fas fa-envelope me-2"></i>
                    البريد الإلكتروني
                </label>
                <input type="email" 
                       class="form-control <?= $this->hasError('email') ? 'is-invalid' : '' ?>" 
                       id="email" 
                       name="email" 
                       value="<?= $this->escape($this->old('email')) ?>"
                       placeholder="أدخل بريدك الإلكتروني"
                       required>
                <?php if ($this->hasError('email')): ?>
                    <div class="invalid-feedback">
                        <?= implode('<br>', $this->error('email')) ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label fw-bold">
                    <i class="fas fa-lock me-2"></i>
                    كلمة المرور
                </label>
                <div class="position-relative">
                    <input type="password" 
                           class="form-control <?= $this->hasError('password') ? 'is-invalid' : '' ?>" 
                           id="password" 
                           name="password" 
                           placeholder="أدخل كلمة المرور"
                           required>
                    <button type="button" 
                            class="btn btn-link position-absolute top-50 end-0 translate-middle-y pe-3" 
                            onclick="togglePassword('password')">
                        <i class="fas fa-eye" id="password-eye"></i>
                    </button>
                </div>
                <?php if ($this->hasError('password')): ?>
                    <div class="invalid-feedback">
                        <?= implode('<br>', $this->error('password')) ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
                        <label class="form-check-label" for="remember">
                            تذكرني
                        </label>
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="<?= $this->url('/forgot-password') ?>" class="text-decoration-none">
                        نسيت كلمة المرور؟
                    </a>
                </div>
            </div>
            
            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    تسجيل الدخول
                </button>
            </div>
        </form>
        
        <div class="auth-links">
            <p class="text-center text-muted">
                ليس لديك حساب؟ 
                <a href="<?= $this->url('/register') ?>" class="fw-bold">
                    إنشاء حساب جديد
                </a>
            </p>
        </div>
        
        <!-- Demo Accounts -->
        <div class="mt-4 p-3 bg-light rounded">
            <h6 class="fw-bold text-center mb-3">حسابات تجريبية</h6>
            <div class="row text-center">
                <div class="col-md-4 mb-2">
                    <small class="d-block fw-bold">مدير النظام</small>
                    <small class="text-muted">admin@doctorna.com</small>
                </div>
                <div class="col-md-4 mb-2">
                    <small class="d-block fw-bold">طبيب</small>
                    <small class="text-muted">ahmed@doctorna.com</small>
                </div>
                <div class="col-md-4 mb-2">
                    <small class="d-block fw-bold">مريض</small>
                    <small class="text-muted">sara@example.com</small>
                </div>
            </div>
            <p class="text-center text-muted small mb-0 mt-2">
                كلمة المرور لجميع الحسابات: <code>password</code>
            </p>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const eye = document.getElementById(inputId + '-eye');
    
    if (input.type === 'password') {
        input.type = 'text';
        eye.classList.remove('fa-eye');
        eye.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        eye.classList.remove('fa-eye-slash');
        eye.classList.add('fa-eye');
    }
}

// Auto-fill demo credentials
document.addEventListener('DOMContentLoaded', function() {
    // Add click handlers for demo accounts
    const demoAccounts = [
        { email: 'admin@doctorna.com', type: 'مدير النظام' },
        { email: 'ahmed@doctorna.com', type: 'طبيب' },
        { email: 'sara@example.com', type: 'مريض' }
    ];
    
    // Add clickable demo account buttons
    const demoContainer = document.querySelector('.bg-light.rounded');
    const demoButtons = demoContainer.querySelectorAll('.col-md-4');
    
    demoButtons.forEach((button, index) => {
        button.style.cursor = 'pointer';
        button.addEventListener('click', function() {
            document.getElementById('email').value = demoAccounts[index].email;
            document.getElementById('password').value = 'password';
        });
    });
});
</script>
