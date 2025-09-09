@extends('layouts.app')

@section('title', $furnace->name . ' - Ocak Detayı')

@section('header', $furnace->name . ' Detayları')

@section('header-buttons')
    <div class="btn-group" role="group">
        <a href="{{ route('furnaces.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>
            Ocaklar
        </a>
        <a href="{{ route('furnaces.edit', $furnace) }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-edit me-1"></i>
            Düzenle
        </a>
        @if($furnace->status !== 'active')
            <button type="button" class="btn btn-outline-success btn-sm" onclick="updateFurnaceStatus('active')">
                <i class="fas fa-play me-1"></i>
                Aktif Yap
            </button>
        @else
            <button type="button" class="btn btn-outline-warning btn-sm" onclick="updateFurnaceStatus('idle')">
                <i class="fas fa-pause me-1"></i>
                Bekletmeye Al
            </button>
        @endif
        <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-ellipsis-v me-1"></i>
                Daha Fazla
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('castings.create', ['furnace_id' => $furnace->id]) }}">
                    <i class="fas fa-plus me-2"></i>Yeni Döküm Başlat
                </a></li>
                <li><a class="dropdown-item" href="{{ route('samples.create', ['furnace_id' => $furnace->id]) }}">
                    <i class="fas fa-vial me-2"></i>Yeni Prova Ekle
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#" onclick="updateFurnaceStatus('maintenance')">
                    <i class="fas fa-tools me-2"></i>Bakıma Al
                </a></li>
                <li><a class="dropdown-item" href="#" onclick="updateFurnaceStatus('inactive')">
                    <i class="fas fa-stop me-2"></i>Kapat
                </a></li>
            </ul>
        </div>
    </div>
@endsection

