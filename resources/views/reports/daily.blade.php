@extends('layouts.app')

@section('title', 'Günlük Rapor - Cansan Kalite Kontrol')

@section('header', 'Günlük Rapor')

@section('header-buttons')
    <!-- Desktop buttons -->
    <div class="btn-group d-none d-md-flex" role="group">
        <input type="date" class="form-control form-control-sm" id="reportDate" value="{{ request('date', date('Y-m-d')) }}" onchange="changeDate()">
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="printReport()">
            <i class="fas fa-print me-1"></i>
            Yazdır
        </button>
        <button type="button" class="btn btn-outline-success btn-sm" onclick="exportExcel()">
            <i class="fas fa-file-excel me-1"></i>
            Excel
        </button>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>
            Geri
        </a>
    </div>
    
    <!-- Mobile buttons -->
    <div class="d-flex d-md-none gap-2">
        <input type="date" class="form-control form-control-sm" id="reportDateMobile" value="{{ request('date', date('Y-m-d')) }}" onchange="changeDate()">
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="printReport()">
            <i class="fas fa-print"></i>
        </button>
        <button type="button" class="btn btn-outline-success btn-sm" onclick="exportExcel()">
            <i class="fas fa-file-excel"></i>
        </button>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i>
        </a>
    </div>
@endsection

@section('content')
<!-- Rapor Başlığı -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="mb-1">Cansan Çelik Üretim Fabrikası</h3>
                <h4 class="text-primary mb-1">Günlük Kalite Kontrol Raporu</h4>
                <p class="text-muted mb-0">
                    <i class="fas fa-calendar me-1"></i>
                    {{ request('date') ? \Carbon\Carbon::parse(request('date'))->format('d.m.Y') : date('d.m.Y') }}
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Özet İstatistikler -->
<div class="row mb-4">
    <div class="col-6 col-md-3 mb-3">
        <div class="card stat-card text-center">
            <div class="card-body">
                <i class="fas fa-fire text-primary fa-2x mb-2"></i>
                <h3 class="text-primary mb-1">0</h3>
                <small class="text-muted d-none d-sm-block">Toplam Döküm</small>
                <small class="text-muted d-block d-sm-none">Döküm</small>
            </div>
        </div>
    </div>
    
    <div class="col-6 col-md-3 mb-3">
        <div class="card stat-card text-center">
            <div class="card-body">
                <i class="fas fa-vial text-success fa-2x mb-2"></i>
                <h3 class="text-success mb-1">0</h3>
                <small class="text-muted d-none d-sm-block">Toplam Prova</small>
                <small class="text-muted d-block d-sm-none">Prova</small>
            </div>
        </div>
    </div>
    
    <div class="col-6 col-md-3 mb-3">
        <div class="card stat-card text-center">
            <div class="card-body">
                <i class="fas fa-check-circle text-info fa-2x mb-2"></i>
                <h3 class="text-info mb-1">0%</h3>
                <small class="text-muted d-none d-sm-block">Kalite Oranı</small>
                <small class="text-muted d-block d-sm-none">Kalite</small>
            </div>
        </div>
    </div>
    
    <div class="col-6 col-md-3 mb-3">
        <div class="card stat-card text-center">
            <div class="card-body">
                <i class="fas fa-tools text-warning fa-2x mb-2"></i>
                <h3 class="text-warning mb-1">0</h3>
                <small class="text-muted d-none d-sm-block">Ham Madde Ekleme</small>
                <small class="text-muted d-block d-sm-none">Ham Madde</small>
            </div>
        </div>
    </div>
</div>

