-- Adım 12: Gecikmeli Dökümler Oluştur (Test İçin)
-- Bu script test amaçlı gecikmeli dökümler oluşturur

USE steel_factory_db;

-- Mevcut durumu göster
SELECT '📊 MEVCUT DÖKÜM DURUMU' AS '═══════════════════════════════════════';

SELECT 
    COUNT(*) AS 'Toplam Döküm',
    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS 'Devam Eden',
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS 'Tamamlanan'
FROM castings 
WHERE production_date = CURDATE();

-- Bazı tamamlanmış dökümleri gecikmeli yap (test için)
UPDATE castings 
SET 
    start_time = DATE_SUB(NOW(), INTERVAL 3 HOUR),
    end_time = DATE_SUB(NOW(), INTERVAL 40 MINUTE)  -- 140 dakika (20 dk gecikme)
WHERE status = 'completed' 
  AND production_date = CURDATE()
  AND global_casting_number = (
      SELECT MAX(global_casting_number) 
      FROM (SELECT global_casting_number FROM castings WHERE status = 'completed' AND production_date = CURDATE()) AS sub
  );

-- İkinci döküm - daha az gecikme
UPDATE castings 
SET 
    start_time = DATE_SUB(NOW(), INTERVAL 4 HOUR),
    end_time = DATE_SUB(NOW(), INTERVAL 2 HOUR 10 MINUTE)  -- 130 dakika (10 dk gecikme)
WHERE status = 'completed' 
  AND production_date = CURDATE()
  AND global_casting_number = (
      SELECT MAX(global_casting_number) 
      FROM (
          SELECT global_casting_number 
          FROM castings 
          WHERE status = 'completed' 
            AND production_date = CURDATE()
            AND global_casting_number < (
                SELECT MAX(global_casting_number) 
                FROM castings 
                WHERE status = 'completed' 
                  AND production_date = CURDATE()
            )
      ) AS sub
  );

-- Üçüncü döküm - normal süre (gecikme yok)
UPDATE castings 
SET 
    start_time = DATE_SUB(NOW(), INTERVAL 6 HOUR),
    end_time = DATE_SUB(NOW(), INTERVAL 4 HOUR)  -- 120 dakika (gecikme yok)
WHERE status = 'completed' 
  AND production_date = CURDATE()
  AND global_casting_number = (
      SELECT MIN(global_casting_number) 
      FROM (SELECT global_casting_number FROM castings WHERE status = 'completed' AND production_date = CURDATE()) AS sub
  );

-- Sonuçları göster
SELECT '🎯 GECİKMELİ DÖKÜM TEST VERİLERİ' AS '─────────────────────────────────────';

SELECT 
    c.global_casting_number AS 'Genel No',
    f.furnace_number AS 'Ocak',
    DATE_FORMAT(c.start_time, '%H:%i') AS 'Başlangıç',
    DATE_FORMAT(c.end_time, '%H:%i') AS 'Bitiş',
    TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) AS 'Toplam Süre (dk)',
    CASE 
        WHEN TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) > 120 
        THEN CONCAT('+', TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) - 120, ' dk gecikme')
        ELSE 'Normal süre'
    END AS 'Gecikme Durumu',
    CASE 
        WHEN TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) > 120 
        THEN '⚠️ Gecikme nedeni sorulacak'
        ELSE '✅ Normal'
    END AS 'Aksiyon'
FROM castings c
JOIN furnaces f ON c.furnace_id = f.id
WHERE c.status = 'completed' 
  AND c.production_date = CURDATE()
ORDER BY c.global_casting_number DESC;

-- Web sayfasında nasıl görünecek
SELECT '🌐 WEB SAYFASINDA GÖRÜNÜM' AS '─────────────────────────────────────';

SELECT 
    c.global_casting_number AS 'Genel No',
    CONCAT(
        'Toplam: ',
        FLOOR(TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) / 60),
        ' saat ',
        TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) % 60,
        ' dk',
        CASE 
            WHEN TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) > 120 
            THEN CONCAT(' [+', TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) - 120, ' dk gecikme]')
            ELSE ''
        END
    ) AS 'Görünecek Metin',
    CASE 
        WHEN TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) > 120 
        THEN 'Gecikme Nedeni butonu gösterilecek'
        ELSE 'Normal görünüm'
    END AS 'Ek Özellik'
FROM castings c
WHERE c.status = 'completed' 
  AND c.production_date = CURDATE()
ORDER BY c.global_casting_number DESC;

SELECT '✅ Gecikmeli döküm test verileri oluşturuldu!' AS Sonuc;
SELECT 'Web sayfasını yenileyin ve gecikme butonlarını test edin.' AS Talimat;
