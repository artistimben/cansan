@extends('layouts.app')

@section('title', 'Ocaklar - Cansan Kalite Kontrol')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="furnace-id" content="{{ $furnaces->first()->id ?? '' }}">
@endpush

@section('header', 'Ocak Yönetimi')

@section('header-buttons')
    <div class="btn-group" role="group">
        <a href="{{ route('furnaces.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>
            Yeni Ocak
        </a>
        <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#statusModal">
            <i class="fas fa-chart-bar me-1"></i>
            Durum Özeti
        </button>
        <button type="button" class="btn btn-outline-success btn-sm" onclick="exportFurnaces()">
            <i class="fas fa-download me-1"></i>
            Export
        </button>
    </div>
@endsection

@section('content')
<!-- Hızlı İstatistikler -->
<div class="row mb-4">
    <div class="col-6 col-md-3 mb-3">
        <div class="card text-center h-100">
            <div class="card-body d-flex flex-column justify-content-center">
                <i class="fas fa-fire text-danger fa-2x mb-2"></i>
                <h4 class="text-danger mb-1">{{ $furnaces->where('status', 'active')->count() }}</h4>
                <small class="text-muted d-none d-sm-block">Aktif Ocak</small>
                <small class="text-muted d-block d-sm-none">Aktif</small>
            </div>
        </div>
    </div>
    
    <div class="col-6 col-md-3 mb-3">
        <div class="card text-center h-100">
            <div class="card-body d-flex flex-column justify-content-center">
                <i class="fas fa-pause-circle text-warning fa-2x mb-2"></i>
                <h4 class="text-warning mb-1">{{ $furnaces->where('status', 'idle')->count() }}</h4>
                <small class="text-muted d-none d-sm-block">Beklemede</small>
                <small class="text-muted d-block d-sm-none">Bekle</small>
            </div>
        </div>
    </div>
    
    <div class="col-6 col-md-3 mb-3">
        <div class="card text-center h-100">
            <div class="card-body d-flex flex-column justify-content-center">
                <i class="fas fa-tools text-info fa-2x mb-2"></i>
                <h4 class="text-info mb-1">{{ $furnaces->where('status', 'maintenance')->count() }}</h4>
                <small class="text-muted d-none d-sm-block">Bakımda</small>
                <small class="text-muted d-block d-sm-none">Bakım</small>
            </div>
        </div>
    </div>
    
    <div class="col-6 col-md-3 mb-3">
        <div class="card text-center h-100">
            <div class="card-body d-flex flex-column justify-content-center">
                <i class="fas fa-stop-circle text-secondary fa-2x mb-2"></i>
                <h4 class="text-secondary mb-1">{{ $furnaces->where('status', 'inactive')->count() }}</h4>
                <small class="text-muted d-none d-sm-block">Kapalı</small>
                <small class="text-muted d-block d-sm-none">Kapalı</small>
            </div>
        </div>
    </div>
</div>

