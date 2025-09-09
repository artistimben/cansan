

<?php $__env->startSection('title', 'Ocaklar - Cansan Kalite Kontrol'); ?>

<?php $__env->startSection('header', 'Ocak Yönetimi'); ?>

<?php $__env->startSection('header-buttons'); ?>
    <div class="btn-group" role="group">
        <a href="<?php echo e(route('furnaces.create')); ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>
            Yeni Ocak
        </a>
        <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#statusModal">
            <i class="fas fa-chart-bar me-1"></i>
            Durum Özeti
        </button>
        <button type="button" class="btn btn-outline-success btn-sm" onclick="exportFurnaces()">
            <i class="fas fa-download me-1"></i>
            Export
        </button>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<!-- Hızlı İstatistikler -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-fire text-danger fa-2x mb-2"></i>
                <h4 class="text-danger mb-1"><?php echo e($furnaces->where('status', 'active')->count()); ?></h4>
                <small class="text-muted">Aktif Ocak</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-pause-circle text-warning fa-2x mb-2"></i>
                <h4 class="text-warning mb-1"><?php echo e($furnaces->where('status', 'idle')->count()); ?></h4>
                <small class="text-muted">Beklemede</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-tools text-info fa-2x mb-2"></i>
                <h4 class="text-info mb-1"><?php echo e($furnaces->where('status', 'maintenance')->count()); ?></h4>
                <small class="text-muted">Bakımda</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-stop-circle text-secondary fa-2x mb-2"></i>
                <h4 class="text-secondary mb-1"><?php echo e($furnaces->where('status', 'inactive')->count()); ?></h4>
                <small class="text-muted">Kapalı</small>
            </div>
        </div>
    </div>
</div>

