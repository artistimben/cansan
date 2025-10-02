-- Adım 2: Ocakları Kur
-- Bu script ocakları oluşturur (eğer yoksa)

USE steel_factory_db;

-- Mevcut ocakları kontrol et
SELECT 'Mevcut ocak sayısı:' AS Bilgi, COUNT(*) AS Sayi FROM furnaces;

-- Eğer ocak yoksa oluştur
INSERT IGNORE INTO furnaces (furnace_number, furnace_set, status, total_castings, max_castings_before_maintenance, is_charging) VALUES
(1, 1, 'active', 0, 30, TRUE),
(2, 1, 'standby', 0, 30, FALSE),
(3, 2, 'active', 0, 30, TRUE),
(4, 2, 'standby', 0, 30, FALSE),
(5, 3, 'active', 0, 30, TRUE),
(6, 3, 'standby', 0, 30, FALSE);

-- Sonuç kontrol
SELECT 
    furnace_number AS 'Ocak No',
    furnace_set AS 'Set',
    status AS 'Durum',
    total_castings AS 'Toplam Döküm',
    is_charging AS 'Charging'
FROM furnaces 
ORDER BY furnace_number;

SELECT 'Ocak kurulumu tamamlandı!' AS Mesaj;
