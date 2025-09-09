@extends('layouts.app')

@section('title', 'Bekleyen Provalar - Cansan Kalite Kontrol')

@section('header', 'Bekleyen Provalar')

@section('header-buttons')
    <div class="btn-group" role="group">
        <a href="{{ route('samples.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>
            Tüm Provalar
        </a>
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="refreshPage()">
            <i class="fas fa-sync-alt me-1"></i>
            Yenile
        </button>
        <button type="button" class="btn btn-outline-success btn-sm" onclick="bulkApprove()" id="bulkApproveBtn" disabled>
            <i class="fas fa-check me-1"></i>
            Seçilenleri Onayla
        </button>
    </div>
@endsection

@section('content')
<!-- Özet Bilgiler -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center bg-warning text-white">
            <div class="card-body">
                <i class="fas fa-hourglass-half fa-2x mb-2"></i>
                <h3 class="mb-1">{{ $samples->count() }}</h3>
                <small>Bekleyen Prova</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card text-center bg-info text-white">
            <div class="card-body">
                <i class="fas fa-clock fa-2x mb-2"></i>
                <h3 class="mb-1">{{ $samples->where('sample_time', '<', now()->subHours(2))->count() }}</h3>
                <small>2+ Saat Bekleyen</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card text-center bg-danger text-white">
            <div class="card-body">
                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                <h3 class="mb-1">{{ $samples->where('sample_time', '<', now()->subHours(4))->count() }}</h3>
                <small>4+ Saat Bekleyen</small>
            </div>
        </div>
    </div>
</div>

<!-- Uyarı Mesajları -->
@if($samples->where('sample_time', '<', now()->subHours(4))->count() > 0)
    <div class="alert alert-danger" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Dikkat!</strong> 4 saatten uzun süredir bekleyen provalar var. Lütfen öncelikle bunları değerlendirin.
    </div>
@elseif($samples->where('sample_time', '<', now()->subHours(2))->count() > 0)
    <div class="alert alert-warning" role="alert">
        <i class="fas fa-clock me-2"></i>
        <strong>Uyarı!</strong> 2 saatten uzun süredir bekleyen provalar var.
    </div>
@endif

<!-- Bekleyen Provalar Listesi -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Bekleyen Provalar
                </h5>
                <div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                        <label class="form-check-label" for="selectAll">
                            Tümünü Seç
                        </label>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if($samples->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="40">
                                        <input type="checkbox" class="form-check-input" id="headerCheck" onchange="toggleSelectAll()">
                                    </th>
                                    <th>Prova #</th>
                                    <th>Ocak</th>
                                    <th>Döküm #</th>
                                    <th>Prova Tarihi</th>
                                    <th>Bekleme Süresi</th>
                                    <th>Analiz Eden</th>
                                    <th>Kimyasal Değerler</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($samples->sortBy('sample_time') as $sample)
                                    @php
                                        $waitingHours = $sample->sample_time->diffInHours(now());
                                        $rowClass = '';
                                        if ($waitingHours >= 4) {
                                            $rowClass = 'table-danger';
                                        } elseif ($waitingHours >= 2) {
                                            $rowClass = 'table-warning';
                                        }
                                    @endphp
                                    <tr class="{{ $rowClass }}">
                                        <td>
                                            <input type="checkbox" class="form-check-input sample-checkbox" value="{{ $sample->id }}" onchange="updateBulkActions()">
                                        </td>
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
                                        <td>
                                            @if($waitingHours >= 4)
                                                <span class="badge bg-danger">{{ $waitingHours }} saat</span>
                                            @elseif($waitingHours >= 2)
                                                <span class="badge bg-warning">{{ $waitingHours }} saat</span>
                                            @else
                                                <span class="badge bg-info">{{ $waitingHours }} saat</span>
                                            @endif
                                            <br><small class="text-muted">{{ $sample->sample_time->diffForHumans() }}</small>
                                        </td>
                                        <td>{{ $sample->analyzed_by }}</td>
                                        <td>
                                            <small>
                                                @if($sample->carbon_percentage)
                                                    <div>C: {{ number_format($sample->carbon_percentage, 3) }}%</div>
                                                @endif
                                                @if($sample->manganese_percentage)
                                                    <div>Mn: {{ number_format($sample->manganese_percentage, 3) }}%</div>
                                                @endif
                                                @if($sample->silicon_percentage)
                                                    <div>Si: {{ number_format($sample->silicon_percentage, 3) }}%</div>
                                                @endif
                                                @if(!$sample->carbon_percentage && !$sample->manganese_percentage && !$sample->silicon_percentage)
                                                    <span class="text-muted">Veri yok</span>
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-success btn-sm" onclick="quickApprove({{ $sample->id }})" title="Hızlı Onayla">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" onclick="quickReject({{ $sample->id }})" title="Hızlı Reddet">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <button type="button" class="btn btn-info btn-sm" onclick="needsAdjustment({{ $sample->id }})" title="Düzeltme Gerekli">
                                                    <i class="fas fa-tools"></i>
                                                </button>
                                                <a href="{{ route('samples.show', $sample) }}" class="btn btn-outline-primary btn-sm" title="Detay">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                        <h5>Harika! Bekleyen prova yok</h5>
                        <p>Tüm provalar değerlendirilmiş durumda.</p>
                        <a href="{{ route('samples.index') }}" class="btn btn-primary">
                            <i class="fas fa-list me-1"></i>
                            Tüm Provaları Görüntüle
                        </a>
                    </div>
                @endif
            </div>
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
                    <input type="hidden" id="sample_ids" name="sample_ids">
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

@endsection

@push('scripts')
<script>
// Tümünü seç/seçme
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const headerCheck = document.getElementById('headerCheck');
    const checkboxes = document.querySelectorAll('.sample-checkbox');
    
    // Sync header checkbox with selectAll
    headerCheck.checked = selectAll.checked;
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateBulkActions();
}

