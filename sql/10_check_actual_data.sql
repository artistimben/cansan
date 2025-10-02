-- Adım 10: Gerçek Veriyi Kontrol Et
-- Bu script veritabanındaki gerçek zaman verilerini gösterir

USE steel_factory_db;

SELECT '🔍 VERİTABANINDAKİ GERÇEK VERİLER' AS '═══════════════════════════════════════';

-- Tamamlanmış dökümlerin gerçek zaman verileri
SELECT 
    c.id AS 'ID',
    c.global_casting_number AS 'Genel No',
    f.furnace_number AS 'Ocak',
    c.start_time AS 'Başlangıç (Raw)',
    c.end_time AS 'Bitiş (Raw)',
    DATE_FORMAT(c.start_time, '%H:%i:%s') AS 'Başlangıç',
    DATE_FORMAT(c.end_time, '%H:%i:%s') AS 'Bitiş',
    TIMESTAMPDIFF(SECOND, c.start_time, c.end_time) AS 'Saniye Farkı',
    TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) AS 'Dakika Farkı',
    c.status AS 'Durum'
FROM castings c
JOIN furnaces f ON c.furnace_id = f.id
WHERE c.status = 'completed' 
  AND c.production_date = CURDATE()
ORDER BY c.global_casting_number DESC
LIMIT 10;

-- Devam eden dökümlerin zaman verileri
SELECT '📊 DEVAM EDEN DÖKÜMLER' AS '─────────────────────────────────────';
SELECT 
    c.id AS 'ID',
    c.global_casting_number AS 'Genel No',
    f.furnace_number AS 'Ocak',
    c.start_time AS 'Başlangıç (Raw)',
    DATE_FORMAT(c.start_time, '%H:%i:%s') AS 'Başlangıç',
    TIMESTAMPDIFF(MINUTE, c.start_time, NOW()) AS 'Şu Ana Kadar Geçen (dk)',
    c.status AS 'Durum'
FROM castings c
JOIN furnaces f ON c.furnace_id = f.id
WHERE c.status = 'in_progress'
ORDER BY c.global_casting_number DESC;

SELECT 'Veri kontrolü tamamlandı!' AS Mesaj;
