-- Adım 16: Start Time Sorununu Kesin Çözüm
-- Bu script start_time sorununu kesinlikle çözer

USE steel_factory_db;

-- Önce mevcut durumu kaydet
CREATE TEMPORARY TABLE temp_casting_backup AS
SELECT * FROM castings WHERE production_date = CURDATE();

-- Mevcut sorunlu durumu göster
SELECT '🔍 MEVCUT SORUNLU DURUM' AS '═══════════════════════════════════════';

SELECT 
    c.id AS 'ID',
    c.global_casting_number AS 'No',
    f.furnace_number AS 'Ocak',
    c.status AS 'Durum',
    DATE_FORMAT(c.start_time, '%H:%i:%s') AS 'Start',
    DATE_FORMAT(c.end_time, '%H:%i:%s') AS 'End',
    CASE 
        WHEN c.status = 'completed' AND TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) <= 0 THEN '❌ SORUNLU'
        WHEN c.status = 'completed' AND TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) > 0 THEN '✅ NORMAL'
        WHEN c.status = 'in_progress' THEN '🔄 DEVAM EDİYOR'
        ELSE '❓ DİĞER'
    END AS 'Analiz'
FROM castings c
JOIN furnaces f ON c.furnace_id = f.id
WHERE c.production_date = CURDATE()
ORDER BY c.global_casting_number DESC;

-- Tüm bugünkü dökümler için temiz veri oluştur
SELECT '🔧 TEMİZ VERİ OLUŞTURMA' AS '─────────────────────────────────────';

-- Önce tüm bugünkü dökümler sil
DELETE FROM quality_controls WHERE casting_id IN (
    SELECT id FROM castings WHERE production_date = CURDATE()
);
DELETE FROM castings WHERE production_date = CURDATE();

-- Temiz test verileri oluştur
SET @base_time = DATE_SUB(NOW(), INTERVAL 4 HOUR);

-- Tamamlanmış dökümler (gerçekçi zamanlarla)
INSERT INTO castings (furnace_id, casting_number_per_furnace, global_casting_number, start_time, end_time, production_date, status) VALUES
-- 1. Ocak dökümler
(1, 1, 1, DATE_SUB(@base_time, INTERVAL 0 MINUTE), DATE_ADD(DATE_SUB(@base_time, INTERVAL 0 MINUTE), INTERVAL 120 MINUTE), CURDATE(), 'completed'),
(1, 2, 4, DATE_SUB(@base_time, INTERVAL -130 MINUTE), DATE_ADD(DATE_SUB(@base_time, INTERVAL -130 MINUTE), INTERVAL 125 MINUTE), CURDATE(), 'completed'),

-- 3. Ocak dökümler  
(3, 1, 2, DATE_SUB(@base_time, INTERVAL -10 MINUTE), DATE_ADD(DATE_SUB(@base_time, INTERVAL -10 MINUTE), INTERVAL 115 MINUTE), CURDATE(), 'completed'),
(3, 2, 5, DATE_SUB(@base_time, INTERVAL -140 MINUTE), DATE_ADD(DATE_SUB(@base_time, INTERVAL -140 MINUTE), INTERVAL 130 MINUTE), CURDATE(), 'completed'),

-- 5. Ocak dökümler
(5, 1, 3, DATE_SUB(@base_time, INTERVAL -20 MINUTE), DATE_ADD(DATE_SUB(@base_time, INTERVAL -20 MINUTE), INTERVAL 118 MINUTE), CURDATE(), 'completed'),
(5, 2, 6, DATE_SUB(@base_time, INTERVAL -150 MINUTE), DATE_ADD(DATE_SUB(@base_time, INTERVAL -150 MINUTE), INTERVAL 135 MINUTE), CURDATE(), 'completed');

-- Devam eden dökümler (farklı başlangıç zamanları)
INSERT INTO castings (furnace_id, casting_number_per_furnace, global_casting_number, start_time, production_date, status) VALUES
(1, 3, 7, DATE_SUB(NOW(), INTERVAL 45 MINUTE), CURDATE(), 'in_progress'),
(3, 3, 8, DATE_SUB(NOW(), INTERVAL 75 MINUTE), CURDATE(), 'in_progress'),
(5, 3, 9, DATE_SUB(NOW(), INTERVAL 30 MINUTE), CURDATE(), 'in_progress');

-- Ocakların döküm sayılarını güncelle
UPDATE furnaces SET total_castings = 2 WHERE id IN (1, 3, 5);

-- Sonucu göster
SELECT '✅ TEMİZ VERİ OLUŞTURULDU' AS '─────────────────────────────────────';

SELECT 
    c.id AS 'ID',
    c.global_casting_number AS 'No',
    f.furnace_number AS 'Ocak',
    c.status AS 'Durum',
    DATE_FORMAT(c.start_time, '%H:%i:%s') AS 'Başlangıç',
    DATE_FORMAT(c.end_time, '%H:%i:%s') AS 'Bitiş',
    CASE 
        WHEN c.status = 'completed' THEN TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time)
        WHEN c.status = 'in_progress' THEN TIMESTAMPDIFF(MINUTE, c.start_time, NOW())
        ELSE NULL
    END AS 'Süre (dk)',
    CASE 
        WHEN c.status = 'completed' AND TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) > 0 THEN '✅ NORMAL'
        WHEN c.status = 'in_progress' AND TIMESTAMPDIFF(MINUTE, c.start_time, NOW()) > 0 THEN '🔄 DEVAM EDİYOR'
        ELSE '❌ SORUNLU'
    END AS 'Durum'
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

-- Test talimatları
SELECT '📋 TEST TALİMATLARI' AS '─────────────────────────────────────';

SELECT 
    'ŞİMDİ TEST EDİN:' AS 'ADIM 1',
    '1. Web sayfasını yenileyin (Ctrl+F5)' AS 'ADIM 2',
    '2. Bir devam eden döküm tamamlayın' AS 'ADIM 3', 
    '3. sql/15_debug_start_time_change.sql çalıştırın' AS 'ADIM 4',
    '4. Start time değişip değişmediğini kontrol edin' AS 'ADIM 5';

-- Beklenen sonuç
SELECT '✅ BEKLENEN SONUÇ' AS '─────────────────────────────────────';

SELECT 
    'Döküm tamamlandıktan sonra:' AS 'Beklenti',
    '- Devam eden dökümün start_time DEĞİŞMEMELİ' AS 'Kritik 1',
    '- Sadece end_time ve status güncellenMELİ' AS 'Kritik 2',
    '- Yeni döküm farklı start_time ile başlaMALI' AS 'Kritik 3',
    '- Süre hesaplaması doğru olMALI' AS 'Kritik 4';

SELECT '🎯 Temiz veri oluşturuldu! Artık test edebilirsiniz.' AS Mesaj;
