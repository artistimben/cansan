@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Başlık -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-edit text-primary"></i>
                        Prova Düzenle
                    </h1>
                    <p class="text-muted mb-0">Prova #{{ $sample->sample_number }} bilgilerini düzenleyin</p>
                </div>
                <div>
                    <a href="{{ route('samples.show', $sample) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Provaya Dön
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Sol Kolon - Form -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-flask"></i> Prova Bilgileri
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('samples.update', $sample) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- Prova Numarası -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="sample_number" class="form-label">
                                    <i class="fas fa-hashtag text-primary"></i> Prova Numarası *
                                </label>
                                <input type="text" class="form-control @error('sample_number') is-invalid @enderror" 
                                       id="sample_number" name="sample_number" value="{{ old('sample_number', $sample->sample_number) }}" required>
                                @error('sample_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="sample_time" class="form-label">
                                    <i class="fas fa-clock text-info"></i> Prova Zamanı *
                                </label>
                                <input type="datetime-local" class="form-control @error('sample_time') is-invalid @enderror" 
                                       id="sample_time" name="sample_time" 
                                       value="{{ old('sample_time', \Carbon\Carbon::parse($sample->sample_time)->format('Y-m-d\TH:i')) }}" required>
                                @error('sample_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Döküm Bilgileri (Readonly) -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Döküm Numarası</label>
                                <input type="text" class="form-control" value="{{ $sample->casting->casting_number }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ocak</label>
                                <input type="text" class="form-control" 
                                       value="{{ $sample->casting->furnace->furnaceSet->name }} - {{ $sample->casting->furnace->name }}" readonly>
                            </div>
                        </div>

                        <!-- Kimyasal Analiz -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-atom"></i> Kimyasal Analiz Sonuçları
                                </h6>
                            </div>
                            <div class="col-md-6 col-lg-3 mb-3">
                                <label for="carbon_content" class="form-label">Karbon (C) %</label>
                                <input type="number" step="0.01" class="form-control @error('carbon_content') is-invalid @enderror" 
                                       id="carbon_content" name="carbon_content" 
                                       value="{{ old('carbon_content', $sample->carbon_content) }}">
                                @error('carbon_content')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 col-lg-3 mb-3">
                                <label for="manganese_content" class="form-label">Manganez (Mn) %</label>
                                <input type="number" step="0.01" class="form-control @error('manganese_content') is-invalid @enderror" 
                                       id="manganese_content" name="manganese_content" 
                                       value="{{ old('manganese_content', $sample->manganese_content) }}">
                                @error('manganese_content')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 col-lg-3 mb-3">
                                <label for="phosphorus_percentage" class="form-label">Fosfor (P) %</label>
                                <input type="number" step="0.01" class="form-control @error('phosphorus_percentage') is-invalid @enderror" 
                                       id="phosphorus_percentage" name="phosphorus_percentage" 
                                       value="{{ old('phosphorus_percentage', $sample->phosphorus_percentage) }}">
                                @error('phosphorus_percentage')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 col-lg-3 mb-3">
                                <label for="sulfur_percentage" class="form-label">Kükürt (S) %</label>
                                <input type="number" step="0.01" class="form-control @error('sulfur_percentage') is-invalid @enderror" 
                                       id="sulfur_percentage" name="sulfur_percentage" 
                                       value="{{ old('sulfur_percentage', $sample->sulfur_percentage) }}">
                                @error('sulfur_percentage')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Diğer Elementler -->
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <label for="chromium_percentage" class="form-label">Krom (Cr) %</label>
                                <input type="number" step="0.01" class="form-control @error('chromium_percentage') is-invalid @enderror" 
                                       id="chromium_percentage" name="chromium_percentage" 
                                       value="{{ old('chromium_percentage', $sample->chromium_percentage) }}">
                                @error('chromium_percentage')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="nickel_percentage" class="form-label">Nikel (Ni) %</label>
                                <input type="number" step="0.01" class="form-control @error('nickel_percentage') is-invalid @enderror" 
                                       id="nickel_percentage" name="nickel_percentage" 
                                       value="{{ old('nickel_percentage', $sample->nickel_percentage) }}">
                                @error('nickel_percentage')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="molybdenum_percentage" class="form-label">Molibden (Mo) %</label>
                                <input type="number" step="0.01" class="form-control @error('molybdenum_percentage') is-invalid @enderror" 
                                       id="molybdenum_percentage" name="molybdenum_percentage" 
                                       value="{{ old('molybdenum_percentage', $sample->molybdenum_percentage) }}">
                                @error('molybdenum_percentage')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Kalite Durumu -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="quality_status" class="form-label">
                                    <i class="fas fa-check-circle text-success"></i> Kalite Durumu *
                                </label>
                                <select class="form-select @error('quality_status') is-invalid @enderror" 
                                        id="quality_status" name="quality_status" required>
                                    <option value="pending" {{ old('quality_status', $sample->quality_status) === 'pending' ? 'selected' : '' }}>Beklemede</option>
                                    <option value="approved" {{ old('quality_status', $sample->quality_status) === 'approved' ? 'selected' : '' }}>Onaylandı</option>
                                    <option value="rejected" {{ old('quality_status', $sample->quality_status) === 'rejected' ? 'selected' : '' }}>Reddedildi</option>
                                    <option value="needs_adjustment" {{ old('quality_status', $sample->quality_status) === 'needs_adjustment' ? 'selected' : '' }}>Ayarlama Gerekli</option>
                                </select>
                                @error('quality_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="quality_standard_id" class="form-label">
                                    <i class="fas fa-ruler text-info"></i> Kalite Standardı
                                </label>
                                <select class="form-select @error('quality_standard_id') is-invalid @enderror" 
                                        id="quality_standard_id" name="quality_standard_id">
                                    <option value="">Kalite standardı seçiniz...</option>
                                    @foreach($qualityStandards as $standard)
                                        <option value="{{ $standard->id }}" 
                                                {{ old('quality_standard_id', $sample->quality_standard_id) == $standard->id ? 'selected' : '' }}>
                                            {{ $standard->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('quality_standard_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Operatör ve Notlar -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="operator_name" class="form-label">
                                    <i class="fas fa-user text-warning"></i> Operatör Adı
                                </label>
                                <input type="text" class="form-control @error('operator_name') is-invalid @enderror" 
                                       id="operator_name" name="operator_name" 
                                       value="{{ old('operator_name', $sample->operator_name) }}">
                                @error('operator_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="temperature" class="form-label">
                                    <i class="fas fa-thermometer-half text-danger"></i> Sıcaklık (°C)
                                </label>
                                <input type="number" step="0.1" class="form-control @error('temperature') is-invalid @enderror" 
                                       id="temperature" name="temperature" 
                                       value="{{ old('temperature', $sample->temperature) }}">
                                @error('temperature')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Notlar -->
                        <div class="mb-4">
                            <label for="notes" class="form-label">
                                <i class="fas fa-sticky-note text-secondary"></i> Notlar
                            </label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="4" 
                                      placeholder="Prova hakkında notlar...">{{ old('notes', $sample->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Butonlar -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Değişiklikleri Kaydet
                            </button>
                            <a href="{{ route('samples.show', $sample) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> İptal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sağ Kolon - Bilgiler -->
        <div class="col-md-4">
            <!-- Döküm Bilgileri -->
            <div class="card">
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
                </div>
            </div>

            <!-- Mevcut Değerler -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar"></i> Mevcut Değerler
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-2">
                            <div class="p-2 border rounded">
                                <small class="text-muted">Karbon</small>
                                <div class="fw-bold">{{ $sample->carbon_content ?? '-' }}%</div>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="p-2 border rounded">
                                <small class="text-muted">Manganez</small>
                                <div class="fw-bold">{{ $sample->manganese_content ?? '-' }}%</div>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="p-2 border rounded">
                                <small class="text-muted">Fosfor</small>
                                <div class="fw-bold">{{ $sample->phosphorus_percentage ?? '-' }}%</div>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="p-2 border rounded">
                                <small class="text-muted">Kükürt</small>
                                <div class="fw-bold">{{ $sample->sulfur_percentage ?? '-' }}%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
