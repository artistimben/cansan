

<?php $__env->startSection('title', 'Ocak Yönetimi - Cansan Kalite Kontrol'); ?>

<?php $__env->startSection('header', 'Ocak Yönetimi'); ?>

<?php $__env->startSection('header-buttons'); ?>
    <div class="btn-group d-none d-md-flex" role="group">
        <a href="<?php echo e(route('furnaces.index')); ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>
            Ocaklara Dön
        </a>
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="refreshFurnaces()">
            <i class="fas fa-sync-alt me-1"></i>
            Yenile
        </button>
    </div>
    
    <!-- Mobile buttons -->
    <div class="d-flex d-md-none gap-2">
        <a href="<?php echo e(route('furnaces.index')); ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i>
        </a>
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="refreshFurnaces()">
            <i class="fas fa-sync-alt"></i>
        </button>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Description -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <span class="d-none d-sm-inline">Refraktör değişimi, bakım ve duruş işlemleri</span>
                <span class="d-inline d-sm-none">Ocak işlemleri</span>
            </div>
        </div>
    </div>

    <!-- Ocaklar -->
    <div class="row">
        <?php $__currentLoopData = $furnaces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $furnace): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-12 col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-fire text-primary"></i>
                        <span class="d-none d-sm-inline"><?php echo e($furnace->furnaceSet->name); ?> - <?php echo e($furnace->name); ?></span>
                        <span class="d-inline d-sm-none"><?php echo e($furnace->name); ?></span>
                    </h5>
                    <span class="badge bg-<?php echo e($furnace->status === 'active' ? 'success' : ($furnace->status === 'maintenance' ? 'warning' : 'secondary')); ?>">
                        <?php echo e(ucfirst($furnace->status)); ?>

                    </span>
                </div>
                <div class="card-body">
                    <!-- Döküm İstatistikleri -->
                    <div class="row mb-3">
                        <div class="col-6">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Toplam Döküm</h6>
                                <h4 class="text-primary mb-0"><?php echo e($furnace->total_castings_count ?? 0); ?></h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Mevcut Döngü</h6>
                                <h4 class="text-info mb-0"><?php echo e($furnace->current_cycle_castings ?? 0); ?></h4>
                            </div>
                        </div>
                    </div>

                    <!-- Refraktör Bilgileri -->
                    <?php if($furnace->last_refractory_change): ?>
                    <div class="mb-3">
                        <small class="text-muted">Son Refraktör Değişimi:</small>
                        <div class="fw-bold"><?php echo e(\Carbon\Carbon::parse($furnace->last_refractory_change)->format('d.m.Y')); ?></div>
                        <small class="text-muted">Refraktörden Sonra: <?php echo e($furnace->castings_since_refractory ?? 0); ?> döküm</small>
                    </div>
                    <?php endif; ?>

                    <!-- Bakım İlerlemesi -->
                    <?php
                        $maintenanceProgress = $furnace->getMaintenanceProgress();
                    ?>
                    <?php if($maintenanceProgress['progress'] > 0): ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Bakım İlerlemesi</small>
                            <small class="text-muted"><?php echo e($maintenanceProgress['progress']); ?>%</small>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-<?php echo e($maintenanceProgress['needs_maintenance'] ? 'danger' : 'warning'); ?>" 
                                 style="width: <?php echo e($maintenanceProgress['progress']); ?>%"></div>
                        </div>
                        <?php if($maintenanceProgress['needs_maintenance']): ?>
                        <small class="text-danger">Bakım gerekli!</small>
                        <?php else: ?>
                        <small class="text-muted"><?php echo e($maintenanceProgress['days_remaining']); ?> gün kaldı</small>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Son Durum Değişiklikleri -->
                    <?php if($furnace->statusLogs->count() > 0): ?>
                    <div class="mb-3">
                        <small class="text-muted">Son Durum Değişiklikleri:</small>
                        <div class="mt-1">
                            <?php $__currentLoopData = $furnace->statusLogs->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="text-muted"><?php echo e(\Carbon\Carbon::parse($log->status_changed_at)->format('d.m H:i')); ?></small>
                                <span class="badge bg-light text-dark"><?php echo e(ucfirst($log->status)); ?></span>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <div class="row g-2">
                        <?php if($furnace->status === 'maintenance'): ?>
                            <div class="col-12">
                                <button class="btn btn-success btn-sm w-100" onclick="showEndMaintenanceModal(<?php echo e($furnace->id); ?>, '<?php echo e($furnace->name); ?>')">
                                    <i class="fas fa-check"></i> 
                                    <span class="d-none d-sm-inline">Bakımı Bitir</span>
                                    <span class="d-inline d-sm-none">Bitir</span>
                                </button>
                            </div>
                        <?php elseif($furnace->status === 'shutdown'): ?>
                            <div class="col-12">
                                <button class="btn btn-success btn-sm w-100" onclick="showEndShutdownModal(<?php echo e($furnace->id); ?>, '<?php echo e($furnace->name); ?>')">
                                    <i class="fas fa-play"></i> 
                                    <span class="d-none d-sm-inline">Devreye Al</span>
                                    <span class="d-inline d-sm-none">Başlat</span>
                                </button>
                            </div>
                        <?php else: ?>
                            <!-- Mobile buttons (stacked) -->
                            <div class="d-block d-sm-none">
                                <div class="d-grid gap-2">
                                    <button class="btn btn-warning btn-sm" onclick="showRefractoryModal(<?php echo e($furnace->id); ?>, '<?php echo e($furnace->name); ?>')">
                                        <i class="fas fa-fire-extinguisher me-1"></i>Refraktör Değişimi
                                    </button>
                                    <button class="btn btn-info btn-sm" onclick="showMaintenanceModal(<?php echo e($furnace->id); ?>, '<?php echo e($furnace->name); ?>')">
                                        <i class="fas fa-tools me-1"></i>Bakım Başlat
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="showShutdownModal(<?php echo e($furnace->id); ?>, '<?php echo e($furnace->name); ?>')">
                                        <i class="fas fa-power-off me-1"></i>Duruş Başlat
                                    </button>
                                    <button class="btn btn-secondary btn-sm" onclick="showResetModal(<?php echo e($furnace->id); ?>, '<?php echo e($furnace->name); ?>')">
                                        <i class="fas fa-redo me-1"></i>Sayaç Sıfırla
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Desktop buttons (grid) -->
                            <div class="d-none d-sm-block">
                                <div class="row g-2">
                                    <div class="col-3">
                                        <button class="btn btn-warning btn-sm w-100" onclick="showRefractoryModal(<?php echo e($furnace->id); ?>, '<?php echo e($furnace->name); ?>')" title="Refraktör Değişimi">
                                            <i class="fas fa-fire-extinguisher"></i> 
                                            <span class="d-none d-md-inline">Refraktör</span>
                                        </button>
                                    </div>
                                    <div class="col-3">
                                        <button class="btn btn-info btn-sm w-100" onclick="showMaintenanceModal(<?php echo e($furnace->id); ?>, '<?php echo e($furnace->name); ?>')" title="Bakım Başlat">
                                            <i class="fas fa-tools"></i> 
                                            <span class="d-none d-md-inline">Bakım</span>
                                        </button>
                                    </div>
                                    <div class="col-3">
                                        <button class="btn btn-danger btn-sm w-100" onclick="showShutdownModal(<?php echo e($furnace->id); ?>, '<?php echo e($furnace->name); ?>')" title="Duruş Başlat">
                                            <i class="fas fa-power-off"></i> 
                                            <span class="d-none d-md-inline">Duruş</span>
                                        </button>
                                    </div>
                                    <div class="col-3">
                                        <button class="btn btn-secondary btn-sm w-100" onclick="showResetModal(<?php echo e($furnace->id); ?>, '<?php echo e($furnace->name); ?>')" title="Döküm Sayacını Sıfırla">
                                            <i class="fas fa-redo"></i> 
                                            <span class="d-none d-md-inline">Sıfırla</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>