<!-- Set Bazlı Ocak Görünümü -->
<div class="row mb-4">
    @foreach($furnaceSets as $set)
        <div class="col-12 col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-layer-group me-2"></i>
                        <span class="d-none d-sm-inline">{{ $set->name }}</span>
                        <span class="d-inline d-sm-none">{{ $set->name }}</span>
                    </h6>
                    <span class="badge bg-light text-dark">
                        {{ $set->furnaces->where('status', 'active')->count() }}/{{ $set->furnaces->count() }} 
                        <span class="d-none d-sm-inline">Aktif</span>
                        <span class="d-inline d-sm-none">A</span>
                    </span>
                </div>
                <div class="card-body p-2">
                    @php
                        $furnacesInSet = $set->furnaces->toArray();
                        $furnaceCount = count($furnacesInSet);
                    @endphp
                    
                    @foreach($set->furnaces as $index => $furnace)
                        <div class="d-flex justify-content-between align-items-center p-3 mb-3 border rounded position-relative shadow-sm">
                            <!-- Swap Butonu (sadece 2 veya daha fazla ocak varsa ve ilk ocak değilse) -->
                            @if($furnaceCount >= 2 && $index > 0)
                                <button type="button" 
                                        class="btn btn-outline-primary btn-sm position-absolute rounded-circle" 
                                        style="left: -12px; top: 50%; transform: translateY(-50%); z-index: 10; width: 32px; height: 32px; padding: 0;"
                                        onclick="swapFurnaces({{ $furnacesInSet[$index-1]['id'] }}, {{ $furnace->id }})"
                                        title="Ocakları Değiştir">
                                    <i class="fas fa-exchange-alt fa-sm"></i>
                                </button>
                            @endif
                            
                            <div class="flex-grow-1 {{ $furnaceCount >= 2 && $index > 0 ? 'ms-4' : '' }}">
                                <div class="d-flex align-items-center mb-2">
                                    <strong class="fs-6">{{ $furnace->name }}</strong>
                                    <span class="badge bg-info ms-2">{{ $furnaceCastingCounts[$furnace->id] ?? 0 }}. DÖKÜM</span>
                                    <!-- Bilgi Butonu -->
                                    <button type="button" 
                                            class="btn btn-outline-info btn-sm ms-2 rounded-circle" 
                                            style="width: 24px; height: 24px; padding: 0;"
                                            onclick="showFurnaceInfo({{ $furnace->id }})"
                                            title="Ocak Bilgileri">
                                        <i class="fas fa-info fa-xs"></i>
                                    </button>
                                </div>
                                <div class="small text-muted d-none d-sm-block">
                                    <i class="fas fa-weight-hanging me-1"></i>Kapasite: {{ $furnace->capacity ?? 'N/A' }} ton | 
                                    <i class="fas fa-layer-group me-1"></i>Set: {{ $furnace->furnaceSet->name ?? 'N/A' }}
                                </div>
                                <div class="small text-muted d-block d-sm-none">
                                    <i class="fas fa-weight-hanging me-1"></i>{{ $furnace->capacity ?? 'N/A' }}t | 
                                    <i class="fas fa-layer-group me-1"></i>{{ $furnace->furnaceSet->name ?? 'N/A' }}
                                </div>
                                <div class="small text-info">
                                    <i class="fas fa-fire me-1"></i>{{ $furnace->castings->count() }} döküm
                                    @if($furnace->castings->count() > 0)
                                        <span class="d-none d-sm-inline">(Sıradaki: {{ $furnace->castings->count() + 1 }}.)</span>
                                        <span class="d-inline d-sm-none">({{ $furnace->castings->count() + 1 }})</span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-end ms-2">
                                <span class="badge fs-6 px-3 py-2
                                    @if($furnace->status === 'active') bg-success
                                    @elseif($furnace->status === 'idle') bg-warning
                                    @elseif($furnace->status === 'maintenance') bg-info
                                    @else bg-secondary
                                    @endif">
                                    @if($furnace->status === 'active') 
                                        <i class="fas fa-play me-1"></i>Aktif
                                    @elseif($furnace->status === 'idle') 
                                        <i class="fas fa-pause me-1"></i>Beklemede
                                    @elseif($furnace->status === 'maintenance') 
                                        <i class="fas fa-tools me-1"></i>Bakımda
                                    @else 
                                        <i class="fas fa-stop me-1"></i>Kapalı
                                    @endif
                                </span>
                                @if($furnace->status === 'active')
                                    <div class="small text-success mt-2 d-flex align-items-center">
                                        <i class="fas fa-thermometer-half me-1"></i>
                                        <strong>{{ $furnace->current_temperature ?? 'N/A' }}°C</strong>
                                    </div>
                                    <div class="mt-2">
                                        <button type="button" 
                                                class="btn btn-warning btn-sm" 
                                                onclick="deactivateFurnace({{ $furnace->id }})"
                                                title="Ocağı Beklemede Al">
                                            <i class="fas fa-pause me-1"></i>
                                            Beklemede Al
                                        </button>
                                    </div>
                                @else
                                    <div class="small text-muted mt-2">
                                        <i class="fas fa-clock me-1"></i>
                                        <span id="inactive-duration-{{ $furnace->id }}">Hesaplanıyor...</span>
                                    </div>
                                    @if($furnace->status === 'idle')
                                        <div class="mt-2">
                                            <button type="button" 
                                                    class="btn btn-success btn-sm" 
                                                    onclick="activateFurnace({{ $furnace->id }})"
                                                    title="Ocağı Aktif Et">
                                                <i class="fas fa-play me-1"></i>
                                                Aktif Et
                                            </button>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Tüm Dökümler -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-fire me-2"></i>
                    Tüm Dökümler
                    <span class="badge bg-primary ms-2">{{ $allCastings->total() }}</span>
                </h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newCastingModal">
                    <i class="fas fa-plus me-1"></i>
                    Yeni Döküm
                </button>
            </div>
            <div class="card-body p-0">
                <!-- Filtreler -->
                <div class="card-header bg-light">
                    <form method="GET" action="{{ route('furnaces.index') }}">
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
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Tamamlandı</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>İptal</option>
                                </select>
                            </div>
                            <div class="col-6 col-sm-3 col-md-2 mb-3">
                                <label class="form-label">Vardiya</label>
                                <select name="shift" class="form-select">
                                    <option value="">Tüm Vardiyalar</option>
                                    <option value="Gündüz" {{ request('shift') == 'Gündüz' ? 'selected' : '' }}>Gündüz</option>
                                    <option value="Gece" {{ request('shift') == 'Gece' ? 'selected' : '' }}>Gece</option>
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
                                <a href="{{ route('furnaces.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                    <span class="d-none d-sm-inline ms-1">Temizle</span>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
                
                
                @if($allCastings->count() > 0)
                <!-- Desktop Table -->
                <div class="table-responsive d-none d-lg-block">
                        <table class="table table-hover mb-0 table-sm">
                        <thead>
                            <tr>
                                    <th>DÖKÜM SAYISI</th>
                                    <th>OCAK (DÖKÜM SAYISI)</th>
                                    <th>START</th>
                                    <th>STOP</th>
                                    <th>TTT</th>
                                    <th>DEVİRME SICAKLIĞI</th>
                                    <th>OCAK FORMENİ</th>
                                    <th>KULLANILAN ALYAJ</th>
                                    <th>ANALİZLER</th>
                                    <th>İŞLEMLER</th>
                            </tr>
                        </thead>
                        <tbody>
                                @foreach($allCastings as $index => $casting)
                                <tr data-furnace-id="{{ $casting->furnace_id }}">
                                    <td>
                                            <div class="d-flex align-items-center">
                                                @if($casting->status === 'active')
                                                    <i class="fas fa-hourglass-half text-warning me-2"></i>
                                                    <span class="badge bg-warning">CHARGING</span>
                                                @elseif($casting->status === 'charging')
                                                    <i class="fas fa-play-circle text-info me-2"></i>
                                                    <span class="badge bg-info">CHARGING</span>
                                                @elseif($casting->status === 'completed')
                                                    <i class="fas fa-check-circle text-success me-2"></i>
                                                    <span class="badge bg-success">{{ $totalCastings - $index }}. DÖKÜM</span>
                                                @elseif($casting->status === 'cancelled')
                                                    <i class="fas fa-times-circle text-danger me-2"></i>
                                                    <span class="badge bg-danger">İPTAL</span>
                                                @else
                                                    <i class="fas fa-circle text-secondary me-2"></i>
                                                    <span class="badge bg-secondary">{{ $casting->status }}</span>
                                                @endif
                                            </div>
                                    </td>
                                    <td>
                                        @if($casting->status === 'active')
                                            <div class="d-flex align-items-center">
                                                <select class="form-select form-select-sm me-2" style="width: 180px;" 
                                                        onchange="updateCastingFurnace({{ $casting->id }}, this.value)">
                                                    @foreach($furnaces->where('status', 'active') as $furnace)
                                                        <option value="{{ $furnace->id }}" 
                                                                {{ $casting->furnace_id == $furnace->id ? 'selected' : '' }}>
                                                            {{ $furnace->name }} ({{ $furnaceCastingCounts[$furnace->id] ?? 0 }}. DÖKÜM)
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span class="badge bg-info">{{ $castingFurnaceSequence[$casting->id] ?? 0 }}. DÖKÜM</span>
                                            </div>
                                        @elseif($casting->status === 'charging')
                                            <div class="d-flex align-items-center">
                                                <strong class="text-info">{{ $casting->furnace->name }} ({{ $furnaceCastingCounts[$casting->furnace_id] ?? 0 }}. DÖKÜM)</strong>
                                                <span class="badge bg-info ms-2">YENİ DÖKÜM</span>
                                            </div>
                                        @else
                                            <strong>{{ $casting->furnace->name }} ({{ $castingFurnaceSequence[$casting->id] ?? 0 }}. DÖKÜM)</strong>
                                        @endif
                                    </td>
                                    <td>
                                            @if($casting->status === 'charging')
                                                <strong class="text-info">-</strong>
                                            @else
                                                <strong>{{ $casting->started_at ? $casting->started_at->format('H:i') : '-' }}</strong>
                                            @endif
                                    </td>
                                    <td>
                                            @if($casting->status === 'charging')
                                                <strong class="text-info">-</strong>
                                            @else
                                                <strong>{{ $casting->completed_at ? $casting->completed_at->format('H:i') : '---' }}</strong>
                                            @endif
                                    </td>
                                    <td>
                                            @if($casting->status === 'charging')
                                                <strong class="text-info">-</strong>
                                            @elseif($casting->completed_at)
                                                <strong>{{ $casting->started_at->diffInMinutes($casting->completed_at) }} DK</strong>
                                            @elseif($casting->status === 'active')
                                                <strong class="text-success">
                                                    {{ $casting->started_at->diffInMinutes(now()) }} DK
                                                    <i class="fas fa-clock fa-spin"></i>
                                                </strong>
                                        @else
                                                <strong>--- DK</strong>
                                        @endif
                                    </td>
                                    <td>
                                            @if($casting->status === 'charging')
                                                <strong class="text-info">{{ $casting->target_temperature ?? '-' }}°</strong>
                                            @elseif($casting->status === 'active' && $casting->furnace->current_temperature)
                                                <strong class="text-danger">{{ $casting->furnace->current_temperature }}°</strong>
                                            @elseif($casting->status === 'completed' && $casting->final_temperature)
                                                <strong class="text-success">{{ $casting->final_temperature }}°</strong>
                                        @else
                                                <strong>---°</strong>
                                        @endif
                                    </td>
                                    <td>
                                            @if($casting->status === 'charging')
                                                <strong class="text-info">Sistem</strong>
                                            @else
                                                <strong>{{ $casting->operator_name ?? '---' }}</strong>
                                            @endif
                                    </td>
                                    <td>
                                            @if($casting->status === 'charging')
                                                <div class="small text-info">
                                                    <div><strong>SİLİS:</strong> 0 KG</div>
                                                    <div><strong>MANGAN:</strong> 0 KG</div>
                                                    <div><strong>KARBON:</strong> 0 KG</div>
                                                </div>
                                            @else
                                                <div class="small">
                                                    <div><strong>SİLİS:</strong> {{ $casting->adjustments->where('material_type', 'silicon')->sum('amount_kg') ?? 0 }} KG</div>
                                                    <div><strong>MANGAN:</strong> {{ $casting->adjustments->where('material_type', 'manganese')->sum('amount_kg') ?? 0 }} KG</div>
                                                    <div><strong>KARBON:</strong> {{ $casting->adjustments->where('material_type', 'carbon')->sum('amount_kg') ?? 0 }} KG</div>
                                                </div>
                                            @endif
                                            @if($casting->status !== 'charging')
                                                <div class="mt-1">
                                                    <button type="button" class="btn btn-xs btn-outline-primary add-alyaj-btn" 
                                                            data-casting-id="{{ $casting->id }}" title="Alyaj Ekle">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-xs btn-outline-info edit-alyaj-btn" 
                                                            data-casting-id="{{ $casting->id }}" title="Alyaj Düzenle">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($casting->status === 'charging')
                                                <div class="text-info">
                                                    <small>Yeni döküm için hazır</small>
                                                </div>
                                            @else
                                                <div class="prova-container" data-casting-id="{{ $casting->id }}">
                                                    @if($casting->samples->count() > 0)
                                                        @foreach($casting->samples as $sampleIndex => $sample)
                                                        <div class="prova-item mb-2" data-sample-id="{{ $sample->id }}">
                                                            <div class="d-flex align-items-center mb-1">
                                                                <strong class="me-2">{{ $sampleIndex + 1 }}.PROVA:</strong>
                                                                <button class="btn btn-sm btn-outline-primary edit-prova-btn" 
                                                                        data-sample-id="{{ $sample->id }}"
                                                                        title="Düzenle">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                            </div>
                                                            <div class="prova-values">
                                                                <span class="prova-value" data-field="carbon">C:{{ $sample->carbon ?? '---' }}</span>
                                                                <span class="prova-value" data-field="silicon">Sİ:{{ $sample->silicon ?? '---' }}</span>
                                                                <span class="prova-value" data-field="manganese">MN:{{ $sample->manganese ?? '---' }}</span>
                                                                <span class="prova-value" data-field="sulfur">S:{{ $sample->sulfur ?? '---' }}</span>
                                                                <span class="prova-value" data-field="phosphorus">P:{{ $sample->phosphorus ?? '---' }}</span>
                                                                <span class="prova-value" data-field="copper">CU:{{ $sample->copper ?? '---' }}</span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                            @else
                                                    <div class="text-muted">
                                                        <button class="btn btn-sm btn-outline-success add-prova-btn" 
                                                                data-casting-id="{{ $casting->id }}"
                                                                title="Prova Ekle">
                                                            <i class="fas fa-plus"></i> Prova Ekle
                                                </button>
                                                    </div>
                                            @endif
                                            @endif
                                    </td>
                                    <td>
                                            @if($casting->status === 'charging')
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-info start-casting" 
                                                        data-furnace-id="{{ $casting->furnace_id }}"
                                                        title="Yeni Döküm Başlat">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            @elseif($casting->status === 'active')
                                                <div class="btn-group" role="group">
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
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                                @if($casting->status !== 'charging')
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-info add-prova-btn" 
                                                            data-casting-id="{{ $casting->id }}"
                                                            title="Prova Ekle">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-fire fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Henüz döküm bulunmuyor</h5>
                        <p class="text-muted">İlk dökümü başlatmak için "Yeni Döküm" butonuna tıklayın</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newCastingModal">
                            <i class="fas fa-plus"></i> Yeni Döküm Başlat
                        </button>
                    </div>
                @endif
                
                <!-- Pagination -->
                <div class="card-footer">
                    {{ $allCastings->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Durum Özeti Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ocak Durum Özeti</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Set Bazlı Durum</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Set</th>
                                        <th>Aktif</th>
                                        <th>Toplam</th>
                                        <th>Oran</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($furnaceSets as $set)
                                        <tr>
                                            <td>{{ $set->name }}</td>
                                            <td>{{ $set->furnaces->where('status', 'active')->count() }}</td>
                                            <td>{{ $set->furnaces->count() }}</td>
                                            <td>
                                                @php 
                                                    $percentage = $set->furnaces->count() > 0 
                                                        ? ($set->furnaces->where('status', 'active')->count() / $set->furnaces->count()) * 100 
                                                        : 0;
                                                @endphp
                                                <div class="progress" style="height: 15px;">
                                                    <div class="progress-bar bg-success" style="width: {{ $percentage }}%">
                                                        {{ number_format($percentage, 0) }}%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h6>Genel Durum Dağılımı</h6>
                        <canvas id="statusChart" width="300" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<!-- Durum Güncelleme Modal -->
<div class="modal fade" id="statusUpdateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ocak Durumu Güncelle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusUpdateForm">
                <div class="modal-body">
                    <input type="hidden" id="furnace_id" name="furnace_id">
                    <input type="hidden" id="new_status" name="new_status">
                    
                    <div class="mb-3">
                        <label for="status_notes" class="form-label">Durum Değişikliği Notları</label>
                        <textarea class="form-control" id="status_notes" name="status_notes" rows="3" placeholder="Durum değişikliği hakkında notlarınız..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Durumu Güncelle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Alyaj Ekleme Modalı -->
<div class="modal fade" id="addAlyajModal" tabindex="-1" aria-labelledby="addAlyajModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAlyajModalLabel">Yeni Alyaj Malzemesi Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addAlyajForm">
                    <input type="hidden" id="alyajCastingId" name="casting_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="carbon_amount" class="form-label">Karbon (KG)</label>
                            <input type="number" class="form-control" id="carbon_amount" name="carbon_amount" step="0.01" min="0" placeholder="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="manganese_amount" class="form-label">Mangan (KG)</label>
                            <input type="number" class="form-control" id="manganese_amount" name="manganese_amount" step="0.01" min="0" placeholder="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="silicon_amount" class="form-label">Silisyum (KG)</label>
                            <input type="number" class="form-control" id="silicon_amount" name="silicon_amount" step="0.01" min="0" placeholder="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phosphorus_amount" class="form-label">Fosfor (KG)</label>
                            <input type="number" class="form-control" id="phosphorus_amount" name="phosphorus_amount" step="0.01" min="0" placeholder="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="sulfur_amount" class="form-label">Kükürt (KG)</label>
                            <input type="number" class="form-control" id="sulfur_amount" name="sulfur_amount" step="0.01" min="0" placeholder="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="copper_amount" class="form-label">Bakır (KG)</label>
                            <input type="number" class="form-control" id="copper_amount" name="copper_amount" step="0.01" min="0" placeholder="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="adjustment_reason" class="form-label">Ekleme Nedeni</label>
                            <input type="text" class="form-control" id="adjustment_reason" name="adjustment_reason" placeholder="Alyaj ekleme nedeni...">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="alyaj_notes" class="form-label">Notlar</label>
                            <textarea class="form-control" id="alyaj_notes" name="notes" rows="2" maxlength="500" placeholder="Alyaj ekleme notları..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-success" id="saveNewAlyajBtn">Alyaj Ekle</button>
            </div>
        </div>
    </div>
</div>

<!-- Alyaj Düzenleme Modalı -->
<div class="modal fade" id="editAlyajModal" tabindex="-1" aria-labelledby="editAlyajModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAlyajModalLabel">Alyaj Malzemesi Düzenle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editAlyajForm">
                    <input type="hidden" id="editAlyajCastingId" name="casting_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_carbon_amount" class="form-label">Karbon (KG)</label>
                            <input type="number" class="form-control" id="edit_carbon_amount" name="carbon_amount" step="0.01" min="0" placeholder="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_manganese_amount" class="form-label">Mangan (KG)</label>
                            <input type="number" class="form-control" id="edit_manganese_amount" name="manganese_amount" step="0.01" min="0" placeholder="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_silicon_amount" class="form-label">Silisyum (KG)</label>
                            <input type="number" class="form-control" id="edit_silicon_amount" name="silicon_amount" step="0.01" min="0" placeholder="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_phosphorus_amount" class="form-label">Fosfor (KG)</label>
                            <input type="number" class="form-control" id="edit_phosphorus_amount" name="phosphorus_amount" step="0.01" min="0" placeholder="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_sulfur_amount" class="form-label">Kükürt (KG)</label>
                            <input type="number" class="form-control" id="edit_sulfur_amount" name="sulfur_amount" step="0.01" min="0" placeholder="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_copper_amount" class="form-label">Bakır (KG)</label>
                            <input type="number" class="form-control" id="edit_copper_amount" name="copper_amount" step="0.01" min="0" placeholder="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_adjustment_reason" class="form-label">Ekleme Nedeni</label>
                            <input type="text" class="form-control" id="edit_adjustment_reason" name="adjustment_reason" placeholder="Alyaj ekleme nedeni...">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_alyaj_notes" class="form-label">Notlar</label>
                            <textarea class="form-control" id="edit_alyaj_notes" name="notes" rows="2" maxlength="500" placeholder="Alyaj ekleme notları..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-success" id="saveEditAlyajBtn">Kaydet</button>
                <button type="button" class="btn btn-danger" id="deleteAlyajBtn">Sil</button>
            </div>
        </div>
    </div>
</div>

<!-- Yeni Döküm Modal -->
<div class="modal fade" id="newCastingModal" tabindex="-1" aria-labelledby="newCastingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newCastingModalLabel">
                    <i class="fas fa-plus me-2"></i>
                    Yeni Döküm Başlat
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row" id="furnace-selection-cards">
                    @foreach($furnaces->where('status', 'idle')->take(3) as $furnace)
                        <div class="col-md-4 mb-3">
                            <div class="card furnace-selection-card h-100" data-furnace-id="{{ $furnace->id }}" style="cursor: pointer;">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-fire fa-3x text-primary"></i>
                                    </div>
                                    <h5 class="card-title">{{ $furnace->name }}</h5>
                                    <p class="card-text text-muted">{{ $furnace->furnaceSet->name ?? 'N/A' }}</p>
                                    <div class="casting-count-display">
                                        <span class="badge bg-primary fs-6">
                                            {{ $furnaceCastingCounts[$furnace->id] ?? 0 }}. DÖKÜM
                                        </span>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-muted">Kapasite: {{ $furnace->capacity ?? 'N/A' }} ton</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Seçilen ocak için form -->
                <div id="castingFormSection" class="mt-4" style="display: none;">
                    <hr>
                    <h6 class="mb-3">Döküm Bilgileri</h6>
                    <form id="newCastingForm">
                        <input type="hidden" id="selectedFurnaceId" name="furnace_id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Vardiya</label>
                                <div class="form-control-plaintext" id="shiftDisplay">
                                    <span class="badge bg-info" id="currentShift">Hesaplanıyor...</span>
                                </div>
                                <input type="hidden" id="shiftSelect" name="shift">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="operatorName" class="form-label">Operatör Adı</label>
                                <input type="text" class="form-control" id="operatorName" name="operator_name" value="Sistem" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="targetTemperature" class="form-label">Devirme Sıcaklığı (°C)</label>
                                <input type="number" class="form-control" id="targetTemperature" name="target_temperature" value="1600" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="castingNotes" class="form-label">Notlar</label>
                                <textarea class="form-control" id="castingNotes" name="notes" rows="2" placeholder="Döküm notları..."></textarea>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-primary" id="startNewCastingBtn" disabled>
                    <i class="fas fa-play me-1"></i>
                    Dökümü Başlat
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Ocak Bilgi Modalı -->
<div class="modal fade" id="furnaceInfoModal" tabindex="-1" aria-labelledby="furnaceInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="furnaceInfoModalLabel">
                    <i class="fas fa-info-circle me-2"></i>
                    Ocak Bilgileri
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="furnaceInfoContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Yükleniyor...</span>
                        </div>
                        <p class="mt-2">Ocak bilgileri yükleniyor...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Kapat
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Ocak durumu güncelle
function updateFurnaceStatus(furnaceId, newStatus) {
    document.getElementById('furnace_id').value = furnaceId;
    document.getElementById('new_status').value = newStatus;
    
    const statusNames = {
        'active': 'Aktif',
        'idle': 'Beklemede',
        'maintenance': 'Bakımda',
        'inactive': 'Kapalı'
    };
    
    document.querySelector('#statusUpdateModal .modal-title').textContent = 
        `Ocak Durumunu "${statusNames[newStatus]}" Olarak Güncelle`;
    
    const modal = new bootstrap.Modal(document.getElementById('statusUpdateModal'));
    modal.show();
}

// Durum güncelleme form submit
document.getElementById('statusUpdateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const furnaceId = formData.get('furnace_id');
    
    fetch(`/furnaces/${furnaceId}/update-status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            status: formData.get('new_status'),
            status_notes: formData.get('status_notes')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            
            // Etkilenen ocakları göster
            if (data.affected_furnaces && data.affected_furnaces.length > 0) {
                showToast(`Etkilenen ocaklar: ${data.affected_furnaces.join(', ')}`, 'info');
            }
            
            location.reload();
        } else {
            showToast(data.message || 'Hata oluştu', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Hata oluştu', 'error');
    });
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('statusUpdateModal'));
    modal.hide();
});

// Export fonksiyonu
function exportFurnaces() {
    window.open('{{ route("furnaces.export") }}', '_blank');
    showToast('Ocak listesi export ediliyor...', 'info');
}

// Durum grafiği
function initStatusChart() {
    const ctx = document.getElementById('statusChart');
    if (!ctx) return;
    
    const statusData = {
        'Aktif': {{ $furnaces->where('status', 'active')->count() }},
        'Beklemede': {{ $furnaces->where('status', 'idle')->count() }},
        'Bakımda': {{ $furnaces->where('status', 'maintenance')->count() }},
        'Kapalı': {{ $furnaces->where('status', 'inactive')->count() }}
    };
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(statusData),
            datasets: [{
                data: Object.values(statusData),
                backgroundColor: [
                    '#28a745', // Aktif - yeşil
                    '#ffc107', // Beklemede - sarı
                    '#17a2b8', // Bakımda - mavi
                    '#6c757d'  // Kapalı - gri
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Modal açıldığında grafiği başlat
document.getElementById('statusModal').addEventListener('shown.bs.modal', function() {
    initStatusChart();
});

// Klavye kısayolları
document.addEventListener('keydown', function(e) {
    // Ctrl + N: Yeni ocak
    if (e.ctrlKey && e.key === 'n') {
        e.preventDefault();
        window.location.href = '{{ route("furnaces.create") }}';
    }
    
    // Ctrl + I: Durum özeti
    if (e.ctrlKey && e.key === 'i') {
        e.preventDefault();
        const modal = new bootstrap.Modal(document.getElementById('statusModal'));
        modal.show();
    }
});

// Döküm tamamlama - Sadece bir kez bağla
$(document).off('click', '.complete-casting').on('click', '.complete-casting', function() {
    const castingId = $(this).data('casting-id');
    const button = $(this);
    
    // Sıcaklık girişi modalını göster
    showTemperatureInputModal(castingId, button);
});

// Döküm iptal etme
$(document).off('click', '.cancel-casting').on('click', '.cancel-casting', function() {
    const castingId = $(this).data('casting-id');
    const button = $(this);
    
    if (confirm('Bu dökümü iptal etmek istediğinizden emin misiniz?')) {
        // Butonu devre dışı bırak
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: `/api/v1/castings/${castingId}/cancel`,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'json',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            },
            success: function(response) {
                console.log('Döküm iptal response:', response);
                if (response.success) {
                    alert('Döküm başarıyla iptal edildi! Sayfa yenileniyor...');
                    location.reload();
                } else {
                    alert('Hata: ' + response.message);
                    button.prop('disabled', false).html('<i class="fas fa-times"></i> İptal');
                }
            },
            error: function(xhr) {
                console.error('Döküm iptal hatası:', xhr);
                let errorMessage = 'Bilinmeyen hata';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                alert('Hata: ' + errorMessage);
                button.prop('disabled', false).html('<i class="fas fa-times"></i> İptal');
            }
        });
    }
});

// Vardiya otomatik belirleme fonksiyonu
function determineShift() {
    const now = new Date();
    const hour = now.getHours();
    
    let shift, shiftName;
    if (hour >= 8 && hour < 16) {
        shift = 'A';
        shiftName = 'A Vardiyası (08:00-16:00)';
    } else if (hour >= 16 && hour < 24) {
        shift = 'B';
        shiftName = 'B Vardiyası (16:00-00:00)';
    } else {
        shift = 'C';
        shiftName = 'C Vardiyası (00:00-08:00)';
    }
    
    return { shift, shiftName };
}

// Ocak seçim kartları
$(document).off('click', '.furnace-selection-card').on('click', '.furnace-selection-card', function() {
    // Tüm kartlardan seçim kaldır
    $('.furnace-selection-card').removeClass('selected');
    
    // Bu kartı seç
    $(this).addClass('selected');
    
    // Seçilen ocağın ID'sini al
    const furnaceId = $(this).data('furnace-id');
    $('#selectedFurnaceId').val(furnaceId);
    
    // Vardiyayı otomatik belirle
    const shiftInfo = determineShift();
    $('#shiftSelect').val(shiftInfo.shift);
    $('#currentShift').text(shiftInfo.shiftName);
    
    // Form bölümünü göster
    $('#castingFormSection').show();
    
    // Başlat butonunu aktif et
    $('#startNewCastingBtn').prop('disabled', false);
});

// Yeni döküm modal butonu
$(document).off('click', '#startNewCastingBtn').on('click', '#startNewCastingBtn', function() {
    const form = $('#newCastingForm');
    const formData = form.serialize();
    
    // Form validasyonu
    if (!form[0].checkValidity()) {
        form[0].reportValidity();
        return;
    }
    
    const furnaceId = $('#selectedFurnaceId').val();
    if (!furnaceId) {
        alert('Lütfen bir ocak seçin!');
        return;
    }
    
    // Butonu devre dışı bırak
    $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Başlatılıyor...');
    
    $.ajax({
        url: `/furnaces/${furnaceId}/start-casting`,
        type: 'POST',
        data: formData + '&_token=' + $('meta[name="csrf-token"]').attr('content'),
        dataType: 'json',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
        },
        success: function(response) {
            console.log('Yeni döküm başlatma response:', response);
            if (response.success) {
                alert('Yeni döküm başarıyla başlatıldı! Sayfa yenileniyor...');
                $('#newCastingModal').modal('hide');
                location.reload();
            } else {
                alert('Hata: ' + response.message);
                $('#startNewCastingBtn').prop('disabled', false).html('<i class="fas fa-play me-1"></i> Dökümü Başlat');
            }
        },
        error: function(xhr) {
            console.error('Yeni döküm başlatma hatası:', xhr);
            let errorMessage = 'Bilinmeyen hata';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            alert('Hata: ' + errorMessage);
            $('#startNewCastingBtn').prop('disabled', false).html('<i class="fas fa-play me-1"></i> Dökümü Başlat');
        }
    });
});

// Modal kapandığında temizle
$('#newCastingModal').on('hidden.bs.modal', function() {
    $('.furnace-selection-card').removeClass('selected');
    $('#castingFormSection').hide();
    $('#startNewCastingBtn').prop('disabled', true);
    $('#newCastingForm')[0].reset();
});

// Prova ekleme modalında yön tuşları ile geçiş
$(document).off('keydown', '#addProvaModal input[type="number"]').on('keydown', '#addProvaModal input[type="number"]', function(e) {
    if (e.key === 'ArrowDown' || e.key === 'Enter') {
        e.preventDefault();
        const inputs = $('#addProvaModal input[type="number"]');
        const currentIndex = inputs.index(this);
        const nextIndex = (currentIndex + 1) % inputs.length;
        inputs.eq(nextIndex).focus();
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        const inputs = $('#addProvaModal input[type="number"]');
        const currentIndex = inputs.index(this);
        const prevIndex = currentIndex === 0 ? inputs.length - 1 : currentIndex - 1;
        inputs.eq(prevIndex).focus();
    }
});

// Prova düzenleme modalında yön tuşları ile geçiş
$(document).off('keydown', '#editProvaModal input[type="number"]').on('keydown', '#editProvaModal input[type="number"]', function(e) {
    if (e.key === 'ArrowDown' || e.key === 'Enter') {
        e.preventDefault();
        const inputs = '#editProvaModal input[type="number"]';
        const currentIndex = $(inputs).index(this);
        const nextIndex = (currentIndex + 1) % $(inputs).length;
        $(inputs).eq(nextIndex).focus();
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        const inputs = '#editProvaModal input[type="number"]';
        const currentIndex = $(inputs).index(this);
        const prevIndex = currentIndex === 0 ? $(inputs).length - 1 : currentIndex - 1;
        $(inputs).eq(prevIndex).focus();
    }
});

// Alyaj ekleme modalında yön tuşları ile geçiş
$(document).off('keydown', '#addAlyajModal input[type="number"]').on('keydown', '#addAlyajModal input[type="number"]', function(e) {
    if (e.key === 'ArrowDown' || e.key === 'Enter') {
        e.preventDefault();
        const inputs = $('#addAlyajModal input[type="number"]');
        const currentIndex = inputs.index(this);
        const nextIndex = (currentIndex + 1) % inputs.length;
        inputs.eq(nextIndex).focus();
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        const inputs = $('#addAlyajModal input[type="number"]');
        const currentIndex = inputs.index(this);
        const prevIndex = currentIndex === 0 ? inputs.length - 1 : currentIndex - 1;
        inputs.eq(prevIndex).focus();
    }
});

// Alyaj düzenleme modalında yön tuşları ile geçiş
$(document).off('keydown', '#editAlyajModal input[type="number"]').on('keydown', '#editAlyajModal input[type="number"]', function(e) {
    if (e.key === 'ArrowDown' || e.key === 'Enter') {
        e.preventDefault();
        const inputs = $('#editAlyajModal input[type="number"]');
        const currentIndex = inputs.index(this);
        const nextIndex = (currentIndex + 1) % inputs.length;
        inputs.eq(nextIndex).focus();
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        const inputs = $('#editAlyajModal input[type="number"]');
        const currentIndex = inputs.index(this);
        const prevIndex = currentIndex === 0 ? inputs.length - 1 : currentIndex - 1;
        inputs.eq(prevIndex).focus();
    }
});

// Sıcaklık girişi modalını göster
function showTemperatureInputModal(castingId, button) {
    // Modal HTML oluştur
    const modalHtml = `
        <div class="modal fade" id="temperatureInputModal" tabindex="-1" aria-labelledby="temperatureInputModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="temperatureInputModalLabel">
                            <i class="fas fa-thermometer-half me-2"></i>
                            Döküm Tamamlama
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="final_temperature" class="form-label">
                                <i class="fas fa-thermometer-half me-1"></i>
                                Devirme Sıcaklığı (°C) <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   class="form-control" 
                                   id="final_temperature" 
                                   name="final_temperature" 
                                   min="0" 
                                   max="2000" 
                                   step="0.1"
                                   placeholder="Örnek: 1650.5"
                                   required>
                            <div class="form-text">Dökümün tamamlandığı sıcaklık değerini girin (0-2000°C)</div>
                        </div>
                        <div class="mb-3">
                            <label for="completion_notes" class="form-label">
                                <i class="fas fa-sticky-note me-1"></i>
                                Tamamlama Notları
                            </label>
                            <textarea class="form-control" 
                                      id="completion_notes" 
                                      name="completion_notes" 
                                      rows="3" 
                                      placeholder="Döküm hakkında notlar..."></textarea>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Bilgi:</strong> Döküm tamamlandıktan sonra yeni döküm için ocak seçimi yapılacak.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="button" class="btn btn-success" id="confirm-temperature-completion">
                            <i class="fas fa-check me-1"></i>
                            Dökümü Tamamla
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Modal'ı DOM'a ekle
    $('body').append(modalHtml);
    
    // Modal'ı göster
    const modal = new bootstrap.Modal(document.getElementById('temperatureInputModal'));
    modal.show();
    
    // Onay butonu
    $(document).off('click', '#confirm-temperature-completion').on('click', '#confirm-temperature-completion', function() {
        const finalTemperature = $('#final_temperature').val();
        const completionNotes = $('#completion_notes').val();
        
        if (!finalTemperature || finalTemperature < 0 || finalTemperature > 2000) {
            alert('Lütfen geçerli bir sıcaklık değeri girin (0-2000°C)');
            return;
        }
        
        // Modal'ı kapat
        $('#temperatureInputModal').modal('hide');
        
        // Dökümü tamamla
        completeCastingWithTemperature(castingId, button, finalTemperature, completionNotes);
    });
    
    // Modal kapandığında temizle
    $('#temperatureInputModal').on('hidden.bs.modal', function() {
        $(this).remove();
    });
}

// Sıcaklık ile döküm tamamlama
function completeCastingWithTemperature(castingId, button, finalTemperature, completionNotes) {
    // Butonu devre dışı bırak
    button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Tamamlanıyor...');
    
    console.log('Döküm tamamlama isteği gönderiliyor...');
    console.log('Casting ID:', castingId);
    console.log('Final Temperature:', finalTemperature);
    
    // Döküm ID'sinden ocak ID'sini bul
    const castingElement = button.closest('tr, .card');
    const furnaceId = castingElement.data('furnace-id');
    
    if (!furnaceId) {
        alert('Ocak ID bulunamadı!');
        button.prop('disabled', false).html('<i class="fas fa-check"></i> Tamamla');
        return;
    }
    
    console.log('Furnace ID:', furnaceId);
    
    $.ajax({
        url: `/furnaces/${furnaceId}/castings/${castingId}/complete`,
        type: 'POST',
        data: {
            auto_start_next: true,
            final_temperature: finalTemperature,
            completion_notes: completionNotes,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
        },
        success: function(response) {
            console.log('Döküm tamamlama response:', response);
            if (response.success) {
                if (response.new_casting) {
                    alert(`Döküm başarıyla tamamlandı! Yeni döküm ${response.new_casting.casting_number} otomatik olarak başlatıldı.`);
                } else {
                    alert('Döküm başarıyla tamamlandı!');
                }
                // Sayfayı yenile
                location.reload();
            } else {
                alert('Hata: ' + response.message);
                button.prop('disabled', false).html('<i class="fas fa-check"></i> Tamamla');
            }
        },
        error: function(xhr) {
            console.error('AJAX Error:', xhr);
            console.error('Status:', xhr.status);
            console.error('Response:', xhr.responseText);
            
            let errorMessage = 'Bilinmeyen hata';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.responseText) {
                errorMessage = xhr.responseText;
            }
            alert('Hata: ' + errorMessage);
            button.prop('disabled', false).html('<i class="fas fa-check"></i> Tamamla');
        }
    });
}

// Yeni döküm oluşturma ve ocak seçimi
function createNewCastingWithFurnaceSelection() {
    console.log('createNewCastingWithFurnaceSelection fonksiyonu çağrıldı');
    
    // Önce yeni döküm oluştur (geçici olarak ilk idle ocağa)
    createTemporaryCasting();
}

// Direkt yeni döküm oluştur (modal olmadan) - Global fonksiyon
window.createNewCastingDirectly = function() {
    console.log('createNewCastingDirectly fonksiyonu çağrıldı');
    
    // İlk aktif ocağı bul ve yeni döküm oluştur
    $.ajax({
        url: '/furnaces/active-furnaces',
        type: 'GET',
        dataType: 'json',
        success: function(furnaces) {
            if (furnaces && furnaces.length > 0) {
                const firstFurnace = furnaces[0];
                console.log('İlk ocak bulundu:', firstFurnace);
                
                // Yeni döküm oluştur
                $.ajax({
                    url: `/furnaces/${firstFurnace.id}/start-casting`,
                    type: 'POST',
                    data: {
                        shift: 'A',
                        operator_name: 'Sistem',
                        target_temperature: 1600,
                        notes: 'Otomatik oluşturulan döküm',
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType: 'json',
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
                    },
                    success: function(response) {
                        console.log('Yeni döküm oluşturuldu:', response);
                        if (response.success) {
                            alert('Yeni döküm başarıyla oluşturuldu! Sayfa yenileniyor...');
                            location.reload();
                        } else {
                            alert('Yeni döküm oluşturulamadı: ' + response.message);
                            location.reload();
                        }
                    },
                    error: function(xhr) {
                        console.error('Yeni döküm oluşturma hatası:', xhr);
                        alert('Yeni döküm oluşturulamadı. Sayfa yenileniyor...');
                        location.reload();
                    }
                });
            } else {
                console.log('Aktif ocak bulunamadı, sayfa yenileniyor...');
                location.reload();
            }
        },
        error: function(xhr) {
            console.error('Ocaklar getirilemedi:', xhr);
            alert('Aktif ocaklar getirilemedi. Sayfa yenileniyor...');
            location.reload();
        }
    });
}

// Geçici döküm oluştur
function createTemporaryCasting() {
    console.log('Geçici döküm oluşturuluyor...');
    
    // İlk idle ocağı bul ve önce aktif yap, sonra döküm oluştur
    $.ajax({
        url: '/furnaces/active-furnaces',
        type: 'GET',
        dataType: 'json',
        success: function(furnaces) {
            if (furnaces && furnaces.length > 0) {
                const firstFurnace = furnaces[0];
                console.log('İlk ocak bulundu:', firstFurnace);
                
                // Önce ocağı aktif yap
                $.ajax({
                    url: `/furnaces/${firstFurnace.id}/toggle-status`,
                    type: 'POST',
                    data: {
                        status: 'active',
                        current_temperature: 1600,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType: 'json',
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
                    },
                    success: function(toggleResponse) {
                        if (toggleResponse.success) {
                            console.log('Ocak aktif yapıldı:', toggleResponse);
                            
                            // Sonra döküm oluştur
                            $.ajax({
                                url: `/furnaces/${firstFurnace.id}/start-casting`,
                                type: 'POST',
                                data: {
                                    shift: 'A',
                                    operator_name: 'Sistem',
                                    target_temperature: 1600,
                                    notes: 'Geçici döküm - ocak seçimi bekleniyor',
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                dataType: 'json',
                                beforeSend: function(xhr) {
                                    xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
                                },
                                success: function(response) {
                                    console.log('Geçici döküm oluşturuldu:', response);
                                    if (response.success) {
                                        // Şimdi ocak seçim modalını göster
                                        showFurnaceSelectionModal(furnaces, response.casting.id);
                                    } else {
                                        alert('Geçici döküm oluşturulamadı: ' + response.message);
                                        location.reload();
                                    }
                                },
                                error: function(xhr) {
                                    console.error('Döküm oluşturma hatası:', xhr);
                                    alert('Döküm oluşturulamadı: ' + (xhr.responseJSON?.message || 'Bilinmeyen hata'));
                                    location.reload();
                                }
                            });
                        } else {
                            alert('Ocak aktif yapılamadı: ' + toggleResponse.message);
                            location.reload();
                        }
                    },
                    error: function(xhr) {
                        console.error('Ocak aktif yapma hatası:', xhr);
                        alert('Ocak aktif yapılamadı: ' + (xhr.responseJSON?.message || 'Bilinmeyen hata'));
                        location.reload();
                    }
                });
            } else {
                alert('Aktif ocak bulunamadı. Lütfen önce bir ocağı aktif yapın.');
                location.reload();
            }
        },
        error: function(xhr) {
            console.error('Geçici döküm oluşturma hatası:', xhr);
            alert('Geçici döküm oluşturulamadı. Sayfa yenileniyor...');
            location.reload();
        }
    });
}

// Ocak seçim modalını göster
function showFurnaceSelectionModal(furnaces, castingId = null) {
    console.log('showFurnaceSelectionModal fonksiyonu çağrıldı, ocaklar:', furnaces, 'castingId:', castingId);
    
    if (!furnaces || furnaces.length === 0) {
        console.log('Ocak listesi boş, sayfa yenileniyor...');
        location.reload();
        return;
    }
    
    // Modal HTML oluştur
    const modalHtml = `
        <div class="modal fade" id="furnaceSelectionModal" tabindex="-1" aria-labelledby="furnaceSelectionModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="furnaceSelectionModalLabel">
                            <i class="fas fa-fire me-2"></i>
                            Döküm İçin Ocak Seçin
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row" id="furnace-selection-grid">
                            ${furnaces.map(furnace => `
                                <div class="col-md-6 mb-3">
                                    <div class="card furnace-selection-card" data-furnace-id="${furnace.id}">
                                        <div class="card-body text-center">
                                            <h6 class="card-title">${furnace.name}</h6>
                                            <p class="card-text text-muted">${furnace.furnace_set_name}</p>
                                            <div class="casting-count-display">
                                                <span class="badge bg-primary fs-6" id="casting-count-${furnace.id}">
                                                    ${furnace.casting_count || 0}. DÖKÜM
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="button" class="btn btn-primary" id="confirm-furnace-selection" disabled>
                            <i class="fas fa-check me-1"></i>
                            Ocağı Seç
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Modal'ı DOM'a ekle
    $('body').append(modalHtml);
    
    // Modal'ı göster
    const modal = new bootstrap.Modal(document.getElementById('furnaceSelectionModal'));
    modal.show();
    
    // Ocak seçim eventi
    let selectedFurnaceId = null;
    $(document).off('click', '.furnace-selection-card').on('click', '.furnace-selection-card', function() {
        // Önceki seçimi temizle
        $('.furnace-selection-card').removeClass('border-primary bg-light');
        
        // Yeni seçimi işaretle
        $(this).addClass('border-primary bg-light');
        selectedFurnaceId = $(this).data('furnace-id');
        
        // Onay butonunu aktif et
        $('#confirm-furnace-selection').prop('disabled', false);
    });
    
    // Onay butonu
    $(document).off('click', '#confirm-furnace-selection').on('click', '#confirm-furnace-selection', function() {
        if (selectedFurnaceId) {
            if (castingId) {
                // Mevcut dökümün ocağını değiştir
                updateCastingFurnace(castingId, selectedFurnaceId);
            } else {
                // Yeni döküm başlat
                startNewCasting(selectedFurnaceId);
            }
        }
    });
    
    // Modal kapandığında temizle
    $('#furnaceSelectionModal').on('hidden.bs.modal', function() {
        $(this).remove();
    });
}

// Dökümün ocağını güncelle (global fonksiyon)
window.updateCastingFurnace = function(castingId, furnaceId) {
    console.log('Döküm ocağı güncelleniyor, Casting ID:', castingId, 'Furnace ID:', furnaceId);
    
    $.ajax({
        url: `/api/v1/castings/${castingId}/update-furnace`,
        type: 'POST',
        data: {
            furnace_id: furnaceId,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
        },
        success: function(response) {
            console.log('Döküm ocağı güncelleme response:', response);
            if (response.success) {
                alert('Döküm ocağı başarıyla güncellendi! Sayfa yenileniyor...');
                // Sayfayı yenile
                location.reload();
            } else {
                alert('Hata: ' + response.message);
            }
        },
        error: function(xhr) {
            console.error('Döküm ocağı güncelleme hatası:', xhr);
            let errorMessage = 'Bilinmeyen hata';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            alert('Hata: ' + errorMessage);
        }
    });
}

// Yeni dökümü başlat
function startNewCasting(furnaceId) {
    console.log('Yeni döküm başlatılıyor, Ocak ID:', furnaceId);
    
    $.ajax({
        url: `/furnaces/${furnaceId}/start-casting`,
        type: 'POST',
        data: {
            shift: 'A',
            operator_name: 'Sistem',
            target_temperature: 1600,
            notes: 'Otomatik başlatılan döküm',
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
        },
        success: function(response) {
            console.log('Yeni döküm başlatma response:', response);
            if (response.success) {
                alert('Yeni döküm başarıyla başlatıldı! Sayfa yenileniyor...');
                // Modal'ı kapat
                $('#furnaceSelectionModal').modal('hide');
                // Sayfayı yenile
                location.reload();
            } else {
                alert('Hata: ' + response.message);
            }
        },
        error: function(xhr) {
            console.error('Yeni döküm başlatma hatası:', xhr);
            let errorMessage = 'Bilinmeyen hata';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            alert('Hata: ' + errorMessage);
        }
    });
}

// Otomatik yenileme (1 dakikada bir)
setInterval(() => {
    if (!document.hidden) {
        location.reload();
    }
}, 60000);

// Prova düzenleme
$(document).on('click', '.edit-prova-btn', function(e) {
    e.preventDefault();
    
    console.log('Prova düzenleme butonu tıklandı'); // Debug
    
    const sampleId = $(this).data('sample-id');
    console.log('Sample ID:', sampleId); // Debug
    
    if (!sampleId) {
        alert('Prova ID bulunamadı!');
        return;
    }
    
    const provaItem = $(this).closest('.prova-item');
    
    // Mevcut değerleri al
    const carbon = provaItem.find('[data-field="carbon"]').text().replace('C:', '');
    const silicon = provaItem.find('[data-field="silicon"]').text().replace('Sİ:', '');
    const manganese = provaItem.find('[data-field="manganese"]').text().replace('MN:', '');
    const sulfur = provaItem.find('[data-field="sulfur"]').text().replace('S:', '');
    const phosphorus = provaItem.find('[data-field="phosphorus"]').text().replace('P:', '');
    const copper = provaItem.find('[data-field="copper"]').text().replace('CU:', '');
    
    console.log('Mevcut değerler:', {carbon, silicon, manganese, sulfur, phosphorus, copper}); // Debug
    
    // Modal formunu doldur
    $('#sampleId').val(sampleId);
    $('#carbon').val(carbon === '---' ? '' : carbon);
    $('#silicon').val(silicon === '---' ? '' : silicon);
    $('#manganese').val(manganese === '---' ? '' : manganese);
    $('#sulfur').val(sulfur === '---' ? '' : sulfur);
    $('#phosphorus').val(phosphorus === '---' ? '' : phosphorus);
    $('#copper').val(copper === '---' ? '' : copper);
    
    console.log('Modal formu dolduruldu'); // Debug
    
    // Modalı aç
    $('#editProvaModal').modal('show');
});

// Yeni prova ekleme - Sadece bir kez bağla
$(document).off('click', '.add-prova-btn').on('click', '.add-prova-btn', function() {
    const castingId = $(this).data('casting-id');
    $('#castingId').val(castingId);
    $('#addProvaForm')[0].reset();
    $('#addProvaModal').modal('show');
});

// Prova kaydetme
$(document).on('click', '#saveProvaBtn', function(e) {
    e.preventDefault();
    
    console.log('Prova kaydetme butonu tıklandı'); // Debug
    
    const sampleId = $('#sampleId').val();
    console.log('Sample ID:', sampleId); // Debug
    
    if (!sampleId) {
        alert('Prova ID bulunamadı!');
        return;
    }
    
    const formData = {
        carbon: $('#carbon').val() || 0,
        silicon: $('#silicon').val() || 0,
        manganese: $('#manganese').val() || 0,
        sulfur: $('#sulfur').val() || 0,
        phosphorus: $('#phosphorus').val() || 0,
        copper: $('#copper').val() || 0,
        _token: $('meta[name="csrf-token"]').attr('content')
    };
    
    console.log('Güncellenecek veri:', formData); // Debug
    
    // Butonu devre dışı bırak
    $(this).prop('disabled', true).text('Güncelleniyor...');
    
    $.ajax({
        url: `/samples/${sampleId}`,
        type: 'PUT',
        data: formData,
        dataType: 'json',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
        },
        success: function(response) {
            console.log('Başarılı yanıt:', response);
            alert('Prova başarıyla güncellendi!');
            // Modalı kapat
            $('#editProvaModal').modal('hide');
            // Sayfayı yenile
            location.reload();
        },
        error: function(xhr, status, error) {
            console.error('AJAX Hatası:', {
                status: xhr.status,
                statusText: xhr.statusText,
                responseText: xhr.responseText,
                error: error
            });
            
            let errorMessage = 'Bilinmeyen hata';
            if (xhr.responseJSON) {
                if (xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON.errors) {
                    errorMessage = Object.values(xhr.responseJSON.errors).flat().join(', ');
                }
            } else if (xhr.status === 422) {
                errorMessage = 'Doğrulama hatası - Lütfen tüm alanları kontrol edin';
            } else if (xhr.status === 500) {
                errorMessage = 'Sunucu hatası - Lütfen tekrar deneyin';
            } else if (xhr.status === 404) {
                errorMessage = 'Prova bulunamadı';
            }
            
            alert('Hata: ' + errorMessage);
        },
        complete: function() {
            // Butonu tekrar aktif et
            $('#saveProvaBtn').prop('disabled', false).text('Kaydet');
        }
    });
});

// Yeni prova kaydetme - Sadece bir kez bağla
$(document).off('click', '#saveNewProvaBtn').on('click', '#saveNewProvaBtn', function(e) {
    e.preventDefault();
    
    console.log('Prova ekleme butonu tıklandı'); // Debug
    
    const castingId = $('#castingId').val();
    console.log('Casting ID:', castingId); // Debug
    
    if (!castingId) {
        alert('Döküm ID bulunamadı!');
        return;
    }
    
    const formData = {
        casting_id: castingId,
        carbon: $('#new_carbon').val() || 0,
        silicon: $('#new_silicon').val() || 0,
        manganese: $('#new_manganese').val() || 0,
        sulfur: $('#new_sulfur').val() || 0,
        phosphorus: $('#new_phosphorus').val() || 0,
        copper: $('#new_copper').val() || 0,
        _token: $('meta[name="csrf-token"]').attr('content')
    };
    
    console.log('Gönderilen veri:', formData); // Debug
    
    // Butonu devre dışı bırak
    $(this).prop('disabled', true).text('Ekleniyor...');
    
    $.ajax({
        url: '/samples',
        type: 'POST',
        data: formData,
        dataType: 'json',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
        },
        success: function(response) {
            console.log('Başarılı yanıt:', response);
            alert('Prova başarıyla eklendi!');
            // Modalı kapat
            $('#addProvaModal').modal('hide');
            // Sayfayı yenile
            location.reload();
        },
        error: function(xhr, status, error) {
            console.error('AJAX Hatası:', {
                status: xhr.status,
                statusText: xhr.statusText,
                responseText: xhr.responseText,
                error: error
            });
            
            let errorMessage = 'Bilinmeyen hata';
            if (xhr.responseJSON) {
                if (xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON.errors) {
                    errorMessage = Object.values(xhr.responseJSON.errors).flat().join(', ');
                }
            } else if (xhr.status === 422) {
                errorMessage = 'Doğrulama hatası - Lütfen tüm alanları kontrol edin';
            } else if (xhr.status === 500) {
                errorMessage = 'Sunucu hatası - Lütfen tekrar deneyin';
            } else if (xhr.status === 404) {
                errorMessage = 'Sayfa bulunamadı - Route kontrol edin';
            }
            
            alert('Hata: ' + errorMessage);
        },
        complete: function() {
            // Butonu tekrar aktif et
            $('#saveNewProvaBtn').prop('disabled', false).text('Prova Ekle');
        }
    });
});
</script>

<style>
.furnace-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}

