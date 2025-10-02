-- ⚡ HEMEN ÇÖZ - START_TIME SORUNU
-- Bu scripti doğrudan phpMyAdmin'de çalıştırın

USE steel_factory_db;

-- START_TIME kolonunu düzelt (ON UPDATE otomatik güncellemeyi kaldır)
ALTER TABLE castings MODIFY COLUMN start_time TIMESTAMP NOT NULL;

-- END_TIME kolonunu düzelt  
ALTER TABLE castings MODIFY COLUMN end_time TIMESTAMP NULL DEFAULT NULL;

-- CREATED_AT kolonunu düzelt
ALTER TABLE castings MODIFY COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- UPDATED_AT kolonunu düzelt (sadece bu ON UPDATE olmalı)
ALTER TABLE castings MODIFY COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Sonuç mesajı
SELECT '✅ SORUN ÇÖZÜLDÜ!' AS Mesaj;
SELECT 'Artık start_time otomatik güncellenMEYECEK.' AS Bilgi;
SELECT 'Web sayfasını yenileyin ve döküm tamamlama testini yapın.' AS Talimat;
