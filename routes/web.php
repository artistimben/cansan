<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FurnaceController;
use App\Http\Controllers\SampleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CastingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Cansan Çelik Üretim Fabrikası Kalite Kontrol Sistemi
| Web rotaları burada tanımlanır
|
*/

// Ana sayfa - Welcome sayfası
Route::get('/', function () {
    return view('welcome');
});

// Dashboard (Kontrol Paneli)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Debug route
Route::get('/debug/furnace-status', [App\Http\Controllers\DebugController::class, 'furnaceStatus'])->name('debug.furnace-status');
Route::get('/debug/active-castings', [App\Http\Controllers\DebugController::class, 'activeCastings'])->name('debug.active-castings');

// Force set rule
Route::get('/force-set-rule', [App\Http\Controllers\ForceSetRuleController::class, 'enforceSetRule'])->name('force.set-rule');

// API Routes
Route::prefix('api')->group(function () {
    Route::get('/furnaces/{furnace}/active-casting-check', [App\Http\Controllers\FurnaceController::class, 'checkActiveCasting'])->name('api.furnaces.active-casting-check');
});

// Ocak Yönetimi
Route::prefix('furnace-management')->name('furnace-management.')->group(function () {
    Route::get('/', [App\Http\Controllers\FurnaceManagementController::class, 'index'])->name('index');
    Route::post('/{furnace}/change-refractory', [App\Http\Controllers\FurnaceManagementController::class, 'changeRefractory'])->name('change-refractory');
    Route::post('/{furnace}/start-maintenance', [App\Http\Controllers\FurnaceManagementController::class, 'startMaintenance'])->name('start-maintenance');
    Route::post('/{furnace}/end-maintenance', [App\Http\Controllers\FurnaceManagementController::class, 'endMaintenance'])->name('end-maintenance');
    Route::post('/{furnace}/shutdown', [App\Http\Controllers\FurnaceManagementController::class, 'shutdown'])->name('shutdown');
    Route::post('/{furnace}/end-shutdown', [App\Http\Controllers\FurnaceManagementController::class, 'endShutdown'])->name('end-shutdown');
    Route::post('/{furnace}/reset-casting-count', [App\Http\Controllers\FurnaceManagementController::class, 'resetCastingCount'])->name('reset-casting-count');
    Route::get('/{furnace}/status-history', [App\Http\Controllers\FurnaceManagementController::class, 'statusHistory'])->name('status-history');
    Route::get('/{furnace}/statistics', [App\Http\Controllers\FurnaceManagementController::class, 'statistics'])->name('statistics');
});

// Ocak Raporları
Route::prefix('furnace-reports')->name('furnace-reports.')->group(function () {
    Route::get('/', [App\Http\Controllers\FurnaceReportController::class, 'index'])->name('index');
    Route::get('/furnace/{furnace}', [App\Http\Controllers\FurnaceReportController::class, 'furnaceDetail'])->name('furnace-detail');
    Route::get('/maintenance', [App\Http\Controllers\FurnaceReportController::class, 'maintenanceReport'])->name('maintenance');
    Route::get('/shutdown', [App\Http\Controllers\FurnaceReportController::class, 'shutdownReport'])->name('shutdown');
    Route::get('/samples', [App\Http\Controllers\FurnaceReportController::class, 'sampleReport'])->name('samples');
});

Route::get('/dashboard/realtime-status', [DashboardController::class, 'getRealtimeStatus'])->name('dashboard.realtime');
Route::get('/dashboard/health-check', [DashboardController::class, 'healthCheck'])->name('dashboard.health');

// Ocak Yönetimi
Route::prefix('furnaces')->name('furnaces.')->group(function () {
    Route::get('/', [FurnaceController::class, 'index'])->name('index');
    Route::get('/create', [FurnaceController::class, 'create'])->name('create');
    Route::post('/', [FurnaceController::class, 'store'])->name('store');
    Route::get('/{furnace}', [FurnaceController::class, 'show'])->name('show');
    Route::get('/{furnace}/edit', [FurnaceController::class, 'edit'])->name('edit');
    Route::put('/{furnace}', [FurnaceController::class, 'update'])->name('update');
    Route::delete('/{furnace}', [FurnaceController::class, 'destroy'])->name('destroy');
    Route::post('/{furnace}/update-status', [FurnaceController::class, 'updateStatus'])->name('update-status');
    Route::post('/{furnace}/toggle-status', [FurnaceController::class, 'toggleStatus'])->name('toggle-status');
    Route::post('/{furnace}/start-casting', [FurnaceController::class, 'startCasting'])->name('start-casting');
    Route::post('/{furnace}/castings/{casting}/complete', [FurnaceController::class, 'completeCasting'])->name('complete-casting');
    Route::get('/{furnace}/performance-report', [FurnaceController::class, 'performanceReport'])->name('performance-report');
    Route::post('/{furnace}/update-operational-status', [FurnaceController::class, 'updateOperationalStatus'])->name('update-operational-status');
    Route::get('/export', [FurnaceController::class, 'export'])->name('export');
});

