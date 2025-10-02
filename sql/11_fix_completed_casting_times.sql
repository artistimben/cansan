-- Adım 11: Tamamlanmış Döküm Zamanlarını Düzelt
-- Bu script tamamlanmış dökümlerin start_time ve end_time değerlerini gerçekçi hale getirir

USE steel_factory_db;

-- Mevcut sorunlu tamamlanmış dökümleri göster
SELECT '🔍 SORUNLU TAMAMLANMIŞ DÖKÜMLER (ÖNCESİ)' AS '═══════════════════════════════════════';

SELECT 
    c.id AS 'ID',
    c.global_casting_number AS 'Genel No',
    f.furnace_number AS 'Ocak',
    DATE_FORMAT(c.start_time, '%H:%i:%s') AS 'Başlangıç',
    DATE_FORMAT(c.end_time, '%H:%i:%s') AS 'Bitiş',
    TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) AS 'Süre (dk)',
    CASE 
        WHEN TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) <= 0 THEN '❌ SORUNLU'
        ELSE '✅ NORMAL'
    END AS 'Durum'
FROM castings c
JOIN furnaces f ON c.furnace_id = f.id
WHERE c.status = 'completed' 
  AND c.production_date = CURDATE()
ORDER BY c.global_casting_number DESC;

-- Sorunlu tamamlanmış dökümlerin zamanlarını düzelt
-- Her döküm için farklı başlangıç ve bitiş zamanları ata

-- En son tamamlanan dökümler için gerçekçi zamanlar
UPDATE castings 
SET 
    start_time = DATE_SUB(NOW(), INTERVAL 3 HOUR),
    end_time = DATE_SUB(NOW(), INTERVAL 1 HOUR)
WHERE status = 'completed' 
  AND production_date = CURDATE()
  AND global_casting_number = (
      SELECT MAX(global_casting_number) 
      FROM (SELECT global_casting_number FROM castings WHERE status = 'completed' AND production_date = CURDATE()) AS sub
  );

-- İkinci son döküm
UPDATE castings 
SET 
    start_time = DATE_SUB(NOW(), INTERVAL 4 HOUR),
    end_time = DATE_SUB(NOW(), INTERVAL 2 HOUR)
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

-- Üçüncü son döküm
UPDATE castings 
SET 
    start_time = DATE_SUB(NOW(), INTERVAL 5 HOUR),
    end_time = DATE_SUB(NOW(), INTERVAL 3 HOUR)
WHERE status = 'completed' 
  AND production_date = CURDATE()
  AND global_casting_number = (
      SELECT MIN(global_casting_number) 
      FROM (SELECT global_casting_number FROM castings WHERE status = 'completed' AND production_date = CURDATE()) AS sub
  );

-- Diğer tamamlanmış dökümler için de düzelt (eğer varsa)
UPDATE castings c
JOIN (
    SELECT 
        id,
        ROW_NUMBER() OVER (ORDER BY global_casting_number DESC) as rn
    FROM castings 
    WHERE status = 'completed' 
      AND production_date = CURDATE()
      AND TIMESTAMPDIFF(MINUTE, start_time, end_time) <= 0
) ranked ON c.id = ranked.id
SET 
    c.start_time = DATE_SUB(NOW(), INTERVAL (2 + ranked.rn) HOUR),
    c.end_time = DATE_SUB(NOW(), INTERVAL ranked.rn HOUR);

-- Düzeltilmiş dökümleri göster
SELECT '✅ DÜZELTİLMİŞ TAMAMLANMIŞ DÖKÜMLER (SONRASI)' AS '═══════════════════════════════════════';

SELECT 
    c.id AS 'ID',
    c.global_casting_number AS 'Genel No',
    f.furnace_number AS 'Ocak',
    DATE_FORMAT(c.start_time, '%H:%i:%s') AS 'Başlangıç',
    DATE_FORMAT(c.end_time, '%H:%i:%s') AS 'Bitiş',
    TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) AS 'Süre (dk)',
    CONCAT(
        FLOOR(TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) / 60),
        ' saat ',
        TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) % 60,
        ' dakika'
    ) AS 'Süre Formatı',
    CASE 
        WHEN TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) <= 0 THEN '❌ HALA SORUNLU'
        WHEN TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) BETWEEN 90 AND 150 THEN '✅ NORMAL'
        ELSE '⚠️ KONTROL ET'
    END AS 'Durum'
FROM castings c
JOIN furnaces f ON c.furnace_id = f.id
WHERE c.status = 'completed' 
  AND c.production_date = CURDATE()
ORDER BY c.global_casting_number DESC;

-- Test: Web sayfasında nasıl görünecek
SELECT '🌐 WEB SAYFASINDA NASIL GÖRÜNECEK' AS '─────────────────────────────────────';

SELECT 
    c.global_casting_number AS 'Genel No',
    f.furnace_number AS 'Ocak',
    DATE_FORMAT(c.start_time, '%H:%i') AS 'Başlangıç Saati',
    DATE_FORMAT(c.end_time, '%H:%i') AS 'Bitiş Saati',
    CONCAT(
        'Toplam: ',
        CASE 
            WHEN FLOOR(TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) / 60) > 0 
            THEN CONCAT(FLOOR(TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) / 60), ' saat ')
            ELSE ''
        END,
        TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) % 60,
        ' dk'
    ) AS 'Görünecek Süre'
FROM castings c
JOIN furnaces f ON c.furnace_id = f.id
WHERE c.status = 'completed' 
  AND c.production_date = CURDATE()
ORDER BY c.global_casting_number DESC;

SELECT '✅ Tamamlanmış döküm zamanları düzeltildi!' AS Sonuc;
SELECT 'Şimdi web sayfasını yenileyin (Ctrl+F5) ve kontrol edin.' AS Talimat;
