<?php

namespace App\Console\Commands;

use App\Models\DailyReport;
use Illuminate\Console\Command;
use Carbon\Carbon;

/**
 * Günlük Rapor Oluşturma Komutu
 * 
 * Kullanım: php artisan reports:daily
 * 
 * Bu komut her gün sabah 08:00'da çalışır ve:
 * - Önceki günün raporunu oluşturur
 * - Döküm istatistiklerini hesaplar
 * - Verimlilik analizini yapar
 */
class GenerateDailyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:daily {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Günlük üretim raporunu oluşturur (varsayılan: bugün için)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = $this->argument('date') ?? now()->toDateString();
        
        $this->info("Günlük rapor oluşturuluyor: " . Carbon::parse($date)->format('d.m.Y'));
        
        try {
            // Günlük rapor oluştur
            $report = DailyReport::generateTodaysReport($date);
            
            $this->info("✓ Günlük rapor başarıyla oluşturuldu");
            
            // Rapor özetini göster
            $summary = $report->getSummary();
            
            $this->table(
                ['Metrik', 'Değer'],
                [
                    ['Tarih', $summary['date']],
                    ['Toplam Döküm', $summary['total_castings']],
                    ['Verimlilik', $summary['efficiency']],
                    ['Aktif Ocak', $summary['active_furnaces']],
                    ['Bakım Sayısı', $summary['maintenance_count']],
                    ['En Verimli Ocak', $summary['top_performing_furnace'] ? 
                        $summary['top_performing_furnace']['furnace_number'] . '. Ocak (' . 
                        $summary['top_performing_furnace']['casting_count'] . ' döküm)' : 'Yok'],
                    ['Verimlilik Durumu', $summary['efficiency_status']['text']]
                ]
            );
            
            // Ocak bazında detaylar
            if (!empty($report->furnace_castings)) {
                $this->info("\nOcak Bazında Döküm Sayıları:");
                foreach ($report->furnace_castings as $furnaceNumber => $castingCount) {
                    $this->info("  {$furnaceNumber}. Ocak: {$castingCount} döküm");
                }
            }
            
            // Bakım faaliyetleri
            if (!empty($report->maintenance_activities)) {
                $this->info("\nBakım Faaliyetleri:");
                foreach ($report->maintenance_activities as $activity) {
                    $this->info("  {$activity['furnace_number']}. Ocak - {$activity['maintenance_date']} - {$activity['reason']}");
                }
            }
            
            // Verimlilik değerlendirmesi
            $efficiency = $report->production_efficiency;
            if ($efficiency >= 90) {
                $this->info("\n🎉 Mükemmel verimlilik! Hedefin üzerinde performans.");
            } elseif ($efficiency >= 75) {
                $this->info("\n👍 İyi verimlilik. Hedeflere yakın performans.");
            } elseif ($efficiency >= 60) {
                $this->warn("\n⚠️ Orta verimlilik. İyileştirme fırsatları mevcut.");
            } else {
                $this->error("\n❌ Düşük verimlilik. Acil iyileştirme gerekli.");
            }
            
        } catch (\Exception $e) {
            $this->error("Günlük rapor oluşturulurken hata: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