.table th {
    background-color: #f8f9fa;
    font-weight: bold;
    font-size: 0.85rem;
    padding: 0.5rem;
    border-bottom: 2px solid #dee2e6;
}

.table td {
    padding: 0.5rem;
    vertical-align: middle;
    font-size: 0.85rem;
}

.table-sm th,
.table-sm td {
    padding: 0.3rem 0.5rem;
}

.badge {
    font-size: 0.75rem;
}

.small {
    font-size: 0.75rem;
    line-height: 1.2;
}

.table-responsive {
    font-size: 0.85rem;
}

/* Mobil kartlar için */
.card-body {
    padding: 1rem;
}

.card-title {
    font-size: 0.9rem;
}

/* Durum ikonları */
.fa-hourglass-half {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.prova-values {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.prova-value {
    background-color: #f8f9fa;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    border: 1px solid #dee2e6;
}

.prova-item {
    border: 1px solid #e9ecef;
    border-radius: 0.375rem;
    padding: 0.5rem;
    background-color: #f8f9fa;
}

/* Ocak seçim kartları */
.furnace-selection-card {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.furnace-selection-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.furnace-selection-card.border-primary {
    border-color: #0d6efd !important;
    background-color: #f8f9fa !important;
}

.casting-count-display .badge {
    font-size: 1rem;
    padding: 0.5em 0.75em;
}

/* Yeni döküm modal kartları */
.furnace-selection-card {
    transition: all 0.3s ease;
    border: 2px solid #e9ecef;
}

.furnace-selection-card:hover {
    border-color: #0d6efd;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.furnace-selection-card.selected {
    border-color: #0d6efd;
    background-color: #f8f9ff;
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2);
}

.furnace-selection-card .card-body {
    padding: 1.5rem;
}

.furnace-selection-card .fas.fa-fire {
    color: #0d6efd;
}

.furnace-selection-card.selected .fas.fa-fire {
    color: #0d6efd;
}
</style>

<!-- Prova Düzenleme Modalı -->
<div class="modal fade" id="editProvaModal" tabindex="-1" aria-labelledby="editProvaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProvaModalLabel">Prova Değerlerini Düzenle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editProvaForm">
                    <input type="hidden" id="sampleId" name="sample_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="carbon" class="form-label">Karbon (C)</label>
                            <input type="number" class="form-control" id="carbon" name="carbon" step="0.01" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="silicon" class="form-label">Silisyum (Sİ)</label>
                            <input type="number" class="form-control" id="silicon" name="silicon" step="0.01" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="manganese" class="form-label">Mangan (MN)</label>
                            <input type="number" class="form-control" id="manganese" name="manganese" step="0.01" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="sulfur" class="form-label">Kükürt (S)</label>
                            <input type="number" class="form-control" id="sulfur" name="sulfur" step="0.01" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phosphorus" class="form-label">Fosfor (P)</label>
                            <input type="number" class="form-control" id="phosphorus" name="phosphorus" step="0.01" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="copper" class="form-label">Bakır (CU)</label>
                            <input type="number" class="form-control" id="copper" name="copper" step="0.01" min="0">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-primary" id="saveProvaBtn">Kaydet</button>
            </div>
        </div>
    </div>
</div>

<!-- Yeni Prova Ekleme Modalı -->
<div class="modal fade" id="addProvaModal" tabindex="-1" aria-labelledby="addProvaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProvaModalLabel">Yeni Prova Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addProvaForm">
                    <input type="hidden" id="castingId" name="casting_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="new_carbon" class="form-label">Karbon (C)</label>
                            <input type="number" class="form-control" id="new_carbon" name="carbon" step="0.01" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="new_silicon" class="form-label">Silisyum (Sİ)</label>
                            <input type="number" class="form-control" id="new_silicon" name="silicon" step="0.01" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="new_manganese" class="form-label">Mangan (MN)</label>
                            <input type="number" class="form-control" id="new_manganese" name="manganese" step="0.01" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="new_sulfur" class="form-label">Kükürt (S)</label>
                            <input type="number" class="form-control" id="new_sulfur" name="sulfur" step="0.01" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="new_phosphorus" class="form-label">Fosfor (P)</label>
                            <input type="number" class="form-control" id="new_phosphorus" name="phosphorus" step="0.01" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="new_copper" class="form-label">Bakır (CU)</label>
                            <input type="number" class="form-control" id="new_copper" name="copper" step="0.01" min="0">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-success" id="saveNewProvaBtn">Prova Ekle</button>
            </div>
        </div>
    </div>
</div>

<script>
// Alyaj ekleme - Sadece bir kez bağla
$(document).off('click', '.add-alyaj-btn').on('click', '.add-alyaj-btn', function() {
    const castingId = $(this).data('casting-id');
    $('#alyajCastingId').val(castingId);
    $('#addAlyajForm')[0].reset();
    $('#addAlyajModal').modal('show');
});

// Alyaj düzenleme - Sadece bir kez bağla
$(document).off('click', '.edit-alyaj-btn').on('click', '.edit-alyaj-btn', function() {
    const castingId = $(this).data('casting-id');
    $('#editAlyajCastingId').val(castingId);
    
    // Döküme ait alyaj malzemelerini getir
    $.ajax({
        url: `/adjustments/casting/${castingId}`,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.adjustments.length > 0) {
                // Tüm alanları sıfırla
                $('#edit_carbon_amount').val('');
                $('#edit_manganese_amount').val('');
                $('#edit_silicon_amount').val('');
                $('#edit_phosphorus_amount').val('');
                $('#edit_sulfur_amount').val('');
                $('#edit_copper_amount').val('');
                
                // Mevcut alyaj malzemelerini modal'a yükle
                response.adjustments.forEach(function(adjustment) {
                    switch(adjustment.material_type) {
                        case 'carbon':
                            $('#edit_carbon_amount').val(adjustment.amount_kg);
                            break;
                        case 'manganese':
                            $('#edit_manganese_amount').val(adjustment.amount_kg);
                            break;
                        case 'silicon':
                            $('#edit_silicon_amount').val(adjustment.amount_kg);
                            break;
                        case 'phosphorus':
                            $('#edit_phosphorus_amount').val(adjustment.amount_kg);
                            break;
                        case 'sulfur':
                            $('#edit_sulfur_amount').val(adjustment.amount_kg);
                            break;
                        case 'copper':
                            $('#edit_copper_amount').val(adjustment.amount_kg);
                            break;
                    }
                });
                
                // İlk adjustment'ın diğer bilgilerini yükle
                const firstAdjustment = response.adjustments[0];
                $('#edit_adjustment_reason').val(firstAdjustment.adjustment_reason);
                $('#edit_alyaj_notes').val(firstAdjustment.notes);
                
                $('#editAlyajModal').modal('show');
            } else {
                alert('Bu döküm için alyaj malzemesi bulunamadı!');
            }
        },
        error: function() {
            alert('Alyaj malzemeleri yüklenirken hata oluştu!');
        }
    });
});

