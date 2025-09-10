@extends('layouts.app')

@section('title', 'Raporlar - Cansan Kalite Kontrol')

@section('header', 'Raporlama Sistemi')

@section('header-buttons')
    <div class="btn-group d-none d-md-flex" role="group">
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="refreshReports()">
            <i class="fas fa-sync-alt me-1"></i>
            Yenile
        </button>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-success btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-download me-1"></i>
                İndir
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" onclick="exportReport('daily')">Günlük Rapor</a></li>
                <li><a class="dropdown-item" href="#" onclick="exportReport('weekly')">Haftalık Rapor</a></li>
                <li><a class="dropdown-item" href="#" onclick="exportReport('monthly')">Aylık Rapor</a></li>
            </ul>
        </div>
    </div>
    
    <!-- Mobile buttons -->
    <div class="d-flex d-md-none gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="refreshReports()">
            <i class="fas fa-sync-alt"></i>
        </button>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-success btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-download"></i>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" onclick="exportReport('daily')">Günlük</a></li>
                <li><a class="dropdown-item" href="#" onclick="exportReport('weekly')">Haftalık</a></li>
                <li><a class="dropdown-item" href="#" onclick="exportReport('monthly')">Aylık</a></li>
            </ul>
        </div>
    </div>
@endsection

@section('content')
<!-- Rapor Türleri -->
<div class="row mb-4">
    <div class="col-12 col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-calendar-day fa-3x text-primary mb-3"></i>
                <h5 class="card-title">Günlük Rapor</h5>
                <p class="card-text d-none d-sm-block">Bugünkü döküm, prova ve kalite istatistikleri. Ocak bazında detaylı analiz.</p>
                <p class="card-text d-block d-sm-none">Günlük döküm ve prova istatistikleri</p>
                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                    <a href="{{ route('reports.daily') }}" class="btn btn-primary">
                        <i class="fas fa-eye me-1"></i>
                        <span class="d-none d-sm-inline">Görüntüle</span>
                        <span class="d-inline d-sm-none">Gör</span>
                    </a>
                    <button class="btn btn-outline-primary" onclick="exportReport('daily')">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-calendar-week fa-3x text-success mb-3"></i>
                <h5 class="card-title">Haftalık Rapor</h5>
                <p class="card-text d-none d-sm-block">Son 7 günün trend analizi. Ocak performans karşılaştırması ve kalite oranları.</p>
                <p class="card-text d-block d-sm-none">Haftalık trend analizi</p>
                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                    <a href="{{ route('reports.weekly') }}" class="btn btn-success">
                        <i class="fas fa-eye me-1"></i>
                        <span class="d-none d-sm-inline">Görüntüle</span>
                        <span class="d-inline d-sm-none">Gör</span>
                    </a>
                    <button class="btn btn-outline-success" onclick="exportReport('weekly')">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-calendar-alt fa-3x text-info mb-3"></i>
                <h5 class="card-title">Aylık Rapor</h5>
                <p class="card-text d-none d-sm-block">Aylık performans özeti. Ham madde tüketimi, vardiya analizi ve genel trend.</p>
                <p class="card-text d-block d-sm-none">Aylık performans özeti</p>
                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                    <a href="{{ route('reports.monthly') }}" class="btn btn-info">
                        <i class="fas fa-eye me-1"></i>
                        <span class="d-none d-sm-inline">Görüntüle</span>
                        <span class="d-inline d-sm-none">Gör</span>
                    </a>
                    <button class="btn btn-outline-info" onclick="exportReport('monthly')">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hızlı İstatistikler -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    Hızlı İstatistikler
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 col-md-3 mb-3">
                        <div class="border rounded p-3">
                            <i class="fas fa-fire text-primary fa-2x mb-2"></i>
                            <h4 class="text-primary" id="today-castings">-</h4>
                            <small class="text-muted d-none d-sm-block">Bugünkü Döküm</small>
                            <small class="text-muted d-block d-sm-none">Döküm</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="border rounded p-3">
                            <i class="fas fa-vial text-success fa-2x mb-2"></i>
                            <h4 class="text-success" id="today-samples">-</h4>
                            <small class="text-muted d-none d-sm-block">Bugünkü Prova</small>
                            <small class="text-muted d-block d-sm-none">Prova</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="border rounded p-3">
                            <i class="fas fa-check-circle text-info fa-2x mb-2"></i>
                            <h4 class="text-info" id="quality-rate">-%</h4>
                            <small class="text-muted d-none d-sm-block">Kalite Oranı</small>
                            <small class="text-muted d-block d-sm-none">Kalite</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="border rounded p-3">
                            <i class="fas fa-tools text-warning fa-2x mb-2"></i>
                            <h4 class="text-warning" id="today-adjustments">-</h4>
                            <small class="text-muted d-none d-sm-block">Ham Madde Ekleme</small>
                            <small class="text-muted d-block d-sm-none">Ham Madde</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rapor Geçmişi -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i>
                    Son Raporlar
                </h5>
                <span class="badge bg-secondary">Yakında</span>
            </div>
            <div class="card-body">
                <div class="text-center text-muted py-4">
                    <i class="fas fa-clock fa-3x mb-3"></i>
                    <p>Rapor geçmişi özelliği yakında eklenecek</p>
                    <small>Oluşturulan raporlar burada listelenecek</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rapor Ayarları Modal -->
