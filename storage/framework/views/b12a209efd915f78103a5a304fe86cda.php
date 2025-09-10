

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Başlık -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-plus text-primary"></i>
                        Yeni Döküm Başlat
                    </h1>
                    <p class="text-muted mb-0">Ocaktan yeni döküm başlatın ve takip edin</p>
                </div>
                <div>
                    <a href="<?php echo e(route('castings.index')); ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Geri Dön
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Aktif Döküm Uyarısı -->
    <?php if(!empty($furnacesWithActiveCastings)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-warning d-flex align-items-center" role="alert">
                    <i class="fas fa-exclamation-triangle me-3"></i>
                    <div>
                        <strong>Dikkat!</strong> Aşağıdaki ocaklarda zaten aktif döküm bulunuyor:
                        <ul class="mb-0 mt-2">
                            <?php $__currentLoopData = $furnacesWithActiveCastings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $furnaceName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><strong><?php echo e($furnaceName); ?></strong> - Önce mevcut dökümü tamamlayın</li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Sol Kolon - Form -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-fire"></i> Döküm Bilgileri
                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('castings.store')); ?>" method="POST" id="castingForm">
                        <?php echo csrf_field(); ?>
                        
                        <!-- Ocak Seçimi -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="furnace_id" class="form-label">
                                    <i class="fas fa-fire text-primary"></i> Ocak Seçimi *
                                </label>
                                <select name="furnace_id" id="furnace_id" class="form-select <?php $__errorArgs = ['furnace_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                    <option value="">Ocak seçiniz...</option>
                                    <?php $__currentLoopData = $activeFurnaces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $furnace): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($furnace->id); ?>" 
                                            <?php echo e((old('furnace_id', $selectedFurnace?->id) == $furnace->id) ? 'selected' : ''); ?>

                                            data-set-name="<?php echo e($furnace->furnaceSet->name); ?>"
                                            data-furnace-name="<?php echo e($furnace->name); ?>"
                                            data-current-temp="<?php echo e($furnace->current_temperature); ?>"
                                            data-max-temp="<?php echo e($furnace->max_temperature); ?>">
                                            <?php echo e($furnace->furnaceSet->name); ?> - <?php echo e($furnace->name); ?>

                                            <?php if($furnace->current_temperature): ?>
                                                (<?php echo e($furnace->current_temperature); ?>°C)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['furnace_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-6">
                                <label for="casting_number" class="form-label">
                                    <i class="fas fa-hashtag text-info"></i> Döküm Numarası *
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text" id="furnace-info">
                                        <i class="fas fa-fire"></i>
                                    </span>
                                    <input type="text" 
                                           name="casting_number" 
                                           id="casting_number" 
                                           class="form-control <?php $__errorArgs = ['casting_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           value="<?php echo e(old('casting_number')); ?>" 
                                           readonly
                                           placeholder="Ocak seçildikten sonra otomatik oluşturulur">
                                </div>
                                <?php $__errorArgs = ['casting_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> 
                                    Döküm numarası otomatik olarak oluşturulur (Örn: 3.OCAK-27.DÖKÜM)
                                </small>
                            </div>
                        </div>

                        <!-- Tarih ve Vardiya -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="casting_date" class="form-label">
                                    <i class="fas fa-calendar text-success"></i> Döküm Tarihi *
                                </label>
                                <input type="date" 
                                       name="casting_date" 
                                       id="casting_date" 
                                       class="form-control <?php $__errorArgs = ['casting_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       value="<?php echo e(old('casting_date', date('Y-m-d'))); ?>" 
                                       required>
                                <?php $__errorArgs = ['casting_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-4">
                                <label for="casting_time" class="form-label">
                                    <i class="fas fa-clock text-warning"></i> Döküm Saati *
                                </label>
                                <input type="time" 
                                       name="casting_time" 
                                       id="casting_time" 
                                       class="form-control <?php $__errorArgs = ['casting_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       value="<?php echo e(old('casting_time', date('H:i'))); ?>" 
                                       required>
                                <?php $__errorArgs = ['casting_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-4">
                                <label for="shift" class="form-label">
                                    <i class="fas fa-user-clock text-info"></i> Vardiya *
                                </label>
                                <select name="shift" id="shift" class="form-select <?php $__errorArgs = ['shift'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                    <option value="">Vardiya seçiniz...</option>
                                    <option value="Gündüz" <?php echo e(old('shift') === 'Gündüz' ? 'selected' : ''); ?>>
                                        🌞 Gündüz (06:00-18:00)
                                    </option>
                                    <option value="Gece" <?php echo e(old('shift') === 'Gece' ? 'selected' : ''); ?>>
                                        🌙 Gece (18:00-06:00)
                                    </option>
                                </select>
                                <?php $__errorArgs = ['shift'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <!-- Operatör ve Sıcaklık -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="operator_name" class="form-label">
                                    <i class="fas fa-user text-primary"></i> Operatör Adı *
                                </label>
                                <input type="text" 
                                       name="operator_name" 
                                       id="operator_name" 
                                       class="form-control <?php $__errorArgs = ['operator_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       value="<?php echo e(old('operator_name')); ?>" 
                                       placeholder="Operatör adı ve soyadı"
                                       required>
                                <?php $__errorArgs = ['operator_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-6">
                                <label for="target_temperature" class="form-label">
                                    <i class="fas fa-thermometer-half text-danger"></i> Hedef Sıcaklık (°C)
                                </label>
                                <div class="input-group">
                                    <input type="number" 
                                           name="target_temperature" 
                                           id="target_temperature" 
                                           class="form-control <?php $__errorArgs = ['target_temperature'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           value="<?php echo e(old('target_temperature')); ?>" 
                                           min="0" 
                                           max="3000" 
                                           placeholder="1650">
                                    <span class="input-group-text">°C</span>
                                </div>
                                <?php $__errorArgs = ['target_temperature'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <small class="form-text text-muted">
                                    <span id="temp-range-info">Maksimum sıcaklık bilgisi için ocak seçiniz</span>
                                </small>
                            </div>
                        </div>

                        <!-- Notlar -->
                        <div class="mb-4">
                            <label for="notes" class="form-label">
                                <i class="fas fa-sticky-note text-warning"></i> Notlar ve Açıklamalar
                            </label>
                            <textarea name="notes" 
                                      id="notes" 
                                      class="form-control <?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                      rows="3" 
                                      placeholder="Döküm ile ilgili özel notlar, dikkat edilecek hususlar..."><?php echo e(old('notes')); ?></textarea>
                            <?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="<?php echo e(route('castings.index')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> İptal
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-play"></i> Dökümü Başlat
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sağ Kolon - Bilgi Paneli -->
        <div class="col-md-4">
            <!-- Seçilen Ocak Bilgisi -->
            <div class="card mb-3" id="furnace-info-card" style="display: none;">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-fire"></i> Seçilen Ocak
                    </h6>
                </div>
                <div class="card-body">
                    <div id="selected-furnace-info">
                        <!-- JavaScript ile doldurulacak -->
                    </div>
                </div>
            </div>

            <!-- Döküm İstatistikleri -->
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar"></i> Bugünkü İstatistikler
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary mb-1"><?php echo e($todayStats['total_castings'] ?? 0); ?></h4>
                                <small class="text-muted">Toplam Döküm</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success mb-1"><?php echo e($todayStats['active_castings'] ?? 0); ?></h4>
                            <small class="text-muted">Aktif Döküm</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-warning mb-1"><?php echo e($todayStats['total_samples'] ?? 0); ?></h4>
                                <small class="text-muted">Toplam Prova</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-info mb-1"><?php echo e($todayStats['active_furnaces'] ?? 0); ?></h4>
                            <small class="text-muted">Aktif Ocak</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Yardım Paneli -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-question-circle text-info"></i> Yardım
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-lightbulb"></i> Döküm Numaralandırma</h6>
                        <p class="mb-2">Sistem otomatik olarak döküm numarası oluşturur:</p>
                        <ul class="mb-0 small">
                            <li><strong>3.OCAK-27.DÖKÜM</strong> = 3. ocaktan 27. döküm</li>
                            <li><strong>1.OCAK-15.DÖKÜM</strong> = 1. ocaktan 15. döküm</li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Önemli</h6>
                        <ul class="mb-0 small">
                            <li>Sadece aktif ocaklardan döküm başlatabilirsiniz</li>
                            <li>Her ocak için döküm sayısı otomatik artar</li>
                            <li>Döküm başladıktan sonra prova ekleyebilirsiniz</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
$(document).ready(function() {
    // Ocak seçimi değiştiğinde
    $('#furnace_id').change(function() {
        const selectedOption = $(this).find(':selected');
        const furnaceId = $(this).val();
        
        if (furnaceId) {
            // Ocak bilgilerini al
            const setName = selectedOption.data('set-name');
            const furnaceName = selectedOption.data('furnace-name');
            const currentTemp = selectedOption.data('current-temp');
            const maxTemp = selectedOption.data('max-temp');
            
            // Döküm numarası oluştur (AJAX ile)
            generateCastingNumber(furnaceId);
            
            // Ocak bilgi kartını göster
            showFurnaceInfo(setName, furnaceName, currentTemp, maxTemp);
            
            // Sıcaklık aralığı bilgisi
            if (maxTemp) {
                $('#temp-range-info').html(`<i class="fas fa-info-circle"></i> Maksimum: ${maxTemp}°C`);
                $('#target_temperature').attr('max', maxTemp);
            }
        } else {
            // Bilgileri temizle
            $('#furnace-info-card').hide();
            $('#casting_number').val('');
            $('#temp-range-info').text('Maksimum sıcaklık bilgisi için ocak seçiniz');
        }
    });
    
    // Sayfa yüklendiğinde seçili ocak varsa bilgileri göster
    if ($('#furnace_id').val()) {
        $('#furnace_id').trigger('change');
    }
    
    // Vardiya otomatik seçimi (saate göre)
    if (!$('#shift').val()) {
        const currentHour = new Date().getHours();
        if (currentHour >= 6 && currentHour < 18) {
            $('#shift').val('Gündüz');
        } else {
            $('#shift').val('Gece');
        }
    }
});

function generateCastingNumber(furnaceId) {
    $.ajax({
        url: `/api/furnaces/${furnaceId}/next-casting-number`,
        method: 'GET',
        success: function(response) {
            $('#casting_number').val(response.casting_number);
            $('#furnace-info').html(`<i class="fas fa-hashtag"></i> ${response.casting_count + 1}. Döküm`);
        },
        error: function() {
            $('#casting_number').val('HATA - Manuel giriniz');
        }
    });
}

function showFurnaceInfo(setName, furnaceName, currentTemp, maxTemp) {
    const tempColor = currentTemp > 1500 ? 'text-danger' : currentTemp > 1000 ? 'text-warning' : 'text-info';
    
    const infoHtml = `
        <div class="row mb-2">
            <div class="col-4"><strong>Set:</strong></div>
            <div class="col-8">${setName}</div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><strong>Ocak:</strong></div>
            <div class="col-8">${furnaceName}</div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><strong>Mevcut:</strong></div>
            <div class="col-8">
                <span class="${tempColor}">
                    <i class="fas fa-thermometer-half"></i> ${currentTemp || 0}°C
                </span>
            </div>
        </div>
        <div class="row">
            <div class="col-4"><strong>Maksimum:</strong></div>
            <div class="col-8">
                <span class="text-danger">
                    <i class="fas fa-thermometer-full"></i> ${maxTemp || 'N/A'}°C
                </span>
            </div>
        </div>
    `;
    
    $('#selected-furnace-info').html(infoHtml);
    $('#furnace-info-card').show();
}

// Form submit kontrolü - Aktif döküm kontrolü
$('#castingForm').on('submit', function(e) {
    const selectedFurnaceId = $('#furnace_id').val();
    
    if (selectedFurnaceId) {
        // Seçilen ocakta aktif döküm var mı kontrol et
        $.ajax({
            url: `/api/furnaces/${selectedFurnaceId}/active-casting-check`,
            method: 'GET',
            success: function(response) {
                if (response.has_active_casting) {
                    e.preventDefault();
                    showToast('Bu ocakta zaten aktif bir döküm bulunuyor. Önce mevcut dökümü tamamlayın.', 'error');
                    return false;
                }
            },
            error: function() {
                // Hata durumunda form'u gönder
                return true;
            }
        });
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\cansan\resources\views/castings/create.blade.php ENDPATH**/ ?>