// Yeni alyaj kaydetme - Sadece bir kez bağla
$(document).off('click', '#saveNewAlyajBtn').on('click', '#saveNewAlyajBtn', function(e) {
    e.preventDefault();
    
    const castingId = $('#alyajCastingId').val();
    const adjustmentReason = $('#adjustment_reason').val();
    const notes = $('#alyaj_notes').val();
    
    // Malzeme türleri ve miktarları
    const materials = [
        { type: 'carbon', amount: $('#carbon_amount').val() },
        { type: 'manganese', amount: $('#manganese_amount').val() },
        { type: 'silicon', amount: $('#silicon_amount').val() },
        { type: 'phosphorus', amount: $('#phosphorus_amount').val() },
        { type: 'sulfur', amount: $('#sulfur_amount').val() },
        { type: 'copper', amount: $('#copper_amount').val() }
    ];
    
    // Sadece miktar girilen malzemeleri filtrele
    const materialsToAdd = materials.filter(material => 
        material.amount && parseFloat(material.amount) > 0
    );
    
    if (materialsToAdd.length === 0) {
        alert('Lütfen en az bir malzeme miktarı girin!');
        return;
    }
    
    // Butonu devre dışı bırak
    $(this).prop('disabled', true).text('Ekleniyor...');
    
    // Her malzeme için ayrı kayıt oluştur
    let completedRequests = 0;
    let totalRequests = materialsToAdd.length;
    let hasError = false;
    
    materialsToAdd.forEach((material, index) => {
        const formData = {
            casting_id: castingId,
            material_type: material.type,
            amount_kg: material.amount,
            adjustment_reason: adjustmentReason,
            notes: notes,
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        
        $.ajax({
            url: '/adjustments',
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            },
            success: function(response) {
                completedRequests++;
                if (completedRequests === totalRequests && !hasError) {
                    alert(`${materialsToAdd.length} alyaj malzemesi başarıyla eklendi!`);
                    $('#addAlyajModal').modal('hide');
                    location.reload();
                }
            },
            error: function(xhr) {
                hasError = true;
                let errorMessage = 'Bilinmeyen hata';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                alert('Hata: ' + errorMessage);
                $('#saveNewAlyajBtn').prop('disabled', false).text('Alyaj Ekle');
            }
        });
    });
});

