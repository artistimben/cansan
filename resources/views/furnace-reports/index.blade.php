@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Başlık -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-chart-line text-primary"></i>
                        Ocak Raporları
                    </h1>
                    <p class="text-muted mb-0">Bakım, duruş, prova notları ve istatistikler</p>
                </div>
                <div>
                    <a href="{{ route('furnace-management.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Ocak Yönetimine Dön
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Rapor Kartları -->
    <div class="row">
        <!-- Ocak Detay Raporları -->
        <div class="col-lg-6 col-xl-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-fire fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Ocak Detay Raporları</h5>
                    <p class="card-text">Her ocağın detaylı raporunu görüntüleyin</p>
                </div>
                <div class="card-footer">
                    <div class="row g-2">
                        @foreach($furnaces as $furnace)
                        <div class="col-6">
                            <a href="{{ route('furnace-reports.furnace-detail', $furnace) }}" class="btn btn-outline-primary btn-sm w-100">
                                {{ $furnace->furnaceSet->name }} - {{ $furnace->name }}
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Bakım Raporu -->
        <div class="col-lg-6 col-xl-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-tools fa-3x text-warning mb-3"></i>
                    <h5 class="card-title">Bakım Raporu</h5>
                    <p class="card-text">Bakım geçmişi ve refraktör değişimleri</p>
                </div>
                <div class="card-footer">
                    <a href="{{ route('furnace-reports.maintenance') }}" class="btn btn-warning w-100">
                        <i class="fas fa-tools"></i> Bakım Raporunu Görüntüle
                    </a>
                </div>
            </div>
        </div>

        <!-- Duruş Raporu -->
        <div class="col-lg-6 col-xl-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-power-off fa-3x text-danger mb-3"></i>
                    <h5 class="card-title">Duruş Raporu</h5>
                    <p class="card-text">Duruş nedenleri ve süreleri</p>
                </div>
                <div class="card-footer">
                    <a href="{{ route('furnace-reports.shutdown') }}" class="btn btn-danger w-100">
                        <i class="fas fa-power-off"></i> Duruş Raporunu Görüntüle
                    </a>
                </div>
            </div>
        </div>

        <!-- Prova Raporu -->
        <div class="col-lg-6 col-xl-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-flask fa-3x text-info mb-3"></i>
                    <h5 class="card-title">Prova Raporu</h5>
                    <p class="card-text">Kalite kontrol provaları ve sonuçları</p>
                </div>
                <div class="card-footer">
                    <a href="{{ route('furnace-reports.samples') }}" class="btn btn-info w-100">
                        <i class="fas fa-flask"></i> Prova Raporunu Görüntüle
                    </a>
                </div>
            </div>
        </div>

        <!-- Genel İstatistikler -->
        <div class="col-lg-6 col-xl-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-chart-bar fa-3x text-success mb-3"></i>
                    <h5 class="card-title">Genel İstatistikler</h5>
                    <p class="card-text">Tüm ocakların genel performansı</p>
                </div>
                <div class="card-footer">
                    <a href="{{ route('reports.index') }}" class="btn btn-success w-100">
                        <i class="fas fa-chart-bar"></i> Genel Raporları Görüntüle
                    </a>
                </div>
            </div>
        </div>

        <!-- Durum Geçmişi -->
        <div class="col-lg-6 col-xl-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-history fa-3x text-secondary mb-3"></i>
                    <h5 class="card-title">Durum Geçmişi</h5>
                    <p class="card-text">Tüm durum değişiklikleri</p>
                </div>
                <div class="card-footer">
                    <a href="{{ route('furnace-reports.maintenance') }}?view=all" class="btn btn-secondary w-100">
                        <i class="fas fa-history"></i> Durum Geçmişini Görüntüle
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Hızlı İstatistikler -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-tachometer-alt"></i> Hızlı İstatistikler
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($furnaces as $furnace)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                                <div>
                                    <h6 class="mb-1">{{ $furnace->furnaceSet->name }} - {{ $furnace->name }}</h6>
                                    <small class="text-muted">Toplam: {{ $furnace->total_castings_count ?? 0 }} döküm</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-{{ $furnace->status === 'active' ? 'success' : ($furnace->status === 'maintenance' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($furnace->status) }}
                                    </span>
                                    <br>
                                    <small class="text-muted">{{ $furnace->current_cycle_castings ?? 0 }} mevcut döngü</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
