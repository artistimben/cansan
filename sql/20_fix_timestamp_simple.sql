-- Adım 20: TIMESTAMP ON UPDATE Basit Çözüm
-- Bu script yetki sorunu olmadan çalışır

USE steel_factory_db;

-- Mevcut tablo yapısını göster
SELECT '🔍 MEVCUT TABLO YAPISI' AS Bilgi;
SHOW CREATE TABLE castings;

-- start_time kolonunu düzelt - ON UPDATE kaldır
SELECT '🔧 START_TIME DÜZELTME BAŞLIYOR...' AS Bilgi;

ALTER TABLE castings 
MODIFY COLUMN start_time TIMESTAMP NOT NULL;

SELECT '✅ Start_time düzeltildi!' AS Bilgi;

-- end_time kolonunu düzelt
ALTER TABLE castings 
MODIFY COLUMN end_time TIMESTAMP NULL DEFAULT NULL;

SELECT '✅ End_time düzeltildi!' AS Bilgi;

-- created_at ve updated_at düzelt (sadece updated_at ON UPDATE olmalı)
ALTER TABLE castings 
MODIFY COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE castings 
MODIFY COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

SELECT '✅ Tüm TIMESTAMP kolonları düzeltildi!' AS Bilgi;

-- Düzeltme sonrası tablo yapısı
SELECT '📊 DÜZELTME SONRASI TABLO YAPISI' AS Bilgi;
SHOW CREATE TABLE castings;

-- Basit test
SELECT '🧪 TEST BAŞLIYOR...' AS Bilgi;

SET @test_id = (SELECT id FROM castings WHERE status = 'in_progress' AND production_date = CURDATE() LIMIT 1);
SET @old_start = (SELECT start_time FROM castings WHERE id = @test_id);

-- Bir şeyler güncelle
UPDATE castings SET notes = 'Test' WHERE id = @test_id;

-- Kontrol
SELECT 
    CASE 
        WHEN (SELECT start_time FROM castings WHERE id = @test_id) = @old_start 
        THEN '✅ BAŞARILI: Start_time artık değişmiyor!'
        ELSE '❌ HALA SORUN VAR: Start_time hala değişiyor'
    END AS 'Test Sonucu';

-- Temizlik
UPDATE castings SET notes = NULL WHERE id = @test_id;

SELECT '🎉 Tamamlandı! Şimdi web sayfasında döküm tamamlama testini yapabilirsiniz.' AS Mesaj;
