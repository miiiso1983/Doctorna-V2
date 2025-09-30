-- Doctorna Database Schema
-- Doctor Appointment Booking System

SET FOREIGN_KEY_CHECKS = 0;

-- Create database
CREATE DATABASE IF NOT EXISTS doctorna_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE doctorna_db;

-- Users table (main users table for all roles)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'doctor', 'patient') NOT NULL DEFAULT 'patient',
    status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    email_verified_at TIMESTAMP NULL,
    avatar VARCHAR(255),
    address TEXT,
    city VARCHAR(100),
    country VARCHAR(100) DEFAULT 'Iraq',
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status),
    INDEX idx_location (latitude, longitude)
);

-- Medical specializations table
CREATE TABLE specializations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    name_en VARCHAR(255),
    description TEXT,
    icon VARCHAR(100),
    color VARCHAR(7) DEFAULT '#007bff',
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_active (is_active),
    INDEX idx_sort (sort_order)
);

-- Doctors table (extended profile for doctors)
CREATE TABLE doctors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    specialization_id INT,
    license_number VARCHAR(100),
    experience_years INT DEFAULT 0,
    biography TEXT,
    education TEXT,
    certifications TEXT,
    languages VARCHAR(255),
    consultation_fee DECIMAL(10, 2) DEFAULT 0.00,
    status ENUM('pending', 'approved', 'suspended') DEFAULT 'pending',
    rating DECIMAL(3, 2) DEFAULT 0.00,
    total_reviews INT DEFAULT 0,
    total_appointments INT DEFAULT 0,
    clinic_name VARCHAR(255),
    clinic_address TEXT,
    clinic_phone VARCHAR(20),
    working_hours JSON,
    available_days JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (specialization_id) REFERENCES specializations(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_specialization (specialization_id),
    INDEX idx_status (status),
    INDEX idx_rating (rating)
);

-- Patients table (extended profile for patients)
CREATE TABLE patients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    date_of_birth DATE,
    gender ENUM('male', 'female') DEFAULT 'male',
    blood_type VARCHAR(5),
    height DECIMAL(5, 2),
    weight DECIMAL(5, 2),
    emergency_contact VARCHAR(20),
    emergency_contact_name VARCHAR(255),
    medical_history TEXT,
    allergies TEXT,
    current_medications TEXT,
    insurance_provider VARCHAR(255),
    insurance_number VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_gender (gender),
    INDEX idx_birth_date (date_of_birth)
);

-- Symptoms table
CREATE TABLE symptoms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    name_en VARCHAR(255),
    description TEXT,
    category VARCHAR(100),
    severity_level ENUM('mild', 'moderate', 'severe') DEFAULT 'mild',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_category (category),
    INDEX idx_active (is_active)
);

-- Symptom-Specialization mapping (for recommendations)
CREATE TABLE symptom_specializations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    symptom_id INT NOT NULL,
    specialization_id INT NOT NULL,
    relevance_score DECIMAL(3, 2) DEFAULT 1.00,

    FOREIGN KEY (symptom_id) REFERENCES symptoms(id) ON DELETE CASCADE,
    FOREIGN KEY (specialization_id) REFERENCES specializations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_symptom_spec (symptom_id, specialization_id)
);

-- Patient symptoms tracking
CREATE TABLE patient_symptoms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    symptom_id INT NOT NULL,
    severity ENUM('mild', 'moderate', 'severe') DEFAULT 'mild',
    duration VARCHAR(100),
    notes TEXT,
    reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (symptom_id) REFERENCES symptoms(id) ON DELETE CASCADE,
    INDEX idx_patient (patient_id),
    INDEX idx_symptom (symptom_id),
    INDEX idx_reported (reported_at)
);

