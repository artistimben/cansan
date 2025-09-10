

<?php $__env->startSection('title', 'Kontrol Paneli - Cansan Kalite Kontrol'); ?>

<?php $__env->startSection('header', 'Kontrol Paneli'); ?>

<?php $__env->startSection('header-buttons'); ?>
    <div class="btn-group d-none d-md-flex" role="group">
        <a href="<?php echo e(route('castings.create')); ?>" class="btn btn-success btn-sm" onclick="console.log('Yeni Döküm butonu tıklandı')">
            <i class="fas fa-plus-circle me-1"></i>
            Yeni Döküm
        </a>
        <a href="<?php echo e(route('samples.create')); ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>
            Yeni Prova
        </a>
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="refreshDashboard()">
            <i class="fas fa-sync-alt me-1"></i>
            Yenile
        </button>
        <button type="button" class="btn btn-outline-info btn-sm" onclick="toggleAutoRefresh()">
            <i class="fas fa-clock me-1"></i>
            <span id="auto-refresh-text">Otomatik Yenileme</span>
        </button>
    </div>
    
    <!-- Mobile buttons -->
    <div class="d-flex d-md-none gap-2">
        <a href="<?php echo e(route('castings.create')); ?>" class="btn btn-success btn-sm">
            <i class="fas fa-plus-circle"></i>
        </a>
        <a href="<?php echo e(route('samples.create')); ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i>
        </a>
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="refreshDashboard()">
            <i class="fas fa-sync-alt"></i>
        </button>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<!-- Sistem Durumu Kartları -->
<div class="row mb-4">
    <div class="col-6 col-md-3 mb-3">
        <div class="card stat-card h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="stat-number" id="total-castings"><?php echo e($dailyStats['total_castings']); ?></div>
                <div class="text-muted d-none d-sm-block">Bugünkü Döküm</div>
                <div class="text-muted d-block d-sm-none">Döküm</div>
                <small class="text-success d-none d-md-block">
                    <i class="fas fa-arrow-up me-1"></i>
                    Aktif Ocak: <?php echo e($dailyStats['active_furnaces']); ?>

                </small>
                <small class="text-success d-block d-md-none">
                    <i class="fas fa-fire me-1"></i>
                    <?php echo e($dailyStats['active_furnaces']); ?> Ocak
                </small>
            </div>
        </div>
    </div>
    
    <div class="col-6 col-md-3 mb-3">
        <div class="card stat-card h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="stat-number" id="total-samples"><?php echo e($dailyStats['total_samples']); ?></div>
                <div class="text-muted d-none d-sm-block">Bugünkü Prova</div>
                <div class="text-muted d-block d-sm-none">Prova</div>
                <small class="text-info d-none d-md-block">
                    <i class="fas fa-vial me-1"></i>
                    Haftalık: <?php echo e($weeklyStats['total_samples']); ?>

                </small>
                <small class="text-info d-block d-md-none">
                    <i class="fas fa-vial me-1"></i>
                    Hafta: <?php echo e($weeklyStats['total_samples']); ?>

                </small>
            </div>
        </div>
    </div>
    
    <div class="col-6 col-md-3 mb-3">
        <div class="card stat-card h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="stat-number text-success" id="approved-samples"><?php echo e($dailyStats['approved_samples']); ?></div>
                <div class="text-muted d-none d-sm-block">Onaylanan Prova</div>
                <div class="text-muted d-block d-sm-none">Onay</div>
                <small class="text-warning d-none d-md-block">
                    <i class="fas fa-hourglass-half me-1"></i>
                    Bekleyen: <?php echo e($dailyStats['pending_samples']); ?>

                </small>
                <small class="text-warning d-block d-md-none">
                    <i class="fas fa-clock me-1"></i>
                    Bekle: <?php echo e($dailyStats['pending_samples']); ?>

                </small>
            </div>
        </div>
    </div>
    
    <div class="col-6 col-md-3 mb-3">
        <div class="card stat-card h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="stat-number text-primary" id="quality-rate"><?php echo e($weeklyStats['quality_rate']); ?>%</div>
                <div class="text-muted d-none d-sm-block">Kalite Oranı</div>
                <div class="text-muted d-block d-sm-none">Kalite</div>
                <small class="text-danger d-none d-md-block">
                    <i class="fas fa-times-circle me-1"></i>
                    Reddedilen: <?php echo e($dailyStats['rejected_samples']); ?>

                </small>
                <small class="text-danger d-block d-md-none">
                    <i class="fas fa-times me-1"></i>
                    Red: <?php echo e($dailyStats['rejected_samples']); ?>

                </small>
            </div>
        </div>
    </div>
