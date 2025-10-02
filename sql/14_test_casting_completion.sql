-- Adım 14: Döküm Tamamlama Test Scripti
-- Bu script döküm tamamlama işlemini test eder

USE steel_factory_db;

-- Test öncesi durum
SELECT '🔍 TEST ÖNCESİ DURUM' AS '═══════════════════════════════════════';

SELECT 
    c.id AS 'Döküm ID',
    c.global_casting_number AS 'Genel No',
    f.furnace_number AS 'Ocak',
    c.casting_number_per_furnace AS 'Ocak Dökümü',
    c.status AS 'Durum',
    DATE_FORMAT(c.start_time, '%H:%i:%s') AS 'Başlangıç',
    DATE_FORMAT(c.end_time, '%H:%i:%s') AS 'Bitiş',
    CASE 
        WHEN c.status = 'in_progress' THEN TIMESTAMPDIFF(MINUTE, c.start_time, NOW())
        WHEN c.status = 'completed' AND c.end_time IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time)
        ELSE NULL
    END AS 'Süre (dk)'
FROM castings c
JOIN furnaces f ON c.furnace_id = f.id
WHERE c.production_date = CURDATE()
ORDER BY 
    CASE c.status 
        WHEN 'in_progress' THEN 1 
        WHEN 'completed' THEN 2 
        ELSE 3 
    END,
    c.global_casting_number DESC;

-- Test için manuel döküm tamamlama simülasyonu
SELECT '🧪 MANUEL DÖKÜM TAMAMLAMA SİMÜLASYONU' AS '─────────────────────────────────────';

-- En eski devam eden döküm için simülasyon
SELECT 
    c.id AS 'Test Edilecek Döküm ID',
    f.furnace_number AS 'Ocak',
    c.global_casting_number AS 'Genel No',
    DATE_FORMAT(c.start_time, '%H:%i:%s') AS 'Mevcut Başlangıç',
    DATE_FORMAT(NOW(), '%H:%i:%s') AS 'Tamamlanma Zamanı (Şimdi)',
    TIMESTAMPDIFF(MINUTE, c.start_time, NOW()) AS 'Hesaplanacak Süre (dk)',
    CASE 
        WHEN TIMESTAMPDIFF(MINUTE, c.start_time, NOW()) > 120 
        THEN CONCAT('Gecikme: +', TIMESTAMPDIFF(MINUTE, c.start_time, NOW()) - 120, ' dk')
        ELSE 'Normal süre'
    END AS 'Gecikme Durumu'
FROM castings c
JOIN furnaces f ON c.furnace_id = f.id
WHERE c.status = 'in_progress' 
  AND c.production_date = CURDATE()
ORDER BY c.start_time ASC
LIMIT 1;

-- Sistem durumu kontrol
SELECT '📊 SİSTEM DURUMU KONTROL' AS '─────────────────────────────────────';

SELECT 
    'Devam Eden Döküm' AS 'Metrik',
    COUNT(*) AS 'Adet'
FROM castings 
WHERE status = 'in_progress' AND production_date = CURDATE()

UNION ALL

SELECT 
    'Tamamlanan Döküm' AS 'Metrik',
    COUNT(*) AS 'Adet'
FROM castings 
WHERE status = 'completed' AND production_date = CURDATE()

UNION ALL

SELECT 
    'Sorunlu Tamamlanan (Süre <= 0)' AS 'Metrik',
    COUNT(*) AS 'Adet'
FROM castings 
WHERE status = 'completed' 
  AND production_date = CURDATE()
  AND (end_time IS NULL OR TIMESTAMPDIFF(MINUTE, start_time, end_time) <= 0)

UNION ALL

SELECT 
    'Normal Tamamlanan (Süre > 0)' AS 'Metrik',
    COUNT(*) AS 'Adet'
FROM castings 
WHERE status = 'completed' 
  AND production_date = CURDATE()
  AND end_time IS NOT NULL 
  AND TIMESTAMPDIFF(MINUTE, start_time, end_time) > 0;

-- Test talimatları
SELECT '📋 TEST TALİMATLARI' AS '─────────────────────────────────────';

SELECT 
    '1. Bu scripti çalıştırın ve mevcut durumu görün' AS 'Adım 1',
    '2. Web sayfasında bir döküm tamamlayın' AS 'Adım 2', 
    '3. Bu scripti tekrar çalıştırın ve değişiklikleri kontrol edin' AS 'Adım 3',
    '4. Yeni döküm başladığını ve eski dökümün süresinin doğru olduğunu kontrol edin' AS 'Adım 4';

-- Beklenen sonuç
SELECT '✅ BEKLENEN SONUÇ' AS '─────────────────────────────────────';

SELECT 
    'Döküm tamamlandıktan sonra:' AS 'Beklenti',
    '- Eski dökümün start_time değişmemeli' AS 'Kontrol 1',
    '- Eski dökümün end_time şimdiki zaman olmalı' AS 'Kontrol 2', 
    '- Süre hesaplaması pozitif olmalı' AS 'Kontrol 3',
    '- Yeni döküm başlamalı' AS 'Kontrol 4',
    '- Yeni dökümün start_time şimdiki zaman olmalı' AS 'Kontrol 5';

SELECT 'Test scripti hazır! Döküm tamamlama işlemini test edebilirsiniz.' AS Mesaj;
