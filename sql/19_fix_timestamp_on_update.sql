-- Adım 19: TIMESTAMP ON UPDATE Sorununu Çöz
-- Bu script start_time kolonundaki ON UPDATE sorununu düzeltir

USE steel_factory_db;

-- Mevcut tablo yapısını kontrol et
SELECT '🔍 CASTINGS TABLOSU MEVCUT YAPI' AS '═══════════════════════════════════════';

SHOW CREATE TABLE castings;

-- Kolonların detaylı bilgisi
SELECT '📊 TIMESTAMP KOLONLARI DETAYLI' AS '─────────────────────────────────────';

SELECT 
    COLUMN_NAME AS 'Kolon Adı',
    COLUMN_TYPE AS 'Tip',
    IS_NULLABLE AS 'Null?',
    COLUMN_DEFAULT AS 'Varsayılan',
    EXTRA AS 'Ekstra Özellikler',
    COLUMN_KEY AS 'Key'
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'steel_factory_db' 
  AND TABLE_NAME = 'castings'
  AND COLUMN_NAME IN ('start_time', 'end_time', 'created_at', 'updated_at')
ORDER BY ORDINAL_POSITION;

-- start_time kolonunu düzelt - ON UPDATE kaldır
SELECT '🔧 START_TIME KOLONUNU DÜZELT' AS '─────────────────────────────────────';

ALTER TABLE castings 
MODIFY COLUMN start_time TIMESTAMP NOT NULL;

-- end_time kolonunu düzelt
ALTER TABLE castings 
MODIFY COLUMN end_time TIMESTAMP NULL DEFAULT NULL;

-- Düzeltme sonrası kontrol
SELECT '✅ DÜZELTME SONRASI KONTROL' AS '─────────────────────────────────────';

SELECT 
    COLUMN_NAME AS 'Kolon Adı',
    COLUMN_TYPE AS 'Tip',
    COLUMN_DEFAULT AS 'Varsayılan',
    EXTRA AS 'Ekstra',
    CASE 
        WHEN EXTRA LIKE '%on update%' THEN '❌ ON UPDATE VAR'
        ELSE '✅ ON UPDATE YOK'
    END AS 'Durum'
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'steel_factory_db' 
  AND TABLE_NAME = 'castings'
  AND COLUMN_NAME IN ('start_time', 'end_time', 'created_at', 'updated_at')
ORDER BY ORDINAL_POSITION;

-- Test: Bir kayıt güncelle ve start_time değişiyor mu kontrol et
SELECT '🧪 TEST: START_TIME DEĞİŞİYOR MU?' AS '─────────────────────────────────────';

SET @test_id = (SELECT id FROM castings WHERE status = 'in_progress' AND production_date = CURDATE() LIMIT 1);
SET @old_start_time = (SELECT start_time FROM castings WHERE id = @test_id);

-- Test güncelleme
UPDATE castings SET notes = 'Test güncelleme' WHERE id = @test_id;

-- Kontrol
SELECT 
    'Start Time Testi' AS 'Test',
    CASE 
        WHEN (SELECT start_time FROM castings WHERE id = @test_id) = @old_start_time THEN '✅ DEĞİŞMEDİ (Sorun çözüldü!)'
        ELSE '❌ HALA DEĞİŞİYOR'
    END AS 'Sonuç';

-- Geri al
UPDATE castings SET notes = NULL WHERE id = @test_id;

SELECT '🎯 Düzeltme tamamlandı! Şimdi döküm tamamlama testini yapın.' AS Mesaj;