</div>

<!-- Ocak Durumu ve Aktif Dökümler -->
<div class="row mb-4">
    <div class="col-12 col-lg-8 mb-4 mb-lg-0">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-fire me-2"></i>
                    <span class="d-none d-sm-inline">Ocak Durumu ve Aktif Dökümler</span>
                    <span class="d-inline d-sm-none">Ocaklar</span>
                </h5>
                <span class="badge bg-success"><?php echo e(count($activeFurnaces)); ?> Aktif</span>
            </div>
            <div class="card-body">
                <?php if(empty($setStats)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                        <p>Henüz ocak verisi bulunmuyor</p>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php $__currentLoopData = $setStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $setStat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-12 col-sm-6 col-lg-4 mb-3">
                                <div class="card border-0 bg-light h-100">
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="furnace-status <?php echo e($setStat['active_furnace'] ? 'furnace-active' : 'furnace-inactive'); ?>"></span>
                                            <h6 class="mb-0 flex-grow-1"><?php echo e($setStat['set']->name); ?></h6>
                                        </div>
                                        
                                        <?php if($setStat['active_furnace']): ?>
                                            <div class="mb-2">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <strong class="text-primary"><?php echo e($setStat['active_furnace']->name); ?></strong>
                                                    <span class="badge bg-success badge-sm">Aktif</span>
                                                </div>
                                            </div>
                                            
                                            <div class="small text-muted d-none d-md-block mb-2">
                                                <div class="row">
                                                    <div class="col-4 text-center">
                                                        <div class="fw-bold"><?php echo e($setStat['daily_castings']); ?></div>
                                                        <div>Günlük</div>
                                                    </div>
                                                    <div class="col-4 text-center">
                                                        <div class="fw-bold"><?php echo e($setStat['weekly_castings']); ?></div>
                                                        <div>Haftalık</div>
                                                    </div>
                                                    <div class="col-4 text-center">
                                                        <div class="fw-bold"><?php echo e($setStat['monthly_castings']); ?></div>
                                                        <div>Aylık</div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="small text-muted d-block d-md-none mb-2">
                                                <div class="d-flex justify-content-between">
                                                    <span>Gün: <strong><?php echo e($setStat['daily_castings']); ?></strong></span>
                                                    <span>Hafta: <strong><?php echo e($setStat['weekly_castings']); ?></strong></span>
                                                    <span>Ay: <strong><?php echo e($setStat['monthly_castings']); ?></strong></span>
                                                </div>
                                            </div>
                                            
                                            <?php
                                                $activeCasting = collect($recentActivities['active_castings'])->firstWhere('furnace_id', $setStat['active_furnace']->id);
                                            ?>
                                            
                                            <?php if($activeCasting): ?>
                                                <div class="mt-auto p-2 bg-white rounded">
                                                    <div class="small">
                                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                                            <strong>Döküm #<?php echo e($activeCasting->casting_number); ?></strong>
                                                            <span class="badge <?php echo e($activeCasting->getQualityStatus() === 'approved' ? 'bg-success' : ($activeCasting->getQualityStatus() === 'rejected' ? 'bg-danger' : 'bg-warning')); ?>">
                                                                <?php echo e(ucfirst($activeCasting->getQualityStatus())); ?>

                                                            </span>
                                                        </div>
                                                        <div class="text-muted">Prova: <?php echo e($activeCasting->samples->count()); ?> adet</div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="mt-auto">
                                                <p class="text-muted mb-0 text-center">
                                                    <i class="fas fa-power-off me-1"></i>
                                                    Aktif ocak yok
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    <span class="d-none d-sm-inline">Günlük Kalite Dağılımı</span>
                    <span class="d-inline d-sm-none">Kalite</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height: 200px; max-height: 200px;">
                    <canvas id="qualityChart"></canvas>
                </div>
                
                <div class="mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small">Onaylanan</span>
                        <span class="badge badge-quality-approved"><?php echo e($dailyStats['approved_samples']); ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small">Reddedilen</span>
                        <span class="badge badge-quality-rejected"><?php echo e($dailyStats['rejected_samples']); ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small">Bekleyen</span>
                        <span class="badge badge-quality-pending"><?php echo e($dailyStats['pending_samples']); ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small">Düzeltme Gerekli</span>
                        <span class="badge badge-quality-needs-adjustment"><?php echo e($dailyStats['needs_adjustment']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Son Aktiviteler -->
<div class="row">
    <div class="col-12 col-lg-6 mb-4 mb-lg-0">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-clock me-2"></i>
                    <span class="d-none d-sm-inline">Son Provalar</span>
                    <span class="d-inline d-sm-none">Provalar</span>
                </h5>
                <a href="<?php echo e(route('samples.index')); ?>" class="btn btn-outline-primary btn-sm">
                    <span class="d-none d-sm-inline">Tümünü Gör</span>
                    <span class="d-inline d-sm-none">Tümü</span>
                </a>
            </div>
            <div class="card-body">
                <?php if($recentActivities['latest_samples']->isEmpty()): ?>
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-info-circle me-1"></i>
                        Henüz prova kaydı yok
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php $__currentLoopData = $recentActivities['latest_samples']->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sample): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="list-group-item px-0 border-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-1">
                                            <h6 class="mb-0 me-2">
                                                <span class="d-none d-sm-inline"><?php echo e($sample->casting->furnace->name ?? 'N/A'); ?> - </span>
                                                Döküm #<?php echo e($sample->casting->casting_number); ?>

                                            </h6>
                                        </div>
                                        <p class="mb-1 small">
                                            Prova #<?php echo e($sample->sample_number); ?> - 
                                            <?php echo e($sample->analyzed_by); ?>

                                        </p>
                                        <small class="text-muted">
                                            <?php echo e($sample->sample_time->diffForHumans()); ?>

                                        </small>
                                    </div>
                                    <span class="badge 
                                        <?php if($sample->quality_status === 'approved'): ?> bg-success
                                        <?php elseif($sample->quality_status === 'rejected'): ?> bg-danger
                                        <?php elseif($sample->quality_status === 'pending'): ?> bg-warning
                                        <?php elseif($sample->quality_status === 'needs_adjustment'): ?> bg-info
                                        <?php else: ?> bg-secondary
                                        <?php endif; ?> ms-2">
                                        <?php if($sample->quality_status === 'approved'): ?> Onaylandı
                                        <?php elseif($sample->quality_status === 'rejected'): ?> Reddedildi
                                        <?php elseif($sample->quality_status === 'pending'): ?> Beklemede
                                        <?php elseif($sample->quality_status === 'needs_adjustment'): ?> Düzeltme Gerekli
                                        <?php else: ?> <?php echo e($sample->quality_status); ?>

                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-tools me-2"></i>
                    <span class="d-none d-sm-inline">Son Ham Madde Eklemeleri</span>
                    <span class="d-inline d-sm-none">Ham Madde</span>
                </h5>
                <span class="badge bg-info"><?php echo e($dailyStats['total_adjustments']); ?> Bugün</span>
            </div>
            <div class="card-body">
                <?php if($recentActivities['latest_adjustments']->isEmpty()): ?>
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-info-circle me-1"></i>
                        Bugün ham madde eklenmedi
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php $__currentLoopData = $recentActivities['latest_adjustments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $adjustment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="list-group-item px-0 border-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <?php echo e($adjustment->getMaterialNameTurkish()); ?>

                                            <small class="text-muted">(<?php echo e($adjustment->amount_kg); ?> kg)</small>
                                        </h6>
                                        <p class="mb-1 small">
                                            <span class="d-none d-sm-inline"><?php echo e($adjustment->casting->furnace->name ?? 'N/A'); ?> - </span>
                                            Döküm #<?php echo e($adjustment->casting->casting_number); ?>

                                        </p>
                                        <small class="text-muted">
                                            <?php echo e($adjustment->adjustment_date->diffForHumans()); ?> - 
                                            <?php echo e($adjustment->added_by); ?>

                                        </small>
                                    </div>
                                    <span class="badge <?php echo e($adjustment->is_successful ? 'bg-success' : 'bg-warning'); ?> ms-2">
                                        <?php echo e($adjustment->is_successful ? 'Başarılı' : 'Beklemede'); ?>

                                    </span>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Hızlı İşlemler Modal -->
<div class="modal fade" id="quickActionsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hızlı İşlemler</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-grid gap-2">
                    <a href="<?php echo e(route('samples.create')); ?>" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Yeni Prova Ekle
                    </a>
                    <a href="<?php echo e(route('samples.pending')); ?>" class="btn btn-warning">
                        <i class="fas fa-hourglass-half me-2"></i>
                        Bekleyen Provaları Görüntüle
                    </a>
                    <a href="<?php echo e(route('reports.daily')); ?>" class="btn btn-info">
                        <i class="fas fa-chart-line me-2"></i>
                        Günlük Rapor
                    </a>
                    <button type="button" class="btn btn-success" onclick="exportDailyReport()">
                        <i class="fas fa-download me-2"></i>
                        Rapor İndir
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
let autoRefreshInterval = null;
let isAutoRefreshEnabled = false;

// Kalite dağılımı grafiği
const ctx = document.getElementById('qualityChart').getContext('2d');
const qualityChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Onaylanan', 'Reddedilen', 'Bekleyen', 'Düzeltme Gerekli'],
        datasets: [{
            data: [
                <?php echo e($dailyStats['approved_samples']); ?>,
                <?php echo e($dailyStats['rejected_samples']); ?>,
                <?php echo e($dailyStats['pending_samples']); ?>,
                <?php echo e($dailyStats['needs_adjustment']); ?>

            ],
            backgroundColor: [
                '#059669', // success
                '#dc2626', // danger
                '#d97706', // warning
                '#0284c7'  // info
            ],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            x: {
                display: false
            },
            y: {
                display: false
            }
        }
    }
});

