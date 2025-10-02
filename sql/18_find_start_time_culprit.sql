-- Adım 18: Start Time Değişiminin Gerçek Nedenini Bul
-- Bu script sorunu adım adım tespit eder

USE steel_factory_db;

-- Veritabanı tetikleyicilerini kontrol et
SELECT '🔍 VERİTABANI TETİKLEYİCİLERİ KONTROLÜ' AS '═══════════════════════════════════════';

SHOW TRIGGERS LIKE 'castings';

-- Tablo yapısını kontrol et  
SELECT '📊 CASTINGS TABLOSU YAPISI' AS '─────────────────────────────────────';

DESCRIBE castings;

-- On update davranışını kontrol et
SELECT '🔧 ON UPDATE AYARLARI' AS '─────────────────────────────────────';

SELECT 
    COLUMN_NAME AS 'Kolon',
    COLUMN_TYPE AS 'Tip',
    IS_NULLABLE AS 'Null',
    COLUMN_DEFAULT AS 'Varsayılan',
    EXTRA AS 'Ekstra'
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'steel_factory_db' 
  AND TABLE_NAME = 'castings'
  AND (COLUMN_NAME LIKE '%time%' OR EXTRA LIKE '%update%');

-- Test: Basit bir güncelleme yap ve ne oluyor gözlemle
SELECT '🧪 TEST: BASIT BİR GÜNCELLEME' AS '─────────────────────────────────────';

-- Önce mevcut bir kayıt seç
SELECT 
    id AS 'Test Döküm ID',
    DATE_FORMAT(start_time, '%Y-%m-%d %H:%i:%s.%f') AS 'Eski Start Time',
    DATE_FORMAT(updated_at, '%Y-%m-%d %H:%i:%s.%f') AS 'Eski Updated At'
FROM castings 
WHERE status = 'in_progress' 
  AND production_date = CURDATE()
LIMIT 1;

-- Şimdi sadece bir değişiklik yapmadan UPDATE çalıştır
SET @test_id = (SELECT id FROM castings WHERE status = 'in_progress' AND production_date = CURDATE() LIMIT 1);
SET @old_start_time = (SELECT start_time FROM castings WHERE id = @test_id);

-- Hiçbir şey değiştirmeden UPDATE
UPDATE castings SET status = 'in_progress' WHERE id = @test_id;

-- Sonucu kontrol et
SELECT 
    id AS 'Test Döküm ID',
    DATE_FORMAT(start_time, '%Y-%m-%d %H:%i:%s.%f') AS 'Yeni Start Time',
    DATE_FORMAT(updated_at, '%Y-%m-%d %H:%i:%s.%f') AS 'Yeni Updated At',
    CASE 
        WHEN start_time = @old_start_time THEN '✅ DEĞİŞMEDİ'
        ELSE '❌ DEĞİŞTİ!'
    END AS 'Start Time Durumu'
FROM castings 
WHERE id = @test_id;

-- Tablo oluşturma komutunu göster
SELECT '📋 TABLO YARATMA KOMUTU' AS '─────────────────────────────────────';

SHOW CREATE TABLE castings;

SELECT 'Sorun tespiti tamamlandı!' AS Mesaj;
