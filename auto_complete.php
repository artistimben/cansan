<?php
/**
 * Otomatik Döküm Tamamlama Scripti
 * Cron job olarak her dakika çalışır
 * 
 * Crontab'a eklemek için:
 * * * * * * /usr/bin/php /path/to/auto_complete.php >> /dev/null 2>&1
 */

require_once 'config.php';
require_once 'functions.php';

echo date('Y-m-d H:i:s') . " - Otomatik döküm kontrolü başlatıldı\n";

// GEÇİCİ OLARAK DEVRE DIŞI - TEST İÇİN
echo "⚠️ Otomatik döküm tamamlama geçici olarak devre dışı bırakıldı.\n";
echo "Manuel test tamamlandıktan sonra aktif edilecek.\n";
exit(0);

try {
    // Süresi dolmuş dökümler
    $stmt = $pdo->query("
        SELECT c.*, f.furnace_number 
        FROM castings c 
        JOIN furnaces f ON c.furnace_id = f.id 
        WHERE c.status = 'in_progress' 
        AND TIMESTAMPDIFF(MINUTE, c.start_time, NOW()) >= 125
    ");
    
    $overdue_castings = $stmt->fetchAll();
    $completed_count = 0;
    
    foreach ($overdue_castings as $casting) {
        try {
            $pdo->beginTransaction();
            
            echo "Döküm tamamlanıyor: {$casting['furnace_number']}. Ocak, Döküm #{$casting['global_casting_number']}\n";
            
            // Döküm tamamla - güvenli şekilde  
            $current_end_time = date('Y-m-d H:i:s');
            $stmt = $pdo->prepare("UPDATE castings SET status = 'completed', end_time = ? WHERE id = ? AND status = 'in_progress'");
            $stmt->execute([$current_end_time, $casting['id']]);
            
            // Ocağın toplam döküm sayısını artır
            $stmt = $pdo->prepare("UPDATE furnaces SET total_castings = total_castings + 1 WHERE id = ?");
            $stmt->execute([$casting['furnace_id']]);
            
            // Bakım gerekli mi kontrol et
            $stmt = $pdo->prepare("
                SELECT * FROM furnaces 
                WHERE id = ? AND total_castings >= max_castings_before_maintenance
            ");
            $stmt->execute([$casting['furnace_id']]);
            $needs_maintenance = $stmt->fetch();
            
            if ($needs_maintenance) {
                echo "Ocak bakıma gönderiliyor: {$casting['furnace_number']}. Ocak\n";
                send_furnace_to_maintenance($casting['furnace_id']);
            } else {
                // Yeni döküm başlat
                start_new_casting($casting['furnace_id']);
                echo "Yeni döküm başlatıldı: {$casting['furnace_number']}. Ocak\n";
            }
            
            $pdo->commit();
            $completed_count++;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "HATA: " . $e->getMessage() . "\n";
        }
    }
    
    // Boş ocakları kontrol et
    $stmt = $pdo->query("
        SELECT f.* FROM furnaces f 
        LEFT JOIN castings c ON f.id = c.furnace_id AND c.status = 'in_progress'
        WHERE f.status = 'active' AND c.id IS NULL
    ");
    
    $empty_furnaces = $stmt->fetchAll();
    $started_count = 0;
    
    foreach ($empty_furnaces as $furnace) {
        try {
            echo "Boş ocak tespit edildi: {$furnace['furnace_number']}. Ocak\n";
            start_new_casting($furnace['id']);
            echo "Yeni döküm başlatıldı: {$furnace['furnace_number']}. Ocak\n";
            $started_count++;
        } catch (Exception $e) {
            echo "HATA: " . $e->getMessage() . "\n";
        }
    }
    
    // Günlük rapor (sabah 8'de)
    if (date('H:i') === '08:00') {
        try {
            generate_daily_report();
            echo "Günlük rapor oluşturuldu\n";
        } catch (Exception $e) {
            echo "Rapor hatası: " . $e->getMessage() . "\n";
        }
    }
    
    echo "İşlem tamamlandı - Tamamlanan: {$completed_count}, Başlatılan: {$started_count}\n";
    
} catch (Exception $e) {
    echo "Genel hata: " . $e->getMessage() . "\n";
}

echo "---\n";
?>
