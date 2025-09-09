@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Başlık -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-power-off text-danger"></i>
                        Duruş Raporu
                    </h1>
                    <p class="text-muted mb-0">Duruş nedenleri ve süreleri</p>
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
                    <form method="GET" action="{{ route('furnace-reports.shutdown') }}">
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
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="fas fa-filter"></i> Filtrele
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Duruş Kayıtları -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-power-off"></i> Duruş Kayıtları
                        <span class="badge bg-danger ms-2">{{ $shutdownLogs->count() }} kayıt</span>
                    </h5>
                </div>
                <div class="card-body">
                    @if($shutdownLogs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tarih</th>
                                        <th>Ocak</th>
                                        <th>Duruş Nedeni</th>
                                        <th>Operatör</th>
                                        <th>Notlar</th>
                                        <th>Döküm Sayısı</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($shutdownLogs as $log)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($log->status_changed_at)->format('d.m.Y H:i') }}</td>
                                        <td>
                                            <strong>{{ $log->furnace->furnaceSet->name }} - {{ $log->furnace->name }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger">
                                                <i class="fas fa-power-off"></i> {{ $log->reason ?? 'Duruş' }}
                                            </span>
                                        </td>
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
                                        <td>{{ $log->castings_count_at_change }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-power-off fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Duruş kaydı bulunmuyor</h5>
                            <p class="text-muted">Seçilen tarih aralığında duruş kaydı yok.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
