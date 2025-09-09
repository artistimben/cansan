<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Döküm Modeli
 * Her ocaktan alınan döküm kayıtları
 */
class Casting extends Model
{
    use HasFactory;

    protected $fillable = [
        'furnace_id',
        'casting_number',
        'casting_date',
        'shift',
        'operator_name',
        'target_temperature',
        'final_temperature',
        'notes',
        'completion_notes',
        'cancellation_reason',
        'status',
        'started_at',
        'completed_at'
    ];

    protected $casts = [
        'casting_date' => 'datetime',
        'target_temperature' => 'decimal:2',
        'final_temperature' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    /**
     * Bu dökümün ait olduğu ocak
     */
    public function furnace(): BelongsTo
    {
        return $this->belongsTo(Furnace::class);
    }

    /**
     * Bu dökümden alınan provalar
     */
    public function samples(): HasMany
    {
        return $this->hasMany(Sample::class);
    }

    /**
     * Bu dökümde yapılan ham madde eklemeleri
     */
    public function adjustments(): HasMany
    {
        return $this->hasMany(Adjustment::class);
    }

    /**
     * Bu dökümün son prova numarası
     */
    public function getLastSampleNumber()
    {
        $lastSample = $this->samples()
            ->orderBy('sample_number', 'desc')
            ->first();
            
        return $lastSample ? $lastSample->sample_number : 0;
    }

    /**
     * Bu dökümün bir sonraki prova numarası
     */
    public function getNextSampleNumber()
    {
        return $this->getLastSampleNumber() + 1;
    }

    /**
     * Bu dökümün kalite durumu
     */
    public function getQualityStatus()
    {
        $samples = $this->samples;
        
        if ($samples->isEmpty()) {
            return 'no_samples';
        }
        
        $approved = $samples->where('quality_status', 'approved')->count();
        $rejected = $samples->where('quality_status', 'rejected')->count();
        $pending = $samples->where('quality_status', 'pending')->count();
        $needsAdjustment = $samples->where('quality_status', 'needs_adjustment')->count();
        
        if ($rejected > 0) {
            return 'rejected';
        } elseif ($needsAdjustment > 0) {
            return 'needs_adjustment';
        } elseif ($pending > 0) {
            return 'pending';
        } elseif ($approved > 0) {
            return 'approved';
        }
        
        return 'unknown';
    }

    /**
     * Bu dökümün ortalama kimyasal değerleri
     */
    public function getAverageChemicalValues()
    {
        $samples = $this->samples()->where('quality_status', 'approved')->get();
        
        if ($samples->isEmpty()) {
            return null;
        }
        
        return [
            'carbon' => $samples->avg('carbon_percentage'),
            'manganese' => $samples->avg('manganese_percentage'),
            'silicon' => $samples->avg('silicon_percentage'),
            'phosphorus' => $samples->avg('phosphorus_percentage'),
            'sulfur' => $samples->avg('sulfur_percentage'),
            'chromium' => $samples->avg('chromium_percentage'),
            'nickel' => $samples->avg('nickel_percentage'),
            'molybdenum' => $samples->avg('molybdenum_percentage'),
        ];
    }

    /**
     * Bu döküm tamamlandı mı?
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Bu döküm aktif mi?
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Scope: Aktif dökümleri getir
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Tamamlanan dökümleri getir
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: Belirli tarih aralığındaki dökümleri getir
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('casting_date', [$startDate, $endDate]);
    }

    /**
     * Scope: Belirli vardiyaya ait dökümleri getir
     */
    public function scopeShift($query, $shift)
    {
        return $query->where('shift', $shift);
    }

    /**
     * Bir ocakta aktif döküm olup olmadığını kontrol et
     */
    public static function hasActiveCastingInFurnace(int $furnaceId): bool
    {
        return self::where('furnace_id', $furnaceId)
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Bir ocağa yeni döküm başlatmadan önce kontrolü
     */
    public function canStartNewCasting(): bool
    {
        // Aynı ocakta aktif döküm var mı?
        $activeCastingExists = self::hasActiveCastingInFurnace($this->furnace_id);

        // Debug log
        \Log::info("Casting {$this->casting_number} start check: 
            Active casting exists: " . ($activeCastingExists ? 'Yes' : 'No'));

        return !$activeCastingExists;
    }

    /**
     * Bir dökümün tamamlanabilirlik kontrolü
     */
    public function canComplete(): bool
    {
        // Sadece aktif dökümler tamamlanabilir
        if ($this->status !== 'active') {
            \Log::info("Casting {$this->casting_number} cannot be completed: Not active (Current status: {$this->status})");
            return false;
        }

        // Minimum prova sayısı kontrolü (örneğin 3 prova)
        $sampleCount = $this->samples()->count();
        $minRequiredSamples = 3;

        $canComplete = $sampleCount >= $minRequiredSamples;

        // Debug log
        \Log::info("Casting {$this->casting_number} completion check: 
            Sample count: {$sampleCount}, 
            Min required: {$minRequiredSamples}, 
            Can complete: " . ($canComplete ? 'Yes' : 'No'));

        return $canComplete;
    }

    /**
     * Dökümü tamamlama işlemi
     */
    public function completeCasting(?float $finalTemperature = null): bool
    {
        // Tamamlanabilirlik kontrolü
        if (!$this->canComplete()) {
            return false;
        }

        // Dökümü tamamla
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'final_temperature' => $finalTemperature
        ]);

        // Debug log
        \Log::info("Casting {$this->casting_number} completed successfully");

        return true;
    }
}
