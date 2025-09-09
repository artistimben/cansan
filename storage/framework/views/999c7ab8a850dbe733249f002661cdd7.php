

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Başlık -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-fire text-primary"></i>
                        Döküm Detayları
                    </h1>
                    <p class="text-muted mb-0">
                        <strong><?php echo e($casting->casting_number); ?></strong> - <?php echo e($casting->furnace->furnaceSet->name); ?> <?php echo e($casting->furnace->name); ?>

                    </p>
                </div>
                <div>
                    <div class="btn-group">
                        <a href="<?php echo e(route('castings.index')); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Geri Dön
                        </a>
                        <?php if($casting->status === 'active'): ?>
                            <button type="button" class="btn btn-success complete-casting" data-casting-id="<?php echo e($casting->id); ?>">
                                <i class="fas fa-check"></i> Dökümü Tamamla
                            </button>
                            <button type="button" class="btn btn-danger cancel-casting" data-casting-id="<?php echo e($casting->id); ?>">
                                <i class="fas fa-times"></i> Dökümü İptal Et
                            </button>
                        <?php endif; ?>
                        <a href="<?php echo e(route('samples.create', ['casting_id' => $casting->id])); ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Yeni Prova Ekle
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Sol Kolon -->
        <div class="col-md-8">
            <!-- Döküm Bilgileri -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> Döküm Bilgileri
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Döküm Numarası:</strong></td>
                                    <td><?php echo e($casting->casting_number); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Ocak:</strong></td>
                                    <td><?php echo e($casting->furnace->furnaceSet->name); ?> - <?php echo e($casting->furnace->name); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Tarih/Saat:</strong></td>
                                    <td><?php echo e($casting->casting_date->format('d.m.Y H:i')); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Vardiya:</strong></td>
                                    <td>
                                        <span class="badge bg-<?php echo e($casting->shift === 'Gündüz' ? 'warning' : 'info'); ?>">
                                            <?php echo e($casting->shift); ?>

                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Operatör:</strong></td>
                                    <td><?php echo e($casting->operator_name); ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Durum:</strong></td>
                                    <td>
                                        <span class="badge 
                                            <?php if($casting->status === 'active'): ?> bg-success
                                            <?php elseif($casting->status === 'completed'): ?> bg-primary
                                            <?php elseif($casting->status === 'cancelled'): ?> bg-danger
                                            <?php else: ?> bg-secondary
                                            <?php endif; ?>">
                                            <?php if($casting->status === 'active'): ?> 
                                                <i class="fas fa-play"></i> Aktif
                                            <?php elseif($casting->status === 'completed'): ?> 
                                                <i class="fas fa-check"></i> Tamamlandı
                                            <?php elseif($casting->status === 'cancelled'): ?> 
                                                <i class="fas fa-times"></i> İptal
                                            <?php else: ?> 
                                                <?php echo e($casting->status); ?>

                                            <?php endif; ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Başlama Zamanı:</strong></td>
                                    <td><?php echo e($casting->started_at->format('d.m.Y H:i')); ?></td>
                                </tr>
                                <?php if($casting->completed_at): ?>
                                <tr>
                                    <td><strong>Bitiş Zamanı:</strong></td>
                                    <td><?php echo e($casting->completed_at->format('d.m.Y H:i')); ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td><strong>Süre:</strong></td>
                                    <td>
                                        <span class="text-info">
                                            <i class="fas fa-clock"></i>
                                            <?php echo e($stats['duration']); ?> dakika
                                        </span>
                                    </td>
                                </tr>
                                <?php if($casting->target_temperature): ?>
                                <tr>
                                    <td><strong>Hedef Sıcaklık:</strong></td>
                                    <td>
                                        <span class="text-warning">
                                            <i class="fas fa-thermometer-half"></i>
                                            <?php echo e($casting->target_temperature); ?>°C
                                        </span>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                    <?php if($casting->notes): ?>
                        <div class="mt-3">
                            <strong>Notlar:</strong>
                            <div class="alert alert-info mt-2">
                                <?php echo e($casting->notes); ?>

                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Provalar -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-vial"></i> Provalar
                        <span class="badge bg-primary ms-2"><?php echo e($stats['total_samples']); ?></span>
                    </h5>
                    <a href="<?php echo e(route('samples.create', ['casting_id' => $casting->id])); ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Prova Ekle
                    </a>
                </div>
                <div class="card-body">
                    <?php if($casting->samples->count() > 0): ?>
                        <div class="row">
                            <?php $__currentLoopData = $casting->samples->sortBy('sample_number'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sample): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card border-start border-4 
                                        <?php if($sample->quality_status === 'approved'): ?> border-success
                                        <?php elseif($sample->quality_status === 'rejected'): ?> border-danger
                                        <?php elseif($sample->quality_status === 'pending'): ?> border-warning
                                        <?php elseif($sample->quality_status === 'needs_adjustment'): ?> border-info
                                        <?php else: ?> border-secondary
                                        <?php endif; ?>">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-vial"></i>
                                                    <?php echo e($sample->sample_number); ?>. Prova
                                                </h6>
                                                <span class="badge 
                                                    <?php if($sample->quality_status === 'approved'): ?> bg-success
                                                    <?php elseif($sample->quality_status === 'rejected'): ?> bg-danger
                                                    <?php elseif($sample->quality_status === 'pending'): ?> bg-warning
                                                    <?php elseif($sample->quality_status === 'needs_adjustment'): ?> bg-info
                                                    <?php else: ?> bg-secondary
                                                    <?php endif; ?>">
                                                    <?php if($sample->quality_status === 'approved'): ?> Onaylandı
                                                    <?php elseif($sample->quality_status === 'rejected'): ?> Reddedildi
                                                    <?php elseif($sample->quality_status === 'pending'): ?> Beklemede
                                                    <?php elseif($sample->quality_status === 'needs_adjustment'): ?> Düzeltme Gerekli
                                                    <?php else: ?> <?php echo e($sample->quality_status); ?>

                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                            
                                            <div class="row text-center mb-2">
                                                <div class="col-4">
                                                    <small class="text-muted">Karbon</small>
                                                    <div><strong><?php echo e(number_format($sample->carbon_content, 2)); ?>%</strong></div>
                                                </div>
                                                <div class="col-4">
                                                    <small class="text-muted">Mangan</small>
                                                    <div><strong><?php echo e(number_format($sample->manganese_content, 2)); ?>%</strong></div>
                                                </div>
                                                <div class="col-4">
                                                    <small class="text-muted">Sıcaklık</small>
                                                    <div><strong><?php echo e($sample->temperature); ?>°C</strong></div>
                                                </div>
                                            </div>
                                            
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    <i class="fas fa-clock"></i>
                                                    <?php echo e($sample->sample_time->format('H:i')); ?>

                                                </small>
                                                <div class="btn-group" role="group">
                                                    <a href="<?php echo e(route('samples.show', $sample)); ?>" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="Detay">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="<?php echo e(route('samples.edit', $sample)); ?>" 
                                                       class="btn btn-sm btn-outline-warning" 
                                                       title="Düzenle">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-vial fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">Henüz prova alınmamış</h6>
                            <p class="text-muted">Bu döküm için prova eklemek üzere aşağıdaki butona tıklayın</p>
                            <a href="<?php echo e(route('samples.create', ['casting_id' => $casting->id])); ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> İlk Provayı Ekle
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sağ Kolon -->
        <div class="col-md-4">
            <!-- İstatistikler -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-pie"></i> Döküm İstatistikleri
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary mb-1"><?php echo e($stats['total_samples']); ?></h4>
                                <small class="text-muted">Toplam Prova</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success mb-1"><?php echo e($stats['approved_samples']); ?></h4>
                            <small class="text-muted">Onaylanan</small>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-warning mb-1"><?php echo e($stats['pending_samples']); ?></h4>
                                <small class="text-muted">Beklemede</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-danger mb-1"><?php echo e($stats['rejected_samples']); ?></h4>
                            <small class="text-muted">Reddedilen</small>
                        </div>
                    </div>
                    
                    <?php if($stats['total_samples'] > 0): ?>
                        <hr>
                        <div class="progress mb-2" style="height: 8px;">
                            <div class="progress-bar bg-success" 
                                 style="width: <?php echo e(($stats['approved_samples'] / $stats['total_samples']) * 100); ?>%"></div>
                            <div class="progress-bar bg-warning" 
                                 style="width: <?php echo e(($stats['pending_samples'] / $stats['total_samples']) * 100); ?>%"></div>
                            <div class="progress-bar bg-danger" 
                                 style="width: <?php echo e(($stats['rejected_samples'] / $stats['total_samples']) * 100); ?>%"></div>
                        </div>
                        <small class="text-muted">
                            Onay oranı: %<?php echo e(number_format(($stats['approved_samples'] / $stats['total_samples']) * 100, 1)); ?>

                        </small>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Ocak Bilgileri -->
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-fire"></i> Ocak Bilgileri
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-4"><strong>Set:</strong></div>
                        <div class="col-8"><?php echo e($casting->furnace->furnaceSet->name); ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4"><strong>Ocak:</strong></div>
                        <div class="col-8"><?php echo e($casting->furnace->name); ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4"><strong>Durum:</strong></div>
                        <div class="col-8">
                            <span class="badge bg-<?php echo e($casting->furnace->status === 'active' ? 'success' : 'secondary'); ?>">
                                <?php echo e(ucfirst($casting->furnace->status)); ?>

                            </span>
                        </div>
                    </div>
                    <?php if($casting->furnace->current_temperature): ?>
                    <div class="row mb-2">
                        <div class="col-4"><strong>Sıcaklık:</strong></div>
                        <div class="col-8">
                            <span class="text-danger">
                                <i class="fas fa-thermometer-half"></i>
                                <?php echo e($casting->furnace->current_temperature); ?>°C
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-4"><strong>Kapasite:</strong></div>
                        <div class="col-8"><?php echo e($casting->furnace->capacity ?? 'N/A'); ?> ton</div>
                    </div>
                </div>
            </div>

            <!-- Hızlı İşlemler -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-bolt"></i> Hızlı İşlemler
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?php echo e(route('samples.create', ['casting_id' => $casting->id])); ?>" 
                           class="btn btn-primary">
                            <i class="fas fa-plus"></i> Yeni Prova Ekle
                        </a>
                        <?php if($casting->status === 'active'): ?>
                            <button type="button" class="btn btn-success complete-casting" 
                                    data-casting-id="<?php echo e($casting->id); ?>">
                                <i class="fas fa-check"></i> Dökümü Tamamla
                            </button>
                        <?php endif; ?>
                        <a href="<?php echo e(route('castings.edit', $casting)); ?>" class="btn btn-outline-warning">
                            <i class="fas fa-edit"></i> Döküm Bilgilerini Düzenle
                        </a>
                        <a href="<?php echo e(route('reports.daily', ['casting_id' => $casting->id])); ?>" 
                           class="btn btn-outline-info">
                            <i class="fas fa-file-alt"></i> Döküm Raporu
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal'lar -->
<?php echo $__env->make('castings.partials.complete-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('castings.partials.cancel-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
$(document).ready(function() {
    // Döküm tamamlama
    $('.complete-casting').click(function() {
        const castingId = $(this).data('casting-id');
        $('#completeCastingModal').modal('show');
        $('#completeCastingForm').attr('action', `/castings/${castingId}/complete`);
    });

    // Döküm iptal etme
    $('.cancel-casting').click(function() {
        const castingId = $(this).data('casting-id');
        $('#cancelCastingModal').modal('show');
        $('#cancelCastingForm').attr('action', `/castings/${castingId}/cancel`);
    });
    
    // Hızlı neden seçimi
    $('.cancel-reason-btn').on('click', function() {
        const reason = $(this).data('reason');
        $('#cancellation_reason').val(reason);
        $(this).closest('.list-group').find('.list-group-item').removeClass('active');
        $(this).addClass('active');
    });

    // Complete form submit
    $('#completeCastingForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const url = form.attr('action');
        const formData = form.serialize();
        
        // Loading state
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Tamamlanıyor...').prop('disabled', true);
        
        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            },
            error: function(xhr) {
                alert('Bir hata oluştu: ' + (xhr.responseJSON?.message || 'Bilinmeyen hata'));
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });

    // Cancel form submit
    $('#cancelCastingForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const url = form.attr('action');
        const formData = form.serialize();
        
        // Loading state
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> İptal Ediliyor...').prop('disabled', true);
        
        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            },
            error: function(xhr) {
                alert('Bir hata oluştu: ' + (xhr.responseJSON?.message || 'Bilinmeyen hata'));
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
    
    // Otomatik yenileme (aktif döküm için)
    <?php if($casting->status === 'active'): ?>
        setInterval(function() {
            location.reload();
        }, 30000); // 30 saniyede bir yenile
    <?php endif; ?>
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\cansan\kk-cansan\resources\views/castings/show.blade.php ENDPATH**/ ?>