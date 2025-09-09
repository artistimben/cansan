<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Sample;
use App\Models\Casting;
use App\Models\Furnace;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| Cansan Çelik Üretim Fabrikası Kalite Kontrol Sistemi
| Konsol komutları ve zamanlanmış görevler
|
*/

// İlham verici alıntı komutu
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sistem durumu kontrol komutu
Artisan::command('cansan:status', function () {
    $this->info('Cansan Kalite Kontrol Sistemi Durum Raporu');
    $this->info('=====================================');
    
    // Aktif ocak sayısı
    $activeFurnaces = Furnace::active()->count();
        $this->line("Aktif Ocak Sayısı: " . $activeFurnaces);
    
    // Bugünkü döküm sayısı
    $todayCastings = Casting::whereDate('casting_date', today())->count();
    $this->line("Bugünkü Döküm Sayısı: " . $todayCastings);
    
    // Bugünkü prova sayısı
    $todaySamples = Sample::whereDate('sample_date', today())->count();
    $this->line("Bugünkü Prova Sayısı: " . $todaySamples);
    
    // Bekleyen prova sayısı
    $pendingSamples = Sample::pending()->count();
    $this->line("Bekleyen Prova Sayısı: " . $pendingSamples);
    
    if ($pendingSamples > 10) {
        $this->warn("⚠️  Çok fazla prova analiz bekliyor!");
    }
    
    $this->info('=====================================');
    $this->info('Sistem normal çalışıyor ✅');
    
})->purpose('Display system status');

// Veritabanı temizleme komutu
Artisan::command('cansan:cleanup', function () {
    $this->info('Veritabanı temizleme işlemi başlatılıyor...');
    
    // 1 yıldan eski kayıtları temizle
    $oneYearAgo = now()->subYear();
    
    $oldSamples = Sample::where('sample_date', '<', $oneYearAgo)->count();
    Sample::where('sample_date', '<', $oneYearAgo)->delete();
    $this->line("Silinen eski prova kayıtları: " . $oldSamples);
    
    $oldCastings = Casting::where('casting_date', '<', $oneYearAgo)->count();
    Casting::where('casting_date', '<', $oneYearAgo)->delete();
    $this->line("Silinen eski döküm kayıtları: " . $oldCastings);
    
    $this->info('Temizleme işlemi tamamlandı ✅');
    
})->purpose('Clean up old database records');

// Günlük rapor oluşturma komutu
Artisan::command('cansan:daily-report {date?}', function ($date = null) {
    $reportDate = $date ? \Carbon\Carbon::parse($date) : today();
    
    $this->info("Günlük Rapor - " . $reportDate->format('d.m.Y'));
    $this->info('================================');
    
    // Günlük istatistikler
    $castings = Casting::whereDate('casting_date', $reportDate)->count();
    $samples = Sample::whereDate('sample_date', $reportDate)->count();
    $approvedSamples = Sample::whereDate('sample_date', $reportDate)->approved()->count();
    $rejectedSamples = Sample::whereDate('sample_date', $reportDate)->rejected()->count();
    
    $this->table(
        ['Metrik', 'Değer'],
        [
            ['Toplam Döküm', $castings],
            ['Toplam Prova', $samples],
            ['Onaylanan Prova', $approvedSamples],
            ['Reddedilen Prova', $rejectedSamples],
            ['Kalite Oranı', ($samples > 0 ? round(($approvedSamples / $samples) * 100, 1) . '%' : '0%')]
        ]
    );
    
})->purpose('Generate daily report for specified date');

// Prova hatırlatma komutu
Artisan::command('cansan:sample-reminder', function () {
    // 2 saatten uzun süre prova alınmayan aktif dökümleri bul
    $activeCastings = Casting::active()->with('samples')->get();
    
    $needsAttention = [];
    foreach ($activeCastings as $casting) {
        $lastSample = $casting->samples()->latest('sample_date')->first();
        
        if (!$lastSample || $lastSample->sample_date->diffInHours(now()) > 2) {
            $needsAttention[] = [
                'Ocak' => ($casting->furnace->name ?? 'Bilinmiyor'),
                'Döküm #' => $casting->casting_number,
                'Son Prova' => ($lastSample ? $lastSample->sample_date->format('H:i') : 'Hiç alınmamış'),
                'Süre' => ($lastSample ? $lastSample->sample_date->diffForHumans() : 'N/A')
            ];
        }
    }
    
    if (empty($needsAttention)) {
        $this->info('Tüm aktif dökümlerde prova takibi normal ✅');
    } else {
        $this->warn('⚠️  Dikkat gerektiren dökümler:');
        $this->table(
            ['Ocak', 'Döküm #', 'Son Prova', 'Süre'],
            $needsAttention
        );
    }
    
})->purpose('Check for castings that need sample attention');

// Kalite standardı kontrol komutu
Artisan::command('cansan:quality-check', function () {
    $this->info('Kalite Standardı Kontrol Raporu');
    $this->info('==============================');
    
    // Son 24 saatteki provalar
    $recentSamples = Sample::where('sample_date', '>', now()->subDay())
                          ->where('quality_status', '!=', 'pending')
                          ->with('casting.furnace')
                          ->get();
    
    if ($recentSamples->isEmpty()) {
        $this->warn('Son 24 saatte analiz edilmiş prova bulunamadı');
        return;
    }
    
    // Kalite dağılımı
    $approved = $recentSamples->where('quality_status', 'approved')->count();
    $rejected = $recentSamples->where('quality_status', 'rejected')->count();
    $needsAdjustment = $recentSamples->where('quality_status', 'needs_adjustment')->count();
    
    $this->table(
        ['Durum', 'Adet', 'Yüzde'],
        [
            ['Onaylanan', $approved, round(($approved / $recentSamples->count()) * 100, 1) . '%'],
            ['Reddedilen', $rejected, round(($rejected / $recentSamples->count()) * 100, 1) . '%'],
            ['Düzeltme Gerekli', $needsAdjustment, round(($needsAdjustment / $recentSamples->count()) * 100, 1) . '%']
        ]
    );
    
    // Problemli provalar
    $problematic = $recentSamples->whereIn('quality_status', ['rejected', 'needs_adjustment']);
    if ($problematic->count() > 0) {
        $this->warn("\n⚠️  Problemli Provalar:");
        foreach ($problematic as $sample) {
            $furnaceName = ($sample->casting->furnace->name ?? 'N/A');
            $this->line("- " . $furnaceName . " Döküm #" . $sample->casting->casting_number . " Prova #" . $sample->sample_number . " (" . $sample->quality_status . ")");
        }
    }
    
})->purpose('Check quality standards compliance');

/*
|--------------------------------------------------------------------------
| Zamanlanmış Görevler (Schedule)
|--------------------------------------------------------------------------
| Bu görevler app/Console/Kernel.php dosyasında tanımlanmıştır
*/
