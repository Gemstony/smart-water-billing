<?php
// SQL file for Smart Water Billing System
-- Run this file to create all required tables

-- 1. Users Table (for both customers and admins)
CREATE TABLE `users` (
    `user_id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) UNIQUE NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `meter_id` VARCHAR(50) UNIQUE,
    `account_balance` DECIMAL(10,2) DEFAULT 0.00,
    `role` ENUM('admin', 'customer') DEFAULT 'customer',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_meter_id` (`meter_id`),
    INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tokens Table (for prepaid unit top-ups)
CREATE TABLE `tokens` (
    `token_id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `token_code` VARCHAR(20) UNIQUE NOT NULL,
    `user_id` INT(11) NOT NULL,
    `units_purchased` INT(11) NOT NULL,
    `is_used` TINYINT(1) DEFAULT 0,
    `generated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP NULL DEFAULT NULL,
    `used_at` TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
    INDEX `idx_token_code` (`token_code`),
    INDEX `idx_user_used` (`user_id`, `is_used`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Transactions Table (payment log)
CREATE TABLE `transactions` (
    `transaction_id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT(11) NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `water_units` INT(11) NOT NULL,
    `control_number` VARCHAR(50) UNIQUE NOT NULL,
    `payment_method` ENUM('M-Pesa', 'Tigo Pesa', 'Airtel Money') NOT NULL,
    `status` ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    `mpesa_receipt` VARCHAR(50) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `completed_at` TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
    INDEX `idx_control_number` (`control_number`),
    INDEX `idx_user_status` (`user_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Water Usage Table (real-time consumption from ESP32)
CREATE TABLE `water_usage` (
    `usage_id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `meter_id` VARCHAR(50) NOT NULL,
    `user_id` INT(11) NOT NULL,
    `water_used` DECIMAL(10,2) NOT NULL,
    `log_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
    INDEX `idx_meter_id` (`meter_id`),
    INDEX `idx_user_time` (`user_id`, `log_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Rates Table (price per unit, can be changed over time)
CREATE TABLE `rates` (
    `rate_id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `price_per_unit` DECIMAL(10,2) NOT NULL,
    `effective_date` DATE NOT NULL,
    `created_by` INT(11) NOT NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`),
    INDEX `idx_effective_date` (`effective_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. ESP32 Devices Table (for managing hardware)
CREATE TABLE `esp_devices` (
    `device_id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `meter_id` VARCHAR(50) UNIQUE NOT NULL,
    `user_id` INT(11) NULL,
    `firmware_version` VARCHAR(20),
    `last_seen` TIMESTAMP NULL,
    `valve_status` TINYINT(1) DEFAULT 1 COMMENT '1=open, 0=closed',
    `status` ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL,
    INDEX `idx_meter_id` (`meter_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `azampay_tokens` (
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `access_token` TEXT NOT NULL,
    `expires_at` TIMESTAMP NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `payment_logs` (
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `transaction_id` INT(11) NULL,
    `external_id` VARCHAR(100) NULL,
    `request_payload` JSON NULL,
    `response_payload` JSON NULL,
    `http_code` INT(11) NULL,
    `error_message` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;