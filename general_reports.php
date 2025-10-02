<?php
/**
 * Genel Raporlar Sayfası - Çizgi Grafikleri
 */

require_once 'config.php';
require_once 'functions.php';

// Tarih aralığı belirleme
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Tarih validasyonu
if ($start_date > $end_date) {
    $temp = $start_date;
    $start_date = $end_date;
    $end_date = $temp;
}

// Rapor verilerini al
$stmt = $pdo->prepare("
    SELECT 
        dr.report_date,
        dr.total_castings,
        dr.completed_castings,
        dr.delayed_castings,
        dr.total_production_time,
        dr.average_casting_time,
        dr.efficiency,
        dr.report_data
    FROM daily_reports dr
    WHERE dr.report_date BETWEEN ? AND ?
    ORDER BY dr.report_date ASC
");
$stmt->execute([$start_date, $end_date]);
$reports = $stmt->fetchAll();

// Grafik verilerini hazırla
$chart_data = [
    'labels' => [],
    'datasets' => [
        [
            'label' => 'Toplam Döküm',
            'data' => [],
            'borderColor' => '#3b82f6',
            'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
            'tension' => 0.4
        ],
        [
            'label' => 'Tamamlanan Döküm',
            'data' => [],
            'borderColor' => '#10b981',
            'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
            'tension' => 0.4
        ],
        [
            'label' => 'Gecikmeli Döküm',
            'data' => [],
            'borderColor' => '#f59e0b',
            'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
            'tension' => 0.4
        ]
    ]
];

$efficiency_data = [
    'labels' => [],
    'datasets' => [
        [
            'label' => 'Verimlilik (%)',
            'data' => [],
            'borderColor' => '#8b5cf6',
            'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
            'tension' => 0.4
        ]
    ]
];

$production_time_data = [
    'labels' => [],
    'datasets' => [
        [
            'label' => 'Toplam Üretim Süresi (dk)',
            'data' => [],
            'borderColor' => '#ef4444',
            'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
            'tension' => 0.4
        ],
        [
            'label' => 'Ortalama Döküm Süresi (dk)',
            'data' => [],
            'borderColor' => '#06b6d4',
            'backgroundColor' => 'rgba(6, 182, 212, 0.1)',
            'tension' => 0.4
        ]
    ]
];

// Verileri işle
foreach ($reports as $report) {
    $date = date('d.m', strtotime($report['report_date']));
    
    $chart_data['labels'][] = $date;
    $chart_data['datasets'][0]['data'][] = (int)$report['total_castings'];
    $chart_data['datasets'][1]['data'][] = (int)$report['completed_castings'];
    $chart_data['datasets'][2]['data'][] = (int)$report['delayed_castings'];
    
    $efficiency_data['labels'][] = $date;
    $efficiency_data['datasets'][0]['data'][] = (float)$report['efficiency'];
    
    $production_time_data['labels'][] = $date;
    $production_time_data['datasets'][0]['data'][] = (int)$report['total_production_time'];
    $production_time_data['datasets'][1]['data'][] = (float)$report['average_casting_time'];
}

// İstatistikler
$total_days = count($reports);
$total_castings = array_sum(array_column($reports, 'total_castings'));
$total_completed = array_sum(array_column($reports, 'completed_castings'));
$total_delayed = array_sum(array_column($reports, 'delayed_castings'));
$avg_daily_castings = $total_days > 0 ? round($total_castings / $total_days, 1) : 0;
$avg_efficiency = $total_days > 0 ? round(array_sum(array_column($reports, 'efficiency')) / $total_days, 1) : 0;
$total_production_time = array_sum(array_column($reports, 'total_production_time'));
$avg_production_time = $total_days > 0 ? round($total_production_time / $total_days, 1) : 0;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Genel Raporlar - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .chart-container {
            position: relative;
            height: 400px;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .stat-card h3 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-card p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin: 0;
        }

        .chart-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .chart-card h5 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e5e7eb;
        }

        .date-filter {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }

        .no-data {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
        }

        .no-data i {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-graph-up-arrow"></i> <?= APP_NAME ?>
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">
                    <i class="bi bi-house"></i> Ana Sayfa
                </a>
                <a class="nav-link" href="reports.php">
                    <i class="bi bi-file-earmark-bar-graph"></i> Günlük Raporlar
                </a>
                <a class="nav-link active" href="general_reports.php">
                    <i class="bi bi-graph-up"></i> Genel Raporlar
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Başlık -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="text-center mb-4">
                    <i class="bi bi-graph-up text-primary"></i>
                    Genel Raporlar
                </h1>
            </div>
        </div>

        <!-- Tarih Filtresi -->
        <div class="date-filter">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="start_date" class="form-label fw-bold">Başlangıç Tarihi</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" 
                           value="<?= $start_date ?>" max="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label fw-bold">Bitiş Tarihi</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" 
                           value="<?= $end_date ?>" max="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Raporu Güncelle
                    </button>
                </div>
            </form>
        </div>

        <?php if (empty($reports)): ?>
            <!-- Veri Yok -->
            <div class="chart-card">
                <div class="no-data">
                    <i class="bi bi-inbox"></i>
                    <h4>Veri Bulunamadı</h4>
                    <p>Seçilen tarih aralığında rapor verisi bulunmamaktadır.</p>
                </div>
            </div>
        <?php else: ?>
            <!-- İstatistik Kartları -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <h3><?= $total_castings ?></h3>
                        <p><i class="bi bi-cast"></i> Toplam Döküm</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <h3><?= $total_completed ?></h3>
                        <p><i class="bi bi-check-circle"></i> Tamamlanan</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <h3><?= $total_delayed ?></h3>
                        <p><i class="bi bi-exclamation-triangle"></i> Gecikmeli</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <h3>%<?= $avg_efficiency ?></h3>
                        <p><i class="bi bi-speedometer2"></i> Ort. Verimlilik</p>
                    </div>
                </div>
            </div>

            <!-- Döküm Grafiği -->
            <div class="chart-card">
                <h5><i class="bi bi-bar-chart-line"></i> Günlük Döküm Analizi</h5>
                <div class="chart-container">
                    <canvas id="castingsChart"></canvas>
                </div>
            </div>

            <!-- Verimlilik Grafiği -->
            <div class="chart-card">
                <h5><i class="bi bi-speedometer2"></i> Günlük Verimlilik Analizi</h5>
                <div class="chart-container">
                    <canvas id="efficiencyChart"></canvas>
                </div>
            </div>

            <!-- Üretim Süresi Grafiği -->
            <div class="chart-card">
                <h5><i class="bi bi-clock-history"></i> Üretim Süresi Analizi</h5>
                <div class="chart-container">
                    <canvas id="productionTimeChart"></canvas>
                </div>
            </div>

            <!-- Detaylı İstatistikler -->
            <div class="chart-card">
                <h5><i class="bi bi-info-circle"></i> Detaylı İstatistikler</h5>
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span><i class="bi bi-calendar-range"></i> Rapor Dönemi</span>
                                <strong><?= $total_days ?> gün</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><i class="bi bi-cast"></i> Ortalama Günlük Döküm</span>
                                <strong><?= $avg_daily_castings ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><i class="bi bi-clock"></i> Toplam Üretim Süresi</span>
                                <strong><?= number_format($total_production_time) ?> dk</strong>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span><i class="bi bi-clock-history"></i> Ortalama Üretim Süresi</span>
                                <strong><?= $avg_production_time ?> dk/gün</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><i class="bi bi-percent"></i> Gecikme Oranı</span>
                                <strong><?= $total_castings > 0 ? round(($total_delayed / $total_castings) * 100, 1) : 0 ?>%</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><i class="bi bi-graph-up"></i> En Yüksek Verimlilik</span>
                                <strong>%<?= max(array_column($reports, 'efficiency')) ?></strong>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Grafik verileri
        const castingsData = <?= json_encode($chart_data) ?>;
        const efficiencyData = <?= json_encode($efficiency_data) ?>;
        const productionTimeData = <?= json_encode($production_time_data) ?>;

        // Döküm Grafiği
        const castingsCtx = document.getElementById('castingsChart').getContext('2d');
        new Chart(castingsCtx, {
            type: 'line',
            data: castingsData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Günlük Döküm Sayıları'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Verimlilik Grafiği
        const efficiencyCtx = document.getElementById('efficiencyChart').getContext('2d');
        new Chart(efficiencyCtx, {
            type: 'line',
            data: efficiencyData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Günlük Verimlilik Oranları'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });

        // Üretim Süresi Grafiği
        const productionTimeCtx = document.getElementById('productionTimeChart').getContext('2d');
        new Chart(productionTimeCtx, {
            type: 'line',
            data: productionTimeData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Günlük Üretim Süreleri'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + ' dk';
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