// Dashboard yenileme fonksiyonu
function refreshDashboard() {
    const refreshBtn = document.querySelector('button[onclick="refreshDashboard()"]');
    showLoading(refreshBtn);
    
    fetch('<?php echo e(route("dashboard.realtime")); ?>')
        .then(response => response.json())
        .then(data => {
            updateDashboardData(data);
            showToast('Dashboard güncellendi', 'success');
        })
        .catch(error => {
            console.error('Dashboard güncellenemedi:', error);
            showToast('Dashboard güncellenirken hata oluştu', 'error');
        })
        .finally(() => {
            hideLoading(refreshBtn);
        });
}

// Dashboard verilerini güncelle
function updateDashboardData(data) {
    // Buraya gerçek zamanlı veri güncelleme kodları gelecek
    console.log('Dashboard data updated:', data);
}

// Otomatik yenileme toggle
function toggleAutoRefresh() {
    const button = document.querySelector('button[onclick="toggleAutoRefresh()"]');
    const text = document.getElementById('auto-refresh-text');
    
    if (isAutoRefreshEnabled) {
        clearInterval(autoRefreshInterval);
        isAutoRefreshEnabled = false;
        button.classList.remove('btn-success');
        button.classList.add('btn-outline-info');
        text.textContent = 'Otomatik Yenileme';
        showToast('Otomatik yenileme kapatıldı', 'info');
    } else {
        autoRefreshInterval = setInterval(refreshDashboard, 30000); // 30 saniye
        isAutoRefreshEnabled = true;
        button.classList.remove('btn-outline-info');
        button.classList.add('btn-success');
        text.textContent = 'Otomatik Açık';
        showToast('Otomatik yenileme açıldı (30s)', 'success');
    }
}