-- Appointments table
CREATE TABLE appointments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    duration INT DEFAULT 30,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed', 'no_show') DEFAULT 'pending',
    type ENUM('consultation', 'follow_up', 'emergency') DEFAULT 'consultation',
    symptoms TEXT,
    notes TEXT,
    doctor_notes TEXT,
    prescription TEXT,
    fee DECIMAL(10, 2),
    payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
    cancellation_reason TEXT,
    cancelled_by ENUM('patient', 'doctor', 'admin'),
    cancelled_at TIMESTAMP NULL,
    confirmed_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    INDEX idx_patient (patient_id),
    INDEX idx_doctor (doctor_id),
    INDEX idx_date (appointment_date),
    INDEX idx_status (status),
    INDEX idx_datetime (appointment_date, appointment_time)
);

-- Doctor availability/schedule
CREATE TABLE doctor_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    doctor_id INT NOT NULL,
    day_of_week ENUM('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_available BOOLEAN DEFAULT TRUE,
    break_start TIME,
    break_end TIME,
    max_appointments INT DEFAULT 20,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    INDEX idx_doctor (doctor_id),
    INDEX idx_day (day_of_week),
    UNIQUE KEY unique_doctor_day (doctor_id, day_of_week)
);

-- Payments table
CREATE TABLE IF NOT EXISTS payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    appointment_id INT NOT NULL,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'IQD',
    status ENUM('initiated','pending','paid','failed','refunded','canceled') DEFAULT 'initiated',
    gateway VARCHAR(50) DEFAULT 'qi_card',
    gateway_ref VARCHAR(191) NULL,
    auth_code VARCHAR(64) NULL,
    extra TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL,
    INDEX idx_pay_appt (appointment_id),
    INDEX idx_pay_patient (patient_id),
    INDEX idx_pay_doctor (doctor_id)
);


-- Doctor reviews and ratings
CREATE TABLE reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    appointment_id INT NOT NULL,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review TEXT,
    is_anonymous BOOLEAN DEFAULT FALSE,
    is_approved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    INDEX idx_doctor (doctor_id),
    INDEX idx_patient (patient_id),
    INDEX idx_rating (rating),
    INDEX idx_approved (is_approved)
);

-- Notifications table
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    data JSON,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_read (is_read),
    INDEX idx_type (type),
    INDEX idx_created (created_at)
);

-- Password reset tokens
CREATE TABLE password_resets (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_token (token),
    INDEX idx_expires (expires_at)
);

-- System settings
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    key_name VARCHAR(255) UNIQUE NOT NULL,
    value TEXT,
    description TEXT,
    type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_key (key_name),
    INDEX idx_public (is_public)
);

-- Activity logs
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
);

SET FOREIGN_KEY_CHECKS = 1;

-- Sample Data

-- Insert specializations
INSERT INTO specializations (name, name_en, description, icon, color) VALUES
('طب عام', 'General Medicine', 'الطب العام والفحوصات الأولية', 'fas fa-stethoscope', '#007bff'),
('طب الأطفال', 'Pediatrics', 'طب الأطفال والرضع', 'fas fa-baby', '#28a745'),
('طب النساء والولادة', 'Gynecology & Obstetrics', 'طب النساء والولادة', 'fas fa-female', '#e83e8c'),
('طب القلب', 'Cardiology', 'أمراض القلب والأوعية الدموية', 'fas fa-heartbeat', '#dc3545'),
('طب العيون', 'Ophthalmology', 'أمراض العيون والبصر', 'fas fa-eye', '#fd7e14'),
('طب الأسنان', 'Dentistry', 'طب وجراحة الأسنان', 'fas fa-tooth', '#20c997'),
('الطب النفسي', 'Psychiatry', 'الصحة النفسية والعقلية', 'fas fa-brain', '#6f42c1'),
('جراحة عامة', 'General Surgery', 'الجراحة العامة', 'fas fa-cut', '#495057'),
('طب الجلدية', 'Dermatology', 'أمراض الجلد والتجميل', 'fas fa-hand-paper', '#ffc107'),
('طب العظام', 'Orthopedics', 'أمراض العظام والمفاصل', 'fas fa-bone', '#6c757d');

