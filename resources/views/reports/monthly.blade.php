@extends('layouts.app')

@section('title', 'Aylık Rapor - Cansan Kalite Kontrol')

@section('header', 'Aylık Rapor')

@section('header-buttons')
    <div class="btn-group" role="group">
        <input type="month" class="form-control form-control-sm" id="reportMonth" value="{{ request('month', date('Y-m')) }}" onchange="changeMonth()">
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
                <h4 class="text-info mb-1">Aylık Kalite Kontrol Raporu</h4>
                <p class="text-muted mb-0">
                    <i class="fas fa-calendar-alt me-1"></i>
                    {{ request('month') ? \Carbon\Carbon::parse(request('month'))->format('F Y') : date('F Y') }}
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Aylık Özet İstatistikler -->
<div class="row mb-4">
    <div class="col-md-2 mb-3">
        <div class="card text-center bg-primary text-white">
            <div class="card-body">
                <i class="fas fa-fire fa-2x mb-2"></i>
                <h3 class="mb-1">0</h3>
                <small>Toplam Döküm</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-2 mb-3">
        <div class="card text-center bg-success text-white">
            <div class="card-body">
                <i class="fas fa-calendar-day fa-2x mb-2"></i>
                <h3 class="mb-1">0</h3>
                <small>Günlük Ortalama</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-2 mb-3">
        <div class="card text-center bg-info text-white">
            <div class="card-body">
                <i class="fas fa-vial fa-2x mb-2"></i>
                <h3 class="mb-1">0</h3>
                <small>Toplam Prova</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-2 mb-3">
        <div class="card text-center bg-warning text-white">
            <div class="card-body">
                <i class="fas fa-chart-line fa-2x mb-2"></i>
                <h3 class="mb-1">0</h3>
                <small>Günlük Ort. Prova</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-2 mb-3">
        <div class="card text-center bg-success text-white">
            <div class="card-body">
                <i class="fas fa-percentage fa-2x mb-2"></i>
                <h3 class="mb-1">0%</h3>
                <small>Genel Kalite Oranı</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-2 mb-3">
        <div class="card text-center bg-danger text-white">
            <div class="card-body">
                <i class="fas fa-weight-hanging fa-2x mb-2"></i>
                <h3 class="mb-1">0</h3>
                <small>Ham Madde (kg)</small>
            </div>
        </div>
    </div>
</div>