// Alyaj düzenleme kaydetme - Sadece bir kez bağla
$(document).off('click', '#saveEditAlyajBtn').on('click', '#saveEditAlyajBtn', function(e) {
    e.preventDefault();
    
    const castingId = $('#editAlyajCastingId').val();
    const adjustmentReason = $('#edit_adjustment_reason').val();
    const notes = $('#edit_alyaj_notes').val();
    
    // Malzeme türleri ve miktarları
    const materials = [
        { type: 'carbon', amount: $('#edit_carbon_amount').val() },
        { type: 'manganese', amount: $('#edit_manganese_amount').val() },
        { type: 'silicon', amount: $('#edit_silicon_amount').val() },
        { type: 'phosphorus', amount: $('#edit_phosphorus_amount').val() },
        { type: 'sulfur', amount: $('#edit_sulfur_amount').val() },
        { type: 'copper', amount: $('#edit_copper_amount').val() }
    ];
    
    // Sadece miktar girilen malzemeleri filtrele
    const materialsToUpdate = materials.filter(material => 
        material.amount && parseFloat(material.amount) > 0
    );
    
    if (materialsToUpdate.length === 0) {
        alert('Lütfen en az bir malzeme miktarı girin!');
        return;
    }
    
    // Butonu devre dışı bırak
    $(this).prop('disabled', true).text('Kaydediliyor...');
    
    // Önce mevcut alyaj malzemelerini sil
    $.ajax({
        url: `/adjustments/casting/${castingId}`,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.adjustments.length > 0) {
                // Mevcut alyaj malzemelerini sil
                let deletedCount = 0;
                response.adjustments.forEach(function(adjustment) {
                    $.ajax({
                        url: `/adjustments/${adjustment.id}`,
                        type: 'DELETE',
                        data: { _token: $('meta[name="csrf-token"]').attr('content') },
                        success: function() {
                            deletedCount++;
                            if (deletedCount === response.adjustments.length) {
                                // Silme işlemi tamamlandı, yeni alyaj malzemelerini ekle
                                addNewAdjustments();
                            }
                        },
                        error: function() {
                            alert('Mevcut alyaj malzemeleri silinirken hata oluştu!');
                            $('#saveEditAlyajBtn').prop('disabled', false).text('Kaydet');
                        }
                    });
                });
            } else {
                // Mevcut alyaj yok, direkt yeni alyaj malzemelerini ekle
                addNewAdjustments();
            }
        },
        error: function() {
            alert('Mevcut alyaj malzemeleri yüklenirken hata oluştu!');
            $('#saveEditAlyajBtn').prop('disabled', false).text('Kaydet');
        }
    });
    
    function addNewAdjustments() {
        // Her malzeme için ayrı kayıt oluştur
        let completedRequests = 0;
        let totalRequests = materialsToUpdate.length;
        let hasError = false;
        
        materialsToUpdate.forEach((material, index) => {
            const formData = {
                casting_id: castingId,
                material_type: material.type,
                amount_kg: material.amount,
                adjustment_reason: adjustmentReason,
                notes: notes,
                _token: $('meta[name="csrf-token"]').attr('content')
            };
            
            $.ajax({
                url: '/adjustments',
                type: 'POST',
                data: formData,
                dataType: 'json',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
                },
                success: function(response) {
                    completedRequests++;
                    if (completedRequests === totalRequests && !hasError) {
                        alert(`${materialsToUpdate.length} alyaj malzemesi başarıyla güncellendi!`);
                        $('#editAlyajModal').modal('hide');
                        location.reload();
                    }
                },
                error: function(xhr) {
                    hasError = true;
                    let errorMessage = 'Bilinmeyen hata';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    alert('Hata: ' + errorMessage);
                    $('#saveEditAlyajBtn').prop('disabled', false).text('Kaydet');
                }
            });
        });
    }
});

