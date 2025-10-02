-- Adım 8: Döküm Zamanlarını Düzelt
-- Bu script devam eden dökümlerin başlangıç zamanlarını gerçekçi hale getirir

USE steel_factory_db;

-- Mevcut devam eden dökümleri göster
SELECT 'Mevcut devam eden dökümler (düzeltilmeden önce):' AS Bilgi;
SELECT 
    c.id AS 'Döküm ID',
    f.furnace_number AS 'Ocak',
    c.global_casting_number AS 'Genel No',
    DATE_FORMAT(c.start_time, '%H:%i:%s') AS 'Başlangıç',
    CASE 
        WHEN c.end_time IS NULL THEN 'Devam Ediyor'
        ELSE DATE_FORMAT(c.end_time, '%H:%i:%s')
    END AS 'Bitiş',
    TIMESTAMPDIFF(MINUTE, c.start_time, COALESCE(c.end_time, NOW())) AS 'Geçen Süre (dk)'
FROM castings c
JOIN furnaces f ON c.furnace_id = f.id
WHERE c.status = 'in_progress'
ORDER BY c.global_casting_number;

-- Devam eden dökümlerin başlangıç zamanlarını gerçekçi hale getir
-- Her döküm farklı zamanda başlamış gibi ayarla

UPDATE castings 
SET start_time = DATE_SUB(NOW(), INTERVAL 45 MINUTE)
WHERE status = 'in_progress' 
  AND furnace_id = (SELECT id FROM furnaces WHERE furnace_number = 1 LIMIT 1);

UPDATE castings 
SET start_time = DATE_SUB(NOW(), INTERVAL 75 MINUTE)
WHERE status = 'in_progress' 
  AND furnace_id = (SELECT id FROM furnaces WHERE furnace_number = 3 LIMIT 1);

UPDATE castings 
SET start_time = DATE_SUB(NOW(), INTERVAL 30 MINUTE)
WHERE status = 'in_progress' 
  AND furnace_id = (SELECT id FROM furnaces WHERE furnace_number = 5 LIMIT 1);

-- Düzeltilmiş dökümleri göster
SELECT 'Düzeltilmiş devam eden dökümler:' AS Bilgi;
SELECT 
    c.id AS 'Döküm ID',
    f.furnace_number AS 'Ocak',
    c.global_casting_number AS 'Genel No',
    DATE_FORMAT(c.start_time, '%H:%i:%s') AS 'Başlangıç',
    DATE_FORMAT(DATE_ADD(c.start_time, INTERVAL 120 MINUTE), '%H:%i:%s') AS 'Tahmini Bitiş',
    TIMESTAMPDIFF(MINUTE, c.start_time, NOW()) AS 'Geçen Süre (dk)',
    GREATEST(0, 120 - TIMESTAMPDIFF(MINUTE, c.start_time, NOW())) AS 'Kalan Süre (dk)',
    ROUND(
        LEAST(100, (TIMESTAMPDIFF(MINUTE, c.start_time, NOW()) / 120) * 100), 1
    ) AS 'İlerleme %'
FROM castings c
JOIN furnaces f ON c.furnace_id = f.id
WHERE c.status = 'in_progress'
ORDER BY c.global_casting_number;

-- Test için bir döküm tamamlama simülasyonu
SELECT 'Test: Döküm tamamlama simülasyonu' AS Test;
SELECT 
    'Eğer şimdi 1. ocağın dökümünü tamamlarsanız:' AS Senaryo,
    CONCAT(
        'Başlangıç: ', 
        DATE_FORMAT((SELECT start_time FROM castings WHERE status = 'in_progress' AND furnace_id = 1 LIMIT 1), '%H:%i'),
        ' - Bitiş: ',
        DATE_FORMAT(NOW(), '%H:%i'),
        ' - Süre: ',
        TIMESTAMPDIFF(MINUTE, (SELECT start_time FROM castings WHERE status = 'in_progress' AND furnace_id = 1 LIMIT 1), NOW()),
        ' dakika'
    ) AS 'Sonuç';

SELECT 'Döküm zamanları düzeltildi! Artık gerçekçi süreler gösterecek.' AS Mesaj;
