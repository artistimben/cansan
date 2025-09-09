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
                        Döküm Detayları
                    </h1>
                    <p class="text-muted mb-0">
                        <strong>{{ $casting->casting_number }}</strong> - {{ $casting->furnace->furnaceSet->name }} {{ $casting->furnace->name }}
                    </p>
                </div>
                <div>
                    <div class="btn-group">
                        <a href="{{ route('castings.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Geri Dön
                        </a>
                        @if($casting->status === 'active')
                            <button type="button" class="btn btn-success complete-casting" data-casting-id="{{ $casting->id }}">
                                <i class="fas fa-check"></i> Dökümü Tamamla
                            </button>
                            <button type="button" class="btn btn-danger cancel-casting" data-casting-id="{{ $casting->id }}">
                                <i class="fas fa-times"></i> Dökümü İptal Et
                            </button>
                        @endif
                        <a href="{{ route('samples.create', ['casting_id' => $casting->id]) }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Yeni Prova Ekle
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Sol Kolon -->
        <div class="col-md-8">
            <!-- Döküm Bilgileri -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> Döküm Bilgileri
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Döküm Numarası:</strong></td>
                                    <td>{{ $casting->casting_number }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Ocak:</strong></td>
                                    <td>{{ $casting->furnace->furnaceSet->name }} - {{ $casting->furnace->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tarih/Saat:</strong></td>
                                    <td>{{ $casting->casting_date->format('d.m.Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Vardiya:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $casting->shift === 'Gündüz' ? 'warning' : 'info' }}">
                                            {{ $casting->shift }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Operatör:</strong></td>
                                    <td>{{ $casting->operator_name }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Durum:</strong></td>
                                    <td>
                                        <span class="badge 
                                            @if($casting->status === 'active') bg-success
                                            @elseif($casting->status === 'completed') bg-primary
                                            @elseif($casting->status === 'cancelled') bg-danger
                                            @else bg-secondary
                                            @endif">
                                            @if($casting->status === 'active') 
                                                <i class="fas fa-play"></i> Aktif
                                            @elseif($casting->status === 'completed') 
                                                <i class="fas fa-check"></i> Tamamlandı
                                            @elseif($casting->status === 'cancelled') 
                                                <i class="fas fa-times"></i> İptal
                                            @else 
                                                {{ $casting->status }}
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Başlama Zamanı:</strong></td>
                                    <td>{{ $casting->started_at->format('d.m.Y H:i') }}</td>
                                </tr>
                                @if($casting->completed_at)
                                <tr>
                                    <td><strong>Bitiş Zamanı:</strong></td>
                                    <td>{{ $casting->completed_at->format('d.m.Y H:i') }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td><strong>Süre:</strong></td>
                                    <td>
                                        <span class="text-info">
                                            <i class="fas fa-clock"></i>
                                            {{ $stats['duration'] }} dakika
                                        </span>
                                    </td>
                                </tr>
                                @if($casting->target_temperature)
                                <tr>
                                    <td><strong>Hedef Sıcaklık:</strong></td>
                                    <td>
                                        <span class="text-warning">
                                            <i class="fas fa-thermometer-half"></i>
                                            {{ $casting->target_temperature }}°C
                                        </span>
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                    @if($casting->notes)
                        <div class="mt-3">
                            <strong>Notlar:</strong>
                            <div class="alert alert-info mt-2">
                                {{ $casting->notes }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Provalar -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-vial"></i> Provalar
                        <span class="badge bg-primary ms-2">{{ $stats['total_samples'] }}</span>
                    </h5>
                    <a href="{{ route('samples.create', ['casting_id' => $casting->id]) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Prova Ekle
                    </a>
                </div>
                <div class="card-body">
                    @if($casting->samples->count() > 0)
                        <div class="row">
                            @foreach($casting->samples->sortBy('sample_number') as $sample)
                                <div class="col-md-6 mb-3">
                                    <div class="card border-start border-4 
                                        @if($sample->quality_status === 'approved') border-success
                                        @elseif($sample->quality_status === 'rejected') border-danger
                                        @elseif($sample->quality_status === 'pending') border-warning
                                        @elseif($sample->quality_status === 'needs_adjustment') border-info
                                        @else border-secondary
                                        @endif">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-vial"></i>
                                                    {{ $sample->sample_number }}. Prova
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
                                            
                                            <div class="row text-center mb-2">
                                                <div class="col-4">
                                                    <small class="text-muted">Karbon</small>
                                                    <div><strong>{{ number_format($sample->carbon_content, 2) }}%</strong></div>
                                                </div>
                                                <div class="col-4">
                                                    <small class="text-muted">Mangan</small>
                                                    <div><strong>{{ number_format($sample->manganese_content, 2) }}%</strong></div>
                                                </div>
                                                <div class="col-4">
                                                    <small class="text-muted">Sıcaklık</small>
                                                    <div><strong>{{ $sample->temperature }}°C</strong></div>
                                                </div>
                                            </div>
                                            
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    <i class="fas fa-clock"></i>
                                                    {{ $sample->sample_time->format('H:i') }}
                                                </small>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('samples.show', $sample) }}" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="Detay">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('samples.edit', $sample) }}" 
                                                       class="btn btn-sm btn-outline-warning" 
                                                       title="Düzenle">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-vial fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">Henüz prova alınmamış</h6>
                            <p class="text-muted">Bu döküm için prova eklemek üzere aşağıdaki butona tıklayın</p>
                            <a href="{{ route('samples.create', ['casting_id' => $casting->id]) }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> İlk Provayı Ekle
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sağ Kolon -->
        <div class="col-md-4">
            <!-- İstatistikler -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-pie"></i> Döküm İstatistikleri
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary mb-1">{{ $stats['total_samples'] }}</h4>
                                <small class="text-muted">Toplam Prova</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success mb-1">{{ $stats['approved_samples'] }}</h4>
                            <small class="text-muted">Onaylanan</small>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-warning mb-1">{{ $stats['pending_samples'] }}</h4>
                                <small class="text-muted">Beklemede</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-danger mb-1">{{ $stats['rejected_samples'] }}</h4>
                            <small class="text-muted">Reddedilen</small>
                        </div>
                    </div>
                    
                    @if($stats['total_samples'] > 0)
                        <hr>
                        <div class="progress mb-2" style="height: 8px;">
                            <div class="progress-bar bg-success" 
                                 style="width: {{ ($stats['approved_samples'] / $stats['total_samples']) * 100 }}%"></div>
                            <div class="progress-bar bg-warning" 
                                 style="width: {{ ($stats['pending_samples'] / $stats['total_samples']) * 100 }}%"></div>
                            <div class="progress-bar bg-danger" 
                                 style="width: {{ ($stats['rejected_samples'] / $stats['total_samples']) * 100 }}%"></div>
                        </div>
                        <small class="text-muted">
                            Onay oranı: %{{ number_format(($stats['approved_samples'] / $stats['total_samples']) * 100, 1) }}
                        </small>
                    @endif
                </div>
            </div>

            <!-- Ocak Bilgileri -->
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-fire"></i> Ocak Bilgileri
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-4"><strong>Set:</strong></div>
                        <div class="col-8">{{ $casting->furnace->furnaceSet->name }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4"><strong>Ocak:</strong></div>
                        <div class="col-8">{{ $casting->furnace->name }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4"><strong>Durum:</strong></div>
                        <div class="col-8">
                            <span class="badge bg-{{ $casting->furnace->status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($casting->furnace->status) }}
                            </span>
                        </div>
                    </div>
                    @if($casting->furnace->current_temperature)
                    <div class="row mb-2">
                        <div class="col-4"><strong>Sıcaklık:</strong></div>
                        <div class="col-8">
                            <span class="text-danger">
                                <i class="fas fa-thermometer-half"></i>
                                {{ $casting->furnace->current_temperature }}°C
                            </span>
                        </div>
                    </div>
                    @endif
                    <div class="row">
                        <div class="col-4"><strong>Kapasite:</strong></div>
                        <div class="col-8">{{ $casting->furnace->capacity ?? 'N/A' }} ton</div>
                    </div>
                </div>
            </div>

            <!-- Hızlı İşlemler -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-bolt"></i> Hızlı İşlemler
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('samples.create', ['casting_id' => $casting->id]) }}" 
                           class="btn btn-primary">
                            <i class="fas fa-plus"></i> Yeni Prova Ekle
                        </a>
                        @if($casting->status === 'active')
                            <button type="button" class="btn btn-success complete-casting" 
                                    data-casting-id="{{ $casting->id }}">
                                <i class="fas fa-check"></i> Dökümü Tamamla
                            </button>
                        @endif
                        <a href="{{ route('castings.edit', $casting) }}" class="btn btn-outline-warning">
                            <i class="fas fa-edit"></i> Döküm Bilgilerini Düzenle
                        </a>
                        <a href="{{ route('reports.daily', ['casting_id' => $casting->id]) }}" 
                           class="btn btn-outline-info">
                            <i class="fas fa-file-alt"></i> Döküm Raporu
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal'lar -->
@include('castings.partials.complete-modal')
@include('castings.partials.cancel-modal')

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Döküm tamamlama
    $('.complete-casting').click(function() {
        const castingId = $(this).data('casting-id');
        $('#completeCastingModal').modal('show');
        $('#completeCastingForm').attr('action', `/castings/${castingId}/complete`);
    });

    // Döküm iptal etme
    $('.cancel-casting').click(function() {
        const castingId = $(this).data('casting-id');
        $('#cancelCastingModal').modal('show');
        $('#cancelCastingForm').attr('action', `/castings/${castingId}/cancel`);
    });
    
    // Hızlı neden seçimi
    $('.cancel-reason-btn').on('click', function() {
        const reason = $(this).data('reason');
        $('#cancellation_reason').val(reason);
        $(this).closest('.list-group').find('.list-group-item').removeClass('active');
        $(this).addClass('active');
    });

    // Complete form submit
    $('#completeCastingForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const url = form.attr('action');
        const formData = form.serialize();
        
        // Loading state
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Tamamlanıyor...').prop('disabled', true);
        
        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            },
            error: function(xhr) {
                alert('Bir hata oluştu: ' + (xhr.responseJSON?.message || 'Bilinmeyen hata'));
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });

    // Cancel form submit
    $('#cancelCastingForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const url = form.attr('action');
        const formData = form.serialize();
        
        // Loading state
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> İptal Ediliyor...').prop('disabled', true);
        
        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            },
            error: function(xhr) {
                alert('Bir hata oluştu: ' + (xhr.responseJSON?.message || 'Bilinmeyen hata'));
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
    
    // Otomatik yenileme (aktif döküm için)
    @if($casting->status === 'active')
        setInterval(function() {
            location.reload();
        }, 30000); // 30 saniyede bir yenile
    @endif
});
</script>
@endpush