@section('content')
<!-- Ocak Durum Kartı -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-fire me-2"></i>
                    {{ $furnace->name }} - Anlık Durum
                </h5>
                <span class="badge 
                    @if($furnace->status === 'active') bg-success
                    @elseif($furnace->status === 'idle') bg-warning
                    @elseif($furnace->status === 'maintenance') bg-info
                    @else bg-secondary
                    @endif fs-6">
                    @if($furnace->status === 'active') AKTİF
                    @elseif($furnace->status === 'idle') BEKLEMEDE
                    @elseif($furnace->status === 'maintenance') BAKIMDA
                    @else KAPALI
                    @endif
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <div class="p-3">
                            <i class="fas fa-thermometer-half fa-3x text-danger mb-2"></i>
                            <h4 class="text-danger">
                                {{ $furnace->getLastRecordedTemperature() ?? 'N/A' }}°C
                            </h4>
                            <small class="text-muted">Son Kaydedilen Sıcaklık</small>
                            <div class="mt-2">
                                <button class="btn btn-outline-danger btn-sm" onclick="showTemperatureModal()">
                                    <i class="fas fa-plus"></i> Sıcaklık Ekle
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 text-center">
                        <div class="p-3">
                            <i class="fas fa-fire fa-3x text-primary mb-2"></i>
                            <h4 class="text-primary">
                                {{ $stats['total_castings'] }}
                            </h4>
                            <small class="text-muted">Toplam Döküm</small>
                            <div class="mt-2">
                                <span class="badge bg-info">
                                    Sıradaki: {{ $stats['next_casting_number'] }}. döküm
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 text-center">
                        <div class="p-3">
                            <i class="fas fa-weight-hanging fa-3x text-info mb-2"></i>
                            <h4 class="text-info">
                                {{ $furnace->capacity ?? 'N/A' }} ton
                            </h4>
                            <small class="text-muted">Kapasite</small>
                        </div>
                    </div>
                    
                    <div class="col-md-3 text-center">
                        <div class="p-3">
                            <i class="fas fa-fire fa-3x text-warning mb-2"></i>
                            <h4 class="text-warning">
                                {{ $furnace->castings->where('status', 'active')->count() }}
                            </h4>
                            <small class="text-muted">Aktif Döküm</small>
                        </div>
                    </div>
                    
                    <div class="col-md-3 text-center">
                        <div class="p-3">
                            <i class="fas fa-vial fa-3x text-success mb-2"></i>
                            <h4 class="text-success">
                                {{ $furnace->castings->flatMap->samples->where('quality_status', 'pending')->count() }}
                            </h4>
                            <small class="text-muted">Bekleyen Prova</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bakım Takibi -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card {{ $stats['needs_maintenance'] ? 'border-danger' : 'border-warning' }}">
            <div class="card-header {{ $stats['needs_maintenance'] ? 'bg-danger text-white' : 'bg-warning text-dark' }}">
                <h6 class="mb-0">
                    <i class="fas fa-tools me-2"></i>
                    Bakım Takibi
                    @if($stats['needs_maintenance'])
                        <span class="badge bg-light text-danger ms-2">BAKIM GEREKLİ!</span>
                    @endif
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <div class="p-2">
                            <h4 class="{{ $stats['needs_maintenance'] ? 'text-danger' : 'text-warning' }}">
                                {{ $stats['castings_since_maintenance'] }}
                            </h4>
                            <small class="text-muted">Son Bakımdan Bu Yana Döküm</small>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="p-2">
                            <h4 class="text-info">
                                {{ $stats['max_castings_before_maintenance'] }}
                            </h4>
                            <small class="text-muted">Maksimum Döküm Sayısı</small>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="p-2">
                            <h4 class="{{ $stats['needs_maintenance'] ? 'text-danger' : 'text-success' }}">
                                %{{ number_format($stats['maintenance_progress'], 1) }}
                            </h4>
                            <small class="text-muted">Bakım İlerleme Oranı</small>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="p-2">
                            @if($furnace->last_maintenance_date)
                                <h6 class="text-muted">
                                    {{ \Carbon\Carbon::parse($furnace->last_maintenance_date)->format('d.m.Y') }}
                                </h6>
                                <small class="text-muted">Son Bakım Tarihi</small>
                            @else
                                <h6 class="text-muted">-</h6>
                                <small class="text-muted">Son Bakım Tarihi</small>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Progress Bar -->
                <div class="mt-3">
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar {{ $stats['needs_maintenance'] ? 'bg-danger' : ($stats['maintenance_progress'] > 80 ? 'bg-warning' : 'bg-success') }}" 
                             style="width: {{ $stats['maintenance_progress'] }}%">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <small class="text-muted">0 döküm</small>
                        <small class="text-muted">{{ $stats['max_castings_before_maintenance'] }} döküm</small>
                    </div>
                </div>
                
                @if($stats['needs_maintenance'])
                    <div class="alert alert-danger mt-3 mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Bakım Gerekli!</strong> Bu ocak maksimum döküm sayısına ulaştı. Refraktör kontrolü ve bakım yapılması önerilir.
                    </div>
                @elseif($stats['maintenance_progress'] > 80)
                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Dikkat!</strong> Ocak bakım zamanına yaklaşıyor. Bakım planlaması yapılması önerilir.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Detaylı Bilgiler ve İstatistikler -->
