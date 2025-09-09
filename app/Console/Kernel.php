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
        // Her saat başı sistem durumu kontrolü
        $schedule->command('cansan:status')->hourly();

        // Her gün gece 02:00'de günlük rapor oluştur
        $schedule->command('cansan:daily-report')->dailyAt('02:00');

        // Her 2 saatte bir prova hatırlatması
        $schedule->command('cansan:sample-reminder')->everyTwoHours();

        // Her hafta pazar günü gece 03:00'te veritabanı temizliği
        $schedule->command('cansan:cleanup')->weekly()->sundays()->at('03:00');

        // Her 4 saatte bir kalite kontrol raporu
        $schedule->command('cansan:quality-check')->everyFourHours();
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
