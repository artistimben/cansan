<?php
/**
 * Çelik Fabrikası Yönetim Sistemi - Yardımcı Fonksiyonlar
 */

require_once 'config.php';

// Aktif ocakları getir
function get_active_furnaces() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM furnaces WHERE status = 'active' ORDER BY furnace_number");
    return $stmt->fetchAll();
}

// Tüm ocakları set bazında getir (set değiştirme için)
function get_all_furnaces_by_set() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM furnaces ORDER BY furnace_set, furnace_number");
    $furnaces = $stmt->fetchAll();
    
    $sets = [];
    foreach ($furnaces as $furnace) {
        $set = $furnace['furnace_set'];
        if (!isset($sets[$set])) {
            $sets[$set] = [];
        }
        $sets[$set][] = $furnace;
    }
    
    return $sets;
}

// Devam eden dökümler
function get_active_castings() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT c.*, f.furnace_number 
        FROM castings c 
        JOIN furnaces f ON c.furnace_id = f.id 
        WHERE c.status = 'in_progress' 
        ORDER BY c.start_time DESC
    ");
    return $stmt->fetchAll();
}

// Bugünkü tüm dökümler (devam eden önce, sonra tamamlanan)
function get_todays_castings() {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT c.*, f.furnace_number 
        FROM castings c 
        JOIN furnaces f ON c.furnace_id = f.id 
        WHERE c.production_date = ? 
        ORDER BY 
            CASE c.status 
                WHEN 'in_progress' THEN 1 
                WHEN 'completed' THEN 2 
                ELSE 3 
            END,
            c.global_casting_number DESC
    ");
    $stmt->execute([today()]);
    return $stmt->fetchAll();
}

// Günlük istatistikler
function get_daily_stats() {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM castings WHERE production_date = ?");
    $stmt->execute([today()]);
    $total = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as completed FROM castings WHERE production_date = ? AND status = 'completed'");
    $stmt->execute([today()]);
    $completed = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as in_progress FROM castings WHERE status = 'in_progress'");
    $stmt->execute();
    $in_progress = $stmt->fetchColumn();
    
    return [
        'total_castings' => $total,
        'completed_castings' => $completed,
        'in_progress_castings' => $in_progress
    ];
}

// Bakıma gitmesi gereken ocaklar
function get_furnaces_needing_maintenance() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT * FROM furnaces 
        WHERE total_castings >= max_castings_before_maintenance 
        AND status = 'active'
    ");
    return $stmt->fetchAll();
}

// Ocağın mevcut döküm durumu
function get_current_casting($furnace_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT * FROM castings 
        WHERE furnace_id = ? AND status = 'in_progress' 
        LIMIT 1
    ");
    $stmt->execute([$furnace_id]);
    return $stmt->fetch();
}

// Kalite kontrol bilgisi
function get_quality_control($casting_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM quality_controls WHERE casting_id = ? LIMIT 1");
    $stmt->execute([$casting_id]);
    return $stmt->fetch();
}

