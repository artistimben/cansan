@extends('layouts.app')

@section('title', 'Provalar - Cansan Kalite Kontrol')

@section('header', 'Prova Yönetimi')

@section('header-buttons')
    <div class="btn-group d-none d-md-flex" role="group">
        <a href="{{ route('samples.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>
            Yeni Prova
        </a>
        <a href="{{ route('samples.pending') }}" class="btn btn-warning btn-sm">
            <i class="fas fa-hourglass-half me-1"></i>
            Bekleyen Provalar
        </a>
        <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#filterModal">
            <i class="fas fa-filter me-1"></i>
            Filtrele
        </button>
        <button type="button" class="btn btn-outline-success btn-sm" onclick="exportSamples()">
            <i class="fas fa-download me-1"></i>
            Export
        </button>
    </div>
    
    <!-- Mobile buttons -->
    <div class="d-flex d-md-none gap-2">
        <a href="{{ route('samples.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i>
        </a>
        <a href="{{ route('samples.pending') }}" class="btn btn-warning btn-sm">
            <i class="fas fa-hourglass-half"></i>
        </a>
        <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#filterModal">
            <i class="fas fa-filter"></i>
        </button>
    </div>
@endsection

@section('content')
<!-- Hızlı İstatistikler -->
<div class="row mb-4">
    <div class="col-6 col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-vial text-primary fa-2x mb-2"></i>
                <h4 class="text-primary mb-1">{{ $samples->total() }}</h4>
                <small class="text-muted d-none d-sm-block">Toplam Prova</small>
                <small class="text-muted d-block d-sm-none">Toplam</small>
            </div>
        </div>
    </div>
    
    <div class="col-6 col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                <h4 class="text-success mb-1">{{ $samples->where('quality_status', 'approved')->count() }}</h4>
                <small class="text-muted d-none d-sm-block">Onaylanan</small>
                <small class="text-muted d-block d-sm-none">Onay</small>
            </div>
        </div>
    </div>
    
    <div class="col-6 col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-hourglass-half text-warning fa-2x mb-2"></i>
                <h4 class="text-warning mb-1">{{ $samples->where('quality_status', 'pending')->count() }}</h4>
                <small class="text-muted d-none d-sm-block">Bekleyen</small>
                <small class="text-muted d-block d-sm-none">Bekle</small>
            </div>
        </div>
    </div>
    
    <div class="col-6 col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-times-circle text-danger fa-2x mb-2"></i>
                <h4 class="text-danger mb-1">{{ $samples->where('quality_status', 'rejected')->count() }}</h4>
                <small class="text-muted d-none d-sm-block">Reddedilen</small>
                <small class="text-muted d-block d-sm-none">Red</small>
            </div>
        </div>
    </div>
</div>

<!-- Filtre Bilgisi -->
@if(request()->hasAny(['furnace_id', 'quality_status', 'date_from', 'date_to']))
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-filter me-2"></i>
                    Filtre aktif: 
                    @if(request('furnace_id'))
                        <span class="badge bg-primary me-1">Ocak</span>
                    @endif
                    @if(request('quality_status'))
                        <span class="badge bg-secondary me-1">Kalite Durumu</span>
                    @endif
                    @if(request('date_from') || request('date_to'))
                        <span class="badge bg-info me-1">Tarih Aralığı</span>
                    @endif
                </div>
                <a href="{{ route('samples.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>
                    Filtreyi Temizle
                </a>
            </div>
        </div>
    </div>
@endif

