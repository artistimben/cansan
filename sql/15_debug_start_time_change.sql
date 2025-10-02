-- Adım 15: Start Time Değişimi Debug
-- Bu script start_time değişim sorununu tespit eder

USE steel_factory_db;

-- Devam eden dökümlerin şu anki durumu
SELECT '🔍 DEVAM EDEN DÖKÜMLERİN ŞU ANKİ DURUMU' AS '═══════════════════════════════════════';

SELECT 
    c.id AS 'Döküm ID',
    c.global_casting_number AS 'Genel No',
    f.furnace_number AS 'Ocak',
    c.status AS 'Durum',
    c.start_time AS 'Start Time (Raw)',
    DATE_FORMAT(c.start_time, '%Y-%m-%d %H:%i:%s') AS 'Başlangıç (Formatted)',
    DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s') AS 'Şimdiki Zaman',
    TIMESTAMPDIFF(MINUTE, c.start_time, NOW()) AS 'Geçen Süre (dk)',
    CASE 
        WHEN ABS(TIMESTAMPDIFF(MINUTE, c.start_time, NOW())) < 5 THEN '⚠️ ÇOK YENİ (Şüpheli)'
        WHEN TIMESTAMPDIFF(MINUTE, c.start_time, NOW()) > 30 THEN '✅ NORMAL'
        ELSE '❓ KONTROL ET'
    END AS 'Analiz'
FROM castings c
JOIN furnaces f ON c.furnace_id = f.id
WHERE c.status = 'in_progress' 
  AND c.production_date = CURDATE()
ORDER BY c.global_casting_number DESC;

-- Son tamamlanan dökümlerin start_time analizi
SELECT '📊 SON TAMAMLANAN DÖKÜMLERİN START_TIME ANALİZİ' AS '─────────────────────────────────────';

SELECT 
    c.id AS 'Döküm ID',
    c.global_casting_number AS 'Genel No',
    f.furnace_number AS 'Ocak',
    c.status AS 'Durum',
    DATE_FORMAT(c.start_time, '%H:%i:%s') AS 'Başlangıç',
    DATE_FORMAT(c.end_time, '%H:%i:%s') AS 'Bitiş',
    TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) AS 'Süre (dk)',
    CASE 
        WHEN ABS(TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time)) < 5 THEN '❌ SORUNLU (Çok yakın zamanlar)'
        WHEN TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) BETWEEN 90 AND 180 THEN '✅ NORMAL'
        WHEN TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) <= 0 THEN '❌ SORUNLU (Negatif/Sıfır)'
        ELSE '⚠️ KONTROL ET'
    END AS 'Durum Analizi',
    c.created_at AS 'Oluşturma Zamanı',
    c.updated_at AS 'Güncellenme Zamanı'
FROM castings c
JOIN furnaces f ON c.furnace_id = f.id
WHERE c.status = 'completed' 
  AND c.production_date = CURDATE()
ORDER BY c.global_casting_number DESC
LIMIT 5;

-- Şüpheli kayıtları tespit et
SELECT '🚨 ŞÜPHELİ KAYITLARI TESPİT ET' AS '─────────────────────────────────────';

-- Start time ve end time çok yakın olan kayıtlar
SELECT 
    'Start-End zamanları çok yakın' AS 'Sorun Tipi',
    COUNT(*) AS 'Adet'
FROM castings 
WHERE status = 'completed' 
  AND production_date = CURDATE()
  AND ABS(TIMESTAMPDIFF(MINUTE, start_time, end_time)) < 5

UNION ALL

-- Start time çok yeni olan devam eden dökümler
SELECT 
    'Devam eden ama çok yeni start_time' AS 'Sorun Tipi',
    COUNT(*) AS 'Adet'
FROM castings 
WHERE status = 'in_progress' 
  AND production_date = CURDATE()
  AND TIMESTAMPDIFF(MINUTE, start_time, NOW()) < 5

UNION ALL

-- Updated_at zamanı son 10 dakikada olan tamamlanan dökümler
SELECT 
    'Son 10 dakikada güncellenen tamamlanan' AS 'Sorun Tipi',
    COUNT(*) AS 'Adet'
FROM castings 
WHERE status = 'completed' 
  AND production_date = CURDATE()
  AND TIMESTAMPDIFF(MINUTE, updated_at, NOW()) < 10;

-- Detaylı zaman analizi
SELECT '🕐 DETAYLI ZAMAN ANALİZİ' AS '─────────────────────────────────────';

SELECT 
    c.id AS 'ID',
    c.global_casting_number AS 'No',
    f.furnace_number AS 'Ocak',
    c.status AS 'Durum',
    DATE_FORMAT(c.start_time, '%H:%i:%s') AS 'Start',
    DATE_FORMAT(c.end_time, '%H:%i:%s') AS 'End',
    DATE_FORMAT(c.created_at, '%H:%i:%s') AS 'Created',
    DATE_FORMAT(c.updated_at, '%H:%i:%s') AS 'Updated',
    CASE 
        WHEN c.status = 'completed' AND DATE_FORMAT(c.start_time, '%H:%i:%s') = DATE_FORMAT(c.end_time, '%H:%i:%s') 
        THEN '❌ START=END'
        WHEN c.status = 'completed' AND DATE_FORMAT(c.start_time, '%H:%i:%s') = DATE_FORMAT(c.updated_at, '%H:%i:%s') 
        THEN '❌ START=UPDATED'
        WHEN c.status = 'in_progress' AND DATE_FORMAT(c.start_time, '%H:%i:%s') = DATE_FORMAT(c.updated_at, '%H:%i:%s') 
        THEN '⚠️ START=UPDATED (Devam eden)'
        ELSE '✅ Normal'
    END AS 'Problem Tespiti'
FROM castings c
JOIN furnaces f ON c.furnace_id = f.id
WHERE c.production_date = CURDATE()
ORDER BY c.updated_at DESC
LIMIT 10;

SELECT 'Debug analizi tamamlandı. Sorunlu kayıtları tespit ettik.' AS Mesaj;