-- Insert symptoms
INSERT INTO symptoms (name, name_en, category, severity_level) VALUES
('صداع', 'Headache', 'neurological', 'mild'),
('حمى', 'Fever', 'general', 'moderate'),
('سعال', 'Cough', 'respiratory', 'mild'),
('ألم في الصدر', 'Chest Pain', 'cardiovascular', 'severe'),
('ضيق في التنفس', 'Shortness of Breath', 'respiratory', 'moderate'),
('ألم في البطن', 'Abdominal Pain', 'gastrointestinal', 'moderate'),
('غثيان', 'Nausea', 'gastrointestinal', 'mild'),
('دوخة', 'Dizziness', 'neurological', 'mild'),
('ألم في الظهر', 'Back Pain', 'musculoskeletal', 'moderate'),
('طفح جلدي', 'Skin Rash', 'dermatological', 'mild'),
('ألم في المفاصل', 'Joint Pain', 'musculoskeletal', 'moderate'),
('اضطراب في النوم', 'Sleep Disorder', 'neurological', 'mild'),
('قلق', 'Anxiety', 'psychological', 'moderate'),
('اكتئاب', 'Depression', 'psychological', 'severe'),
('ألم في الأسنان', 'Tooth Pain', 'dental', 'moderate');

-- Map symptoms to specializations
INSERT INTO symptom_specializations (symptom_id, specialization_id, relevance_score) VALUES
(1, 1, 0.8), (1, 7, 0.9), -- صداع -> طب عام، طب نفسي
(2, 1, 0.9), (2, 2, 0.7), -- حمى -> طب عام، أطفال
(3, 1, 0.8), -- سعال -> طب عام
(4, 4, 0.9), (4, 1, 0.6), -- ألم صدر -> قلب، طب عام
(5, 4, 0.9), (5, 1, 0.7), -- ضيق تنفس -> قلب، طب عام
(6, 1, 0.8), -- ألم بطن -> طب عام
(7, 1, 0.8), -- غثيان -> طب عام
(8, 1, 0.7), (8, 7, 0.6), -- دوخة -> طب عام، نفسي
(9, 10, 0.9), (9, 1, 0.6), -- ألم ظهر -> عظام، طب عام
(10, 9, 0.9), -- طفح جلدي -> جلدية
(11, 10, 0.9), (11, 1, 0.6), -- ألم مفاصل -> عظام، طب عام
(12, 7, 0.9), (12, 1, 0.5), -- اضطراب نوم -> نفسي، طب عام
(13, 7, 0.9), -- قلق -> نفسي
(14, 7, 0.9), -- اكتئاب -> نفسي
(15, 6, 0.9); -- ألم أسنان -> أسنان

-- Insert admin user
INSERT INTO users (name, email, phone, password, role, status, email_verified_at, city, country) VALUES
('مدير النظام', 'admin@doctorna.com', '+9647701234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', 'active', NOW(), 'بغداد', 'Iraq');

