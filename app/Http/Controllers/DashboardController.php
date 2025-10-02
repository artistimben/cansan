<?php

namespace App\Http\Controllers;

use App\Models\Furnace;
use App\Models\Casting;
use App\Models\QualityControl;
use App\Models\DailyReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Ana Dashboard Controller
 * 
 * Tek sayfada tüm fabrika operasyonlarını yönetir:
 * - Aktif ocaklar ve döküm durumları
 * - Real-time döküm takibi
 * - Kalite kontrol değerleri
 * - Günlük raporlar
 */
class DashboardController extends Controller
{
    /**
     * Ana dashboard sayfası
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Aktif ocaklar (maksimum 3 adet)
        $activeFurnaces = Furnace::getActiveFurnaces();
        
        // Devam eden dökümler
        $activeCastings = Casting::getActiveCastings();
        
        // Bugünkü döküm listesi (genel sıralama)
        $todaysCastings = Casting::getTodaysCastings();
        
        // Günlük istatistikler
        $dailyStats = Casting::getDailyCastingStats();
        
        // Bakıma gitmesi gereken ocaklar
        $maintenanceNeeded = Furnace::getFurnacesNeedingMaintenance();
        
        // Son kalite kontrol sonuçları
        $recentQualityControls = QualityControl::with('casting.furnace')
            ->orderBy('test_time', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact(
            'activeFurnaces',
            'activeCastings', 
            'todaysCastings',
            'dailyStats',
            'maintenanceNeeded',
            'recentQualityControls'
        ));
    }

    /**
     * Real-time veri güncelleme (AJAX)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRealTimeData()
    {
        try {
            $data = [
                'active_furnaces' => $this->getActiveFurnacesData(),
                'active_castings' => $this->getActiveCastingsData(),
                'daily_stats' => Casting::getDailyCastingStats(),
                'timestamp' => now()->format('H:i:s')
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Veri güncellenirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aktif ocakların detaylı verilerini getirir
     */
    private function getActiveFurnacesData()
    {
        return Furnace::getActiveFurnaces()->map(function ($furnace) {
            $currentCasting = $furnace->getCurrentCasting();
            
            return [
                'id' => $furnace->id,
                'furnace_number' => $furnace->furnace_number,
                'furnace_set' => $furnace->furnace_set,
                'total_castings' => $furnace->total_castings,
                'max_castings' => $furnace->max_castings_before_maintenance,
                'is_charging' => $furnace->is_charging,
                'current_casting' => $currentCasting ? [
                    'id' => $currentCasting->id,
                    'casting_number' => $currentCasting->casting_number_per_furnace,
                    'global_number' => $currentCasting->global_casting_number,
                    'start_time' => $currentCasting->start_time->format('H:i'),
                    'estimated_end' => $currentCasting->estimated_end_time->format('H:i'),
                    'progress' => $currentCasting->progress_percentage,
                    'remaining_minutes' => $currentCasting->remaining_time,
                    'is_delayed' => $currentCasting->isDelayed()
                ] : null,
                'todays_castings' => $furnace->getTodaysCastingCount(),
                'maintenance_progress' => round(($furnace->total_castings / $furnace->max_castings_before_maintenance) * 100, 1)
            ];
        });
    }

    /**
     * Aktif dökümlerin detaylı verilerini getirir
     */
    private function getActiveCastingsData()
    {
        return Casting::getActiveCastings()->map(function ($casting) {
            return [
                'id' => $casting->id,
                'furnace_number' => $casting->furnace->furnace_number,
                'casting_number' => $casting->casting_number_per_furnace,
                'global_number' => $casting->global_casting_number,
                'start_time' => $casting->start_time->format('d.m.Y H:i'),
                'estimated_end' => $casting->estimated_end_time->format('H:i'),
                'progress' => $casting->progress_percentage,
                'remaining_time' => $casting->remaining_time,
                'is_delayed' => $casting->isDelayed(),
                'status_color' => $casting->isDelayed() ? 'danger' : 'info'
            ];
        });
    }

