<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QualityControlController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Çelik Fabrikası Yönetim Sistemi
|--------------------------------------------------------------------------
|
| Ana dashboard üzerinden tüm işlemler gerçekleştirilir:
| - Ocak yönetimi ve döküm takibi
| - Kalite kontrol değerleri
| - Günlük raporlama
|
*/

// Ana Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Real-time veri güncelleme (AJAX)
Route::get('/api/realtime-data', [DashboardController::class, 'getRealTimeData'])->name('api.realtime');

// Döküm İşlemleri
Route::prefix('casting')->name('casting.')->group(function () {
    // Döküm tamamlama
    Route::post('/{casting}/complete', [DashboardController::class, 'completeCasting'])->name('complete');
    
    // Yeni döküm başlatma
    Route::post('/furnace/{furnace}/start', [DashboardController::class, 'startCasting'])->name('start');
    
    // Ocak döküm geçmişi
    Route::get('/furnace/{furnace}/history', [DashboardController::class, 'getFurnaceCastingHistory'])->name('history');
});

// Ocak Yönetimi
Route::prefix('furnace')->name('furnace.')->group(function () {
    // Ocak bakıma gönderme
    Route::post('/{furnace}/maintenance', [DashboardController::class, 'sendToMaintenance'])->name('maintenance');
});

// Kalite Kontrol
Route::prefix('quality-control')->name('quality.')->group(function () {
    // Kalite kontrol değerleri kaydetme/güncelleme
    Route::post('/casting/{casting}', [QualityControlController::class, 'store'])->name('store');
    
    // Kalite kontrol verilerini getirme
    Route::get('/casting/{casting}', [QualityControlController::class, 'show'])->name('show');
    
    // Standart kompozisyon limitleri
    Route::get('/limits', [QualityControlController::class, 'getStandardLimits'])->name('limits');
    
    // Kalite kontrol raporu
    Route::get('/report', [QualityControlController::class, 'getQualityReport'])->name('report');
    
    // Toplu test değerlendirmesi
    Route::post('/bulk-evaluate', [QualityControlController::class, 'bulkEvaluate'])->name('bulk.evaluate');
});

// Günlük Raporlar
Route::prefix('reports')->name('reports.')->group(function () {
    // Günlük rapor oluşturma
    Route::post('/daily', [DashboardController::class, 'generateDailyReport'])->name('daily.generate');
    
    // Haftalık raporlar
    Route::get('/weekly', function () {
        // TODO: Haftalık rapor controller'ı eklenecek
        return response()->json(['message' => 'Haftalık rapor özelliği geliştiriliyor...']);
    })->name('weekly');
    
    // Aylık raporlar
    Route::get('/monthly', function () {
        // TODO: Aylık rapor controller'ı eklenecek
        return response()->json(['message' => 'Aylık rapor özelliği geliştiriliyor...']);
    })->name('monthly');
});

// API Routes (Mobile veya harici entegrasyonlar için)
Route::prefix('api/v1')->name('api.')->group(function () {
    // Sistem durumu
    Route::get('/status', function () {
        return response()->json([
            'system' => 'Çelik Fabrikası Yönetim Sistemi',
            'version' => '1.0.0',
            'status' => 'active',
            'timestamp' => now()->toISOString(),
            'active_furnaces' => \App\Models\Furnace::where('status', 'active')->count(),
            'active_castings' => \App\Models\Casting::where('status', 'in_progress')->count()
        ]);
    })->name('status');
    
    // Tüm ocakların durumu
    Route::get('/furnaces', function () {
        $furnaces = \App\Models\Furnace::with('castings')->get();
        return response()->json([
            'success' => true,
            'data' => $furnaces->map(function ($furnace) {
                return [
                    'furnace_number' => $furnace->furnace_number,
                    'status' => $furnace->status,
                    'total_castings' => $furnace->total_castings,
                    'current_casting' => $furnace->getCurrentCasting() ? [
                        'casting_number' => $furnace->getCurrentCasting()->casting_number_per_furnace,
                        'progress' => $furnace->getCurrentCasting()->progress_percentage
                    ] : null
                ];
            })
        ]);
    })->name('furnaces');
    
    // Günlük istatistikler
    Route::get('/stats/daily', function () {
        $stats = \App\Models\Casting::getDailyCastingStats();
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    })->name('stats.daily');
});

// Test Routes (Development aşamasında)
Route::prefix('test')->name('test.')->group(function () {
    // Sistem kurulumu ve başlangıç verisi
    Route::get('/setup', function () {
        try {
            // 6 ocak oluştur
            for ($i = 1; $i <= 6; $i++) {
                $set = ceil($i / 2); // 1-2: Set 1, 3-4: Set 2, 5-6: Set 3
                $status = $i <= 3 ? 'active' : 'standby'; // İlk 3 ocak aktif
                
                \App\Models\Furnace::updateOrCreate(
                    ['furnace_number' => $i],
                    [
                        'furnace_set' => $set,
                        'status' => $status,
                        'total_castings' => 0,
                        'max_castings_before_maintenance' => 30,
                        'is_charging' => false
                    ]
                );
            }
            
            // Aktif ocaklarda döküm başlat
            $activeFurnaces = \App\Models\Furnace::where('status', 'active')->get();
            foreach ($activeFurnaces as $furnace) {
                $furnace->startNewCasting();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Sistem başarıyla kuruldu. 6 ocak oluşturuldu, ilk 3 tanesi aktif edildi ve döküm başlatıldı.',
                'data' => [
                    'total_furnaces' => 6,
                    'active_furnaces' => 3,
                    'active_castings' => $activeFurnaces->count()
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sistem kurulumu sırasında hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    })->name('setup');
    
    // Test verisi temizleme
    Route::get('/cleanup', function () {
        try {
            \App\Models\QualityControl::truncate();
            \App\Models\Casting::truncate();
            \App\Models\DailyReport::truncate();
            \App\Models\Furnace::truncate();
            
            return response()->json([
                'success' => true,
                'message' => 'Tüm test verileri temizlendi.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Veri temizleme sırasında hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    })->name('cleanup');
});