// Alyaj silme - Sadece bir kez bağla
$(document).off('click', '#deleteAlyajBtn').on('click', '#deleteAlyajBtn', function(e) {
    e.preventDefault();
    
    if (!confirm('Bu alyaj malzemesini silmek istediğinizden emin misiniz?')) {
        return;
    }
    
    const adjustmentId = $('#editAlyajId').val();
    
    // Butonu devre dışı bırak
    $(this).prop('disabled', true).text('Siliniyor...');
    
    $.ajax({
        url: `/adjustments/${adjustmentId}`,
        type: 'DELETE',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
        },
        success: function(response) {
            alert('Alyaj malzemesi başarıyla silindi!');
            $('#editAlyajModal').modal('hide');
            location.reload();
        },
        error: function(xhr) {
            let errorMessage = 'Bilinmeyen hata';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            alert('Hata: ' + errorMessage);
        },
        complete: function() {
            $('#deleteAlyajBtn').prop('disabled', false).text('Sil');
        }
    });
});

// Ocak durumlarını değiştir
function swapFurnaces(furnace1Id, furnace2Id) {
    if (!confirm('Bu iki ocağın durumlarını değiştirmek istediğinizden emin misiniz?')) {
        return;
    }

    // Loading göster
    const swapBtn = event.target.closest('button');
    const originalContent = swapBtn.innerHTML;
    swapBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    swapBtn.disabled = true;

    $.ajax({
        url: '/api/furnaces/swap-status',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            furnace1_id: furnace1Id,
            furnace2_id: furnace2Id
        },
        success: function(response) {
            if (response.success) {
                // Sayfayı yenile
                location.reload();
            } else {
                alert('Hata: ' + response.message);
                // Butonu eski haline getir
                swapBtn.innerHTML = originalContent;
                swapBtn.disabled = false;
            }
        },
        error: function(xhr) {
            let errorMessage = 'Ocak durumları değiştirilirken hata oluştu.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            alert('Hata: ' + errorMessage);
            // Butonu eski haline getir
            swapBtn.innerHTML = originalContent;
            swapBtn.disabled = false;
        }
    });
}

