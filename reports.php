<?php
/**
 * Günlük Raporlar Sayfası
 */

require_once 'config.php';
require_once 'functions.php';

// Rapor listesi al
$stmt = $pdo->query("
    SELECT * FROM daily_reports 
    ORDER BY report_date DESC 
    LIMIT 30
");
$reports = $stmt->fetchAll();

// Eğer specific report ID varsa, detayını göster
$selected_report = null;
if (isset($_GET['report_id'])) {
    $report_id = (int)$_GET['report_id'];
    $stmt = $pdo->prepare("SELECT * FROM daily_reports WHERE id = ?");
    $stmt->execute([$report_id]);
    $selected_report = $stmt->fetch();
    
    if ($selected_report && $selected_report['report_data']) {
        $selected_report['report_data'] = json_decode($selected_report['report_data'], true);
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Günlük Raporlar - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #1e3a8a;
            --secondary-color: #3b82f6;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
        }

        body {
            background-color: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, #3b82f6 100%);
            box-shadow: 0 2px 10px rgba(30, 58, 138, 0.1);
        }

        .report-card {
            transition: all 0.3s ease;
            cursor: pointer;
            border-left: 4px solid var(--primary-color);
        }

        .report-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-left-color: var(--secondary-color);
        }

        .report-card.selected {
            border-left-color: var(--success-color);
            background-color: #f0fdf4;
        }

        .stat-box {
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .stat-box.primary { background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); color: white; }
        .stat-box.success { background: linear-gradient(135deg, #059669 0%, #10b981 100%); color: white; }
        .stat-box.warning { background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%); color: white; }
        .stat-box.danger { background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%); color: white; }
        .stat-box.info { background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%); color: white; }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
        }

        .stat-label {
            font-size: 0.875rem;
            opacity: 0.9;
            margin: 0;
        }

        .furnace-performance-table th {
            background-color: var(--primary-color);
            color: white;
        }

        .casting-detail-row:hover {
            background-color: #f1f5f9;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            
            .container-fluid {
                padding: 0 !important;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top no-print">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-arrow-left-circle"></i> Ana Sayfa'ya Dön
            </a>
            <span class="navbar-text text-white">
                <i class="bi bi-file-earmark-text"></i> Günlük Raporlar
            </span>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="row">
            <!-- Sol Panel: Rapor Listesi -->
            <div class="col-md-3 no-print">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-list-ul"></i> Rapor Listesi</h5>
                    </div>
                    <div class="card-body p-0" style="max-height: 80vh; overflow-y: auto;">
                        <?php if (empty($reports)): ?>
                            <div class="p-3 text-center text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">Henüz rapor yok</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($reports as $report): ?>
                                <a href="?report_id=<?= $report['id'] ?>" class="text-decoration-none">
                                    <div class="report-card card mb-2 mx-2 mt-2 <?= ($selected_report && $selected_report['id'] == $report['id']) ? 'selected' : '' ?>">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1">
                                                        <i class="bi bi-calendar3"></i>
                                                        <?= date('d.m.Y', strtotime($report['report_date'])) ?>
                                                    </h6>
                                                    <small class="text-muted">
                                                        <?= $report['total_castings'] ?> döküm, 
                                                        <?= $report['delayed_castings'] ?> gecikme
                                                    </small>
                                                </div>
                                                <?php if ($selected_report && $selected_report['id'] == $report['id']): ?>
                                                    <span class="badge bg-success"><i class="bi bi-check-circle"></i></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sağ Panel: Rapor Detayı -->
            <div class="col-md-9">
                <?php if ($selected_report): ?>
                    <?php $data = $selected_report['report_data']; ?>
                    
                    <!-- Rapor Başlığı -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-1">
                                        <i class="bi bi-file-earmark-bar-graph"></i>
                                        Günlük Rapor: <?= date('d.m.Y', strtotime($selected_report['report_date'])) ?>
                                    </h3>
                                    <p class="text-muted mb-0">
                                        <i class="bi bi-clock-history"></i>
                                        Oluşturulma: <?= date('d.m.Y H:i', strtotime($selected_report['created_at'])) ?>
                                    </p>
                                </div>
                                <button class="btn btn-primary no-print" onclick="window.print()">
                                    <i class="bi bi-printer"></i> Yazdır
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- İstatistik Kartları -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="stat-box primary">
                                <p class="stat-value"><?= $selected_report['total_castings'] ?></p>
                                <p class="stat-label">Toplam Döküm</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box success">
                                <p class="stat-value"><?= $selected_report['completed_castings'] ?></p>
                                <p class="stat-label">Tamamlanan</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box warning">
                                <p class="stat-value"><?= $selected_report['delayed_castings'] ?></p>
                                <p class="stat-label">Geciken Döküm</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box info">
                                <p class="stat-value"><?= round($selected_report['average_casting_time']) ?> dk</p>
                                <p class="stat-label">Ortalama Süre</p>
                            </div>
                        </div>
                    </div>

                    <!-- Verimlilik ve Analizler -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card shadow-sm">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="bi bi-graph-up-arrow"></i> Verimlilik Analizi</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Günlük Verimlilik</span>
                                            <strong>%<?= round($data['efficiency'], 1) ?></strong>
                                        </div>
                                        <div class="progress" style="height: 25px;">
                                            <div class="progress-bar <?= $data['efficiency'] >= 80 ? 'bg-success' : ($data['efficiency'] >= 60 ? 'bg-warning' : 'bg-danger') ?>" 
                                                 style="width: <?= min(100, $data['efficiency']) ?>%">
                                                %<?= round($data['efficiency'], 1) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle"></i>
                                        Hedef: <?= $data['expected_daily_castings'] ?> döküm/gün
                                    </small>
                                    
                                    <?php if (isset($data['fastest_casting']) && $data['fastest_casting']): ?>
                                        <hr>
                                        <div class="d-flex justify-content-between">
                                            <span><i class="bi bi-lightning-charge text-success"></i> En Hızlı</span>
                                            <span>Ocak <?= $data['fastest_casting']['furnace'] ?> - <?= $data['fastest_casting']['duration'] ?> dk</span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($data['slowest_casting']) && $data['slowest_casting']): ?>
                                        <div class="d-flex justify-content-between mt-2">
                                            <span><i class="bi bi-hourglass-split text-warning"></i> En Yavaş</span>
                                            <span>Ocak <?= $data['slowest_casting']['furnace'] ?> - <?= $data['slowest_casting']['duration'] ?> dk</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card shadow-sm">
                                <div class="card-header bg-warning text-white">
                                    <h6 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Gecikme Detayları</h6>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($data['delayed_details'])): ?>
                                        <div class="text-center text-success">
                                            <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                                            <p class="mt-2 mb-0">Geciken döküm yok!</p>
                                        </div>
                                    <?php else: ?>
                                        <div style="max-height: 200px; overflow-y: auto;">
                                            <?php foreach ($data['delayed_details'] as $delayed): ?>
                                                <div class="border-bottom pb-2 mb-2">
                                                    <div class="d-flex justify-content-between">
                                                        <strong>Ocak <?= $delayed['furnace'] ?> - Döküm #<?= $delayed['casting_number'] ?></strong>
                                                        <span class="badge bg-danger">+<?= $delayed['delay'] ?> dk</span>
                                                    </div>
                                                    <?php if (!empty($delayed['remarks'])): ?>
                                                        <small class="text-muted">
                                                            <i class="bi bi-chat-left-text"></i> <?= htmlspecialchars($delayed['remarks']) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ocak Bazında Performans -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="bi bi-fire"></i> Ocak Bazında Performans</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover furnace-performance-table">
                                    <thead>
                                        <tr>
                                            <th>Ocak No</th>
                                            <th class="text-center">Toplam Döküm</th>
                                            <th class="text-center">Tamamlanan</th>
                                            <th class="text-center">Gecikme</th>
                                            <th class="text-center">Toplam Süre</th>
                                            <th class="text-center">Ortalama Süre</th>
                                            <th class="text-center">Performans</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data['furnace_stats'] as $furnace_num => $stats): ?>
                                            <tr>
                                                <td><strong>Ocak <?= $furnace_num ?></strong></td>
                                                <td class="text-center"><?= $stats['total_castings'] ?></td>
                                                <td class="text-center"><?= $stats['completed_count'] ?></td>
                                                <td class="text-center">
                                                    <?php if ($stats['delayed_count'] > 0): ?>
                                                        <span class="badge bg-warning"><?= $stats['delayed_count'] ?></span>
                                                    <?php else: ?>
                                                        <span class="text-success">0</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center"><?= $stats['total_time'] ?> dk</td>
                                                <td class="text-center"><?= round($stats['average_time']) ?> dk</td>
                                                <td class="text-center">
                                                    <?php
                                                    $performance = $stats['total_castings'] > 0 
                                                        ? round((($stats['total_castings'] - $stats['delayed_count']) / $stats['total_castings']) * 100)
                                                        : 0;
                                                    $badge_class = $performance >= 80 ? 'bg-success' : ($performance >= 60 ? 'bg-warning' : 'bg-danger');
                                                    ?>
                                                    <span class="badge <?= $badge_class ?>">%<?= $performance ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Döküm Detayları -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="bi bi-list-check"></i> Tüm Döküm Detayları</h6>
                            <span class="badge bg-light text-dark"><?= count($data['castings']) ?> döküm</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Ocak</th>
                                            <th>Döküm No</th>
                                            <th>Başlangıç</th>
                                            <th>Bitiş</th>
                                            <th>Süre</th>
                                            <th>Durum</th>
                                            <th>Prova</th>
                                            <th>Açıklama</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data['castings'] as $casting): ?>
                                            <?php
                                            $duration = (int)$casting['duration_minutes'];
                                            $is_delayed = $duration > 120;
                                            ?>
                                            <tr class="casting-detail-row <?= $is_delayed ? 'table-warning' : '' ?>">
                                                <td><strong><?= $casting['furnace_number'] ?></strong></td>
                                                <td>#<?= $casting['casting_number_per_furnace'] ?> (<?= $casting['global_casting_number'] ?>)</td>
                                                <td><?= date('H:i', strtotime($casting['start_time'])) ?></td>
                                                <td><?= $casting['end_time'] ? date('H:i', strtotime($casting['end_time'])) : '-' ?></td>
                                                <td>
                                                    <?= $duration ?> dk
                                                    <?php if ($is_delayed): ?>
                                                        <i class="bi bi-exclamation-triangle text-warning"></i>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($casting['status'] === 'completed'): ?>
                                                        <span class="badge bg-success">Tamamlandı</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">Devam Ediyor</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $prova_data = $casting['prova_data'] ? json_decode($casting['prova_data'], true) : null;
                                                    if ($prova_data && is_array($prova_data) && count($prova_data) > 0):
                                                    ?>
                                                        <span class="badge bg-info"><?= count($prova_data) ?> prova</span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small><?= htmlspecialchars($casting['remarks'] ?? '') ?: '-' ?></small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="card shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-file-earmark-text" style="font-size: 5rem; color: #cbd5e1;"></i>
                            <h4 class="mt-3 text-muted">Rapor Seçilmedi</h4>
                            <p class="text-muted">Soldaki listeden bir rapor seçin.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