<!-- Set Bazlı Ocak Görünümü -->
<div class="row mb-4">
    <?php $__currentLoopData = $furnaceSets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $set): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-layer-group me-2"></i>
                        <?php echo e($set->name); ?>

                    </h6>
                    <span class="badge bg-light text-dark">
                        <?php echo e($set->furnaces->where('status', 'active')->count()); ?>/<?php echo e($set->furnaces->count()); ?> Aktif
                    </span>
                </div>
                <div class="card-body p-2">
                    <?php $__currentLoopData = $set->furnaces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $furnace): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="d-flex justify-content-between align-items-center p-2 mb-2 border rounded">
                            <div>
                                <strong><?php echo e($furnace->name); ?></strong>
                                <br><small class="text-muted">Kapasite: <?php echo e($furnace->capacity ?? 'N/A'); ?> ton</small>
                                <br><small class="text-info">
                                    <i class="fas fa-fire"></i> 
                                    <?php echo e($furnace->castings->count()); ?> döküm 
                                    <?php if($furnace->castings->count() > 0): ?>
                                        (Sıradaki: <?php echo e($furnace->castings->count() + 1); ?>.)
                                    <?php endif; ?>
                                </small>
                            </div>
                            <div class="text-end">
                                <span class="badge 
                                    <?php if($furnace->status === 'active'): ?> bg-success
                                    <?php elseif($furnace->status === 'idle'): ?> bg-warning
                                    <?php elseif($furnace->status === 'maintenance'): ?> bg-info
                                    <?php else: ?> bg-secondary
                                    <?php endif; ?>">
                                    <?php if($furnace->status === 'active'): ?> Aktif
                                    <?php elseif($furnace->status === 'idle'): ?> Beklemede
                                    <?php elseif($furnace->status === 'maintenance'): ?> Bakımda
                                    <?php else: ?> Kapalı
                                    <?php endif; ?>
                                </span>
                                <?php if($furnace->status === 'active'): ?>
                                    <br><small class="text-success">
                                        <i class="fas fa-fire"></i>
                                        <?php echo e($furnace->current_temperature ?? 'N/A'); ?>°C
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<!-- Detaylı Ocak Listesi -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Tüm Ocaklar
                </h5>
                <small class="text-muted"><?php echo e($furnaces->count()); ?> ocak</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Ocak</th>
                                <th>Set</th>
                                <th>Durum</th>
                                <th>Kapasite</th>
                                <th>Sıcaklık</th>
                                <th>Son Döküm</th>
                                <th>Aktif Döküm</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $furnaces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $furnace): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <strong><?php echo e($furnace->name); ?></strong>
                                        <?php if($furnace->description): ?>
                                            <br><small class="text-muted"><?php echo e($furnace->description); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo e($furnace->furnaceSet->name); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge 
                                            <?php if($furnace->status === 'active'): ?> bg-success
                                            <?php elseif($furnace->status === 'idle'): ?> bg-warning
                                            <?php elseif($furnace->status === 'maintenance'): ?> bg-info
                                            <?php else: ?> bg-secondary
                                            <?php endif; ?>">
                                            <?php if($furnace->status === 'active'): ?> Aktif
                                            <?php elseif($furnace->status === 'idle'): ?> Beklemede
                                            <?php elseif($furnace->status === 'maintenance'): ?> Bakımda
                                            <?php else: ?> Kapalı
                                            <?php endif; ?>
                                        </span>
                                        <?php if($furnace->status_updated_at): ?>
                                            <br><small class="text-muted"><?php echo e($furnace->status_updated_at->diffForHumans()); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo e($furnace->capacity ?? 'N/A'); ?> ton
                                        <?php if($furnace->max_temperature): ?>
                                            <br><small class="text-muted">Max: <?php echo e($furnace->max_temperature); ?>°C</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($furnace->current_temperature && $furnace->status === 'active'): ?>
                                            <span class="text-danger">
                                                <i class="fas fa-thermometer-half"></i>
                                                <?php echo e($furnace->current_temperature); ?>°C
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($furnace->castings->count() > 0): ?>
                                            <?php $lastCasting = $furnace->castings->first(); ?>
                                            <strong>#<?php echo e($lastCasting->casting_number); ?></strong>
                                            <br><small class="text-muted"><?php echo e($lastCasting->casting_date->format('d.m.Y H:i')); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">Döküm yok</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php $activeCasting = $furnace->castings->where('status', 'active')->first(); ?>
                                        <?php if($activeCasting): ?>
                                            <span class="badge bg-success">#<?php echo e($activeCasting->casting_number); ?></span>
                                            <br><small class="text-muted"><?php echo e($activeCasting->shift); ?> Vardiyası</small>
                                        <?php else: ?>
                                            <span class="text-muted">Aktif döküm yok</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?php echo e(route('furnaces.show', $furnace)); ?>" class="btn btn-outline-info btn-sm" title="Detay">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo e(route('furnaces.edit', $furnace)); ?>" class="btn btn-outline-primary btn-sm" title="Düzenle">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if($furnace->status !== 'active'): ?>
                                                <button type="button" class="btn btn-outline-success btn-sm" onclick="updateFurnaceStatus(<?php echo e($furnace->id); ?>, 'active')" title="Aktif Yap">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-outline-warning btn-sm" onclick="updateFurnaceStatus(<?php echo e($furnace->id); ?>, 'idle')" title="Bekletmeye Al">
                                                    <i class="fas fa-pause"></i>
                                                </button>
                                            <?php endif; ?>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" title="Daha Fazla">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="<?php echo e(route('castings.index', ['furnace_id' => $furnace->id])); ?>">
                                                        <i class="fas fa-fire me-2"></i>Dökümleri Görüntüle
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="<?php echo e(route('samples.index', ['furnace_id' => $furnace->id])); ?>">
                                                        <i class="fas fa-vial me-2"></i>Provalarını Görüntüle
                                                    </a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item" href="#" onclick="updateFurnaceStatus(<?php echo e($furnace->id); ?>, 'maintenance')">
                                                        <i class="fas fa-tools me-2"></i>Bakıma Al
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="updateFurnaceStatus(<?php echo e($furnace->id); ?>, 'inactive')">
                                                        <i class="fas fa-stop me-2"></i>Kapat
                                                    </a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Durum Özeti Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ocak Durum Özeti</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Set Bazlı Durum</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Set</th>
                                        <th>Aktif</th>
                                        <th>Toplam</th>
                                        <th>Oran</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $furnaceSets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $set): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($set->name); ?></td>
                                            <td><?php echo e($set->furnaces->where('status', 'active')->count()); ?></td>
                                            <td><?php echo e($set->furnaces->count()); ?></td>
                                            <td>
                                                <?php 
                                                    $percentage = $set->furnaces->count() > 0 
                                                        ? ($set->furnaces->where('status', 'active')->count() / $set->furnaces->count()) * 100 
                                                        : 0;
                                                ?>
                                                <div class="progress" style="height: 15px;">
                                                    <div class="progress-bar bg-success" style="width: <?php echo e($percentage); ?>%">
                                                        <?php echo e(number_format($percentage, 0)); ?>%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h6>Genel Durum Dağılımı</h6>
                        <canvas id="statusChart" width="300" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<!-- Durum Güncelleme Modal -->
