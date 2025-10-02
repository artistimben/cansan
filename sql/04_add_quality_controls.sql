-- Adım 4: Kalite Kontrol Verileri Ekle
-- Bu script tamamlanmış dökümler için örnek kalite kontrol verileri ekler

USE steel_factory_db;

-- Bugünkü tamamlanmış dökümler için kalite kontrol ekle
INSERT IGNORE INTO quality_controls (casting_id, carbon_percentage, silicon_percentage, manganese_percentage, temperature, test_result, test_time, tested_by)
SELECT 
    c.id,
    ROUND(0.200 + (RAND() * 0.100), 3) as carbon_percentage,    -- 0.200-0.300 arası
    ROUND(0.150 + (RAND() * 0.080), 3) as silicon_percentage,   -- 0.150-0.230 arası
    ROUND(0.700 + (RAND() * 0.200), 3) as manganese_percentage, -- 0.700-0.900 arası
    ROUND(1550 + (RAND() * 80), 1) as temperature,              -- 1550-1630°C arası
    'passed' as test_result,
    DATE_ADD(c.end_time, INTERVAL 10 MINUTE) as test_time,
    'Kalite Kontrol Uzmanı' as tested_by
FROM castings c
WHERE c.production_date = CURDATE() 
    AND c.status = 'completed' 
    AND c.end_time IS NOT NULL
    AND NOT EXISTS (
        SELECT 1 FROM quality_controls qc WHERE qc.casting_id = c.id
    );

-- Sonuç kontrol
SELECT 
    c.global_casting_number AS 'Döküm No',
    f.furnace_number AS 'Ocak',
    qc.carbon_percentage AS 'Karbon %',
    qc.silicon_percentage AS 'Silisyum %',
    qc.manganese_percentage AS 'Mangan %',
    qc.temperature AS 'Sıcaklık °C',
    qc.test_result AS 'Sonuç',
    DATE_FORMAT(qc.test_time, '%H:%i') AS 'Test Saati'
FROM quality_controls qc
JOIN castings c ON qc.casting_id = c.id
JOIN furnaces f ON c.furnace_id = f.id
WHERE c.production_date = CURDATE()
ORDER BY c.global_casting_number;

SELECT 'Kalite kontrol verileri eklendi!' AS Mesaj;