// Toplu işlem butonlarını güncelle
function updateBulkActions() {
    const checkedBoxes = document.querySelectorAll('.sample-checkbox:checked');
    const bulkApproveBtn = document.getElementById('bulkApproveBtn');
    
    if (checkedBoxes.length > 0) {
        bulkApproveBtn.disabled = false;
        bulkApproveBtn.textContent = `${checkedBoxes.length} Provayı Onayla`;
    } else {
        bulkApproveBtn.disabled = true;
        bulkApproveBtn.innerHTML = '<i class="fas fa-check me-1"></i>Seçilenleri Onayla';
    }
    
    // SelectAll checkbox durumunu güncelle
    const allCheckboxes = document.querySelectorAll('.sample-checkbox');
    const selectAll = document.getElementById('selectAll');
    const headerCheck = document.getElementById('headerCheck');
    
    if (checkedBoxes.length === allCheckboxes.length) {
        selectAll.checked = true;
        headerCheck.checked = true;
    } else if (checkedBoxes.length === 0) {
        selectAll.checked = false;
        headerCheck.checked = false;
    } else {
        selectAll.indeterminate = true;
        headerCheck.indeterminate = true;
    }
}

// Hızlı onayla
function quickApprove(sampleId) {
    updateQualityStatus([sampleId], 'approved');
}

// Hızlı reddet
function quickReject(sampleId) {
    updateQualityStatus([sampleId], 'rejected');
}

// Düzeltme gerekli
function needsAdjustment(sampleId) {
    updateQualityStatus([sampleId], 'needs_adjustment');
}

// Toplu onayla
function bulkApprove() {
    const checkedBoxes = document.querySelectorAll('.sample-checkbox:checked');
    const sampleIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    if (sampleIds.length === 0) {
        showToast('Lütfen en az bir prova seçin', 'warning');
        return;
    }
    
    if (confirm(`${sampleIds.length} prova onaylanacak. Emin misiniz?`)) {
        updateQualityStatus(sampleIds, 'approved');
    }
}

// Kalite durumu güncelle
function updateQualityStatus(sampleIds, status) {
    document.getElementById('sample_ids').value = JSON.stringify(sampleIds);
    document.getElementById('quality_status').value = status;
    
    const statusNames = {
        'approved': 'Onayla',
        'rejected': 'Reddet',
        'needs_adjustment': 'Düzeltme Gerekli'
    };
    
    document.querySelector('#qualityStatusModal .modal-title').textContent = 
        `${statusNames[status]} - ${sampleIds.length} Prova`;
    
    const modal = new bootstrap.Modal(document.getElementById('qualityStatusModal'));
    modal.show();
}

// Kalite durumu form submit
document.getElementById('qualityStatusForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const sampleIds = JSON.parse(formData.get('sample_ids'));
    const status = formData.get('quality_status');
    const notes = formData.get('quality_notes');
    
    // Her bir prova için ayrı ayrı güncelle
    const promises = sampleIds.map(sampleId => {
        return fetch(`/samples/${sampleId}/update-quality-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                quality_status: status,
                quality_notes: notes
            })
        });
    });
    
    Promise.all(promises)
        .then(responses => Promise.all(responses.map(r => r.json())))
        .then(results => {
            const successCount = results.filter(r => r.success).length;
            if (successCount === sampleIds.length) {
                showToast(`${successCount} prova başarıyla güncellendi`, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(`${successCount}/${sampleIds.length} prova güncellendi`, 'warning');
                setTimeout(() => location.reload(), 2000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Hata oluştu', 'error');
        });
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('qualityStatusModal'));
    modal.hide();
});

// Sayfa yenile
function refreshPage() {
    location.reload();
}

// Otomatik yenileme (30 saniyede bir)
setInterval(() => {
    if (!document.hidden) {
        location.reload();
    }
}, 30000);

// Klavye kısayolları
document.addEventListener('keydown', function(e) {
    // Ctrl + A: Tümünü seç
    if (e.ctrlKey && e.key === 'a') {
        e.preventDefault();
        document.getElementById('selectAll').checked = true;
        toggleSelectAll();
    }
    
    // Ctrl + Enter: Seçilenleri onayla
    if (e.ctrlKey && e.key === 'Enter') {
        e.preventDefault();
        bulkApprove();
    }
    
    // F5: Yenile
    if (e.key === 'F5') {
        e.preventDefault();
        refreshPage();
    }
});

// Sayfa yüklendiğinde
document.addEventListener('DOMContentLoaded', function() {
    // Checkbox event listeners
    document.querySelectorAll('.sample-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });
    
    updateBulkActions();
});
</script>
@endpush
