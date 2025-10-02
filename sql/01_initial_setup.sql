-- Adım 1: İlk Kurulum
-- Bu script sadece veritabanı ve tablolar yoksa çalıştırılır

-- Veritabanı oluştur (eğer yoksa)
CREATE DATABASE IF NOT EXISTS steel_factory_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE steel_factory_db;

-- Ocaklar tablosu
CREATE TABLE IF NOT EXISTS furnaces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    furnace_number INT NOT NULL UNIQUE,
    furnace_set INT NOT NULL,
    status ENUM('active', 'maintenance', 'standby') DEFAULT 'standby',
    total_castings INT DEFAULT 0,
    max_castings_before_maintenance INT DEFAULT 30,
    last_maintenance_date TIMESTAMP NULL,
    next_maintenance_due TIMESTAMP NULL,
    is_charging BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Dökümler tablosu
CREATE TABLE IF NOT EXISTS castings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    furnace_id INT NOT NULL,
    casting_number_per_furnace INT NOT NULL,
    global_casting_number INT NOT NULL,
    start_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    end_time TIMESTAMP NULL,
    duration_minutes INT DEFAULT 120,
    status ENUM('in_progress', 'completed', 'cancelled') DEFAULT 'in_progress',
    production_date DATE NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (furnace_id) REFERENCES furnaces(id) ON DELETE CASCADE,
    INDEX idx_furnace_casting (furnace_id, casting_number_per_furnace),
    INDEX idx_production_date (production_date),
    INDEX idx_global_casting (global_casting_number)
) ENGINE=InnoDB;

-- Kalite kontrol tablosu
CREATE TABLE IF NOT EXISTS quality_controls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    casting_id INT NOT NULL,
    carbon_percentage DECIMAL(5,3) NULL,
    silicon_percentage DECIMAL(5,3) NULL,
    manganese_percentage DECIMAL(5,3) NULL,
    phosphorus_percentage DECIMAL(5,3) NULL,
    sulfur_percentage DECIMAL(5,3) NULL,
    chromium_percentage DECIMAL(5,3) NULL,
    nickel_percentage DECIMAL(5,3) NULL,
    copper_percentage DECIMAL(5,3) NULL,
    temperature DECIMAL(6,2) NULL,
    test_result ENUM('passed', 'failed', 'pending') DEFAULT 'pending',
    test_time TIMESTAMP NULL,
    tested_by VARCHAR(100) NULL,
    remarks TEXT NULL,
    prova_data JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (casting_id) REFERENCES castings(id) ON DELETE CASCADE,
    INDEX idx_casting (casting_id),
    INDEX idx_test_result (test_result)
) ENGINE=InnoDB;

-- Günlük raporlar tablosu
CREATE TABLE IF NOT EXISTS daily_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_date DATE NOT NULL UNIQUE,
    total_castings INT NOT NULL,
    furnace_castings JSON NULL,
    active_furnaces_count INT NOT NULL,
    maintenance_activities JSON NULL,
    production_efficiency DECIMAL(5,2) NULL,
    shift_start_time TIME DEFAULT '08:00:00',
    shift_end_time TIME DEFAULT '08:00:00',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

SELECT 'İlk kurulum tamamlandı!' AS Mesaj;
