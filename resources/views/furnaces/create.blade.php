@extends('layouts.app')

@section('title', 'Yeni Ocak - Cansan Kalite Kontrol')

@section('header', 'Yeni Ocak Ekle')

@section('header-buttons')
    <div class="btn-group" role="group">
        <a href="{{ route('furnaces.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>
            Geri
        </a>
    </div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-fire me-2"></i>
                    Yeni Ocak Kaydı
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('furnaces.store') }}" id="furnaceForm">
                    @csrf
                    
                    <!-- Temel Bilgiler -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-info-circle me-2"></i>
                                Temel Bilgiler
                            </h6>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="furnace_set_id" class="form-label">Ocak Seti <span class="text-danger">*</span></label>
                                <select class="form-select @error('furnace_set_id') is-invalid @enderror" name="furnace_set_id" id="furnace_set_id" required>
                                    <option value="">Set seçiniz...</option>
                                    @foreach($furnaceSets as $set)
                                        <option value="{{ $set->id }}" {{ old('furnace_set_id') == $set->id ? 'selected' : '' }}>
                                            {{ $set->name }} ({{ $set->description }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('furnace_set_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Ocak Adı <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       name="name" id="name" value="{{ old('name') }}" 
                                       placeholder="Örn: Ocak 1" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="description" class="form-label">Açıklama</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          name="description" id="description" rows="3" 
                                          placeholder="Ocak hakkında açıklama...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Teknik Özellikler -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-success mb-3">
                                <i class="fas fa-cogs me-2"></i>
                                Teknik Özellikler
                            </h6>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="capacity" class="form-label">Kapasite</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('capacity') is-invalid @enderror" 
                                           name="capacity" id="capacity" 
                                           step="0.1" min="0" max="1000" 
                                           value="{{ old('capacity') }}" 
                                           placeholder="50.0">
                                    <span class="input-group-text">ton</span>
                                </div>
                                @error('capacity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="max_temperature" class="form-label">Maksimum Sıcaklık</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('max_temperature') is-invalid @enderror" 
                                           name="max_temperature" id="max_temperature" 
                                           step="1" min="0" max="3000" 
                                           value="{{ old('max_temperature') }}" 
                                           placeholder="1800">
                                    <span class="input-group-text">°C</span>
                                </div>
                                @error('max_temperature')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fuel_type" class="form-label">Yakıt Türü</label>
                                <select class="form-select @error('fuel_type') is-invalid @enderror" name="fuel_type" id="fuel_type">
                                    <option value="">Yakıt türü seçiniz...</option>
                                    <option value="natural_gas" {{ old('fuel_type') == 'natural_gas' ? 'selected' : '' }}>Doğal Gaz</option>
                                    <option value="electricity" {{ old('fuel_type') == 'electricity' ? 'selected' : '' }}>Elektrik</option>
                                    <option value="coal" {{ old('fuel_type') == 'coal' ? 'selected' : '' }}>Kömür</option>
                                    <option value="oil" {{ old('fuel_type') == 'oil' ? 'selected' : '' }}>Mazot</option>
                                    <option value="mixed" {{ old('fuel_type') == 'mixed' ? 'selected' : '' }}>Karma</option>
                                </select>
                                @error('fuel_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="installation_date" class="form-label">Kurulum Tarihi</label>
                                <input type="date" class="form-control @error('installation_date') is-invalid @enderror" 
                                       name="installation_date" id="installation_date" 
                                       value="{{ old('installation_date') }}">
                                @error('installation_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Durum ve Ayarlar -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-warning mb-3">
                                <i class="fas fa-sliders-h me-2"></i>
                                Durum ve Ayarlar
                            </h6>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Başlangıç Durumu</label>
                                <select class="form-select @error('status') is-invalid @enderror" name="status" id="status">
                                    <option value="inactive" {{ old('status', 'inactive') == 'inactive' ? 'selected' : '' }}>Kapalı</option>
                                    <option value="idle" {{ old('status') == 'idle' ? 'selected' : '' }}>Beklemede</option>
                                    <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Bakımda</option>
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="current_temperature" class="form-label">Mevcut Sıcaklık</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('current_temperature') is-invalid @enderror" 
                                           name="current_temperature" id="current_temperature" 
                                           step="0.1" min="0" max="3000" 
                                           value="{{ old('current_temperature') }}" 
                                           placeholder="0">
                                    <span class="input-group-text">°C</span>
                                </div>
                                @error('current_temperature')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Sadece aktif ocaklar için gerekli</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bakım Bilgileri -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-info mb-3">
                                <i class="fas fa-tools me-2"></i>
                                Bakım Bilgileri
                            </h6>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="last_maintenance_date" class="form-label">Son Bakım Tarihi</label>
                                <input type="date" class="form-control @error('last_maintenance_date') is-invalid @enderror" 
                                       name="last_maintenance_date" id="last_maintenance_date" 
                                       value="{{ old('last_maintenance_date') }}">
                                @error('last_maintenance_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="maintenance_interval_days" class="form-label">Bakım Aralığı</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('maintenance_interval_days') is-invalid @enderror" 
                                           name="maintenance_interval_days" id="maintenance_interval_days" 
                                           step="1" min="1" max="365" 
                                           value="{{ old('maintenance_interval_days', 30) }}" 
                                           placeholder="30">
                                    <span class="input-group-text">gün</span>
                                </div>
                                @error('maintenance_interval_days')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Butonları -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('furnaces.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i>
                                    İptal
                                </a>
                                
                                <div>
                                    <button type="button" class="btn btn-outline-primary me-2" onclick="resetForm()">
                                        <i class="fas fa-undo me-1"></i>
                                        Temizle
                                    </button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save me-1"></i>
                                        Ocak Kaydet
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Yardım Kartı -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card bg-light">
            <div class="card-body">
                <h6 class="text-primary mb-3">
                    <i class="fas fa-lightbulb me-2"></i>
                    Yardımcı Bilgiler
                </h6>
                <div class="row">
                    <div class="col-md-4">
                        <h6>Ocak Durumları</h6>
                        <ul class="list-unstyled small">
                            <li><strong>Aktif:</strong> Üretimde, döküm yapılıyor</li>
                            <li><strong>Beklemede:</strong> Hazır, ama üretim yok</li>
                            <li><strong>Bakımda:</strong> Bakım/onarım yapılıyor</li>
                            <li><strong>Kapalı:</strong> Kullanım dışı</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6>Yakıt Türleri</h6>
                        <ul class="list-unstyled small">
                            <li><strong>Doğal Gaz:</strong> En yaygın, temiz yanma</li>
                            <li><strong>Elektrik:</strong> Endüksiyon ocakları</li>
                            <li><strong>Kömür:</strong> Geleneksel, yüksek sıcaklık</li>
                            <li><strong>Mazot:</strong> Yedek yakıt</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6>Kapasite Rehberi</h6>
                        <ul class="list-unstyled small">
                            <li><strong>Küçük Ocak:</strong> 10-30 ton</li>
                            <li><strong>Orta Ocak:</strong> 30-100 ton</li>
                            <li><strong>Büyük Ocak:</strong> 100+ ton</li>
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
// Form temizle
function resetForm() {
    if (confirm('Tüm girilen veriler silinecek. Emin misiniz?')) {
        document.getElementById('furnaceForm').reset();
    }
}

// Set seçimi değiştiğinde ocak adını otomatik öner
document.getElementById('furnace_set_id').addEventListener('change', function() {
    const setId = this.value;
    const nameInput = document.getElementById('name');
    
    if (setId && !nameInput.value) {
        const setName = this.options[this.selectedIndex].text.split(' (')[0];
        
        // Set'teki mevcut ocak sayısını tahmin et (1-2 arası)
        fetch(`/api/furnace-sets/${setId}/furnaces/count`)
            .then(response => response.json())
            .then(data => {
                const nextNumber = data.count + 1;
                nameInput.value = `${setName.replace('Set', 'Ocak')} ${nextNumber}`;
            })
            .catch(() => {
                // API yoksa varsayılan öner
                nameInput.value = `${setName.replace('Set', 'Ocak')} 1`;
            });
    }
});

// Durum değiştiğinde sıcaklık alanını etkinleştir/devre dışı bırak
document.getElementById('status').addEventListener('change', function() {
    const tempInput = document.getElementById('current_temperature');
    const tempGroup = tempInput.closest('.input-group');
    
    if (this.value === 'active') {
        tempInput.required = true;
        tempGroup.classList.add('border-success');
        tempInput.placeholder = '1500';
    } else {
        tempInput.required = false;
        tempGroup.classList.remove('border-success');
        tempInput.placeholder = '0';
        if (this.value === 'inactive') {
            tempInput.value = '0';
        }
    }
});

// Form validasyonu
document.getElementById('furnaceForm').addEventListener('submit', function(e) {
    const setId = document.getElementById('furnace_set_id').value;
    const name = document.getElementById('name').value.trim();
    const status = document.getElementById('status').value;
    const currentTemp = document.getElementById('current_temperature').value;
    
    if (!setId) {
        e.preventDefault();
        showToast('Lütfen bir ocak seti seçin', 'warning');
        document.getElementById('furnace_set_id').focus();
        return;
    }
    
    if (!name) {
        e.preventDefault();
        showToast('Lütfen ocak adını girin', 'warning');
        document.getElementById('name').focus();
        return;
    }
    
    if (status === 'active' && (!currentTemp || parseFloat(currentTemp) <= 0)) {
        e.preventDefault();
        showToast('Aktif ocaklar için geçerli bir sıcaklık değeri girin', 'warning');
        document.getElementById('current_temperature').focus();
        return;
    }
});

// Klavye kısayolları
document.addEventListener('keydown', function(e) {
    // Ctrl + S: Form kaydet
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        document.getElementById('furnaceForm').submit();
    }
    
    // Ctrl + R: Form temizle
    if (e.ctrlKey && e.key === 'r') {
        e.preventDefault();
        resetForm();
    }
});

// Otomatik format
document.getElementById('capacity').addEventListener('blur', function() {
    if (this.value && !isNaN(this.value)) {
        this.value = parseFloat(this.value).toFixed(1);
    }
});

document.getElementById('current_temperature').addEventListener('blur', function() {
    if (this.value && !isNaN(this.value)) {
        this.value = parseFloat(this.value).toFixed(1);
    }
});
</script>
@endpush
