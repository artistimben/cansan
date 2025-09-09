<?php

namespace App\Http\Controllers;

use App\Models\Furnace;
use App\Models\FurnaceSet;
use App\Models\Casting;
use App\Models\Sample;
use App\Models\Adjustment;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Ana Kontrol Paneli Controller'ı
 * Genel sistem durumu, istatistikler ve özet bilgiler
 */
class DashboardController extends Controller
{
    /**
     * Ana kontrol paneli sayfasını göster
     */
    public function index()
    {
        // Aktif ocakları getir
        $activeFurnaces = Furnace::active()->with('furnaceSet')->get();
        
        // Günlük istatistikler
        $today = Carbon::today();
        $dailyStats = [
            'total_castings' => Casting::whereDate('casting_date', $today)->count(),
            'total_samples' => Sample::whereDate('sample_time', $today)->count(),
            'approved_samples' => Sample::whereDate('sample_time', $today)->approved()->count(),
            'pending_samples' => Sample::whereDate('sample_time', $today)->pending()->count(),
            'rejected_samples' => Sample::whereDate('sample_time', $today)->rejected()->count(),
            'needs_adjustment' => Sample::whereDate('sample_time', $today)->needsAdjustment()->count(),
            'total_adjustments' => 0, // Adjustment tablosu şimdilik yok
            'active_furnaces' => Furnace::active()->count()
        ];
        
        // Haftalık istatistikler
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();
        $weeklyStats = [
            'total_castings' => Casting::whereBetween('casting_date', [$weekStart, $weekEnd])->count(),
            'total_samples' => Sample::whereBetween('sample_time', [$weekStart, $weekEnd])->count(),
            'approved_samples' => Sample::whereBetween('sample_time', [$weekStart, $weekEnd])->approved()->count(),
            'quality_rate' => 0 // Hesaplanacak
        ];
        
        // Kalite oranını hesapla
        if ($weeklyStats['total_samples'] > 0) {
            $weeklyStats['quality_rate'] = round(($weeklyStats['approved_samples'] / $weeklyStats['total_samples']) * 100, 2);
        }
        
        // Set bazında günlük döküm sayıları
        $furnaceSets = FurnaceSet::with(['furnaces' => function($query) {
            $query->active();
        }])->get();
        
        $setStats = [];
        foreach ($furnaceSets as $set) {
            $setStats[] = [
                'set' => $set,
                'active_furnace' => $set->activeFurnace(),
                'daily_castings' => $set->getDailyCastingCount(),
                'weekly_castings' => $set->getWeeklyCastingCount(),
                'monthly_castings' => $set->getMonthlyCastingCount()
            ];
        }
        
        // Son aktiviteler
        $recentActivities = [
            'latest_samples' => Sample::with(['casting.furnace'])->latest('sample_time')->take(10)->get(),
            'latest_adjustments' => Adjustment::with(['casting.furnace', 'sample'])->latest('adjustment_date')->take(5)->get(),
            'active_castings' => Casting::active()->with(['furnace', 'samples'])->get()
        ];
        
        return view('dashboard.index', compact(
            'activeFurnaces',
            'dailyStats',
            'weeklyStats',
            'setStats',
            'recentActivities'
        ));
    }
    
    /**
     * Gerçek zamanlı durum güncellemesi için AJAX endpoint
     */
    public function getRealtimeStatus()
    {
        $activeFurnaces = Furnace::active()->with(['furnaceSet', 'castings' => function($query) {
            $query->active()->latest();
        }])->get();
        
        $status = [];
        foreach ($activeFurnaces as $furnace) {
            $activeCasting = $furnace->getActiveCasting();
            $status[] = [
                'furnace_id' => $furnace->id,
                'furnace_name' => $furnace->name,
                'set_name' => $furnace->furnaceSet->name,
                'active_casting' => $activeCasting ? [
                    'id' => $activeCasting->id,
                    'casting_number' => $activeCasting->casting_number,
                    'samples_count' => $activeCasting->samples()->count(),
                    'last_sample_time' => $activeCasting->samples()->latest('sample_time')->first()?->sample_time,
                    'quality_status' => $activeCasting->getQualityStatus()
                ] : null
            ];
        }
        
        return response()->json([
            'timestamp' => now(),
            'furnaces' => $status,
            'system_status' => 'operational'
        ]);
    }
    
    /**
     * Sistem sağlık durumu kontrolü
     */
    public function healthCheck()
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'active_furnaces' => $this->checkActiveFurnaces(),
            'recent_activity' => $this->checkRecentActivity(),
            'pending_samples' => $this->checkPendingSamples()
        ];
        
        $overallStatus = collect($checks)->every(function($check) {
            return $check['status'] === 'ok';
        }) ? 'healthy' : 'warning';
        
        return response()->json([
            'overall_status' => $overallStatus,
            'checks' => $checks,
            'timestamp' => now()
        ]);
    }
    
    /**
     * Veritabanı bağlantısını kontrol et
     */
    private function checkDatabase()
    {
        try {
            \DB::connection()->getPdo();
            return ['status' => 'ok', 'message' => 'Veritabanı bağlantısı aktif'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Veritabanı bağlantı hatası: ' . $e->getMessage()];
        }
    }
    
    /**
     * Aktif ocakları kontrol et
     */
    private function checkActiveFurnaces()
    {
        $activeCount = Furnace::active()->count();
        
        if ($activeCount === 0) {
            return ['status' => 'warning', 'message' => 'Hiç aktif ocak yok'];
        } elseif ($activeCount > 3) {
            return ['status' => 'warning', 'message' => 'Normalden fazla ocak aktif (' . $activeCount . ')'];
        }
        
        return ['status' => 'ok', 'message' => $activeCount . ' ocak aktif çalışıyor'];
    }
    
    /**
     * Son aktiviteleri kontrol et
     */
    private function checkRecentActivity()
    {
        $lastSample = Sample::latest('sample_time')->first();
        
        if (!$lastSample) {
            return ['status' => 'warning', 'message' => 'Hiç prova kaydı yok'];
        }
        
        $hoursSinceLastSample = Carbon::parse($lastSample->sample_time)->diffInHours(now());
        
        if ($hoursSinceLastSample > 4) {
            return ['status' => 'warning', 'message' => 'Son prova ' . $hoursSinceLastSample . ' saat önce alındı'];
        }
        
        return ['status' => 'ok', 'message' => 'Sistem aktif kullanılıyor'];
    }
    
    /**
     * Bekleyen provaları kontrol et
     */
    private function checkPendingSamples()
    {
        $pendingCount = Sample::pending()->count();
        
        if ($pendingCount > 10) {
            return ['status' => 'warning', 'message' => $pendingCount . ' prova analiz bekliyor'];
        }
        
        return ['status' => 'ok', 'message' => $pendingCount . ' prova analiz bekliyor'];
    }
}
