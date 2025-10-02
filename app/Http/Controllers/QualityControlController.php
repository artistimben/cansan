<?php

namespace App\Http\Controllers;

use App\Models\Casting;
use App\Models\QualityControl;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Kalite Kontrol Controller
 * 
 * Döküm kalite kontrol değerlerini yönetir:
 * - Karbon, Silisyum, Mangan vb. değerler
 * - Otomatik test sonucu değerlendirmesi
 * - Limit dışı değer uyarıları
 */
class QualityControlController extends Controller
{
    /**
     * Kalite kontrol değerleri kaydetme/güncelleme
     * 
     * @param Request $request
     * @param int $castingId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $castingId)
    {
        try {
            $casting = Casting::findOrFail($castingId);
            
            // Validation rules
            $rules = [
                'carbon_percentage' => 'nullable|numeric|between:0,5',
                'silicon_percentage' => 'nullable|numeric|between:0,5',
                'manganese_percentage' => 'nullable|numeric|between:0,5',
                'phosphorus_percentage' => 'nullable|numeric|between:0,1',
                'sulfur_percentage' => 'nullable|numeric|between:0,1',
                'chromium_percentage' => 'nullable|numeric|between:0,10',
                'nickel_percentage' => 'nullable|numeric|between:0,10',
                'copper_percentage' => 'nullable|numeric|between:0,10',
                'temperature' => 'nullable|numeric|between:1000,2000',
                'tested_by' => 'nullable|string|max:100',
                'remarks' => 'nullable|string|max:500',
                'prova_data' => 'nullable|array'
            ];

            $request->validate($rules, [
                'carbon_percentage.between' => 'Karbon yüzdesi 0-5 arasında olmalıdır.',
                'silicon_percentage.between' => 'Silisyum yüzdesi 0-5 arasında olmalıdır.',
                'manganese_percentage.between' => 'Mangan yüzdesi 0-5 arasında olmalıdır.',
                'temperature.between' => 'Sıcaklık 1000-2000°C arasında olmalıdır.'
            ]);

            // Mevcut kalite kontrolü güncelle veya yeni oluştur
            $qualityControl = $casting->qualityControl;
            
            if ($qualityControl) {
                $qualityControl->update($request->all());
            } else {
                $qualityControl = $casting->addQualityControl($request->all());
            }

            // Otomatik test sonucu değerlendirmesi
            $testResult = $qualityControl->evaluateTestResult();
            
            // Limit dışı değerleri tespit et
            $outOfLimitValues = $qualityControl->getOutOfLimitValues();

            return response()->json([
                'success' => true,
                'message' => 'Kalite kontrol değerleri başarıyla kaydedildi.',
                'data' => [
                    'quality_control_id' => $qualityControl->id,
                    'test_result' => $testResult,
                    'test_result_text' => $qualityControl->getStatusText(),
                    'status_color' => $qualityControl->getStatusColor(),
                    'out_of_limit_values' => $outOfLimitValues,
                    'composition' => $qualityControl->getFormattedComposition()
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Girilen değerlerde hata var.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kalite kontrol değerleri kaydedilirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kalite kontrol değerlerini getir
     * 
     * @param int $castingId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($castingId)
    {
        try {
            $casting = Casting::with(['qualityControl', 'furnace'])->findOrFail($castingId);
            
            $data = [
                'casting' => [
                    'id' => $casting->id,
                    'furnace_number' => $casting->furnace->furnace_number,
                    'casting_number' => $casting->casting_number_per_furnace,
                    'global_number' => $casting->global_casting_number,
                    'start_time' => $casting->start_time->format('d.m.Y H:i'),
                    'status' => $casting->status
                ],
                'quality_control' => null
            ];

            if ($casting->qualityControl) {
                $qc = $casting->qualityControl;
                $data['quality_control'] = [
                    'id' => $qc->id,
                    'carbon_percentage' => $qc->carbon_percentage,
                    'silicon_percentage' => $qc->silicon_percentage,
                    'manganese_percentage' => $qc->manganese_percentage,
                    'phosphorus_percentage' => $qc->phosphorus_percentage,
                    'sulfur_percentage' => $qc->sulfur_percentage,
                    'chromium_percentage' => $qc->chromium_percentage,
                    'nickel_percentage' => $qc->nickel_percentage,
                    'copper_percentage' => $qc->copper_percentage,
                    'temperature' => $qc->temperature,
                    'prova_data' => $qc->prova_data,
                    'test_result' => $qc->test_result,
                    'test_result_text' => $qc->getStatusText(),
                    'status_color' => $qc->getStatusColor(),
                    'test_time' => $qc->test_time ? $qc->test_time->format('d.m.Y H:i') : null,
                    'tested_by' => $qc->tested_by,
                    'remarks' => $qc->remarks,
                    'composition' => $qc->getFormattedComposition(),
                    'out_of_limit_values' => $qc->getOutOfLimitValues()
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kalite kontrol verileri getirilemedi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Standart kompozisyon limitlerini getir
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStandardLimits()
    {
        try {
            $limits = QualityControl::getStandardLimits();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'limits' => $limits,
                    'formatted_limits' => [
                        'Karbon (C)' => $limits['carbon']['min'] . '% - ' . $limits['carbon']['max'] . '%',
                        'Silisyum (Si)' => $limits['silicon']['min'] . '% - ' . $limits['silicon']['max'] . '%',
                        'Mangan (Mn)' => $limits['manganese']['min'] . '% - ' . $limits['manganese']['max'] . '%',
                        'Fosfor (P)' => 'Maks ' . $limits['phosphorus']['max'] . '%',
                        'Kükürt (S)' => 'Maks ' . $limits['sulfur']['max'] . '%',
                        'Sıcaklık' => $limits['temperature']['min'] . '°C - ' . $limits['temperature']['max'] . '°C'
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Standart limitler getirilemedi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kalite kontrol raporu (Filtrelenebilir)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQualityReport(Request $request)
    {
        try {
            $query = QualityControl::with('casting.furnace');

            // Tarih filtreleme
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('test_time', [
                    $request->start_date . ' 00:00:00',
                    $request->end_date . ' 23:59:59'
                ]);
            }

            // Test sonucu filtreleme
            if ($request->has('test_result') && $request->test_result !== 'all') {
                $query->where('test_result', $request->test_result);
            }

            // Ocak filtreleme
            if ($request->has('furnace_id') && $request->furnace_id !== 'all') {
                $query->whereHas('casting', function ($q) use ($request) {
                    $q->where('furnace_id', $request->furnace_id);
                });
            }

            $qualityControls = $query->orderBy('test_time', 'desc')
                                   ->limit(100)
                                   ->get();

            // İstatistikler
            $stats = [
                'total_tests' => $qualityControls->count(),
                'passed_tests' => $qualityControls->where('test_result', 'passed')->count(),
                'failed_tests' => $qualityControls->where('test_result', 'failed')->count(),
                'pending_tests' => $qualityControls->where('test_result', 'pending')->count(),
                'success_rate' => 0
            ];

            if ($stats['total_tests'] > 0) {
                $stats['success_rate'] = round(($stats['passed_tests'] / $stats['total_tests']) * 100, 1);
            }

            $data = $qualityControls->map(function ($qc) {
                return [
                    'id' => $qc->id,
                    'casting_id' => $qc->casting_id,
                    'furnace_number' => $qc->casting->furnace->furnace_number,
                    'casting_number' => $qc->casting->casting_number_per_furnace,
                    'global_number' => $qc->casting->global_casting_number,
                    'test_time' => $qc->test_time ? $qc->test_time->format('d.m.Y H:i') : '-',
                    'test_result' => $qc->test_result,
                    'test_result_text' => $qc->getStatusText(),
                    'status_color' => $qc->getStatusColor(),
                    'carbon_percentage' => $qc->carbon_percentage,
                    'silicon_percentage' => $qc->silicon_percentage,
                    'manganese_percentage' => $qc->manganese_percentage,
                    'temperature' => $qc->temperature,
                    'tested_by' => $qc->tested_by,
                    'out_of_limit_count' => count($qc->getOutOfLimitValues())
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kalite kontrol raporu oluşturulamadı: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toplu kalite kontrol değerlendirmesi (Beklemedeki testler için)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkEvaluate(Request $request)
    {
        try {
            $pendingTests = QualityControl::where('test_result', 'pending')->get();
            $evaluatedCount = 0;
            $passedCount = 0;
            $failedCount = 0;

            foreach ($pendingTests as $qc) {
                $result = $qc->evaluateTestResult();
                $evaluatedCount++;
                
                if ($result === 'passed') {
                    $passedCount++;
                } else {
                    $failedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "{$evaluatedCount} adet beklemedeki test değerlendirildi.",
                'data' => [
                    'evaluated_count' => $evaluatedCount,
                    'passed_count' => $passedCount,
                    'failed_count' => $failedCount,
                    'success_rate' => $evaluatedCount > 0 ? round(($passedCount / $evaluatedCount) * 100, 1) : 0
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Toplu değerlendirme yapılırken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }
}
