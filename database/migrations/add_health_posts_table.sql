-- Migration: Add health_posts table
-- Description: Create table for health posts/articles created by doctors

CREATE TABLE IF NOT EXISTS `health_posts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `doctor_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `image_path` VARCHAR(255) DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE CASCADE,
  INDEX `idx_status` (`status`),
  INDEX `idx_doctor_id` (`doctor_id`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

