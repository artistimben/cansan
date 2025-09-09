<?php

namespace App\Http\Controllers;

use App\Models\Sample;
use App\Models\Casting;
use App\Models\Furnace;
use App\Models\QualityStandard;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Prova Yönetimi Controller'ı
 * Prova kayıt, analiz ve kalite kontrol işlemleri
 */
class SampleController extends Controller
{
    /**
     * Prova listesi
     */
    public function index(Request $request)
    {
        $query = Sample::with(['casting.furnace.furnaceSet']);
        
        // Filtreleme
        if ($request->has('furnace_id') && $request->furnace_id) {
            $query->whereHas('casting', function($q) use ($request) {
                $q->where('furnace_id', $request->furnace_id);
            });
        }
        
        if ($request->has('quality_status') && $request->quality_status) {
            $query->where('quality_status', $request->quality_status);
        }
        
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('sample_time', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('sample_time', '<=', $request->date_to);
        }
        
        $samples = $query->orderBy('sample_time', 'desc')->paginate(20);
        
        // Filtreleme için gerekli veriler
        $furnaces = Furnace::with('furnaceSet')->get();
        $qualityStatuses = [
            'pending' => 'Beklemede',
            'approved' => 'Onaylandı',
            'rejected' => 'Reddedildi',
            'needs_adjustment' => 'Düzeltme Gerekli'
        ];
        
        return view('samples.index', compact('samples', 'furnaces', 'qualityStatuses'));
    }
    
    /**
     * Yeni prova kayıt formu
     */
    public function create(Request $request)
    {
        $casting_id = $request->get('casting_id');
        $selectedCasting = null;
        
        if ($casting_id) {
            $selectedCasting = Casting::with(['furnace.furnaceSet', 'samples'])->findOrFail($casting_id);
        }
        
        // Aktif dökümleri getir
        $activeCastings = Casting::where('status', 'active')
            ->with(['furnace.furnaceSet', 'samples'])
            ->orderBy('casting_date', 'desc')
            ->get();
        
        // Kalite standartları
        $qualityStandards = QualityStandard::all();
        
        return view('samples.create', compact('selectedCasting', 'activeCastings', 'qualityStandards'));
    }
    
    /**
     * Yeni prova kaydet
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'casting_id' => 'required|exists:castings,id',
            'sample_number' => 'nullable|integer|min:1',
            'sample_date' => 'required|date',
            'sample_time' => 'required',
            'sample_type' => 'required|string|in:regular,ladle,final,control',
            'carbon_content' => 'required|numeric|min:0|max:10',
            'manganese_content' => 'required|numeric|min:0|max:10',
            'silicon_content' => 'nullable|numeric|min:0|max:10',
            'temperature' => 'required|numeric|min:0|max:3000',
            'quality_standard_id' => 'nullable|exists:quality_standards,id',
            'sampled_by' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'action' => 'nullable|string|in:save,save_and_add'
        ]);
        
        $casting = Casting::findOrFail($validated['casting_id']);
        
        // Prova numarası otomatik oluştur (eğer belirtilmemişse)
        if (empty($validated['sample_number'])) {
            $validated['sample_number'] = $casting->samples()->max('sample_number') + 1;
        }
        
        // Tarih ve saati birleştir
        $sampleDateTime = Carbon::parse($validated['sample_date'] . ' ' . $validated['sample_time']);
        
        $sample = Sample::create([
            'casting_id' => $validated['casting_id'],
            'sample_number' => $validated['sample_number'],
            'sample_time' => $sampleDateTime,
            'sample_type' => $validated['sample_type'],
            'carbon_content' => $validated['carbon_content'],
            'manganese_content' => $validated['manganese_content'],
            'silicon_content' => $validated['silicon_content'],
            'temperature' => $validated['temperature'],
            'quality_standard_id' => $validated['quality_standard_id'],
            'sampled_by' => $validated['sampled_by'],
            'notes' => $validated['notes'],
            'quality_status' => 'pending'
        ]);
        
        // Kalite kontrolü yap
        if ($sample->quality_standard_id) {
            $sample->checkQualityCompliance();
        }
        
        $message = "Prova #{$sample->sample_number} başarıyla kaydedildi!";
        
        // Aksiyon türüne göre yönlendirme
        if ($validated['action'] === 'save_and_add') {
            return redirect()
                ->route('samples.create', ['casting_id' => $casting->id])
                ->with('success', $message . ' Yeni prova ekleyebilirsiniz.');
        }
        
        return redirect()
            ->route('castings.show', $casting)
            ->with('success', $message);
    }
    
    /**
     * Prova detaylarını göster
     */
    public function show(Sample $sample)
    {
        $sample->load(['casting.furnace.furnaceSet', 'adjustments']);
        
        // Kalite standartları ile karşılaştırma
        $qualityStandards = QualityStandard::active()->get();
        $qualityChecks = [];
        
        foreach ($qualityStandards as $standard) {
            $check = $sample->checkQualityStandards($standard);
            $qualityChecks[] = [
                'standard' => $standard,
                'is_compliant' => $check['is_compliant'],
                'violations' => $check['violations']
            ];
        }
        
        return view('samples.show', compact('sample', 'qualityChecks'));
    }
    
    /**
     * Prova düzenleme formu
     */
    public function edit(Sample $sample)
    {
        $sample->load('casting.furnace.furnaceSet');
        $qualityStandards = QualityStandard::all();
        
        return view('samples.edit', compact('sample', 'qualityStandards'));
    }
    
