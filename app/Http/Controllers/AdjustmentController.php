<?php

namespace App\Http\Controllers;

use App\Models\Adjustment;
use App\Models\Casting;
use App\Models\Sample;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdjustmentController extends Controller
{
    /**
     * Alyaj malzemesi ekleme
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'casting_id' => 'required|exists:castings,id',
            'material_type' => 'required|in:carbon,manganese,silicon,phosphorus,sulfur,copper',
            'amount_kg' => 'required|numeric|min:0',
            'target_percentage' => 'nullable|numeric|min:0|max:100',
            'adjustment_reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500'
        ]);

        $adjustment = Adjustment::create([
            'sample_id' => null, // Alyaj malzemesi doğrudan döküme ekleniyor
            'casting_id' => $request->casting_id,
            'material_type' => $request->material_type,
            'amount_kg' => $request->amount_kg,
            'target_percentage' => $request->target_percentage,
            'adjustment_date' => now(),
            'added_by' => auth()->user()->name ?? 'Sistem',
            'adjustment_reason' => $request->adjustment_reason,
            'notes' => $request->notes,
            'is_successful' => false
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Alyaj malzemesi başarıyla eklendi!',
            'adjustment' => $adjustment
        ]);
    }

    /**
     * Alyaj malzemesi güncelleme
     */
    public function update(Request $request, Adjustment $adjustment): JsonResponse
    {
        $request->validate([
            'material_type' => 'required|in:carbon,manganese,silicon,phosphorus,sulfur,copper',
            'amount_kg' => 'required|numeric|min:0',
            'target_percentage' => 'nullable|numeric|min:0|max:100',
            'adjustment_reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500'
        ]);

        $adjustment->update([
            'material_type' => $request->material_type,
            'amount_kg' => $request->amount_kg,
            'target_percentage' => $request->target_percentage,
            'adjustment_reason' => $request->adjustment_reason,
            'notes' => $request->notes
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Alyaj malzemesi başarıyla güncellendi!',
            'adjustment' => $adjustment
        ]);
    }

    /**
     * Alyaj malzemesi silme
     */
    public function destroy(Adjustment $adjustment): JsonResponse
    {
        $adjustment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Alyaj malzemesi başarıyla silindi!'
        ]);
    }

    /**
     * Döküme ait alyaj malzemelerini getir
     */
    public function getByCasting(Casting $casting): JsonResponse
    {
        $adjustments = $casting->adjustments()
            ->orderBy('adjustment_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'adjustments' => $adjustments
        ]);
    }
}
