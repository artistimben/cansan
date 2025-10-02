<?php
/**
 * Çelik Fabrikası Yönetim Sistemi - Ana Sayfa
 * XAMPP için basitleştirilmiş versiyon
 */

require_once 'config.php';
require_once 'functions.php';

// AJAX isteklerini handle et
if (isset($_GET['action'])) {
    handle_ajax_request($_GET['action']);
}

// Ana dashboard verilerini getir
$activeFurnaces = get_active_furnaces();
$activeCastings = get_active_castings();
$todaysCastings = get_todays_castings();
$dailyStats = get_daily_stats();
$maintenanceNeeded = get_furnaces_needing_maintenance();
$furnacesBySet = get_all_furnaces_by_set();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #1e3a8a;
            --secondary-color: #64748b;
            --accent-color: #dc2626;
            --success-color: #16a34a;
            --warning-color: #ca8a04;
            --info-color: #0284c7;
            --light-bg: #f8fafc;
            --dark-text: #1e293b;
            --border-color: #e2e8f0;
        }

        body {
            background-color: var(--light-bg);
            color: var(--dark-text);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, #3b82f6 100%);
            box-shadow: 0 2px 10px rgba(30, 58, 138, 0.1);
        }

        .card {
            border: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .card-header {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #94a3b8 100%);
            color: white;
            font-weight: 600;
            border-bottom: none;
        }

        .furnace-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .furnace-active {
            border-left: 5px solid var(--success-color);
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        }

        .furnace-maintenance {
            border-left: 5px solid var(--accent-color);
            background: linear-gradient(135deg, #fef2f2 0%, #fecaca 100%);
        }

        .furnace-standby {
            border-left: 5px solid var(--secondary-color);
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }

        .progress {
            height: 8px;
            border-radius: 4px;
            background-color: #e2e8f0;
        }

        .progress-bar {
            border-radius: 4px;
            transition: width 0.6s ease;
        }

        .casting-table th {
            background: linear-gradient(135deg, var(--primary-color) 0%, #3b82f6 100%);
            color: white;
            font-weight: 600;
            border: none;
            padding: 12px 8px;
        }

        .casting-table td {
            vertical-align: middle;
            padding: 10px 8px;
            border-bottom: 1px solid var(--border-color);
        }

        .casting-table tbody tr:hover {
            background-color: #f1f5f9;
        }

        .status-badge {
            font-size: 0.8rem;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            border-left: 4px solid;
        }

        .stat-card.primary { border-left-color: var(--primary-color); }
        .stat-card.success { border-left-color: var(--success-color); }
        .stat-card.warning { border-left-color: var(--warning-color); }
        .stat-card.info { border-left-color: var(--info-color); }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--secondary-color);
            font-weight: 600;
        }

        /* Prova Kayıtları Stilleri */
        .prova-item {
            background: linear-gradient(135deg, #fefce8 0%, #fef9c3 100%);
            border-left: 4px solid var(--warning-color);
            transition: all 0.3s ease;
        }

        .prova-item:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            transform: translateX(5px);
        }

        .prova-item .form-label-sm {
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--dark-text);
        }

        .prova-item .form-control-sm {
            font-size: 0.875rem;
            padding: 0.25rem 0.5rem;
            height: calc(1.5em + 0.5rem + 2px);
        }

        #prova-list .text-muted {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            border: 2px dashed var(--border-color);
        }

        /* Inline Prova Display */
        .prova-display {
            font-size: 0.7rem !important;
            background: #fef9c3 !important;
            padding: 3px 8px !important;
            border-radius: 4px !important;
            border-left: 3px solid #ca8a04 !important;
            margin-bottom: 2px !important;
            display: inline-block;
            width: 100%;
        }

        .prova-form-container {
            animation: slideDown 0.3s ease-out;
        }
        
        /* Responsive prova formu */
        @media (max-width: 1200px) {
            .prova-form-container .row {
                flex-direction: column;
                align-items: stretch !important;
            }
            
            .prova-form-container .col-auto {
                width: 100% !important;
                margin-bottom: 10px;
            }
            
            .prova-form-container .col-auto:last-child {
                margin-bottom: 0;
            }
            
            .prova-form-container input {
                width: 100% !important;
                max-width: 200px;
            }
            
            .prova-form-container .btn {
                width: 100%;
                max-width: 200px;
            }
        }
        
        @media (max-width: 768px) {
            .prova-form-container {
                padding: 10px !important;
            }
            
            .prova-form-container .row {
                gap: 8px;
            }
            
            .prova-form-container input {
                font-size: 0.9rem;
                padding: 8px 12px;
            }
            
            .prova-form-container label {
                font-size: 0.8rem;
                margin-bottom: 4px;
            }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .prova-form-container input {
            font-size: 0.85rem;
            transition: all 0.2s ease;
            border: 2px solid #d1d5db;
        }

        .prova-form-container input:focus {
            border-color: #16a34a !important;
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.2) !important;
            background-color: #f0fdf4;
            transform: scale(1.05);
        }

        .prova-form-container input:hover {
            border-color: #84cc16;
        }

        .prova-form-container label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #16a34a;
            text-transform: uppercase;
        }
        
        /* Klavye kısayol bilgisi */
        .keyboard-hint {
            font-size: 0.7rem;
            color: #6b7280;
            font-style: italic;
            margin-top: 5px;
        }
        
        .keyboard-hint kbd {
            background: #374151;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.7rem;
            font-family: monospace;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            border: 1px solid #1f2937;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .charging {
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-fire"></i>
                <?= APP_NAME ?>
            </a>
            
            <div class="navbar-nav ms-auto">
                <button class="btn btn-warning me-3" onclick="completeDayConfirm()" style="font-weight: 600;">
                    <i class="bi bi-calendar-check"></i> Günü Bitir
                </button>
                <a href="reports.php" class="btn btn-outline-light me-3">
                    <i class="bi bi-file-earmark-text"></i> Günlük Raporlar
                </a>
                <a href="general_reports.php" class="btn btn-outline-light me-3">
                    <i class="bi bi-graph-up"></i> Genel Raporlar
                </a>
                <span class="navbar-text">
                    <i class="bi bi-clock"></i>
                    <span id="current-time"><?= date('d.m.Y H:i:s') ?></span>
                </span>
            </div>
        </div>
    </nav>

    <!-- Ana İçerik -->
    <main class="container-fluid py-4">
        <div class="row">
            <!-- Sistem Durumu İstatistikleri -->
            <div class="col-12 mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="stat-card primary">
                            <div class="stat-number"><?= count($activeFurnaces) ?></div>
                            <div class="stat-label">Aktif Ocak</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card success">
                            <div class="stat-number"><?= count($activeCastings) ?></div>
                            <div class="stat-label">Devam Eden Döküm</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card info">
                            <div class="stat-number"><?= $dailyStats['total_castings'] ?></div>
                            <div class="stat-label">Bugünkü Toplam Döküm</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card warning">
                            <div class="stat-number"><?= count($maintenanceNeeded) ?></div>
                            <div class="stat-label">Bakım Gerekli</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aktif Ocaklar -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-fire"></i>
                            Aktif Ocaklar ve Döküm Durumları
                        </h5>
                        <button class="btn btn-light btn-sm" onclick="location.reload()">
                            <i class="bi bi-arrow-clockwise"></i>
                            Yenile
                        </button>
                    </div>
                    <div class="card-body">
                        <?php if (empty($activeFurnaces)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-exclamation-triangle text-warning" style="font-size: 2rem;"></i>
                                <p class="mt-2 text-muted">Aktif ocak bulunamadı.</p>
                                <button class="btn btn-primary" onclick="setupSystem()">
                                    Sistem Kurulumu Yap
                                </button>
                            </div>
                        <?php else: ?>
                            <?php foreach ($activeFurnaces as $furnace): ?>
                                <?php $currentCasting = get_current_casting($furnace['id']); ?>
                                <div class="furnace-card card mb-3 furnace-<?= strtolower($furnace['status']) ?> <?= $furnace['is_charging'] ? 'charging' : '' ?>">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-2">
                                                <h4 class="mb-0 text-primary">
                                                    <i class="bi bi-gear-wide-connected"></i>
                                                    <?= $furnace['furnace_number'] ?>. Ocak
                                                </h4>
                                                <small class="text-muted">Set <?= $furnace['furnace_set'] ?></small>
                                            </div>
                                            
                                            <div class="col-md-4">
                                                <?php if ($currentCasting): ?>
                                                    <div class="mb-2">
                                                        <strong><?= $currentCasting['casting_number_per_furnace'] ?>. Döküm</strong>
                                                        <span class="badge bg-info ms-2">Genel: #<?= $currentCasting['global_casting_number'] ?></span>
                                                    </div>
                                                    <div class="mb-2">
                                                        <small class="text-muted">
                                                            Başlangıç: <?= date('H:i', strtotime($currentCasting['start_time'])) ?> |
                                                            Tahmini Bitiş: <?= date('H:i', strtotime($currentCasting['start_time'] . ' +120 minutes')) ?>
                                                        </small>
                                                    </div>
                                                    <?php 
                                                        $elapsedMinutes = (time() - strtotime($currentCasting['start_time'])) / 60;
                                                        $progress = min(100, ($elapsedMinutes / 120) * 100);
                                                        $remainingMinutes = max(0, 120 - $elapsedMinutes);
                                                    ?>
                                                    <div class="progress mb-2">
                                                        <div class="progress-bar bg-success" style="width: <?= $progress ?>%"></div>
                                                    </div>
                                                    <small class="text-muted">
                                                        Kalan Süre: <?= round($remainingMinutes) ?> dakika
                                                        <?php if ($elapsedMinutes > 125): ?>
                                                            <span class="text-danger">⚠️ GECİKME</span>
                                                        <?php endif; ?>
                                                    </small>
                                                <?php else: ?>
                                                    <div class="text-muted">
                                                        <i class="bi bi-pause-circle"></i>
                                                        Döküm bekleniyor
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="col-md-3">
                                                <div class="mb-2">
                                                    <small class="text-muted">Toplam Döküm:</small>
                                                    <strong class="d-block"><?= $furnace['total_castings'] ?>/<?= $furnace['max_castings_before_maintenance'] ?></strong>
                                                </div>
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar bg-warning" 
                                                         style="width: <?= ($furnace['total_castings'] / $furnace['max_castings_before_maintenance']) * 100 ?>%"></div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-3 text-end">
                                                <div class="btn-group-vertical" role="group">
                                                    <?php if ($currentCasting): ?>
                                                        <button class="btn btn-success btn-sm mb-1" 
                                                                onclick="completeCasting(<?= $currentCasting['id'] ?>)">
                                                            <i class="bi bi-check-circle"></i>
                                                            Dökümü Tamamla
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="btn btn-primary btn-sm mb-1" 
                                                                onclick="startCasting(<?= $furnace['id'] ?>)">
                                                            <i class="bi bi-play-circle"></i>
                                                            Döküm Başlat
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <?php 
                                                    // Set içinde standby ocak varsa değiştirme butonu göster
                                                    $set = $furnace['furnace_set'];
                                                    $hasStandbyInSet = false;
                                                    $standbyFurnaceNumber = '';
                                                    if (isset($furnacesBySet[$set])) {
                                                        foreach ($furnacesBySet[$set] as $f) {
                                                            if ($f['status'] === 'standby') {
                                                                $hasStandbyInSet = true;
                                                                $standbyFurnaceNumber = $f['furnace_number'];
                                                                break;
                                                            }
                                                        }
                                                    }
                                                    if ($hasStandbyInSet): ?>
                                                        <button class="btn btn-info btn-sm mb-1" 
                                                                onclick="switchFurnaceInSet(<?= $set ?>, <?= $furnace['furnace_number'] ?>, <?= $standbyFurnaceNumber ?>)"
                                                                title="Set <?= $set ?>: <?= $standbyFurnaceNumber ?>. Ocak'a geç">
                                                            <i class="bi bi-arrow-left-right"></i>
                                                            <?= $standbyFurnaceNumber ?>. Ocak'a Geç
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($furnace['total_castings'] >= $furnace['max_castings_before_maintenance']): ?>
                                                        <button class="btn btn-danger btn-sm" 
                                                                onclick="sendToMaintenance(<?= $furnace['id'] ?>)">
                                                            <i class="bi bi-tools"></i>
                                                            Bakıma Gönder
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Günlük İstatistikler -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-graph-up"></i>
                            Günlük İstatistikler
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <h3 class="text-success"><?= $dailyStats['completed_castings'] ?></h3>
                                <small class="text-muted">Tamamlanan</small>
                            </div>
                            <div class="col-6 mb-3">
                                <h3 class="text-info"><?= $dailyStats['in_progress_castings'] ?></h3>
                                <small class="text-muted">Devam Eden</small>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary" onclick="generateDailyReport()">
                                <i class="bi bi-file-earmark-text"></i>
                                Günlük Rapor Oluştur
                            </button>
                            <button class="btn btn-outline-success" onclick="openQualityControlModal()">
                                <i class="bi bi-clipboard-check"></i>
                                Kalite Kontrol
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Bakım Gerekli Ocaklar -->
                <?php if (!empty($maintenanceNeeded)): ?>
                    <div class="card mt-3">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0">
                                <i class="bi bi-exclamation-triangle"></i>
                                Bakım Gerekli Ocaklar
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php foreach ($maintenanceNeeded as $furnace): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span><?= $furnace['furnace_number'] ?>. Ocak</span>
                                    <span class="badge bg-warning text-dark">
                                        <?= $furnace['total_castings'] ?>/<?= $furnace['max_castings_before_maintenance'] ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Döküm Listesi -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-list-ol"></i>
                            Bugünkü Döküm Listesi (Genel Sıralama)
                        </h5>
                        <button class="btn btn-outline-primary btn-sm" onclick="location.reload()">
                            <i class="bi bi-arrow-clockwise"></i>
                            Yenile
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover casting-table">
                                <thead>
                                    <tr>
                                        <th>Genel Sıra</th>
                                        <th>Ocak</th>
                                        <th>Ocak Dökümü</th>
                                        <th>Başlangıç</th>
                                        <th>Döküm Süresi</th>
                                        <th>İlerleme</th>
                                        <th>Kalite</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($todaysCastings)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                                <p class="mt-2 text-muted">Bugün henüz döküm yapılmamış.</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($todaysCastings as $casting): ?>
                                            <tr data-casting-id="<?= $casting['id'] ?>">
                                                <td>
                                                    <strong class="text-primary">#<?= $casting['global_casting_number'] ?></strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary"><?= $casting['furnace_number'] ?>. Ocak</span>
                                                </td>
                                                <td><?= $casting['casting_number_per_furnace'] ?>. Döküm</td>
                                                <td><?= date('H:i', strtotime($casting['start_time'])) ?></td>
                                                <td>
                                                    <?php if ($casting['status'] === 'in_progress'): ?>
                                                        <span class="status-badge bg-info text-white">Devam Ediyor</span>
                                                    <?php elseif ($casting['status'] === 'completed'): ?>
                                                        <?php 
                                                            // Tamamlanan döküm için gerçek süreyi hesapla
                                                            if ($casting['end_time'] && $casting['end_time'] != '0000-00-00 00:00:00') {
                                                                $startTime = strtotime($casting['start_time']);
                                                                $endTime = strtotime($casting['end_time']);
                                                                $durationSeconds = $endTime - $startTime;
                                                                $durationMinutes = round($durationSeconds / 60);
                                                                
                                                                if ($durationMinutes > 0) {
                                                                    $hours = floor($durationMinutes / 60);
                                                                    $minutes = $durationMinutes % 60;
                                                                    
                                                                    // Gecikme kontrolü (120 dakikadan fazla ise)
                                                                    if ($durationMinutes > 120) {
                                                                        $delayMinutes = $durationMinutes - 120;
                                                                        echo "<div class='text-warning mb-1'>";
                                                                        echo "<strong>Toplam: " . ($hours > 0 ? $hours . " saat " : "") . $minutes . " dk</strong>";
                                                                        echo " <span class='badge bg-warning text-dark'>+" . $delayMinutes . " dk gecikme</span>";
                                                                        echo "</div>";
                                                                        echo "<small class='text-muted'>";
                                                                        echo "Başlangıç: " . date('H:i', $startTime) . "<br>";
                                                                        echo "Bitiş: " . date('H:i', $endTime) . "<br>";
                                                                        echo "<button class='btn btn-sm btn-outline-warning mt-1' onclick='askDelayReason(" . $casting['id'] . ", " . $delayMinutes . ")'>";
                                                                        echo "<i class='bi bi-question-circle'></i> Gecikme Nedeni";
                                                                        echo "</button>";
                                                                        echo "</small>";
                                                                    } else {
                                                                        echo "<div class='text-success mb-1'>";
                                                                        echo "<strong>Toplam: " . ($hours > 0 ? $hours . " saat " : "") . $minutes . " dk</strong>";
                                                                        echo "</div>";
                                                                        echo "<small class='text-muted'>";
                                                                        echo "Başlangıç: " . date('H:i', $startTime) . "<br>";
                                                                        echo "Bitiş: " . date('H:i', $endTime);
                                                                        echo "</small>";
                                                                    }
                                                                } else {
                                                                    echo "<span class='status-badge bg-danger text-white'>Süre Hatası</span>";
                                                                }
                                                            } else {
                                                                echo "<span class='status-badge bg-success text-white'>Tamamlandı</span>";
                                                            }
                                                        ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($casting['status'] === 'in_progress'): ?>
                                                        <?php 
                                                            $elapsedMinutes = (time() - strtotime($casting['start_time'])) / 60;
                                                            $progress = min(100, ($elapsedMinutes / 120) * 100);
                                                            $remainingMinutes = max(0, 120 - $elapsedMinutes);
                                                        ?>
                                                        <div class="progress" style="height: 20px;">
                                                            <div class="progress-bar bg-info" 
                                                                 style="width: <?= $progress ?>%"
                                                                 title="<?= round($progress) ?>%">
                                                                <?= round($progress) ?>%
                                                            </div>
                                                        </div>
                                                        <small class="text-muted"><?= round($remainingMinutes) ?> dk kaldı</small>
                                                    <?php else: ?>
                                                        <span class="text-success">✓ Tamamlandı</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php $qc = get_quality_control($casting['id']); ?>
                                                    <div class="d-flex flex-column gap-1">
                                                        <?php if ($qc && $qc['prova_data']): ?>
                                                            <?php 
                                                                $provas = json_decode($qc['prova_data'], true);
                                                                if ($provas && is_array($provas)):
                                                                    foreach ($provas as $index => $prova):
                                                            ?>
                                                                <div class="prova-display" style="font-size: 0.75rem; background: #fef9c3; padding: 2px 6px; border-radius: 4px;">
                                                                    <strong><?= $index + 1 ?>.PROVA:</strong>
                                                                    C:<?= $prova['c'] ?: '-' ?> 
                                                                    SI:<?= $prova['si'] ?: '-' ?> 
                                                                    MN:<?= $prova['mn'] ?: '-' ?> 
                                                                    S:<?= $prova['s'] ?: '-' ?> 
                                                                    P:<?= $prova['p'] ?: '-' ?> 
                                                                    CU:<?= $prova['cu'] ?: '-' ?>
                                                                </div>
                                                            <?php 
                                                                    endforeach;
                                                                endif;
                                                            ?>
                                                        <?php endif; ?>
                                                        <button class="btn btn-outline-success btn-sm" 
                                                                onclick="toggleProvaForm(<?= $casting['id'] ?>)">
                                                            <i class="bi bi-plus-circle"></i> Prova Ekle
                                                        </button>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <?php if ($casting['status'] === 'in_progress'): ?>
                                                            <button class="btn btn-success btn-sm" 
                                                                    onclick="completeCasting(<?= $casting['id'] ?>)"
                                                                    title="Dökümü Tamamla">
                                                                <i class="bi bi-check-circle"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <!-- Prova Ekleme Formu (Gizli) -->
                                            <tr id="prova-form-<?= $casting['id'] ?>" class="prova-form-row" style="display: none;">
                                                <td colspan="8" class="p-0">
                                                    <div class="prova-form-container" style="background: #f0fdf4; padding: 15px; border-left: 4px solid #16a34a;">
                                                        <div class="row align-items-end g-2">
                                                            <div class="col-12 col-lg-auto">
                                                                <strong class="d-block mb-2">YENİ PROVA:</strong>
                                                            </div>
                                                            
                                                            <!-- Desktop: Yan yana, Mobile: Alt alta -->
                                                            <div class="col-6 col-md-4 col-lg-auto">
                                                                <label class="form-label form-label-sm mb-1">C</label>
                                                                <input type="number" class="form-control form-control-sm prova-input" 
                                                                       id="prova-c-<?= $casting['id'] ?>" 
                                                                       data-casting="<?= $casting['id'] ?>"
                                                                       data-field="c"
                                                                       placeholder="0.00" step="0.01" style="width: 70px;"
                                                                       onkeydown="handleProvaKeyNav(event, <?= $casting['id'] ?>, 'c')">
                                                            </div>
                                                            <div class="col-6 col-md-4 col-lg-auto">
                                                                <label class="form-label form-label-sm mb-1">SI</label>
                                                                <input type="number" class="form-control form-control-sm prova-input" 
                                                                       id="prova-si-<?= $casting['id'] ?>" 
                                                                       data-casting="<?= $casting['id'] ?>"
                                                                       data-field="si"
                                                                       placeholder="0.00" step="0.01" style="width: 70px;"
                                                                       onkeydown="handleProvaKeyNav(event, <?= $casting['id'] ?>, 'si')">
                                                            </div>
                                                            <div class="col-6 col-md-4 col-lg-auto">
                                                                <label class="form-label form-label-sm mb-1">MN</label>
                                                                <input type="number" class="form-control form-control-sm prova-input" 
                                                                       id="prova-mn-<?= $casting['id'] ?>" 
                                                                       data-casting="<?= $casting['id'] ?>"
                                                                       data-field="mn"
                                                                       placeholder="0.00" step="0.01" style="width: 70px;"
                                                                       onkeydown="handleProvaKeyNav(event, <?= $casting['id'] ?>, 'mn')">
                                                            </div>
                                                            <div class="col-6 col-md-4 col-lg-auto">
                                                                <label class="form-label form-label-sm mb-1">S</label>
                                                                <input type="number" class="form-control form-control-sm prova-input" 
                                                                       id="prova-s-<?= $casting['id'] ?>" 
                                                                       data-casting="<?= $casting['id'] ?>"
                                                                       data-field="s"
                                                                       placeholder="0.00" step="0.01" style="width: 70px;"
                                                                       onkeydown="handleProvaKeyNav(event, <?= $casting['id'] ?>, 's')">
                                                            </div>
                                                            <div class="col-6 col-md-4 col-lg-auto">
                                                                <label class="form-label form-label-sm mb-1">P</label>
                                                                <input type="number" class="form-control form-control-sm prova-input" 
                                                                       id="prova-p-<?= $casting['id'] ?>" 
                                                                       data-casting="<?= $casting['id'] ?>"
                                                                       data-field="p"
                                                                       placeholder="0.00" step="0.01" style="width: 70px;"
                                                                       onkeydown="handleProvaKeyNav(event, <?= $casting['id'] ?>, 'p')">
                                                            </div>
                                                            <div class="col-6 col-md-4 col-lg-auto">
                                                                <label class="form-label form-label-sm mb-1">CU</label>
                                                                <input type="number" class="form-control form-control-sm prova-input" 
                                                                       id="prova-cu-<?= $casting['id'] ?>" 
                                                                       data-casting="<?= $casting['id'] ?>"
                                                                       data-field="cu"
                                                                       placeholder="0.00" step="0.01" style="width: 70px;"
                                                                       onkeydown="handleProvaKeyNav(event, <?= $casting['id'] ?>, 'cu')">
                                                            </div>
                                                            <div class="col-12 col-md-6 col-lg-auto">
                                                                <div class="d-flex gap-2">
                                                                    <button class="btn btn-success btn-sm flex-fill" 
                                                                            onclick="saveInlineProva(<?= $casting['id'] ?>)">
                                                                        <i class="bi bi-save"></i> Kaydet
                                                                    </button>
                                                                    <button class="btn btn-secondary btn-sm flex-fill" 
                                                                            onclick="toggleProvaForm(<?= $casting['id'] ?>)">
                                                                        <i class="bi bi-x"></i> İptal
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="keyboard-hint mt-2">
                                                            <i class="bi bi-keyboard"></i>
                                                            <strong>Klavye Kısayolları:</strong> 
                                                            ← → Yön tuşları ile geçiş | 
                                                            <kbd>Enter</kbd> Kaydet | 
                                                            <kbd>Esc</kbd> İptal | 
                                                            <kbd>Tab</kbd> İleri
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Kalite Kontrol Modal -->
    <div class="modal fade" id="qualityControlModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-clipboard-check"></i>
                        Kalite Kontrol Değerleri
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="qualityControlForm">
                        <input type="hidden" id="qc-casting-id" name="casting_id">
                        
                        <!-- Döküm Bilgileri -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">Döküm Bilgileri</h6>
                            </div>
                            <div class="card-body" id="casting-info">
                                <!-- JavaScript ile doldurulacak -->
                            </div>
                        </div>

                        <!-- Kimyasal Kompozisyon -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">Kimyasal Kompozisyon (%)</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Karbon (C)</label>
                                        <input type="number" class="form-control" name="carbon_percentage" step="0.01" placeholder="0.00">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Silisyum (Si)</label>
                                        <input type="number" class="form-control" name="silicon_percentage" step="0.01" placeholder="0.00">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Mangan (Mn)</label>
                                        <input type="number" class="form-control" name="manganese_percentage" step="0.01" placeholder="0.00">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Fosfor (P)</label>
                                        <input type="number" class="form-control" name="phosphorus_percentage" step="0.01" placeholder="0.00">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Kükürt (S)</label>
                                        <input type="number" class="form-control" name="sulfur_percentage" step="0.01" placeholder="0.00">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Bakır (Cu)</label>
                                        <input type="number" class="form-control" name="copper_percentage" step="0.01" placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Prova Kayıtları -->
                        <div class="card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Prova Kayıtları</h6>
                                <button type="button" class="btn btn-sm btn-success" onclick="addProvaRecord()">
                                    <i class="bi bi-plus-circle"></i> Prova Ekle
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="prova-list" class="mb-3">
                                    <div class="text-muted text-center py-3">
                                        <i class="bi bi-clipboard-data"></i>
                                        <p class="mb-0">Henüz prova kaydı eklenmedi.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Test Bilgileri -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Test Bilgileri</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Testi Yapan</label>
                                        <input type="text" class="form-control" name="tested_by" placeholder="Kalite kontrol uzmanı">
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Açıklamalar</label>
                                        <textarea class="form-control" name="remarks" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="saveQualityControl()">
                        <i class="bi bi-save"></i> Kaydet
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // Saat güncelleme
        function updateCurrentTime() {
            const now = new Date();
            const timeString = now.toLocaleString('tr-TR', {
                day: '2-digit',
                month: '2-digit', 
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('current-time').textContent = timeString;
        }

        // Her saniye saat güncelle
        setInterval(updateCurrentTime, 1000);

        // AJAX fonksiyonları
        function completeCasting(castingId) {
            if (confirm('Bu dökümü tamamlamak istediğinizden emin misiniz?')) {
                console.log('🔧 Döküm tamamlama başlatıldı - ID:', castingId);
                
                fetch('?action=complete_casting&casting_id=' + castingId, {
                    method: 'POST'
                })
                .then(response => {
                    console.log('📡 Server response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('📊 Server response data:', data);
                    
                    if (data.success) {
                        alert(data.message);
                        console.log('✅ Döküm başarıyla tamamlandı, sayfa yenileniyor...');
                        location.reload();
                    } else {
                        console.error('❌ Döküm tamamlama hatası:', data.message);
                        alert('Hata: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('🚨 JavaScript hatası:', error);
                    alert('Beklenmeyen hata: ' + error.message);
                });
            }
        }

        function startCasting(furnaceId) {
            fetch('?action=start_casting&furnace_id=' + furnaceId, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Hata: ' + data.message);
                }
            });
        }

        function switchFurnaceInSet(setNumber, currentFurnaceNumber, targetFurnaceNumber) {
            if (confirm(`Set ${setNumber}: ${currentFurnaceNumber}. Ocak'tan ${targetFurnaceNumber}. Ocak'a geçmek istediğinizden emin misiniz?\n\nMevcut döküm tamamlanacak ve yeni ocakta döküm başlatılacak.`)) {
                fetch('?action=switch_furnace_in_set&furnace_set=' + setNumber, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let message = data.message;
                        
                        // Detaylı bilgi varsa göster
                        if (data.data) {
                            message += '\n\n📊 Detaylar:';
                            message += '\n✓ ' + data.data.old_furnace + '. Ocak → Standby';
                            message += '\n✓ ' + data.data.new_furnace + '. Ocak → Aktif';
                            message += '\n✓ Yeni Döküm: #' + data.data.new_casting_number;
                            message += '\n✓ Genel Sıra: #' + data.data.new_global_number;
                        }
                        
                        alert(message);
                        location.reload();
                    } else {
                        alert('Hata: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Ocak değiştirme hatası: ' + error.message);
                });
            }
        }
        
        // Günü Bitir fonksiyonu
        function completeDayConfirm() {
            const confirmation = confirm(
                '⚠️ GÜNÜ BİTİR\n\n' +
                'Bu işlem:\n' +
                '• Devam eden tüm dökümleri tamamlar\n' +
                '• Günlük rapor oluşturur\n' +
                '• Tüm döküm verilerini kaydeder\n\n' +
                'Devam etmek istediğinizden emin misiniz?'
            );
            
            if (!confirmation) return;
            
            // Loading göster
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> İşleniyor...';
            
            fetch('?action=complete_day', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                
                if (data.success) {
                    let message = '✅ GÜN BAŞARIYLA TAMAMLANDI!\n\n';
                    message += '📊 ÖZET:\n';
                    message += `• Toplam Döküm: ${data.total_castings}\n`;
                    message += `• Tamamlanan: ${data.completed_castings}\n`;
                    message += `• Geciken: ${data.delayed_castings}\n`;
                    message += `• Ortalama Süre: ${data.average_time} dk\n`;
                    message += `• Verimlilik: %${data.efficiency}\n\n`;
                    message += '📄 Detaylı raporu görmek için "Raporlar" sayfasına gidin.';
                    
                    alert(message);
                    location.reload();
                } else {
                    alert('❌ Hata: ' + data.message);
                }
            })
            .catch(error => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                alert('Günü bitirme hatası: ' + error.message);
            });
        }
        
        function sendToMaintenance(furnaceId) {
            if (confirm('Bu ocağı bakıma göndermek istediğinizden emin misiniz?')) {
                fetch('?action=send_to_maintenance&furnace_id=' + furnaceId, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Hata: ' + data.message);
                    }
                });
            }
        }

        function setupSystem() {
            if (confirm('Sistem kurulumu yapılsın mı? Bu işlem mevcut verileri sıfırlayabilir.')) {
                fetch('?action=setup_system', {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    location.reload();
                });
            }
        }

        function generateDailyReport() {
            fetch('?action=generate_daily_report', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
            });
        }

        // Prova Input Klavye Navigasyonu
        // Alan sırası: C -> SI -> MN -> S -> P -> CU
        const provaFieldOrder = ['c', 'si', 'mn', 's', 'p', 'cu'];
        
        /**
         * Prova input alanları arasında klavye navigasyonu
         * Kullanım: Sağ/Sol ok tuşları ile geçiş, Enter ile kaydet, Escape ile iptal
         */
        function handleProvaKeyNav(event, castingId, currentField) {
            const keyCode = event.keyCode || event.which;
            
            // Enter tuşu (13) - Kaydet
            if (keyCode === 13) {
                event.preventDefault();
                saveInlineProva(castingId);
                return;
            }
            
            // Escape tuşu (27) - İptal
            if (keyCode === 27) {
                event.preventDefault();
                toggleProvaForm(castingId);
                return;
            }
            
            // Sağ ok tuşu (39) - Sonraki alana geç
            if (keyCode === 39) {
                const currentIndex = provaFieldOrder.indexOf(currentField);
                if (currentIndex < provaFieldOrder.length - 1) {
                    event.preventDefault();
                    const nextField = provaFieldOrder[currentIndex + 1];
                    const nextInput = document.getElementById('prova-' + nextField + '-' + castingId);
                    if (nextInput) {
                        nextInput.focus();
                        nextInput.select();
                    }
                }
                return;
            }
            
            // Sol ok tuşu (37) - Önceki alana geç
            if (keyCode === 37) {
                const currentIndex = provaFieldOrder.indexOf(currentField);
                if (currentIndex > 0) {
                    event.preventDefault();
                    const prevField = provaFieldOrder[currentIndex - 1];
                    const prevInput = document.getElementById('prova-' + prevField + '-' + castingId);
                    if (prevInput) {
                        prevInput.focus();
                        prevInput.select();
                    }
                }
                return;
            }
            
            // Tab tuşu (9) - Varsayılan davranış (sonraki alana geç)
            // Tab + Shift - Önceki alana geç (tarayıcı otomatik halleder)
        }
        
        // Inline Prova Formu Yönetimi
        function toggleProvaForm(castingId) {
            const formRow = document.getElementById('prova-form-' + castingId);
            
            // Diğer açık formları kapat
            document.querySelectorAll('.prova-form-row').forEach(row => {
                if (row.id !== 'prova-form-' + castingId) {
                    row.style.display = 'none';
                }
            });
            
            // Bu formu aç/kapat
            if (formRow.style.display === 'none') {
                formRow.style.display = 'table-row';
                // Input alanlarını temizle
                document.getElementById('prova-c-' + castingId).value = '';
                document.getElementById('prova-si-' + castingId).value = '';
                document.getElementById('prova-mn-' + castingId).value = '';
                document.getElementById('prova-s-' + castingId).value = '';
                document.getElementById('prova-p-' + castingId).value = '';
                document.getElementById('prova-cu-' + castingId).value = '';
                // İlk inputa focus
                document.getElementById('prova-c-' + castingId).focus();
            } else {
                formRow.style.display = 'none';
            }
        }
        
        function saveInlineProva(castingId) {
            // Prova değerlerini topla
            const provaData = {
                c: document.getElementById('prova-c-' + castingId).value,
                si: document.getElementById('prova-si-' + castingId).value,
                mn: document.getElementById('prova-mn-' + castingId).value,
                s: document.getElementById('prova-s-' + castingId).value,
                p: document.getElementById('prova-p-' + castingId).value,
                cu: document.getElementById('prova-cu-' + castingId).value
            };
            
            // En az bir değer girilmiş mi kontrol et
            const hasValue = Object.values(provaData).some(val => val && val.trim() !== '');
            if (!hasValue) {
                alert('Lütfen en az bir değer girin.');
                return;
            }
            
            // Mevcut provalar ile birleştir
            fetch('?action=get_quality_control&casting_id=' + castingId)
                .then(response => response.json())
                .then(data => {
                    let existingProvas = [];
                    if (data.success && data.data.quality_control && data.data.quality_control.prova_data) {
                        try {
                            existingProvas = typeof data.data.quality_control.prova_data === 'string' 
                                ? JSON.parse(data.data.quality_control.prova_data)
                                : data.data.quality_control.prova_data;
                            
                            if (!Array.isArray(existingProvas)) {
                                existingProvas = [];
                            }
                        } catch(e) {
                            existingProvas = [];
                        }
                    }
                    
                    // Yeni provayı ekle
                    existingProvas.push(provaData);
                    
                    // Kaydet
                    const formData = new FormData();
                    formData.append('prova_data', JSON.stringify(existingProvas));
                    
                    fetch('?action=save_quality_control&casting_id=' + castingId, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            alert('Prova başarıyla kaydedildi!');
                            location.reload();
                        } else {
                            alert('Hata: ' + result.message);
                        }
                    })
                    .catch(error => {
                        alert('Kaydetme hatası: ' + error.message);
                    });
                })
                .catch(error => {
                    alert('Veri yükleme hatası: ' + error.message);
                });
        }
        
        // Prova kayıtları yönetimi (Modal için)
        window.provaRecords = [];
        
        function addProvaRecord() {
            window.provaRecords.push({
                c: '', si: '', mn: '', s: '', p: '', cu: ''
            });
            renderProvaList();
        }
        
        function removeProvaRecord(index) {
            window.provaRecords.splice(index, 1);
            renderProvaList();
        }
        
        function updateProvaValue(index, field, value) {
            if (window.provaRecords[index]) {
                window.provaRecords[index][field] = value;
            }
        }
        
        function renderProvaList() {
            const provaListDiv = document.getElementById('prova-list');
            
            if (window.provaRecords.length === 0) {
                provaListDiv.innerHTML = `
                    <div class="text-muted text-center py-3">
                        <i class="bi bi-clipboard-data"></i>
                        <p class="mb-0">Henüz prova kaydı eklenmedi.</p>
                    </div>
                `;
                return;
            }
            
            let html = '';
            window.provaRecords.forEach((prova, index) => {
                html += `
                    <div class="prova-item card mb-2">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong>PROVA ${index + 1}:</strong>
                                <button type="button" class="btn btn-sm btn-danger" onclick="removeProvaRecord(${index})">
                                    <i class="bi bi-trash"></i> Sil
                                </button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-2">
                                    <label class="form-label form-label-sm">C</label>
                                    <input type="number" class="form-control form-control-sm" 
                                           value="${prova.c || ''}" 
                                           onchange="updateProvaValue(${index}, 'c', this.value)"
                                           placeholder="0.00" step="0.01">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label form-label-sm">SI</label>
                                    <input type="number" class="form-control form-control-sm" 
                                           value="${prova.si || ''}" 
                                           onchange="updateProvaValue(${index}, 'si', this.value)"
                                           placeholder="0.00" step="0.01">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label form-label-sm">MN</label>
                                    <input type="number" class="form-control form-control-sm" 
                                           value="${prova.mn || ''}" 
                                           onchange="updateProvaValue(${index}, 'mn', this.value)"
                                           placeholder="0.00" step="0.01">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label form-label-sm">S</label>
                                    <input type="number" class="form-control form-control-sm" 
                                           value="${prova.s || ''}" 
                                           onchange="updateProvaValue(${index}, 's', this.value)"
                                           placeholder="0.00" step="0.01">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label form-label-sm">P</label>
                                    <input type="number" class="form-control form-control-sm" 
                                           value="${prova.p || ''}" 
                                           onchange="updateProvaValue(${index}, 'p', this.value)"
                                           placeholder="0.00" step="0.01">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label form-label-sm">CU</label>
                                    <input type="number" class="form-control form-control-sm" 
                                           value="${prova.cu || ''}" 
                                           onchange="updateProvaValue(${index}, 'cu', this.value)"
                                           placeholder="0.00" step="0.01">
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    C:${prova.c || '-'} SI:${prova.si || '-'} MN:${prova.mn || '-'} S:${prova.s || '-'} P:${prova.p || '-'} CU:${prova.cu || '-'}
                                </small>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            provaListDiv.innerHTML = html;
        }
        
        function openQualityControlModal(castingId) {
            // Verileri temizle
            document.getElementById('qualityControlForm').reset();
            document.getElementById('qc-casting-id').value = castingId;
            window.provaRecords = [];
            
            // Döküm ve kalite kontrol verilerini yükle
            fetch('?action=get_quality_control&casting_id=' + castingId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const casting = data.data.casting;
                        const qc = data.data.quality_control;
                        
                        // Döküm bilgilerini göster
                        document.getElementById('casting-info').innerHTML = `
                            <div class="row">
                                <div class="col-md-3"><strong>Ocak:</strong> ${casting.furnace_number}</div>
                                <div class="col-md-3"><strong>Döküm No:</strong> ${casting.casting_number_per_furnace}</div>
                                <div class="col-md-3"><strong>Genel Sıra:</strong> #${casting.global_casting_number}</div>
                                <div class="col-md-3"><strong>Başlangıç:</strong> ${casting.start_time}</div>
                            </div>
                        `;
                        
                        // Mevcut kalite kontrol verilerini doldur
                        if (qc) {
                            document.querySelector('[name="carbon_percentage"]').value = qc.carbon_percentage || '';
                            document.querySelector('[name="silicon_percentage"]').value = qc.silicon_percentage || '';
                            document.querySelector('[name="manganese_percentage"]').value = qc.manganese_percentage || '';
                            document.querySelector('[name="phosphorus_percentage"]').value = qc.phosphorus_percentage || '';
                            document.querySelector('[name="sulfur_percentage"]').value = qc.sulfur_percentage || '';
                            document.querySelector('[name="copper_percentage"]').value = qc.copper_percentage || '';
                            document.querySelector('[name="tested_by"]').value = qc.tested_by || '';
                            document.querySelector('[name="remarks"]').value = qc.remarks || '';
                            
                            // Prova verilerini yükle
                            if (qc.prova_data) {
                                try {
                                    window.provaRecords = typeof qc.prova_data === 'string' ? JSON.parse(qc.prova_data) : qc.prova_data;
                                } catch(e) {
                                    window.provaRecords = [];
                                }
                            }
                        }
                        
                        renderProvaList();
                        
                        // Modal'ı göster
                        const modal = new bootstrap.Modal(document.getElementById('qualityControlModal'));
                        modal.show();
                    } else {
                        alert('Hata: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Veri yüklenirken hata oluştu: ' + error.message);
                });
        }
        
        function saveQualityControl() {
            const castingId = document.getElementById('qc-casting-id').value;
            const form = document.getElementById('qualityControlForm');
            const formData = new FormData(form);
            
            // Prova verilerini ekle
            formData.append('prova_data', JSON.stringify(window.provaRecords || []));
            
            fetch('?action=save_quality_control&casting_id=' + castingId, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    bootstrap.Modal.getInstance(document.getElementById('qualityControlModal')).hide();
                    location.reload();
                } else {
                    alert('Hata: ' + data.message);
                }
            })
            .catch(error => {
                alert('Kaydetme hatası: ' + error.message);
            });
        }

        // Gecikme nedeni sorma
        function askDelayReason(castingId, delayMinutes) {
            const reason = prompt(
                'Bu döküm ' + delayMinutes + ' dakika gecikti.\n\n' +
                'Gecikmenin nedenini belirtiniz:\n' +
                '1. Hurda kalitesi düşük\n' +
                '2. Elektrik kesintisi\n' +
                '3. Ekipman arızası\n' +
                '4. Operatör değişimi\n' +
                '5. Diğer (açıklayınız)\n\n' +
                'Neden:'
            );
            
            if (reason && reason.trim() !== '') {
                fetch('?action=save_delay_reason&casting_id=' + castingId + '&reason=' + encodeURIComponent(reason.trim()), {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Gecikme nedeni kaydedildi: ' + reason);
                        location.reload();
                    } else {
                        alert('Hata: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Gecikme nedeni kaydedilemedi.');
                });
            }
        }

        // Sayfa yüklendiğinde
        document.addEventListener('DOMContentLoaded', function() {
            updateCurrentTime();
            
            // 30 saniyede bir sayfayı yenile (real-time effect)
            setInterval(function() {
                location.reload();
            }, 30000);
        });
    </script>
</body>
</html>
