@extends('layouts.app')

@section('title', 'Ana Dashboard - Çelik Fabrikası')

@section('content')
<div class="row">
    <!-- Sistem Durumu İstatistikleri -->
    <div class="col-12 mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="stat-card primary">
                    <div class="stat-number" id="active-furnaces-count">{{ $activeFurnaces->count() }}</div>
                    <div class="stat-label">Aktif Ocak</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card success">
                    <div class="stat-number" id="active-castings-count">{{ $activeCastings->count() }}</div>
                    <div class="stat-label">Devam Eden Döküm</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card info">
                    <div class="stat-number" id="todays-total-castings">{{ $dailyStats['total_castings'] }}</div>
                    <div class="stat-label">Bugünkü Toplam Döküm</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card warning">
                    <div class="stat-number" id="maintenance-needed-count">{{ $maintenanceNeeded->count() }}</div>
                    <div class="stat-label">Bakım Gerekli</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Aktif Ocaklar -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-fire"></i>
                    Aktif Ocaklar ve Döküm Durumları
                </h5>
                <button class="btn btn-light btn-sm" onclick="refreshFurnaceData()">
                    <i class="bi bi-arrow-clockwise"></i>
                    Yenile
                </button>
            </div>
            <div class="card-body" id="active-furnaces-container">
                @forelse($activeFurnaces as $furnace)
                    <div class="furnace-card card mb-3 furnace-{{ strtolower($furnace->status) }} {{ $furnace->is_charging ? 'charging' : '' }}" 
                         data-furnace-id="{{ $furnace->id }}">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <h4 class="mb-0 text-primary">
                                        <i class="bi bi-gear-wide-connected"></i>
                                        {{ $furnace->furnace_number }}. Ocak
                                    </h4>
                                    <small class="text-muted">Set {{ $furnace->furnace_set }}</small>
                                </div>
                                
                                <div class="col-md-4">
                                    @if($furnace->getCurrentCasting())
                                        @php $casting = $furnace->getCurrentCasting() @endphp
                                        <div class="mb-2">
                                            <strong>{{ $casting->casting_number_per_furnace }}. Döküm</strong>
                                            <span class="badge bg-info ms-2">Genel: #{{ $casting->global_casting_number }}</span>
                                        </div>
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                Başlangıç: {{ $casting->start_time->format('H:i') }} |
                                                Tahmini Bitiş: {{ $casting->estimated_end_time->format('H:i') }}
                                            </small>
                                        </div>
                                        <div class="progress mb-2">
                                            <div class="progress-bar bg-success" style="width: {{ $casting->progress_percentage }}%"></div>
                                        </div>
                                        <small class="text-muted">
                                            Kalan Süre: {{ $casting->remaining_time }} dakika
                                            @if($casting->isDelayed())
                                                <span class="text-danger">⚠️ GECİKME</span>
                                            @endif
                                        </small>
                                    @else
                                        <div class="text-muted">
                                            <i class="bi bi-pause-circle"></i>
                                            Döküm bekleniyor
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="mb-2">
                                        <small class="text-muted">Bugünkü Döküm:</small>
                                        <strong class="d-block">{{ $furnace->getTodaysCastingCount() }}</strong>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted">Toplam Döküm:</small>
                                        <strong class="d-block">{{ $furnace->total_castings }}/{{ $furnace->max_castings_before_maintenance }}</strong>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-warning" 
                                             style="width: {{ ($furnace->total_castings / $furnace->max_castings_before_maintenance) * 100 }}%"></div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3 text-end">
                                    <div class="btn-group-vertical" role="group">
                                        @if($furnace->getCurrentCasting())
                                            <button class="btn btn-success btn-sm mb-1" 
                                                    onclick="completeCasting({{ $furnace->getCurrentCasting()->id }})">
                                                <i class="bi bi-check-circle"></i>
                                                Dökümü Tamamla
                                            </button>
                                        @else
                                            <button class="btn btn-primary btn-sm mb-1" 
                                                    onclick="startCasting({{ $furnace->id }})">
                                                <i class="bi bi-play-circle"></i>
                                                Döküm Başlat
                                            </button>
                                        @endif
                                        
                                        <button class="btn btn-outline-warning btn-sm mb-1" 
                                                onclick="viewFurnaceHistory({{ $furnace->id }})">
                                            <i class="bi bi-clock-history"></i>
                                            Geçmiş
                                        </button>
                                        
                                        @if($furnace->total_castings >= $furnace->max_castings_before_maintenance)
                                            <button class="btn btn-danger btn-sm" 
                                                    onclick="sendToMaintenance({{ $furnace->id }})">
                                                <i class="bi bi-tools"></i>
                                                Bakıma Gönder
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4">
                        <i class="bi bi-exclamation-triangle text-warning" style="font-size: 2rem;"></i>
                        <p class="mt-2 text-muted">Aktif ocak bulunamadı.</p>
                        <button class="btn btn-primary" onclick="window.location.href='/test/setup'">
                            Sistem Kurulumu Yap
                        </button>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Günlük İstatistikler ve Hızlı İşlemler -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-graph-up"></i>
                    Günlük İstatistikler
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <h3 class="text-success">{{ $dailyStats['completed_castings'] }}</h3>
                        <small class="text-muted">Tamamlanan</small>
                    </div>
                    <div class="col-6 mb-3">
                        <h3 class="text-info">{{ $dailyStats['in_progress_castings'] }}</h3>
                        <small class="text-muted">Devam Eden</small>
                    </div>
                </div>
                
                @if(count($dailyStats['furnace_breakdown']) > 0)
                    <h6 class="mt-3 mb-2">Ocak Bazında Döküm:</h6>
                    @foreach($dailyStats['furnace_breakdown'] as $furnaceData)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>{{ $furnaceData['furnace_number'] }}. Ocak:</span>
                            <span class="badge bg-primary">{{ $furnaceData['casting_count'] }}</span>
                        </div>
                    @endforeach
                @endif

                <hr>
                
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary" onclick="generateDailyReport()">
                        <i class="bi bi-file-earmark-text"></i>
                        Günlük Rapor Oluştur
                    </button>
                    <button class="btn btn-outline-success" onclick="openQualityControlModal()">
                        <i class="bi bi-clipboard-check"></i>
                        Kalite Kontrol
                    </button>
                </div>
            </div>
        </div>

        <!-- Bakım Gerekli Ocaklar -->
        @if($maintenanceNeeded->count() > 0)
            <div class="card mt-3">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="bi bi-exclamation-triangle"></i>
                        Bakım Gerekli Ocaklar
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($maintenanceNeeded as $furnace)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>{{ $furnace->furnace_number }}. Ocak</span>
                            <span class="badge bg-warning text-dark">
                                {{ $furnace->total_castings }}/{{ $furnace->max_castings_before_maintenance }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Döküm Listesi (Genel Sıralama) -->
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-list-ol"></i>
                    Bugünkü Döküm Listesi (Genel Sıralama)
                </h5>
                <div>
                    <button class="btn btn-outline-primary btn-sm me-2" onclick="refreshCastingList()">
                        <i class="bi bi-arrow-clockwise"></i>
                        Yenile
                    </button>
                    <button class="btn btn-success btn-sm" onclick="exportTodaysCastings()">
                        <i class="bi bi-download"></i>
                        Excel İndir
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover casting-table" id="castings-table">
                        <thead>
                            <tr>
                                <th>Genel Sıra</th>
                                <th>Ocak</th>
                                <th>Ocak Dökümü</th>
                                <th>Başlangıç</th>
                                <th>Tahmini Bitiş</th>
                                <th>Durum</th>
                                <th>İlerleme</th>
                                <th>Kalite</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="castings-tbody">
                            @forelse($todaysCastings as $casting)
                                <tr class="{{ $casting->isDelayed() ? 'delayed' : '' }}" data-casting-id="{{ $casting->id }}">
                                    <td>
                                        <strong class="text-primary">#{{ $casting->global_casting_number }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $casting->furnace->furnace_number }}. Ocak</span>
                                        <small class="d-block text-muted">Set {{ $casting->furnace->furnace_set }}</small>
                                    </td>
                                    <td>{{ $casting->casting_number_per_furnace }}. Döküm</td>
                                    <td>{{ $casting->start_time->format('H:i') }}</td>
                                    <td>
                                        {{ $casting->estimated_end_time->format('H:i') }}
                                        @if($casting->isDelayed())
                                            <i class="bi bi-exclamation-triangle text-danger ms-1" title="Gecikmede"></i>
                                        @endif
                                    </td>
                                    <td>
                                        @if($casting->status === 'in_progress')
                                            <span class="status-badge bg-info text-white">Devam Ediyor</span>
                                        @elseif($casting->status === 'completed')
                                            <span class="status-badge bg-success text-white">Tamamlandı</span>
                                        @else
                                            <span class="status-badge bg-secondary text-white">{{ ucfirst($casting->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($casting->status === 'in_progress')
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-info" 
                                                     style="width: {{ $casting->progress_percentage }}%"
                                                     title="{{ $casting->progress_percentage }}%">
                                                    {{ round($casting->progress_percentage) }}%
                                                </div>
                                            </div>
                                            <small class="text-muted">{{ $casting->remaining_time }} dk kaldı</small>
                                        @else
                                            <span class="text-success">✓ Tamamlandı</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($casting->qualityControl)
                                            <span class="status-badge bg-{{ $casting->qualityControl->getStatusColor() }} text-white">
                                                {{ $casting->qualityControl->getStatusText() }}
                                            </span>
                                        @else
                                            <button class="btn btn-outline-warning btn-sm" 
                                                    onclick="openQualityControlModal({{ $casting->id }})">
                                                <i class="bi bi-clipboard-plus"></i>
                                                Ekle
                                            </button>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @if($casting->status === 'in_progress')
                                                <button class="btn btn-success btn-sm" 
                                                        onclick="completeCasting({{ $casting->id }})"
                                                        title="Dökümü Tamamla">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                            @endif
                                            
                                            <button class="btn btn-info btn-sm" 
                                                    onclick="viewCastingDetails({{ $casting->id }})"
                                                    title="Detayları Görüntüle">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            
                                            @if($casting->qualityControl)
                                                <button class="btn btn-warning btn-sm" 
                                                        onclick="editQualityControl({{ $casting->id }})"
                                                        title="Kalite Kontrolü Düzenle">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                        <p class="mt-2 text-muted">Bugün henüz döküm yapılmamış.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Kalite Kontrol Modal -->
<div class="modal fade" id="qualityControlModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-clipboard-check"></i>
                    Kalite Kontrol Değerleri
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="qualityControlForm" class="quality-control-form">
                    <input type="hidden" id="qc-casting-id" name="casting_id">
                    
                    <!-- Döküm Bilgileri -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Döküm Bilgileri</h6>
                        </div>
                        <div class="card-body" id="casting-info">
                            <!-- JavaScript ile doldurulacak -->
                        </div>
                    </div>

                    <!-- Kimyasal Kompozisyon -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Kimyasal Kompozisyon (%)</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Karbon (C)</label>
                                    <input type="number" class="form-control" name="carbon_percentage" 
                                           step="0.001" min="0" max="5" placeholder="0.000">
                                    <small class="form-text text-muted">Standart: 0.15% - 0.35%</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Silisyum (Si)</label>
                                    <input type="number" class="form-control" name="silicon_percentage" 
                                           step="0.001" min="0" max="5" placeholder="0.000">
                                    <small class="form-text text-muted">Standart: 0.10% - 0.30%</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mangan (Mn)</label>
                                    <input type="number" class="form-control" name="manganese_percentage" 
                                           step="0.001" min="0" max="5" placeholder="0.000">
                                    <small class="form-text text-muted">Standart: 0.60% - 1.00%</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fosfor (P)</label>
                                    <input type="number" class="form-control" name="phosphorus_percentage" 
                                           step="0.001" min="0" max="1" placeholder="0.000">
                                    <small class="form-text text-muted">Maksimum: 0.04%</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Kükürt (S)</label>
                                    <input type="number" class="form-control" name="sulfur_percentage" 
                                           step="0.001" min="0" max="1" placeholder="0.000">
                                    <small class="form-text text-muted">Maksimum: 0.05%</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Krom (Cr)</label>
                                    <input type="number" class="form-control" name="chromium_percentage" 
                                           step="0.001" min="0" max="10" placeholder="0.000">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nikel (Ni)</label>
                                    <input type="number" class="form-control" name="nickel_percentage" 
                                           step="0.001" min="0" max="10" placeholder="0.000">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Bakır (Cu)</label>
                                    <input type="number" class="form-control" name="copper_percentage" 
                                           step="0.001" min="0" max="10" placeholder="0.000">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Sıcaklık (°C)</label>
                                    <input type="number" class="form-control" name="temperature" 
                                           step="1" min="1000" max="2000" placeholder="1550">
                                    <small class="form-text text-muted">Standart: 1500°C - 1650°C</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Prova Kayıtları -->
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Prova Kayıtları</h6>
                            <button type="button" class="btn btn-sm btn-success" onclick="addProvaRecord()">
                                <i class="bi bi-plus-circle"></i> Prova Ekle
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="prova-list" class="mb-3">
                                <!-- Provalar buraya dinamik eklenecek -->
                                <div class="text-muted text-center py-3">
                                    <i class="bi bi-clipboard-data"></i>
                                    <p class="mb-0">Henüz prova kaydı eklenmedi. "Prova Ekle" butonuna tıklayarak yeni prova ekleyebilirsiniz.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Test Bilgileri -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Test Bilgileri</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Testi Yapan</label>
                                    <input type="text" class="form-control" name="tested_by" 
                                           placeholder="Kalite kontrol uzmanı adı">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Açıklamalar</label>
                                    <textarea class="form-control" name="remarks" rows="3" 
                                              placeholder="Ek açıklamalar ve notlar..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-primary" onclick="saveQualityControl()">
                    <i class="bi bi-save"></i>
                    Kaydet ve Değerlendir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Ocak Geçmişi Modal -->
<div class="modal fade" id="furnaceHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-clock-history"></i>
                    Ocak Döküm Geçmişi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="furnace-history-content">
                <!-- JavaScript ile doldurulacak -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Real-time veri güncelleme
    let refreshInterval;
    
    function startRealTimeUpdates() {
        refreshInterval = setInterval(function() {
            refreshRealTimeData();
        }, 30000); // 30 saniyede bir güncelle
    }
    
    function stopRealTimeUpdates() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
    }
    
    function refreshRealTimeData() {
        $.get('{{ route("api.realtime") }}')
            .done(function(response) {
                if (response.success) {
                    updateDashboardData(response.data);
                }
            })
            .fail(function() {
                console.log('Real-time veri güncellemesi başarısız');
            });
    }
    
    function updateDashboardData(data) {
        // İstatistikleri güncelle
        $('#active-furnaces-count').text(data.active_furnaces.length);
        $('#active-castings-count').text(data.active_castings.length);
        $('#todays-total-castings').text(data.daily_stats.total_castings);
        
        // Aktif ocakları güncelle
        updateActiveFurnaces(data.active_furnaces);
        
        // Döküm listesini güncelle
        updateCastingsList(data.active_castings);
    }
    
    function updateActiveFurnaces(furnaces) {
        // Furnace kartlarını güncelle (detaylı güncelleme için)
        furnaces.forEach(function(furnace) {
            const furnaceCard = $(`[data-furnace-id="${furnace.id}"]`);
            if (furnaceCard.length) {
                // Progress bar güncelle
                if (furnace.current_casting) {
                    furnaceCard.find('.progress-bar').css('width', furnace.current_casting.progress + '%');
                    furnaceCard.find('.progress-bar').parent().next().find('small').text(
                        `Kalan Süre: ${furnace.current_casting.remaining_minutes} dakika`
                    );
                }
            }
        });
    }
    
    // Döküm tamamlama
    function completeCasting(castingId) {
        if (confirm('Bu dökümü tamamlamak istediğinizden emin misiniz?')) {
            $.post(`/casting/${castingId}/complete`)
                .done(function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        showToast(response.message, 'error');
                    }
                });
        }
    }
    
    // Yeni döküm başlatma
    function startCasting(furnaceId) {
        $.post(`/casting/furnace/${furnaceId}/start`)
            .done(function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showToast(response.message, 'error');
                }
            });
    }
    
    // Ocak bakıma gönderme
    function sendToMaintenance(furnaceId) {
        if (confirm('Bu ocağı bakıma göndermek istediğinizden emin misiniz? Aynı setteki yedek ocak otomatik olarak aktif hale gelecektir.')) {
            $.post(`/furnace/${furnaceId}/maintenance`)
                .done(function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        showToast(response.message, 'error');
                    }
                });
        }
    }
    
    // Ocak geçmişi görüntüleme
    function viewFurnaceHistory(furnaceId) {
        $.get(`/casting/furnace/${furnaceId}/history`)
            .done(function(response) {
                if (response.success) {
                    displayFurnaceHistory(response.data);
                    $('#furnaceHistoryModal').modal('show');
                } else {
                    showToast(response.message, 'error');
                }
            });
    }
    
    function displayFurnaceHistory(data) {
        let html = `
            <div class="row mb-3">
                <div class="col-md-4">
                    <h6>Ocak Bilgileri</h6>
                    <p><strong>${data.furnace.number}. Ocak</strong> (Set ${data.furnace.set})</p>
                    <p>Toplam Döküm: <strong>${data.furnace.total_castings}</strong></p>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Döküm No</th>
                            <th>Genel Sıra</th>
                            <th>Başlangıç</th>
                            <th>Bitiş</th>
                            <th>Süre (dk)</th>
                            <th>Durum</th>
                            <th>Kalite</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        data.castings.forEach(function(casting) {
            html += `
                <tr>
                    <td>${casting.casting_number}</td>
                    <td>#${casting.global_number}</td>
                    <td>${casting.start_time}</td>
                    <td>${casting.end_time}</td>
                    <td>${casting.duration}</td>
                    <td>
                        <span class="badge bg-${casting.status === 'completed' ? 'success' : 'info'}">
                            ${casting.status === 'completed' ? 'Tamamlandı' : 'Devam Ediyor'}
                        </span>
                    </td>
                    <td>
                        ${casting.has_quality_control ? 
                            `<span class="badge bg-${casting.quality_result === 'passed' ? 'success' : 'danger'}">
                                ${casting.quality_result === 'passed' ? 'Başarılı' : 'Başarısız'}
                            </span>` : 
                            '<span class="text-muted">-</span>'
                        }
                    </td>
                </tr>
            `;
        });
        
        html += '</tbody></table></div>';
        $('#furnace-history-content').html(html);
    }
    
    // Kalite kontrol modal
    function openQualityControlModal(castingId = null) {
        if (castingId) {
            // Belirli döküm için kalite kontrol
            $.get(`/quality-control/casting/${castingId}`)
                .done(function(response) {
                    if (response.success) {
                        fillQualityControlForm(response.data);
                        $('#qualityControlModal').modal('show');
                    }
                });
        } else {
            // Genel kalite kontrol (döküm seçilecek)
            showToast('Lütfen kalite kontrolü yapılacak dökümü seçin.', 'info');
        }
    }
    
    function fillQualityControlForm(data) {
        $('#qc-casting-id').val(data.casting.id);
        
        // Döküm bilgilerini doldur
        let castingInfo = `
            <div class="row">
                <div class="col-md-3"><strong>Ocak:</strong> ${data.casting.furnace_number}</div>
                <div class="col-md-3"><strong>Döküm No:</strong> ${data.casting.casting_number}</div>
                <div class="col-md-3"><strong>Genel Sıra:</strong> #${data.casting.global_number}</div>
                <div class="col-md-3"><strong>Başlangıç:</strong> ${data.casting.start_time}</div>
            </div>
        `;
        $('#casting-info').html(castingInfo);
        
        // Mevcut kalite kontrol verilerini doldur
        if (data.quality_control) {
            const qc = data.quality_control;
            $('input[name="carbon_percentage"]').val(qc.carbon_percentage);
            $('input[name="silicon_percentage"]').val(qc.silicon_percentage);
            $('input[name="manganese_percentage"]').val(qc.manganese_percentage);
            $('input[name="phosphorus_percentage"]').val(qc.phosphorus_percentage);
            $('input[name="sulfur_percentage"]').val(qc.sulfur_percentage);
            $('input[name="chromium_percentage"]').val(qc.chromium_percentage);
            $('input[name="nickel_percentage"]').val(qc.nickel_percentage);
            $('input[name="copper_percentage"]').val(qc.copper_percentage);
            $('input[name="temperature"]').val(qc.temperature);
            $('input[name="tested_by"]').val(qc.tested_by);
            $('textarea[name="remarks"]').val(qc.remarks);
            
            // Prova verilerini yükle
            if (qc.prova_data && qc.prova_data.length > 0) {
                window.provaRecords = qc.prova_data;
                renderProvaList();
            } else {
                window.provaRecords = [];
            }
        } else {
            // Yeni kayıt için prova listesini temizle
            window.provaRecords = [];
            renderProvaList();
        }
    }
    
    function saveQualityControl() {
        const castingId = $('#qc-casting-id').val();
        
        // Form verilerini topla
        const formData = {
            carbon_percentage: $('input[name="carbon_percentage"]').val(),
            silicon_percentage: $('input[name="silicon_percentage"]').val(),
            manganese_percentage: $('input[name="manganese_percentage"]').val(),
            phosphorus_percentage: $('input[name="phosphorus_percentage"]').val(),
            sulfur_percentage: $('input[name="sulfur_percentage"]').val(),
            chromium_percentage: $('input[name="chromium_percentage"]').val(),
            nickel_percentage: $('input[name="nickel_percentage"]').val(),
            copper_percentage: $('input[name="copper_percentage"]').val(),
            temperature: $('input[name="temperature"]').val(),
            tested_by: $('input[name="tested_by"]').val(),
            remarks: $('textarea[name="remarks"]').val(),
            prova_data: window.provaRecords || []
        };
        
        $.ajax({
            url: `/quality-control/casting/${castingId}`,
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        }).done(function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                $('#qualityControlModal').modal('hide');
                
                // Test sonucunu göster
                if (response.data.out_of_limit_values && Object.keys(response.data.out_of_limit_values).length > 0) {
                    showToast('Bazı değerler standart limitler dışında!', 'warning');
                }
                
                setTimeout(function() {
                    location.reload();
                }, 2000);
            } else {
                showToast(response.message, 'error');
            }
        }).fail(function(xhr) {
            showToast('Kalite kontrol kaydedilemedi: ' + (xhr.responseJSON?.message || 'Bilinmeyen hata'), 'error');
        });
    }
    
    // Prova kayıtları yönetimi
    // Global prova kayıtları dizisi
    window.provaRecords = [];
    
    /**
     * Yeni prova kaydı ekler
     * Kullanım: addProvaRecord() - Boş prova formu gösterir
     */
    function addProvaRecord() {
        const provaIndex = window.provaRecords.length;
        
        // Yeni prova kaydı objesi
        const newProva = {
            c: '',
            si: '',
            mn: '',
            s: '',
            p: '',
            cu: ''
        };
        
        window.provaRecords.push(newProva);
        renderProvaList();
    }
    
    /**
     * Prova kaydını listeden siler
     * Kullanım: removeProvaRecord(index) - Belirtilen indeksteki provayı siler
     */
    function removeProvaRecord(index) {
        window.provaRecords.splice(index, 1);
        renderProvaList();
    }
    
    /**
     * Prova değerlerini günceller
     * Kullanım: updateProvaValue(index, field, value) - Prova alanını günceller
     */
    function updateProvaValue(index, field, value) {
        if (window.provaRecords[index]) {
            window.provaRecords[index][field] = value;
        }
    }
    
    /**
     * Prova listesini HTML olarak render eder
     * Kullanım: renderProvaList() - Mevcut prova kayıtlarını listeler
     */
    function renderProvaList() {
        const provaListDiv = $('#prova-list');
        
        if (window.provaRecords.length === 0) {
            provaListDiv.html(`
                <div class="text-muted text-center py-3">
                    <i class="bi bi-clipboard-data"></i>
                    <p class="mb-0">Henüz prova kaydı eklenmedi. "Prova Ekle" butonuna tıklayarak yeni prova ekleyebilirsiniz.</p>
                </div>
            `);
            return;
        }
        
        let html = '';
        window.provaRecords.forEach((prova, index) => {
            html += `
                <div class="prova-item card mb-2">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>PROVA ${index + 1}:</strong>
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeProvaRecord(${index})">
                                <i class="bi bi-trash"></i> Sil
                            </button>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-2">
                                <label class="form-label form-label-sm">C</label>
                                <input type="number" class="form-control form-control-sm" 
                                       value="${prova.c || ''}" 
                                       onchange="updateProvaValue(${index}, 'c', this.value)"
                                       placeholder="0.00" step="0.01">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label form-label-sm">SI</label>
                                <input type="number" class="form-control form-control-sm" 
                                       value="${prova.si || ''}" 
                                       onchange="updateProvaValue(${index}, 'si', this.value)"
                                       placeholder="0.00" step="0.01">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label form-label-sm">MN</label>
                                <input type="number" class="form-control form-control-sm" 
                                       value="${prova.mn || ''}" 
                                       onchange="updateProvaValue(${index}, 'mn', this.value)"
                                       placeholder="0.00" step="0.01">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label form-label-sm">S</label>
                                <input type="number" class="form-control form-control-sm" 
                                       value="${prova.s || ''}" 
                                       onchange="updateProvaValue(${index}, 's', this.value)"
                                       placeholder="0.00" step="0.01">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label form-label-sm">P</label>
                                <input type="number" class="form-control form-control-sm" 
                                       value="${prova.p || ''}" 
                                       onchange="updateProvaValue(${index}, 'p', this.value)"
                                       placeholder="0.00" step="0.01">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label form-label-sm">CU</label>
                                <input type="number" class="form-control form-control-sm" 
                                       value="${prova.cu || ''}" 
                                       onchange="updateProvaValue(${index}, 'cu', this.value)"
                                       placeholder="0.00" step="0.01">
                            </div>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                PROVA: C:${prova.c || '-'} SI:${prova.si || '-'} MN:${prova.mn || '-'} S:${prova.s || '-'} P:${prova.p || '-'} CU:${prova.cu || '-'}
                            </small>
                        </div>
                    </div>
                </div>
            `;
        });
        
        provaListDiv.html(html);
    }
    
    // Günlük rapor oluşturma
    function generateDailyReport() {
        $.post('/reports/daily')
            .done(function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                } else {
                    showToast(response.message, 'error');
                }
            });
    }
    
    // Veri yenileme fonksiyonları
    function refreshFurnaceData() {
        location.reload();
    }
    
    function refreshCastingList() {
        location.reload();
    }
    
    // Excel export (placeholder)
    function exportTodaysCastings() {
        showToast('Excel export özelliği geliştiriliyor...', 'info');
    }
    
    // Sayfa yüklendiğinde
    $(document).ready(function() {
        // Real-time güncellemeleri başlat
        startRealTimeUpdates();
        
        // Sayfa kapatılırken interval'ı temizle
        $(window).on('beforeunload', function() {
            stopRealTimeUpdates();
        });
        
        // Modal temizleme
        $('#qualityControlModal').on('hidden.bs.modal', function() {
            $('#qualityControlForm')[0].reset();
            $('#casting-info').empty();
        });
    });
</script>
@endpush