<div class="modal fade" id="reportSettingsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rapor Ayarları</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="reportSettingsForm">
                    <div class="mb-3">
                        <label for="reportType" class="form-label">Rapor Türü</label>
                        <select class="form-select" id="reportType">
                            <option value="daily">Günlük</option>
                            <option value="weekly">Haftalık</option>
                            <option value="monthly">Aylık</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="reportDate" class="form-label">Tarih</label>
                        <input type="date" class="form-control" id="reportDate" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label for="reportFormat" class="form-label">Format</label>
                        <select class="form-select" id="reportFormat">
                            <option value="html">HTML (Görüntüle)</option>
                            <option value="excel">Excel (.xlsx)</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="includeCharts" checked>
                        <label class="form-check-label" for="includeCharts">
                            Grafikleri dahil et
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-primary" onclick="generateCustomReport()">Rapor Oluştur</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Hızlı istatistikleri yükle
function loadQuickStats() {
    fetch('/api/v1/dashboard')
        .then(response => response.json())
        .then(data => {
            // Burada API'den gelen verilerle istatistikleri güncelleyeceğiz
            document.getElementById('today-castings').textContent = '0';
            document.getElementById('today-samples').textContent = '0';
            document.getElementById('quality-rate').textContent = '0%';
            document.getElementById('today-adjustments').textContent = '0';
        })
        .catch(error => {
            console.log('İstatistikler yüklenemedi:', error);
        });
}

// Raporları yenile
function refreshReports() {
    loadQuickStats();
    showToast('Raporlar yenilendi', 'success');
}

// Rapor export
function exportReport(type) {
    const today = new Date().toISOString().split('T')[0];
    const url = `{{ route('reports.export') }}?type=${type}&date=${today}`;
    
    showToast(`${type.charAt(0).toUpperCase() + type.slice(1)} raporu indiriliyor...`, 'info');
    
    // Gerçek implementasyonda dosya indirme işlemi yapılacak
    setTimeout(() => {
        showToast('Rapor hazırlandı', 'success');
    }, 2000);
}

// Özel rapor oluştur
function generateCustomReport() {
    const form = document.getElementById('reportSettingsForm');
    const formData = new FormData(form);
    
    const settings = {
        type: document.getElementById('reportType').value,
        date: document.getElementById('reportDate').value,
        format: document.getElementById('reportFormat').value,
        includeCharts: document.getElementById('includeCharts').checked
    };
    
    console.log('Rapor ayarları:', settings);
    
    // Modal'ı kapat
    const modal = bootstrap.Modal.getInstance(document.getElementById('reportSettingsModal'));
    modal.hide();
    
    // Rapor oluştur
    if (settings.format === 'html') {
        window.open(`/reports/${settings.type}?date=${settings.date}`, '_blank');
    } else {
        exportReport(settings.type);
    }
}

// Sayfa yüklendiğinde
document.addEventListener('DOMContentLoaded', function() {
    loadQuickStats();
    
    // Her 30 saniyede bir istatistikleri güncelle
    setInterval(loadQuickStats, 30000);
});

// Klavye kısayolları
document.addEventListener('keydown', function(e) {
    // Ctrl + 1: Günlük rapor
    if (e.ctrlKey && e.key === '1') {
        e.preventDefault();
        window.location.href = '{{ route("reports.daily") }}';
    }
    
    // Ctrl + 2: Haftalık rapor
    if (e.ctrlKey && e.key === '2') {
        e.preventDefault();
        window.location.href = '{{ route("reports.weekly") }}';
    }
    
    // Ctrl + 3: Aylık rapor
    if (e.ctrlKey && e.key === '3') {
        e.preventDefault();
        window.location.href = '{{ route("reports.monthly") }}';
    }
});
</script>
@endpush