<!-- Ocak Bazında Detaylar -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-fire me-2"></i>
                    Ocak Bazında Döküm Detayları
                </h5>
            </div>
            <div class="card-body">
                <!-- Desktop Table -->
                <div class="table-responsive d-none d-lg-block">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Set</th>
                                <th>Ocak</th>
                                <th>Durum</th>
                                <th>Döküm Sayısı</th>
                                <th>Prova Sayısı</th>
                                <th>Kalite Oranı</th>
                                <th>Son Döküm</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Set 1</td>
                                <td>Ocak 1</td>
                                <td><span class="badge bg-success">Aktif</span></td>
                                <td>0</td>
                                <td>0</td>
                                <td>0%</td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>Set 2</td>
                                <td>Ocak 3</td>
                                <td><span class="badge bg-success">Aktif</span></td>
                                <td>0</td>
                                <td>0</td>
                                <td>0%</td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>Set 3</td>
                                <td>Ocak 5</td>
                                <td><span class="badge bg-success">Aktif</span></td>
                                <td>0</td>
                                <td>0</td>
                                <td>0%</td>
                                <td>-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Mobile Card View -->
                <div class="d-block d-lg-none">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="card-title mb-0">Set 1 - Ocak 1</h6>
                                <span class="badge bg-success">Aktif</span>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted">Döküm Sayısı:</small>
                                    <div class="fw-bold">0</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Prova Sayısı:</small>
                                    <div class="fw-bold">0</div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-6">
                                    <small class="text-muted">Kalite Oranı:</small>
                                    <div class="fw-bold">0%</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Son Döküm:</small>
                                    <div class="fw-bold">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="card-title mb-0">Set 2 - Ocak 3</h6>
                                <span class="badge bg-success">Aktif</span>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted">Döküm Sayısı:</small>
                                    <div class="fw-bold">0</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Prova Sayısı:</small>
                                    <div class="fw-bold">0</div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-6">
                                    <small class="text-muted">Kalite Oranı:</small>
                                    <div class="fw-bold">0%</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Son Döküm:</small>
                                    <div class="fw-bold">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="card-title mb-0">Set 3 - Ocak 5</h6>
                                <span class="badge bg-success">Aktif</span>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted">Döküm Sayısı:</small>
                                    <div class="fw-bold">0</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Prova Sayısı:</small>
                                    <div class="fw-bold">0</div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-6">
                                    <small class="text-muted">Kalite Oranı:</small>
                                    <div class="fw-bold">0%</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Son Döküm:</small>
                                    <div class="fw-bold">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Kalite Analizi -->
