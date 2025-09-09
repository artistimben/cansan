

<?php $__env->startSection('title', 'Günlük Rapor - Cansan Kalite Kontrol'); ?>

<?php $__env->startSection('header', 'Günlük Rapor'); ?>

<?php $__env->startSection('header-buttons'); ?>
    <div class="btn-group" role="group">
        <input type="date" class="form-control form-control-sm" id="reportDate" value="<?php echo e(request('date', date('Y-m-d'))); ?>" onchange="changeDate()">
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="printReport()">
            <i class="fas fa-print me-1"></i>
            Yazdır
        </button>
        <button type="button" class="btn btn-outline-success btn-sm" onclick="exportExcel()">
            <i class="fas fa-file-excel me-1"></i>
            Excel
        </button>
        <a href="<?php echo e(route('reports.index')); ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>
            Geri
        </a>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<!-- Rapor Başlığı -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="mb-1">Cansan Çelik Üretim Fabrikası</h3>
                <h4 class="text-primary mb-1">Günlük Kalite Kontrol Raporu</h4>
                <p class="text-muted mb-0">
                    <i class="fas fa-calendar me-1"></i>
                    <?php echo e(request('date') ? \Carbon\Carbon::parse(request('date'))->format('d.m.Y') : date('d.m.Y')); ?>

                </p>
            </div>
        </div>
    </div>
</div>

<!-- Özet İstatistikler -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-fire text-primary fa-2x mb-2"></i>
                <h3 class="text-primary mb-1">0</h3>
                <small class="text-muted">Toplam Döküm</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-vial text-success fa-2x mb-2"></i>
                <h3 class="text-success mb-1">0</h3>
                <small class="text-muted">Toplam Prova</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-check-circle text-info fa-2x mb-2"></i>
                <h3 class="text-info mb-1">0%</h3>
                <small class="text-muted">Kalite Oranı</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-tools text-warning fa-2x mb-2"></i>
                <h3 class="text-warning mb-1">0</h3>
                <small class="text-muted">Ham Madde Ekleme</small>
            </div>
        </div>
    </div>
</div>

<!-- Ocak Bazında Detaylar -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-fire me-2"></i>
                    Ocak Bazında Döküm Detayları
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Set</th>
                                <th>Ocak</th>
                                <th>Durum</th>
                                <th>Döküm Sayısı</th>
                                <th>Prova Sayısı</th>
                                <th>Kalite Oranı</th>
                                <th>Son Döküm</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Set 1</td>
                                <td>Ocak 1</td>
                                <td><span class="badge bg-success">Aktif</span></td>
                                <td>0</td>
                                <td>0</td>
                                <td>0%</td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>Set 2</td>
                                <td>Ocak 3</td>
                                <td><span class="badge bg-success">Aktif</span></td>
                                <td>0</td>
                                <td>0</td>
                                <td>0%</td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>Set 3</td>
                                <td>Ocak 5</td>
                                <td><span class="badge bg-success">Aktif</span></td>
                                <td>0</td>
                                <td>0</td>
                                <td>0%</td>
                                <td>-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Kalite Analizi -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Kalite Dağılımı
                </h5>
            </div>
            <div class="card-body">
                <canvas id="qualityChart" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Saatlik Döküm Trendi
                </h5>
            </div>
            <div class="card-body">
                <canvas id="trendChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Ham Madde Kullanımı -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-tools me-2"></i>
                    Ham Madde Ekleme Detayları
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Malzeme</th>
                                        <th>Kullanım (kg)</th>
                                        <th>Başarı Oranı</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Karbon</td>
                                        <td>0</td>
                                        <td>0%</td>
                                    </tr>
                                    <tr>
                                        <td>Mangan</td>
                                        <td>0</td>
                                        <td>0%</td>
                                    </tr>
                                    <tr>
                                        <td>Silisyum</td>
                                        <td>0</td>
                                        <td>0%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <canvas id="materialChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Vardiya Analizi -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-clock me-2"></i>
                    Vardiya Bazında Analiz
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Vardiya</th>
                                <th>Döküm Sayısı</th>
                                <th>Prova Sayısı</th>
                                <th>Kalite Oranı</th>
                                <th>Ortalama Prova/Döküm</th>
                                <th>Ham Madde Ekleme</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-primary">A Vardiyası</span></td>
                                <td>0</td>
                                <td>0</td>
                                <td>0%</td>
                                <td>0</td>
                                <td>0</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-success">B Vardiyası</span></td>
                                <td>0</td>
                                <td>0</td>
                                <td>0%</td>
                                <td>0</td>
                                <td>0</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-warning">C Vardiyası</span></td>
                                <td>0</td>
                                <td>0</td>
                                <td>0%</td>
                                <td>0</td>
                                <td>0</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rapor Notu -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center text-muted">
                <small>
                    <i class="fas fa-info-circle me-1"></i>
                    Bu rapor <?php echo e(date('d.m.Y H:i')); ?> tarihinde otomatik olarak oluşturulmuştur.
                </small>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Tarih değiştir
function changeDate() {
    const date = document.getElementById('reportDate').value;
    window.location.href = `<?php echo e(route('reports.daily')); ?>?date=${date}`;
}

// Rapor yazdır
function printReport() {
    window.print();
}

// Excel export
function exportExcel() {
    const date = document.getElementById('reportDate').value;
    window.open(`<?php echo e(route('reports.export')); ?>?type=daily&date=${date}&format=excel`, '_blank');
}

// Grafikler
document.addEventListener('DOMContentLoaded', function() {
    // Kalite dağılımı grafiği
    const qualityCtx = document.getElementById('qualityChart').getContext('2d');
    new Chart(qualityCtx, {
        type: 'doughnut',
        data: {
            labels: ['Onaylanan', 'Reddedilen', 'Bekleyen', 'Düzeltme Gerekli'],
            datasets: [{
                data: [0, 0, 0, 0],
                backgroundColor: ['#28a745', '#dc3545', '#ffc107', '#17a2b8']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    
    // Trend grafiği
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00'],
            datasets: [{
                label: 'Döküm Sayısı',
                data: [0, 0, 0, 0, 0, 0],
                borderColor: '#007bff',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    
    // Ham madde grafiği
    const materialCtx = document.getElementById('materialChart').getContext('2d');
    new Chart(materialCtx, {
        type: 'bar',
        data: {
            labels: ['Karbon', 'Mangan', 'Silisyum'],
            datasets: [{
                label: 'Kullanım (kg)',
                data: [0, 0, 0],
                backgroundColor: '#ffc107'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\cansan\kk-cansan\resources\views/reports/daily.blade.php ENDPATH**/ ?>