    /**
     * Döküm tamamlama (Manuel)
     * 
     * @param Request $request
     * @param int $castingId
     * @return \Illuminate\Http\JsonResponse
     */
    public function completeCasting(Request $request, $castingId)
    {
        try {
            $casting = Casting::findOrFail($castingId);
            
            if ($casting->status !== 'in_progress') {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu döküm zaten tamamlanmış veya iptal edilmiş.'
                ], 400);
            }

            $casting->complete();

            return response()->json([
                'success' => true,
                'message' => "{$casting->furnace->furnace_number}. ocak {$casting->casting_number_per_furnace}. döküm başarıyla tamamlandı.",
                'data' => [
                    'casting_id' => $casting->id,
                    'new_casting_started' => $casting->furnace->getCurrentCasting() ? true : false
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Döküm tamamlanırken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manuel döküm başlatma
     * 
     * @param Request $request
     * @param int $furnaceId
     * @return \Illuminate\Http\JsonResponse
     */
    public function startCasting(Request $request, $furnaceId)
    {
        try {
            $furnace = Furnace::findOrFail($furnaceId);
            
            if ($furnace->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu ocak aktif durumda değil.'
                ], 400);
            }

            $casting = $furnace->startNewCasting();
            
            if (!$casting) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu ocakta zaten devam eden bir döküm var.'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => "{$furnace->furnace_number}. ocakta yeni döküm başlatıldı.",
                'data' => [
                    'casting_id' => $casting->id,
                    'casting_number' => $casting->casting_number_per_furnace,
                    'global_number' => $casting->global_casting_number
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Döküm başlatılırken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ocak bakıma gönderme
     * 
     * @param Request $request
     * @param int $furnaceId
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendToMaintenance(Request $request, $furnaceId)
    {
        try {
            $furnace = Furnace::findOrFail($furnaceId);
            
            // Mevcut döküm var mı kontrol et
            $currentCasting = $furnace->getCurrentCasting();
            if ($currentCasting) {
                return response()->json([
                    'success' => false,
                    'message' => 'Devam eden döküm tamamlanmadan bakıma gönderilemez.'
                ], 400);
            }

            $furnace->goToMaintenance();

            return response()->json([
                'success' => true,
                'message' => "{$furnace->furnace_number}. ocak bakıma gönderildi. Aynı setteki yedek ocak aktif hale getirildi.",
                'data' => [
                    'furnace_id' => $furnace->id,
                    'maintenance_date' => $furnace->last_maintenance_date->format('d.m.Y H:i')
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocak bakıma gönderilirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Günlük rapor oluşturma (Manuel)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateDailyReport(Request $request)
    {
        try {
            $date = $request->input('date', now()->toDateString());
            $report = DailyReport::generateTodaysReport($date);

            return response()->json([
                'success' => true,
                'message' => Carbon::parse($date)->format('d.m.Y') . ' tarihli günlük rapor oluşturuldu.',
                'data' => $report->getSummary()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Günlük rapor oluşturulurken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Döküm geçmişi (Modal için)
     * 
     * @param int $furnaceId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFurnaceCastingHistory($furnaceId)
    {
        try {
            $furnace = Furnace::findOrFail($furnaceId);
            $castings = Casting::getFurnaceCastingHistory($furnaceId, 20);

            return response()->json([
                'success' => true,
                'data' => [
                    'furnace' => [
                        'number' => $furnace->furnace_number,
                        'set' => $furnace->furnace_set,
                        'total_castings' => $furnace->total_castings
                    ],
                    'castings' => $castings->map(function ($casting) {
                        return [
                            'casting_number' => $casting->casting_number_per_furnace,
                            'global_number' => $casting->global_casting_number,
                            'start_time' => $casting->start_time->format('d.m.Y H:i'),
                            'end_time' => $casting->end_time ? $casting->end_time->format('H:i') : '-',
                            'duration' => $casting->actual_duration ?? $casting->duration_minutes,
                            'status' => $casting->status,
                            'has_quality_control' => $casting->qualityControl ? true : false,
                            'quality_result' => $casting->qualityControl ? $casting->qualityControl->test_result : null
                        ];
                    })
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Döküm geçmişi getirilemedi: ' . $e->getMessage()
            ], 500);
        }
    }
}
