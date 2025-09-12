<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Prova Modeli
 * Her dökümden alınan prova örnekleri ve analiz sonuçları
 */
class Sample extends Model
{
    use HasFactory;

    protected $fillable = [
        'casting_id',
        'sample_number',
        'sample_time',
        'sample_type',
        'carbon_content',
        'manganese_content',
        'silicon_content',
        'sulfur_content',
        'phosphorus_content',
        'copper_content',
        'temperature',
        'quality_standard_id',
        'sampled_by',
        'notes',
        'quality_status',
        'quality_notes',
        'analyzed_by',
        'analyzed_at',
        'reported_via_radio',
        'reported_at',
        'reported_by'
    ];

    protected $casts = [
        'sample_time' => 'datetime',
        'analyzed_at' => 'datetime',
        'reported_at' => 'datetime',
        'reported_via_radio' => 'boolean',
        'sample_number' => 'integer',
        'carbon_content' => 'decimal:3',
        'manganese_content' => 'decimal:3',
        'silicon_content' => 'decimal:3',
        'sulfur_content' => 'decimal:3',
        'phosphorus_content' => 'decimal:3',
        'copper_content' => 'decimal:3',
        'temperature' => 'decimal:2'
    ];

    /**
     * Bu provanın ait olduğu döküm
     */
    public function casting(): BelongsTo
    {
        return $this->belongsTo(Casting::class);
    }

    /**
     * Bu prova sonucunda yapılan ham madde eklemeleri
     */
    public function adjustments(): HasMany
    {
        return $this->hasMany(Adjustment::class);
    }

    /**
     * Bu provanın kimyasal değerlerini dizi olarak getir
     */
    public function getChemicalValues()
    {
        return [
            'carbon' => $this->carbon_percentage,
            'manganese' => $this->manganese_percentage,
            'silicon' => $this->silicon_percentage,
            'phosphorus' => $this->phosphorus_percentage,
            'sulfur' => $this->sulfur_percentage,
            'chromium' => $this->chromium_percentage,
            'nickel' => $this->nickel_percentage,
            'molybdenum' => $this->molybdenum_percentage,
        ];
    }

    /**
     * Bu provanın kalite standartlarına uygun olup olmadığını kontrol et
     */
    public function checkQualityStandards(QualityStandard $standard)
    {
        $violations = [];
        
        // Karbon kontrolü
        if ($this->carbon_percentage !== null) {
            if ($standard->carbon_min && $this->carbon_percentage < $standard->carbon_min) {
                $violations[] = "Karbon çok düşük ({$this->carbon_percentage}% < {$standard->carbon_min}%)";
            }
            if ($standard->carbon_max && $this->carbon_percentage > $standard->carbon_max) {
                $violations[] = "Karbon çok yüksek ({$this->carbon_percentage}% > {$standard->carbon_max}%)";
            }
        }
        
        // Mangan kontrolü
        if ($this->manganese_percentage !== null) {
            if ($standard->manganese_min && $this->manganese_percentage < $standard->manganese_min) {
                $violations[] = "Mangan çok düşük ({$this->manganese_percentage}% < {$standard->manganese_min}%)";
            }
            if ($standard->manganese_max && $this->manganese_percentage > $standard->manganese_max) {
                $violations[] = "Mangan çok yüksek ({$this->manganese_percentage}% > {$standard->manganese_max}%)";
            }
        }
        
        // Silisyum kontrolü
        if ($this->silicon_percentage !== null) {
            if ($standard->silicon_min && $this->silicon_percentage < $standard->silicon_min) {
                $violations[] = "Silisyum çok düşük ({$this->silicon_percentage}% < {$standard->silicon_min}%)";
            }
            if ($standard->silicon_max && $this->silicon_percentage > $standard->silicon_max) {
                $violations[] = "Silisyum çok yüksek ({$this->silicon_percentage}% > {$standard->silicon_max}%)";
            }
        }
        
        // Fosfor kontrolü
        if ($this->phosphorus_percentage !== null) {
            if ($standard->phosphorus_min && $this->phosphorus_percentage < $standard->phosphorus_min) {
                $violations[] = "Fosfor çok düşük ({$this->phosphorus_percentage}% < {$standard->phosphorus_min}%)";
            }
            if ($standard->phosphorus_max && $this->phosphorus_percentage > $standard->phosphorus_max) {
                $violations[] = "Fosfor çok yüksek ({$this->phosphorus_percentage}% > {$standard->phosphorus_max}%)";
            }
        }
        
        // Kükürt kontrolü
        if ($this->sulfur_percentage !== null) {
            if ($standard->sulfur_min && $this->sulfur_percentage < $standard->sulfur_min) {
                $violations[] = "Kükürt çok düşük ({$this->sulfur_percentage}% < {$standard->sulfur_min}%)";
            }
            if ($standard->sulfur_max && $this->sulfur_percentage > $standard->sulfur_max) {
                $violations[] = "Kükürt çok yüksek ({$this->sulfur_percentage}% > {$standard->sulfur_max}%)";
            }
        }
        
        return [
            'is_compliant' => empty($violations),
            'violations' => $violations
        ];
    }

