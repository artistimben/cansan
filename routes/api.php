<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FurnaceController;
use App\Http\Controllers\SampleController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Cansan Çelik Üretim Fabrikası Kalite Kontrol Sistemi
| API rotaları - mobil uygulamalar ve dış sistemler için
|
*/

// API versiyonlama
Route::prefix('v1')->group(function () {
    
    // Sistem durumu
    Route::get('/status', [DashboardController::class, 'healthCheck'])->name('api.status');
    Route::get('/dashboard', [DashboardController::class, 'getRealtimeStatus'])->name('api.dashboard');
    
    // Ocak işlemleri
    Route::prefix('furnaces')->group(function () {
        Route::get('/', [FurnaceController::class, 'index'])->name('api.furnaces.index');
        Route::get('/{furnace}', [FurnaceController::class, 'show'])->name('api.furnaces.show');
        Route::post('/{furnace}/toggle-status', [FurnaceController::class, 'toggleStatus'])->name('api.furnaces.toggle-status');
        Route::post('/{furnace}/start-casting', [FurnaceController::class, 'startCasting'])->name('api.furnaces.start-casting');
        Route::post('/{furnace}/castings/{casting}/complete', [FurnaceController::class, 'completeCasting'])->name('api.furnaces.complete-casting');
        Route::get('/{furnace}/performance', [FurnaceController::class, 'performanceReport'])->name('api.furnaces.performance');
    });
    
    // Prova işlemleri
    Route::prefix('samples')->group(function () {
        Route::get('/', [SampleController::class, 'index'])->name('api.samples.index');
        Route::post('/', [SampleController::class, 'store'])->name('api.samples.store');
        Route::get('/{sample}', [SampleController::class, 'show'])->name('api.samples.show');
        Route::put('/{sample}', [SampleController::class, 'update'])->name('api.samples.update');
        Route::delete('/{sample}', [SampleController::class, 'destroy'])->name('api.samples.destroy');
        Route::post('/{sample}/quality-status', [SampleController::class, 'updateQualityStatus'])->name('api.samples.quality-status');
        Route::post('/{sample}/radio-report', [SampleController::class, 'recordRadioReport'])->name('api.samples.radio-report');
        Route::get('/pending/list', [SampleController::class, 'pending'])->name('api.samples.pending');
        Route::get('/quality/report', [SampleController::class, 'qualityReport'])->name('api.samples.quality-report');
    });
    
    // Raporlama
    Route::prefix('reports')->group(function () {
        Route::get('/daily', [ReportController::class, 'daily'])->name('api.reports.daily');
        Route::get('/weekly', [ReportController::class, 'weekly'])->name('api.reports.weekly');
        Route::get('/monthly', [ReportController::class, 'monthly'])->name('api.reports.monthly');
        Route::get('/export', [ReportController::class, 'export'])->name('api.reports.export');
    });
    
});

// Telsiz entegrasyonu için özel endpoint'ler
Route::prefix('radio')->group(function () {
    
    // Prova sonuçlarını telsiz ile bildirme
    Route::post('/report-sample', function (Request $request) {
        $request->validate([
            'sample_id' => 'required|exists:samples,id',
            'reported_by' => 'required|string|max:100',
            'message' => 'nullable|string|max:500'
        ]);
        
        $sample = \App\Models\Sample::find($request->sample_id);
        $sample->update([
            'reported_via_radio' => true,
            'reported_at' => now(),
            'reported_by' => $request->reported_by,
            'quality_notes' => $sample->quality_notes . "\n[Telsiz]: " . $request->message
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Prova sonucu telsiz ile bildirildi',
            'sample' => $sample
        ]);
    })->name('api.radio.report-sample');
    
    // Aktif dökümlerin durumunu getir (telsiz operatörü için)
    Route::get('/active-castings', function () {
        $activeCastings = \App\Models\Casting::active()
            ->with(['furnace.furnaceSet', 'samples' => function($query) {
                $query->latest('sample_date')->take(1);
            }])
            ->get();
        
        return response()->json([
            'active_castings' => $activeCastings->map(function($casting) {
                $lastSample = $casting->samples->first();
                return [
                    'casting_id' => $casting->id,
                    'casting_number' => $casting->casting_number,
                    'furnace_name' => $casting->furnace->name,
                    'set_name' => $casting->furnace->furnaceSet->name,
                    'casting_date' => $casting->casting_date,
                    'shift' => $casting->shift,
                    'last_sample' => $lastSample ? [
                        'id' => $lastSample->id,
                        'sample_number' => $lastSample->sample_number,
                        'quality_status' => $lastSample->quality_status,
                        'sample_date' => $lastSample->sample_date
                    ] : null,
                    'needs_attention' => $lastSample && in_array($lastSample->quality_status, ['rejected', 'needs_adjustment'])
                ];
            })
        ]);
    })->name('api.radio.active-castings');
    
});

// Webhook endpoint'leri (dış sistem entegrasyonları için)
Route::prefix('webhooks')->group(function () {
    
    // Laboratuvar analiz sonuçları
    Route::post('/lab-results', function (Request $request) {
        // Laboratuvar sisteminden gelen analiz sonuçlarını işle
        $request->validate([
            'sample_id' => 'required|exists:samples,id',
            'results' => 'required|array'
        ]);
        
        $sample = \App\Models\Sample::find($request->sample_id);
        $sample->update($request->results);
        
        return response()->json([
            'success' => true,
            'message' => 'Laboratuvar sonuçları güncellendi'
        ]);
    })->name('api.webhooks.lab-results');
    
    // ERP sistemi entegrasyonu
    Route::post('/erp-integration', function (Request $request) {
        // ERP sistemine döküm ve kalite verilerini gönder
        return response()->json([
            'success' => true,
            'message' => 'ERP entegrasyonu tamamlandı'
        ]);
    })->name('api.webhooks.erp-integration');
    
});

// Test endpoint'i
Route::get('/test', function () {
    return response()->json([
        'message' => 'Cansan Kalite Kontrol API çalışıyor',
        'version' => '1.0.0',
        'timestamp' => now(),
        'endpoints' => [
            'status' => '/api/v1/status',
            'dashboard' => '/api/v1/dashboard',
            'furnaces' => '/api/v1/furnaces',
            'samples' => '/api/v1/samples',
            'reports' => '/api/v1/reports',
            'radio' => '/api/radio/*',
            'webhooks' => '/api/webhooks/*'
        ]
    ]);
})->name('api.test');
