@extends('layouts.app')

@section('title', 'Kontrol Paneli - Cansan Kalite Kontrol')

@section('header', 'Kontrol Paneli')

@section('header-buttons')
    <div class="btn-group" role="group">
        <a href="{{ route('castings.create') }}" class="btn btn-success btn-sm" onclick="console.log('Yeni Döküm butonu tıklandı')">
            <i class="fas fa-plus-circle me-1"></i>
            Yeni Döküm
        </a>
        <a href="{{ route('samples.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>
            Yeni Prova
        </a>
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="refreshDashboard()">
            <i class="fas fa-sync-alt me-1"></i>
            Yenile
        </button>
        <button type="button" class="btn btn-outline-info btn-sm" onclick="toggleAutoRefresh()">
            <i class="fas fa-clock me-1"></i>
            <span id="auto-refresh-text">Otomatik Yenileme</span>
        </button>
    </div>
@endsection

@section('content')
<!-- Sistem Durumu Kartları -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="stat-number" id="total-castings">{{ $dailyStats['total_castings'] }}</div>
                <div class="text-muted">Bugünkü Döküm</div>
                <small class="text-success">
                    <i class="fas fa-arrow-up me-1"></i>
                    Aktif Ocak: {{ $dailyStats['active_furnaces'] }}
                </small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="stat-number" id="total-samples">{{ $dailyStats['total_samples'] }}</div>
                <div class="text-muted">Bugünkü Prova</div>
                <small class="text-info">
                    <i class="fas fa-vial me-1"></i>
                    Haftalık: {{ $weeklyStats['total_samples'] }}
                </small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="stat-number text-success" id="approved-samples">{{ $dailyStats['approved_samples'] }}</div>
                <div class="text-muted">Onaylanan Prova</div>
                <small class="text-warning">
                    <i class="fas fa-hourglass-half me-1"></i>
                    Bekleyen: {{ $dailyStats['pending_samples'] }}
                </small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="stat-number text-primary" id="quality-rate">{{ $weeklyStats['quality_rate'] }}%</div>
                <div class="text-muted">Kalite Oranı</div>
                <small class="text-danger">
                    <i class="fas fa-times-circle me-1"></i>
                    Reddedilen: {{ $dailyStats['rejected_samples'] }}
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Ocak Durumu ve Aktif Dökümler -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-fire me-2"></i>
                    Ocak Durumu ve Aktif Dökümler
                </h5>
                <span class="badge bg-success">{{ count($activeFurnaces) }} Aktif Ocak</span>
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
                            <div class="col-md-4 mb-3">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="furnace-status {{ $setStat['active_furnace'] ? 'furnace-active' : 'furnace-inactive' }}"></span>
                                            <h6 class="mb-0">{{ $setStat['set']->name }}</h6>
                                        </div>
                                        
                                        @if($setStat['active_furnace'])
                                            <p class="mb-1">
                                                <strong>{{ $setStat['active_furnace']->name }}</strong>
                                                <span class="badge bg-success badge-sm ms-1">Aktif</span>
                                            </p>
                                            
                                            <div class="small text-muted">
                                                <div>Günlük: {{ $setStat['daily_castings'] }} döküm</div>
                                                <div>Haftalık: {{ $setStat['weekly_castings'] }} döküm</div>
                                                <div>Aylık: {{ $setStat['monthly_castings'] }} döküm</div>
                                            </div>
                                            
                                            @php
                                                $activeCasting = collect($recentActivities['active_castings'])->firstWhere('furnace_id', $setStat['active_furnace']->id);
                                            @endphp
                                            
                                            @if($activeCasting)
                                                <div class="mt-2 p-2 bg-white rounded">
                                                    <div class="small">
                                                        <strong>Döküm #{{ $activeCasting->casting_number }}</strong>
                                                        <div>Prova: {{ $activeCasting->samples->count() }} adet</div>
                                                        <div>Durum: 
                                                            <span class="badge {{ $activeCasting->getQualityStatus() === 'approved' ? 'bg-success' : ($activeCasting->getQualityStatus() === 'rejected' ? 'bg-danger' : 'bg-warning') }}">
                                                                {{ ucfirst($activeCasting->getQualityStatus()) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @else
                                            <p class="text-muted mb-0">Aktif ocak yok</p>
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
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Günlük Kalite Dağılımı
                </h5>
            </div>
            <div class="card-body">
                <canvas id="qualityChart" width="300" height="200" style="max-height: 200px;"></canvas>
                
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

<!-- Son Aktiviteler -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-clock me-2"></i>
                    Son Provalar
                </h5>
                <a href="{{ route('samples.index') }}" class="btn btn-outline-primary btn-sm">
                    Tümünü Gör
                </a>
            </div>
            <div class="card-body">
                @if($recentActivities['latest_samples']->isEmpty())
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-info-circle me-1"></i>
                        Henüz prova kaydı yok
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($recentActivities['latest_samples']->take(5) as $sample)
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">
                                            {{ $sample->casting->furnace->name ?? 'N/A' }} - 
                                            Döküm #{{ $sample->casting->casting_number }}
                                        </h6>
                                        <p class="mb-1 small">
                                            Prova #{{ $sample->sample_number }} - 
                                            {{ $sample->analyzed_by }}
                                        </p>
                                        <small class="text-muted">
                                            {{ $sample->sample_time->diffForHumans() }}
                                        </small>
                                    </div>
                                    <span class="badge 
                                        @if($sample->quality_status === 'approved') bg-success
                                        @elseif($sample->quality_status === 'rejected') bg-danger
                                        @elseif($sample->quality_status === 'pending') bg-warning
                                        @elseif($sample->quality_status === 'needs_adjustment') bg-info
                                        @else bg-secondary
                                        @endif">
                                        @if($sample->quality_status === 'approved') Onaylandı
                                        @elseif($sample->quality_status === 'rejected') Reddedildi
                                        @elseif($sample->quality_status === 'pending') Beklemede
                                        @elseif($sample->quality_status === 'needs_adjustment') Düzeltme Gerekli
                                        @else {{ $sample->quality_status }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-tools me-2"></i>
                    Son Ham Madde Eklemeleri
                </h5>
                <span class="badge bg-info">{{ $dailyStats['total_adjustments'] }} Bugün</span>
            </div>
            <div class="card-body">
                @if($recentActivities['latest_adjustments']->isEmpty())
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-info-circle me-1"></i>
                        Bugün ham madde eklenmedi
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($recentActivities['latest_adjustments'] as $adjustment)
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">
                                            {{ $adjustment->getMaterialNameTurkish() }}
                                            <small class="text-muted">({{ $adjustment->amount_kg }} kg)</small>
                                        </h6>
                                        <p class="mb-1 small">
                                            {{ $adjustment->casting->furnace->name ?? 'N/A' }} - 
                                            Döküm #{{ $adjustment->casting->casting_number }}
                                        </p>
                                        <small class="text-muted">
                                            {{ $adjustment->adjustment_date->diffForHumans() }} - 
                                            {{ $adjustment->added_by }}
                                        </small>
                                    </div>
                                    <span class="badge {{ $adjustment->is_successful ? 'bg-success' : 'bg-warning' }}">
                                        {{ $adjustment->is_successful ? 'Başarılı' : 'Beklemede' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
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
                    <a href="{{ route('samples.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Yeni Prova Ekle
                    </a>
                    <a href="{{ route('samples.pending') }}" class="btn btn-warning">
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
                {{ $dailyStats['approved_samples'] }},
                {{ $dailyStats['rejected_samples'] }},
                {{ $dailyStats['pending_samples'] }},
                {{ $dailyStats['needs_adjustment'] }}
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
        maintainAspectRatio: true,
        plugins: {
            legend: {
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
        window.location.href = '{{ route("samples.create") }}';
    }
    
    // Ctrl + P: Bekleyen provalar
    if (e.ctrlKey && e.key === 'p') {
        e.preventDefault();
        window.location.href = '{{ route("samples.pending") }}';
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
</script>
@endpush
