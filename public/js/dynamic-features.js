/**
 * Doctorna Dynamic Features
 * Advanced AJAX functionality and real-time interactions
 */

// Appointment booking functionality
const AppointmentBooking = {
    // Initialize appointment booking
    init: function() {
        this.bindEvents();
        this.loadAvailableSlots();
    },
    
    // Bind event listeners
    bindEvents: function() {
        // Date selection
        document.addEventListener('change', function(e) {
            if (e.target.matches('#appointment-date')) {
                AppointmentBooking.loadAvailableSlots();
            }
        });
        
        // Time slot selection
        document.addEventListener('click', function(e) {
            if (e.target.matches('.time-slot')) {
                AppointmentBooking.selectTimeSlot(e.target);
            }
        });
        
        // Book appointment form
        const bookingForm = document.getElementById('booking-form');
        if (bookingForm) {
            bookingForm.addEventListener('submit', AppointmentBooking.submitBooking);
        }
    },
    
    // Load available time slots
    loadAvailableSlots: function() {
        const dateInput = document.getElementById('appointment-date');
        const doctorId = document.getElementById('doctor-id')?.value;
        const slotsContainer = document.getElementById('time-slots');
        
        if (!dateInput || !doctorId || !slotsContainer) return;
        
        const selectedDate = dateInput.value;
        if (!selectedDate) return;
        
        Doctorna.utils.showLoading(slotsContainer);
        
        Doctorna.ajax.get(`/doctor/${doctorId}/available-slots`, { date: selectedDate })
            .then(response => {
                if (response.success) {
                    this.renderTimeSlots(response.data.slots, slotsContainer);
                } else {
                    slotsContainer.innerHTML = '<div class="alert alert-warning">لا توجد مواعيد متاحة في هذا التاريخ</div>';
                }
            })
            .catch(error => {
                slotsContainer.innerHTML = '<div class="alert alert-danger">حدث خطأ في تحميل المواعيد</div>';
            });
    },
    
    // Render time slots
    renderTimeSlots: function(slots, container) {
        if (slots.length === 0) {
            container.innerHTML = '<div class="alert alert-info">لا توجد مواعيد متاحة في هذا التاريخ</div>';
            return;
        }
        
        const slotsHtml = slots.map(slot => `
            <button type="button" class="btn btn-outline-primary time-slot me-2 mb-2" 
                    data-time="${slot.time}" data-formatted="${slot.formatted}">
                ${slot.formatted}
            </button>
        `).join('');
        
        container.innerHTML = `
            <h6 class="mb-3">اختر الوقت المناسب:</h6>
            <div class="time-slots-grid">${slotsHtml}</div>
        `;
    },
    
    // Select time slot
    selectTimeSlot: function(button) {
        // Remove previous selection
        document.querySelectorAll('.time-slot').forEach(btn => {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-outline-primary');
        });
        
        // Select current slot
        button.classList.remove('btn-outline-primary');
        button.classList.add('btn-primary');
        
        // Update hidden input
        const timeInput = document.getElementById('appointment-time');
        if (timeInput) {
            timeInput.value = button.dataset.time;
        }
        
        // Show booking form
        const bookingSection = document.getElementById('booking-section');
        if (bookingSection) {
            bookingSection.style.display = 'block';
            bookingSection.scrollIntoView({ behavior: 'smooth' });
        }
    },
    
    // Submit booking
    submitBooking: function(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        // Validate required fields
        if (!data.appointment_date || !data.appointment_time) {
            Doctorna.utils.showToast('يرجى اختيار التاريخ والوقت', 'warning');
            return;
        }
        
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>جاري الحجز...';
        submitBtn.disabled = true;
        
        Doctorna.ajax.post('/patient/book-appointment', data)
            .then(response => {
                if (response.success) {
                    Doctorna.utils.showToast('تم حجز الموعد بنجاح', 'success');
                    // Redirect to appointments page
                    setTimeout(() => {
                        window.location.href = '/patient/appointments';
                    }, 2000);
                } else {
                    Doctorna.utils.showToast(response.message || 'حدث خطأ في الحجز', 'danger');
                }
            })
            .catch(error => {
                Doctorna.utils.showToast('حدث خطأ في الحجز', 'danger');
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
    }
};

// Live search functionality
const LiveSearch = {
    // Initialize live search
    init: function() {
        this.bindEvents();
    },
    
    // Bind event listeners
    bindEvents: function() {
        const searchInputs = document.querySelectorAll('.live-search');
        searchInputs.forEach(input => {
            this.setupLiveSearch(input);
        });
    },
    
    // Setup live search for an input
    setupLiveSearch: function(input) {
        const searchUrl = input.dataset.searchUrl;
        const resultsContainer = document.querySelector(input.dataset.resultsContainer);
        const minLength = parseInt(input.dataset.minLength) || 2;
        
        if (!searchUrl || !resultsContainer) return;
        
        let searchTimeout;
        
        input.addEventListener('input', function() {
            const query = this.value.trim();
            
            clearTimeout(searchTimeout);
            
            if (query.length < minLength) {
                resultsContainer.innerHTML = '';
                return;
            }
            
            searchTimeout = setTimeout(() => {
                LiveSearch.performSearch(query, searchUrl, resultsContainer);
            }, 300);
        });
        
        // Hide results when clicking outside
        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !resultsContainer.contains(e.target)) {
                resultsContainer.innerHTML = '';
            }
        });
    },
    
    // Perform search
    performSearch: function(query, url, container) {
        Doctorna.utils.showLoading(container);
        
        Doctorna.ajax.get(url, { q: query })
            .then(response => {
                if (response.success) {
                    container.innerHTML = response.html || '<div class="text-muted p-3">لا توجد نتائج</div>';
                } else {
                    container.innerHTML = '<div class="text-muted p-3">لا توجد نتائج</div>';
                }
            })
            .catch(error => {
                container.innerHTML = '<div class="text-danger p-3">حدث خطأ في البحث</div>';
            });
    }
};