<!-- Haftalık Trend Analizi -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Haftalık Trend Analizi
                </h5>
            </div>
            <div class="card-body">
                <canvas id="monthlyTrendChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Ocak Kullanım Analizi -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-fire me-2"></i>
                    Ocak Kullanım Analizi
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Ocak</th>
                                <th>Kullanım Oranı</th>
                                <th>Toplam Döküm</th>
                                <th>Aktif Gün Ort.</th>
                                <th>Performans</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-primary">Ocak 1</span></td>
                                <td>0%</td>
                                <td>0</td>
                                <td>0</td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 0%">0%</div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-success">Ocak 3</span></td>
                                <td>0%</td>
                                <td>0</td>
                                <td>0</td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" style="width: 0%">0%</div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-info">Ocak 5</span></td>
                                <td>0%</td>
                                <td>0</td>
                                <td>0</td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar bg-info" style="width: 0%">0%</div>
                                    </div>
                                </td>
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
                    Ocak Kullanım Dağılımı
                </h5>
            </div>
            <div class="card-body">
                <canvas id="furnaceUtilizationChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Kalite Performans Analizi -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-area me-2"></i>
                    Kalite Performans Analizi
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Element</th>
                                <th>Ortalama</th>
                                <th>Min</th>
                                <th>Max</th>
                                <th>Std. Sapma</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Karbon (%)</td>
                                <td>0.000</td>
                                <td>0.000</td>
                                <td>0.000</td>
                                <td>0.000</td>
                            </tr>
                            <tr>
                                <td>Mangan (%)</td>
                                <td>0.000</td>
                                <td>0.000</td>
                                <td>0.000</td>
                                <td>0.000</td>
                            </tr>
                            <tr>
                                <td>Silisyum (%)</td>
                                <td>0.000</td>
                                <td>0.000</td>
                                <td>0.000</td>
                                <td>0.000</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <canvas id="qualityPerformanceChart" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-tools me-2"></i>
                    Ham Madde Tüketim Analizi
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
                                <th>Ortalama (kg)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Karbon</td>
                                <td>0</td>
                                <td>0.00</td>
                                <td>0.00</td>
                            </tr>
                            <tr>
                                <td>Mangan</td>
                                <td>0</td>
                                <td>0.00</td>
                                <td>0.00</td>
                            </tr>
                            <tr>
                                <td>Silisyum</td>
                                <td>0</td>
                                <td>0.00</td>
                                <td>0.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <canvas id="materialConsumptionChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Vardiya Performans Analizi -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-clock me-2"></i>
                    Vardiya Performans Analizi
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Vardiya</th>
                                        <th>Toplam Döküm</th>
                                        <th>Toplam Prova</th>
                                        <th>Kalite Oranı</th>
                                        <th>Ort. Prova/Döküm</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="badge bg-primary">A Vardiyası</span></td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0%</td>
                                        <td>0</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-success">B Vardiyası</span></td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0%</td>
                                        <td>0</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-warning">C Vardiyası</span></td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0%</td>
                                        <td>0</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <canvas id="shiftPerformanceChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Aylık Değerlendirme -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-clipboard-check me-2"></i>
                    Aylık Değerlendirme ve Öneriler
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6 class="text-success">Güçlü Yönler</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Sistem altyapısı hazır</li>
                            <li><i class="fas fa-check text-success me-2"></i>Otomatik raporlama aktif</li>
                            <li><i class="fas fa-check text-success me-2"></i>6 ocak sistemi kuruldu</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-warning">İyileştirme Alanları</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-exclamation text-warning me-2"></i>Prova veri girişi bekleniyor</li>
                            <li><i class="fas fa-exclamation text-warning me-2"></i>Kalite standartları test edilecek</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-info">Gelecek Hedefler</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-arrow-right text-info me-2"></i>Telsiz entegrasyonu testi</li>
                            <li><i class="fas fa-arrow-right text-info me-2"></i>Mobil uygulama geliştirme</li>
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
// Ay değiştir
function changeMonth() {
    const month = document.getElementById('reportMonth').value;
    window.location.href = `{{ route('reports.monthly') }}?month=${month}`;
}

// Rapor yazdır
function printReport() {
    window.print();
}

// Excel export
function exportExcel() {
    const month = document.getElementById('reportMonth').value;
    window.open(`{{ route('reports.export') }}?type=monthly&month=${month}&format=excel`, '_blank');
}

// Grafikler
document.addEventListener('DOMContentLoaded', function() {
    // Aylık trend grafiği
    const trendCtx = document.getElementById('monthlyTrendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: ['Hafta 1', 'Hafta 2', 'Hafta 3', 'Hafta 4'],
            datasets: [
                {
                    label: 'Döküm',
                    data: [0, 0, 0, 0],
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.1
                },
                {
                    label: 'Kalite Oranı (%)',
                    data: [0, 0, 0, 0],
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.1,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
    
    // Diğer grafikler...
    const furnaceCtx = document.getElementById('furnaceUtilizationChart').getContext('2d');
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
    
    // Kalite performans
    const qualityCtx = document.getElementById('qualityPerformanceChart').getContext('2d');
    new Chart(qualityCtx, {
        type: 'radar',
        data: {
            labels: ['Karbon', 'Mangan', 'Silisyum', 'Fosfor', 'Kükürt'],
            datasets: [{
                label: 'Ortalama Değerler',
                data: [0, 0, 0, 0, 0],
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    
    // Ham madde tüketimi
    const materialCtx = document.getElementById('materialConsumptionChart').getContext('2d');
    new Chart(materialCtx, {
        type: 'bar',
        data: {
            labels: ['Karbon', 'Mangan', 'Silisyum'],
            datasets: [{
                label: 'Tüketim (kg)',
                data: [0, 0, 0],
                backgroundColor: ['#ffc107', '#fd7e14', '#6f42c1']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    
    // Vardiya performansı
    const shiftCtx = document.getElementById('shiftPerformanceChart').getContext('2d');
    new Chart(shiftCtx, {
        type: 'doughnut',
        data: {
            labels: ['A Vardiyası', 'B Vardiyası', 'C Vardiyası'],
            datasets: [{
                data: [0, 0, 0],
                backgroundColor: ['#007bff', '#28a745', '#ffc107']
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
