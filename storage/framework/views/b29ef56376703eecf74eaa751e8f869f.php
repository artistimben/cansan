

<?php $__env->startSection('title', $furnace->name . ' - Ocak Detayı'); ?>

<?php $__env->startSection('header', $furnace->name . ' Detayları'); ?>

<?php $__env->startSection('header-buttons'); ?>
    <div class="btn-group" role="group">
        <a href="<?php echo e(route('furnaces.index')); ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>
            Ocaklar
        </a>
        <a href="<?php echo e(route('furnaces.edit', $furnace)); ?>" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-edit me-1"></i>
            Düzenle
        </a>
        <?php if($furnace->status !== 'active'): ?>
            <button type="button" class="btn btn-outline-success btn-sm" onclick="updateFurnaceStatus('active')">
                <i class="fas fa-play me-1"></i>
                Aktif Yap
            </button>
        <?php else: ?>
            <button type="button" class="btn btn-outline-warning btn-sm" onclick="updateFurnaceStatus('idle')">
                <i class="fas fa-pause me-1"></i>
                Bekletmeye Al
            </button>
        <?php endif; ?>
        <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-ellipsis-v me-1"></i>
                Daha Fazla
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="<?php echo e(route('castings.create', ['furnace_id' => $furnace->id])); ?>">
                    <i class="fas fa-plus me-2"></i>Yeni Döküm Başlat
                </a></li>
                <li><a class="dropdown-item" href="<?php echo e(route('samples.create', ['furnace_id' => $furnace->id])); ?>">
                    <i class="fas fa-vial me-2"></i>Yeni Prova Ekle
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#" onclick="updateFurnaceStatus('maintenance')">
                    <i class="fas fa-tools me-2"></i>Bakıma Al
                </a></li>
                <li><a class="dropdown-item" href="#" onclick="updateFurnaceStatus('inactive')">
                    <i class="fas fa-stop me-2"></i>Kapat
                </a></li>
            </ul>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<!-- Ocak Durum Kartı -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-fire me-2"></i>
                    <?php echo e($furnace->name); ?> - Anlık Durum
                </h5>
                <span class="badge 
                    <?php if($furnace->status === 'active'): ?> bg-success
                    <?php elseif($furnace->status === 'idle'): ?> bg-warning
                    <?php elseif($furnace->status === 'maintenance'): ?> bg-info
                    <?php else: ?> bg-secondary
                    <?php endif; ?> fs-6">
                    <?php if($furnace->status === 'active'): ?> AKTİF
                    <?php elseif($furnace->status === 'idle'): ?> BEKLEMEDE
                    <?php elseif($furnace->status === 'maintenance'): ?> BAKIMDA
                    <?php else: ?> KAPALI
                    <?php endif; ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <div class="p-3">
                            <i class="fas fa-thermometer-half fa-3x text-danger mb-2"></i>
                            <h4 class="text-danger">
                                <?php echo e($furnace->getLastRecordedTemperature() ?? 'N/A'); ?>°C
                            </h4>
                            <small class="text-muted">Son Kaydedilen Sıcaklık</small>
                            <div class="mt-2">
                                <button class="btn btn-outline-danger btn-sm" onclick="showTemperatureModal()">
                                    <i class="fas fa-plus"></i> Sıcaklık Ekle
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 text-center">
                        <div class="p-3">
                            <i class="fas fa-fire fa-3x text-primary mb-2"></i>
                            <h4 class="text-primary">
                                <?php echo e($stats['total_castings']); ?>

                            </h4>
                            <small class="text-muted">Toplam Döküm</small>
                            <div class="mt-2">
                                <span class="badge bg-info">
                                    Sıradaki: <?php echo e($stats['next_casting_number']); ?>. döküm
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 text-center">
                        <div class="p-3">
                            <i class="fas fa-weight-hanging fa-3x text-info mb-2"></i>
                            <h4 class="text-info">
                                <?php echo e($furnace->capacity ?? 'N/A'); ?> ton
                            </h4>
                            <small class="text-muted">Kapasite</small>
                        </div>
                    </div>
                    
                    <div class="col-md-3 text-center">
                        <div class="p-3">
                            <i class="fas fa-fire fa-3x text-warning mb-2"></i>
                            <h4 class="text-warning">
                                <?php echo e($furnace->castings->where('status', 'active')->count()); ?>

                            </h4>
                            <small class="text-muted">Aktif Döküm</small>
                        </div>
                    </div>
                    
                    <div class="col-md-3 text-center">
                        <div class="p-3">
                            <i class="fas fa-vial fa-3x text-success mb-2"></i>
                            <h4 class="text-success">
                                <?php echo e($furnace->castings->flatMap->samples->where('quality_status', 'pending')->count()); ?>

                            </h4>
                            <small class="text-muted">Bekleyen Prova</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bakım Takibi -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card <?php echo e($stats['needs_maintenance'] ? 'border-danger' : 'border-warning'); ?>">
            <div class="card-header <?php echo e($stats['needs_maintenance'] ? 'bg-danger text-white' : 'bg-warning text-dark'); ?>">
                <h6 class="mb-0">
                    <i class="fas fa-tools me-2"></i>
                    Bakım Takibi
                    <?php if($stats['needs_maintenance']): ?>
                        <span class="badge bg-light text-danger ms-2">BAKIM GEREKLİ!</span>
                    <?php endif; ?>
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <div class="p-2">
                            <h4 class="<?php echo e($stats['needs_maintenance'] ? 'text-danger' : 'text-warning'); ?>">
                                <?php echo e($stats['castings_since_maintenance']); ?>

                            </h4>
                            <small class="text-muted">Son Bakımdan Bu Yana Döküm</small>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="p-2">
                            <h4 class="text-info">
                                <?php echo e($stats['max_castings_before_maintenance']); ?>

                            </h4>
                            <small class="text-muted">Maksimum Döküm Sayısı</small>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="p-2">
                            <h4 class="<?php echo e($stats['needs_maintenance'] ? 'text-danger' : 'text-success'); ?>">
                                %<?php echo e(number_format($stats['maintenance_progress'], 1)); ?>

                            </h4>
                            <small class="text-muted">Bakım İlerleme Oranı</small>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="p-2">
                            <?php if($furnace->last_maintenance_date): ?>
                                <h6 class="text-muted">
                                    <?php echo e(\Carbon\Carbon::parse($furnace->last_maintenance_date)->format('d.m.Y')); ?>

                                </h6>
                                <small class="text-muted">Son Bakım Tarihi</small>
                            <?php else: ?>
                                <h6 class="text-muted">-</h6>
                                <small class="text-muted">Son Bakım Tarihi</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Progress Bar -->
                <div class="mt-3">
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar <?php echo e($stats['needs_maintenance'] ? 'bg-danger' : ($stats['maintenance_progress'] > 80 ? 'bg-warning' : 'bg-success')); ?>" 
                             style="width: <?php echo e($stats['maintenance_progress']); ?>%">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <small class="text-muted">0 döküm</small>
                        <small class="text-muted"><?php echo e($stats['max_castings_before_maintenance']); ?> döküm</small>
                    </div>
                </div>
                
                <?php if($stats['needs_maintenance']): ?>
                    <div class="alert alert-danger mt-3 mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Bakım Gerekli!</strong> Bu ocak maksimum döküm sayısına ulaştı. Refraktör kontrolü ve bakım yapılması önerilir.
                    </div>
                <?php elseif($stats['maintenance_progress'] > 80): ?>
                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Dikkat!</strong> Ocak bakım zamanına yaklaşıyor. Bakım planlaması yapılması önerilir.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Detaylı Bilgiler ve İstatistikler -->
