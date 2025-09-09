@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Başlık -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-plus text-primary"></i>
                        Yeni Prova Ekle
                    </h1>
                    <p class="text-muted mb-0">
                        @if($selectedCasting)
                            <strong>{{ $selectedCasting->casting_number }}</strong> dökümü için prova ekleyin
                        @else
                            Döküm seçerek prova ekleyin
                        @endif
                    </p>
                </div>
                <div>
                    <a href="{{ $selectedCasting ? route('castings.show', $selectedCasting) : route('samples.index') }}" 
                       class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Geri Dön
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
                        <i class="fas fa-vial"></i> Prova Bilgileri
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('samples.store') }}" method="POST" id="sampleForm">
                        @csrf
                        
                        <!-- Döküm Seçimi -->
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <label for="casting_id" class="form-label">
                                    <i class="fas fa-fire text-primary"></i> Döküm Seçimi *
                                </label>
                                <select name="casting_id" id="casting_id" class="form-select @error('casting_id') is-invalid @enderror" required>
                                    <option value="">Döküm seçiniz...</option>
                                    @foreach($activeCastings as $casting)
                                        <option value="{{ $casting->id }}" 
                                            {{ (old('casting_id', $selectedCasting?->id) == $casting->id) ? 'selected' : '' }}
                                            data-furnace-name="{{ $casting->furnace->name }}"
                                            data-casting-number="{{ $casting->casting_number }}"
                                            data-sample-count="{{ $casting->samples->count() }}"
                                            data-operator="{{ $casting->operator_name }}"
                                            data-shift="{{ $casting->shift }}">
                                            {{ $casting->casting_number }} - {{ $casting->furnace->furnaceSet->name }} {{ $casting->furnace->name }}
                                            ({{ $casting->samples->count() }} prova)
                                        </option>
                                    @endforeach
                                </select>
                                @error('casting_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="sample_number" class="form-label">
                                    <i class="fas fa-hashtag text-info"></i> Prova Numarası *
                                </label>
                                <div class="input-group">
                                    <input type="number" 
                                           name="sample_number" 
                                           id="sample_number" 
                                           class="form-control @error('sample_number') is-invalid @enderror" 
                                           value="{{ old('sample_number') }}" 
                                           min="1" 
                                           max="999" 
                                           readonly
                                           placeholder="Otomatik">
                                    <span class="input-group-text">. PROVA</span>
                                </div>
                                @error('sample_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> 
                                    Prova numarası otomatik oluşturulur
                                </small>
                            </div>
                        </div>

                        <!-- Prova Zamanı -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="sample_date" class="form-label">
                                    <i class="fas fa-calendar text-success"></i> Prova Tarihi *
                                </label>
                                <input type="date" 
                                       name="sample_date" 
                                       id="sample_date" 
                                       class="form-control @error('sample_date') is-invalid @enderror" 
                                       value="{{ old('sample_date', date('Y-m-d')) }}" 
                                       required>
                                @error('sample_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="sample_time" class="form-label">
                                    <i class="fas fa-clock text-warning"></i> Prova Saati *
                                </label>
                                <input type="time" 
                                       name="sample_time" 
                                       id="sample_time" 
                                       class="form-control @error('sample_time') is-invalid @enderror" 
                                       value="{{ old('sample_time', date('H:i')) }}" 
                                       required>
                                @error('sample_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Kimyasal Analiz -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-flask text-info"></i> Kimyasal Analiz Değerleri
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="carbon_content" class="form-label">
                                                <i class="fas fa-atom text-dark"></i> Karbon (%) *
                                            </label>
                                            <div class="input-group">
                                                <input type="number" 
                                                       name="carbon_content" 
                                                       id="carbon_content" 
                                                       class="form-control @error('carbon_content') is-invalid @enderror" 
                                                       value="{{ old('carbon_content') }}" 
                                                       step="0.01" 
                                                       min="0" 
                                                       max="5" 
                                                       placeholder="0.45"
                                                       required>
                                                <span class="input-group-text">%</span>
                                            </div>
                                            @error('carbon_content')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="manganese_content" class="form-label">
                                                <i class="fas fa-atom text-secondary"></i> Mangan (%) *
                                            </label>
                                            <div class="input-group">
                                                <input type="number" 
                                                       name="manganese_content" 
                                                       id="manganese_content" 
                                                       class="form-control @error('manganese_content') is-invalid @enderror" 
                                                       value="{{ old('manganese_content') }}" 
                                                       step="0.01" 
                                                       min="0" 
                                                       max="5" 
                                                       placeholder="0.65"
                                                       required>
                                                <span class="input-group-text">%</span>
                                            </div>
                                            @error('manganese_content')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="silicon_content" class="form-label">
                                                <i class="fas fa-atom text-primary"></i> Silisyum (%)
                                            </label>
                                            <div class="input-group">
                                                <input type="number" 
                                                       name="silicon_content" 
                                                       id="silicon_content" 
                                                       class="form-control @error('silicon_content') is-invalid @enderror" 
                                                       value="{{ old('silicon_content') }}" 
                                                       step="0.01" 
                                                       min="0" 
                                                       max="3" 
                                                       placeholder="0.25">
                                                <span class="input-group-text">%</span>
                                            </div>
                                            @error('silicon_content')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="temperature" class="form-label">
                                                <i class="fas fa-thermometer-half text-danger"></i> Sıcaklık (°C) *
                                            </label>
                                            <div class="input-group">
                                                <input type="number" 
                                                       name="temperature" 
                                                       id="temperature" 
                                                       class="form-control @error('temperature') is-invalid @enderror" 
                                                       value="{{ old('temperature') }}" 
                                                       min="0" 
                                                       max="3000" 
                                                       placeholder="1650"
                                                       required>
                                                <span class="input-group-text">°C</span>
                                            </div>
                                            @error('temperature')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Prova Türü ve Kalite -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="sample_type" class="form-label">
                                    <i class="fas fa-tags text-info"></i> Prova Türü
                                </label>
                                <select name="sample_type" id="sample_type" class="form-select @error('sample_type') is-invalid @enderror">
                                    <option value="regular" {{ old('sample_type', 'regular') === 'regular' ? 'selected' : '' }}>
                                        🔬 Normal Prova
                                    </option>
                                    <option value="ladle" {{ old('sample_type') === 'ladle' ? 'selected' : '' }}>
                                        🥄 Pota Prova
                                    </option>
                                    <option value="final" {{ old('sample_type') === 'final' ? 'selected' : '' }}>
                                        ✅ Son Prova
                                    </option>
                                    <option value="control" {{ old('sample_type') === 'control' ? 'selected' : '' }}>
                                        🔍 Kontrol Prova
                                    </option>
                                </select>
                                @error('sample_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="quality_standard_id" class="form-label">
                                    <i class="fas fa-star text-warning"></i> Kalite Standardı
                                </label>
                                <select name="quality_standard_id" id="quality_standard_id" class="form-select @error('quality_standard_id') is-invalid @enderror">
                                    <option value="">Standart seçiniz...</option>
                                    @foreach($qualityStandards as $standard)
                                        <option value="{{ $standard->id }}" 
                                            {{ old('quality_standard_id') == $standard->id ? 'selected' : '' }}
                                            data-carbon-min="{{ $standard->carbon_min }}"
                                            data-carbon-max="{{ $standard->carbon_max }}"
                                            data-manganese-min="{{ $standard->manganese_min }}"
                                            data-manganese-max="{{ $standard->manganese_max }}">
                                            {{ $standard->name }}
                                            (C: {{ $standard->carbon_min }}-{{ $standard->carbon_max }}%, 
                                             Mn: {{ $standard->manganese_min }}-{{ $standard->manganese_max }}%)
                                        </option>
                                    @endforeach
                                </select>
                                @error('quality_standard_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="sampled_by" class="form-label">
                                    <i class="fas fa-user text-primary"></i> Prova Alan
                                </label>
                                <input type="text" 
                                       name="sampled_by" 
                                       id="sampled_by" 
                                       class="form-control @error('sampled_by') is-invalid @enderror" 
                                       value="{{ old('sampled_by') }}" 
                                       placeholder="Prova alan personel adı">
                                @error('sampled_by')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Notlar -->
                        <div class="mb-4">
                            <label for="notes" class="form-label">
                                <i class="fas fa-sticky-note text-warning"></i> Notlar ve Gözlemler
                            </label>
                            <textarea name="notes" 
                                      id="notes" 
                                      class="form-control @error('notes') is-invalid @enderror" 
                                      rows="3" 
                                      placeholder="Prova ile ilgili özel notlar, gözlemler, anormallikler...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ $selectedCasting ? route('castings.show', $selectedCasting) : route('samples.index') }}" 
                               class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> İptal
                            </a>
                            <div>
                                <button type="submit" name="action" value="save" class="btn btn-primary me-2">
                                    <i class="fas fa-save"></i> Provayı Kaydet
                                </button>
                                <button type="submit" name="action" value="save_and_add" class="btn btn-success">
                                    <i class="fas fa-plus"></i> Kaydet ve Yeni Prova Ekle
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sağ Kolon -->
        <div class="col-md-4">
            <!-- Seçilen Döküm Bilgisi -->
            <div class="card mb-3" id="casting-info-card" style="display: none;">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-fire"></i> Seçilen Döküm
                    </h6>
                </div>
                <div class="card-body">
                    <div id="selected-casting-info">
                        <!-- JavaScript ile doldurulacak -->
                    </div>
                </div>
            </div>

            <!-- Kalite Standart Bilgisi -->
            <div class="card mb-3" id="quality-standard-card" style="display: none;">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-star"></i> Kalite Standardı
                    </h6>
                </div>
                <div class="card-body">
                    <div id="quality-standard-info">
                        <!-- JavaScript ile doldurulacak -->
                    </div>
                </div>
            </div>

            <!-- Yardım -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-question-circle text-info"></i> Yardım
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-vial"></i> Prova Türleri</h6>
                        <ul class="mb-0 small">
                            <li><strong>Normal Prova:</strong> Rutin kontrol</li>
                            <li><strong>Pota Prova:</strong> Pota içeriği kontrolü</li>
                            <li><strong>Son Prova:</strong> Döküm bitiş kontrolü</li>
                            <li><strong>Kontrol Prova:</strong> Ek doğrulama</li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Önemli</h6>
                        <ul class="mb-0 small">
                            <li>Karbon ve Mangan değerleri zorunludur</li>
                            <li>Sıcaklık değeri mutlaka girilmelidir</li>
                            <li>Prova numarası otomatik oluşturulur</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Döküm seçimi değiştiğinde
    $('#casting_id').change(function() {
        const selectedOption = $(this).find(':selected');
        const castingId = $(this).val();
        
        if (castingId) {
            // Döküm bilgilerini al
            const furnaceName = selectedOption.data('furnace-name');
            const castingNumber = selectedOption.data('casting-number');
            const sampleCount = selectedOption.data('sample-count');
            const operator = selectedOption.data('operator');
            const shift = selectedOption.data('shift');
            
            // Prova numarası oluştur
            const nextSampleNumber = sampleCount + 1;
            $('#sample_number').val(nextSampleNumber);
            
            // Döküm bilgi kartını göster
            showCastingInfo(furnaceName, castingNumber, sampleCount, operator, shift, nextSampleNumber);
        } else {
            // Bilgileri temizle
            $('#casting-info-card').hide();
            $('#sample_number').val('');
        }
    });
    
    // Kalite standardı seçimi değiştiğinde
    $('#quality_standard_id').change(function() {
        const selectedOption = $(this).find(':selected');
        const standardId = $(this).val();
        
        if (standardId) {
            const carbonMin = selectedOption.data('carbon-min');
            const carbonMax = selectedOption.data('carbon-max');
            const manganeseMin = selectedOption.data('manganese-min');
            const manganeseMax = selectedOption.data('manganese-max');
            
            showQualityStandardInfo(selectedOption.text(), carbonMin, carbonMax, manganeseMin, manganeseMax);
        } else {
            $('#quality-standard-card').hide();
        }
    });
    
    // Sayfa yüklendiğinde seçili döküm varsa bilgileri göster
    if ($('#casting_id').val()) {
        $('#casting_id').trigger('change');
    }
    
    if ($('#quality_standard_id').val()) {
        $('#quality_standard_id').trigger('change');
    }
});

function showCastingInfo(furnaceName, castingNumber, sampleCount, operator, shift, nextSampleNumber) {
    const infoHtml = `
        <div class="row mb-2">
            <div class="col-4"><strong>Döküm:</strong></div>
            <div class="col-8">${castingNumber}</div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><strong>Ocak:</strong></div>
            <div class="col-8">${furnaceName}</div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><strong>Operatör:</strong></div>
            <div class="col-8">${operator}</div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><strong>Vardiya:</strong></div>
            <div class="col-8">
                <span class="badge bg-${shift === 'Gündüz' ? 'warning' : 'info'}">${shift}</span>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><strong>Mevcut:</strong></div>
            <div class="col-8">${sampleCount} prova</div>
        </div>
        <div class="row">
            <div class="col-4"><strong>Yeni:</strong></div>
            <div class="col-8">
                <span class="badge bg-primary">${nextSampleNumber}. PROVA</span>
            </div>
        </div>
    `;
    
    $('#selected-casting-info').html(infoHtml);
    $('#casting-info-card').show();
}

function showQualityStandardInfo(standardName, carbonMin, carbonMax, manganeseMin, manganeseMax) {
    const infoHtml = `
        <h6>${standardName}</h6>
        <div class="row mb-2">
            <div class="col-4"><strong>Karbon:</strong></div>
            <div class="col-8">${carbonMin}% - ${carbonMax}%</div>
        </div>
        <div class="row">
            <div class="col-4"><strong>Mangan:</strong></div>
            <div class="col-8">${manganeseMin}% - ${manganeseMax}%</div>
        </div>
        <hr>
        <small class="text-muted">
            <i class="fas fa-info-circle"></i> 
            Girilen değerler bu aralıklarda olmalıdır
        </small>
    `;
    
    $('#quality-standard-info').html(infoHtml);
    $('#quality-standard-card').show();
}
</script>
@endpush