<!-- Refraktör Değişim Modal -->
<div class="modal fade" id="refractoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Refraktör Değişimi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="refractoryForm">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Dikkat!</strong> Refraktör değişimi yapıldığında döküm sayacı sıfırlanacaktır.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ocak</label>
                        <input type="text" class="form-control" id="refractoryFurnaceName" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notlar</label>
                        <textarea class="form-control" name="notes" rows="3" placeholder="Refraktör değişim notları..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Operatör Adı</label>
                        <input type="text" class="form-control" name="operator_name" placeholder="Operatör adı...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-warning">Refraktör Değiştir</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bakım Modal -->
<div class="modal fade" id="maintenanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bakım Başlat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="maintenanceForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Ocak</label>
                        <input type="text" class="form-control" id="maintenanceFurnaceName" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bakım Nedeni</label>
                        <input type="text" class="form-control" name="reason" placeholder="Bakım nedeni...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notlar</label>
                        <textarea class="form-control" name="notes" rows="3" placeholder="Bakım notları..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Operatör Adı</label>
                        <input type="text" class="form-control" name="operator_name" placeholder="Operatör adı...">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="reset_count" id="resetCount">
                        <label class="form-check-label" for="resetCount">
                            Döküm sayacını sıfırla
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-info">Bakım Başlat</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Duruş Modal -->
<div class="modal fade" id="shutdownModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Duruş Başlat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="shutdownForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Ocak</label>
                        <input type="text" class="form-control" id="shutdownFurnaceName" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Duruş Nedeni *</label>
                        <input type="text" class="form-control" name="reason" required placeholder="Duruş nedeni...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notlar</label>
                        <textarea class="form-control" name="notes" rows="3" placeholder="Duruş notları..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Operatör Adı</label>
                        <input type="text" class="form-control" name="operator_name" placeholder="Operatör adı...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-danger">Duruş Başlat</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Sıfırlama Modal -->