<div class="row mb-4">
    <div class="col-12 col-lg-6 mb-4 mb-lg-0">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    <span class="d-none d-sm-inline">Kalite Dağılımı</span>
                    <span class="d-inline d-sm-none">Kalite</span>
                </h5>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 300px;">
                    <canvas id="qualityChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    <span class="d-none d-sm-inline">Saatlik Döküm Trendi</span>
                    <span class="d-inline d-sm-none">Trend</span>
                </h5>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 300px;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ham Madde Kullanımı -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-tools me-2"></i>
                    Ham Madde Ekleme Detayları
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-lg-6 mb-4 mb-lg-0">
                        <!-- Desktop Table -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Malzeme</th>
                                        <th>Kullanım (kg)</th>
                                        <th>Başarı Oranı</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Karbon</td>
                                        <td>0</td>
                                        <td>0%</td>
                                    </tr>
                                    <tr>
                                        <td>Mangan</td>
                                        <td>0</td>
                                        <td>0%</td>
                                    </tr>
                                    <tr>
                                        <td>Silisyum</td>
                                        <td>0</td>
                                        <td>0%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Mobile Card View -->
                        <div class="d-block d-md-none">
                            <div class="card mb-2">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold">Karbon</span>
                                        <span>0 kg (0%)</span>
                                    </div>
                                </div>
                            </div>
                            <div class="card mb-2">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold">Mangan</span>
                                        <span>0 kg (0%)</span>
                                    </div>
                                </div>
                            </div>
                            <div class="card mb-2">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold">Silisyum</span>
                                        <span>0 kg (0%)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div style="position: relative; height: 200px;">
                            <canvas id="materialChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Vardiya Analizi -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-clock me-2"></i>
                    Vardiya Bazında Analiz
                </h5>
            </div>
            <div class="card-body">
                <!-- Desktop Table -->
                <div class="table-responsive d-none d-lg-block">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Vardiya</th>
                                <th>Döküm Sayısı</th>
                                <th>Prova Sayısı</th>
                                <th>Kalite Oranı</th>
                                <th>Ortalama Prova/Döküm</th>
                                <th>Ham Madde Ekleme</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-primary">A Vardiyası</span></td>
                                <td>0</td>
                                <td>0</td>
                                <td>0%</td>
                                <td>0</td>
                                <td>0</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-success">B Vardiyası</span></td>
                                <td>0</td>
                                <td>0</td>
                                <td>0%</td>
                                <td>0</td>
                                <td>0</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-warning">C Vardiyası</span></td>
                                <td>0</td>
                                <td>0</td>
                                <td>0%</td>
                                <td>0</td>
                                <td>0</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Mobile Card View -->
                <div class="d-block d-lg-none">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-primary">A Vardiyası</span>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted">Döküm Sayısı:</small>
                                    <div class="fw-bold">0</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Prova Sayısı:</small>
                                    <div class="fw-bold">0</div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-6">
                                    <small class="text-muted">Kalite Oranı:</small>
                                    <div class="fw-bold">0%</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Ham Madde:</small>
                                    <div class="fw-bold">0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-success">B Vardiyası</span>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted">Döküm Sayısı:</small>
                                    <div class="fw-bold">0</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Prova Sayısı:</small>
                                    <div class="fw-bold">0</div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-6">
                                    <small class="text-muted">Kalite Oranı:</small>
                                    <div class="fw-bold">0%</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Ham Madde:</small>
                                    <div class="fw-bold">0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-warning">C Vardiyası</span>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted">Döküm Sayısı:</small>
                                    <div class="fw-bold">0</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Prova Sayısı:</small>
                                    <div class="fw-bold">0</div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-6">
                                    <small class="text-muted">Kalite Oranı:</small>
                                    <div class="fw-bold">0%</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Ham Madde:</small>
                                    <div class="fw-bold">0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rapor Notu -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center text-muted">
                <small>
                    <i class="fas fa-info-circle me-1"></i>
                    Bu rapor {{ date('d.m.Y H:i') }} tarihinde otomatik olarak oluşturulmuştur.
                </small>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Tarih değiştir
function changeDate() {
    const dateInput = document.getElementById('reportDate') || document.getElementById('reportDateMobile');
    const date = dateInput.value;
    window.location.href = `{{ route('reports.daily') }}?date=${date}`;
}

// Rapor yazdır
function printReport() {
    window.print();
}

// Excel export
function exportExcel() {
    const date = document.getElementById('reportDate').value;
    window.open(`{{ route('reports.export') }}?type=daily&date=${date}&format=excel`, '_blank');
}

// Grafikler
document.addEventListener('DOMContentLoaded', function() {
    // Kalite dağılımı grafiği
    const qualityCtx = document.getElementById('qualityChart').getContext('2d');
    new Chart(qualityCtx, {
        type: 'doughnut',
        data: {
            labels: ['Onaylanan', 'Reddedilen', 'Bekleyen', 'Düzeltme Gerekli'],
            datasets: [{
                data: [0, 0, 0, 0],
                backgroundColor: ['#28a745', '#dc3545', '#ffc107', '#17a2b8']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    
    // Trend grafiği
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00'],
            datasets: [{
                label: 'Döküm Sayısı',
                data: [0, 0, 0, 0, 0, 0],
                borderColor: '#007bff',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    
    // Ham madde grafiği
    const materialCtx = document.getElementById('materialChart').getContext('2d');
    new Chart(materialCtx, {
        type: 'bar',
        data: {
            labels: ['Karbon', 'Mangan', 'Silisyum'],
            datasets: [{
                label: 'Kullanım (kg)',
                data: [0, 0, 0],
                backgroundColor: '#ffc107'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
});
</script>
@endpush
