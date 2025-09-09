@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Başlık -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-flask text-info"></i>
                        Prova Raporu
                    </h1>
                    <p class="text-muted mb-0">Kalite kontrol provaları ve sonuçları</p>
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
                    <form method="GET" action="{{ route('furnace-reports.samples') }}">
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
                                <button type="submit" class="btn btn-info w-100">
                                    <i class="fas fa-filter"></i> Filtrele
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Prova Kayıtları -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-flask"></i> Prova Kayıtları
                        <span class="badge bg-info ms-2">{{ $samples->count() }} kayıt</span>
                    </h5>
                </div>
                <div class="card-body">
                    @if($samples->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tarih</th>
                                        <th>Ocak</th>
                                        <th>Döküm No</th>
                                        <th>Prova No</th>
                                        <th>Kalite Durumu</th>
                                        <th>Karbon %</th>
                                        <th>Manganez %</th>
                                        <th>Fosfor %</th>
                                        <th>Kükürt %</th>
                                        <th>Notlar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($samples as $sample)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($sample->sample_time)->format('d.m.Y H:i') }}</td>
                                        <td>
                                            <strong>{{ $sample->casting->furnace->furnaceSet->name }} - {{ $sample->casting->furnace->name }}</strong>
                                        </td>
                                        <td>{{ $sample->casting->casting_number }}</td>
                                        <td>{{ $sample->sample_number }}</td>
                                        <td>
                                            @if($sample->quality_status === 'approved')
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check"></i> Onaylandı
                                                </span>
                                            @elseif($sample->quality_status === 'rejected')
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times"></i> Reddedildi
                                                </span>
                                            @elseif($sample->quality_status === 'needs_adjustment')
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-exclamation-triangle"></i> Ayarlama Gerekli
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-clock"></i> Beklemede
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $sample->carbon_content ?? '-' }}%</td>
                                        <td>{{ $sample->manganese_content ?? '-' }}%</td>
                                        <td>{{ $sample->phosphorus_percentage ?? '-' }}%</td>
                                        <td>{{ $sample->sulfur_percentage ?? '-' }}%</td>
                                        <td>
                                            @if($sample->notes)
                                                <span class="text-truncate d-inline-block" style="max-width: 150px;" title="{{ $sample->notes }}">
                                                    {{ $sample->notes }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-flask fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Prova kaydı bulunmuyor</h5>
                            <p class="text-muted">Seçilen tarih aralığında prova kaydı yok.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