// Prova Yönetimi
Route::prefix('samples')->name('samples.')->group(function () {
    Route::get('/', [SampleController::class, 'index'])->name('index');
    Route::get('/create', [SampleController::class, 'create'])->name('create');
    Route::post('/', [SampleController::class, 'store'])->name('store');
    Route::get('/{sample}', [SampleController::class, 'show'])->name('show');
    Route::get('/{sample}/edit', [SampleController::class, 'edit'])->name('edit');
    Route::put('/{sample}', [SampleController::class, 'update'])->name('update');
    Route::delete('/{sample}', [SampleController::class, 'destroy'])->name('destroy');
    Route::post('/{sample}/update-quality-status', [SampleController::class, 'updateQualityStatus'])->name('update-quality-status');
    Route::post('/{sample}/record-radio-report', [SampleController::class, 'recordRadioReport'])->name('record-radio-report');
    Route::get('/reports/quality', [SampleController::class, 'qualityReport'])->name('quality-report');
    Route::get('/pending/list', [SampleController::class, 'pending'])->name('pending');
});

// Döküm Yönetimi
Route::prefix('castings')->name('castings.')->group(function () {
    Route::get('/', [CastingController::class, 'index'])->name('index');
    Route::get('/create', [CastingController::class, 'create'])->name('create');
    Route::post('/', [CastingController::class, 'store'])->name('store');
    Route::get('/{casting}', [CastingController::class, 'show'])->name('show');
    Route::get('/{casting}/edit', [CastingController::class, 'edit'])->name('edit');
    Route::put('/{casting}', [CastingController::class, 'update'])->name('update');
    Route::delete('/{casting}', [CastingController::class, 'destroy'])->name('destroy');
    Route::post('/{casting}/complete', [CastingController::class, 'complete'])->name('complete');
    Route::post('/{casting}/cancel', [CastingController::class, 'cancel'])->name('cancel');
});

// Raporlama Sistemi
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/daily', [ReportController::class, 'daily'])->name('daily');
    Route::get('/weekly', [ReportController::class, 'weekly'])->name('weekly');
    Route::get('/monthly', [ReportController::class, 'monthly'])->name('monthly');
    Route::get('/export', [ReportController::class, 'export'])->name('export');
});

// API Routes (AJAX çağrıları için)
Route::prefix('api')->name('api.')->group(function () {
    // Gerçek zamanlı durum güncellemeleri
    Route::get('/dashboard/status', [DashboardController::class, 'getRealtimeStatus'])->name('dashboard.status');
    
    // Ocak durum güncellemeleri
    Route::post('/furnaces/{furnace}/toggle', [FurnaceController::class, 'toggleStatus'])->name('furnaces.toggle');
    Route::post('/furnaces/{furnace}/toggle-status', [FurnaceController::class, 'toggleStatus'])->name('furnaces.toggle-status');
    Route::post('/furnaces/{furnace}/casting/start', [FurnaceController::class, 'startCasting'])->name('furnaces.casting.start');
    Route::post('/furnaces/{furnace}/casting/{casting}/complete', [FurnaceController::class, 'completeCasting'])->name('furnaces.casting.complete');
    Route::get('/furnaces/{furnace}/next-casting-number', [FurnaceController::class, 'getNextCastingNumber'])->name('furnaces.next-casting-number');
    
    // Sıcaklık yönetimi route'ları
    Route::post('/furnaces/{furnace}/temperature-log', [FurnaceController::class, 'addTemperatureLog'])->name('furnaces.add-temperature-log');
    Route::get('/furnaces/{furnace}/temperature-history', [FurnaceController::class, 'temperatureHistory'])->name('furnaces.temperature-history');
    
    // Prova işlemleri
    Route::post('/samples/{sample}/quality-status', [SampleController::class, 'updateQualityStatus'])->name('samples.quality-status');
    Route::post('/samples/{sample}/radio-report', [SampleController::class, 'recordRadioReport'])->name('samples.radio-report');
    
    // Rapor verileri
    Route::get('/reports/daily-data', [ReportController::class, 'daily'])->name('reports.daily-data');
    Route::get('/reports/weekly-data', [ReportController::class, 'weekly'])->name('reports.weekly-data');
    Route::get('/reports/monthly-data', [ReportController::class, 'monthly'])->name('reports.monthly-data');
});

/*
|--------------------------------------------------------------------------
| Yardımcı Rotalar
|--------------------------------------------------------------------------
*/

// Sistem durumu kontrolü (monitoring için)
Route::get('/health', [DashboardController::class, 'healthCheck'])->name('health');
