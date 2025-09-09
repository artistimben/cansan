<?php

namespace App\Http\Controllers;

use App\Models\Furnace;
use App\Models\FurnaceSet;
use App\Models\Casting;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Ocak Yönetimi Controller'ı
 * Ocak durumu, döküm başlatma/durdurma işlemleri
 */
class FurnaceController extends Controller
{
    /**
     * Tüm ocakları listele
     */
    public function index()
    {
        $furnaceSets = FurnaceSet::with(['furnaces' => function($query) {
            $query->with(['castings' => function($subQuery) {
                $subQuery->latest()->take(5);
            }]);
        }])->get();
        
        $furnaces = Furnace::with(['furnaceSet', 'castings' => function($query) {
            $query->latest()->take(5);
        }])->get();
        
        // Durum istatistikleri
        $statusCounts = [
            'active' => $furnaces->where('status', 'active')->count(),
            'idle' => $furnaces->where('status', 'idle')->count(),
            'maintenance' => $furnaces->where('status', 'maintenance')->count(),
            'inactive' => $furnaces->where('status', 'inactive')->count(),
        ];
        
        return view('furnaces.index', compact('furnaceSets', 'furnaces', 'statusCounts'));
    }
    
    /**
     * Belirli bir ocağın detaylarını göster
     */
    public function show(Furnace $furnace)
    {
        $furnace->load(['furnaceSet', 'castings.samples']);
        
        // Son 30 günlük performans
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $recentCastings = $furnace->castings()
            ->where('casting_date', '>=', $thirtyDaysAgo)
            ->with('samples')
            ->orderBy('casting_date', 'desc')
            ->get();
        
        // Toplam döküm sayısı (tüm zamanlar)
        $totalCastings = $furnace->castings()->count();
        $nextCastingNumber = $totalCastings + 1;
        
        // Bakım takibi için veriler
        $maxCastingsBeforeMaintenance = $furnace->maintenance_interval_days ?? 100; // Varsayılan 100 döküm
        $castingsSinceLastMaintenance = $furnace->castings()
            ->where('casting_date', '>', $furnace->last_maintenance_date ?? '1900-01-01')
            ->count();
        $maintenanceProgress = ($castingsSinceLastMaintenance / $maxCastingsBeforeMaintenance) * 100;
        $needsMaintenance = $castingsSinceLastMaintenance >= $maxCastingsBeforeMaintenance;
        
        // Performans istatistikleri
        $stats = [
            'total_castings' => $totalCastings,
            'next_casting_number' => $nextCastingNumber,
            'castings_since_maintenance' => $castingsSinceLastMaintenance,
            'max_castings_before_maintenance' => $maxCastingsBeforeMaintenance,
            'maintenance_progress' => min($maintenanceProgress, 100),
            'needs_maintenance' => $needsMaintenance,
            'total_castings_30d' => $recentCastings->count(),
            'total_samples_30d' => $recentCastings->sum(function($casting) {
                return $casting->samples->count();
            }),
            'approved_samples_30d' => $recentCastings->sum(function($casting) {
                return $casting->samples->where('quality_status', 'approved')->count();
            }),
            'rejected_samples_30d' => $recentCastings->sum(function($casting) {
                return $casting->samples->where('quality_status', 'rejected')->count();
            }),
            'avg_samples_per_casting' => $recentCastings->count() > 0 
                ? round($recentCastings->sum(function($casting) { return $casting->samples->count(); }) / $recentCastings->count(), 1)
                : 0
        ];
        
        // Kalite oranı
        $totalSamples = $stats['approved_samples_30d'] + $stats['rejected_samples_30d'];
        $stats['quality_rate'] = $totalSamples > 0 
            ? round(($stats['approved_samples_30d'] / $totalSamples) * 100, 1)
            : 0;
        
        // Aktif döküm
        $activeCasting = $furnace->getActiveCasting();
        
        return view('furnaces.show', compact('furnace', 'recentCastings', 'stats', 'activeCasting'));
    }
    
    /**
     * Ocak durumunu değiştir (set kuralları ile)
     */
    public function toggleStatus(Request $request, Furnace $furnace)
    {
        $request->validate([
            'status' => 'required|in:active,idle,maintenance,inactive',
            'current_temperature' => 'nullable|numeric|min:0|max:2000',
            'status_notes' => 'nullable|string|max:500'
        ]);
        
        // Set kuralına göre durum değiştir
        $result = $furnace->changeStatusWithSetRule($request->status);
        
        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }
        
