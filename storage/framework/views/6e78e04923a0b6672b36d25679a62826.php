<!-- Döküm İptal Etme Modal -->
<div class="modal fade" id="cancelCastingModal" tabindex="-1" aria-labelledby="cancelCastingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="cancelCastingModalLabel">
                    <i class="fas fa-times-circle"></i> Dökümü İptal Et
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cancelCastingForm" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Dikkat!</strong> 
                        Bu işlem döküm durumunu "İptal Edildi" olarak değiştirecektir. Bu işlem geri alınamaz!
                    </div>
                    
                    <div class="mb-3">
                        <label for="cancellation_reason" class="form-label">
                            <i class="fas fa-exclamation-circle text-danger"></i> İptal Nedeni *
                        </label>
                        <textarea name="cancellation_reason" 
                                  id="cancellation_reason" 
                                  class="form-control" 
                                  rows="4" 
                                  placeholder="Döküm neden iptal ediliyor? Detaylı açıklama yazınız..."
                                  required></textarea>
                        <small class="form-text text-muted">İptal nedeni zorunludur ve raporlarda görünecektir</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-clock text-info"></i> İptal Zamanı
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
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-user text-primary"></i> İptal Eden
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text" class="form-control" value="Sistem Kullanıcısı" readonly>
                                </div>
                                <small class="form-text text-muted">İptal işlemini yapan kullanıcı</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- İptal Nedeni Örnekleri -->
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-lightbulb text-warning"></i> Yaygın İptal Nedenleri
                        </label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="list-group list-group-flush">
                                    <button type="button" class="list-group-item list-group-item-action cancel-reason-btn" 
                                            data-reason="Ocak arızası nedeniyle döküm durduruludu">
                                        🔧 Ocak Arızası
                                    </button>
                                    <button type="button" class="list-group-item list-group-item-action cancel-reason-btn" 
                                            data-reason="Hammadde kalitesi uygun olmadığı için döküm iptal edildi">
                                        📦 Hammadde Sorunu
                                    </button>
                                    <button type="button" class="list-group-item list-group-item-action cancel-reason-btn" 
                                            data-reason="Güvenlik sorunu nedeniyle döküm acil olarak durduruldu">
                                        ⚠️ Güvenlik Sorunu
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="list-group list-group-flush">
                                    <button type="button" class="list-group-item list-group-item-action cancel-reason-btn" 
                                            data-reason="Sıcaklık kontrolü sağlanamadığı için döküm iptal edildi">
                                        🌡️ Sıcaklık Sorunu
                                    </button>
                                    <button type="button" class="list-group-item list-group-item-action cancel-reason-btn" 
                                            data-reason="Operatör değişimi nedeniyle döküm yeniden başlatılacak">
                                        👤 Operatör Değişimi
                                    </button>
                                    <button type="button" class="list-group-item list-group-item-action cancel-reason-btn" 
                                            data-reason="Planlı bakım nedeniyle döküm ertelendi">
                                        🔧 Planlı Bakım
                                    </button>
                                </div>
                            </div>
                        </div>
                        <small class="form-text text-muted">Yukarıdaki seçeneklerden birini tıklayarak hızlıca neden girebilirsiniz</small>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i>
                        <strong>Bilgi:</strong> İptal edilen dökümlerin provalarına hâlâ erişebilir ve raporlarda görüntüleyebilirsiniz.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-arrow-left"></i> Vazgeç
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Dökümü İptal Et
                    </button>
                </div>
            </form>
        </div>
    </div>
</div><?php /**PATH C:\xampp\htdocs\cansan\kk-cansan\resources\views/castings/partials/cancel-modal.blade.php ENDPATH**/ ?>