// Günlük rapor export
function exportDailyReport() {
    const today = new Date().toISOString().split('T')[0];
    window.open(`<?php echo e(route('reports.export')); ?>?type=daily&date=${today}`, '_blank');
}

// Kalite durumu badge sınıfları
function getQualityBadgeClass(status) {
    switch(status) {
        case 'approved': return 'bg-success';
        case 'rejected': return 'bg-danger';
        case 'pending': return 'bg-warning';
        case 'needs_adjustment': return 'bg-info';
        default: return 'bg-secondary';
    }
}

// Kalite durumu Türkçe metinleri
function getQualityStatusText(status) {
    switch(status) {
        case 'approved': return 'Onaylandı';
        case 'rejected': return 'Reddedildi';
        case 'pending': return 'Beklemede';
        case 'needs_adjustment': return 'Düzeltme Gerekli';
        default: return status;
    }
}

// Klavye kısayolları
document.addEventListener('keydown', function(e) {
    // Ctrl + R: Dashboard yenile
    if (e.ctrlKey && e.key === 'r') {
        e.preventDefault();
        refreshDashboard();
    }
    
    // Ctrl + N: Yeni prova
    if (e.ctrlKey && e.key === 'n') {
        e.preventDefault();
        window.location.href = '<?php echo e(route("samples.create")); ?>';
    }
    
    // Ctrl + P: Bekleyen provalar
    if (e.ctrlKey && e.key === 'p') {
        e.preventDefault();
        window.location.href = '<?php echo e(route("samples.pending")); ?>';
    }
});

// Sayfa yüklendiğinde
document.addEventListener('DOMContentLoaded', function() {
    // Otomatik yenileme geçici olarak devre dışı
    console.log('Dashboard loaded - Auto refresh disabled');
});

// Sayfa kapanırken otomatik yenilemeyi durdur
window.addEventListener('beforeunload', function() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\cansan\resources\views/dashboard/index.blade.php ENDPATH**/ ?>