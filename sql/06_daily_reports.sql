-- Günlük Raporlar Tablosu
CREATE TABLE IF NOT EXISTS daily_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_date DATE NOT NULL UNIQUE,
    total_castings INT NOT NULL DEFAULT 0,
    completed_castings INT NOT NULL DEFAULT 0,
    delayed_castings INT NOT NULL DEFAULT 0,
    total_production_time INT NOT NULL DEFAULT 0, -- dakika cinsinden
    average_casting_time DECIMAL(10,2) DEFAULT 0,
    report_data JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_report_date (report_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rapor detayları için genişletme (opsiyonel, JSON'da da tutabiliriz)
CREATE TABLE IF NOT EXISTS report_furnace_performance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    furnace_id INT NOT NULL,
    furnace_number INT NOT NULL,
    total_castings INT NOT NULL DEFAULT 0,
    total_time INT NOT NULL DEFAULT 0, -- dakika
    average_time DECIMAL(10,2) DEFAULT 0,
    delayed_count INT NOT NULL DEFAULT 0,
    FOREIGN KEY (report_id) REFERENCES daily_reports(id) ON DELETE CASCADE,
    FOREIGN KEY (furnace_id) REFERENCES furnaces(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

