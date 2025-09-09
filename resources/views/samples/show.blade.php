@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Başlık -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-flask text-primary"></i>
                        Prova Detayları
                    </h1>
                    <p class="text-muted mb-0">Prova #{{ $sample->sample_number }} detay bilgileri</p>
                </div>
                <div>
                    <a href="{{ route('samples.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Provaya Dön
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Sol Kolon - Prova Bilgileri -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> Prova Bilgileri
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Prova Numarası</label>
                                <p class="form-control-plaintext">{{ $sample->sample_number }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Döküm Numarası</label>
                                <p class="form-control-plaintext">
                                    <a href="{{ route('castings.show', $sample->casting) }}" class="text-decoration-none">
                                        {{ $sample->casting->casting_number }}
                                    </a>
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Ocak</label>
                                <p class="form-control-plaintext">
                                    <a href="{{ route('furnaces.show', $sample->casting->furnace) }}" class="text-decoration-none">
                                        {{ $sample->casting->furnace->furnaceSet->name }} - {{ $sample->casting->furnace->name }}
                                    </a>
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Prova Zamanı</label>
                                <p class="form-control-plaintext">{{ \Carbon\Carbon::parse($sample->sample_time)->format('d.m.Y H:i:s') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Kalite Durumu</label>
                                <p class="form-control-plaintext">
                                    @if($sample->quality_status === 'approved')
                                        <span class="badge bg-success fs-6">
                                            <i class="fas fa-check"></i> Onaylandı
                                        </span>
                                    @elseif($sample->quality_status === 'rejected')
                                        <span class="badge bg-danger fs-6">
                                            <i class="fas fa-times"></i> Reddedildi
                                        </span>
                                    @elseif($sample->quality_status === 'needs_adjustment')
                                        <span class="badge bg-warning fs-6">
                                            <i class="fas fa-exclamation-triangle"></i> Ayarlama Gerekli
                                        </span>
                                    @else
                                        <span class="badge bg-secondary fs-6">
                                            <i class="fas fa-clock"></i> Beklemede
                                        </span>
                                    @endif
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Kalite Standardı</label>
                                <p class="form-control-plaintext">
                                    @if($sample->qualityStandard)
                                        {{ $sample->qualityStandard->name }}
                                    @else
                                        <span class="text-muted">Belirtilmemiş</span>
                                    @endif
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Operatör</label>
                                <p class="form-control-plaintext">{{ $sample->operator_name ?? 'Belirtilmemiş' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Oluşturulma Tarihi</label>
                                <p class="form-control-plaintext">{{ \Carbon\Carbon::parse($sample->created_at)->format('d.m.Y H:i:s') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kimyasal Analiz Sonuçları -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-atom"></i> Kimyasal Analiz Sonuçları
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-muted mb-1">Karbon (C)</h6>
                                <h4 class="text-primary mb-0">{{ $sample->carbon_content ?? '-' }}%</h4>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-muted mb-1">Manganez (Mn)</h6>
                                <h4 class="text-info mb-0">{{ $sample->manganese_content ?? '-' }}%</h4>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-muted mb-1">Fosfor (P)</h6>
                                <h4 class="text-warning mb-0">{{ $sample->phosphorus_percentage ?? '-' }}%</h4>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-muted mb-1">Kükürt (S)</h6>
                                <h4 class="text-danger mb-0">{{ $sample->sulfur_percentage ?? '-' }}%</h4>
                            </div>
                        </div>
                    </div>
                    
                    @if($sample->chromium_percentage || $sample->nickel_percentage || $sample->molybdenum_percentage)
                    <div class="row mt-3">
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-muted mb-1">Krom (Cr)</h6>
                                <h5 class="text-secondary mb-0">{{ $sample->chromium_percentage ?? '-' }}%</h5>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-muted mb-1">Nikel (Ni)</h6>
                                <h5 class="text-secondary mb-0">{{ $sample->nickel_percentage ?? '-' }}%</h5>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-muted mb-1">Molibden (Mo)</h6>
                                <h5 class="text-secondary mb-0">{{ $sample->molybdenum_percentage ?? '-' }}%</h5>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Notlar -->
            @if($sample->notes)
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-sticky-note"></i> Notlar
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $sample->notes }}</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Sağ Kolon - İşlemler ve Bilgiler -->
        <div class="col-lg-4">
            <!-- Kalite Durumu Güncelleme -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-edit"></i> Kalite Durumu Güncelle
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('samples.update', $sample) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">Yeni Durum</label>
                            <select name="quality_status" class="form-select" required>
                                <option value="pending" {{ $sample->quality_status === 'pending' ? 'selected' : '' }}>Beklemede</option>
                                <option value="approved" {{ $sample->quality_status === 'approved' ? 'selected' : '' }}>Onaylandı</option>
                                <option value="rejected" {{ $sample->quality_status === 'rejected' ? 'selected' : '' }}>Reddedildi</option>
                                <option value="needs_adjustment" {{ $sample->quality_status === 'needs_adjustment' ? 'selected' : '' }}>Ayarlama Gerekli</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Güncelleme Notu</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Durum güncelleme notu..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Durumu Güncelle
                        </button>
                    </form>
                </div>
            </div>

            <!-- Döküm Bilgileri -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-fire"></i> Döküm Bilgileri
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Döküm No:</strong> {{ $sample->casting->casting_number }}
                    </div>
                    <div class="mb-2">
                        <strong>Vardiya:</strong> {{ $sample->casting->shift }}
                    </div>
                    <div class="mb-2">
                        <strong>Operatör:</strong> {{ $sample->casting->operator_name ?? 'Belirtilmemiş' }}
                    </div>
                    <div class="mb-2">
                        <strong>Durum:</strong> 
                        <span class="badge bg-{{ $sample->casting->status === 'active' ? 'success' : ($sample->casting->status === 'completed' ? 'primary' : 'secondary') }}">
                            {{ ucfirst($sample->casting->status) }}
                        </span>
                    </div>
                    <div class="mb-2">
                        <strong>Başlangıç:</strong> {{ \Carbon\Carbon::parse($sample->casting->started_at)->format('d.m.Y H:i') }}
                    </div>
                    @if($sample->casting->completed_at)
                    <div class="mb-2">
                        <strong>Tamamlanma:</strong> {{ \Carbon\Carbon::parse($sample->casting->completed_at)->format('d.m.Y H:i') }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Hızlı İşlemler -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt"></i> Hızlı İşlemler
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('samples.edit', $sample) }}" class="btn btn-outline-primary">
                            <i class="fas fa-edit"></i> Provayı Düzenle
                        </a>
                        <a href="{{ route('castings.show', $sample->casting) }}" class="btn btn-outline-info">
                            <i class="fas fa-fire"></i> Dökümü Görüntüle
                        </a>
                        <a href="{{ route('furnaces.show', $sample->casting->furnace) }}" class="btn btn-outline-warning">
                            <i class="fas fa-cog"></i> Ocağı Görüntüle
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
