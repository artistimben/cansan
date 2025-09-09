@extends('layouts.app')

@section('title', 'Haftalık Rapor - Cansan Kalite Kontrol')

@section('header', 'Haftalık Rapor')

@section('header-buttons')
    <div class="btn-group" role="group">
        <input type="week" class="form-control form-control-sm" id="reportWeek" value="{{ request('week', date('Y-\WW')) }}" onchange="changeWeek()">
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
@endsection

@section('content')
<!-- Rapor Başlığı -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="mb-1">Cansan Çelik Üretim Fabrikası</h3>
                <h4 class="text-success mb-1">Haftalık Kalite Kontrol Raporu</h4>
                <p class="text-muted mb-0">
                    <i class="fas fa-calendar-week me-1"></i>
                    Hafta: {{ request('week', date('Y-\WW')) }}
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Haftalık Özet -->
<div class="row mb-4">
    <div class="col-md-2 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-fire text-primary fa-2x mb-2"></i>
                <h4 class="text-primary mb-1">0</h4>
                <small class="text-muted">Toplam Döküm</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-2 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-chart-line text-info fa-2x mb-2"></i>
                <h4 class="text-info mb-1">0</h4>
                <small class="text-muted">Günlük Ortalama</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-2 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-vial text-success fa-2x mb-2"></i>
                <h4 class="text-success mb-1">0</h4>
                <small class="text-muted">Toplam Prova</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-2 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-percentage text-warning fa-2x mb-2"></i>
                <h4 class="text-warning mb-1">0</h4>
                <small class="text-muted">Döküm Başına</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-2 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                <h4 class="text-success mb-1">0%</h4>
                <small class="text-muted">Kalite Oranı</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-2 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-tools text-danger fa-2x mb-2"></i>
                <h4 class="text-danger mb-1">0%</h4>
                <small class="text-muted">Düzeltme Oranı</small>
            </div>
        </div>
    </div>
</div>

<!-- Günlük Trend Analizi -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Günlük Trend Analizi
                </h5>
            </div>
            <div class="card-body">
                <canvas id="weeklyTrendChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Ocak Performans Karşılaştırması -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-fire me-2"></i>
                    Ocak Performans Karşılaştırması
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Ocak</th>
                                <th>Döküm</th>
                                <th>Prova</th>
                                <th>Kalite Oranı</th>
                                <th>Ortalama Prova/Döküm</th>
                                <th>Performans</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-primary">Ocak 1</span></td>
                                <td>0</td>
                                <td>0</td>
                                <td>0%</td>
                                <td>0</td>
                                <td><div class="progress"><div class="progress-bar" style="width: 0%"></div></div></td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-success">Ocak 3</span></td>
                                <td>0</td>
                                <td>0</td>
                                <td>0%</td>
                                <td>0</td>
                                <td><div class="progress"><div class="progress-bar bg-success" style="width: 0%"></div></div></td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-info">Ocak 5</span></td>
                                <td>0</td>
                                <td>0</td>
                                <td>0%</td>
                                <td>0</td>
                                <td><div class="progress"><div class="progress-bar bg-info" style="width: 0%"></div></div></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Ocak Dağılımı
                </h5>
            </div>
            <div class="card-body">
                <canvas id="furnaceDistributionChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Kalite Trend Analizi -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-area me-2"></i>
                    Kalite Trend Analizi
                </h5>
            </div>
            <div class="card-body">
                <canvas id="qualityTrendChart" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-tools me-2"></i>
                    En Çok Kullanılan Ham Maddeler
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Malzeme</th>
                                <th>Kullanım</th>
                                <th>Toplam (kg)</th>
                                <th>Başarı</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Karbon</td>
                                <td>0</td>
                                <td>0 kg</td>
                                <td><span class="badge bg-success">0%</span></td>
                            </tr>
                            <tr>
                                <td>Mangan</td>
                                <td>0</td>
                                <td>0 kg</td>
                                <td><span class="badge bg-success">0%</span></td>
                            </tr>
                            <tr>
                                <td>Silisyum</td>
                                <td>0</td>
                                <td>0 kg</td>
                                <td><span class="badge bg-success">0%</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <canvas id="materialUsageChart" height="150"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Haftalık Notlar -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-sticky-note me-2"></i>
                    Haftalık Değerlendirme
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-success">Pozitif Gelişmeler</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Veri girişi başladığında otomatik analiz edilecek</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-warning">İyileştirme Alanları</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-exclamation text-warning me-2"></i>İlk prova verilerinin girilmesi bekleniyor</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Hafta değiştir
function changeWeek() {
    const week = document.getElementById('reportWeek').value;
    window.location.href = `{{ route('reports.weekly') }}?week=${week}`;
}

// Rapor yazdır
function printReport() {
    window.print();
}

// Excel export
function exportExcel() {
    const week = document.getElementById('reportWeek').value;
    window.open(`{{ route('reports.export') }}?type=weekly&week=${week}&format=excel`, '_blank');
}

// Grafikler
document.addEventListener('DOMContentLoaded', function() {
    // Haftalık trend grafiği
    const trendCtx = document.getElementById('weeklyTrendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: ['Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar'],
            datasets: [
                {
                    label: 'Döküm',
                    data: [0, 0, 0, 0, 0, 0, 0],
                    borderColor: '#007bff',
                    tension: 0.1
                },
                {
                    label: 'Prova',
                    data: [0, 0, 0, 0, 0, 0, 0],
                    borderColor: '#28a745',
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    
    // Ocak dağılımı
    const furnaceCtx = document.getElementById('furnaceDistributionChart').getContext('2d');
    new Chart(furnaceCtx, {
        type: 'doughnut',
        data: {
            labels: ['Ocak 1', 'Ocak 3', 'Ocak 5'],
            datasets: [{
                data: [0, 0, 0],
                backgroundColor: ['#007bff', '#28a745', '#17a2b8']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    
    // Kalite trend
    const qualityCtx = document.getElementById('qualityTrendChart').getContext('2d');
    new Chart(qualityCtx, {
        type: 'area',
        data: {
            labels: ['Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar'],
            datasets: [{
                label: 'Kalite Oranı (%)',
                data: [0, 0, 0, 0, 0, 0, 0],
                backgroundColor: 'rgba(40, 167, 69, 0.2)',
                borderColor: '#28a745',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    
    // Ham madde kullanımı
    const materialCtx = document.getElementById('materialUsageChart').getContext('2d');
    new Chart(materialCtx, {
        type: 'horizontalBar',
        data: {
            labels: ['Karbon', 'Mangan', 'Silisyum'],
            datasets: [{
                label: 'Kullanım (kg)',
                data: [0, 0, 0],
                backgroundColor: ['#ffc107', '#fd7e14', '#6f42c1']
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