-- Insert sample doctors
INSERT INTO users (name, email, phone, password, role, status, email_verified_at, address, city, country, latitude, longitude) VALUES
('د. أحمد محمد', 'ahmed@doctorna.com', '+9647701111111', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor', 'active', NOW(), 'شارع السعدون، بغداد', 'بغداد', 'Iraq', 33.3152, 44.3661),
('د. فاطمة علي', 'fatima@doctorna.com', '+9647702222222', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor', 'active', NOW(), 'شارع الجزائر، البصرة', 'البصرة', 'Iraq', 30.5081, 47.7835),
('د. محمد السعيد', 'mohammed@doctorna.com', '+9647703333333', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor', 'active', NOW(), 'شارع 60 متر، أربيل', 'أربيل', 'Iraq', 36.1911, 44.0092);

-- Insert doctor profiles
INSERT INTO doctors (user_id, specialization_id, license_number, experience_years, biography, consultation_fee, status, rating, total_reviews, clinic_name, clinic_address, clinic_phone, working_hours, available_days) VALUES
(2, 1, 'DOC001', 10, 'طبيب عام متخصص في الطب الباطني مع خبرة 10 سنوات في التشخيص والعلاج', 200.00, 'approved', 4.5, 25, 'عيادة بغداد الطبية', 'شارع السعدون، بغداد', '+9647701111111', '{"morning": {"start": "08:00", "end": "12:00"}, "evening": {"start": "16:00", "end": "20:00"}}', '["sunday", "monday", "tuesday", "wednesday", "thursday"]'),
(3, 3, 'DOC002', 8, 'طبيبة نساء وولادة متخصصة في الحمل والولادة الطبيعية', 300.00, 'approved', 4.8, 40, 'مستشفى البصرة للنساء', 'شارع الجزائر، البصرة', '+9647702222222', '{"morning": {"start": "09:00", "end": "13:00"}, "evening": {"start": "17:00", "end": "21:00"}}', '["sunday", "monday", "tuesday", "wednesday", "thursday", "saturday"]'),
(4, 4, 'DOC003', 12, 'طبيب قلب وأوعية دموية مع خبرة واسعة في جراحة القلب', 400.00, 'approved', 4.7, 35, 'مركز أربيل لأمراض القلب', 'شارع 60 متر، أربيل', '+9647703333333', '{"morning": {"start": "08:30", "end": "12:30"}, "evening": {"start": "15:30", "end": "19:30"}}', '["sunday", "monday", "tuesday", "wednesday", "thursday"]');

-- Insert sample patients
INSERT INTO users (name, email, phone, password, role, status, email_verified_at, address, city, country, latitude, longitude) VALUES
('سارة أحمد', 'sara@example.com', '+9647704444444', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient', 'active', NOW(), 'حي الكرادة، بغداد', 'بغداد', 'Iraq', 33.3152, 44.3661),
('خالد محمد', 'khalid@example.com', '+9647705555555', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient', 'active', NOW(), 'حي الجبيلة، البصرة', 'البصرة', 'Iraq', 30.5081, 47.7835);

-- Insert patient profiles
INSERT INTO patients (user_id, date_of_birth, gender, blood_type, emergency_contact, emergency_contact_name) VALUES
(5, '1990-05-15', 'female', 'O+', '+9647706666666', 'أحمد سارة'),
(6, '1985-12-20', 'male', 'A+', '+9647707777777', 'فاطمة خالد');

-- Insert doctor schedules
INSERT INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time, is_available, max_appointments) VALUES
(1, 'sunday', '08:00:00', '12:00:00', TRUE, 8),
(1, 'sunday', '16:00:00', '20:00:00', TRUE, 8),
(1, 'monday', '08:00:00', '12:00:00', TRUE, 8),
(1, 'monday', '16:00:00', '20:00:00', TRUE, 8),
(1, 'tuesday', '08:00:00', '12:00:00', TRUE, 8),
(1, 'tuesday', '16:00:00', '20:00:00', TRUE, 8),
(1, 'wednesday', '08:00:00', '12:00:00', TRUE, 8),
(1, 'wednesday', '16:00:00', '20:00:00', TRUE, 8),
(1, 'thursday', '08:00:00', '12:00:00', TRUE, 8),
(1, 'thursday', '16:00:00', '20:00:00', TRUE, 8);

-- Insert system settings
INSERT INTO settings (key_name, value, description, type, is_public) VALUES
('site_name', 'Doctorna - نظام حجز المواعيد الطبية', 'اسم الموقع', 'string', TRUE),
('site_description', 'نظام متطور لحجز المواعيد الطبية مع الأطباء المتخصصين', 'وصف الموقع', 'string', TRUE),
('contact_email', 'info@doctorna.com', 'البريد الإلكتروني للتواصل', 'string', TRUE),
('contact_phone', '+9647700000000', 'رقم الهاتف للتواصل', 'string', TRUE),
('appointment_duration', '30', 'مدة الموعد بالدقائق', 'number', FALSE),
('max_appointments_per_day', '20', 'أقصى عدد مواعيد في اليوم', 'number', FALSE),
('booking_advance_days', '30', 'عدد الأيام المسموح بحجز موعد مسبقاً', 'number', FALSE),
('cancellation_hours', '24', 'عدد الساعات المطلوبة لإلغاء الموعد', 'number', FALSE);
