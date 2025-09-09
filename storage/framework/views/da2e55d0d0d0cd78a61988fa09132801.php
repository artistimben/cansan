

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Başlık -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-fire text-primary"></i>
                        Döküm Yönetimi
                    </h1>
                    <p class="text-muted mb-0">Ocak dökümlerini yönetin ve takip edin</p>
                </div>
                <div>
                    <a href="<?php echo e(route('castings.create')); ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Yeni Döküm Başlat
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtreler -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-filter"></i> Filtreler
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?php echo e(route('castings.index')); ?>">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">Ocak</label>
                                <select name="furnace_id" class="form-select">
                                    <option value="">Tüm Ocaklar</option>
                                    <?php $__currentLoopData = $furnaces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $furnace): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($furnace->id); ?>" 
                                            <?php echo e(request('furnace_id') == $furnace->id ? 'selected' : ''); ?>>
                                            <?php echo e($furnace->furnaceSet->name); ?> - <?php echo e($furnace->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Durum</label>
                                <select name="status" class="form-select">
                                    <option value="">Tüm Durumlar</option>
                                    <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($key); ?>" 
                                            <?php echo e(request('status') == $key ? 'selected' : ''); ?>>
                                            <?php echo e($status); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Vardiya</label>
                                <select name="shift" class="form-select">
                                    <option value="">Tüm Vardiyalar</option>
                                    <?php $__currentLoopData = $shifts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shift): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($shift); ?>" 
                                            <?php echo e(request('shift') == $shift ? 'selected' : ''); ?>>
                                            <?php echo e($shift); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Başlangıç Tarihi</label>
                                <input type="date" name="date_from" class="form-control" 
                                       value="<?php echo e(request('date_from')); ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Bitiş Tarihi</label>
                                <input type="date" name="date_to" class="form-control" 
                                       value="<?php echo e(request('date_to')); ?>">
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-search"></i>
                                </button>
                                <a href="<?php echo e(route('castings.index')); ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Döküm Listesi -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list"></i> Döküm Listesi
                        <span class="badge bg-primary ms-2"><?php echo e($castings->total()); ?></span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if($castings->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Döküm No</th>
                                        <th>Ocak</th>
                                        <th>Tarih/Saat</th>
                                        <th>Vardiya</th>
                                        <th>Operatör</th>
                                        <th>Durum</th>
                                        <th>Prova Sayısı</th>
                                        <th>Süre</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $castings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $casting): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td>
                                                <strong class="text-primary">
                                                    <?php echo e($casting->casting_number); ?>

                                                </strong>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="furnace-indicator bg-<?php echo e($casting->furnace->status === 'active' ? 'success' : 'secondary'); ?> me-2"></div>
                                                    <div>
                                                        <div class="fw-bold"><?php echo e($casting->furnace->name); ?></div>
                                                        <small class="text-muted"><?php echo e($casting->furnace->furnaceSet->name); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div><?php echo e($casting->casting_date->format('d.m.Y')); ?></div>
                                                <small class="text-muted"><?php echo e($casting->casting_date->format('H:i')); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo e($casting->shift === 'Gündüz' ? 'warning' : 'info'); ?>">
                                                    <?php echo e($casting->shift); ?>

                                                </span>
                                            </td>
                                            <td><?php echo e($casting->operator_name); ?></td>
                                            <td>
                                                <span class="badge 
                                                    <?php if($casting->status === 'active'): ?> bg-success
                                                    <?php elseif($casting->status === 'completed'): ?> bg-primary
                                                    <?php elseif($casting->status === 'cancelled'): ?> bg-danger
                                                    <?php else: ?> bg-secondary
                                                    <?php endif; ?>">
                                                    <?php if($casting->status === 'active'): ?> Aktif
                                                    <?php elseif($casting->status === 'completed'): ?> Tamamlandı
                                                    <?php elseif($casting->status === 'cancelled'): ?> İptal
                                                    <?php else: ?> <?php echo e($casting->status); ?>

                                                    <?php endif; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-info me-1"><?php echo e($casting->samples->count()); ?></span>
                                                    <?php if($casting->samples->where('quality_status', 'approved')->count() > 0): ?>
                                                        <span class="badge bg-success me-1">
                                                            <?php echo e($casting->samples->where('quality_status', 'approved')->count()); ?> ✓
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if($casting->samples->where('quality_status', 'pending')->count() > 0): ?>
                                                        <span class="badge bg-warning me-1">
                                                            <?php echo e($casting->samples->where('quality_status', 'pending')->count()); ?> ?
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if($casting->completed_at): ?>
                                                    <?php echo e($casting->started_at->diffInMinutes($casting->completed_at)); ?> dk
                                                <?php else: ?>
                                                    <span class="text-success">
                                                        <?php echo e($casting->started_at->diffInMinutes(now())); ?> dk
                                                        <i class="fas fa-clock fa-spin"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?php echo e(route('castings.show', $casting)); ?>" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="Detayları Görüntüle">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if($casting->status === 'active'): ?>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-success complete-casting" 
                                                                data-casting-id="<?php echo e($casting->id); ?>"
                                                                title="Dökümü Tamamla">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-danger cancel-casting" 
                                                                data-casting-id="<?php echo e($casting->id); ?>"
                                                                title="Dökümü İptal Et">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <a href="<?php echo e(route('samples.create', ['casting_id' => $casting->id])); ?>" 
                                                       class="btn btn-sm btn-outline-info" 
                                                       title="Prova Ekle">
                                                        <i class="fas fa-plus"></i> Prova
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="card-footer">
                            <?php echo e($castings->links()); ?>

                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-fire fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Henüz döküm bulunmuyor</h5>
                            <p class="text-muted">İlk dökümü başlatmak için "Yeni Döküm Başlat" butonuna tıklayın</p>
                            <a href="<?php echo e(route('castings.create')); ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Yeni Döküm Başlat
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.furnace-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}
</style>

<!-- Modal'lar castings/show sayfasında kullanılıyor -->

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
$(document).ready(function() {
    // Döküm tamamlama - sayfayı yönlendir
    $('.complete-casting').click(function() {
        const castingId = $(this).data('casting-id');
        if (confirm('Bu dökümü tamamlamak istediğinizden emin misiniz?')) {
            window.location.href = `/castings/${castingId}`;
        }
    });

    // Döküm iptal etme - sayfayı yönlendir
    $('.cancel-casting').click(function() {
        const castingId = $(this).data('casting-id');
        if (confirm('Bu dökümü iptal etmek istediğinizden emin misiniz?')) {
            window.location.href = `/castings/${castingId}`;
        }
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\cansan\kk-cansan\resources\views/castings/index.blade.php ENDPATH**/ ?>