<div class="modal fade" id="resetModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Döküm Sayacını Sıfırla</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="resetForm">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Dikkat!</strong> Döküm sayacı sıfırlanacaktır. Bu işlem geri alınamaz.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ocak</label>
                        <input type="text" class="form-control" id="resetFurnaceName" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notlar</label>
                        <textarea class="form-control" name="notes" rows="3" placeholder="Sıfırlama notları..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Operatör Adı</label>
                        <input type="text" class="form-control" name="operator_name" placeholder="Operatör adı...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-secondary">Sıfırla</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bakım Bitirme Modal -->
<div class="modal fade" id="endMaintenanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bakımı Bitir</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="endMaintenanceForm">
                <div class="modal-body">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <strong>Bakım Tamamlandı!</strong> Ocağı hangi duruma almak istiyorsunuz?
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ocak</label>
                        <input type="text" class="form-control" id="endMaintenanceFurnaceName" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Yeni Durum *</label>
                        <select class="form-select" name="new_status" required>
                            <option value="">Durum seçiniz...</option>
                            <option value="active">Aktif</option>
                            <option value="idle">Beklemede</option>
                            <option value="inactive">Pasif</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bakım Notları</label>
                        <textarea class="form-control" name="notes" rows="3" placeholder="Bakım tamamlama notları..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Operatör Adı</label>
                        <input type="text" class="form-control" name="operator_name" placeholder="Operatör adı...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-success">Bakımı Bitir</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Duruş Bitirme Modal -->