        // Sıcaklık güncelleme
        if ($request->filled('current_temperature')) {
            $furnace->update([
                'current_temperature' => $request->current_temperature
            ]);
            
            // Sıcaklık geçmişine kaydet
            $furnace->addTemperatureLog(
                $request->current_temperature,
                $request->status === 'active' ? 'working' : 'shutdown',
                $request->status_notes ?? "Durum değişikliği: {$request->status}",
                'Sistem'
            );
        }
            
        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'affected_furnaces' => $result['affected_furnaces'],
            'furnace' => $furnace->fresh()
        ]);
    }
    
    /**
     * Yeni döküm başlat
     */
    public function startCasting(Request $request, Furnace $furnace)
    {
        $request->validate([
            'shift' => 'required|string|max:10',
            'temperature' => 'nullable|numeric|min:0|max:2000',
            'notes' => 'nullable|string|max:1000'
        ]);
        
        // Ocak aktif mi kontrol et
        if ($furnace->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Sadece aktif ocaklarda döküm başlatılabilir'
            ], 400);
        }

        // Aktif döküm kontrolü
        if (Casting::hasActiveCastingInFurnace($furnace->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Bu ocakta zaten aktif bir döküm bulunuyor. Önce mevcut dökümü tamamlayın.'
            ], 400);
        }
        
        // Döküm numarası formatı: "3.OCAK-27.DÖKÜM"
        $furnaceName = strtoupper(str_replace(' ', '', $furnace->name));
        $castingCount = $furnace->castings()->count();
        $nextCastingNumber = $castingCount + 1;
        $castingNumber = $furnaceName . '-' . $nextCastingNumber . '.DÖKÜM';
        
        // Yeni döküm oluştur
        $casting = $furnace->castings()->create([
            'casting_number' => $castingNumber,
            'casting_date' => now(),
            'shift' => $request->shift,
            'status' => 'active',
            'started_at' => now(),
            'notes' => $request->notes,
            'target_temperature' => $request->temperature
        ]);
        
        // Sıcaklık kaydı
        if ($request->filled('temperature')) {
            $furnace->addTemperatureLog(
                $request->temperature, 
                'working', 
                "Döküm {$castingNumber} başlatıldı", 
                'Sistem'
            );
        }
        
        return response()->json([
            'success' => true,
            'message' => "Yeni döküm başlatıldı: {$castingNumber}",
            'casting' => $casting,
            'casting_number' => $castingNumber,
            'casting_count' => $castingCount,
            'next_number' => $nextCastingNumber,
            'furnace_name' => $furnace->name,
            'furnace_set' => $furnace->furnaceSet->name
        ]);
    }
    
    /**
     * Dökümü tamamla
     */
    public function completeCasting(Request $request, Furnace $furnace, Casting $casting)
    {
        // Döküm furnace'a ait mi kontrol et
        if ($casting->furnace_id !== $furnace->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bu döküm bu ocağa ait değil'
            ], 400);
        }

        // Döküm aktif mi kontrol et
        if ($casting->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Sadece aktif dökümler tamamlanabilir'
            ], 400);
        }

        // Minimum prova sayısı kontrolü
        $sampleCount = $casting->samples()->count();
        if ($sampleCount < 3) {
            return response()->json([
                'success' => false,
                'message' => "Döküm tamamlanamaz. En az 3 prova gerekli (Şu an: {$sampleCount} prova)"
            ], 400);
        }

        // Döküm tamamlama
        $request->validate([
            'final_temperature' => 'nullable|numeric|min:0|max:2000',
            'completion_notes' => 'nullable|string|max:1000'
        ]);

        // Dökümü tamamla
        $casting->update([
            'status' => 'completed',
            'completed_at' => now(),
            'final_temperature' => $request->final_temperature,
            'completion_notes' => $request->completion_notes
        ]);

        // Ocağın sıcaklık geçmişine kaydet
        if ($request->filled('final_temperature')) {
            $furnace->addTemperatureLog(
                $request->final_temperature, 
                'shutdown', 
                "Döküm {$casting->casting_number} tamamlandı", 
                'Sistem'
            );
        }

        return response()->json([
            'success' => true,
            'message' => "Döküm {$casting->casting_number} başarıyla tamamlandı",
            'casting' => $casting->fresh(),
            'sample_count' => $sampleCount
        ]);
    }
    
    /**
     * Ocak performans raporu
     */
    public function performanceReport(Furnace $furnace, Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        
        $castings = $furnace->castings()
            ->whereBetween('casting_date', [$startDate, $endDate])
            ->with('samples.adjustments')
            ->get();
        
        // Günlük döküm sayıları
        $dailyCounts = $castings->groupBy(function($casting) {
            return $casting->casting_date->format('Y-m-d');
        })->map(function($group) {
            return $group->count();
        });
        
        // Vardiya bazında dağılım
        $shiftDistribution = $castings->groupBy('shift')->map(function($group) {
            return $group->count();
        });
        
        // Kalite istatistikleri
        $allSamples = $castings->flatMap(function($casting) {
            return $casting->samples;
        });
        
        $qualityStats = [
            'total_samples' => $allSamples->count(),
            'approved' => $allSamples->where('quality_status', 'approved')->count(),
            'rejected' => $allSamples->where('quality_status', 'rejected')->count(),
            'pending' => $allSamples->where('quality_status', 'pending')->count(),
            'needs_adjustment' => $allSamples->where('quality_status', 'needs_adjustment')->count()
        ];
        
        // Ham madde ekleme istatistikleri
        $allAdjustments = $allSamples->flatMap(function($sample) {
            return $sample->adjustments;
        });
        
        $adjustmentStats = [
            'total_adjustments' => $allAdjustments->count(),
            'successful_adjustments' => $allAdjustments->where('is_successful', true)->count(),
            'materials_used' => $allAdjustments->groupBy('material_type')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'total_amount' => $group->sum('amount_kg')
                ];
            })
        ];
        
        return response()->json([
            'furnace' => $furnace,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'casting_stats' => [
                'total_castings' => $castings->count(),
                'daily_counts' => $dailyCounts,
                'shift_distribution' => $shiftDistribution
            ],
            'quality_stats' => $qualityStats,
            'adjustment_stats' => $adjustmentStats
        ]);
    }
    
    /**
     * Ocak durumu güncelle (operasyonel/bakım)
     */
    public function updateOperationalStatus(Request $request, Furnace $furnace)
    {
        $request->validate([
            'is_operational' => 'required|boolean',
            'notes' => 'nullable|string|max:1000'
        ]);
        
        $furnace->update([
            'is_operational' => $request->is_operational,
            'description' => $request->notes
        ]);
        
        $message = $request->is_operational 
            ? $furnace->name . ' operasyonel duruma getirildi'
            : $furnace->name . ' bakım durumuna alındı';
            
        return response()->json([
            'success' => true,
            'message' => $message,
            'furnace' => $furnace->fresh()
        ]);
    }
    
    /**
     * Yeni ocak oluşturma formu
     */
    public function create()
    {
        $furnaceSets = FurnaceSet::all();
        
        return view('furnaces.create', compact('furnaceSets'));
    }
    
    /**
     * Yeni ocak kaydet
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'furnace_set_id' => 'required|exists:furnace_sets,id',
            'name' => 'required|string|max:100|unique:furnaces,name',
            'description' => 'nullable|string|max:500',
            'capacity' => 'nullable|numeric|min:0|max:1000',
            'max_temperature' => 'nullable|numeric|min:0|max:3000',
            'fuel_type' => 'nullable|in:natural_gas,electricity,coal,oil,mixed',
            'installation_date' => 'nullable|date',
            'status' => 'required|in:active,idle,maintenance,inactive',
            'current_temperature' => 'nullable|numeric|min:0|max:3000',
            'last_maintenance_date' => 'nullable|date',
            'maintenance_interval_days' => 'nullable|integer|min:1|max:365'
        ]);
        
        $furnace = Furnace::create($validated + [
            'status_updated_at' => now()
        ]);
        
        return redirect()
            ->route('furnaces.show', $furnace)
            ->with('success', 'Ocak başarıyla oluşturuldu!');
    }
    
    /**
     * Ocak düzenleme formu
     */
    public function edit(Furnace $furnace)
    {
        $furnaceSets = FurnaceSet::all();
        
        return view('furnaces.edit', compact('furnace', 'furnaceSets'));
    }
    
    /**
     * Ocak güncelle
     */
    public function update(Request $request, Furnace $furnace)
    {
        $validated = $request->validate([
            'furnace_set_id' => 'required|exists:furnace_sets,id',
            'name' => 'required|string|max:100|unique:furnaces,name,' . $furnace->id,
            'description' => 'nullable|string|max:500',
            'capacity' => 'nullable|numeric|min:0|max:1000',
            'max_temperature' => 'nullable|numeric|min:0|max:3000',
            'fuel_type' => 'nullable|in:natural_gas,electricity,coal,oil,mixed',
            'installation_date' => 'nullable|date',
            'status' => 'required|in:active,idle,maintenance,inactive',
            'current_temperature' => 'nullable|numeric|min:0|max:3000',
            'last_maintenance_date' => 'nullable|date',
            'maintenance_interval_days' => 'nullable|integer|min:1|max:365'
        ]);
        
        // Durum değişikliği varsa timestamp güncelle
        if ($furnace->status !== $validated['status']) {
            $validated['status_updated_at'] = now();
        }
        
        $furnace->update($validated);
        
        return redirect()
            ->route('furnaces.show', $furnace)
            ->with('success', 'Ocak bilgileri güncellendi!');
    }
    
    /**
     * Ocak sil
     */
    public function destroy(Furnace $furnace)
    {
        // Aktif dökümleri kontrol et
        if ($furnace->castings()->where('status', 'active')->exists()) {
            return back()->with('error', 'Aktif dökümü olan ocak silinemez!');
        }
        
        $furnace->delete();
        
        return redirect()
            ->route('furnaces.index')
            ->with('success', 'Ocak silindi!');
    }
    
    /**
     * Ocak durumu güncelle
     */
    public function updateStatus(Request $request, Furnace $furnace)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,idle,maintenance,inactive',
            'status_notes' => 'nullable|string|max:1000',
            'current_temperature' => 'nullable|numeric|min:0|max:3000'
        ]);
        
        // Set kuralına göre durum değiştir
        $result = $furnace->changeStatusWithSetRule($validated['status']);
        
        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }
        
        // Sıcaklık güncelleme
        if (isset($validated['current_temperature'])) {
            $furnace->update([
                'current_temperature' => $validated['current_temperature']
            ]);
            
            // Sıcaklık geçmişine kaydet
            $furnace->addTemperatureLog(
                $validated['current_temperature'],
                $validated['status'] === 'active' ? 'working' : 'shutdown',
                $validated['status_notes'] ?? "Durum değişikliği: {$validated['status']}",
                'Sistem'
            );
        }
        
        $statusNames = [
            'active' => 'Aktif',
            'idle' => 'Beklemede',
            'maintenance' => 'Bakımda',
            'inactive' => 'Kapalı'
        ];
        
        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'affected_furnaces' => $result['affected_furnaces']
        ]);
    }
    
    /**
     * Ocak listesi export
     */
    public function export()
    {
        $furnaces = Furnace::with('furnaceSet')->get();
        
        // Excel export için gerekli kod buraya eklenecek
        // Şimdilik JSON olarak döndür
        return response()->json($furnaces);
    }
    
    /**
     * Ocak için bir sonraki döküm numarasını al
     */
    public function getNextCastingNumber(Furnace $furnace)
    {
        // Bu ocaktan yapılan toplam döküm sayısı
        $castingCount = $furnace->castings()->count();
        $nextCastingNumber = $castingCount + 1;
        
        // Döküm numarası formatı: "3.OCAK-27.DÖKÜM"
        $furnaceName = strtoupper(str_replace(' ', '', $furnace->name));
        $castingNumber = $furnaceName . '-' . $nextCastingNumber . '.DÖKÜM';
        
        return response()->json([
            'success' => true,
            'casting_number' => $castingNumber,
            'casting_count' => $castingCount,
            'next_number' => $nextCastingNumber,
            'furnace_name' => $furnace->name,
            'furnace_set' => $furnace->furnaceSet->name
        ]);
    }

    /**
     * Sıcaklık kaydı ekle
     */
    public function addTemperatureLog(Request $request, Furnace $furnace)
    {
        $request->validate([
            'temperature' => 'required|numeric|min:0|max:2000',
            'log_type' => 'required|in:working,shutdown,maintenance,manual',
            'notes' => 'nullable|string|max:500',
            'recorded_by' => 'nullable|string|max:100'
        ]);

        $temperatureLog = $furnace->addTemperatureLog(
            $request->temperature,
            $request->log_type,
            $request->notes,
            $request->recorded_by
        );

        // Ocağın current_temperature'ını güncelle
        $furnace->update([
            'current_temperature' => $request->temperature
        ]);

        return response()->json([
            'success' => true,
            'message' => "Sıcaklık kaydı eklendi: {$request->temperature}°C",
            'temperature_log' => $temperatureLog,
            'furnace' => $furnace->fresh()
        ]);
    }

    /**
     * Sıcaklık geçmişi
     */
    public function temperatureHistory(Furnace $furnace)
    {
        $temperatureLogs = $furnace->temperatureLogs()
            ->latest('recorded_at')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'temperature_logs' => $temperatureLogs,
            'last_temperature' => $furnace->getLastRecordedTemperature()
        ]);
    }
    
    /**
     * Ocakta aktif döküm var mı kontrol et
     */
    public function checkActiveCasting(Furnace $furnace)
    {
        $hasActiveCasting = Casting::hasActiveCastingInFurnace($furnace->id);
        
        return response()->json([
            'has_active_casting' => $hasActiveCasting,
            'furnace_name' => $furnace->name,
            'furnace_set' => $furnace->furnaceSet->name
        ]);
    }
}