    /**
     * Prova güncelle
     */
    public function update(Request $request, Sample $sample)
    {
        $request->validate([
            'carbon_percentage' => 'nullable|numeric|min:0|max:10',
            'manganese_percentage' => 'nullable|numeric|min:0|max:10',
            'silicon_percentage' => 'nullable|numeric|min:0|max:10',
            'phosphorus_percentage' => 'nullable|numeric|min:0|max:1',
            'sulfur_percentage' => 'nullable|numeric|min:0|max:1',
            'chromium_percentage' => 'nullable|numeric|min:0|max:10',
            'nickel_percentage' => 'nullable|numeric|min:0|max:10',
            'molybdenum_percentage' => 'nullable|numeric|min:0|max:5',
            'temperature' => 'nullable|numeric|min:0|max:2000',
            'density' => 'nullable|numeric|min:0|max:20',
            'quality_notes' => 'nullable|string|max:1000',
            'analyzed_by' => 'required|string|max:100'
        ]);
        
        $sample->update($request->only([
            'carbon_percentage',
            'manganese_percentage',
            'silicon_percentage',
            'phosphorus_percentage',
            'sulfur_percentage',
            'chromium_percentage',
            'nickel_percentage',
            'molybdenum_percentage',
            'temperature',
            'density',
            'quality_notes',
            'analyzed_by'
        ]));
        
        return redirect()->route('samples.show', $sample)
                        ->with('success', 'Prova başarıyla güncellendi');
    }
    
    /**
     * Prova kalite durumunu güncelle
     */
    public function updateQualityStatus(Request $request, Sample $sample)
    {
        $request->validate([
            'quality_status' => 'required|in:pending,approved,rejected,needs_adjustment',
            'quality_notes' => 'nullable|string|max:1000'
        ]);
        
        $sample->update([
            'quality_status' => $request->quality_status,
            'quality_notes' => $request->quality_notes
        ]);
        
        $statusNames = [
            'pending' => 'Beklemede',
            'approved' => 'Onaylandı',
            'rejected' => 'Reddedildi',
            'needs_adjustment' => 'Düzeltme Gerekli'
        ];
        
        return response()->json([
            'success' => true,
            'message' => 'Kalite durumu "' . $statusNames[$request->quality_status] . '" olarak güncellendi',
            'sample' => $sample->fresh()
        ]);
    }
    
    /**
     * Telsiz bildirimi kaydet
     */
    public function recordRadioReport(Request $request, Sample $sample)
    {
        $request->validate([
            'reported_by' => 'required|string|max:100'
        ]);
        
        $sample->update([
            'reported_via_radio' => true,
            'reported_at' => now(),
            'reported_by' => $request->reported_by
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Telsiz bildirimi kaydedildi',
            'sample' => $sample->fresh()
        ]);
    }
    
    /**
     * Prova kalite kontrol raporu
     */
    public function qualityReport(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(7)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        
        $samples = Sample::with(['casting.furnace'])
            ->whereBetween('sample_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->get();
        
        // Genel istatistikler
        $stats = [
            'total_samples' => $samples->count(),
            'approved' => $samples->where('quality_status', 'approved')->count(),
            'rejected' => $samples->where('quality_status', 'rejected')->count(),
            'pending' => $samples->where('quality_status', 'pending')->count(),
            'needs_adjustment' => $samples->where('quality_status', 'needs_adjustment')->count()
        ];
        
        // Ocak bazında dağılım
        $furnaceStats = $samples->groupBy('casting.furnace.name')->map(function($group) {
            return [
                'total' => $group->count(),
                'approved' => $group->where('quality_status', 'approved')->count(),
                'rejected' => $group->where('quality_status', 'rejected')->count(),
                'quality_rate' => $group->count() > 0 
                    ? round(($group->where('quality_status', 'approved')->count() / $group->count()) * 100, 1)
                    : 0
            ];
        });
        
        // Günlük trend
        $dailyTrend = $samples->groupBy(function($sample) {
            return $sample->sample_time->format('Y-m-d');
        })->map(function($group) {
            return [
                'total' => $group->count(),
                'approved' => $group->where('quality_status', 'approved')->count(),
                'rejected' => $group->where('quality_status', 'rejected')->count()
            ];
        });
        
        // Ortalama kimyasal değerler (onaylanan provalar için)
        $approvedSamples = $samples->where('quality_status', 'approved');
        $avgChemicals = [];
        
        if ($approvedSamples->count() > 0) {
            $avgChemicals = [
                'carbon' => round($approvedSamples->avg('carbon_percentage'), 3),
                'manganese' => round($approvedSamples->avg('manganese_percentage'), 3),
                'silicon' => round($approvedSamples->avg('silicon_percentage'), 3),
                'phosphorus' => round($approvedSamples->avg('phosphorus_percentage'), 3),
                'sulfur' => round($approvedSamples->avg('sulfur_percentage'), 3),
                'chromium' => round($approvedSamples->avg('chromium_percentage'), 3),
                'nickel' => round($approvedSamples->avg('nickel_percentage'), 3),
                'molybdenum' => round($approvedSamples->avg('molybdenum_percentage'), 3)
            ];
        }
        
        return response()->json([
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'general_stats' => $stats,
            'furnace_stats' => $furnaceStats,
            'daily_trend' => $dailyTrend,
            'average_chemicals' => $avgChemicals
        ]);
    }
    
    /**
     * Bekleyen provaları listele
     */
    public function pending()
    {
        $samples = Sample::pending()
            ->with(['casting.furnace.furnaceSet'])
            ->orderBy('sample_time', 'asc')
            ->get();
        
        return view('samples.pending', compact('samples'));
    }
    
    /**
     * Prova sil
     */
    public function destroy(Sample $sample)
    {
        $sample->delete();
        
        return redirect()->route('samples.index')
                        ->with('success', 'Prova başarıyla silindi');
    }
}