<div class="modal fade" id="endShutdownModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Devreye Al</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="endShutdownForm">
                <div class="modal-body">
                    <div class="alert alert-success">
                        <i class="fas fa-play-circle"></i>
                        <strong>Duruş Tamamlandı!</strong> Ocağı hangi duruma almak istiyorsunuz?
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ocak</label>
                        <input type="text" class="form-control" id="endShutdownFurnaceName" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Yeni Durum *</label>
                        <select class="form-select" name="new_status" required>
                            <option value="">Durum seçiniz...</option>
                            <option value="active">Aktif</option>
                            <option value="idle">Beklemede</option>
                            <option value="maintenance">Bakım</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Duruş Notları</label>
                        <textarea class="form-control" name="notes" rows="3" placeholder="Duruş tamamlama notları..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Operatör Adı</label>
                        <input type="text" class="form-control" name="operator_name" placeholder="Operatör adı...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-success">Devreye Al</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
let currentFurnaceId = null;

// Refraktör değişim modal
function showRefractoryModal(furnaceId, furnaceName) {
    currentFurnaceId = furnaceId;
    document.getElementById('refractoryFurnaceName').value = furnaceName;
    new bootstrap.Modal(document.getElementById('refractoryModal')).show();
}

// Bakım modal
function showMaintenanceModal(furnaceId, furnaceName) {
    currentFurnaceId = furnaceId;
    document.getElementById('maintenanceFurnaceName').value = furnaceName;
    new bootstrap.Modal(document.getElementById('maintenanceModal')).show();
}

// Duruş modal
function showShutdownModal(furnaceId, furnaceName) {
    currentFurnaceId = furnaceId;
    document.getElementById('shutdownFurnaceName').value = furnaceName;
    new bootstrap.Modal(document.getElementById('shutdownModal')).show();
}

// Sıfırlama modal
function showResetModal(furnaceId, furnaceName) {
    currentFurnaceId = furnaceId;
    document.getElementById('resetFurnaceName').value = furnaceName;
    new bootstrap.Modal(document.getElementById('resetModal')).show();
}

// Bakım bitirme modal
function showEndMaintenanceModal(furnaceId, furnaceName) {
    currentFurnaceId = furnaceId;
    document.getElementById('endMaintenanceFurnaceName').value = furnaceName;
    new bootstrap.Modal(document.getElementById('endMaintenanceModal')).show();
}

// Duruş bitirme modal
function showEndShutdownModal(furnaceId, furnaceName) {
    currentFurnaceId = furnaceId;
    document.getElementById('endShutdownFurnaceName').value = furnaceName;
    new bootstrap.Modal(document.getElementById('endShutdownModal')).show();
}

// Form gönderimi
document.getElementById('refractoryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm('/furnace-management/' + currentFurnaceId + '/change-refractory', new FormData(this));
});

document.getElementById('maintenanceForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm('/furnace-management/' + currentFurnaceId + '/start-maintenance', new FormData(this));
});

document.getElementById('shutdownForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm('/furnace-management/' + currentFurnaceId + '/shutdown', new FormData(this));
});

document.getElementById('resetForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm('/furnace-management/' + currentFurnaceId + '/reset-casting-count', new FormData(this));
});

document.getElementById('endMaintenanceForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm('/furnace-management/' + currentFurnaceId + '/end-maintenance', new FormData(this));
});

document.getElementById('endShutdownForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm('/furnace-management/' + currentFurnaceId + '/end-shutdown', new FormData(this));
});

function submitForm(url, formData) {
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            // Modal'ı kapat
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                const modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance) {
                    modalInstance.hide();
                }
            });
            // Sayfayı yenile
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Bir hata oluştu', 'error');
    });
}

function showToast(message, type) {
    // Toast gösterimi (mevcut toast sisteminizi kullanın)
    console.log(type.toUpperCase() + ':', message);
}

// Furnace management refresh
function refreshFurnaces() {
    location.reload();
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\cansan\resources\views/furnace-management/index.blade.php ENDPATH**/ ?>