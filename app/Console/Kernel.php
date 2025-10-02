<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Otomatik döküm tamamlama - Her dakika çalışır
        // 7/24 kesintisiz üretim için kritik
        $schedule->command('castings:auto-complete')
                 ->everyMinute()
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/auto-complete.log'));

        // Günlük rapor oluşturma - Her gün sabah 08:00'da
        // Vardiya değişimi sırasında önceki günün raporunu oluşturur
        $schedule->command('reports:daily')
                 ->dailyAt('08:00')
                 ->timezone('Europe/Istanbul')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/daily-reports.log'));

        // Veritabanı yedekleme - Her gün gece 02:00'da
        // Kritik üretim verilerinin güvenliği için
        $schedule->command('backup:run')
                 ->dailyAt('02:00')
                 ->timezone('Europe/Istanbul')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/backup.log'));

        // Log dosyalarını temizleme - Haftalık
        // Disk alanını korumak için eski logları temizler
        $schedule->command('log:clear')
                 ->weekly()
                 ->sundays()
                 ->at('03:00')
                 ->timezone('Europe/Istanbul');

        // Sistem durumu kontrolü - Her 5 dakikada bir
        // Ocakların durumunu ve sistem sağlığını kontrol eder
        $schedule->call(function () {
            \Log::info('Sistem durumu kontrol edildi', [
                'active_furnaces' => \App\Models\Furnace::where('status', 'active')->count(),
                'active_castings' => \App\Models\Casting::where('status', 'in_progress')->count(),
                'maintenance_needed' => \App\Models\Furnace::getFurnacesNeedingMaintenance()->count(),
                'timestamp' => now()->toISOString()
            ]);
        })->everyFiveMinutes()->name('system-health-check');

        // Kalite kontrol değerlendirmesi - Her saat başı
        // Beklemedeki testleri otomatik değerlendirir
        $schedule->call(function () {
            $pendingTests = \App\Models\QualityControl::where('test_result', 'pending')->get();
            $evaluatedCount = 0;
            
            foreach ($pendingTests as $qc) {
                $qc->evaluateTestResult();
                $evaluatedCount++;
            }
            
            if ($evaluatedCount > 0) {
                \Log::info("Otomatik kalite kontrol değerlendirmesi: {$evaluatedCount} test değerlendirildi");
            }
        })->hourly()->name('quality-control-evaluation');

        // Haftalık performans raporu - Pazartesi sabah 09:00
        $schedule->call(function () {
            // TODO: Haftalık performans raporu oluşturma
            \Log::info('Haftalık performans raporu oluşturuldu');
        })->weekly()->mondays()->at('09:00')->name('weekly-performance-report');

        // Aylık bakım planlaması - Ayın ilk günü 10:00
        $schedule->call(function () {
            // TODO: Aylık bakım planlaması ve uyarıları
            \Log::info('Aylık bakım planlaması güncellendi');
        })->monthly()->at('10:00')->name('monthly-maintenance-planning');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