// Ocak bilgi modalını göster
function showFurnaceInfo(furnaceId) {
    // Modalı göster
    $('#furnaceInfoModal').modal('show');
    
    // Loading göster
    $('#furnaceInfoContent').html(`
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Yükleniyor...</span>
            </div>
            <p class="mt-2">Ocak bilgileri yükleniyor...</p>
        </div>
    `);

    // Ocak bilgilerini getir
    $.ajax({
        url: `/api/furnaces/${furnaceId}/info`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                displayFurnaceInfo(response);
            } else {
                $('#furnaceInfoContent').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ${response.message}
                    </div>
                `);
            }
        },
        error: function(xhr) {
            let errorMessage = 'Ocak bilgileri alınırken hata oluştu.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            $('#furnaceInfoContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${errorMessage}
                </div>
            `);
        }
    });
}

// Ocak bilgilerini modalda göster
function displayFurnaceInfo(data) {
    const furnace = data.furnace;
    const stats = data.stats;
    const allCastings = data.all_castings;
    const inactiveDuration = data.inactive_duration;

    let statusBadge = '';
    let statusIcon = '';
    let statusClass = '';
    
    switch(furnace.status) {
        case 'active':
            statusBadge = 'Aktif';
            statusIcon = 'fas fa-play';
            statusClass = 'success';
            break;
        case 'idle':
            statusBadge = 'Beklemede';
            statusIcon = 'fas fa-pause';
            statusClass = 'warning';
            break;
        case 'maintenance':
            statusBadge = 'Bakımda';
            statusIcon = 'fas fa-tools';
            statusClass = 'info';
            break;
        default:
            statusBadge = 'Kapalı';
            statusIcon = 'fas fa-stop';
            statusClass = 'secondary';
    }

    const content = `
        <div class="row">
            <!-- Sol Kolon - Genel Bilgiler -->
            <div class="col-md-6">
                <div class="card border-0">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Genel Bilgiler
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <strong>Ocak Adı:</strong><br>
                                <span class="text-primary">${furnace.name}</span>
                            </div>
                            <div class="col-6">
                                <strong>Durum:</strong><br>
                                <span class="badge bg-${statusClass}">
                                    <i class="${statusIcon} me-1"></i>${statusBadge}
                                </span>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-6">
                                <strong>Kapasite:</strong><br>
                                <span class="text-info">${furnace.capacity || 'N/A'} ton</span>
                            </div>
                            <div class="col-6">
                                <strong>Set:</strong><br>
                                <span class="text-secondary">${furnace.furnace_set?.name || 'N/A'}</span>
                            </div>
                        </div>

                        ${furnace.status === 'active' ? `
                        <div class="row mb-3">
                            <div class="col-12">
                                <strong>Mevcut Sıcaklık:</strong><br>
                                <span class="text-success fs-5">
                                    <i class="fas fa-thermometer-half me-1"></i>
                                    ${furnace.current_temperature || 'N/A'}°C
                                </span>
                            </div>
                        </div>
                        ` : ''}

                        ${inactiveDuration ? `
                        <div class="row mb-3">
                            <div class="col-12">
                                <strong>Aktif Olmayan Süre:</strong><br>
                                <span class="text-warning">
                                    <i class="fas fa-clock me-1"></i>
                                    ${inactiveDuration}
                                </span>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>

            <!-- Sağ Kolon - İstatistikler -->
            <div class="col-md-6">
                <div class="card border-0">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>
                            İstatistikler
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="border rounded p-2">
                                    <div class="text-primary fs-4">${stats.total_castings}</div>
                                    <small class="text-muted">Toplam Döküm</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="border rounded p-2">
                                    <div class="text-success fs-4">${stats.completed_castings}</div>
                                    <small class="text-muted">Tamamlanan</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="border rounded p-2">
                                    <div class="text-warning fs-4">${stats.active_castings}</div>
                                    <small class="text-muted">Aktif</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="border rounded p-2">
                                    <div class="text-danger fs-4">${stats.cancelled_castings}</div>
                                    <small class="text-muted">İptal Edilen</small>
                                </div>
                            </div>
                        </div>
                        
                        ${stats.average_temperature ? `
                        <div class="mt-3 text-center">
                            <div class="border rounded p-2">
                                <div class="text-info fs-5">
                                    <i class="fas fa-thermometer-half me-1"></i>
                                    ${Math.round(stats.average_temperature)}°C
                                </div>
                                <small class="text-muted">Ortalama Sıcaklık</small>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        </div>

        <!-- Tüm Dökümler -->
        ${allCastings.length > 0 ? `
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-history me-2"></i>
                            <strong>${furnace.name}</strong> - Tüm Dökümler (${allCastings.length} adet)
                            <small class="text-muted ms-2">Sadece bu ocağın dökümleri</small>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Genel Sıra</th>
                                        <th>Ocak Sırası</th>
                                        <th>Döküm No</th>
                                        <th>Durum</th>
                                        <th>Başlangıç</th>
                                        <th>Bitiş</th>
                                        <th>Süre</th>
                                        <th>Devirme Sıcaklığı</th>
                                        <th>Vardiya</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${allCastings.map(casting => {
                                        const startTime = new Date(casting.started_at);
                                        const endTime = casting.completed_at ? new Date(casting.completed_at) : null;
                                        const duration = endTime ? Math.round((endTime - startTime) / (1000 * 60)) : '-';
                                        
                                        let statusBadge = '';
                                        let statusClass = '';
                                        switch(casting.status) {
                                            case 'completed':
                                                statusBadge = 'Tamamlandı';
                                                statusClass = 'success';
                                                break;
                                            case 'active':
                                                statusBadge = 'Aktif';
                                                statusClass = 'warning';
                                                break;
                                            case 'cancelled':
                                                statusBadge = 'İptal';
                                                statusClass = 'danger';
                                                break;
                                            default:
                                                statusBadge = casting.status;
                                                statusClass = 'secondary';
                                        }
                                        
                                        return `
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary" title="Genel döküm sıralamasındaki pozisyon">
                                                        ${casting.global_order || '-'}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary" title="Bu ocağın kaçıncı dökümü">
                                                        ${casting.furnace_order || '-'}
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong>${casting.casting_number}</strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-${statusClass}">
                                                        ${statusBadge}
                                                    </span>
                                                </td>
                                                <td>
                                                    <small>${startTime.toLocaleString('tr-TR')}</small>
                                                </td>
                                                <td>
                                                    <small>${endTime ? endTime.toLocaleString('tr-TR') : '-'}</small>
                                                </td>
                                                <td>
                                                    <span class="text-info">
                                                        ${duration !== '-' ? duration + ' dk' : '-'}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="text-success">
                                                        ${casting.final_temperature || casting.current_temperature || '-'}°C
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">${casting.shift}</span>
                                                </td>
                                            </tr>
                                        `;
                                    }).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ` : `
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0">
                    <div class="card-body text-center">
                        <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Bu ocakta henüz döküm yapılmamış</h5>
                        <p class="text-muted">İlk dökümü başlatmak için "Yeni Döküm" butonunu kullanabilirsiniz.</p>
                    </div>
                </div>
            </div>
        </div>
        `}
    `;

    $('#furnaceInfoContent').html(content);
}

// Ocağı aktif et
function activateFurnace(furnaceId) {
    if (!confirm('Bu ocağı aktif etmek istediğinizden emin misiniz?')) {
        return;
    }

    // Loading göster
    const button = event.target;
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Aktif Ediliyor...';

    $.ajax({
        url: `/furnaces/${furnaceId}/toggle-status`,
        type: 'POST',
        data: {
            status: 'active',
            current_temperature: 1600,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
        },
        success: function(response) {
            if (response.success) {
                alert('Ocak başarıyla aktif edildi!');
                location.reload();
            } else {
                alert('Hata: ' + response.message);
                button.disabled = false;
                button.innerHTML = originalText;
            }
        },
        error: function(xhr) {
            console.error('Ocak aktif etme hatası:', xhr);
            let errorMessage = 'Bilinmeyen hata';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            alert('Hata: ' + errorMessage);
            button.disabled = false;
            button.innerHTML = originalText;
        }
    });
}

// Ocağı beklemede al
function deactivateFurnace(furnaceId) {
    if (!confirm('Bu ocağı beklemede almak istediğinizden emin misiniz?')) {
        return;
    }

    // Loading göster
    const button = event.target;
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Beklemede Alınıyor...';

    $.ajax({
        url: `/furnaces/${furnaceId}/toggle-status`,
        type: 'POST',
        data: {
            status: 'idle',
            current_temperature: null,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
        },
        success: function(response) {
            if (response.success) {
                alert('Ocak başarıyla beklemede alındı!');
                location.reload();
            } else {
                alert('Hata: ' + response.message);
                button.disabled = false;
                button.innerHTML = originalText;
            }
        },
        error: function(xhr) {
            console.error('Ocak beklemede alma hatası:', xhr);
            let errorMessage = 'Bilinmeyen hata';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            alert('Hata: ' + errorMessage);
            button.disabled = false;
            button.innerHTML = originalText;
        }
    });
}

// Sayfa yüklendiğinde aktif olmayan süreleri hesapla
$(document).ready(function() {
    $('[id^="inactive-duration-"]').each(function() {
        const furnaceId = this.id.replace('inactive-duration-', '');
        // Burada gerçek hesaplama yapılabilir
        $(this).text('2 saat 30 dakika'); // Örnek değer
    });
});
</script>
@endpush

