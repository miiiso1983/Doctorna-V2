/**
 * Doctorna - Main JavaScript File
 */

// Global variables
window.Doctorna = {
    config: {
        baseUrl: window.location.origin,
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        locale: 'ar'
    },
    
    // Utility functions
    utils: {
        // Show loading spinner
        showLoading: function(element) {
            if (typeof element === 'string') {
                element = document.querySelector(element);
            }
            if (element) {
                element.innerHTML = '<div class="spinner mx-auto"></div>';
            }
        },
        
        // Hide loading spinner
        hideLoading: function(element) {
            if (typeof element === 'string') {
                element = document.querySelector(element);
            }
            if (element) {
                element.innerHTML = '';
            }
        },
        
        // Show toast notification
        showToast: function(message, type = 'success') {
            const toastContainer = document.getElementById('toast-container') || this.createToastContainer();
            const toast = this.createToast(message, type);
            toastContainer.appendChild(toast);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                toast.remove();
            }, 5000);
        },
        
        // Create toast container
        createToastContainer: function() {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
            return container;
        },
        
        // Create toast element
        createToast: function(message, type) {
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type} border-0`;
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.parentElement.parentElement.remove()"></button>
                </div>
            `;
            
            // Show toast
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            return toast;
        },
        
        // Format currency (Iraqi Dinar)
        formatCurrency: function(amount) {
            try {
                return new Intl.NumberFormat('ar-IQ', {
                    style: 'currency',
                    currency: 'IQD',
                    currencyDisplay: 'symbol',
                    maximumFractionDigits: 0
                }).format(amount);
            } catch (e) {
                // Fallback
                return (Number(amount) || 0).toLocaleString('ar-IQ') + ' د.ع';
            }
        },
        
        // Format date
        formatDate: function(date, options = {}) {
            const defaultOptions = {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            return new Intl.DateTimeFormat('ar-IQ', { ...defaultOptions, ...options }).format(new Date(date));
        },
        
        // Validate email
        validateEmail: function(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },
        
        // Validate phone
        validatePhone: function(phone) {
            const re = /^[0-9+\-\s()]+$/;
            return re.test(phone);
        }
    },
    
    // AJAX helper
    ajax: {
        // GET request
        get: function(url, options = {}) {
            return this.request('GET', url, null, options);
        },
        
        // POST request
        post: function(url, data = {}, options = {}) {
            return this.request('POST', url, data, options);
        },
        
        // PUT request
        put: function(url, data = {}, options = {}) {
            return this.request('PUT', url, data, options);
        },
        
        // DELETE request
        delete: function(url, options = {}) {
            return this.request('DELETE', url, null, options);
        },
        
        // Generic request
        request: function(method, url, data = null, options = {}) {
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };
            
            // Add CSRF token for state-changing requests
            if (['POST', 'PUT', 'DELETE', 'PATCH'].includes(method)) {
                defaultOptions.headers['X-CSRF-TOKEN'] = Doctorna.config.csrfToken;
            }
            
            const config = {
                method: method,
                ...defaultOptions,
                ...options
            };
            
            if (data) {
                if (data instanceof FormData) {
                    delete config.headers['Content-Type']; // Let browser set it
                    config.body = data;
                } else {
                    config.body = JSON.stringify(data);
                }
            }
            
            return fetch(url, config)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .catch(error => {
                    console.error('AJAX Error:', error);
                    throw error;
                });
        }
    },
    
    // Form helpers
    forms: {
        // Serialize form data
        serialize: function(form) {
            if (typeof form === 'string') {
                form = document.querySelector(form);
            }

            const formData = new FormData(form);
            const data = {};

            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }
            
            return data;
        },
        
        // Validate form
        validate: function(form) {
            if (typeof form === 'string') {
                form = document.querySelector(form);
            }
            
            const inputs = form.querySelectorAll('input, select, textarea');
            let isValid = true;
            
            inputs.forEach(input => {
                if (input.hasAttribute('required') && !input.value.trim()) {
                    this.showFieldError(input, 'هذا الحقل مطلوب');
                    isValid = false;
                } else if (input.type === 'email' && input.value && !Doctorna.utils.validateEmail(input.value)) {
                    this.showFieldError(input, 'البريد الإلكتروني غير صحيح');
                    isValid = false;
                } else {
                    this.clearFieldError(input);
                }
            });
            
            return isValid;
        },
        
        // Show field error
        showFieldError: function(field, message) {
            field.classList.add('is-invalid');
            
            let feedback = field.parentNode.querySelector('.invalid-feedback');
            if (!feedback) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                field.parentNode.appendChild(feedback);
            }
            feedback.textContent = message;
        },
        
        // Clear field error
        clearFieldError: function(field) {
            field.classList.remove('is-invalid');
            const feedback = field.parentNode.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.remove();
            }
        }
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Auto-hide alerts
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    // Confirm dialogs
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    confirmButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            const message = this.getAttribute('data-confirm');
            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    });
    
    // Auto-resize textareas
    const textareas = document.querySelectorAll('textarea[data-auto-resize]');
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    });
    
    // Search functionality
    const searchInputs = document.querySelectorAll('[data-search]');
    searchInputs.forEach(input => {
        let timeout;
        input.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                const target = this.getAttribute('data-search');
                const query = this.value.toLowerCase();
                const items = document.querySelectorAll(target);
                
                items.forEach(item => {
                    const text = item.textContent.toLowerCase();
                    if (text.includes(query)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            }, 300);
        });
    });
    
    // Smooth scrolling for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                event.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Back to top button
    const backToTopButton = document.createElement('button');
    backToTopButton.innerHTML = '<i class="fas fa-arrow-up"></i>';
    backToTopButton.className = 'btn btn-primary position-fixed bottom-0 end-0 m-3 rounded-circle';
    backToTopButton.style.display = 'none';
    backToTopButton.style.zIndex = '9999';
    backToTopButton.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    document.body.appendChild(backToTopButton);
    
    // Show/hide back to top button
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            backToTopButton.style.display = 'block';
        } else {
            backToTopButton.style.display = 'none';
        }
    });
});

// Global error handler
window.addEventListener('error', function(event) {
    console.error('Global error:', event.error);
    // You can send error reports to your server here
});

// Service Worker registration (for PWA support)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js')
            .then(function(registration) {
                console.log('ServiceWorker registration successful');
            })
            .catch(function(error) {
                console.log('ServiceWorker registration failed');
            });
    });
}
