<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ham Madde Ekleme Modeli
 * Prova sonuçlarına göre ocağa eklenen malzemeler (karbon, mangan, vb.)
 */
class Adjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sample_id',
        'casting_id',
        'material_type',
        'amount_kg',
        'target_percentage',
        'actual_percentage_before',
        'actual_percentage_after',
        'adjustment_date',
        'added_by',
        'adjustment_reason',
        'notes',
        'is_successful',
        'verified_at',
        'verified_by'
    ];

    protected $casts = [
        'adjustment_date' => 'datetime',
        'verified_at' => 'datetime',
        'is_successful' => 'boolean',
        'amount_kg' => 'decimal:2',
        'target_percentage' => 'decimal:3',
        'actual_percentage_before' => 'decimal:3',
        'actual_percentage_after' => 'decimal:3'
    ];

    /**
     * Bu eklemenin ait olduğu prova
     */
    public function sample(): BelongsTo
    {
        return $this->belongsTo(Sample::class);
    }

    /**
     * Bu eklemenin ait olduğu döküm
     */
    public function casting(): BelongsTo
    {
        return $this->belongsTo(Casting::class);
    }

    /**
     * Bu ekleme başarılı mı?
     */
    public function isSuccessful()
    {
        return $this->is_successful === true;
    }

    /**
     * Bu ekleme doğrulandı mı?
     */
    public function isVerified()
    {
        return $this->verified_at !== null;
    }

    /**
     * Ekleme sonrası yüzde değişimini hesapla
     */
    public function getPercentageChange()
    {
        if ($this->actual_percentage_before === null || $this->actual_percentage_after === null) {
            return null;
        }
        
        return $this->actual_percentage_after - $this->actual_percentage_before;
    }

    /**
     * Hedef yüzdeye ulaşılıp ulaşılmadığını kontrol et
     */
    public function isTargetAchieved($tolerance = 0.05)
    {
        if ($this->target_percentage === null || $this->actual_percentage_after === null) {
            return null;
        }
        
        $difference = abs($this->actual_percentage_after - $this->target_percentage);
        return $difference <= $tolerance;
    }

    /**
     * Ekleme etkinliğini hesapla (kg başına yüzde artışı)
     */
    public function getEfficiency()
    {
        $percentageChange = $this->getPercentageChange();
        
        if ($percentageChange === null || $this->amount_kg <= 0) {
            return null;
        }
        
        return $percentageChange / $this->amount_kg;
    }

    /**
     * Malzeme türüne göre Türkçe isim getir
     */
    public function getMaterialNameTurkish()
    {
        $names = [
            'carbon' => 'Karbon',
            'manganese' => 'Mangan',
            'silicon' => 'Silisyum',
            'phosphorus' => 'Fosfor',
            'sulfur' => 'Kükürt',
            'chromium' => 'Krom',
            'nickel' => 'Nikel',
            'molybdenum' => 'Molibden'
        ];
        
        return $names[$this->material_type] ?? $this->material_type;
    }

    /**
     * Ekleme nedenine göre Türkçe açıklama getir
     */
    public function getReasonDescription()
    {
        $reasons = [
            'low_content' => 'Düşük İçerik',
            'high_content' => 'Yüksek İçerik',
            'quality_improvement' => 'Kalite İyileştirme',
            'customer_requirement' => 'Müşteri Talebi'
        ];
        
        return $reasons[$this->adjustment_reason] ?? $this->adjustment_reason;
    }

    /**
     * Scope: Başarılı eklemeleri getir
     */
    public function scopeSuccessful($query)
    {
        return $query->where('is_successful', true);
    }

    /**
     * Scope: Doğrulanmış eklemeleri getir
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    /**
     * Scope: Belirli malzeme türündeki eklemeleri getir
     */
    public function scopeMaterialType($query, $type)
    {
        return $query->where('material_type', $type);
    }

    /**
     * Scope: Belirli tarih aralığındaki eklemeleri getir
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('adjustment_date', [$startDate, $endDate]);
    }

    /**
     * Scope: Belirli nedene dayalı eklemeleri getir
     */
    public function scopeReason($query, $reason)
    {
        return $query->where('adjustment_reason', $reason);
    }
}
