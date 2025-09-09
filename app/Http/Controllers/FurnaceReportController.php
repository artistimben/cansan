<?php

namespace App\Http\Controllers;

use App\Models\Furnace;
use App\Models\FurnaceStatusLog;
use App\Models\Casting;
use App\Models\Sample;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Ocak Raporları Controller'ı
 * Bakım, duruş, prova notları ve istatistikler
 */
class FurnaceReportController extends Controller
{
    /**
     * Ocak raporları ana sayfası
     */
    public function index()
    {
        $furnaces = Furnace::with('furnaceSet')->get();
        
        return view('furnace-reports.index', compact('furnaces'));
    }

    /**
     * Ocak detay raporu
     */
    public function furnaceDetail(Furnace $furnace, Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        
        // Durum geçmişi
        $statusLogs = $furnace->statusLogs()
            ->whereBetween('status_changed_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->orderBy('status_changed_at', 'desc')
            ->get();

        // Döküm istatistikleri
        $castingStats = $this->getCastingStatistics($furnace, $dateFrom, $dateTo);
        
        // Bakım istatistikleri
        $maintenanceStats = $this->getMaintenanceStatistics($furnace, $dateFrom, $dateTo);
        
        // Prova istatistikleri
        $sampleStats = $this->getSampleStatistics($furnace, $dateFrom, $dateTo);

        return view('furnace-reports.furnace-detail', compact(
            'furnace', 
            'statusLogs', 
            'castingStats', 
            'maintenanceStats', 
            'sampleStats',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Bakım raporu
     */
    public function maintenanceReport(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        
        $maintenanceLogs = FurnaceStatusLog::with('furnace.furnaceSet')
            ->whereIn('status', ['maintenance', 'refractory_change'])
            ->whereBetween('status_changed_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->orderBy('status_changed_at', 'desc')
            ->get();

        $furnaces = Furnace::with('furnaceSet')->get();

        return view('furnace-reports.maintenance', compact('maintenanceLogs', 'furnaces', 'dateFrom', 'dateTo'));
    }

    /**
     * Duruş raporu
     */
    public function shutdownReport(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        
        $shutdownLogs = FurnaceStatusLog::with('furnace.furnaceSet')
            ->where('status', 'shutdown')
            ->whereBetween('status_changed_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->orderBy('status_changed_at', 'desc')
            ->get();

        $furnaces = Furnace::with('furnaceSet')->get();

        return view('furnace-reports.shutdown', compact('shutdownLogs', 'furnaces', 'dateFrom', 'dateTo'));
    }

    /**
     * Prova raporu
     */
    public function sampleReport(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        
        $samples = Sample::with(['casting.furnace.furnaceSet'])
            ->whereBetween('sample_time', [$dateFrom, $dateTo . ' 23:59:59'])
            ->orderBy('sample_time', 'desc')
            ->get();

        $furnaces = Furnace::with('furnaceSet')->get();

        return view('furnace-reports.samples', compact('samples', 'furnaces', 'dateFrom', 'dateTo'));
    }

    /**
     * Döküm istatistikleri
     */
    private function getCastingStatistics(Furnace $furnace, string $dateFrom, string $dateTo)
    {
        $castings = $furnace->castings()
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->get();

        return [
            'total' => $castings->count(),
            'completed' => $castings->where('status', 'completed')->count(),
            'active' => $castings->where('status', 'active')->count(),
            'cancelled' => $castings->where('status', 'cancelled')->count(),
            'average_duration' => $castings->where('status', 'completed')->avg('duration_minutes') ?? 0
        ];
    }

    /**
     * Bakım istatistikleri
     */
    private function getMaintenanceStatistics(Furnace $furnace, string $dateFrom, string $dateTo)
    {
        $maintenanceLogs = $furnace->statusLogs()
            ->whereIn('status', ['maintenance', 'refractory_change'])
            ->whereBetween('status_changed_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->get();

        return [
            'total_maintenance' => $maintenanceLogs->where('status', 'maintenance')->count(),
            'refractory_changes' => $maintenanceLogs->where('status', 'refractory_change')->count(),
            'total_downtime_hours' => $this->calculateDowntimeHours($maintenanceLogs)
        ];
    }

    /**
     * Prova istatistikleri
     */
    private function getSampleStatistics(Furnace $furnace, string $dateFrom, string $dateTo)
    {
        $samples = Sample::whereHas('casting', function($query) use ($furnace) {
            $query->where('furnace_id', $furnace->id);
        })
        ->whereBetween('sample_time', [$dateFrom, $dateTo . ' 23:59:59'])
        ->get();

        return [
            'total' => $samples->count(),
            'approved' => $samples->where('quality_status', 'approved')->count(),
            'rejected' => $samples->where('quality_status', 'rejected')->count(),
            'pending' => $samples->where('quality_status', 'pending')->count(),
            'needs_adjustment' => $samples->where('quality_status', 'needs_adjustment')->count(),
            'approval_rate' => $samples->count() > 0 ? round(($samples->where('quality_status', 'approved')->count() / $samples->count()) * 100, 2) : 0
        ];
    }

    /**
     * Duruş süresi hesaplama
     */
    private function calculateDowntimeHours($logs)
    {
        $totalHours = 0;
        $maintenanceStart = null;

        foreach ($logs->sortBy('status_changed_at') as $log) {
            if (in_array($log->status, ['maintenance', 'refractory_change']) && !$maintenanceStart) {
                $maintenanceStart = $log->status_changed_at;
            } elseif (!in_array($log->status, ['maintenance', 'refractory_change']) && $maintenanceStart) {
                $totalHours += $maintenanceStart->diffInHours($log->status_changed_at);
                $maintenanceStart = null;
            }
        }

        return $totalHours;
    }
}