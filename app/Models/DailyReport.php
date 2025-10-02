<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Günlük Rapor Modeli
 * 
 * Kullanım:
 * $report = DailyReport::generateTodaysReport(); // Bugünün raporunu oluşturur
 * $reports = DailyReport::getWeeklyReports(); // Haftalık raporları getirir
 */
class DailyReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_date',
        'total_castings',
        'furnace_castings',
        'active_furnaces_count',
        'maintenance_activities',
        'production_efficiency',
        'shift_start_time',
        'shift_end_time',
        'notes'
    ];

    protected $casts = [
        'report_date' => 'date',
        'furnace_castings' => 'array',
        'maintenance_activities' => 'array',
        'production_efficiency' => 'decimal:2',
        'shift_start_time' => 'datetime:H:i',
        'shift_end_time' => 'datetime:H:i'
    ];

    /**
     * Bugünün raporunu oluşturur (sabah 08:00'da çalışır)
     */
    public static function generateTodaysReport($date = null)
    {
        $reportDate = $date ?? now()->toDateString();
        
        // Önceki günün 08:00'ından bugünün 08:00'ına kadar olan dökümler
        $startTime = Carbon::parse($reportDate)->subDay()->setTime(8, 0, 0);
        $endTime = Carbon::parse($reportDate)->setTime(8, 0, 0);

        // Döküm istatistikleri
        $castings = Casting::whereBetween('start_time', [$startTime, $endTime])->get();
        $totalCastings = $castings->count();
        
        // Ocak bazında döküm sayıları
        $furnaceCastings = [];
        $activeFurnaces = Furnace::getActiveFurnaces();
        
        foreach ($activeFurnaces as $furnace) {
            $furnaceCastingCount = $castings->where('furnace_id', $furnace->id)->count();
            $furnaceCastings[$furnace->furnace_number] = $furnaceCastingCount;
        }

        // Bakım faaliyetleri
        $maintenanceActivities = [];
        $maintenanceFurnaces = Furnace::where('status', 'maintenance')
                                     ->whereBetween('last_maintenance_date', [$startTime, $endTime])
                                     ->get();
        
        foreach ($maintenanceFurnaces as $furnace) {
            $maintenanceActivities[] = [
                'furnace_number' => $furnace->furnace_number,
                'maintenance_date' => $furnace->last_maintenance_date->format('Y-m-d H:i'),
                'reason' => 'Refraktör değişimi - ' . $furnace->total_castings . ' döküm sonrası'
            ];
        }

        // Üretim verimliliği hesaplama
        $expectedCastingsPerDay = 36; // 3 ocak × 12 döküm (24 saat / 2 saat)
        $efficiency = $totalCastings > 0 ? ($totalCastings / $expectedCastingsPerDay) * 100 : 0;

        // Raporu kaydet
        $report = self::updateOrCreate(
            ['report_date' => $reportDate],
            [
                'total_castings' => $totalCastings,
                'furnace_castings' => $furnaceCastings,
                'active_furnaces_count' => $activeFurnaces->count(),
                'maintenance_activities' => $maintenanceActivities,
                'production_efficiency' => $efficiency,
                'shift_start_time' => '08:00:00',
                'shift_end_time' => '08:00:00'
            ]
        );

        return $report;
    }

    /**
     * Haftalık raporları getirir
     */
    public static function getWeeklyReports($startDate = null)
    {
        $startDate = $startDate ?? now()->subDays(7)->toDateString();
        $endDate = now()->toDateString();

        return self::whereBetween('report_date', [$startDate, $endDate])
                   ->orderBy('report_date', 'desc')
                   ->get();
    }

    /**
     * Aylık raporları getirir
     */
    public static function getMonthlyReports($year = null, $month = null)
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        return self::whereYear('report_date', $year)
                   ->whereMonth('report_date', $month)
                   ->orderBy('report_date', 'desc')
                   ->get();
    }

    /**
     * Rapor özetini getirir
     */
    public function getSummary()
    {
        return [
            'date' => $this->report_date->format('d.m.Y'),
            'total_castings' => $this->total_castings,
            'efficiency' => number_format($this->production_efficiency, 1) . '%',
            'active_furnaces' => $this->active_furnaces_count,
            'maintenance_count' => count($this->maintenance_activities ?? []),
            'top_performing_furnace' => $this->getTopPerformingFurnace(),
            'efficiency_status' => $this->getEfficiencyStatus()
        ];
    }

    /**
     * En verimli ocağı bulur
     */
    private function getTopPerformingFurnace()
    {
        if (empty($this->furnace_castings)) {
            return null;
        }

        $maxCastings = max($this->furnace_castings);
        $topFurnace = array_search($maxCastings, $this->furnace_castings);

        return [
            'furnace_number' => $topFurnace,
            'casting_count' => $maxCastings
        ];
    }

    /**
     * Verimlilik durumunu değerlendirir
     */
    private function getEfficiencyStatus()
    {
        if ($this->production_efficiency >= 90) {
            return ['status' => 'excellent', 'text' => 'Mükemmel', 'color' => 'success'];
        } elseif ($this->production_efficiency >= 75) {
            return ['status' => 'good', 'text' => 'İyi', 'color' => 'info'];
        } elseif ($this->production_efficiency >= 60) {
            return ['status' => 'average', 'text' => 'Orta', 'color' => 'warning'];
        } else {
            return ['status' => 'poor', 'text' => 'Düşük', 'color' => 'danger'];
        }
    }

    /**
     * Rapor verilerini Excel formatında hazırlar
     */
    public function toExcelData()
    {
        return [
            'Tarih' => $this->report_date->format('d.m.Y'),
            'Toplam Döküm' => $this->total_castings,
            'Aktif Ocak Sayısı' => $this->active_furnaces_count,
            'Verimlilik (%)' => number_format($this->production_efficiency, 1),
            'Bakım Sayısı' => count($this->maintenance_activities ?? []),
            '1. Ocak Döküm' => $this->furnace_castings[1] ?? 0,
            '2. Ocak Döküm' => $this->furnace_castings[2] ?? 0,
            '3. Ocak Döküm' => $this->furnace_castings[3] ?? 0,
            '4. Ocak Döküm' => $this->furnace_castings[4] ?? 0,
            '5. Ocak Döküm' => $this->furnace_castings[5] ?? 0,
            '6. Ocak Döküm' => $this->furnace_castings[6] ?? 0,
            'Notlar' => $this->notes
        ];
    }
}
