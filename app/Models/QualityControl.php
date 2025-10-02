<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Kalite Kontrol Modeli
 * 
 * Kullanım:
 * $qc = QualityControl::create([
 *     'casting_id' => 1,
 *     'carbon_percentage' => 0.25,
 *     'silicon_percentage' => 0.15,
 *     'manganese_percentage' => 0.80
 * ]);
 */
class QualityControl extends Model
{
    use HasFactory;

    protected $fillable = [
        'casting_id',
        'carbon_percentage',
        'silicon_percentage',
        'manganese_percentage',
        'phosphorus_percentage',
        'sulfur_percentage',
        'chromium_percentage',
        'nickel_percentage',
        'copper_percentage',
        'temperature',
        'test_result',
        'test_time',
        'tested_by',
        'remarks',
        'prova_data'
    ];

    protected $casts = [
        'carbon_percentage' => 'decimal:3',
        'silicon_percentage' => 'decimal:3',
        'manganese_percentage' => 'decimal:3',
        'phosphorus_percentage' => 'decimal:3',
        'sulfur_percentage' => 'decimal:3',
        'chromium_percentage' => 'decimal:3',
        'nickel_percentage' => 'decimal:3',
        'copper_percentage' => 'decimal:3',
        'temperature' => 'decimal:2',
        'test_time' => 'datetime',
        'prova_data' => 'array'
    ];

    /**
     * Kalite kontrolün ait olduğu döküm
     */
    public function casting(): BelongsTo
    {
        return $this->belongsTo(Casting::class);
    }

    /**
     * Standart çelik kompozisyon limitleri
     */
    public static function getStandardLimits()
    {
        return [
            'carbon' => ['min' => 0.15, 'max' => 0.35],
            'silicon' => ['min' => 0.10, 'max' => 0.30],
            'manganese' => ['min' => 0.60, 'max' => 1.00],
            'phosphorus' => ['min' => 0.00, 'max' => 0.04],
            'sulfur' => ['min' => 0.00, 'max' => 0.05],
            'temperature' => ['min' => 1500, 'max' => 1650] // °C
        ];
    }

    /**
     * Otomatik test sonucu değerlendirmesi
     */
    public function evaluateTestResult()
    {
        $limits = self::getStandardLimits();
        $failed = false;

        // Karbon kontrolü
        if ($this->carbon_percentage !== null) {
            if ($this->carbon_percentage < $limits['carbon']['min'] || 
                $this->carbon_percentage > $limits['carbon']['max']) {
                $failed = true;
            }
        }

        // Silisyum kontrolü
        if ($this->silicon_percentage !== null) {
            if ($this->silicon_percentage < $limits['silicon']['min'] || 
                $this->silicon_percentage > $limits['silicon']['max']) {
                $failed = true;
            }
        }

        // Mangan kontrolü
        if ($this->manganese_percentage !== null) {
            if ($this->manganese_percentage < $limits['manganese']['min'] || 
                $this->manganese_percentage > $limits['manganese']['max']) {
                $failed = true;
            }
        }

        // Fosfor kontrolü
        if ($this->phosphorus_percentage !== null) {
            if ($this->phosphorus_percentage > $limits['phosphorus']['max']) {
                $failed = true;
            }
        }

        // Kükürt kontrolü
        if ($this->sulfur_percentage !== null) {
            if ($this->sulfur_percentage > $limits['sulfur']['max']) {
                $failed = true;
            }
        }

        // Sıcaklık kontrolü
        if ($this->temperature !== null) {
            if ($this->temperature < $limits['temperature']['min'] || 
                $this->temperature > $limits['temperature']['max']) {
                $failed = true;
            }
        }

        $result = $failed ? 'failed' : 'passed';
        
        $this->update(['test_result' => $result]);
        
        return $result;
    }

    /**
     * Kompozisyon değerlerini yüzde olarak formatlar
     */
    public function getFormattedComposition()
    {
        return [
            'Karbon (C)' => $this->carbon_percentage ? number_format($this->carbon_percentage, 3) . '%' : '-',
            'Silisyum (Si)' => $this->silicon_percentage ? number_format($this->silicon_percentage, 3) . '%' : '-',
            'Mangan (Mn)' => $this->manganese_percentage ? number_format($this->manganese_percentage, 3) . '%' : '-',
            'Fosfor (P)' => $this->phosphorus_percentage ? number_format($this->phosphorus_percentage, 3) . '%' : '-',
            'Kükürt (S)' => $this->sulfur_percentage ? number_format($this->sulfur_percentage, 3) . '%' : '-',
            'Krom (Cr)' => $this->chromium_percentage ? number_format($this->chromium_percentage, 3) . '%' : '-',
            'Nikel (Ni)' => $this->nickel_percentage ? number_format($this->nickel_percentage, 3) . '%' : '-',
            'Bakır (Cu)' => $this->copper_percentage ? number_format($this->copper_percentage, 3) . '%' : '-'
        ];
    }

    /**
     * Limit dışı değerleri tespit eder
     */
    public function getOutOfLimitValues()
    {
        $limits = self::getStandardLimits();
        $outOfLimit = [];

        if ($this->carbon_percentage !== null) {
            if ($this->carbon_percentage < $limits['carbon']['min'] || 
                $this->carbon_percentage > $limits['carbon']['max']) {
                $outOfLimit['carbon'] = [
                    'value' => $this->carbon_percentage,
                    'limit' => $limits['carbon'],
                    'status' => $this->carbon_percentage < $limits['carbon']['min'] ? 'low' : 'high'
                ];
            }
        }

        if ($this->silicon_percentage !== null) {
            if ($this->silicon_percentage < $limits['silicon']['min'] || 
                $this->silicon_percentage > $limits['silicon']['max']) {
                $outOfLimit['silicon'] = [
                    'value' => $this->silicon_percentage,
                    'limit' => $limits['silicon'],
                    'status' => $this->silicon_percentage < $limits['silicon']['min'] ? 'low' : 'high'
                ];
            }
        }

        if ($this->manganese_percentage !== null) {
            if ($this->manganese_percentage < $limits['manganese']['min'] || 
                $this->manganese_percentage > $limits['manganese']['max']) {
                $outOfLimit['manganese'] = [
                    'value' => $this->manganese_percentage,
                    'limit' => $limits['manganese'],
                    'status' => $this->manganese_percentage < $limits['manganese']['min'] ? 'low' : 'high'
                ];
            }
        }

        return $outOfLimit;
    }

    /**
     * Test sonucu rengini döndürür (UI için)
     */
    public function getStatusColor()
    {
        return match($this->test_result) {
            'passed' => 'success',
            'failed' => 'danger',
            'pending' => 'warning',
            default => 'secondary'
        };
    }

    /**
     * Test sonucu metnini döndürür
     */
    public function getStatusText()
    {
        return match($this->test_result) {
            'passed' => 'Başarılı',
            'failed' => 'Başarısız',
            'pending' => 'Beklemede',
            default => 'Bilinmiyor'
        };
    }
}
