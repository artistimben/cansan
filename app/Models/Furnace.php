<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Ocak Modeli
 * Fabrikada toplam 6 ocak var, her sette 2 ocak, sadece 1 tanesi aktif
 */
class Furnace extends Model
{
    use HasFactory;

    protected $fillable = [
        'furnace_set_id',
        'name',
        'furnace_number',
        'status',
        'is_operational',
        'description',
        'capacity',
        'max_temperature',
        'current_temperature',
        'fuel_type',
        'installation_date',
        'last_maintenance_date',
        'maintenance_interval_days',
        'status_updated_at'
    ];

    protected $casts = [
        'is_operational' => 'boolean',
        'furnace_number' => 'integer',
        'capacity' => 'decimal:2',
        'max_temperature' => 'decimal:2',
        'current_temperature' => 'decimal:2',
        'installation_date' => 'date',
        'last_maintenance_date' => 'date',
        'maintenance_interval_days' => 'integer',
        'status_updated_at' => 'datetime'
    ];

    /**
     * Bu ocağın ait olduğu set
     */
    public function furnaceSet(): BelongsTo
    {
        return $this->belongsTo(FurnaceSet::class);
    }

    /**
     * Bu ocaktan yapılan dökümler
     */
    public function castings(): HasMany
    {
        return $this->hasMany(Casting::class);
    }

    /**
     * Bu ocaktan alınan tüm provalar
     */
    public function samples()
    {
        return $this->hasManyThrough(Sample::class, Casting::class);
    }

    /**
     * Bu ocağın günlük döküm sayısı
     */
    public function getDailyCastingCount($date = null)
    {
        $date = $date ?? now()->format('Y-m-d');
        
        return $this->castings()
            ->whereDate('casting_date', $date)
            ->count();
    }

    /**
     * Bu ocağın aktif dökümü
     */
    public function getActiveCasting()
    {
        return $this->castings()
            ->where('status', 'active')
            ->latest('casting_date')
            ->first();
    }

    /**
     * Bu ocağın son döküm numarası
     */
    public function getLastCastingNumber()
    {
        $lastCasting = $this->castings()
            ->orderBy('casting_number', 'desc')
            ->first();
            
        return $lastCasting ? $lastCasting->casting_number : 0;
    }

    /**
     * Bu ocağın bir sonraki döküm numarası
     */
    public function getNextCastingNumber()
    {
        return $this->getLastCastingNumber() + 1;
    }

    /**
     * Bu ocağın haftalık performans raporu
     */
    public function getWeeklyPerformance($startDate = null)
    {
        $startDate = $startDate ?? now()->startOfWeek();
        $endDate = $startDate->copy()->endOfWeek();
        
        $castings = $this->castings()
            ->whereBetween('casting_date', [$startDate, $endDate])
            ->with('samples')
            ->get();
            
        return [
            'total_castings' => $castings->count(),
            'total_samples' => $castings->sum(function($casting) {
                return $casting->samples->count();
            }),
            'quality_approved' => $castings->sum(function($casting) {
                return $casting->samples->where('quality_status', 'approved')->count();
            }),
            'quality_rejected' => $castings->sum(function($casting) {
                return $casting->samples->where('quality_status', 'rejected')->count();
            })
        ];
    }

    /**
     * Scope: Sadece aktif ocakları getir
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Sadece çalışır durumda olan ocakları getir
     */
    public function scopeOperational($query)
    {
        return $query->where('is_operational', true);
    }

    /**
     * Bu ocağın sıcaklık geçmişi
     */
    public function temperatureLogs(): HasMany
    {
        return $this->hasMany(FurnaceTemperatureLog::class);
    }

    /**
     * Bu ocağın son sıcaklık kaydı
     */
    public function getLastTemperatureLog()
    {
        return $this->temperatureLogs()
            ->latest('recorded_at')
            ->first();
    }

    /**
     * Bu ocağın son kaydedilen sıcaklığı
     */
    public function getLastRecordedTemperature(): ?float
    {
        $lastLog = $this->getLastTemperatureLog();
        return $lastLog ? $lastLog->temperature : null;
    }

    /**
     * Sıcaklık kaydı ekle
     */
    public function addTemperatureLog(float $temperature, string $logType = 'working', ?string $notes = null, ?string $recordedBy = null): FurnaceTemperatureLog
    {
        return $this->temperatureLogs()->create([
            'temperature' => $temperature,
            'log_type' => $logType,
            'notes' => $notes,
            'recorded_by' => $recordedBy,
            'recorded_at' => now()
        ]);
    }

    /**
     * Aynı setteki diğer ocakları getir
     */
    public function getSameSetFurnaces()
    {
        return self::where('furnace_set_id', $this->furnace_set_id)
            ->where('id', '!=', $this->id)
            ->get();
    }

    /**
     * Bu ocak aktif hale getirildiğinde aynı setteki diğerlerini idle yap
     */
    public function activateAndDeactivateOthersInSet(): void
    {
        if ($this->status !== 'active') {
            // Bu ocağı aktif yap
            $this->update([
                'status' => 'active',
                'status_updated_at' => now()
            ]);
        }

        // Aynı setteki diğer ocakları idle yap
        self::where('furnace_set_id', $this->furnace_set_id)
            ->where('id', '!=', $this->id)
            ->where('status', 'active')
            ->update([
                'status' => 'idle',
                'status_updated_at' => now()
            ]);
    }

    /**
     * Bu ocağın setteki diğer ocaklar aktifken aktif olup olamayacağını kontrol et
     */
    public function canBeActivated(): bool
    {
        // Aynı setteki aktif ocak sayısını kontrol et
        $activeInSet = self::where('furnace_set_id', $this->furnace_set_id)
            ->where('id', '!=', $this->id)
            ->where('status', 'active')
            ->count();

        // Debug: Aynı setteki aktif ocak sayısını kontrol et
        \Log::info("Furnace {$this->name} (Set {$this->furnace_set_id}) canBeActivated check: {$activeInSet} active in same set");
        
        return $activeInSet === 0;
    }

    /**
     * Set kuralına göre durum değiştirme
     */
    public function changeStatusWithSetRule(string $newStatus): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'affected_furnaces' => []
        ];

        \Log::info("changeStatusWithSetRule called for {$this->name} to {$newStatus}");

        if ($newStatus === 'active') {
            if (!$this->canBeActivated()) {
                $activeInSet = self::where('furnace_set_id', $this->furnace_set_id)
                    ->where('id', '!=', $this->id)
                    ->where('status', 'active')
                    ->first();

                $result['message'] = "Aynı sette {$activeInSet->name} zaten aktif. Önce onu kapatın.";
                \Log::info("Set rule violation: {$result['message']}");
                return $result;
            }

            $this->activateAndDeactivateOthersInSet();
            $result['success'] = true;
            $result['message'] = "{$this->name} aktif hale getirildi.";
            $result['affected_furnaces'] = $this->getSameSetFurnaces()->pluck('name')->toArray();
            \Log::info("Furnace activated successfully: {$result['message']}");
        } else {
            $this->update([
                'status' => $newStatus,
                'status_updated_at' => now()
            ]);
            $result['success'] = true;
            $result['message'] = "{$this->name} {$newStatus} duruma getirildi.";
            \Log::info("Furnace status changed: {$result['message']}");
        }

        return $result;
    }
}