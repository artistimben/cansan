-- Adım 3: Örnek Dökümler Ekle
-- Bu script örnek döküm verilerini ekler

USE steel_factory_db;

-- Bugünkü mevcut dökümler kontrol
SELECT 'Bugünkü mevcut döküm sayısı:' AS Bilgi, COUNT(*) AS Sayi 
FROM castings 
WHERE production_date = CURDATE();

-- Yeni döküm numarasını hesapla
SET @next_global_number = (SELECT COALESCE(MAX(global_casting_number), 0) + 1 FROM castings);

-- Tamamlanmış örnek dökümler ekle (eğer bugün hiç döküm yoksa)
INSERT INTO castings (furnace_id, casting_number_per_furnace, global_casting_number, start_time, end_time, production_date, status)
SELECT * FROM (
    SELECT 1, 1, @next_global_number, DATE_SUB(NOW(), INTERVAL 4 HOUR), DATE_SUB(NOW(), INTERVAL 2 HOUR), CURDATE(), 'completed'
    UNION ALL
    SELECT 3, 1, @next_global_number + 1, DATE_SUB(NOW(), INTERVAL 5 HOUR), DATE_SUB(NOW(), INTERVAL 3 HOUR), CURDATE(), 'completed'
    UNION ALL
    SELECT 5, 1, @next_global_number + 2, DATE_SUB(NOW(), INTERVAL 6 HOUR), DATE_SUB(NOW(), INTERVAL 4 HOUR), CURDATE(), 'completed'
) AS new_castings
WHERE NOT EXISTS (
    SELECT 1 FROM castings WHERE production_date = CURDATE()
);

-- Devam eden dökümler ekle (sadece aktif ocaklarda devam eden döküm yoksa)
INSERT INTO castings (furnace_id, casting_number_per_furnace, global_casting_number, start_time, end_time, production_date, status)
SELECT * FROM (
    SELECT 1, 2, @next_global_number + 3, DATE_SUB(NOW(), INTERVAL 45 MINUTE), NULL, CURDATE(), 'in_progress'
    UNION ALL
    SELECT 3, 2, @next_global_number + 4, DATE_SUB(NOW(), INTERVAL 60 MINUTE), NULL, CURDATE(), 'in_progress'
    UNION ALL
    SELECT 5, 2, @next_global_number + 5, DATE_SUB(NOW(), INTERVAL 30 MINUTE), NULL, CURDATE(), 'in_progress'
) AS new_progress_castings
WHERE NOT EXISTS (
    SELECT 1 FROM castings c 
    JOIN (SELECT 1 as fid UNION SELECT 3 UNION SELECT 5) f ON c.furnace_id = f.fid
    WHERE c.status = 'in_progress'
);

-- Ocakların toplam döküm sayılarını güncelle
UPDATE furnaces f 
SET total_castings = (
    SELECT COUNT(*) 
    FROM castings c 
    WHERE c.furnace_id = f.id AND c.status = 'completed'
);

-- Sonuç kontrol
SELECT 
    c.global_casting_number AS 'Genel No',
    f.furnace_number AS 'Ocak',
    c.casting_number_per_furnace AS 'Ocak Dökümü',
    DATE_FORMAT(c.start_time, '%H:%i') AS 'Başlangıç',
    CASE 
        WHEN c.end_time IS NULL THEN 'Devam Ediyor'
        ELSE DATE_FORMAT(c.end_time, '%H:%i')
    END AS 'Bitiş',
    CASE 
        WHEN c.end_time IS NULL THEN 'Devam Ediyor'
        ELSE CONCAT(TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time), ' dk')
    END AS 'Süre',
    c.status AS 'Durum'
FROM castings c
JOIN furnaces f ON c.furnace_id = f.id
WHERE c.production_date = CURDATE()
ORDER BY c.global_casting_number DESC;

SELECT 'Örnek döküm verileri eklendi!' AS Mesaj;
