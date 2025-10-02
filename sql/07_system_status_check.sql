-- Adım 7: Sistem Durumu Kontrolü
-- Bu script sistemin genel durumunu kontrol eder

USE steel_factory_db;

SELECT '🏭 ÇELİK FABRİKASI SİSTEM DURUMU' AS '═══════════════════════════════════════';

-- Ocak durumları özeti
SELECT 'OCAK DURUMU ÖZETİ' AS '─────────────────────────────────';
SELECT 
    status AS 'Durum',
    COUNT(*) AS 'Adet',
    GROUP_CONCAT(furnace_number ORDER BY furnace_number) AS 'Ocak Numaraları'
FROM furnaces 
GROUP BY status
ORDER BY 
    CASE status 
        WHEN 'active' THEN 1 
        WHEN 'standby' THEN 2 
        WHEN 'maintenance' THEN 3 
    END;

-- Bugünkü döküm özeti
SELECT 'BUGÜNKÜ DÖKÜM ÖZETİ' AS '─────────────────────────────────';
SELECT 
    status AS 'Döküm Durumu',
    COUNT(*) AS 'Adet',
    CONCAT(
        ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM castings WHERE production_date = CURDATE()), 1),
        '%'
    ) AS 'Yüzde'
FROM castings 
WHERE production_date = CURDATE()
GROUP BY status
ORDER BY 
    CASE status 
        WHEN 'in_progress' THEN 1 
        WHEN 'completed' THEN 2 
        WHEN 'cancelled' THEN 3 
    END;

-- Kalite kontrol özeti
SELECT 'KALİTE KONTROL ÖZETİ' AS '─────────────────────────────────';
SELECT 
    qc.test_result AS 'Test Sonucu',
    COUNT(*) AS 'Adet',
    CONCAT(
        ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM quality_controls qc2 
                                  JOIN castings c2 ON qc2.casting_id = c2.id 
                                  WHERE c2.production_date = CURDATE()), 1),
        '%'
    ) AS 'Yüzde'
FROM quality_controls qc
JOIN castings c ON qc.casting_id = c.id
WHERE c.production_date = CURDATE()
GROUP BY qc.test_result
ORDER BY 
    CASE qc.test_result 
        WHEN 'passed' THEN 1 
        WHEN 'pending' THEN 2 
        WHEN 'failed' THEN 3 
    END;

-- Detaylı ocak durumları
SELECT 'DETAYLI OCAK DURUMLARI' AS '─────────────────────────────────';
SELECT 
    f.furnace_number AS 'Ocak No',
    f.furnace_set AS 'Set',
    f.status AS 'Durum',
    f.total_castings AS 'Toplam Döküm',
    CONCAT(f.total_castings, '/', f.max_castings_before_maintenance) AS 'Bakım Durumu',
    CASE 
        WHEN f.is_charging THEN '🔥 Döküm Yapıyor'
        ELSE '⏸️ Boş'
    END AS 'Charging',
    COALESCE(
        (SELECT COUNT(*) FROM castings c WHERE c.furnace_id = f.id AND c.production_date = CURDATE()), 
        0
    ) AS 'Bugünkü Döküm'
FROM furnaces f
ORDER BY f.furnace_number;

-- Bugünkü döküm listesi
SELECT 'BUGÜNKÜ DÖKÜM LİSTESİ' AS '─────────────────────────────────';
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
        WHEN c.end_time IS NULL THEN 
            CONCAT(TIMESTAMPDIFF(MINUTE, c.start_time, NOW()), ' dk geçti')
        ELSE 
            CONCAT(TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time), ' dk')
    END AS 'Süre',
    c.status AS 'Durum',
    CASE 
        WHEN qc.test_result IS NOT NULL THEN qc.test_result
        ELSE 'Test Yok'
    END AS 'Kalite'
FROM castings c
JOIN furnaces f ON c.furnace_id = f.id
LEFT JOIN quality_controls qc ON c.id = qc.casting_id
WHERE c.production_date = CURDATE()
ORDER BY c.global_casting_number DESC;

-- Sistem sağlığı
SELECT 'SİSTEM SAĞLIĞI' AS '─────────────────────────────────';
SELECT 
    'Aktif Ocak Sayısı' AS 'Metrik',
    COUNT(*) AS 'Değer',
    CASE 
        WHEN COUNT(*) = 3 THEN '✅ Normal'
        WHEN COUNT(*) < 3 THEN '⚠️ Eksik'
        ELSE '❓ Fazla'
    END AS 'Durum'
FROM furnaces WHERE status = 'active'

UNION ALL

SELECT 
    'Devam Eden Döküm' AS 'Metrik',
    COUNT(*) AS 'Değer',
    CASE 
        WHEN COUNT(*) = 3 THEN '✅ Normal'
        WHEN COUNT(*) < 3 THEN '⚠️ Eksik'
        ELSE '❓ Fazla'
    END AS 'Durum'
FROM castings WHERE status = 'in_progress'

UNION ALL

SELECT 
    'Bugünkü Toplam Döküm' AS 'Metrik',
    COUNT(*) AS 'Değer',
    CASE 
        WHEN COUNT(*) >= 6 THEN '✅ İyi'
        WHEN COUNT(*) >= 3 THEN '⚠️ Orta'
        ELSE '❌ Düşük'
    END AS 'Durum'
FROM castings WHERE production_date = CURDATE();

SELECT '✅ Sistem durumu kontrolü tamamlandı!' AS Sonuc;
