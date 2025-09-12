@extends('layouts.app')

@section('title', 'Kontrol Paneli - Cansan Kalite Kontrol')

@section('header', 'Kontrol Paneli')

@section('header-buttons')
    <div class="btn-group d-none d-md-flex" role="group">
        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addProvaModal" onclick="console.log('Yeni Döküm butonu tıklandı')">
            <i class="fas fa-plus-circle me-1"></i>
            Yeni Döküm
        </button>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProvaModal">
            <i class="fas fa-plus me-1"></i>
            Yeni Prova
        </button>
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="refreshDashboard()">
            <i class="fas fa-sync-alt me-1"></i>
            Yenile
        </button>
        <button type="button" class="btn btn-outline-info btn-sm" onclick="toggleAutoRefresh()">
            <i class="fas fa-clock me-1"></i>
            <span id="auto-refresh-text">Otomatik Yenileme</span>
        </button>
    </div>
    
    <!-- Mobile buttons -->
    <div class="d-flex d-md-none gap-2">
        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addProvaModal">
            <i class="fas fa-plus-circle"></i>
        </button>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProvaModal">
            <i class="fas fa-plus"></i>
        </button>
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="refreshDashboard()">
            <i class="fas fa-sync-alt"></i>
        </button>
    </div>
@endsection