// Real-time notifications
const Notifications = {
    // Initialize notifications
    init: function() {
        this.checkForUpdates();
        this.startPolling();
    },
    
    // Check for new notifications
    checkForUpdates: function() {
        Doctorna.ajax.get('/notifications/check')
            .then(response => {
                if (response.success && response.data.notifications.length > 0) {
                    this.updateNotificationBadge(response.data.count);
                    this.showNewNotifications(response.data.notifications);
                }
            })
            .catch(error => {
                console.error('Error checking notifications:', error);
            });
    },
    
    // Start polling for notifications
    startPolling: function() {
        setInterval(() => {
            this.checkForUpdates();
        }, 30000); // Check every 30 seconds
    },
    
    // Update notification badge
    updateNotificationBadge: function(count) {
        const badge = document.querySelector('.notification-badge');
        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'inline' : 'none';
        }
    },
    
    // Show new notifications
    showNewNotifications: function(notifications) {
        notifications.forEach(notification => {
            if (!notification.is_read) {
                Doctorna.utils.showToast(notification.message, 'info', 8000);
            }
        });
    },
    
    // Mark notification as read
    markAsRead: function(notificationId) {
        Doctorna.ajax.post(`/notifications/${notificationId}/read`)
            .then(response => {
                if (response.success) {
                    const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
                    if (notificationElement) {
                        notificationElement.classList.add('read');
                    }
                }
            })
            .catch(error => {
                console.error('Error marking notification as read:', error);
            });
    }
};

