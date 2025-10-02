-- Adım 9: Döküm Tamamlama Debug
-- Bu script döküm tamamlama işlemini test eder ve debug yapar

USE steel_factory_db;

-- Mevcut devam eden dökümleri detaylı göster
SELECT '🔍 MEVCUT DEVAM EDEN DÖKÜMLER - DETAY ANALİZ' AS '═══════════════════════════════════════';

SELECT 
    c.id AS 'Döküm ID',
    f.furnace_number AS 'Ocak No',
    c.global_casting_number AS 'Genel No',
    c.casting_number_per_furnace AS 'Ocak Dökümü',
    DATE_FORMAT(c.start_time, '%Y-%m-%d %H:%i:%s') AS 'Başlangıç (Tam)',
    DATE_FORMAT(c.start_time, '%H:%i') AS 'Başlangıç',
    CASE 
        WHEN c.end_time IS NULL THEN 'NULL (Devam Ediyor)'
        ELSE DATE_FORMAT(c.end_time, '%H:%i:%s')
    END AS 'Bitiş',
    TIMESTAMPDIFF(MINUTE, c.start_time, NOW()) AS 'Şu Ana Kadar Geçen (dk)',
    GREATEST(0, 120 - TIMESTAMPDIFF(MINUTE, c.start_time, NOW())) AS 'Kalan Süre (dk)',
    ROUND(
        LEAST(100, (TIMESTAMPDIFF(MINUTE, c.start_time, NOW()) / 120) * 100), 1
    ) AS 'İlerleme %',
    c.status AS 'Durum'
FROM castings c
JOIN furnaces f ON c.furnace_id = f.id
WHERE c.status = 'in_progress'
ORDER BY c.global_casting_number;

-- Test senaryosu: Eğer şimdi döküm tamamlasak ne olur?
SELECT '🧪 TEST SENARYOSU: ŞİMDİ DÖKÜM TAMAMLASAK' AS '─────────────────────────────────────';

SELECT 
    f.furnace_number AS 'Ocak No',
    c.id AS 'Döküm ID',
    DATE_FORMAT(c.start_time, '%H:%i:%s') AS 'Başlangıç Zamanı',
    DATE_FORMAT(NOW(), '%H:%i:%s') AS 'Şimdiki Zaman (Bitiş Olacak)',
    TIMESTAMPDIFF(MINUTE, c.start_time, NOW()) AS 'Hesaplanan Süre (dk)',
    CONCAT(
        FLOOR(TIMESTAMPDIFF(MINUTE, c.start_time, NOW()) / 60),
        ' saat ',
        TIMESTAMPDIFF(MINUTE, c.start_time, NOW()) % 60,
        ' dakika'
    ) AS 'Süre Formatı',
    CASE 
        WHEN TIMESTAMPDIFF(MINUTE, c.start_time, NOW()) = 0 THEN '❌ SORUN: Süre 0!'
        WHEN TIMESTAMPDIFF(MINUTE, c.start_time, NOW()) < 30 THEN '⚠️ DİKKAT: Çok kısa süre'
        WHEN TIMESTAMPDIFF(MINUTE, c.start_time, NOW()) > 180 THEN '⚠️ DİKKAT: Çok uzun süre'
        ELSE '✅ Normal süre'
    END AS 'Durum Analizi'
FROM castings c
JOIN furnaces f ON c.furnace_id = f.id
WHERE c.status = 'in_progress'
ORDER BY f.furnace_number;

-- Tamamlanmış dökümlerin süre analizi
SELECT '📊 TAMAMLANMIŞ DÖKÜMLERIN SÜRE ANALİZİ' AS '─────────────────────────────────────';

SELECT 
    c.global_casting_number AS 'Genel No',
    f.furnace_number AS 'Ocak',
    DATE_FORMAT(c.start_time, '%H:%i') AS 'Başlangıç',
    DATE_FORMAT(c.end_time, '%H:%i') AS 'Bitiş',
    TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) AS 'Gerçek Süre (dk)',
    CASE 
        WHEN TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) = 0 THEN '❌ SORUN: Süre 0!'
        WHEN TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) < 60 THEN '⚠️ Kısa'
        WHEN TIMESTAMPDIFF(MINUTE, c.start_time, c.end_time) > 150 THEN '⚠️ Uzun'
        ELSE '✅ Normal'
    END AS 'Süre Durumu'
FROM castings c
JOIN furnaces f ON c.furnace_id = f.id
WHERE c.status = 'completed' 
  AND c.production_date = CURDATE()
  AND c.end_time IS NOT NULL
ORDER BY c.global_casting_number DESC;

-- Sistem önerileri
SELECT '💡 SİSTEM ÖNERİLERİ' AS '─────────────────────────────────────';

SELECT 
    CASE 
        WHEN EXISTS (
            SELECT 1 FROM castings 
            WHERE status = 'in_progress' 
            AND TIMESTAMPDIFF(MINUTE, start_time, NOW()) < 5
        ) THEN '⚠️ Bazı dökümler çok yakın zamanda başlamış. sql/08_fix_casting_times.sql çalıştırın.'
        ELSE '✅ Döküm başlangıç zamanları uygun görünüyor.'
    END AS 'Başlangıç Zamanları',
    
    CASE 
        WHEN EXISTS (
            SELECT 1 FROM castings 
            WHERE status = 'completed' 
            AND production_date = CURDATE()
            AND TIMESTAMPDIFF(MINUTE, start_time, end_time) = 0
        ) THEN '❌ Bazı tamamlanmış dökümlerin süresi 0. Veri sorunu var!'
        ELSE '✅ Tamamlanmış döküm süreleri normal görünüyor.'
    END AS 'Tamamlanmış Süreler',
    
    CONCAT(
        'Şu anda ', 
        (SELECT COUNT(*) FROM castings WHERE status = 'in_progress'), 
        ' döküm devam ediyor.'
    ) AS 'Aktif Durum';

SELECT '🎯 SONUÇ VE TAVSİYELER' AS '─────────────────────────────────────';

SELECT 
    'Eğer döküm tamamlama süreleri 0 çıkıyorsa:' AS 'Sorun',
    '1. sql/08_fix_casting_times.sql çalıştırın' AS 'Çözüm 1',
    '2. Sayfayı yenileyin (Ctrl+F5)' AS 'Çözüm 2',
    '3. Bu debug scripti tekrar çalıştırın' AS 'Çözüm 3';

SELECT 'Debug analizi tamamlandı!' AS Mesaj;
