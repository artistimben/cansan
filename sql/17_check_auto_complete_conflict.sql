    -- Adım 17: Auto Complete Çakışması Kontrolü
    -- Bu script otomatik döküm tamamlama çakışmasını kontrol eder

    USE steel_factory_db;

    -- Otomatik tamamlanması gereken dökümler var mı?
    SELECT '🔍 OTOMATİK TAMAMLANMASI GEREKEN DÖKÜMLER' AS '═══════════════════════════════════════';

    SELECT 
        c.id AS 'Döküm ID',
        c.global_casting_number AS 'Genel No',
        f.furnace_number AS 'Ocak',
        DATE_FORMAT(c.start_time, '%H:%i:%s') AS 'Başlangıç',
        TIMESTAMPDIFF(MINUTE, c.start_time, NOW()) AS 'Geçen Süre (dk)',
        CASE 
            WHEN TIMESTAMPDIFF(MINUTE, c.start_time, NOW()) >= 125 THEN '🚨 OTOMATİK TAMAMLANMALI'
            WHEN TIMESTAMPDIFF(MINUTE, c.start_time, NOW()) >= 120 THEN '⚠️ SÜRESİ DOLDU'
            ELSE '✅ NORMAL'
        END AS 'Durum'
    FROM castings c
    JOIN furnaces f ON c.furnace_id = f.id
    WHERE c.status = 'in_progress' 
    AND c.production_date = CURDATE()
    ORDER BY c.start_time ASC;

    -- Son güncellenen kayıtları kontrol et
    SELECT '📊 SON GÜNCELLENEN KAYITLAR' AS '─────────────────────────────────────';

    SELECT 
        c.id AS 'ID',
        c.global_casting_number AS 'No',
        f.furnace_number AS 'Ocak',
        c.status AS 'Durum',
        DATE_FORMAT(c.start_time, '%H:%i:%s') AS 'Start',
        DATE_FORMAT(c.end_time, '%H:%i:%s') AS 'End',
        DATE_FORMAT(c.updated_at, '%H:%i:%s') AS 'Güncelleme',
        TIMESTAMPDIFF(SECOND, c.updated_at, NOW()) AS 'Kaç Saniye Önce Güncellendi'
    FROM castings c
    JOIN furnaces f ON c.furnace_id = f.id
    WHERE c.production_date = CURDATE()
    ORDER BY c.updated_at DESC
    LIMIT 5;

    -- Çakışma tespiti
    SELECT '🔍 ÇAKIŞMA TESPİTİ' AS '─────────────────────────────────────';

    -- Aynı anda güncellenen kayıtlar
    SELECT 
        'Son 30 saniyede güncellenen döküm sayısı' AS 'Test',
        COUNT(*) AS 'Adet'
    FROM castings 
    WHERE production_date = CURDATE()
    AND TIMESTAMPDIFF(SECOND, updated_at, NOW()) <= 30

    UNION ALL

    -- Şüpheli start_time değişimleri
    SELECT 
        'Start time = Updated time olan kayıtlar' AS 'Test',
        COUNT(*) AS 'Adet'
    FROM castings 
    WHERE production_date = CURDATE()
    AND ABS(TIMESTAMPDIFF(SECOND, start_time, updated_at)) <= 5;

    -- Çözüm önerileri
    SELECT '💡 ÇÖZÜM ÖNERİLERİ' AS '─────────────────────────────────────';

    SELECT 
        '1. auto_complete.php scriptini durdurun' AS 'Öneri 1',
        '2. Cron job varsa devre dışı bırakın' AS 'Öneri 2',
        '3. Sadece manuel döküm tamamlama kullanın' AS 'Öneri 3',
        '4. Otomatik işlemleri test sonrası açın' AS 'Öneri 4';

    -- Test talimatları
    SELECT '📋 TEST TALİMATLARI' AS '─────────────────────────────────────';

    SELECT 
        'Şimdi yapılacaklar:' AS 'Adım',
        '1. Bu scripti çalıştırın' AS 'Kontrol 1',
        '2. Otomatik tamamlama var mı kontrol edin' AS 'Kontrol 2',
        '3. Manuel döküm tamamlayın' AS 'Test 1',
        '4. Start time korunuyor mu kontrol edin' AS 'Test 2';

    SELECT 'Çakışma kontrolü tamamlandı!' AS Mesaj;