// Auto-save functionality
const AutoSave = {
    // Initialize auto-save
    init: function() {
        this.bindEvents();
    },
    
    // Bind event listeners
    bindEvents: function() {
        const autoSaveForms = document.querySelectorAll('.auto-save');
        autoSaveForms.forEach(form => {
            this.setupAutoSave(form);
        });
    },
    
    // Setup auto-save for a form
    setupAutoSave: function(form) {
        const interval = parseInt(form.dataset.autoSaveInterval) || 30000; // 30 seconds default
        const url = form.dataset.autoSaveUrl || '/auto-save';
        
        let saveTimeout;
        let hasChanges = false;
        
        // Track changes
        form.addEventListener('input', function() {
            hasChanges = true;
            clearTimeout(saveTimeout);
            
            saveTimeout = setTimeout(() => {
                if (hasChanges) {
                    AutoSave.saveForm(form, url);
                    hasChanges = false;
                }
            }, interval);
        });
        
        // Save on page unload
        window.addEventListener('beforeunload', function() {
            if (hasChanges) {
                AutoSave.saveForm(form, url, false); // Synchronous save
            }
        });
    },
    
    // Save form data
    saveForm: function(form, url, async = true) {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        if (async) {
            Doctorna.ajax.post(url, data)
                .then(response => {
                    if (response.success) {
                        this.showSaveIndicator('تم الحفظ تلقائياً', 'success');
                    }
                })
                .catch(error => {
                    this.showSaveIndicator('فشل الحفظ التلقائي', 'warning');
                });
        } else {
            // Synchronous save for page unload
            navigator.sendBeacon(url, JSON.stringify(data));
        }
    },
    
    // Show save indicator
    showSaveIndicator: function(message, type) {
        const indicator = document.getElementById('auto-save-indicator');
        if (indicator) {
            indicator.textContent = message;
            indicator.className = `auto-save-indicator text-${type}`;
            indicator.style.display = 'block';
            
            setTimeout(() => {
                indicator.style.display = 'none';
            }, 3000);
        }
    }
};

// File upload with progress
const FileUpload = {
    // Initialize file upload
    init: function() {
        this.bindEvents();
    },
    
    // Bind event listeners
    bindEvents: function() {
        const fileInputs = document.querySelectorAll('.file-upload');
        fileInputs.forEach(input => {
            this.setupFileUpload(input);
        });
    },
    
    // Setup file upload
    setupFileUpload: function(input) {
        const progressContainer = document.querySelector(input.dataset.progressContainer);
        const previewContainer = document.querySelector(input.dataset.previewContainer);
        
        input.addEventListener('change', function() {
            const files = this.files;
            if (files.length > 0) {
                FileUpload.uploadFiles(files, input.dataset.uploadUrl, progressContainer, previewContainer);
            }
        });
    },
    
    // Upload files
    uploadFiles: function(files, url, progressContainer, previewContainer) {
        Array.from(files).forEach(file => {
            const formData = new FormData();
            formData.append('file', file);
            
            const xhr = new XMLHttpRequest();
            
            // Progress tracking
            if (progressContainer) {
                const progressBar = this.createProgressBar(file.name);
                progressContainer.appendChild(progressBar);
                
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        progressBar.querySelector('.progress-bar').style.width = percentComplete + '%';
                    }
                });
            }
            
            // Handle response
            xhr.addEventListener('load', function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success && previewContainer) {
                        FileUpload.showPreview(response.data, previewContainer);
                    }
                }
                
                // Remove progress bar
                if (progressContainer) {
                    const progressBar = progressContainer.querySelector(`[data-file="${file.name}"]`);
                    if (progressBar) {
                        progressBar.remove();
                    }
                }
            });
            
            xhr.open('POST', url);
            xhr.setRequestHeader('X-CSRF-TOKEN', Doctorna.config.csrfToken);
            xhr.send(formData);
        });
    },
    
    // Create progress bar
    createProgressBar: function(fileName) {
        const progressBar = document.createElement('div');
        progressBar.className = 'mb-2';
        progressBar.dataset.file = fileName;
        progressBar.innerHTML = `
            <small class="text-muted">${fileName}</small>
            <div class="progress">
                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
            </div>
        `;
        return progressBar;
    },
    
    // Show file preview
    showPreview: function(fileData, container) {
        const preview = document.createElement('div');
        preview.className = 'file-preview mb-2';
        
        if (fileData.type.startsWith('image/')) {
            preview.innerHTML = `
                <img src="${fileData.url}" alt="${fileData.name}" class="img-thumbnail" style="max-width: 200px;">
                <p class="small text-muted">${fileData.name}</p>
            `;
        } else {
            preview.innerHTML = `
                <div class="file-icon">
                    <i class="fas fa-file fa-2x"></i>
                    <p class="small text-muted">${fileData.name}</p>
                </div>
            `;
        }
        
        container.appendChild(preview);
    }
};

// Initialize all dynamic features
document.addEventListener('DOMContentLoaded', function() {
    AppointmentBooking.init();
    LiveSearch.init();
    Notifications.init();
    AutoSave.init();
    FileUpload.init();
    
    console.log('Dynamic features initialized');
});
