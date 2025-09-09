@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Başlık -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-fire text-primary"></i>
                        {{ $furnace->furnaceSet->name }} - {{ $furnace->name }} Detay Raporu
                    </h1>
                    <p class="text-muted mb-0">Ocak detaylı raporu ve istatistikleri</p>
                </div>
                <div>
                    <a href="{{ route('furnace-reports.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Raporlara Dön
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarih Filtresi -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('furnace-reports.furnace-detail', $furnace) }}">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Başlangıç Tarihi</label>
                                <input type="date" class="form-control" name="date_from" value="{{ $dateFrom }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Bitiş Tarihi</label>
                                <input type="date" class="form-control" name="date_to" value="{{ $dateTo }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter"></i> Filtrele
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- İstatistikler -->
    <div class="row mb-4">
        <!-- Döküm İstatistikleri -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-fire fa-2x text-primary mb-2"></i>
                    <h5 class="card-title">{{ $castingStats['total'] }}</h5>
                    <p class="card-text">Toplam Döküm</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <h5 class="card-title">{{ $castingStats['completed'] }}</h5>
                    <p class="card-text">Tamamlanan</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-play-circle fa-2x text-info mb-2"></i>
                    <h5 class="card-title">{{ $castingStats['active'] }}</h5>
                    <p class="card-text">Aktif</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                    <h5 class="card-title">{{ $castingStats['cancelled'] }}</h5>
                    <p class="card-text">İptal Edilen</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Prova İstatistikleri -->
    <div class="row mb-4">
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-flask fa-2x text-info mb-2"></i>
                    <h5 class="card-title">{{ $sampleStats['total'] }}</h5>
                    <p class="card-text">Toplam Prova</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-check fa-2x text-success mb-2"></i>
                    <h5 class="card-title">{{ $sampleStats['approved'] }}</h5>
                    <p class="card-text">Onaylanan</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-times fa-2x text-danger mb-2"></i>
                    <h5 class="card-title">{{ $sampleStats['rejected'] }}</h5>
                    <p class="card-text">Reddedilen</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-percentage fa-2x text-primary mb-2"></i>
                    <h5 class="card-title">%{{ $sampleStats['approval_rate'] }}</h5>
                    <p class="card-text">Onay Oranı</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Durum Geçmişi -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history"></i> Durum Geçmişi
                        <span class="badge bg-primary ms-2">{{ $statusLogs->count() }} kayıt</span>
                    </h5>
                </div>
                <div class="card-body">
                    @if($statusLogs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tarih</th>
                                        <th>Durum</th>
                                        <th>Önceki Durum</th>
                                        <th>Neden</th>
                                        <th>Operatör</th>
                                        <th>Notlar</th>
                                        <th>Döküm Sayısı</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($statusLogs as $log)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($log->status_changed_at)->format('d.m.Y H:i') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $log->status === 'active' ? 'success' : ($log->status === 'maintenance' ? 'warning' : ($log->status === 'shutdown' ? 'danger' : 'secondary')) }}">
                                                {{ ucfirst($log->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($log->previous_status)
                                                <span class="badge bg-light text-dark">{{ ucfirst($log->previous_status) }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $log->reason ?? '-' }}</td>
                                        <td>{{ $log->operator_name ?? '-' }}</td>
                                        <td>
                                            @if($log->notes)
                                                <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $log->notes }}">
                                                    {{ $log->notes }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $log->castings_count_at_change }}
                                            @if($log->count_reset)
                                                <i class="fas fa-redo text-warning ms-1" title="Sayaç sıfırlandı"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Durum geçmişi bulunmuyor</h5>
                            <p class="text-muted">Seçilen tarih aralığında durum değişikliği kaydı yok.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
