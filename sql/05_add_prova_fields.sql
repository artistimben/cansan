-- Prova Kayıtları için Yeni Alanlar
-- Mevcut quality_controls tablosuna copper_percentage ve prova_data alanları eklenir

-- Bakır (CU) yüzdesi alanı
ALTER TABLE quality_controls 
ADD COLUMN copper_percentage DECIMAL(5,3) NULL AFTER nickel_percentage;

-- Çoklu prova kayıtları için JSON alanı
ALTER TABLE quality_controls 
ADD COLUMN prova_data JSON NULL AFTER remarks;

-- Başarıyla eklendi mesajı
SELECT 'Prova alanları başarıyla eklendi!' AS Mesaj;

