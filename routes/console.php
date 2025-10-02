<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Çelik Fabrikası özel komutları

Artisan::command('factory:status', function () {
    $this->info('🏭 Çelik Fabrikası Sistem Durumu');
    $this->info('================================');
    
    // Ocak durumları
    $furnaces = \App\Models\Furnace::all();
    $activeFurnaces = $furnaces->where('status', 'active');
    $maintenanceFurnaces = $furnaces->where('status', 'maintenance');
    $standbyFurnaces = $furnaces->where('status', 'standby');
    
    $this->info("Toplam Ocak: {$furnaces->count()}");
    $this->info("Aktif Ocak: {$activeFurnaces->count()}");
    $this->info("Bakımdaki Ocak: {$maintenanceFurnaces->count()}");
    $this->info("Beklemedeki Ocak: {$standbyFurnaces->count()}");
    
    // Döküm durumları
    $activeCastings = \App\Models\Casting::where('status', 'in_progress')->count();
    $todaysCastings = \App\Models\Casting::where('production_date', now()->toDateString())->count();
    
    $this->info("Devam Eden Döküm: {$activeCastings}");
    $this->info("Bugünkü Toplam Döküm: {$todaysCastings}");
    
    // Kalite kontrol
    $pendingQC = \App\Models\QualityControl::where('test_result', 'pending')->count();
    $passedQC = \App\Models\QualityControl::where('test_result', 'passed')
                     ->whereDate('test_time', now()->toDateString())->count();
    $failedQC = \App\Models\QualityControl::where('test_result', 'failed')
                     ->whereDate('test_time', now()->toDateString())->count();
    
    $this->info("Bekleyen Kalite Kontrol: {$pendingQC}");
    $this->info("Bugün Başarılı Test: {$passedQC}");
    $this->info("Bugün Başarısız Test: {$failedQC}");
    
    // Sistem sağlığı
    $this->newLine();
    if ($activeFurnaces->count() === 3 && $activeCastings === 3) {
        $this->info('✅ Sistem sağlıklı - Normal operasyon');
    } else {
        $this->warn('⚠️ Sistem dikkat gerektirir');
        if ($activeFurnaces->count() < 3) {
            $this->warn("   - Aktif ocak sayısı yetersiz ({$activeFurnaces->count()}/3)");
        }
        if ($activeCastings < 3) {
            $this->warn("   - Devam eden döküm sayısı yetersiz ({$activeCastings}/3)");
        }
    }
    
})->purpose('Fabrika sistem durumunu gösterir');

Artisan::command('factory:setup', function () {
    $this->info('🏭 Çelik Fabrikası Sistem Kurulumu');
    $this->info('==================================');
    
    try {
        // 6 ocak oluştur
        for ($i = 1; $i <= 6; $i++) {
            $set = ceil($i / 2); // 1-2: Set 1, 3-4: Set 2, 5-6: Set 3
            $status = $i <= 3 ? 'active' : 'standby'; // İlk 3 ocak aktif
            
            $furnace = \App\Models\Furnace::updateOrCreate(
                ['furnace_number' => $i],
                [
                    'furnace_set' => $set,
                    'status' => $status,
                    'total_castings' => 0,
                    'max_castings_before_maintenance' => 30,
                    'is_charging' => false
                ]
            );
            
            $this->info("✓ {$i}. Ocak oluşturuldu (Set {$set}, Durum: {$status})");
        }
        
        // Aktif ocaklarda döküm başlat
        $activeFurnaces = \App\Models\Furnace::where('status', 'active')->get();
        foreach ($activeFurnaces as $furnace) {
            $casting = $furnace->startNewCasting();
            if ($casting) {
                $this->info("✓ {$furnace->furnace_number}. Ocakta döküm başlatıldı (#{$casting->global_casting_number})");
            }
        }
        
        $this->info("\n🎉 Sistem kurulumu tamamlandı!");
        $this->info("- 6 ocak oluşturuldu");
        $this->info("- İlk 3 ocak aktif edildi");
        $this->info("- Her aktif ocakta döküm başlatıldı");
        $this->info("\nSistem artık 7/24 otomatik operasyona hazır.");
        
    } catch (\Exception $e) {
        $this->error("Kurulum sırasında hata: " . $e->getMessage());
    }
    
})->purpose('Fabrika sistemini kurar ve başlatır');

Artisan::command('factory:reset', function () {
    if ($this->confirm('Tüm fabrika verilerini silmek istediğinizden emin misiniz?')) {
        \App\Models\QualityControl::truncate();
        \App\Models\Casting::truncate();
        \App\Models\DailyReport::truncate();
        \App\Models\Furnace::truncate();
        
        $this->info('✓ Tüm fabrika verileri temizlendi.');
        $this->info('Yeni kurulum için: php artisan factory:setup');
    }
})->purpose('Tüm fabrika verilerini siler');

Artisan::command('factory:simulate {hours=24}', function () {
    $hours = (int) $this->argument('hours');
    $this->info("🎲 {$hours} saatlik üretim simülasyonu başlatılıyor...");
    
    // Bu komut test amaçlı hızlı veri üretimi için kullanılabilir
    // Gerçek zamanlı test yapmak için
    
    $this->info('Simülasyon tamamlandı.');
    
})->purpose('Test amaçlı üretim simülasyonu yapar');
