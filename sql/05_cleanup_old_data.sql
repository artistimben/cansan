-- Adım 5: Eski Verileri Temizle (İsteğe Bağlı)
-- Bu script sadece gerektiğinde çalıştırılır - tüm verileri siler

USE steel_factory_db;

-- UYARI: Bu script tüm verileri siler!
-- Sadece tamamen yeniden başlamak istiyorsanız çalıştırın

-- Mevcut veri sayıları
SELECT 'Silinecek veriler:' AS Uyari;
SELECT 'Kalite Kontrol:' AS Tablo, COUNT(*) AS Adet FROM quality_controls
UNION ALL
SELECT 'Dökümler:' AS Tablo, COUNT(*) AS Adet FROM castings
UNION ALL
SELECT 'Günlük Raporlar:' AS Tablo, COUNT(*) AS Adet FROM daily_reports
UNION ALL
SELECT 'Ocaklar:' AS Tablo, COUNT(*) AS Adet FROM furnaces;

-- Verileri temizle (Foreign key sırası önemli)
-- DELETE FROM quality_controls;
-- DELETE FROM castings;
-- DELETE FROM daily_reports;
-- DELETE FROM furnaces;

-- Auto increment değerlerini sıfırla
-- ALTER TABLE quality_controls AUTO_INCREMENT = 1;
-- ALTER TABLE castings AUTO_INCREMENT = 1;
-- ALTER TABLE daily_reports AUTO_INCREMENT = 1;
-- ALTER TABLE furnaces AUTO_INCREMENT = 1;

SELECT '⚠️ UYARI: Veri temizleme scripti pasif!' AS Mesaj;
SELECT 'Temizlemek için yukarıdaki -- işaretlerini kaldırın' AS Talimat;
