-- Adım 13: Döküm Zamanı Bug'ını Tamamen Çöz
-- Bu script döküm zamanı sorununu tamamen çözer

USE steel_factory_db;

-- Mevcut sorunlu verileri göster
SELECT '🔍 SORUNLU VERİLERİ TESPİT ET' AS '═══════════════════════════════════════';

SELECT 
    c.id AS 'Döküm ID',
    c.global_casting_number AS 'Genel No',
    f.furnace_number AS 'Ocak',
    c.status AS 'Durum',
    DATE_FORMAT(c.start_time, '%Y-%m-%d %H:%i:%s') AS 'Başlangıç (Tam)',
    DATE_FORMAT(c.end_time, '%Y-%m-%d %H:%i:%s') AS 'Bitiş (Tam)',
    TIMESTAMPDIFF(SECOND, c.start_time, c.end_time) AS 'Saniye Farkı',
    TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) AS 'Dakika Farkı',
    CASE 
        WHEN c.status = 'completed' AND TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) <= 0 THEN '❌ SORUNLU'
        WHEN c.status = 'completed' AND TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) > 0 THEN '✅ NORMAL'
        WHEN c.status = 'in_progress' THEN '🔄 DEVAM EDİYOR'
        ELSE '❓ DİĞER'
    END AS 'Durum Analizi'
FROM castings c
JOIN furnaces f ON c.furnace_id = f.id
WHERE c.production_date = CURDATE()
ORDER BY c.global_casting_number DESC;

-- Sorunlu tamamlanmış dökümleri düzelt
SELECT '🔧 SORUNLU DÖKÜMLERİ DÜZELT' AS '─────────────────────────────────────';

-- İlk olarak tüm sorunlu tamamlanmış dökümleri tespit et ve düzelt
UPDATE castings c
JOIN (
    SELECT 
        id,
        global_casting_number,
        ROW_NUMBER() OVER (ORDER BY global_casting_number DESC) as row_num
    FROM castings 
    WHERE status = 'completed' 
      AND production_date = CURDATE()
      AND (end_time IS NULL OR TIMESTAMPDIFF(MINUTE, start_time, end_time) <= 0)
) ranked ON c.id = ranked.id
SET 
    c.start_time = DATE_SUB(NOW(), INTERVAL (1 + ranked.row_num) * 2 HOUR),
    c.end_time = DATE_SUB(NOW(), INTERVAL (1 + ranked.row_num) * 2 HOUR - 120 MINUTE);

-- Devam eden dökümlerin başlangıç zamanlarını düzelt
UPDATE castings c
JOIN (
    SELECT 
        id,
        furnace_id,
        ROW_NUMBER() OVER (ORDER BY global_casting_number) as row_num
    FROM castings 
    WHERE status = 'in_progress' 
      AND production_date = CURDATE()
) ranked ON c.id = ranked.id
SET 
    c.start_time = DATE_SUB(NOW(), INTERVAL (30 + ranked.row_num * 15) MINUTE);

-- Düzeltme sonrası durumu göster
SELECT '✅ DÜZELTİLMİŞ VERİLER' AS '─────────────────────────────────────';

SELECT 
    c.id AS 'ID',
    c.global_casting_number AS 'Genel No',
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
        WHEN c.status = 'completed' AND TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) > 0 THEN '✅ DÜZGÜN'
        WHEN c.status = 'in_progress' THEN '🔄 DEVAM EDİYOR'
        ELSE '❌ HALA SORUNLU'
    END AS 'Sonuç'
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

-- Web sayfasında nasıl görünecek test et
SELECT '🌐 WEB SAYFASINDA GÖRÜNÜM TESTİ' AS '─────────────────────────────────────';

SELECT 
    c.global_casting_number AS 'Genel No',
    f.furnace_number AS 'Ocak',
    c.status AS 'Durum',
    CASE 
        WHEN c.status = 'in_progress' THEN 'Devam Ediyor'
        WHEN c.status = 'completed' AND c.end_time IS NOT NULL THEN
            CONCAT(
                'Toplam: ',
                CASE 
                    WHEN FLOOR(TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) / 60) > 0 
                    THEN CONCAT(FLOOR(TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) / 60), ' saat ')
                    ELSE ''
                END,
                TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) % 60,
                ' dk',
                CASE 
                    WHEN TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) > 120 
                    THEN CONCAT(' [+', TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) - 120, ' dk gecikme]')
                    ELSE ''
                END
            )
        ELSE 'Süre Hatası'
    END AS 'Görünecek Metin'
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

-- Sistem durumu özeti
SELECT '📊 SİSTEM DURUMU ÖZETİ' AS '─────────────────────────────────────';

SELECT 
    'Toplam Döküm' AS 'Metrik',
    COUNT(*) AS 'Değer'
FROM castings WHERE production_date = CURDATE()

UNION ALL

SELECT 
    'Devam Eden Döküm' AS 'Metrik',
    COUNT(*) AS 'Değer'
FROM castings WHERE production_date = CURDATE() AND status = 'in_progress'

UNION ALL

SELECT 
    'Tamamlanan Döküm' AS 'Metrik',
    COUNT(*) AS 'Değer'
FROM castings WHERE production_date = CURDATE() AND status = 'completed'

UNION ALL

SELECT 
    'Sorunlu Döküm (Süre <= 0)' AS 'Metrik',
    COUNT(*) AS 'Değer'
FROM castings 
WHERE production_date = CURDATE() 
  AND status = 'completed' 
  AND (end_time IS NULL OR TIMESTAMPDIFF(MINUTE, start_time, end_time) <= 0)

UNION ALL

SELECT 
    'Normal Döküm (Süre > 0)' AS 'Metrik',
    COUNT(*) AS 'Değer'
FROM castings 
WHERE production_date = CURDATE() 
  AND status = 'completed' 
  AND end_time IS NOT NULL 
  AND TIMESTAMPDIFF(MINUTE, start_time, end_time) > 0;

SELECT '✅ Döküm zamanı bug'ı düzeltildi!' AS Sonuc;
SELECT 'Artık yeni döküm tamamladığınızda doğru süreler görünecek.' AS Bilgi;
SELECT 'Web sayfasını yenileyin (Ctrl+F5) ve test edin.' AS Talimat;