@section('content')
<!-- Sistem Durumu Kartları -->
<div class="row mb-4">
    <div class="col-6 col-md-3 mb-3">
        <div class="card stat-card h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="stat-number" id="total-castings">{{ $dailyStats['total_castings'] }}</div>
                <div class="text-muted d-none d-sm-block">Bugünkü Döküm</div>
                <div class="text-muted d-block d-sm-none">Döküm</div>
                <small class="text-success d-none d-md-block">
                    <i class="fas fa-arrow-up me-1"></i>
                    Aktif Ocak: {{ $dailyStats['active_furnaces'] }}
                </small>
                <small class="text-success d-block d-md-none">
                    <i class="fas fa-fire me-1"></i>
                    {{ $dailyStats['active_furnaces'] }} Ocak
                </small>
            </div>
        </div>
    </div>
    
    <div class="col-6 col-md-3 mb-3">
        <div class="card stat-card h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="stat-number" id="total-samples">{{ $dailyStats['total_samples'] }}</div>
                <div class="text-muted d-none d-sm-block">Bugünkü Prova</div>
                <div class="text-muted d-block d-sm-none">Prova</div>
                <small class="text-info d-none d-md-block">
                    <i class="fas fa-vial me-1"></i>
                    Haftalık: {{ $weeklyStats['total_samples'] }}
                </small>
                <small class="text-info d-block d-md-none">
                    <i class="fas fa-vial me-1"></i>
                    Hafta: {{ $weeklyStats['total_samples'] }}
                </small>
            </div>
        </div>
    </div>
    
    <div class="col-6 col-md-3 mb-3">
        <div class="card stat-card h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="stat-number text-success" id="approved-samples">{{ $dailyStats['approved_samples'] }}</div>
                <div class="text-muted d-none d-sm-block">Onaylanan Prova</div>
                <div class="text-muted d-block d-sm-none">Onay</div>
                <small class="text-warning d-none d-md-block">
                    <i class="fas fa-hourglass-half me-1"></i>
                    Bekleyen: {{ $dailyStats['pending_samples'] }}
                </small>
                <small class="text-warning d-block d-md-none">
                    <i class="fas fa-clock me-1"></i>
                    Bekle: {{ $dailyStats['pending_samples'] }}
                </small>
            </div>
        </div>
    </div>
    
    <div class="col-6 col-md-3 mb-3">
        <div class="card stat-card h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="stat-number text-primary" id="quality-rate">{{ $weeklyStats['quality_rate'] }}%</div>
                <div class="text-muted d-none d-sm-block">Kalite Oranı</div>
                <div class="text-muted d-block d-sm-none">Kalite</div>
                <small class="text-danger d-none d-md-block">
                    <i class="fas fa-times-circle me-1"></i>
                    Reddedilen: {{ $dailyStats['rejected_samples'] }}
                </small>
                <small class="text-danger d-block d-md-none">
                    <i class="fas fa-times me-1"></i>
                    Red: {{ $dailyStats['rejected_samples'] }}
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Ocak Durumu ve Aktif Dökümler -->
<div class="row mb-4">
    <div class="col-12 col-lg-8 mb-4 mb-lg-0">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-fire me-2"></i>
                    <span class="d-none d-sm-inline">Ocak Durumu ve Aktif Dökümler</span>
                    <span class="d-inline d-sm-none">Ocaklar</span>
                </h5>
                <span class="badge bg-success">{{ count($activeFurnaces) }} Aktif</span>
            </div>
            <div class="card-body">
                @if(empty($setStats))
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                        <p>Henüz ocak verisi bulunmuyor</p>
                    </div>
                @else
                    <div class="row">
                        @foreach($setStats as $setStat)
                            <div class="col-12 col-sm-6 col-lg-4 mb-3">
                                <div class="card border-0 bg-light h-100">
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="furnace-status {{ $setStat['active_furnace'] ? 'furnace-active' : 'furnace-inactive' }}"></span>
                                            <h6 class="mb-0 flex-grow-1">{{ $setStat['set']->name }}</h6>
                                        </div>
                                        
                                        @if($setStat['active_furnace'])
                                            <div class="mb-2">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <strong class="text-primary">{{ $setStat['active_furnace']->name }}</strong>
                                                    <span class="badge bg-success badge-sm">Aktif</span>
                                                </div>
                                            </div>
                                            
                                            <div class="small text-muted d-none d-md-block mb-2">
                                                <div class="row">
                                                    <div class="col-4 text-center">
                                                        <div class="fw-bold">{{ $setStat['daily_castings'] }}</div>
                                                        <div>Günlük</div>
                                                    </div>
                                                    <div class="col-4 text-center">
                                                        <div class="fw-bold">{{ $setStat['weekly_castings'] }}</div>
                                                        <div>Haftalık</div>
                                                    </div>
                                                    <div class="col-4 text-center">
                                                        <div class="fw-bold">{{ $setStat['monthly_castings'] }}</div>
                                                        <div>Aylık</div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="small text-muted d-block d-md-none mb-2">
                                                <div class="d-flex justify-content-between">
                                                    <span>Gün: <strong>{{ $setStat['daily_castings'] }}</strong></span>
                                                    <span>Hafta: <strong>{{ $setStat['weekly_castings'] }}</strong></span>
                                                    <span>Ay: <strong>{{ $setStat['monthly_castings'] }}</strong></span>
                                                </div>
                                            </div>
                                            
                                            @php
                                                $activeCasting = collect($recentActivities['active_castings'])->firstWhere('furnace_id', $setStat['active_furnace']->id);
                                            @endphp
                                            
                                            @if($activeCasting)
                                                <div class="mt-auto p-2 bg-white rounded">
                                                    <div class="small">
                                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                                            <strong>Döküm #{{ $activeCasting->casting_number }}</strong>
                                                            <span class="badge {{ $activeCasting->getQualityStatus() === 'approved' ? 'bg-success' : ($activeCasting->getQualityStatus() === 'rejected' ? 'bg-danger' : 'bg-warning') }}">
                                                                {{ ucfirst($activeCasting->getQualityStatus()) }}
                                                            </span>
                                                        </div>
                                                        <div class="text-muted">Prova: {{ $activeCasting->samples->count() }} adet</div>
                                                    </div>
                                                </div>
                                            @endif
                                        @else
                                            <div class="mt-auto">
                                                <p class="text-muted mb-0 text-center">
                                                    <i class="fas fa-power-off me-1"></i>
                                                    Aktif ocak yok
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    <span class="d-none d-sm-inline">Günlük Kalite Dağılımı</span>
                    <span class="d-inline d-sm-none">Kalite</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height: 200px; max-height: 200px;">
                    <canvas id="qualityChart"></canvas>
                </div>
                
                <div class="mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small">Onaylanan</span>
                        <span class="badge badge-quality-approved">{{ $dailyStats['approved_samples'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small">Reddedilen</span>
                        <span class="badge badge-quality-rejected">{{ $dailyStats['rejected_samples'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small">Bekleyen</span>
                        <span class="badge badge-quality-pending">{{ $dailyStats['pending_samples'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small">Düzeltme Gerekli</span>
                        <span class="badge badge-quality-needs-adjustment">{{ $dailyStats['needs_adjustment'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hızlı İşlemler -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>
                    Hızlı İşlemler
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 col-md-3 mb-3">
                        <button type="button" class="btn btn-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" data-bs-toggle="modal" data-bs-target="#addProvaModal">
                            <i class="fas fa-plus-circle fa-2x mb-2"></i>
                            <span>Yeni Döküm</span>
                        </button>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <button type="button" class="btn btn-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" data-bs-toggle="modal" data-bs-target="#addProvaModal">
                            <i class="fas fa-vial fa-2x mb-2"></i>
                            <span>Yeni Prova</span>
                        </button>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <a href="{{ route('samples.index') }}" class="btn btn-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-list fa-2x mb-2"></i>
                            <span>Provalar</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <a href="{{ route('reports.daily') }}" class="btn btn-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-chart-line fa-2x mb-2"></i>
                            <span>Raporlar</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hızlı İşlemler Modal -->
<div class="modal fade" id="quickActionsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hızlı İşlemler</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProvaModal">
                        <i class="fas fa-plus me-2"></i>
                        Yeni Prova Ekle
                    </button>
                    <a href="{{ route('samples.index') }}" class="btn btn-warning">
                        <i class="fas fa-hourglass-half me-2"></i>
                        Bekleyen Provaları Görüntüle
                    </a>
                    <a href="{{ route('reports.daily') }}" class="btn btn-info">
                        <i class="fas fa-chart-line me-2"></i>
                        Günlük Rapor
                    </a>
                    <button type="button" class="btn btn-success" onclick="exportDailyReport()">
                        <i class="fas fa-download me-2"></i>
                        Rapor İndir
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Yeni Prova Ekleme Modalı -->
<div class="modal fade" id="addProvaModal" tabindex="-1" aria-labelledby="addProvaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProvaModalLabel">Yeni Prova Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addProvaForm">
                    <input type="hidden" id="castingId" name="casting_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="new_carbon" class="form-label">Karbon (C)</label>
                            <input type="number" class="form-control" id="new_carbon" name="carbon" step="0.01" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="new_silicon" class="form-label">Silisyum (Sİ)</label>
                            <input type="number" class="form-control" id="new_silicon" name="silicon" step="0.01" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="new_manganese" class="form-label">Mangan (MN)</label>
                            <input type="number" class="form-control" id="new_manganese" name="manganese" step="0.01" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="new_sulfur" class="form-label">Kükürt (S)</label>
                            <input type="number" class="form-control" id="new_sulfur" name="sulfur" step="0.01" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="new_phosphorus" class="form-label">Fosfor (P)</label>
                            <input type="number" class="form-control" id="new_phosphorus" name="phosphorus" step="0.01" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="new_copper" class="form-label">Bakır (CU)</label>
                            <input type="number" class="form-control" id="new_copper" name="copper" step="0.01" min="0">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-success" id="saveNewProvaBtn">Prova Ekle</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let autoRefreshInterval = null;
let isAutoRefreshEnabled = false;

// Kalite dağılımı grafiği
const ctx = document.getElementById('qualityChart').getContext('2d');
const qualityChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Onaylanan', 'Reddedilen', 'Bekleyen', 'Düzeltme Gerekli'],
        datasets: [{
            data: [
                @json($dailyStats['approved_samples']),
                @json($dailyStats['rejected_samples']),
                @json($dailyStats['pending_samples']),
                @json($dailyStats['needs_adjustment'])
            ],
            backgroundColor: [
                '#059669', // success
                '#dc2626', // danger
                '#d97706', // warning
                '#0284c7'  // info
            ],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            x: {
                display: false
            },
            y: {
                display: false
            }
        }
    }
});

// Dashboard yenileme fonksiyonu
function refreshDashboard() {
    const refreshBtn = document.querySelector('button[onclick="refreshDashboard()"]');
    showLoading(refreshBtn);
    
    fetch('{{ route("dashboard.realtime") }}')
        .then(response => response.json())
        .then(data => {
            updateDashboardData(data);
            showToast('Dashboard güncellendi', 'success');
        })
        .catch(error => {
            console.error('Dashboard güncellenemedi:', error);
            showToast('Dashboard güncellenirken hata oluştu', 'error');
        })
        .finally(() => {
            hideLoading(refreshBtn);
        });
}

// Dashboard verilerini güncelle
function updateDashboardData(data) {
    // Buraya gerçek zamanlı veri güncelleme kodları gelecek
    console.log('Dashboard data updated:', data);
}

// Otomatik yenileme toggle
function toggleAutoRefresh() {
    const button = document.querySelector('button[onclick="toggleAutoRefresh()"]');
    const text = document.getElementById('auto-refresh-text');
    
    if (isAutoRefreshEnabled) {
        clearInterval(autoRefreshInterval);
        isAutoRefreshEnabled = false;
        button.classList.remove('btn-success');
        button.classList.add('btn-outline-info');
        text.textContent = 'Otomatik Yenileme';
        showToast('Otomatik yenileme kapatıldı', 'info');
    } else {
        autoRefreshInterval = setInterval(refreshDashboard, 30000); // 30 saniye
        isAutoRefreshEnabled = true;
        button.classList.remove('btn-outline-info');
        button.classList.add('btn-success');
        text.textContent = 'Otomatik Açık';
        showToast('Otomatik yenileme açıldı (30s)', 'success');
    }
}

// Günlük rapor export
function exportDailyReport() {
    const today = new Date().toISOString().split('T')[0];
    window.open(`{{ route('reports.export') }}?type=daily&date=${today}`, '_blank');
}

// Kalite durumu badge sınıfları
function getQualityBadgeClass(status) {
    switch(status) {
        case 'approved': return 'bg-success';
        case 'rejected': return 'bg-danger';
        case 'pending': return 'bg-warning';
        case 'needs_adjustment': return 'bg-info';
        default: return 'bg-secondary';
    }
}

// Kalite durumu Türkçe metinleri
function getQualityStatusText(status) {
    switch(status) {
        case 'approved': return 'Onaylandı';
        case 'rejected': return 'Reddedildi';
        case 'pending': return 'Beklemede';
        case 'needs_adjustment': return 'Düzeltme Gerekli';
        default: return status;
    }
}

// Klavye kısayolları
document.addEventListener('keydown', function(e) {
    // Ctrl + R: Dashboard yenile
    if (e.ctrlKey && e.key === 'r') {
        e.preventDefault();
        refreshDashboard();
    }
    
    // Ctrl + N: Yeni prova
    if (e.ctrlKey && e.key === 'n') {
        e.preventDefault();
        $('#addProvaModal').modal('show');
    }
    
    // Ctrl + P: Bekleyen provalar
    if (e.ctrlKey && e.key === 'p') {
        e.preventDefault();
        window.location.href = '{{ route("samples.index") }}';
    }
});

// Sayfa yüklendiğinde
document.addEventListener('DOMContentLoaded', function() {
    // Otomatik yenileme geçici olarak devre dışı
    console.log('Dashboard loaded - Auto refresh disabled');
});

// Sayfa kapanırken otomatik yenilemeyi durdur
window.addEventListener('beforeunload', function() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
});

// Yeni prova ekleme - Sadece bir kez bağla
$(document).off('click', '.add-prova-btn').on('click', '.add-prova-btn', function() {
    const castingId = $(this).data('casting-id');
    $('#castingId').val(castingId);
    $('#addProvaForm')[0].reset();
    $('#addProvaModal').modal('show');
});

// Yeni prova kaydetme - Sadece bir kez bağla
$(document).off('click', '#saveNewProvaBtn').on('click', '#saveNewProvaBtn', function(e) {
    e.preventDefault();
    
    console.log('Prova ekleme butonu tıklandı'); // Debug
    
    const castingId = $('#castingId').val();
    console.log('Casting ID:', castingId); // Debug
    
    if (!castingId) {
        alert('Döküm ID bulunamadı!');
        return;
    }
    
    const formData = {
        casting_id: castingId,
        carbon: $('#new_carbon').val() || 0,
        silicon: $('#new_silicon').val() || 0,
        manganese: $('#new_manganese').val() || 0,
        sulfur: $('#new_sulfur').val() || 0,
        phosphorus: $('#new_phosphorus').val() || 0,
        copper: $('#new_copper').val() || 0,
        _token: $('meta[name="csrf-token"]').attr('content')
    };
    
    console.log('Gönderilen veri:', formData); // Debug
    
    // Butonu devre dışı bırak
    $(this).prop('disabled', true).text('Ekleniyor...');
    
    $.ajax({
        url: '/samples',
        type: 'POST',
        data: formData,
        dataType: 'json',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
        },
        success: function(response) {
            console.log('Başarılı yanıt:', response);
            alert('Prova başarıyla eklendi!');
            // Modalı kapat
            $('#addProvaModal').modal('hide');
            // Sayfayı yenile
            location.reload();
        },
        error: function(xhr, status, error) {
            console.error('AJAX Hatası:', {
                status: xhr.status,
                statusText: xhr.statusText,
                responseText: xhr.responseText,
                error: error
            });
            
            let errorMessage = 'Bilinmeyen hata';
            if (xhr.responseJSON) {
                if (xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON.errors) {
                    errorMessage = Object.values(xhr.responseJSON.errors).flat().join(', ');
                }
            } else if (xhr.status === 422) {
                errorMessage = 'Doğrulama hatası - Lütfen tüm alanları kontrol edin';
            } else if (xhr.status === 500) {
                errorMessage = 'Sunucu hatası - Lütfen tekrar deneyin';
            } else if (xhr.status === 404) {
                errorMessage = 'Sayfa bulunamadı - Route kontrol edin';
            }
            
            alert('Hata: ' + errorMessage);
        },
        complete: function() {
            // Butonu tekrar aktif et
            $('#saveNewProvaBtn').prop('disabled', false).text('Prova Ekle');
        }
    });
});
</script>
@endpush
