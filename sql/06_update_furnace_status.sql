-- Adım 6: Ocak Durumlarını Güncelle
-- Bu script ocakların durumlarını ve charging durumlarını günceller

USE steel_factory_db;

-- Mevcut durumları göster
SELECT 'Mevcut ocak durumları:' AS Bilgi;
SELECT 
    furnace_number AS 'Ocak No',
    status AS 'Durum',
    total_castings AS 'Toplam Döküm',
    is_charging AS 'Charging',
    CASE 
        WHEN is_charging THEN 'Aktif Döküm Var'
        ELSE 'Döküm Yok'
    END AS 'Açıklama'
FROM furnaces 
ORDER BY furnace_number;

-- Aktif ocakları ayarla (1, 3, 5)
UPDATE furnaces 
SET status = 'active', is_charging = TRUE 
WHERE furnace_number IN (1, 3, 5);

-- Standby ocakları ayarla (2, 4, 6)
UPDATE furnaces 
SET status = 'standby', is_charging = FALSE 
WHERE furnace_number IN (2, 4, 6);

-- Devam eden döküm olan ocakları charging yap
UPDATE furnaces f
SET is_charging = TRUE
WHERE EXISTS (
    SELECT 1 FROM castings c 
    WHERE c.furnace_id = f.id AND c.status = 'in_progress'
);

-- Devam eden döküm olmayan aktif ocakları charging=false yap
UPDATE furnaces f
SET is_charging = FALSE
WHERE f.status = 'active' 
AND NOT EXISTS (
    SELECT 1 FROM castings c 
    WHERE c.furnace_id = f.id AND c.status = 'in_progress'
);

-- Güncellenmiş durumları göster
SELECT 'Güncellenmiş ocak durumları:' AS Bilgi;
SELECT 
    furnace_number AS 'Ocak No',
    furnace_set AS 'Set',
    status AS 'Durum',
    total_castings AS 'Toplam Döküm',
    is_charging AS 'Charging',
    CASE 
        WHEN status = 'active' AND is_charging THEN '🔥 Aktif - Döküm Yapıyor'
        WHEN status = 'active' AND NOT is_charging THEN '⏳ Aktif - Döküm Bekliyor'
        WHEN status = 'standby' THEN '⏸️ Standby - Beklemede'
        WHEN status = 'maintenance' THEN '🔧 Bakımda'
        ELSE '❓ Bilinmiyor'
    END AS 'Durum Açıklaması'
FROM furnaces 
ORDER BY furnace_number;

SELECT 'Ocak durumları güncellendi!' AS Mesaj;
