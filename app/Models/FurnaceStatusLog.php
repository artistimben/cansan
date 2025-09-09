<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ocak Durum Geçmişi Modeli
 * Ocak durum değişikliklerini ve notlarını tutar
 */
class FurnaceStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'furnace_id',
        'status',
        'previous_status',
        'reason',
        'notes',
        'operator_name',
        'status_changed_at',
        'castings_count_at_change',
        'count_reset',
        'reset_type'
    ];

    protected $casts = [
        'status_changed_at' => 'datetime',
        'count_reset' => 'boolean',
    ];

    /**
     * Ocak ilişkisi
     */
    public function furnace(): BelongsTo
    {
        return $this->belongsTo(Furnace::class);
    }

    /**
     * Durum değişikliklerini logla
     */
    public static function logStatusChange(
        int $furnaceId,
        string $status,
        ?string $previousStatus = null,
        ?string $reason = null,
        ?string $notes = null,
        ?string $operatorName = null,
        bool $countReset = false,
        ?string $resetType = null
    ): self {
        $furnace = Furnace::find($furnaceId);
        $castingsCount = $furnace ? $furnace->current_cycle_castings : 0;

        return self::create([
            'furnace_id' => $furnaceId,
            'status' => $status,
            'previous_status' => $previousStatus,
            'reason' => $reason,
            'notes' => $notes,
            'operator_name' => $operatorName,
            'status_changed_at' => now(),
            'castings_count_at_change' => $castingsCount,
            'count_reset' => $countReset,
            'reset_type' => $resetType
        ]);
    }

    /**
     * Refraktör değişim logu
     */
    public static function logRefractoryChange(
        int $furnaceId,
        ?string $notes = null,
        ?string $operatorName = null
    ): self {
        return self::logStatusChange(
            $furnaceId,
            'refractory_change',
            null,
            'Refraktör değişimi',
            $notes,
            $operatorName,
            true,
            'refractory_change'
        );
    }

    /**
     * Bakım logu
     */
    public static function logMaintenance(
        int $furnaceId,
        ?string $reason = null,
        ?string $notes = null,
        ?string $operatorName = null,
        bool $resetCount = false
    ): self {
        return self::logStatusChange(
            $furnaceId,
            'maintenance',
            null,
            $reason,
            $notes,
            $operatorName,
            $resetCount,
            $resetCount ? 'maintenance' : null
        );
    }

    /**
     * Duruş logu
     */
    public static function logShutdown(
        int $furnaceId,
        ?string $reason = null,
        ?string $notes = null,
        ?string $operatorName = null
    ): self {
        return self::logStatusChange(
            $furnaceId,
            'shutdown',
            null,
            $reason,
            $notes,
            $operatorName
        );
    }
}