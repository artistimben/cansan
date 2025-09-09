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
        'status_updated_at',
        // Yeni alanlar
        'total_castings_count',
        'current_cycle_castings',
        'last_refractory_change',
        'castings_since_refractory',
        'maintenance_notes',
        'shutdown_reason',
        'refractory_notes',
        'operational_notes',
        'last_count_reset',
        'reset_type'
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
        'status_updated_at' => 'datetime',
        // Yeni alanlar
        'total_castings_count' => 'integer',
        'current_cycle_castings' => 'integer',
        'last_refractory_change' => 'date',
        'castings_since_refractory' => 'integer',
        'last_count_reset' => 'datetime'
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

    /**
     * Durum geçmişi ilişkisi
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(FurnaceStatusLog::class);
    }

    /**
     * Döküm sayacını artır
     */
    public function incrementCastingCount(): void
    {
        $this->increment('total_castings_count');
        $this->increment('current_cycle_castings');
        $this->increment('castings_since_refractory');
    }

    /**
     * Döküm sayacını sıfırla
     */
    public function resetCastingCount(string $resetType = 'manual', ?string $notes = null): void
    {
        $this->update([
            'current_cycle_castings' => 0,
            'castings_since_refractory' => 0,
            'last_count_reset' => now(),
            'reset_type' => $resetType
        ]);

        // Durum logu oluştur
        FurnaceStatusLog::logStatusChange(
            $this->id,
            $this->status,
            null,
            'Döküm sayacı sıfırlandı',
            $notes,
            null,
            true,
            $resetType
        );
    }

    /**
     * Refraktör değişimi
     */
    public function changeRefractory(?string $notes = null, ?string $operatorName = null): void
    {
        // Döküm sayacını sıfırla
        $this->resetCastingCount('refractory_change', $notes);
        
        // Refraktör değişim tarihini güncelle
        $this->update([
            'last_refractory_change' => now()->toDateString(),
            'refractory_notes' => $notes
        ]);

        // Durum logu oluştur
        FurnaceStatusLog::logRefractoryChange($this->id, $notes, $operatorName);
    }

    /**
     * Bakım durumu
     */
    public function startMaintenance(?string $reason = null, ?string $notes = null, ?string $operatorName = null, bool $resetCount = false): void
    {
        $this->update([
            'status' => 'maintenance',
            'status_updated_at' => now(),
            'maintenance_notes' => $notes
        ]);

        if ($resetCount) {
            $this->resetCastingCount('maintenance', $notes);
        }

        // Durum logu oluştur
        FurnaceStatusLog::logMaintenance($this->id, $reason, $notes, $operatorName, $resetCount);
    }

    /**
     * Duruş durumu
     */
    public function shutdown(?string $reason = null, ?string $notes = null, ?string $operatorName = null): void
    {
        $this->update([
            'status' => 'shutdown',
            'status_updated_at' => now(),
            'shutdown_reason' => $reason,
            'operational_notes' => $notes
        ]);

        // Durum logu oluştur
        FurnaceStatusLog::logShutdown($this->id, $reason, $notes, $operatorName);
    }

    /**
     * Döküm istatistikleri
     */
    public function getCastingStatistics(): array
    {
        return [
            'total_castings' => $this->total_castings_count,
            'current_cycle_castings' => $this->current_cycle_castings,
            'castings_since_refractory' => $this->castings_since_refractory,
            'last_refractory_change' => $this->last_refractory_change,
            'last_count_reset' => $this->last_count_reset,
            'reset_type' => $this->reset_type
        ];
    }

    /**
     * Bakım durumu kontrolü
     */
    public function needsMaintenance(): bool
    {
        if (!$this->last_maintenance_date || !$this->maintenance_interval_days) {
            return false;
        }

        $daysSinceMaintenance = $this->last_maintenance_date->diffInDays(now());
        return $daysSinceMaintenance >= $this->maintenance_interval_days;
    }

    /**
     * Bakım ilerlemesi
     */
    public function getMaintenanceProgress(): array
    {
        if (!$this->last_maintenance_date || !$this->maintenance_interval_days) {
            return ['progress' => 0, 'days_remaining' => 0, 'needs_maintenance' => false];
        }

        $daysSinceMaintenance = $this->last_maintenance_date->diffInDays(now());
        $progress = min(100, ($daysSinceMaintenance / $this->maintenance_interval_days) * 100);
        $daysRemaining = max(0, $this->maintenance_interval_days - $daysSinceMaintenance);

        return [
            'progress' => round($progress, 1),
            'days_remaining' => $daysRemaining,
            'needs_maintenance' => $daysRemaining <= 0
        ];
    }
}