<!-- Prova Listesi -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Prova Listesi
                </h5>
                <small class="text-muted">{{ $samples->total() }} prova bulundu</small>
            </div>
            <div class="card-body">
                @if($samples->count() > 0)
                    <!-- Desktop Table -->
                    <div class="table-responsive d-none d-lg-block">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Prova #</th>
                                    <th>Ocak</th>
                                    <th>Döküm #</th>
                                    <th>Tarih</th>
                                    <th>Analiz Eden</th>
                                    <th>Kalite Durumu</th>
                                    <th>Telsiz</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($samples as $sample)
                                    <tr>
                                        <td>
                                            <strong>#{{ $sample->sample_number }}</strong>
                                            @if($sample->sample_type !== 'regular')
                                                <br><small class="text-muted">{{ ucfirst($sample->sample_type) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">
                                                {{ $sample->casting->furnace->name ?? 'N/A' }}
                                            </span>
                                            <br><small class="text-muted">{{ $sample->casting->furnace->furnaceSet->name ?? 'N/A' }}</small>
                                        </td>
                                        <td>
                                            <strong>#{{ $sample->casting->casting_number }}</strong>
                                            <br><small class="text-muted">{{ $sample->casting->shift ?? 'N/A' }} Vardiyası</small>
                                        </td>
                                        <td>
                                            {{ $sample->sample_time->format('d.m.Y') }}
                                            <br><small class="text-muted">{{ $sample->sample_time->format('H:i') }}</small>
                                        </td>
                                        <td>{{ $sample->analyzed_by }}</td>
                                        <td>
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
                                        </td>
                                        <td>
                                            @if($sample->reported_via_radio)
                                                <i class="fas fa-radio text-success" title="Telsizle bildirildi"></i>
                                                <br><small class="text-muted">{{ $sample->reported_at->format('H:i') }}</small>
                                            @else
                                                <i class="fas fa-radio text-muted" title="Telsizle bildirilmedi"></i>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('samples.show', $sample) }}" class="btn btn-outline-info btn-sm" title="Detay">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('samples.edit', $sample) }}" class="btn btn-outline-primary btn-sm" title="Düzenle">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if($sample->quality_status === 'pending')
                                                    <button type="button" class="btn btn-outline-success btn-sm" onclick="updateQualityStatus({{ $sample->id }}, 'approved')" title="Onayla">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="updateQualityStatus({{ $sample->id }}, 'rejected')" title="Reddet">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @endif
                                                @if(!$sample->reported_via_radio)
                                                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="recordRadioReport({{ $sample->id }})" title="Telsiz Bildirimi">
                                                        <i class="fas fa-radio"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Mobile Card View -->
                    <div class="d-block d-lg-none">
                        @foreach($samples as $sample)
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title mb-0">
                                            <strong>#{{ $sample->sample_number }}</strong>
                                            @if($sample->sample_type !== 'regular')
                                                <br><small class="text-muted">{{ ucfirst($sample->sample_type) }}</small>
                                            @endif
                                        </h6>
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
                                    
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <small class="text-muted">Ocak:</small>
                                            <div>
                                                <span class="badge bg-primary">
                                                    {{ $sample->casting->furnace->name ?? 'N/A' }}
                                                </span>
                                                <br><small class="text-muted">{{ $sample->casting->furnace->furnaceSet->name ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Döküm:</small>
                                            <div>
                                                <strong>#{{ $sample->casting->casting_number }}</strong>
                                                <br><small class="text-muted">{{ $sample->casting->shift ?? 'N/A' }} Vardiyası</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <small class="text-muted">Tarih:</small>
                                            <div>
                                                {{ $sample->sample_time->format('d.m.Y') }}
                                                <br><small class="text-muted">{{ $sample->sample_time->format('H:i') }}</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Analiz Eden:</small>
                                            <div>{{ $sample->analyzed_by }}</div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <small class="text-muted">Telsiz:</small>
                                            <div>
                                                @if($sample->reported_via_radio)
                                                    <i class="fas fa-radio text-success" title="Telsizle bildirildi"></i>
                                                    <br><small class="text-muted">{{ $sample->reported_at->format('H:i') }}</small>
                                                @else
                                                    <i class="fas fa-radio text-muted" title="Telsizle bildirilmedi"></i>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('samples.show', $sample) }}" class="btn btn-sm btn-outline-info flex-fill">
                                            <i class="fas fa-eye"></i> Detay
                                        </a>
                                        <a href="{{ route('samples.edit', $sample) }}" class="btn btn-sm btn-outline-primary flex-fill">
                                            <i class="fas fa-edit"></i> Düzenle
                                        </a>
                                        @if($sample->quality_status === 'pending')
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="updateQualityStatus({{ $sample->id }}, 'approved')" title="Onayla">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="updateQualityStatus({{ $sample->id }}, 'rejected')" title="Reddet">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                        @if(!$sample->reported_via_radio)
                                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="recordRadioReport({{ $sample->id }})" title="Telsiz Bildirimi">
                                                <i class="fas fa-radio"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $samples->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-vial fa-3x mb-3"></i>
                        <h5>Prova bulunamadı</h5>
                        <p>Henüz hiç prova kaydı yok veya filtre kriterlerinize uygun prova bulunamadı.</p>
                        <a href="{{ route('samples.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            İlk Provayı Ekle
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Filtre Modal -->
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Prova Filtreleme</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="GET" action="{{ route('samples.index') }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="furnace_id" class="form-label">Ocak</label>
                        <select class="form-select" name="furnace_id" id="furnace_id">
                            <option value="">Tüm Ocaklar</option>
                            @foreach($furnaces as $furnace)
                                <option value="{{ $furnace->id }}" {{ request('furnace_id') == $furnace->id ? 'selected' : '' }}>
                                    {{ $furnace->name }} ({{ $furnace->furnaceSet->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="quality_status" class="form-label">Kalite Durumu</label>
                        <select class="form-select" name="quality_status" id="quality_status">
                            <option value="">Tüm Durumlar</option>
                            @foreach($qualityStatuses as $status => $label)
                                <option value="{{ $status }}" {{ request('quality_status') == $status ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_from" class="form-label">Başlangıç Tarihi</label>
                                <input type="date" class="form-control" name="date_from" id="date_from" value="{{ request('date_from') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_to" class="form-label">Bitiş Tarihi</label>
                                <input type="date" class="form-control" name="date_to" id="date_to" value="{{ request('date_to') }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <a href="{{ route('samples.index') }}" class="btn btn-outline-warning">Temizle</a>
                    <button type="submit" class="btn btn-primary">Filtrele</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Kalite Durumu Modal -->
<div class="modal fade" id="qualityStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kalite Durumu Güncelle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="qualityStatusForm">
                <div class="modal-body">
                    <input type="hidden" id="sample_id" name="sample_id">
                    <input type="hidden" id="quality_status" name="quality_status">
                    
                    <div class="mb-3">
                        <label for="quality_notes" class="form-label">Notlar</label>
                        <textarea class="form-control" id="quality_notes" name="quality_notes" rows="3" placeholder="Kalite durumu hakkında notlarınız..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Güncelle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Telsiz Bildirimi Modal -->
<div class="modal fade" id="radioReportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Telsiz Bildirimi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="radioReportForm">
                <div class="modal-body">
                    <input type="hidden" id="radio_sample_id" name="sample_id">
                    
                    <div class="mb-3">
                        <label for="reported_by" class="form-label">Bildiren Kişi</label>
                        <input type="text" class="form-control" id="reported_by" name="reported_by" required placeholder="Telsiz operatörü adı">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-radio me-1"></i>
                        Telsizle Bildir
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Kalite durumu güncelle
function updateQualityStatus(sampleId, status) {
    document.getElementById('sample_id').value = sampleId;
    document.getElementById('quality_status').value = status;
    
    const modal = new bootstrap.Modal(document.getElementById('qualityStatusModal'));
    modal.show();
}

// Kalite durumu form submit
document.getElementById('qualityStatusForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const sampleId = formData.get('sample_id');
    
    fetch(`/samples/${sampleId}/update-quality-status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            quality_status: formData.get('quality_status'),
            quality_notes: formData.get('quality_notes')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            location.reload();
        } else {
            showToast('Hata oluştu', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Hata oluştu', 'error');
    });
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('qualityStatusModal'));
    modal.hide();
});

// Telsiz bildirimi
function recordRadioReport(sampleId) {
    document.getElementById('radio_sample_id').value = sampleId;
    
    const modal = new bootstrap.Modal(document.getElementById('radioReportModal'));
    modal.show();
}

// Telsiz bildirimi form submit
document.getElementById('radioReportForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const sampleId = formData.get('sample_id');
    
    fetch(`/samples/${sampleId}/record-radio-report`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            reported_by: formData.get('reported_by')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            location.reload();
        } else {
            showToast('Hata oluştu', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Hata oluştu', 'error');
    });
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('radioReportModal'));
    modal.hide();
});

// Export fonksiyonu
function exportSamples() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'excel');
    
    window.open(`{{ route('samples.index') }}?${params.toString()}`, '_blank');
    showToast('Provalar export ediliyor...', 'info');
}

// Klavye kısayolları
document.addEventListener('keydown', function(e) {
    // Ctrl + N: Yeni prova
    if (e.ctrlKey && e.key === 'n') {
        e.preventDefault();
        window.location.href = '{{ route("samples.create") }}';
    }
    
    // Ctrl + F: Filtre
    if (e.ctrlKey && e.key === 'f') {
        e.preventDefault();
        const modal = new bootstrap.Modal(document.getElementById('filterModal'));
        modal.show();
    }
});
</script>
@endpush