<div class="row mb-4">
    <!-- Teknik Bilgiler -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>
                    Teknik Bilgiler
                </h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tbody>
                        <tr>
                            <td><strong>Ocak Seti:</strong></td>
                            <td>{{ $furnace->furnaceSet->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Kapasite:</strong></td>
                            <td>{{ $furnace->capacity ?? 'Belirtilmemiş' }} ton</td>
                        </tr>
                        <tr>
                            <td><strong>Max Sıcaklık:</strong></td>
                            <td>{{ $furnace->max_temperature ?? 'Belirtilmemiş' }}°C</td>
                        </tr>
                        <tr>
                            <td><strong>Yakıt Türü:</strong></td>
                            <td>
                                @if($furnace->fuel_type)
                                    @switch($furnace->fuel_type)
                                        @case('natural_gas')
                                            <i class="fas fa-fire text-primary"></i> Doğal Gaz
                                            @break
                                        @case('electricity')
                                            <i class="fas fa-bolt text-warning"></i> Elektrik
                                            @break
                                        @case('coal')
                                            <i class="fas fa-mountain text-dark"></i> Kömür
                                            @break
                                        @case('oil')
                                            <i class="fas fa-oil-can text-info"></i> Mazot
                                            @break
                                        @case('mixed')
                                            <i class="fas fa-layer-group text-secondary"></i> Karma
                                            @break
                                        @default
                                            {{ ucfirst($furnace->fuel_type) }}
                                    @endswitch
                                @else
                                    Belirtilmemiş
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Kurulum Tarihi:</strong></td>
                            <td>{{ $furnace->installation_date ? $furnace->installation_date->format('d.m.Y') : 'Belirtilmemiş' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Son Bakım:</strong></td>
                            <td>
                                {{ $furnace->last_maintenance_date ? $furnace->last_maintenance_date->format('d.m.Y') : 'Belirtilmemiş' }}
                                @if($furnace->last_maintenance_date)
                                    <br><small class="text-muted">{{ $furnace->last_maintenance_date->diffForHumans() }}</small>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Sonraki Bakım:</strong></td>
                            <td>
                                @if($furnace->last_maintenance_date && $furnace->maintenance_interval_days)
                                    @php
                                        $nextMaintenance = $furnace->last_maintenance_date->addDays($furnace->maintenance_interval_days);
                                        $isOverdue = $nextMaintenance->isPast();
                                    @endphp
                                    <span class="{{ $isOverdue ? 'text-danger' : 'text-success' }}">
                                        {{ $nextMaintenance->format('d.m.Y') }}
                                    </span>
                                    @if($isOverdue)
                                        <br><small class="text-danger">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            {{ abs($nextMaintenance->diffInDays(now())) }} gün gecikmiş
                                        </small>
                                    @else
                                        <br><small class="text-muted">{{ $nextMaintenance->diffForHumans() }}</small>
                                    @endif
                                @else
                                    Planlanmamış
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                @if($furnace->description)
                    <div class="mt-3">
                        <strong>Açıklama:</strong>
                        <p class="mt-2 text-muted">{{ $furnace->description }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Son 30 Gün İstatistikleri -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Son 30 Gün İstatistikleri
                </h6>
            </div>
            <div class="card-body">
                @php
                    $last30Days = now()->subDays(30);
                    $recentCastings = $furnace->castings->where('casting_date', '>=', $last30Days);
                    $recentSamples = $furnace->castings->flatMap->samples->where('sample_time', '>=', $last30Days);
                @endphp
                
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="p-2 bg-light rounded">
                            <h4 class="text-primary mb-1">{{ $recentCastings->count() }}</h4>
                            <small class="text-muted">Toplam Döküm</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="p-2 bg-light rounded">
                            <h4 class="text-success mb-1">{{ $recentSamples->count() }}</h4>
                            <small class="text-muted">Toplam Prova</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="p-2 bg-light rounded">
                            <h4 class="text-warning mb-1">
                                {{ $recentCastings->where('status', 'completed')->count() }}
                            </h4>
                            <small class="text-muted">Tamamlanan</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="p-2 bg-light rounded">
                            <h4 class="text-info mb-1">
                                {{ $recentSamples->where('quality_status', 'approved')->count() }}
                            </h4>
                            <small class="text-muted">Onaylanan</small>
                        </div>
                    </div>
                </div>
                
                <!-- Kalite Oranı -->
                <div class="mt-3">
                    <label class="form-label small">Kalite Onay Oranı</label>
                    @php
                        $qualityRate = $recentSamples->count() > 0 
                            ? ($recentSamples->where('quality_status', 'approved')->count() / $recentSamples->count()) * 100 
                            : 0;
                    @endphp
                    <div class="progress">
                        <div class="progress-bar bg-success" style="width: {{ $qualityRate }}%">
                            {{ number_format($qualityRate, 1) }}%
                        </div>
                    </div>
                </div>
                
                <!-- Aktif Çalışma Oranı -->
                <div class="mt-3">
                    <label class="form-label small">Son 30 Gün Aktiflik</label>
                    @php
                        $activeDays = $recentCastings->groupBy(function($casting) {
                            return $casting->casting_date->format('Y-m-d');
                        })->count();
                        $activityRate = ($activeDays / 30) * 100;
                    @endphp
                    <div class="progress">
                        <div class="progress-bar bg-primary" style="width: {{ $activityRate }}%">
                            {{ number_format($activityRate, 1) }}%
                        </div>
                    </div>
                    <small class="text-muted">{{ $activeDays }} gün aktif</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Son Dökümleri -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-fire me-2"></i>
                    Son Dökümleri
                </h6>
                <a href="{{ route('castings.index', ['furnace_id' => $furnace->id]) }}" class="btn btn-sm btn-outline-primary">
                    Tümünü Görüntüle
                </a>
            </div>
            <div class="card-body">
                @if($furnace->castings->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Döküm #</th>
                                    <th>Tarih</th>
                                    <th>Vardiya</th>
                                    <th>Durum</th>
                                    <th>Prova Sayısı</th>
                                    <th>Kalite</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($furnace->castings->take(10) as $casting)
                                    <tr>
                                        <td><strong>#{{ $casting->casting_number }}</strong></td>
                                        <td>{{ $casting->casting_date->format('d.m.Y H:i') }}</td>
                                        <td>{{ $casting->shift ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge 
                                                @if($casting->status === 'active') bg-success
                                                @elseif($casting->status === 'completed') bg-primary
                                                @elseif($casting->status === 'cancelled') bg-danger
                                                @else bg-secondary
                                                @endif">
                                                {{ ucfirst($casting->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $casting->samples->count() }}</td>
                                        <td>
                                            @php
                                                $approvedSamples = $casting->samples->where('quality_status', 'approved')->count();
                                                $totalSamples = $casting->samples->count();
                                            @endphp
                                            @if($totalSamples > 0)
                                                <span class="badge bg-info">
                                                    {{ $approvedSamples }}/{{ $totalSamples }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('castings.show', $casting) }}" class="btn btn-outline-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($casting->status === 'active')
                                                    <a href="{{ route('samples.create', ['casting_id' => $casting->id]) }}" class="btn btn-outline-success btn-sm">
                                                        <i class="fas fa-vial"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-fire fa-3x mb-3"></i>
                        <p>Henüz döküm kaydı yok</p>
                        <a href="{{ route('castings.create', ['furnace_id' => $furnace->id]) }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            İlk Dökümü Başlat
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Son Provalar -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-vial me-2"></i>
                    Son Provalar
                </h6>
                <a href="{{ route('samples.index', ['furnace_id' => $furnace->id]) }}" class="btn btn-sm btn-outline-primary">
                    Tümünü Görüntüle
                </a>
            </div>
            <div class="card-body">
                @php $recentSamples = $furnace->castings->flatMap->samples->take(10); @endphp
                @if($recentSamples->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Prova #</th>
                                    <th>Döküm #</th>
                                    <th>Tarih</th>
                                    <th>Analiz Eden</th>
                                    <th>Durum</th>
                                    <th>C%</th>
                                    <th>Mn%</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentSamples as $sample)
                                    <tr>
                                        <td><strong>#{{ $sample->sample_number }}</strong></td>
                                        <td>#{{ $sample->casting->casting_number }}</td>
                                        <td>{{ $sample->sample_time->format('d.m H:i') }}</td>
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
                                                @elseif($sample->quality_status === 'needs_adjustment') Düzeltme
                                                @else {{ $sample->quality_status }}
                                                @endif
                                            </span>
                                        </td>
                                        <td>{{ $sample->carbon_percentage ? number_format($sample->carbon_percentage, 3) : '-' }}</td>
                                        <td>{{ $sample->manganese_percentage ? number_format($sample->manganese_percentage, 3) : '-' }}</td>
                                        <td>
                                            <a href="{{ route('samples.show', $sample) }}" class="btn btn-outline-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-vial fa-3x mb-3"></i>
                        <p>Henüz prova kaydı yok</p>
                        @if($furnace->castings->where('status', 'active')->count() > 0)
                            <a href="{{ route('samples.create', ['furnace_id' => $furnace->id]) }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                İlk Provayı Ekle
                            </a>
                        @endif
                    </div>
                @endif
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
                    <input type="hidden" id="new_status" name="new_status">
                    
                    <div class="mb-3">
                        <label for="status_notes" class="form-label">Durum Değişikliği Notları</label>
                        <textarea class="form-control" id="status_notes" name="status_notes" rows="3" placeholder="Durum değişikliği hakkında notlarınız..."></textarea>
                    </div>
                    
                    <div class="mb-3" id="temperatureField" style="display: none;">
                        <label for="current_temperature" class="form-label">Mevcut Sıcaklık</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="current_temperature" name="current_temperature" step="0.1" placeholder="1500.0">
                            <span class="input-group-text">°C</span>
                        </div>
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

<!-- Sıcaklık Kayıt Modal'ı -->
<div class="modal fade" id="temperatureModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-thermometer-half text-danger"></i>
                    Sıcaklık Kaydı Ekle
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="temperatureForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sıcaklık (°C) *</label>
                            <input type="number" name="temperature" class="form-control" 
                                   min="0" max="2000" step="1" required
                                   placeholder="1600">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kayıt Tipi *</label>
                            <select name="log_type" class="form-select" required>
                                <option value="working">Çalışma</option>
                                <option value="shutdown">Kapatma</option>
                                <option value="maintenance">Bakım</option>
                                <option value="manual">Manuel Kayıt</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notlar</label>
                        <textarea name="notes" class="form-control" rows="3" 
                                  placeholder="Örnek: 1720 derecede devrildi"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Kaydı Yapan</label>
                        <input type="text" name="recorded_by" class="form-control" 
                               placeholder="Operatör adı">
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Bilgi:</strong> Bu kayıt ocağın sıcaklık geçmişine eklenecek.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-save"></i> Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Ocak durumu güncelle
function updateFurnaceStatus(newStatus) {
    document.getElementById('new_status').value = newStatus;
    
    const statusNames = {
        'active': 'Aktif',
        'idle': 'Beklemede',
        'maintenance': 'Bakımda',
        'inactive': 'Kapalı'
    };
    
    document.querySelector('#statusUpdateModal .modal-title').textContent = 
        `Ocak Durumunu "${statusNames[newStatus]}" Olarak Güncelle`;
    
    // Aktif yapılıyorsa sıcaklık alanını göster
    const tempField = document.getElementById('temperatureField');
    const tempInput = document.getElementById('current_temperature');
    
    if (newStatus === 'active') {
        tempField.style.display = 'block';
        tempInput.required = true;
        tempInput.value = '{{ $furnace->current_temperature ?? 1500 }}';
    } else {
        tempField.style.display = 'none';
        tempInput.required = false;
        if (newStatus === 'inactive') {
            tempInput.value = '0';
        }
    }
    
    const modal = new bootstrap.Modal(document.getElementById('statusUpdateModal'));
    modal.show();
}

// Durum güncelleme form submit
document.getElementById('statusUpdateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const newStatus = formData.get('new_status');
    const currentTemp = formData.get('current_temperature');
    
    // Aktif yapılırken sıcaklık kontrolü
    if (newStatus === 'active' && (!currentTemp || parseFloat(currentTemp) <= 0)) {
        showToast('Aktif ocaklar için geçerli bir sıcaklık değeri girin', 'warning');
        document.getElementById('current_temperature').focus();
        return;
    }
    
    console.log('Sending request to toggle furnace status:', {
        status: newStatus,
        status_notes: formData.get('status_notes'),
        current_temperature: currentTemp
    });
    
    fetch(`{{ route('api.furnaces.toggle', $furnace) }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            status: newStatus,
            status_notes: formData.get('status_notes'),
            current_temperature: currentTemp
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Response from server:', data);
        if (data.success) {
            showToast(data.message, 'success');
            
            // Etkilenen ocakları göster
            if (data.affected_furnaces && data.affected_furnaces.length > 0) {
                showToast(`Etkilenen ocaklar: ${data.affected_furnaces.join(', ')}`, 'info');
            }
            
            setTimeout(() => {
                location.reload();
            }, 1500);
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

// Otomatik yenileme (2 dakikada bir)
setInterval(() => {
    if (!document.hidden) {
        location.reload();
    }
}, 120000);

// Klavye kısayolları
document.addEventListener('keydown', function(e) {
    // F5: Yenile
    if (e.key === 'F5') {
        e.preventDefault();
        location.reload();
    }
    
    // Ctrl + E: Düzenle
    if (e.ctrlKey && e.key === 'e') {
        e.preventDefault();
        window.location.href = '{{ route("furnaces.edit", $furnace) }}';
    }
});

// Sıcaklık modal fonksiyonları
function showTemperatureModal() {
    const modal = new bootstrap.Modal(document.getElementById('temperatureModal'));
    modal.show();
}

// Sıcaklık form submit
document.getElementById('temperatureForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    
    // Loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Kaydediliyor...';
    
    fetch('{{ route("furnaces.add-temperature-log", $furnace) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Modal'ı kapat
            bootstrap.Modal.getInstance(document.getElementById('temperatureModal')).hide();
            
            // Success toast
            showToast(data.message, 'success');
            
            // Sayfayı yenile
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast(data.message || 'Sıcaklık kaydı eklenirken hata oluştu', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Sıcaklık kaydı eklenirken hata oluştu', 'error');
    })
    .finally(() => {
        // Reset button
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Kaydet';
    });
});
</script>
@endpush