<div class="modal fade" id="statusUpdateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ocak Durumu Güncelle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusUpdateForm">
                <div class="modal-body">
                    <input type="hidden" id="furnace_id" name="furnace_id">
                    <input type="hidden" id="new_status" name="new_status">
                    
                    <div class="mb-3">
                        <label for="status_notes" class="form-label">Durum Değişikliği Notları</label>
                        <textarea class="form-control" id="status_notes" name="status_notes" rows="3" placeholder="Durum değişikliği hakkında notlarınız..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Durumu Güncelle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Ocak durumu güncelle
function updateFurnaceStatus(furnaceId, newStatus) {
    document.getElementById('furnace_id').value = furnaceId;
    document.getElementById('new_status').value = newStatus;
    
    const statusNames = {
        'active': 'Aktif',
        'idle': 'Beklemede',
        'maintenance': 'Bakımda',
        'inactive': 'Kapalı'
    };
    
    document.querySelector('#statusUpdateModal .modal-title').textContent = 
        `Ocak Durumunu "${statusNames[newStatus]}" Olarak Güncelle`;
    
    const modal = new bootstrap.Modal(document.getElementById('statusUpdateModal'));
    modal.show();
}

// Durum güncelleme form submit
document.getElementById('statusUpdateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const furnaceId = formData.get('furnace_id');
    
    fetch(`/furnaces/${furnaceId}/update-status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            status: formData.get('new_status'),
            status_notes: formData.get('status_notes')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            location.reload();
        } else {
            showToast('Hata oluştu', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Hata oluştu', 'error');
    });
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('statusUpdateModal'));
    modal.hide();
});

// Export fonksiyonu
function exportFurnaces() {
    window.open('<?php echo e(route("furnaces.export")); ?>', '_blank');
    showToast('Ocak listesi export ediliyor...', 'info');
}

// Durum grafiği
function initStatusChart() {
    const ctx = document.getElementById('statusChart');
    if (!ctx) return;
    
    const statusData = {
        'Aktif': <?php echo e($furnaces->where('status', 'active')->count()); ?>,
        'Beklemede': <?php echo e($furnaces->where('status', 'idle')->count()); ?>,
        'Bakımda': <?php echo e($furnaces->where('status', 'maintenance')->count()); ?>,
        'Kapalı': <?php echo e($furnaces->where('status', 'inactive')->count()); ?>

    };
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(statusData),
            datasets: [{
                data: Object.values(statusData),
                backgroundColor: [
                    '#28a745', // Aktif - yeşil
                    '#ffc107', // Beklemede - sarı
                    '#17a2b8', // Bakımda - mavi
                    '#6c757d'  // Kapalı - gri
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Modal açıldığında grafiği başlat
document.getElementById('statusModal').addEventListener('shown.bs.modal', function() {
    initStatusChart();
});

// Klavye kısayolları
document.addEventListener('keydown', function(e) {
    // Ctrl + N: Yeni ocak
    if (e.ctrlKey && e.key === 'n') {
        e.preventDefault();
        window.location.href = '<?php echo e(route("furnaces.create")); ?>';
    }
    
    // Ctrl + I: Durum özeti
    if (e.ctrlKey && e.key === 'i') {
        e.preventDefault();
        const modal = new bootstrap.Modal(document.getElementById('statusModal'));
        modal.show();
    }
});

// Otomatik yenileme (1 dakikada bir)
setInterval(() => {
    if (!document.hidden) {
        location.reload();
    }
}, 60000);
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\cansan\kk-cansan\resources\views/furnaces/index.blade.php ENDPATH**/ ?>