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
                        <span class="d-none d-sm-inline">Döküm Yönetimi</span>
                        <span class="d-inline d-sm-none">Dökümler</span>
                    </h1>
                    <p class="text-muted mb-0 d-none d-md-block">Ocak dökümlerini yönetin ve takip edin</p>
                    <p class="text-muted mb-0 d-block d-md-none">Döküm takibi</p>
                </div>
                <div>
                    <a href="{{ route('castings.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> 
                        <span class="d-none d-sm-inline">Yeni Döküm Başlat</span>
                        <span class="d-inline d-sm-none">Yeni</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtreler -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-filter"></i> 
                        <span class="d-none d-sm-inline">Filtreler</span>
                        <span class="d-inline d-sm-none">Filtre</span>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('castings.index') }}">
                        <div class="row">
                            <div class="col-12 col-sm-6 col-md-3 mb-3">
                                <label class="form-label">Ocak</label>
                                <select name="furnace_id" class="form-select">
                                    <option value="">Tüm Ocaklar</option>
                                    @foreach($furnaces as $furnace)
                                        <option value="{{ $furnace->id }}" 
                                            {{ request('furnace_id') == $furnace->id ? 'selected' : '' }}>
                                            {{ $furnace->furnaceSet->name }} - {{ $furnace->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-sm-3 col-md-2 mb-3">
                                <label class="form-label">Durum</label>
                                <select name="status" class="form-select">
                                    <option value="">Tüm Durumlar</option>
                                    @foreach($statuses as $key => $status)
                                        <option value="{{ $key }}" 
                                            {{ request('status') == $key ? 'selected' : '' }}>
                                            {{ $status }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-sm-3 col-md-2 mb-3">
                                <label class="form-label">Vardiya</label>
                                <select name="shift" class="form-select">
                                    <option value="">Tüm Vardiyalar</option>
                                    @foreach($shifts as $shift)
                                        <option value="{{ $shift }}" 
                                            {{ request('shift') == $shift ? 'selected' : '' }}>
                                            {{ $shift }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-sm-6 col-md-2 mb-3">
                                <label class="form-label">Başlangıç</label>
                                <input type="date" name="date_from" class="form-control" 
                                       value="{{ request('date_from') }}">
                            </div>
                            <div class="col-6 col-sm-6 col-md-2 mb-3">
                                <label class="form-label">Bitiş</label>
                                <input type="date" name="date_to" class="form-control" 
                                       value="{{ request('date_to') }}">
                            </div>
                            <div class="col-12 col-md-1 d-flex align-items-end justify-content-center justify-content-md-start">
                                <button type="submit" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-search"></i>
                                    <span class="d-none d-sm-inline ms-1">Ara</span>
                                </button>
                                <a href="{{ route('castings.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                    <span class="d-none d-sm-inline ms-1">Temizle</span>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Döküm Listesi -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list"></i> Döküm Listesi
                        <span class="badge bg-primary ms-2">{{ $castings->total() }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($castings->count() > 0)
                        <!-- Desktop Table -->
                        <div class="table-responsive d-none d-lg-block">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Döküm No</th>
                                        <th>Ocak</th>
                                        <th>Tarih/Saat</th>
                                        <th>Vardiya</th>
                                        <th>Operatör</th>
                                        <th>Durum</th>
                                        <th>Prova Sayısı</th>
                                        <th>Süre</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($castings as $casting)
                                        <tr>
                                            <td>
                                                <strong class="text-primary">
                                                    {{ $casting->casting_number }}
                                                </strong>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="furnace-indicator bg-{{ $casting->furnace->status === 'active' ? 'success' : 'secondary' }} me-2"></div>
                                                    <div>
                                                        <div class="fw-bold">{{ $casting->furnace->name }}</div>
                                                        <small class="text-muted">{{ $casting->furnace->furnaceSet->name }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>{{ $casting->casting_date->format('d.m.Y') }}</div>
                                                <small class="text-muted">{{ $casting->casting_date->format('H:i') }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $casting->shift === 'Gündüz' ? 'warning' : 'info' }}">
                                                    {{ $casting->shift }}
                                                </span>
                                            </td>
                                            <td>{{ $casting->operator_name }}</td>
                                            <td>
                                                <span class="badge 
                                                    @if($casting->status === 'active') bg-success
                                                    @elseif($casting->status === 'completed') bg-primary
                                                    @elseif($casting->status === 'cancelled') bg-danger
                                                    @else bg-secondary
                                                    @endif">
                                                    @if($casting->status === 'active') Aktif
                                                    @elseif($casting->status === 'completed') Tamamlandı
                                                    @elseif($casting->status === 'cancelled') İptal
                                                    @else {{ $casting->status }}
                                                    @endif
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-info me-1">{{ $casting->samples->count() }}</span>
                                                    @if($casting->samples->where('quality_status', 'approved')->count() > 0)
                                                        <span class="badge bg-success me-1">
                                                            {{ $casting->samples->where('quality_status', 'approved')->count() }} ✓
                                                        </span>
                                                    @endif
                                                    @if($casting->samples->where('quality_status', 'pending')->count() > 0)
                                                        <span class="badge bg-warning me-1">
                                                            {{ $casting->samples->where('quality_status', 'pending')->count() }} ?
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($casting->completed_at)
                                                    {{ $casting->started_at->diffInMinutes($casting->completed_at) }} dk
                                                @else
                                                    <span class="text-success">
                                                        {{ $casting->started_at->diffInMinutes(now()) }} dk
                                                        <i class="fas fa-clock fa-spin"></i>
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('castings.show', $casting) }}" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="Detayları Görüntüle">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($casting->status === 'active')
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-success complete-casting" 
                                                                data-casting-id="{{ $casting->id }}"
                                                                title="Dökümü Tamamla">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-danger cancel-casting" 
                                                                data-casting-id="{{ $casting->id }}"
                                                                title="Dökümü İptal Et">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    @endif
                                                    <a href="{{ route('samples.create', ['casting_id' => $casting->id]) }}" 
                                                       class="btn btn-sm btn-outline-info" 
                                                       title="Prova Ekle">
                                                        <i class="fas fa-plus"></i> Prova
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Mobile Card View -->
                        <div class="d-block d-lg-none p-3">
                            @foreach($castings as $casting)
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title mb-0">
                                                <strong class="text-primary">#{{ $casting->casting_number }}</strong>
                                            </h6>
                                            <span class="badge 
                                                @if($casting->status === 'active') bg-success
                                                @elseif($casting->status === 'completed') bg-primary
                                                @elseif($casting->status === 'cancelled') bg-danger
                                                @else bg-secondary
                                                @endif">
                                                @if($casting->status === 'active') Aktif
                                                @elseif($casting->status === 'completed') Tamamlandı
                                                @elseif($casting->status === 'cancelled') İptal
                                                @else {{ $casting->status }}
                                                @endif
                                            </span>
                                        </div>
                                        
                                        <div class="row mb-2">
                                            <div class="col-6">
                                                <small class="text-muted">Ocak:</small>
                                                <div class="d-flex align-items-center">
                                                    <div class="furnace-indicator bg-{{ $casting->furnace->status === 'active' ? 'success' : 'secondary' }} me-2"></div>
                                                    <div>
                                                        <div class="fw-bold">{{ $casting->furnace->name }}</div>
                                                        <small class="text-muted">{{ $casting->furnace->furnaceSet->name }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Vardiya:</small>
                                                <div>
                                                    <span class="badge bg-{{ $casting->shift === 'Gündüz' ? 'warning' : 'info' }}">
                                                        {{ $casting->shift }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-2">
                                            <div class="col-6">
                                                <small class="text-muted">Tarih:</small>
                                                <div>{{ $casting->casting_date->format('d.m.Y') }}</div>
                                                <small class="text-muted">{{ $casting->casting_date->format('H:i') }}</small>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Operatör:</small>
                                                <div>{{ $casting->operator_name }}</div>
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <small class="text-muted">Prova Sayısı:</small>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-info me-1">{{ $casting->samples->count() }}</span>
                                                    @if($casting->samples->where('quality_status', 'approved')->count() > 0)
                                                        <span class="badge bg-success me-1">
                                                            {{ $casting->samples->where('quality_status', 'approved')->count() }} ✓
                                                        </span>
                                                    @endif
                                                    @if($casting->samples->where('quality_status', 'pending')->count() > 0)
                                                        <span class="badge bg-warning me-1">
                                                            {{ $casting->samples->where('quality_status', 'pending')->count() }} ?
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Süre:</small>
                                                <div>
                                                    @if($casting->completed_at)
                                                        {{ $casting->started_at->diffInMinutes($casting->completed_at) }} dk
                                                    @else
                                                        <span class="text-success">
                                                            {{ $casting->started_at->diffInMinutes(now()) }} dk
                                                            <i class="fas fa-clock fa-spin"></i>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('castings.show', $casting) }}" 
                                               class="btn btn-sm btn-outline-primary flex-fill">
                                                <i class="fas fa-eye"></i> Detay
                                            </a>
                                            @if($casting->status === 'active')
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-success complete-casting flex-fill" 
                                                        data-casting-id="{{ $casting->id }}">
                                                    <i class="fas fa-check"></i> Tamamla
                                                </button>
                                            @endif
                                            <a href="{{ route('samples.create', ['casting_id' => $casting->id]) }}" 
                                               class="btn btn-sm btn-outline-info flex-fill">
                                                <i class="fas fa-plus"></i> Prova
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Pagination -->
                        <div class="card-footer">
                            {{ $castings->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-fire fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Henüz döküm bulunmuyor</h5>
                            <p class="text-muted">İlk dökümü başlatmak için "Yeni Döküm Başlat" butonuna tıklayın</p>
                            <a href="{{ route('castings.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Yeni Döküm Başlat
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.furnace-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}
</style>

<!-- Modal'lar castings/show sayfasında kullanılıyor -->

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Döküm tamamlama - sayfayı yönlendir
    $('.complete-casting').click(function() {
        const castingId = $(this).data('casting-id');
        if (confirm('Bu dökümü tamamlamak istediğinizden emin misiniz?')) {
            window.location.href = `/castings/${castingId}`;
        }
    });

    // Döküm iptal etme - sayfayı yönlendir
    $('.cancel-casting').click(function() {
        const castingId = $(this).data('casting-id');
        if (confirm('Bu dökümü iptal etmek istediğinizden emin misiniz?')) {
            window.location.href = `/castings/${castingId}`;
        }
    });
});
</script>
@endpush