<div class="row mb-4">
    <!-- Teknik Bilgiler -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>
                    Teknik Bilgiler
                </h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tbody>
                        <tr>
                            <td><strong>Ocak Seti:</strong></td>
                            <td><?php echo e($furnace->furnaceSet->name); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Kapasite:</strong></td>
                            <td><?php echo e($furnace->capacity ?? 'Belirtilmemiş'); ?> ton</td>
                        </tr>
                        <tr>
                            <td><strong>Max Sıcaklık:</strong></td>
                            <td><?php echo e($furnace->max_temperature ?? 'Belirtilmemiş'); ?>°C</td>
                        </tr>
                        <tr>
                            <td><strong>Yakıt Türü:</strong></td>
                            <td>
                                <?php if($furnace->fuel_type): ?>
                                    <?php switch($furnace->fuel_type):
                                        case ('natural_gas'): ?>
                                            <i class="fas fa-fire text-primary"></i> Doğal Gaz
                                            <?php break; ?>
                                        <?php case ('electricity'): ?>
                                            <i class="fas fa-bolt text-warning"></i> Elektrik
                                            <?php break; ?>
                                        <?php case ('coal'): ?>
                                            <i class="fas fa-mountain text-dark"></i> Kömür
                                            <?php break; ?>
                                        <?php case ('oil'): ?>
                                            <i class="fas fa-oil-can text-info"></i> Mazot
                                            <?php break; ?>
                                        <?php case ('mixed'): ?>
                                            <i class="fas fa-layer-group text-secondary"></i> Karma
                                            <?php break; ?>
                                        <?php default: ?>
                                            <?php echo e(ucfirst($furnace->fuel_type)); ?>

                                    <?php endswitch; ?>
                                <?php else: ?>
                                    Belirtilmemiş
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Kurulum Tarihi:</strong></td>
                            <td><?php echo e($furnace->installation_date ? $furnace->installation_date->format('d.m.Y') : 'Belirtilmemiş'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Son Bakım:</strong></td>
                            <td>
                                <?php echo e($furnace->last_maintenance_date ? $furnace->last_maintenance_date->format('d.m.Y') : 'Belirtilmemiş'); ?>

                                <?php if($furnace->last_maintenance_date): ?>
                                    <br><small class="text-muted"><?php echo e($furnace->last_maintenance_date->diffForHumans()); ?></small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Sonraki Bakım:</strong></td>
                            <td>
                                <?php if($furnace->last_maintenance_date && $furnace->maintenance_interval_days): ?>
                                    <?php
                                        $nextMaintenance = $furnace->last_maintenance_date->addDays($furnace->maintenance_interval_days);
                                        $isOverdue = $nextMaintenance->isPast();
                                    ?>
                                    <span class="<?php echo e($isOverdue ? 'text-danger' : 'text-success'); ?>">
                                        <?php echo e($nextMaintenance->format('d.m.Y')); ?>

                                    </span>
                                    <?php if($isOverdue): ?>
                                        <br><small class="text-danger">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <?php echo e(abs($nextMaintenance->diffInDays(now()))); ?> gün gecikmiş
                                        </small>
                                    <?php else: ?>
                                        <br><small class="text-muted"><?php echo e($nextMaintenance->diffForHumans()); ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    Planlanmamış
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <?php if($furnace->description): ?>
                    <div class="mt-3">
                        <strong>Açıklama:</strong>
                        <p class="mt-2 text-muted"><?php echo e($furnace->description); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Son 30 Gün İstatistikleri -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Son 30 Gün İstatistikleri
                </h6>
            </div>
            <div class="card-body">
                <?php
                    $last30Days = now()->subDays(30);
                    $recentCastings = $furnace->castings->where('casting_date', '>=', $last30Days);
                    $recentSamples = $furnace->castings->flatMap->samples->where('sample_time', '>=', $last30Days);
                ?>
                
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="p-2 bg-light rounded">
                            <h4 class="text-primary mb-1"><?php echo e($recentCastings->count()); ?></h4>
                            <small class="text-muted">Toplam Döküm</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="p-2 bg-light rounded">
                            <h4 class="text-success mb-1"><?php echo e($recentSamples->count()); ?></h4>
                            <small class="text-muted">Toplam Prova</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="p-2 bg-light rounded">
                            <h4 class="text-warning mb-1">
                                <?php echo e($recentCastings->where('status', 'completed')->count()); ?>

                            </h4>
                            <small class="text-muted">Tamamlanan</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="p-2 bg-light rounded">
                            <h4 class="text-info mb-1">
                                <?php echo e($recentSamples->where('quality_status', 'approved')->count()); ?>

                            </h4>
                            <small class="text-muted">Onaylanan</small>
                        </div>
                    </div>
                </div>
                
                <!-- Kalite Oranı -->
                <div class="mt-3">
                    <label class="form-label small">Kalite Onay Oranı</label>
                    <?php
                        $qualityRate = $recentSamples->count() > 0 
                            ? ($recentSamples->where('quality_status', 'approved')->count() / $recentSamples->count()) * 100 
                            : 0;
                    ?>
                    <div class="progress">
                        <div class="progress-bar bg-success" style="width: <?php echo e($qualityRate); ?>%">
                            <?php echo e(number_format($qualityRate, 1)); ?>%
                        </div>
                    </div>
                </div>
                
                <!-- Aktif Çalışma Oranı -->
                <div class="mt-3">
                    <label class="form-label small">Son 30 Gün Aktiflik</label>
                    <?php
                        $activeDays = $recentCastings->groupBy(function($casting) {
                            return $casting->casting_date->format('Y-m-d');
                        })->count();
                        $activityRate = ($activeDays / 30) * 100;
                    ?>
                    <div class="progress">
                        <div class="progress-bar bg-primary" style="width: <?php echo e($activityRate); ?>%">
                            <?php echo e(number_format($activityRate, 1)); ?>%
                        </div>
                    </div>
                    <small class="text-muted"><?php echo e($activeDays); ?> gün aktif</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Son Dökümleri -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-fire me-2"></i>
                    Son Dökümleri
                </h6>
                <a href="<?php echo e(route('castings.index', ['furnace_id' => $furnace->id])); ?>" class="btn btn-sm btn-outline-primary">
                    Tümünü Görüntüle
                </a>
            </div>
            <div class="card-body">
                <?php if($furnace->castings->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Döküm #</th>
                                    <th>Tarih</th>
                                    <th>Vardiya</th>
                                    <th>Durum</th>
                                    <th>Prova Sayısı</th>
                                    <th>Kalite</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $furnace->castings->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $casting): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><strong>#<?php echo e($casting->casting_number); ?></strong></td>
                                        <td><?php echo e($casting->casting_date->format('d.m.Y H:i')); ?></td>
                                        <td><?php echo e($casting->shift ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="badge 
                                                <?php if($casting->status === 'active'): ?> bg-success
                                                <?php elseif($casting->status === 'completed'): ?> bg-primary
                                                <?php elseif($casting->status === 'cancelled'): ?> bg-danger
                                                <?php else: ?> bg-secondary
                                                <?php endif; ?>">
                                                <?php echo e(ucfirst($casting->status)); ?>

                                            </span>
                                        </td>
                                        <td><?php echo e($casting->samples->count()); ?></td>
                                        <td>
                                            <?php
                                                $approvedSamples = $casting->samples->where('quality_status', 'approved')->count();
                                                $totalSamples = $casting->samples->count();
                                            ?>
                                            <?php if($totalSamples > 0): ?>
                                                <span class="badge bg-info">
                                                    <?php echo e($approvedSamples); ?>/<?php echo e($totalSamples); ?>

                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?php echo e(route('castings.show', $casting)); ?>" class="btn btn-outline-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if($casting->status === 'active'): ?>
                                                    <a href="<?php echo e(route('samples.create', ['casting_id' => $casting->id])); ?>" class="btn btn-outline-success btn-sm">
                                                        <i class="fas fa-vial"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-fire fa-3x mb-3"></i>
                        <p>Henüz döküm kaydı yok</p>
                        <a href="<?php echo e(route('castings.create', ['furnace_id' => $furnace->id])); ?>" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            İlk Dökümü Başlat
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Son Provalar -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-vial me-2"></i>
                    Son Provalar
                </h6>
                <a href="<?php echo e(route('samples.index', ['furnace_id' => $furnace->id])); ?>" class="btn btn-sm btn-outline-primary">
                    Tümünü Görüntüle
                </a>
            </div>
            <div class="card-body">
                <?php $recentSamples = $furnace->castings->flatMap->samples->take(10); ?>
                <?php if($recentSamples->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Prova #</th>
                                    <th>Döküm #</th>
                                    <th>Tarih</th>
                                    <th>Analiz Eden</th>
                                    <th>Durum</th>
                                    <th>C%</th>
                                    <th>Mn%</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $recentSamples; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sample): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><strong>#<?php echo e($sample->sample_number); ?></strong></td>
                                        <td>#<?php echo e($sample->casting->casting_number); ?></td>
                                        <td><?php echo e($sample->sample_time->format('d.m H:i')); ?></td>
                                        <td><?php echo e($sample->analyzed_by); ?></td>
                                        <td>
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
                                                <?php elseif($sample->quality_status === 'needs_adjustment'): ?> Düzeltme
                                                <?php else: ?> <?php echo e($sample->quality_status); ?>

                                                <?php endif; ?>
                                            </span>
                                        </td>
                                        <td><?php echo e($sample->carbon_percentage ? number_format($sample->carbon_percentage, 3) : '-'); ?></td>
                                        <td><?php echo e($sample->manganese_percentage ? number_format($sample->manganese_percentage, 3) : '-'); ?></td>
                                        <td>
                                            <a href="<?php echo e(route('samples.show', $sample)); ?>" class="btn btn-outline-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-vial fa-3x mb-3"></i>
                        <p>Henüz prova kaydı yok</p>
                        <?php if($furnace->castings->where('status', 'active')->count() > 0): ?>
                            <a href="<?php echo e(route('samples.create', ['furnace_id' => $furnace->id])); ?>" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                İlk Provayı Ekle
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
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
                    <input type="hidden" id="new_status" name="new_status">
                    
                    <div class="mb-3">
                        <label for="status_notes" class="form-label">Durum Değişikliği Notları</label>
                        <textarea class="form-control" id="status_notes" name="status_notes" rows="3" placeholder="Durum değişikliği hakkında notlarınız..."></textarea>
                    </div>
                    
                    <div class="mb-3" id="temperatureField" style="display: none;">
                        <label for="current_temperature" class="form-label">Mevcut Sıcaklık</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="current_temperature" name="current_temperature" step="0.1" placeholder="1500.0">
                            <span class="input-group-text">°C</span>
                        </div>
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

<!-- Sıcaklık Kayıt Modal'ı -->
<div class="modal fade" id="temperatureModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-thermometer-half text-danger"></i>
                    Sıcaklık Kaydı Ekle
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="temperatureForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sıcaklık (°C) *</label>
                            <input type="number" name="temperature" class="form-control" 
                                   min="0" max="2000" step="1" required
                                   placeholder="1600">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kayıt Tipi *</label>
                            <select name="log_type" class="form-select" required>
                                <option value="working">Çalışma</option>
                                <option value="shutdown">Kapatma</option>
                                <option value="maintenance">Bakım</option>
                                <option value="manual">Manuel Kayıt</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notlar</label>
                        <textarea name="notes" class="form-control" rows="3" 
                                  placeholder="Örnek: 1720 derecede devrildi"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Kaydı Yapan</label>
                        <input type="text" name="recorded_by" class="form-control" 
                               placeholder="Operatör adı">
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Bilgi:</strong> Bu kayıt ocağın sıcaklık geçmişine eklenecek.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-save"></i> Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Ocak durumu güncelle
function updateFurnaceStatus(newStatus) {
    document.getElementById('new_status').value = newStatus;
    
    const statusNames = {
        'active': 'Aktif',
        'idle': 'Beklemede',
        'maintenance': 'Bakımda',
        'inactive': 'Kapalı'
    };
    
    document.querySelector('#statusUpdateModal .modal-title').textContent = 
        `Ocak Durumunu "${statusNames[newStatus]}" Olarak Güncelle`;
    
    // Aktif yapılıyorsa sıcaklık alanını göster
    const tempField = document.getElementById('temperatureField');
    const tempInput = document.getElementById('current_temperature');
    
    if (newStatus === 'active') {
        tempField.style.display = 'block';
        tempInput.required = true;
        tempInput.value = '<?php echo e($furnace->current_temperature ?? 1500); ?>';
    } else {
        tempField.style.display = 'none';
        tempInput.required = false;
        if (newStatus === 'inactive') {
            tempInput.value = '0';
        }
    }
    
    const modal = new bootstrap.Modal(document.getElementById('statusUpdateModal'));
    modal.show();
}

// Durum güncelleme form submit
document.getElementById('statusUpdateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const newStatus = formData.get('new_status');
    const currentTemp = formData.get('current_temperature');
    
    // Aktif yapılırken sıcaklık kontrolü
    if (newStatus === 'active' && (!currentTemp || parseFloat(currentTemp) <= 0)) {
        showToast('Aktif ocaklar için geçerli bir sıcaklık değeri girin', 'warning');
        document.getElementById('current_temperature').focus();
        return;
    }
    
    // Set kuralı kontrolü - Frontend'de ek güvenlik
    if (newStatus === 'active') {
        // Aynı setteki diğer aktif ocakları kontrol et
        const currentFurnaceSet = <?php echo e($furnace->furnace_set_id); ?>;
        const activeFurnacesInSet = <?php echo json_encode($furnace->furnaceSet->furnaces->where('status', 'active')->where('id', '!=', $furnace->id)->pluck('name')); ?>;
        
        if (activeFurnacesInSet.length > 0) {
            showToast(`Aynı sette ${activeFurnacesInSet.join(', ')} zaten aktif. Önce onu kapatın.`, 'error');
            return;
        }
    }
    
    console.log('Sending request to toggle furnace status:', {
        status: newStatus,
        status_notes: formData.get('status_notes'),
        current_temperature: currentTemp
    });
    
    fetch(`<?php echo e(route('api.furnaces.toggle', $furnace)); ?>`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            status: newStatus,
            status_notes: formData.get('status_notes'),
            current_temperature: currentTemp
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Response from server:', data);
        if (data.success) {
            showToast(data.message, 'success');
            
            // Etkilenen ocakları göster
            if (data.affected_furnaces && data.affected_furnaces.length > 0) {
                showToast(`Etkilenen ocaklar: ${data.affected_furnaces.join(', ')}`, 'info');
            }
            
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast(data.message || 'Hata oluştu', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Hata oluştu', 'error');
    });
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('statusUpdateModal'));
    modal.hide();
});

// Otomatik yenileme (2 dakikada bir)
setInterval(() => {
    if (!document.hidden) {
        location.reload();
    }
}, 120000);

// Klavye kısayolları
document.addEventListener('keydown', function(e) {
    // F5: Yenile
    if (e.key === 'F5') {
        e.preventDefault();
        location.reload();
    }
    
    // Ctrl + E: Düzenle
    if (e.ctrlKey && e.key === 'e') {
        e.preventDefault();
        window.location.href = '<?php echo e(route("furnaces.edit", $furnace)); ?>';
    }
});

// Sıcaklık modal fonksiyonları
function showTemperatureModal() {
    const modal = new bootstrap.Modal(document.getElementById('temperatureModal'));
    modal.show();
}

// Sıcaklık form submit
document.getElementById('temperatureForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    
    // Loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Kaydediliyor...';
    
    fetch('<?php echo e(route("furnaces.add-temperature-log", $furnace)); ?>', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Modal'ı kapat
            bootstrap.Modal.getInstance(document.getElementById('temperatureModal')).hide();
            
            // Success toast
            showToast(data.message, 'success');
            
            // Sayfayı yenile
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast(data.message || 'Sıcaklık kaydı eklenirken hata oluştu', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Sıcaklık kaydı eklenirken hata oluştu', 'error');
    })
    .finally(() => {
        // Reset button
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Kaydet';
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\cansan\kk-cansan\resources\views/furnaces/show.blade.php ENDPATH**/ ?>