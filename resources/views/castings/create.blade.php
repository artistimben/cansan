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
                        Yeni Döküm Başlat
                    </h1>
                    <p class="text-muted mb-0">Ocaktan yeni döküm başlatın ve takip edin</p>
                </div>
                <div>
                    <a href="{{ route('castings.index') }}" class="btn btn-outline-secondary">
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
                        <i class="fas fa-fire"></i> Döküm Bilgileri
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('castings.store') }}" method="POST" id="castingForm">
                        @csrf
                        
                        <!-- Ocak Seçimi -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="furnace_id" class="form-label">
                                    <i class="fas fa-fire text-primary"></i> Ocak Seçimi *
                                </label>
                                <select name="furnace_id" id="furnace_id" class="form-select @error('furnace_id') is-invalid @enderror" required>
                                    <option value="">Ocak seçiniz...</option>
                                    @foreach($activeFurnaces as $furnace)
                                        <option value="{{ $furnace->id }}" 
                                            {{ (old('furnace_id', $selectedFurnace?->id) == $furnace->id) ? 'selected' : '' }}
                                            data-set-name="{{ $furnace->furnaceSet->name }}"
                                            data-furnace-name="{{ $furnace->name }}"
                                            data-current-temp="{{ $furnace->current_temperature }}"
                                            data-max-temp="{{ $furnace->max_temperature }}">
                                            {{ $furnace->furnaceSet->name }} - {{ $furnace->name }}
                                            @if($furnace->current_temperature)
                                                ({{ $furnace->current_temperature }}°C)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('furnace_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="casting_number" class="form-label">
                                    <i class="fas fa-hashtag text-info"></i> Döküm Numarası *
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text" id="furnace-info">
                                        <i class="fas fa-fire"></i>
                                    </span>
                                    <input type="text" 
                                           name="casting_number" 
                                           id="casting_number" 
                                           class="form-control @error('casting_number') is-invalid @enderror" 
                                           value="{{ old('casting_number') }}" 
                                           readonly
                                           placeholder="Ocak seçildikten sonra otomatik oluşturulur">
                                </div>
                                @error('casting_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> 
                                    Döküm numarası otomatik olarak oluşturulur (Örn: 3.OCAK-27.DÖKÜM)
                                </small>
                            </div>
                        </div>

                        <!-- Tarih ve Vardiya -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="casting_date" class="form-label">
                                    <i class="fas fa-calendar text-success"></i> Döküm Tarihi *
                                </label>
                                <input type="date" 
                                       name="casting_date" 
                                       id="casting_date" 
                                       class="form-control @error('casting_date') is-invalid @enderror" 
                                       value="{{ old('casting_date', date('Y-m-d')) }}" 
                                       required>
                                @error('casting_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="casting_time" class="form-label">
                                    <i class="fas fa-clock text-warning"></i> Döküm Saati *
                                </label>
                                <input type="time" 
                                       name="casting_time" 
                                       id="casting_time" 
                                       class="form-control @error('casting_time') is-invalid @enderror" 
                                       value="{{ old('casting_time', date('H:i')) }}" 
                                       required>
                                @error('casting_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="shift" class="form-label">
                                    <i class="fas fa-user-clock text-info"></i> Vardiya *
                                </label>
                                <select name="shift" id="shift" class="form-select @error('shift') is-invalid @enderror" required>
                                    <option value="">Vardiya seçiniz...</option>
                                    <option value="Gündüz" {{ old('shift') === 'Gündüz' ? 'selected' : '' }}>
                                        🌞 Gündüz (06:00-18:00)
                                    </option>
                                    <option value="Gece" {{ old('shift') === 'Gece' ? 'selected' : '' }}>
                                        🌙 Gece (18:00-06:00)
                                    </option>
                                </select>
                                @error('shift')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Operatör ve Sıcaklık -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="operator_name" class="form-label">
                                    <i class="fas fa-user text-primary"></i> Operatör Adı *
                                </label>
                                <input type="text" 
                                       name="operator_name" 
                                       id="operator_name" 
                                       class="form-control @error('operator_name') is-invalid @enderror" 
                                       value="{{ old('operator_name') }}" 
                                       placeholder="Operatör adı ve soyadı"
                                       required>
                                @error('operator_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="target_temperature" class="form-label">
                                    <i class="fas fa-thermometer-half text-danger"></i> Hedef Sıcaklık (°C)
                                </label>
                                <div class="input-group">
                                    <input type="number" 
                                           name="target_temperature" 
                                           id="target_temperature" 
                                           class="form-control @error('target_temperature') is-invalid @enderror" 
                                           value="{{ old('target_temperature') }}" 
                                           min="0" 
                                           max="3000" 
                                           placeholder="1650">
                                    <span class="input-group-text">°C</span>
                                </div>
                                @error('target_temperature')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    <span id="temp-range-info">Maksimum sıcaklık bilgisi için ocak seçiniz</span>
                                </small>
                            </div>
                        </div>

                        <!-- Notlar -->
                        <div class="mb-4">
                            <label for="notes" class="form-label">
                                <i class="fas fa-sticky-note text-warning"></i> Notlar ve Açıklamalar
                            </label>
                            <textarea name="notes" 
                                      id="notes" 
                                      class="form-control @error('notes') is-invalid @enderror" 
                                      rows="3" 
                                      placeholder="Döküm ile ilgili özel notlar, dikkat edilecek hususlar...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('castings.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> İptal
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-play"></i> Dökümü Başlat
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sağ Kolon - Bilgi Paneli -->
        <div class="col-md-4">
            <!-- Seçilen Ocak Bilgisi -->
            <div class="card mb-3" id="furnace-info-card" style="display: none;">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-fire"></i> Seçilen Ocak
                    </h6>
                </div>
                <div class="card-body">
                    <div id="selected-furnace-info">
                        <!-- JavaScript ile doldurulacak -->
                    </div>
                </div>
            </div>

            <!-- Döküm İstatistikleri -->
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar"></i> Bugünkü İstatistikler
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary mb-1">{{ $todayStats['total_castings'] ?? 0 }}</h4>
                                <small class="text-muted">Toplam Döküm</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success mb-1">{{ $todayStats['active_castings'] ?? 0 }}</h4>
                            <small class="text-muted">Aktif Döküm</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-warning mb-1">{{ $todayStats['total_samples'] ?? 0 }}</h4>
                                <small class="text-muted">Toplam Prova</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-info mb-1">{{ $todayStats['active_furnaces'] ?? 0 }}</h4>
                            <small class="text-muted">Aktif Ocak</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Yardım Paneli -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-question-circle text-info"></i> Yardım
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-lightbulb"></i> Döküm Numaralandırma</h6>
                        <p class="mb-2">Sistem otomatik olarak döküm numarası oluşturur:</p>
                        <ul class="mb-0 small">
                            <li><strong>3.OCAK-27.DÖKÜM</strong> = 3. ocaktan 27. döküm</li>
                            <li><strong>1.OCAK-15.DÖKÜM</strong> = 1. ocaktan 15. döküm</li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Önemli</h6>
                        <ul class="mb-0 small">
                            <li>Sadece aktif ocaklardan döküm başlatabilirsiniz</li>
                            <li>Her ocak için döküm sayısı otomatik artar</li>
                            <li>Döküm başladıktan sonra prova ekleyebilirsiniz</li>
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
    // Ocak seçimi değiştiğinde
    $('#furnace_id').change(function() {
        const selectedOption = $(this).find(':selected');
        const furnaceId = $(this).val();
        
        if (furnaceId) {
            // Ocak bilgilerini al
            const setName = selectedOption.data('set-name');
            const furnaceName = selectedOption.data('furnace-name');
            const currentTemp = selectedOption.data('current-temp');
            const maxTemp = selectedOption.data('max-temp');
            
            // Döküm numarası oluştur (AJAX ile)
            generateCastingNumber(furnaceId);
            
            // Ocak bilgi kartını göster
            showFurnaceInfo(setName, furnaceName, currentTemp, maxTemp);
            
            // Sıcaklık aralığı bilgisi
            if (maxTemp) {
                $('#temp-range-info').html(`<i class="fas fa-info-circle"></i> Maksimum: ${maxTemp}°C`);
                $('#target_temperature').attr('max', maxTemp);
            }
        } else {
            // Bilgileri temizle
            $('#furnace-info-card').hide();
            $('#casting_number').val('');
            $('#temp-range-info').text('Maksimum sıcaklık bilgisi için ocak seçiniz');
        }
    });
    
    // Sayfa yüklendiğinde seçili ocak varsa bilgileri göster
    if ($('#furnace_id').val()) {
        $('#furnace_id').trigger('change');
    }
    
    // Vardiya otomatik seçimi (saate göre)
    if (!$('#shift').val()) {
        const currentHour = new Date().getHours();
        if (currentHour >= 6 && currentHour < 18) {
            $('#shift').val('Gündüz');
        } else {
            $('#shift').val('Gece');
        }
    }
});

function generateCastingNumber(furnaceId) {
    $.ajax({
        url: `/api/furnaces/${furnaceId}/next-casting-number`,
        method: 'GET',
        success: function(response) {
            $('#casting_number').val(response.casting_number);
            $('#furnace-info').html(`<i class="fas fa-hashtag"></i> ${response.casting_count + 1}. Döküm`);
        },
        error: function() {
            $('#casting_number').val('HATA - Manuel giriniz');
        }
    });
}

function showFurnaceInfo(setName, furnaceName, currentTemp, maxTemp) {
    const tempColor = currentTemp > 1500 ? 'text-danger' : currentTemp > 1000 ? 'text-warning' : 'text-info';
    
    const infoHtml = `
        <div class="row mb-2">
            <div class="col-4"><strong>Set:</strong></div>
            <div class="col-8">${setName}</div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><strong>Ocak:</strong></div>
            <div class="col-8">${furnaceName}</div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><strong>Mevcut:</strong></div>
            <div class="col-8">
                <span class="${tempColor}">
                    <i class="fas fa-thermometer-half"></i> ${currentTemp || 0}°C
                </span>
            </div>
        </div>
        <div class="row">
            <div class="col-4"><strong>Maksimum:</strong></div>
            <div class="col-8">
                <span class="text-danger">
                    <i class="fas fa-thermometer-full"></i> ${maxTemp || 'N/A'}°C
                </span>
            </div>
        </div>
    `;
    
    $('#selected-furnace-info').html(infoHtml);
    $('#furnace-info-card').show();
}
</script>
@endpush
