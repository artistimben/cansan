

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Başlık -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-tools text-warning"></i>
                        Bakım Raporu
                    </h1>
                    <p class="text-muted mb-0">Bakım geçmişi ve refraktör değişimleri</p>
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
                    <form method="GET" action="<?php echo e(route('furnace-reports.maintenance')); ?>">
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
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="fas fa-filter"></i> Filtrele
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bakım Kayıtları -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-tools"></i> Bakım Kayıtları
                        <span class="badge bg-warning ms-2"><?php echo e($maintenanceLogs->count()); ?> kayıt</span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if($maintenanceLogs->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tarih</th>
                                        <th>Ocak</th>
                                        <th>İşlem Türü</th>
                                        <th>Neden</th>
                                        <th>Operatör</th>
                                        <th>Notlar</th>
                                        <th>Döküm Sayısı</th>
                                        <th>Sayaç Sıfırlandı</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $maintenanceLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e(\Carbon\Carbon::parse($log->status_changed_at)->format('d.m.Y H:i')); ?></td>
                                        <td>
                                            <strong><?php echo e($log->furnace->furnaceSet->name); ?> - <?php echo e($log->furnace->name); ?></strong>
                                        </td>
                                        <td>
                                            <?php if($log->status === 'refractory_change'): ?>
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-fire-extinguisher"></i> Refraktör Değişimi
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-tools"></i> Bakım
                                                </span>
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
                                        <td><?php echo e($log->castings_count_at_change); ?></td>
                                        <td>
                                            <?php if($log->count_reset): ?>
                                                <i class="fas fa-check text-success" title="Evet"></i>
                                            <?php else: ?>
                                                <i class="fas fa-times text-muted" title="Hayır"></i>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-tools fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Bakım kaydı bulunmuyor</h5>
                            <p class="text-muted">Seçilen tarih aralığında bakım kaydı yok.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\cansan\kk-cansan\resources\views/furnace-reports/maintenance.blade.php ENDPATH**/ ?>