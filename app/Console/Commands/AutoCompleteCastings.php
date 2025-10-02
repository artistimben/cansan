<?php

namespace App\Console\Commands;

use App\Models\Casting;
use App\Models\Furnace;
use Illuminate\Console\Command;
use Carbon\Carbon;

/**
 * Otomatik Döküm Tamamlama Komutu
 * 
 * Kullanım: php artisan castings:auto-complete
 * 
 * Bu komut her dakika çalışır ve:
 * - Süresi dolmuş dökümler otomatik tamamlar
 * - Yeni dökümler başlatır
 * - 7/24 kesintisiz üretim sağlar
 */
class AutoCompleteCastings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'castings:auto-complete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Süresi dolmuş dökümler otomatik tamamlar ve yeni dökümler başlatır';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Otomatik döküm tamamlama işlemi başlatıldı...');
        
        // Devam eden dökümler
        $activeCastings = Casting::where('status', 'in_progress')->get();
        $completedCount = 0;
        $startedCount = 0;
        
        foreach ($activeCastings as $casting) {
            $elapsedMinutes = $casting->start_time->diffInMinutes(now());
            
            // Döküm süresi dolmuş mu kontrol et (120 dakika + 5 dakika tolerans)
            if ($elapsedMinutes >= ($casting->duration_minutes + 5)) {
                $this->info("Döküm tamamlanıyor: Ocak {$casting->furnace->furnace_number}, Döküm {$casting->casting_number_per_furnace}");
                
                // Döküm tamamla
                $casting->complete();
                $completedCount++;
                
                $this->info("✓ Döküm tamamlandı ve yeni döküm başlatıldı");
            }
        }
        
        // Aktif ocaklarda döküm olmayan durumları kontrol et
        $activeFurnaces = Furnace::where('status', 'active')->get();
        
        foreach ($activeFurnaces as $furnace) {
            $currentCasting = $furnace->getCurrentCasting();
            
            if (!$currentCasting) {
                $this->info("Boş ocak tespit edildi: {$furnace->furnace_number}. Ocak");
                
                // Yeni döküm başlat
                $newCasting = $furnace->startNewCasting();
                
                if ($newCasting) {
                    $startedCount++;
                    $this->info("✓ Yeni döküm başlatıldı: {$furnace->furnace_number}. Ocak, Döküm {$newCasting->casting_number_per_furnace}");
                } else {
                    $this->warn("⚠ {$furnace->furnace_number}. Ocakta döküm başlatılamadı");
                }
            }
        }
        
        // Bakıma gitmesi gereken ocakları kontrol et
        $maintenanceNeeded = Furnace::getFurnacesNeedingMaintenance();
        foreach ($maintenanceNeeded as $furnace) {
            $currentCasting = $furnace->getCurrentCasting();
            
            // Mevcut döküm tamamlandıysa bakıma gönder
            if (!$currentCasting) {
                $this->info("Ocak bakıma gönderiliyor: {$furnace->furnace_number}. Ocak");
                $furnace->goToMaintenance();
                $this->info("✓ {$furnace->furnace_number}. Ocak bakıma gönderildi, yedek ocak aktif edildi");
            }
        }
        
        $this->info("İşlem tamamlandı:");
        $this->info("- Tamamlanan döküm: {$completedCount}");
        $this->info("- Başlatılan döküm: {$startedCount}");
        $this->info("- Aktif ocak sayısı: " . Furnace::where('status', 'active')->count());
        $this->info("- Devam eden döküm: " . Casting::where('status', 'in_progress')->count());
        
        return 0;
    }
}