// AJAX isteklerini handle et
function handle_ajax_request($action) {
    global $pdo;
    
    switch ($action) {
        case 'complete_casting':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['casting_id'])) {
                $casting_id = (int)$_GET['casting_id'];
                
                try {
                    $pdo->beginTransaction();
                    
                    // Önce döküm bilgilerini al
                    $stmt = $pdo->prepare("SELECT furnace_id FROM castings WHERE id = ? AND status = 'in_progress'");
                    $stmt->execute([$casting_id]);
                    $casting_info = $stmt->fetch();
                    
                    if (!$casting_info) {
                        throw new Exception('Döküm bulunamadı veya zaten tamamlanmış.');
                    }
                    
                    $furnace_id = $casting_info['furnace_id'];
                    
                    // Döküm tamamla - sadece belirli ID'yi güncelle, start_time'a dokunma
                    $current_end_time = date('Y-m-d H:i:s');
                    $stmt = $pdo->prepare("
                        UPDATE castings 
                        SET status = 'completed', end_time = ? 
                        WHERE id = ? AND status = 'in_progress'
                    ");
                    $stmt->execute([$current_end_time, $casting_id]);
                    
                    // Güncellenme kontrolü
                    if ($stmt->rowCount() === 0) {
                        throw new Exception('Döküm güncellenemedi. Döküm zaten tamamlanmış olabilir.');
                    }
                    
                    // Ocağın toplam döküm sayısını artır
                    $stmt = $pdo->prepare("UPDATE furnaces SET total_castings = total_castings + 1 WHERE id = ?");
                    $stmt->execute([$furnace_id]);
                    
                    // Transaction'ı commit et (yeni döküm başlatmadan önce)
                    $pdo->commit();
                    
                    // Şimdi yeni döküm başlat (ayrı transaction)
                    try {
                        start_new_casting($furnace_id);
                        json_response(['success' => true, 'message' => 'Döküm başarıyla tamamlandı ve yeni döküm başlatıldı.']);
                    } catch (Exception $e) {
                        // Yeni döküm başlatılamasa bile tamamlama başarılı
                        json_response(['success' => true, 'message' => 'Döküm tamamlandı, ancak yeni döküm başlatılamadı: ' . $e->getMessage()]);
                    }
                    
                } catch (Exception $e) {
                    $pdo->rollBack();
                    json_response(['success' => false, 'message' => 'Hata: ' . $e->getMessage()], 500);
                }
            }
            break;
            
        case 'start_casting':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['furnace_id'])) {
                $furnace_id = (int)$_GET['furnace_id'];
                
                try {
                    start_new_casting($furnace_id);
                    json_response(['success' => true, 'message' => 'Yeni döküm başarıyla başlatıldı.']);
                } catch (Exception $e) {
                    json_response(['success' => false, 'message' => 'Hata: ' . $e->getMessage()], 500);
                }
            }
            break;
            
        case 'send_to_maintenance':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['furnace_id'])) {
                $furnace_id = (int)$_GET['furnace_id'];
                
                try {
                    send_furnace_to_maintenance($furnace_id);
                    json_response(['success' => true, 'message' => 'Ocak bakıma gönderildi ve yedek ocak aktif edildi.']);
                } catch (Exception $e) {
                    json_response(['success' => false, 'message' => 'Hata: ' . $e->getMessage()], 500);
                }
            }
            break;
            
        case 'setup_system':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    setup_initial_system();
                    json_response(['success' => true, 'message' => 'Sistem başarıyla kuruldu!']);
                } catch (Exception $e) {
                    json_response(['success' => false, 'message' => 'Kurulum hatası: ' . $e->getMessage()], 500);
                }
            }
            break;
            
        case 'generate_daily_report':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    generate_daily_report();
                    json_response(['success' => true, 'message' => 'Günlük rapor başarıyla oluşturuldu.']);
                } catch (Exception $e) {
                    json_response(['success' => false, 'message' => 'Rapor hatası: ' . $e->getMessage()], 500);
                }
            }
            break;
            
        case 'save_delay_reason':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['casting_id']) && isset($_GET['reason'])) {
                $casting_id = (int)$_GET['casting_id'];
                $reason = trim($_GET['reason']);
                
                if (empty($reason)) {
                    json_response(['success' => false, 'message' => 'Gecikme nedeni boş olamaz.'], 400);
                    break;
                }
                
                try {
                    // Döküm notlarına gecikme nedenini ekle
                    $stmt = $pdo->prepare("
                        UPDATE castings 
                        SET notes = CONCAT(
                            COALESCE(notes, ''), 
                            CASE WHEN notes IS NULL OR notes = '' THEN '' ELSE '\n' END,
                            'Gecikme Nedeni: ', ?
                        ) 
                        WHERE id = ?
                    ");
                    $stmt->execute([$reason, $casting_id]);
                    
                    json_response([
                        'success' => true, 
                        'message' => 'Gecikme nedeni başarıyla kaydedildi.',
                        'data' => ['reason' => $reason]
                    ]);
                    
                } catch (Exception $e) {
                    json_response(['success' => false, 'message' => 'Gecikme nedeni kaydedilirken hata: ' . $e->getMessage()], 500);
                }
            }
            break;
            
        case 'get_quality_control':
            if (isset($_GET['casting_id'])) {
                $casting_id = (int)$_GET['casting_id'];
                
                try {
                    // Döküm bilgilerini al
                    $stmt = $pdo->prepare("
                        SELECT c.*, f.furnace_number 
                        FROM castings c 
                        JOIN furnaces f ON c.furnace_id = f.id 
                        WHERE c.id = ?
                    ");
                    $stmt->execute([$casting_id]);
                    $casting = $stmt->fetch();
                    
                    if (!$casting) {
                        json_response(['success' => false, 'message' => 'Döküm bulunamadı.'], 404);
                        break;
                    }
                    
                    // Kalite kontrol verilerini al
                    $qc = get_quality_control($casting_id);
                    
                    json_response([
                        'success' => true,
                        'data' => [
                            'casting' => $casting,
                            'quality_control' => $qc
                        ]
                    ]);
                    
                } catch (Exception $e) {
                    json_response(['success' => false, 'message' => 'Hata: ' . $e->getMessage()], 500);
                }
            }
            break;
            
        case 'save_quality_control':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['casting_id'])) {
                $casting_id = (int)$_GET['casting_id'];
                
                try {
                    // POST verilerini al
                    $data = $_POST;
                    
                    // Prova verisini JSON olarak decode et
                    if (isset($data['prova_data']) && is_string($data['prova_data'])) {
                        $data['prova_data'] = json_decode($data['prova_data'], true);
                    }
                    
                    // Mevcut kalite kontrol var mı kontrol et
                    $existing_qc = get_quality_control($casting_id);
                    
                    if ($existing_qc) {
                        // Güncelle
                        $stmt = $pdo->prepare("
                            UPDATE quality_controls SET
                                carbon_percentage = ?,
                                silicon_percentage = ?,
                                manganese_percentage = ?,
                                phosphorus_percentage = ?,
                                sulfur_percentage = ?,
                                chromium_percentage = ?,
                                nickel_percentage = ?,
                                copper_percentage = ?,
                                temperature = ?,
                                tested_by = ?,
                                remarks = ?,
                                prova_data = ?,
                                test_time = NOW(),
                                updated_at = NOW()
                            WHERE casting_id = ?
                        ");
                        
                        $stmt->execute([
                            $data['carbon_percentage'] ?? null,
                            $data['silicon_percentage'] ?? null,
                            $data['manganese_percentage'] ?? null,
                            $data['phosphorus_percentage'] ?? null,
                            $data['sulfur_percentage'] ?? null,
                            $data['chromium_percentage'] ?? null,
                            $data['nickel_percentage'] ?? null,
                            $data['copper_percentage'] ?? null,
                            $data['temperature'] ?? null,
                            $data['tested_by'] ?? null,
                            $data['remarks'] ?? null,
                            json_encode($data['prova_data'] ?? []),
                            $casting_id
                        ]);
                    } else {
                        // Yeni ekle
                        $stmt = $pdo->prepare("
                            INSERT INTO quality_controls (
                                casting_id, carbon_percentage, silicon_percentage, manganese_percentage,
                                phosphorus_percentage, sulfur_percentage, chromium_percentage,
                                nickel_percentage, copper_percentage, temperature,
                                tested_by, remarks, prova_data, test_time, created_at, updated_at
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW())
                        ");
                        
                        $stmt->execute([
                            $casting_id,
                            $data['carbon_percentage'] ?? null,
                            $data['silicon_percentage'] ?? null,
                            $data['manganese_percentage'] ?? null,
                            $data['phosphorus_percentage'] ?? null,
                            $data['sulfur_percentage'] ?? null,
                            $data['chromium_percentage'] ?? null,
                            $data['nickel_percentage'] ?? null,
                            $data['copper_percentage'] ?? null,
                            $data['temperature'] ?? null,
                            $data['tested_by'] ?? null,
                            $data['remarks'] ?? null,
                            json_encode($data['prova_data'] ?? [])
                        ]);
                    }
                    
                    json_response([
                        'success' => true,
                        'message' => 'Kalite kontrol değerleri başarıyla kaydedildi.'
                    ]);
                    
                } catch (Exception $e) {
                    json_response(['success' => false, 'message' => 'Kalite kontrol kaydedilirken hata: ' . $e->getMessage()], 500);
                }
            }
            break;
            
        case 'complete_day':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    $result = complete_day();
                    json_response($result);
                } catch (Exception $e) {
                    json_response(['success' => false, 'message' => 'Günü bitirme hatası: ' . $e->getMessage()], 500);
                }
            }
            break;
            
        case 'switch_furnace_in_set':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['furnace_set'])) {
                $furnace_set = (int)$_GET['furnace_set'];
                
                try {
                    $pdo->beginTransaction();
                    
                    // Set içindeki ocakları al
                    $stmt = $pdo->prepare("SELECT * FROM furnaces WHERE furnace_set = ? ORDER BY furnace_number");
                    $stmt->execute([$furnace_set]);
                    $furnaces = $stmt->fetchAll();
                    
                    if (count($furnaces) !== 2) {
                        throw new Exception('Set içinde 2 ocak olmalı.');
                    }
                    
                    // Aktif ve standby ocakları belirle
                    $activeFurnace = null;
                    $standbyFurnace = null;
                    
                    foreach ($furnaces as $furnace) {
                        if ($furnace['status'] === 'active') {
                            $activeFurnace = $furnace;
                        } elseif ($furnace['status'] === 'standby') {
                            $standbyFurnace = $furnace;
                        }
                    }
                    
                    if (!$activeFurnace || !$standbyFurnace) {
                        throw new Exception('Set içinde bir aktif ve bir standby ocak olmalı.');
                    }
                    
                    // Aktif ocaktaki devam eden dökümü tamamla
                    $stmt = $pdo->prepare("
                        SELECT id FROM castings 
                        WHERE furnace_id = ? AND status = 'in_progress'
                    ");
                    $stmt->execute([$activeFurnace['id']]);
                    $activeCasting = $stmt->fetch();
                    
                    if ($activeCasting) {
                        $stmt = $pdo->prepare("
                            UPDATE castings 
                            SET status = 'completed', end_time = NOW() 
                            WHERE id = ?
                        ");
                        $stmt->execute([$activeCasting['id']]);
                    }
                    
                    // Aktif ocağı standby yap
                    $stmt = $pdo->prepare("
                        UPDATE furnaces 
                        SET status = 'standby', is_charging = FALSE 
                        WHERE id = ?
                    ");
                    $stmt->execute([$activeFurnace['id']]);
                    
                    // Standby ocağı aktif yap
                    $stmt = $pdo->prepare("
                        UPDATE furnaces 
                        SET status = 'active', is_charging = TRUE 
                        WHERE id = ?
                    ");
                    $stmt->execute([$standbyFurnace['id']]);
                    
                    // Yeni aktif ocakta döküm başlat
                    $newCastingId = start_new_casting($standbyFurnace['id']);
                    
                    // Yeni döküm bilgilerini al
                    $stmt = $pdo->prepare("
                        SELECT casting_number_per_furnace, global_casting_number 
                        FROM castings WHERE id = ?
                    ");
                    $stmt->execute([$newCastingId]);
                    $newCasting = $stmt->fetch();
                    
                    $pdo->commit();
                    
                    json_response([
                        'success' => true,
                        'message' => "Set {$furnace_set}: {$standbyFurnace['furnace_number']}. Ocak aktif edildi (Döküm #{$newCasting['casting_number_per_furnace']}, Genel: #{$newCasting['global_casting_number']})",
                        'data' => [
                            'old_furnace' => $activeFurnace['furnace_number'],
                            'new_furnace' => $standbyFurnace['furnace_number'],
                            'new_casting_id' => $newCastingId,
                            'new_casting_number' => $newCasting['casting_number_per_furnace'],
                            'new_global_number' => $newCasting['global_casting_number']
                        ]
                    ]);
                    
                } catch (Exception $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    json_response(['success' => false, 'message' => 'Ocak değiştirme hatası: ' . $e->getMessage()], 500);
                }
            }
            break;
            
        default:
            json_response(['success' => false, 'message' => 'Geçersiz işlem'], 400);
    }
}

// Yeni döküm başlat
function start_new_casting($furnace_id) {
    global $pdo;
    
    // Mevcut döküm var mı kontrol et
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM castings WHERE furnace_id = ? AND status = 'in_progress'");
    $stmt->execute([$furnace_id]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Bu ocakta zaten devam eden bir döküm var.');
    }
    
    // Genel döküm numarası (güvenli şekilde)
    $stmt = $pdo->query("SELECT COALESCE(MAX(global_casting_number), 0) + 1 FROM castings");
    $global_number = $stmt->fetchColumn();
    
    // Ocak bazında döküm numarası
    $stmt = $pdo->prepare("SELECT COUNT(*) + 1 FROM castings WHERE furnace_id = ?");
    $stmt->execute([$furnace_id]);
    $furnace_number = $stmt->fetchColumn();
    
    // Yeni döküm ekle - start_time'ı açık şekilde NOW() olarak belirt
    $current_time = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("
        INSERT INTO castings (furnace_id, casting_number_per_furnace, global_casting_number, start_time, production_date, status) 
        VALUES (?, ?, ?, ?, ?, 'in_progress')
    ");
    $stmt->execute([$furnace_id, $furnace_number, $global_number, $current_time, today()]);
    
    $new_casting_id = $pdo->lastInsertId();
    
    // Ocağı charging durumuna getir
    $stmt = $pdo->prepare("UPDATE furnaces SET is_charging = TRUE WHERE id = ?");
    $stmt->execute([$furnace_id]);
    
    return $new_casting_id;
}

// Ocağı bakıma gönder
function send_furnace_to_maintenance($furnace_id) {
    global $pdo;
    
    $pdo->beginTransaction();
    
    try {
        // Ocak bilgilerini al
        $stmt = $pdo->prepare("SELECT * FROM furnaces WHERE id = ?");
        $stmt->execute([$furnace_id]);
        $furnace = $stmt->fetch();
        
        if (!$furnace) {
            throw new Exception('Ocak bulunamadı.');
        }
        
        // Ocağı bakıma gönder
        $stmt = $pdo->prepare("
            UPDATE furnaces 
            SET status = 'maintenance', 
                is_charging = FALSE, 
                last_maintenance_date = NOW(), 
                total_castings = 0 
            WHERE id = ?
        ");
        $stmt->execute([$furnace_id]);
        
        // Aynı setteki yedek ocağı aktif et
        $stmt = $pdo->prepare("
            UPDATE furnaces 
            SET status = 'active' 
            WHERE furnace_set = ? AND id != ? AND status = 'standby' 
            LIMIT 1
        ");
        $stmt->execute([$furnace['furnace_set'], $furnace_id]);
        
        // Yeni aktif ocakta döküm başlat
        $stmt = $pdo->prepare("
            SELECT id FROM furnaces 
            WHERE furnace_set = ? AND id != ? AND status = 'active' 
            LIMIT 1
        ");
        $stmt->execute([$furnace['furnace_set'], $furnace_id]);
        $new_active_furnace = $stmt->fetchColumn();
        
        if ($new_active_furnace) {
            start_new_casting($new_active_furnace);
        }
        
        $pdo->commit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Sistem kurulumu
function setup_initial_system() {
    global $pdo;
    
    $pdo->beginTransaction();
    
    try {
        // Mevcut verileri temizle
        $pdo->exec("DELETE FROM quality_controls");
        $pdo->exec("DELETE FROM castings");
        $pdo->exec("DELETE FROM daily_reports");
        $pdo->exec("DELETE FROM furnaces");
        
        // 6 ocak oluştur
        $furnaces = [
            [1, 1, 'active'],
            [2, 1, 'standby'],
            [3, 2, 'active'],
            [4, 2, 'standby'],
            [5, 3, 'active'],
            [6, 3, 'standby']
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO furnaces (furnace_number, furnace_set, status, total_castings, max_castings_before_maintenance, is_charging) 
            VALUES (?, ?, ?, 0, 30, ?)
        ");
        
        foreach ($furnaces as $furnace) {
            $is_charging = ($furnace[2] === 'active');
            $stmt->execute([$furnace[0], $furnace[1], $furnace[2], $is_charging]);
        }
        
        // Aktif ocaklarda döküm başlat
        $active_furnaces = [1, 3, 5]; // Furnace ID'leri
        foreach ($active_furnaces as $furnace_id) {
            start_new_casting($furnace_id);
        }
        
        $pdo->commit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Günlük rapor oluştur
function generate_daily_report() {
    global $pdo;
    
    $date = today();
    
    // Bugünkü döküm sayısı
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM castings WHERE production_date = ?");
    $stmt->execute([$date]);
    $total_castings = $stmt->fetchColumn();
    
    // Aktif ocak sayısı
    $stmt = $pdo->query("SELECT COUNT(*) FROM furnaces WHERE status = 'active'");
    $active_furnaces = $stmt->fetchColumn();
    
    // Ocak bazında döküm sayıları
    $stmt = $pdo->prepare("
        SELECT f.furnace_number, COUNT(c.id) as casting_count 
        FROM furnaces f 
        LEFT JOIN castings c ON f.id = c.furnace_id AND c.production_date = ? 
        GROUP BY f.id, f.furnace_number
    ");
    $stmt->execute([$date]);
    $furnace_castings = [];
    while ($row = $stmt->fetch()) {
        $furnace_castings[$row['furnace_number']] = $row['casting_count'];
    }
    
    // Verimlilik hesaplama (3 ocak x 12 döküm = 36 döküm/gün hedef)
    $expected_castings = 36;
    $efficiency = $total_castings > 0 ? ($total_castings / $expected_castings) * 100 : 0;
    
    // Raporu kaydet
    $stmt = $pdo->prepare("
        INSERT INTO daily_reports (report_date, total_castings, furnace_castings, active_furnaces_count, production_efficiency) 
        VALUES (?, ?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE 
        total_castings = VALUES(total_castings),
        furnace_castings = VALUES(furnace_castings),
        active_furnaces_count = VALUES(active_furnaces_count),
        production_efficiency = VALUES(production_efficiency)
    ");
    
    $stmt->execute([
        $date,
        $total_castings,
        json_encode($furnace_castings),
        $active_furnaces,
        $efficiency
    ]);
}

// Otomatik döküm tamamlama (cron job için)
function auto_complete_castings() {
    global $pdo;
    
    // GEÇİCİ OLARAK DEVRE DIŞI - TEST İÇİN
    error_log("⚠️ auto_complete_castings fonksiyonu geçici olarak devre dışı bırakıldı.");
    return;
    
    // 120 dakikayı geçen dökümler
    $stmt = $pdo->query("
        SELECT * FROM castings 
        WHERE status = 'in_progress' 
        AND TIMESTAMPDIFF(MINUTE, start_time, NOW()) >= 120
    ");
    
    $overdue_castings = $stmt->fetchAll();
    
    foreach ($overdue_castings as $casting) {
        try {
            $pdo->beginTransaction();
            
            // Döküm tamamla - güvenli şekilde
            $current_end_time = date('Y-m-d H:i:s');
            $stmt = $pdo->prepare("UPDATE castings SET status = 'completed', end_time = ? WHERE id = ? AND status = 'in_progress'");
            $stmt->execute([$current_end_time, $casting['id']]);
            
            // Ocağın toplam döküm sayısını artır
            $stmt = $pdo->prepare("UPDATE furnaces SET total_castings = total_castings + 1 WHERE id = ?");
            $stmt->execute([$casting['furnace_id']]);
            
            // Yeni döküm başlat
            start_new_casting($casting['furnace_id']);
            
            $pdo->commit();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Otomatik döküm tamamlama hatası: " . $e->getMessage());
        }
    }
}

// Günü Bitir - Kapsamlı günlük rapor oluştur
function complete_day() {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        $report_date = today();
        
        // 1. SADECE TAMAMLANAN DÖKÜMLERİ RAPORLA
        // Devam eden dökümlere (in_progress) dokunma!
        
        // 2. SADECE TAMAMLANAN dökümleri detaylı bilgiyle al (in_progress olanları alma)
        $stmt = $pdo->prepare("
            SELECT 
                c.*,
                f.furnace_number,
                f.furnace_set,
                qc.carbon_percentage,
                qc.silicon_percentage,
                qc.manganese_percentage,
                qc.phosphorus_percentage,
                qc.sulfur_percentage,
                qc.chromium_percentage,
                qc.nickel_percentage,
                qc.copper_percentage,
                qc.prova_data,
                qc.temperature,
                qc.remarks,
                TIMESTAMPDIFF(MINUTE, c.start_time, COALESCE(c.end_time, NOW())) as duration_minutes
            FROM castings c
            JOIN furnaces f ON c.furnace_id = f.id
            LEFT JOIN quality_controls qc ON c.id = qc.casting_id
            WHERE c.production_date = ? AND c.status = 'completed'
            ORDER BY c.global_casting_number
        ");
        $stmt->execute([$report_date]);
        $castings = $stmt->fetchAll();
        
        // 3. İstatistikleri hesapla
        $total_castings = count($castings);
        $completed_castings = 0;
        $delayed_castings = 0;
        $total_production_time = 0;
        $furnace_stats = [];
        $delayed_details = [];
        $fastest_casting = null;
        $slowest_casting = null;
        
        foreach ($castings as $casting) {
            $duration = (int)$casting['duration_minutes'];
            $furnace_num = $casting['furnace_number'];
            
            if ($casting['status'] === 'completed') {
                $completed_castings++;
            }
            
            $total_production_time += $duration;
            
            // Gecikme kontrolü (120 dk üzeri)
            if ($duration > 120) {
                $delayed_castings++;
                $delayed_details[] = [
                    'furnace' => $furnace_num,
                    'casting_number' => $casting['casting_number_per_furnace'],
                    'duration' => $duration,
                    'delay' => $duration - 120,
                    'remarks' => $casting['remarks']
                ];
            }
            
            // Ocak bazında istatistikler
            if (!isset($furnace_stats[$furnace_num])) {
                $furnace_stats[$furnace_num] = [
                    'total_castings' => 0,
                    'total_time' => 0,
                    'delayed_count' => 0,
                    'completed_count' => 0
                ];
            }
            
            $furnace_stats[$furnace_num]['total_castings']++;
            $furnace_stats[$furnace_num]['total_time'] += $duration;
            
            if ($duration > 120) {
                $furnace_stats[$furnace_num]['delayed_count']++;
            }
            
            if ($casting['status'] === 'completed') {
                $furnace_stats[$furnace_num]['completed_count']++;
            }
            
            // En hızlı/yavaş döküm
            if ($duration > 0) {
                if (!$fastest_casting || $duration < $fastest_casting['duration']) {
                    $fastest_casting = [
                        'furnace' => $furnace_num,
                        'casting_number' => $casting['casting_number_per_furnace'],
                        'duration' => $duration
                    ];
                }
                
                if (!$slowest_casting || $duration > $slowest_casting['duration']) {
                    $slowest_casting = [
                        'furnace' => $furnace_num,
                        'casting_number' => $casting['casting_number_per_furnace'],
                        'duration' => $duration
                    ];
                }
            }
        }
        
        // Ortalama süre hesapla
        $average_time = $completed_castings > 0 ? round($total_production_time / $completed_castings, 2) : 0;
        
        // Verimlilik hesapla (her ocaktan günde 12 döküm bekleniyor, 3 aktif ocak = 36 döküm)
        $expected_daily_castings = 36;
        $efficiency = $total_castings > 0 ? round(($total_castings / $expected_daily_castings) * 100, 2) : 0;
        
        // Ocak bazında ortalamalar
        foreach ($furnace_stats as $furnace_num => $stats) {
            $furnace_stats[$furnace_num]['average_time'] = $stats['completed_count'] > 0 
                ? round($stats['total_time'] / $stats['completed_count'], 2) 
                : 0;
        }
        
        // Rapor verisini JSON olarak hazırla
        $report_data = [
            'castings' => $castings,
            'furnace_stats' => $furnace_stats,
            'delayed_details' => $delayed_details,
            'fastest_casting' => $fastest_casting,
            'slowest_casting' => $slowest_casting,
            'efficiency' => $efficiency,
            'expected_daily_castings' => $expected_daily_castings,
            'statistics' => [
                'total_castings' => $total_castings,
                'completed_castings' => $completed_castings,
                'delayed_castings' => $delayed_castings,
                'total_production_time' => $total_production_time,
                'average_time' => $total_castings > 0 ? round($total_production_time / $total_castings, 1) : 0,
                'fastest_casting' => $fastest_casting,
                'slowest_casting' => $slowest_casting,
                'efficiency' => $efficiency
            ]
        ];
        
        // 4. Günlük raporu kaydet
        $stmt = $pdo->prepare("
            INSERT INTO daily_reports 
            (report_date, total_castings, completed_castings, delayed_castings, total_production_time, average_casting_time, report_data) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            total_castings = VALUES(total_castings),
            completed_castings = VALUES(completed_castings),
            delayed_castings = VALUES(delayed_castings),
            total_production_time = VALUES(total_production_time),
            average_casting_time = VALUES(average_casting_time),
            report_data = VALUES(report_data)
        ");
        
        $stmt->execute([
            $report_date,
            $total_castings,
            $completed_castings,
            $delayed_castings,
            $total_production_time,
            $average_time,
            json_encode($report_data, JSON_UNESCAPED_UNICODE)
        ]);
        
        $report_id = $pdo->lastInsertId();
        if (!$report_id) {
            // Eğer ON DUPLICATE KEY UPDATE çalıştıysa, mevcut ID'yi al
            $stmt = $pdo->prepare("SELECT id FROM daily_reports WHERE report_date = ?");
            $stmt->execute([$report_date]);
            $report_id = $stmt->fetchColumn();
        }
        
        // 5. Ocak bazında performans raporunu kaydet
        $pdo->exec("DELETE FROM report_furnace_performance WHERE report_id = {$report_id}");
        
        foreach ($furnace_stats as $furnace_num => $stats) {
            $stmt = $pdo->prepare("
                SELECT id FROM furnaces WHERE furnace_number = ?
            ");
            $stmt->execute([$furnace_num]);
            $furnace_id = $stmt->fetchColumn();
            
            if ($furnace_id) {
                $stmt = $pdo->prepare("
                    INSERT INTO report_furnace_performance 
                    (report_id, furnace_id, furnace_number, total_castings, total_time, average_time, delayed_count) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $report_id,
                    $furnace_id,
                    $furnace_num,
                    $stats['total_castings'],
                    $stats['total_time'],
                    $stats['average_time'],
                    $stats['delayed_count']
                ]);
            }
        }
        
        // 6. Raporlanan dökümleri listeden kaldır (production_date'i değiştir)
        // Bu sayede get_todays_castings fonksiyonu sadece bugünkü dökümleri gösterir
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $stmt = $pdo->prepare("
            UPDATE castings 
            SET production_date = ? 
            WHERE production_date = ? AND status = 'completed'
        ");
        $stmt->execute([$yesterday, $report_date]);
        
        $pdo->commit();
        
        return [
            'success' => true,
            'report_id' => $report_id,
            'total_castings' => $total_castings,
            'completed_castings' => $completed_castings,
            'delayed_castings' => $delayed_castings,
            'average_time' => $average_time,
            'efficiency' => $efficiency
        ];
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}
?>