    /**
     * Bu prova onaylandı mı?
     */
    public function isApproved()
    {
        return $this->quality_status === 'approved';
    }

    /**
     * Bu prova reddedildi mi?
     */
    public function isRejected()
    {
        return $this->quality_status === 'rejected';
    }

    /**
     * Bu prova düzeltme gerektiriyor mu?
     */
    public function needsAdjustment()
    {
        return $this->quality_status === 'needs_adjustment';
    }

    /**
     * Bu prova beklemede mi?
     */
    public function isPending()
    {
        return $this->quality_status === 'pending';
    }

    /**
     * Scope: Onaylanan provaları getir
     */
    public function scopeApproved($query)
    {
        return $query->where('quality_status', 'approved');
    }

    /**
     * Scope: Reddedilen provaları getir
     */
    public function scopeRejected($query)
    {
        return $query->where('quality_status', 'rejected');
    }

    /**
     * Scope: Beklemedeki provaları getir
     */
    public function scopePending($query)
    {
        return $query->where('quality_status', 'pending');
    }

    /**
     * Scope: Düzeltme gerektiren provaları getir
     */
    public function scopeNeedsAdjustment($query)
    {
        return $query->where('quality_status', 'needs_adjustment');
    }

    /**
     * Scope: Telsizle bildirilen provaları getir
     */
    public function scopeReportedViaRadio($query)
    {
        return $query->where('reported_via_radio', true);
    }

    /**
     * Scope: Belirli tarih aralığındaki provaları getir
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('sample_time', [$startDate, $endDate]);
    }
    
    /**
     * Kalite standardına uygunluk kontrolü
     */
    public function checkQualityCompliance()
    {
        if (!$this->quality_standard_id) {
            return;
        }
        
        $standard = $this->qualityStandard;
        if (!$standard) {
            return;
        }
        
        $isCompliant = true;
        $violations = [];
        
        // Karbon kontrolü
        if ($this->carbon_content < $standard->carbon_min || $this->carbon_content > $standard->carbon_max) {
            $isCompliant = false;
            $violations[] = "Karbon: {$this->carbon_content}% (Beklenen: {$standard->carbon_min}-{$standard->carbon_max}%)";
        }
        
        // Mangan kontrolü
        if ($this->manganese_content < $standard->manganese_min || $this->manganese_content > $standard->manganese_max) {
            $isCompliant = false;
            $violations[] = "Mangan: {$this->manganese_content}% (Beklenen: {$standard->manganese_min}-{$standard->manganese_max}%)";
        }
        
        // Durum güncelleme
        $this->quality_status = $isCompliant ? 'approved' : 'needs_adjustment';
        
        if (!empty($violations)) {
            $this->quality_notes = 'Standart dışı değerler: ' . implode(', ', $violations);
        }
        
        $this->save();
        
        return [
            'is_compliant' => $isCompliant,
            'violations' => $violations
        ];
    }
    
    /**
     * QualityStandard ilişkisi
     */
    public function qualityStandard(): BelongsTo
    {
        return $this->belongsTo(QualityStandard::class);
    }
    
    /**
     * Accessor: Carbon değeri
     */
    public function getCarbonAttribute()
    {
        return $this->carbon_content;
    }
    
    /**
     * Accessor: Silicon değeri
     */
    public function getSiliconAttribute()
    {
        return $this->silicon_content;
    }
    
    /**
     * Accessor: Manganese değeri
     */
    public function getManganeseAttribute()
    {
        return $this->manganese_content;
    }
    
    /**
     * Accessor: Sulfur değeri
     */
    public function getSulfurAttribute()
    {
        return $this->sulfur_content;
    }
    
    /**
     * Accessor: Phosphorus değeri
     */
    public function getPhosphorusAttribute()
    {
        return $this->phosphorus_content;
    }
    
    /**
     * Accessor: Copper değeri
     */
    public function getCopperAttribute()
    {
        return $this->copper_content;
    }
}
