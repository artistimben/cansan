<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Kalite Standardı Modeli
 * Çelik türlerine göre kabul edilebilir kimyasal değer aralıkları
 */
class QualityStandard extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'steel_grade',
        'carbon_min',
        'carbon_max',
        'manganese_min',
        'manganese_max',
        'silicon_min',
        'silicon_max',
        'phosphorus_min',
        'phosphorus_max',
        'sulfur_min',
        'sulfur_max',
        'chromium_min',
        'chromium_max',
        'nickel_min',
        'nickel_max',
        'molybdenum_min',
        'molybdenum_max',
        'is_active',
        'description'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'carbon_min' => 'decimal:3',
        'carbon_max' => 'decimal:3',
        'manganese_min' => 'decimal:3',
        'manganese_max' => 'decimal:3',
        'silicon_min' => 'decimal:3',
        'silicon_max' => 'decimal:3',
        'phosphorus_min' => 'decimal:3',
        'phosphorus_max' => 'decimal:3',
        'sulfur_min' => 'decimal:3',
        'sulfur_max' => 'decimal:3',
        'chromium_min' => 'decimal:3',
        'chromium_max' => 'decimal:3',
        'nickel_min' => 'decimal:3',
        'nickel_max' => 'decimal:3',
        'molybdenum_min' => 'decimal:3',
        'molybdenum_max' => 'decimal:3',
    ];

    /**
     * Belirli bir element için limit değerlerini getir
     */
    public function getElementLimits($element)
    {
        $minField = $element . '_min';
        $maxField = $element . '_max';
        
        return [
            'min' => $this->$minField,
            'max' => $this->$maxField
        ];
    }

    /**
     * Tüm element limitlerini dizi olarak getir
     */
    public function getAllElementLimits()
    {
        return [
            'carbon' => $this->getElementLimits('carbon'),
            'manganese' => $this->getElementLimits('manganese'),
            'silicon' => $this->getElementLimits('silicon'),
            'phosphorus' => $this->getElementLimits('phosphorus'),
            'sulfur' => $this->getElementLimits('sulfur'),
            'chromium' => $this->getElementLimits('chromium'),
            'nickel' => $this->getElementLimits('nickel'),
            'molybdenum' => $this->getElementLimits('molybdenum'),
        ];
    }

    /**
     * Belirli bir değerin bu standartta kabul edilebilir olup olmadığını kontrol et
     */
    public function isValueAcceptable($element, $value)
    {
        if ($value === null) {
            return true; // Null değerler kabul edilebilir
        }
        
        $limits = $this->getElementLimits($element);
        
        if ($limits['min'] !== null && $value < $limits['min']) {
            return false;
        }
        
        if ($limits['max'] !== null && $value > $limits['max']) {
            return false;
        }
        
        return true;
    }

    /**
     * Prova değerlerini bu standarda göre kontrol et
     */
    public function validateSample(Sample $sample)
    {
        $violations = [];
        $chemicalValues = $sample->getChemicalValues();
        
        foreach ($chemicalValues as $element => $value) {
            if (!$this->isValueAcceptable($element, $value)) {
                $limits = $this->getElementLimits($element);
                $violations[$element] = [
                    'value' => $value,
                    'min' => $limits['min'],
                    'max' => $limits['max'],
                    'is_below_min' => $limits['min'] && $value < $limits['min'],
                    'is_above_max' => $limits['max'] && $value > $limits['max']
                ];
            }
        }
        
        return [
            'is_valid' => empty($violations),
            'violations' => $violations
        ];
    }

    /**
     * Scope: Aktif standartları getir
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Belirli çelik sınıfına ait standartları getir
     */
    public function scopeForSteelGrade($query, $grade)
    {
        return $query->where('steel_grade', $grade);
    }
}
