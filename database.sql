-- Çelik Fabrikası Yönetim Sistemi Veritabanı
-- XAMPP/phpMyAdmin'de çalıştırın

CREATE DATABASE IF NOT EXISTS steel_factory_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE steel_factory_db;

-- Ocaklar tablosu
CREATE TABLE furnaces (
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
CREATE TABLE castings (
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
CREATE TABLE quality_controls (
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
CREATE TABLE daily_reports (
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

-- Başlangıç verileri: 6 ocak oluştur
INSERT INTO furnaces (furnace_number, furnace_set, status, total_castings, max_castings_before_maintenance, is_charging) VALUES
(1, 1, 'active', 2, 30, TRUE),
(2, 1, 'standby', 0, 30, FALSE),
(3, 2, 'active', 2, 30, TRUE),
(4, 2, 'standby', 0, 30, FALSE),
(5, 3, 'active', 2, 30, TRUE),
(6, 3, 'standby', 0, 30, FALSE);

-- İlk dökümler başlat (bazıları tamamlanmış, bazıları devam ediyor)
INSERT INTO castings (furnace_id, casting_number_per_furnace, global_casting_number, start_time, end_time, production_date, status) VALUES
-- Tamamlanmış dökümler (örnekleme için)
(1, 1, 1, DATE_SUB(NOW(), INTERVAL 3 HOUR), DATE_SUB(NOW(), INTERVAL 1 HOUR), CURDATE(), 'completed'),
(3, 1, 2, DATE_SUB(NOW(), INTERVAL 4 HOUR), DATE_SUB(NOW(), INTERVAL 2 HOUR), CURDATE(), 'completed'),
(5, 1, 3, DATE_SUB(NOW(), INTERVAL 5 HOUR), DATE_SUB(NOW(), INTERVAL 3 HOUR), CURDATE(), 'completed'),
-- Devam eden dökümler
(1, 2, 4, DATE_SUB(NOW(), INTERVAL 30 MINUTE), NULL, CURDATE(), 'in_progress'),
(3, 2, 5, DATE_SUB(NOW(), INTERVAL 45 MINUTE), NULL, CURDATE(), 'in_progress'),
(5, 2, 6, DATE_SUB(NOW(), INTERVAL 60 MINUTE), NULL, CURDATE(), 'in_progress');

-- Test kalite kontrol verileri
INSERT INTO quality_controls (casting_id, carbon_percentage, silicon_percentage, manganese_percentage, temperature, test_result, test_time, tested_by) VALUES
(1, 0.250, 0.180, 0.850, 1580.5, 'passed', NOW(), 'Kalite Kontrol Uzmanı'),
(2, 0.280, 0.200, 0.900, 1620.0, 'passed', NOW(), 'Kalite Kontrol Uzmanı'),
(3, 0.320, 0.220, 0.780, 1590.5, 'passed', NOW(), 'Kalite Kontrol Uzmanı');

-- Günlük Raporlar Tablosu
CREATE TABLE IF NOT EXISTS daily_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_date DATE NOT NULL UNIQUE,
    total_castings INT NOT NULL DEFAULT 0,
    completed_castings INT NOT NULL DEFAULT 0,
    delayed_castings INT NOT NULL DEFAULT 0,
    total_production_time INT NOT NULL DEFAULT 0,
    average_casting_time DECIMAL(10,2) DEFAULT 0,
    report_data JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_report_date (report_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ocak Bazında Performans Tablosu
CREATE TABLE IF NOT EXISTS report_furnace_performance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    furnace_id INT NOT NULL,
    furnace_number INT NOT NULL,
    total_castings INT NOT NULL DEFAULT 0,
    total_time INT NOT NULL DEFAULT 0,
    average_time DECIMAL(10,2) DEFAULT 0,
    delayed_count INT NOT NULL DEFAULT 0,
    FOREIGN KEY (report_id) REFERENCES daily_reports(id) ON DELETE CASCADE,
    FOREIGN KEY (furnace_id) REFERENCES furnaces(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
