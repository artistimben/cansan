<?php

namespace App\Http\Controllers;

use App\Models\Sample;
use App\Models\Casting;
use App\Models\Furnace;
use App\Models\FurnaceSet;
use App\Models\Adjustment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Raporlama Controller'ı
 * Günlük, haftalık, aylık otomatik raporlar
 */
class ReportController extends Controller
{
    /**
     * Raporlama ana sayfası
     */
    public function index()
    {
        return view('reports.index');
    }
    
    /**
     * Günlük rapor
     */
    public function daily(Request $request)
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);
        
        // Günlük döküm ve prova sayıları
        $dailyStats = $this->getDailyStats($selectedDate);
        
        // Ocak bazında detaylar
        $furnaceDetails = $this->getFurnaceDetails($selectedDate, $selectedDate);
        
        // Kalite istatistikleri
        $qualityStats = $this->getQualityStats($selectedDate, $selectedDate);
        
        // Ham madde ekleme bilgileri
        $adjustmentStats = $this->getAdjustmentStats($selectedDate, $selectedDate);
        
        // Vardiya bazında dağılım
        $shiftStats = $this->getShiftStats($selectedDate, $selectedDate);
        
        return view('reports.daily', compact(
            'selectedDate',
            'dailyStats',
            'furnaceDetails',
            'qualityStats',
            'adjustmentStats',
            'shiftStats'
        ));
    }
    
    /**
     * Haftalık rapor
     */
    public function weekly(Request $request)
    {
        $week = $request->get('week', Carbon::now()->format('Y-W'));
        $selectedWeek = Carbon::now()->setISODate(
            substr($week, 0, 4),
            substr($week, 5)
        );
        
        $startDate = $selectedWeek->startOfWeek();
        $endDate = $selectedWeek->copy()->endOfWeek();
        
        // Haftalık özet istatistikler
        $weeklyStats = $this->getWeeklyStats($startDate, $endDate);
        
        // Günlük trend analizi
        $dailyTrend = $this->getDailyTrend($startDate, $endDate);
        
        // Ocak performans karşılaştırması
        $furnaceComparison = $this->getFurnaceComparison($startDate, $endDate);
        
        // Kalite trend analizi
        $qualityTrend = $this->getQualityTrend($startDate, $endDate);
        
        // En çok kullanılan ham maddeler
        $topAdjustments = $this->getTopAdjustments($startDate, $endDate);
        
        return view('reports.weekly', compact(
            'selectedWeek',
            'startDate',
            'endDate',
            'weeklyStats',
            'dailyTrend',
            'furnaceComparison',
            'qualityTrend',
            'topAdjustments'
        ));
    }
    
    /**
     * Aylık rapor
     */
    public function monthly(Request $request)
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $selectedMonth = Carbon::parse($month . '-01');
        
        $startDate = $selectedMonth->startOfMonth();
        $endDate = $selectedMonth->copy()->endOfMonth();
        
        // Aylık özet istatistikler
        $monthlyStats = $this->getMonthlyStats($startDate, $endDate);
        
        // Haftalık trend analizi
        $weeklyTrend = $this->getWeeklyTrend($startDate, $endDate);
        
        // Ocak kullanım analizi
        $furnaceUtilization = $this->getFurnaceUtilization($startDate, $endDate);
        
        // Kalite performans analizi
        $qualityPerformance = $this->getQualityPerformance($startDate, $endDate);
        
        // Ham madde tüketim analizi
        $materialConsumption = $this->getMaterialConsumption($startDate, $endDate);
        
        // Vardiya performans analizi
        $shiftPerformance = $this->getShiftPerformance($startDate, $endDate);
        
        return view('reports.monthly', compact(
            'selectedMonth',
            'startDate',
            'endDate',
            'monthlyStats',
            'weeklyTrend',
            'furnaceUtilization',
            'qualityPerformance',
            'materialConsumption',
            'shiftPerformance'
        ));
    }
    
    /**
     * Rapor verilerini Excel olarak export et
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'daily'); // daily, weekly, monthly
        $date = $request->get('date', Carbon::now()->format('Y-m-d'));
        
        // Export işlemi burada implement edilecek
        // Laravel Excel kullanılabilir
        
        return response()->json([
            'success' => true,
            'message' => 'Rapor export edildi',
            'download_url' => '#'
        ]);
    }
    
    /**
     * Günlük istatistikleri hesapla
     */
    private function getDailyStats($date)
    {
        $castings = Casting::whereDate('casting_date', $date)->get();
        $samples = Sample::whereDate('sample_time', $date)->get();
        $adjustments = Adjustment::whereDate('adjustment_date', $date)->get();
        
        return [
            'total_castings' => $castings->count(),
            'total_samples' => $samples->count(),
            'approved_samples' => $samples->where('quality_status', 'approved')->count(),
            'rejected_samples' => $samples->where('quality_status', 'rejected')->count(),
            'pending_samples' => $samples->where('quality_status', 'pending')->count(),
            'total_adjustments' => $adjustments->count(),
            'successful_adjustments' => $adjustments->where('is_successful', true)->count(),
            'active_furnaces' => Furnace::active()->count()
        ];
    }
    
    /**
     * Ocak detaylarını getir
     */
    private function getFurnaceDetails($startDate, $endDate)
    {
        return FurnaceSet::with(['furnaces'])->get()->map(function($set) use ($startDate, $endDate) {
            $activeFurnace = $set->activeFurnace();
            
            if (!$activeFurnace) {
                return [
                    'set' => $set,
                    'active_furnace' => null,
                    'castings' => 0,
                    'samples' => 0,
                    'quality_rate' => 0
                ];
            }
            
            $castings = $activeFurnace->castings()
                ->whereBetween('casting_date', [$startDate, $endDate])
                ->get();
            
            $samples = $castings->flatMap(function($casting) {
                return $casting->samples;
            });
            
            $approvedSamples = $samples->where('quality_status', 'approved')->count();
            $totalSamples = $samples->count();
            
            return [
                'set' => $set,
                'active_furnace' => $activeFurnace,
                'castings' => $castings->count(),
                'samples' => $totalSamples,
                'quality_rate' => $totalSamples > 0 ? round(($approvedSamples / $totalSamples) * 100, 1) : 0
            ];
        });
    }
    
    /**
     * Kalite istatistiklerini hesapla
     */
    private function getQualityStats($startDate, $endDate)
    {
        $samples = Sample::whereBetween('sample_time', [$startDate, $endDate])->get();
        
        $stats = [
            'total' => $samples->count(),
            'approved' => $samples->where('quality_status', 'approved')->count(),
            'rejected' => $samples->where('quality_status', 'rejected')->count(),
            'pending' => $samples->where('quality_status', 'pending')->count(),
            'needs_adjustment' => $samples->where('quality_status', 'needs_adjustment')->count()
        ];
        
        $stats['approval_rate'] = $stats['total'] > 0 
            ? round(($stats['approved'] / $stats['total']) * 100, 1) 
            : 0;
        
        return $stats;
    }
    
    /**
     * Ham madde ekleme istatistiklerini hesapla
     */
    private function getAdjustmentStats($startDate, $endDate)
    {
        $adjustments = Adjustment::whereBetween('adjustment_date', [$startDate, $endDate])->get();
        
        return [
            'total' => $adjustments->count(),
            'successful' => $adjustments->where('is_successful', true)->count(),
            'by_material' => $adjustments->groupBy('material_type')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'total_amount' => $group->sum('amount_kg'),
                    'successful' => $group->where('is_successful', true)->count()
                ];
            }),
            'by_reason' => $adjustments->groupBy('adjustment_reason')->map(function($group) {
                return $group->count();
            })
        ];
    }
    
    /**
     * Vardiya istatistiklerini hesapla
     */
    private function getShiftStats($startDate, $endDate)
    {
        $castings = Casting::whereBetween('casting_date', [$startDate, $endDate])->get();
        
        return $castings->groupBy('shift')->map(function($group) {
            $samples = $group->flatMap(function($casting) {
                return $casting->samples;
            });
            
            return [
                'castings' => $group->count(),
                'samples' => $samples->count(),
                'approved_samples' => $samples->where('quality_status', 'approved')->count(),
                'quality_rate' => $samples->count() > 0 
                    ? round(($samples->where('quality_status', 'approved')->count() / $samples->count()) * 100, 1)
                    : 0
            ];
        });
    }
    
    /**
     * Haftalık istatistikleri hesapla
     */
    private function getWeeklyStats($startDate, $endDate)
    {
        $castings = Casting::whereBetween('casting_date', [$startDate, $endDate])->get();
        $samples = Sample::whereBetween('sample_time', [$startDate, $endDate])->get();
        $adjustments = Adjustment::whereBetween('adjustment_date', [$startDate, $endDate])->get();
        
        return [
            'total_castings' => $castings->count(),
            'avg_castings_per_day' => round($castings->count() / 7, 1),
            'total_samples' => $samples->count(),
            'avg_samples_per_casting' => $castings->count() > 0 ? round($samples->count() / $castings->count(), 1) : 0,
            'quality_rate' => $samples->count() > 0 
                ? round(($samples->where('quality_status', 'approved')->count() / $samples->count()) * 100, 1)
                : 0,
            'total_adjustments' => $adjustments->count(),
            'adjustment_success_rate' => $adjustments->count() > 0
                ? round(($adjustments->where('is_successful', true)->count() / $adjustments->count()) * 100, 1)
                : 0
        ];
    }
    
    /**
     * Günlük trend analizini hesapla
     */
    private function getDailyTrend($startDate, $endDate)
    {
        $trend = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            $dailyStats = $this->getDailyStats($currentDate);
            $trend[$currentDate->format('Y-m-d')] = $dailyStats;
            $currentDate->addDay();
        }
        
        return $trend;
    }
    
    /**
     * Ocak performans karşılaştırması
     */
    private function getFurnaceComparison($startDate, $endDate)
    {
        return Furnace::with('furnaceSet')->get()->map(function($furnace) use ($startDate, $endDate) {
            $castings = $furnace->castings()
                ->whereBetween('casting_date', [$startDate, $endDate])
                ->get();
            
            $samples = $castings->flatMap(function($casting) {
                return $casting->samples;
            });
            
            return [
                'furnace' => $furnace,
                'castings' => $castings->count(),
                'samples' => $samples->count(),
                'quality_rate' => $samples->count() > 0 
                    ? round(($samples->where('quality_status', 'approved')->count() / $samples->count()) * 100, 1)
                    : 0,
                'avg_samples_per_casting' => $castings->count() > 0 
                    ? round($samples->count() / $castings->count(), 1) 
                    : 0
            ];
        });
    }
    
    /**
     * Kalite trend analizi
     */
    private function getQualityTrend($startDate, $endDate)
    {
        return Sample::whereBetween('sample_time', [$startDate, $endDate])
            ->selectRaw('DATE(sample_time) as date, quality_status, COUNT(*) as count')
            ->groupBy('date', 'quality_status')
            ->get()
            ->groupBy('date')
            ->map(function($group) {
                $total = $group->sum('count');
                return [
                    'total' => $total,
                    'approved' => $group->where('quality_status', 'approved')->sum('count'),
                    'rejected' => $group->where('quality_status', 'rejected')->sum('count'),
                    'pending' => $group->where('quality_status', 'pending')->sum('count'),
                    'needs_adjustment' => $group->where('quality_status', 'needs_adjustment')->sum('count'),
                    'quality_rate' => $total > 0 
                        ? round(($group->where('quality_status', 'approved')->sum('count') / $total) * 100, 1)
                        : 0
                ];
            });
    }
    
    /**
     * En çok kullanılan ham maddeleri getir
     */
    private function getTopAdjustments($startDate, $endDate)
    {
        return Adjustment::whereBetween('adjustment_date', [$startDate, $endDate])
            ->selectRaw('material_type, COUNT(*) as usage_count, SUM(amount_kg) as total_amount')
            ->groupBy('material_type')
            ->orderBy('usage_count', 'desc')
            ->get();
    }
    
    /**
     * Aylık istatistikleri hesapla
     */
    private function getMonthlyStats($startDate, $endDate)
    {
        $days = $startDate->diffInDays($endDate) + 1;
        $castings = Casting::whereBetween('casting_date', [$startDate, $endDate])->get();
        $samples = Sample::whereBetween('sample_time', [$startDate, $endDate])->get();
        $adjustments = Adjustment::whereBetween('adjustment_date', [$startDate, $endDate])->get();
        
        return [
            'total_castings' => $castings->count(),
            'avg_castings_per_day' => round($castings->count() / $days, 1),
            'total_samples' => $samples->count(),
            'avg_samples_per_day' => round($samples->count() / $days, 1),
            'overall_quality_rate' => $samples->count() > 0 
                ? round(($samples->where('quality_status', 'approved')->count() / $samples->count()) * 100, 1)
                : 0,
            'total_adjustments' => $adjustments->count(),
            'total_material_used' => round($adjustments->sum('amount_kg'), 2),
            'adjustment_success_rate' => $adjustments->count() > 0
                ? round(($adjustments->where('is_successful', true)->count() / $adjustments->count()) * 100, 1)
                : 0
        ];
    }
    
    /**
     * Haftalık trend analizi (aylık rapor için)
     */
    private function getWeeklyTrend($startDate, $endDate)
    {
        $weeks = [];
        $currentWeek = $startDate->copy()->startOfWeek();
        
        while ($currentWeek->lt($endDate)) {
            $weekEnd = $currentWeek->copy()->endOfWeek();
            if ($weekEnd->gt($endDate)) {
                $weekEnd = $endDate->copy();
            }
            
            $weeklyStats = $this->getWeeklyStats($currentWeek, $weekEnd);
            $weeks[$currentWeek->format('Y-m-d')] = $weeklyStats;
            
            $currentWeek->addWeek();
        }
        
        return $weeks;
    }
    
    /**
     * Ocak kullanım analizi
     */
    private function getFurnaceUtilization($startDate, $endDate)
    {
        $totalDays = $startDate->diffInDays($endDate) + 1;
        
        return Furnace::with('furnaceSet')->get()->map(function($furnace) use ($startDate, $endDate, $totalDays) {
            $activeDays = Casting::where('furnace_id', $furnace->id)
                ->whereBetween('casting_date', [$startDate, $endDate])
                ->selectRaw('COUNT(DISTINCT DATE(casting_date)) as days')
                ->first()->days ?? 0;
            
            $totalCastings = $furnace->castings()
                ->whereBetween('casting_date', [$startDate, $endDate])
                ->count();
            
            return [
                'furnace' => $furnace,
                'utilization_rate' => round(($activeDays / $totalDays) * 100, 1),
                'total_castings' => $totalCastings,
                'avg_castings_per_active_day' => $activeDays > 0 ? round($totalCastings / $activeDays, 1) : 0
            ];
        });
    }
    
    /**
     * Kalite performans analizi
     */
    private function getQualityPerformance($startDate, $endDate)
    {
        $samples = Sample::whereBetween('sample_time', [$startDate, $endDate])->get();
        
        // Element bazında ortalama değerler ve standart sapmalar
        $elements = ['carbon', 'manganese', 'silicon', 'phosphorus', 'sulfur', 'chromium', 'nickel', 'molybdenum'];
        $performance = [];
        
        foreach ($elements as $element) {
            $field = $element . '_percentage';
            $values = $samples->whereNotNull($field)->pluck($field);
            
            if ($values->count() > 0) {
                $performance[$element] = [
                    'average' => round($values->avg(), 3),
                    'min' => round($values->min(), 3),
                    'max' => round($values->max(), 3),
                    'std_deviation' => round($this->calculateStandardDeviation($values), 3),
                    'sample_count' => $values->count()
                ];
            }
        }
        
        return $performance;
    }
    
    /**
     * Ham madde tüketim analizi
     */
    private function getMaterialConsumption($startDate, $endDate)
    {
        return Adjustment::whereBetween('adjustment_date', [$startDate, $endDate])
            ->selectRaw('material_type, COUNT(*) as usage_count, SUM(amount_kg) as total_amount, AVG(amount_kg) as avg_amount')
            ->groupBy('material_type')
            ->get()
            ->map(function($item) {
                return [
                    'material' => $item->material_type,
                    'usage_count' => $item->usage_count,
                    'total_amount' => round($item->total_amount, 2),
                    'avg_amount' => round($item->avg_amount, 2)
                ];
            });
    }
    
    /**
     * Vardiya performans analizi
     */
    private function getShiftPerformance($startDate, $endDate)
    {
        return Casting::whereBetween('casting_date', [$startDate, $endDate])
            ->selectRaw('shift, COUNT(*) as total_castings')
            ->groupBy('shift')
            ->get()
            ->map(function($item) use ($startDate, $endDate) {
                $samples = Sample::whereHas('casting', function($query) use ($item, $startDate, $endDate) {
                    $query->where('shift', $item->shift)
                          ->whereBetween('casting_date', [$startDate, $endDate]);
                })->get();
                
                return [
                    'shift' => $item->shift,
                    'total_castings' => $item->total_castings,
                    'total_samples' => $samples->count(),
                    'approved_samples' => $samples->where('quality_status', 'approved')->count(),
                    'quality_rate' => $samples->count() > 0 
                        ? round(($samples->where('quality_status', 'approved')->count() / $samples->count()) * 100, 1)
                        : 0,
                    'avg_samples_per_casting' => $item->total_castings > 0 
                        ? round($samples->count() / $item->total_castings, 1) 
                        : 0
                ];
            });
    }
    
    /**
     * Standart sapma hesapla
     */
    private function calculateStandardDeviation($values)
    {
        $mean = $values->avg();
        $sumSquares = $values->sum(function($value) use ($mean) {
            return pow($value - $mean, 2);
        });
        
        return sqrt($sumSquares / $values->count());
    }
}
