<?php

namespace App\Http\Controllers;

use App\Models\Furnace;
use App\Models\FurnaceStatusLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Ocak Yönetimi Controller'ı
 * Refraktör değişimi, bakım, duruş işlemleri
 */
class FurnaceManagementController extends Controller
{
    /**
     * Ocak yönetim sayfası
     */
    public function index()
    {
        $furnaces = Furnace::with(['furnaceSet', 'statusLogs' => function($query) {
            $query->latest('status_changed_at')->limit(5);
        }])->get();

        return view('furnace-management.index', compact('furnaces'));
    }

    /**
     * Refraktör değişimi
     */
    public function changeRefractory(Request $request, Furnace $furnace)
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:1000',
            'operator_name' => 'nullable|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz veri',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $furnace->changeRefractory(
                $request->notes,
                $request->operator_name
            );

            return response()->json([
                'success' => true,
                'message' => "{$furnace->name} refraktörü değiştirildi ve döküm sayacı sıfırlandı.",
                'furnace' => $furnace->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Refraktör değişimi sırasında hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bakım başlat
     */
    public function startMaintenance(Request $request, Furnace $furnace)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'operator_name' => 'nullable|string|max:100',
            'reset_count' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz veri',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $furnace->startMaintenance(
                $request->reason,
                $request->notes,
                $request->operator_name,
                $request->boolean('reset_count', false)
            );

            $message = "{$furnace->name} bakım durumuna alındı.";
            if ($request->boolean('reset_count')) {
                $message .= " Döküm sayacı sıfırlandı.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'furnace' => $furnace->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bakım başlatma sırasında hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duruş başlat
     */
    public function shutdown(Request $request, Furnace $furnace)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'operator_name' => 'nullable|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz veri',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $furnace->shutdown(
                $request->reason,
                $request->notes,
                $request->operator_name
            );

            return response()->json([
                'success' => true,
                'message' => "{$furnace->name} duruş durumuna alındı.",
                'furnace' => $furnace->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Duruş başlatma sırasında hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Döküm sayacını manuel sıfırla
     */
    public function resetCastingCount(Request $request, Furnace $furnace)
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:1000',
            'operator_name' => 'nullable|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz veri',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $furnace->resetCastingCount('manual', $request->notes);

            return response()->json([
                'success' => true,
                'message' => "{$furnace->name} döküm sayacı sıfırlandı.",
                'furnace' => $furnace->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Döküm sayacı sıfırlama sırasında hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ocak durum geçmişi
     */
    public function statusHistory(Furnace $furnace)
    {
        $statusLogs = $furnace->statusLogs()
            ->latest('status_changed_at')
            ->paginate(20);

        return view('furnace-management.status-history', compact('furnace', 'statusLogs'));
    }

    /**
     * Bakım bitir
     */
    public function endMaintenance(Request $request, Furnace $furnace)
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:1000',
            'operator_name' => 'nullable|string|max:100',
            'new_status' => 'required|in:active,idle,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz veri',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $previousStatus = $furnace->status;
            
            $furnace->update([
                'status' => $request->new_status,
                'status_updated_at' => now(),
                'last_maintenance_date' => now()->toDateString()
            ]);

            // Durum logu oluştur
            FurnaceStatusLog::logStatusChange(
                $furnace->id,
                $request->new_status,
                $previousStatus,
                'Bakım tamamlandı',
                $request->notes,
                $request->operator_name
            );

            return response()->json([
                'success' => true,
                'message' => "{$furnace->name} bakımı tamamlandı ve {$request->new_status} durumuna alındı.",
                'furnace' => $furnace->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bakım bitirme sırasında hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duruş bitir
     */
    public function endShutdown(Request $request, Furnace $furnace)
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:1000',
            'operator_name' => 'nullable|string|max:100',
            'new_status' => 'required|in:active,idle,maintenance'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz veri',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $previousStatus = $furnace->status;
            
            $furnace->update([
                'status' => $request->new_status,
                'status_updated_at' => now()
            ]);

            // Durum logu oluştur
            FurnaceStatusLog::logStatusChange(
                $furnace->id,
                $request->new_status,
                $previousStatus,
                'Duruş tamamlandı',
                $request->notes,
                $request->operator_name
            );

            return response()->json([
                'success' => true,
                'message' => "{$furnace->name} duruşu tamamlandı ve {$request->new_status} durumuna alındı.",
                'furnace' => $furnace->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Duruş bitirme sırasında hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ocak istatistikleri
     */
    public function statistics(Furnace $furnace)
    {
        $statistics = $furnace->getCastingStatistics();
        $maintenanceProgress = $furnace->getMaintenanceProgress();
        
        // Son 30 günlük döküm sayısı
        $recentCastings = $furnace->castings()
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return response()->json([
            'success' => true,
            'statistics' => $statistics,
            'maintenance_progress' => $maintenanceProgress,
            'recent_castings' => $recentCastings,
            'needs_maintenance' => $furnace->needsMaintenance()
        ]);
    }
}