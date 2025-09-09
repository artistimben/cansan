<!-- Döküm Tamamlama Modal -->
<div class="modal fade" id="completeCastingModal" tabindex="-1" aria-labelledby="completeCastingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="completeCastingModalLabel">
                    <i class="fas fa-check-circle"></i> Dökümü Tamamla
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="completeCastingForm" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="alert alert-success">
                        <i class="fas fa-info-circle"></i>
                        <strong>Döküm tamamlanıyor!</strong> 
                        Bu işlem döküm durumunu "Tamamlandı" olarak değiştirecek ve bitiş zamanını kaydedecektir.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="final_temperature" class="form-label">
                                    <i class="fas fa-thermometer-half text-danger"></i> Son Sıcaklık (°C)
                                </label>
                                <div class="input-group">
                                    <input type="number" 
                                           name="final_temperature" 
                                           id="final_temperature" 
                                           class="form-control" 
                                           min="0" 
                                           max="3000" 
                                           placeholder="1650">
                                    <span class="input-group-text">°C</span>
                                </div>
                                <small class="form-text text-muted">Döküm bitişindeki sıcaklık değeri</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-clock text-info"></i> Bitiş Zamanı
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-calendar"></i>
                                    </span>
                                    <input type="text" class="form-control" value="<?php echo e(now()->format('d.m.Y H:i')); ?>" readonly>
                                </div>
                                <small class="form-text text-muted">Otomatik olarak şu anki zaman kaydedilir</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="completion_notes" class="form-label">
                            <i class="fas fa-sticky-note text-warning"></i> Tamamlama Notları
                        </label>
                        <textarea name="completion_notes" 
                                  id="completion_notes" 
                                  class="form-control" 
                                  rows="3" 
                                  placeholder="Döküm tamamlanması ile ilgili notlar, gözlemler, özel durumlar..."></textarea>
                        <small class="form-text text-muted">İsteğe bağlı - döküm tamamlanması ile ilgili özel notlar</small>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Uyarı:</strong> Döküm tamamlandıktan sonra yeni prova eklenemez. Emin misiniz?
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> İptal
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Dökümü Tamamla
                    </button>
                </div>
            </form>
        </div>
    </div>
</div><?php /**PATH C:\xampp\htdocs\cansan\kk-cansan\resources\views/castings/partials/complete-modal.blade.php ENDPATH**/ ?>