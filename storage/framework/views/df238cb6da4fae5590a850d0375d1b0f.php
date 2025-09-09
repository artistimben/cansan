

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Başlık -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-fire text-primary"></i>
                        <?php echo e($furnace->furnaceSet->name); ?> - <?php echo e($furnace->name); ?> Detay Raporu
                    </h1>
                    <p class="text-muted mb-0">Ocak detaylı raporu ve istatistikleri</p>
                </div>
                <div>
                    <a href="<?php echo e(route('furnace-reports.index')); ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Raporlara Dön
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarih Filtresi -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="<?php echo e(route('furnace-reports.furnace-detail', $furnace)); ?>">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Başlangıç Tarihi</label>
                                <input type="date" class="form-control" name="date_from" value="<?php echo e($dateFrom); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Bitiş Tarihi</label>
                                <input type="date" class="form-control" name="date_to" value="<?php echo e($dateTo); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter"></i> Filtrele
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- İstatistikler -->
    <div class="row mb-4">
        <!-- Döküm İstatistikleri -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-fire fa-2x text-primary mb-2"></i>
                    <h5 class="card-title"><?php echo e($castingStats['total']); ?></h5>
                    <p class="card-text">Toplam Döküm</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <h5 class="card-title"><?php echo e($castingStats['completed']); ?></h5>
                    <p class="card-text">Tamamlanan</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-play-circle fa-2x text-info mb-2"></i>
                    <h5 class="card-title"><?php echo e($castingStats['active']); ?></h5>
                    <p class="card-text">Aktif</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                    <h5 class="card-title"><?php echo e($castingStats['cancelled']); ?></h5>
                    <p class="card-text">İptal Edilen</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Prova İstatistikleri -->
    <div class="row mb-4">
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-flask fa-2x text-info mb-2"></i>
                    <h5 class="card-title"><?php echo e($sampleStats['total']); ?></h5>
                    <p class="card-text">Toplam Prova</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-check fa-2x text-success mb-2"></i>
                    <h5 class="card-title"><?php echo e($sampleStats['approved']); ?></h5>
                    <p class="card-text">Onaylanan</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-times fa-2x text-danger mb-2"></i>
                    <h5 class="card-title"><?php echo e($sampleStats['rejected']); ?></h5>
                    <p class="card-text">Reddedilen</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-percentage fa-2x text-primary mb-2"></i>
                    <h5 class="card-title">%<?php echo e($sampleStats['approval_rate']); ?></h5>
                    <p class="card-text">Onay Oranı</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Durum Geçmişi -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history"></i> Durum Geçmişi
                        <span class="badge bg-primary ms-2"><?php echo e($statusLogs->count()); ?> kayıt</span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if($statusLogs->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tarih</th>
                                        <th>Durum</th>
                                        <th>Önceki Durum</th>
                                        <th>Neden</th>
                                        <th>Operatör</th>
                                        <th>Notlar</th>
                                        <th>Döküm Sayısı</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $statusLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e(\Carbon\Carbon::parse($log->status_changed_at)->format('d.m.Y H:i')); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo e($log->status === 'active' ? 'success' : ($log->status === 'maintenance' ? 'warning' : ($log->status === 'shutdown' ? 'danger' : 'secondary'))); ?>">
                                                <?php echo e(ucfirst($log->status)); ?>

                                            </span>
                                        </td>
                                        <td>
                                            <?php if($log->previous_status): ?>
                                                <span class="badge bg-light text-dark"><?php echo e(ucfirst($log->previous_status)); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e($log->reason ?? '-'); ?></td>
                                        <td><?php echo e($log->operator_name ?? '-'); ?></td>
                                        <td>
                                            <?php if($log->notes): ?>
                                                <span class="text-truncate d-inline-block" style="max-width: 200px;" title="<?php echo e($log->notes); ?>">
                                                    <?php echo e($log->notes); ?>

                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo e($log->castings_count_at_change); ?>

                                            <?php if($log->count_reset): ?>
                                                <i class="fas fa-redo text-warning ms-1" title="Sayaç sıfırlandı"></i>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Durum geçmişi bulunmuyor</h5>
                            <p class="text-muted">Seçilen tarih aralığında durum değişikliği kaydı yok.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\cansan\kk-cansan\resources\views/furnace-reports/furnace-detail.blade.php ENDPATH**/ ?>