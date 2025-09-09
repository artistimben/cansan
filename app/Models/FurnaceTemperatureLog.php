<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ocak Sıcaklık Geçmişi Model'i
 * Her ocağın sıcaklık değişikliklerini kayıt eder
 * 
 * @property int $id
 * @property int $furnace_id
 * @property float $temperature
 * @property string $log_type
 * @property string|null $notes
 * @property string|null $recorded_by
 * @property \Carbon\Carbon $recorded_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class FurnaceTemperatureLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'furnace_id',
        'temperature',
        'log_type',
        'notes',
        'recorded_by',
        'recorded_at'
    ];

    protected $casts = [
        'temperature' => 'decimal:2',
        'recorded_at' => 'datetime'
    ];

    /**
     * Bu sıcaklık kaydının ait olduğu ocak
     */
    public function furnace(): BelongsTo
    {
        return $this->belongsTo(Furnace::class);
    }

    /**
     * Log tipi için Türkçe açıklama
     */
    public function getLogTypeTextAttribute(): string
    {
        return match($this->log_type) {
            'working' => 'Çalışma',
            'shutdown' => 'Kapatma',
            'maintenance' => 'Bakım',
            'manual' => 'Manuel Kayıt',
            default => ucfirst($this->log_type)
        };
    }

    /**
     * Sıcaklık seviyesi badge class'ı
     */
    public function getTemperatureBadgeClassAttribute(): string
    {
        if ($this->temperature >= 1700) {
            return 'bg-danger'; // Çok yüksek
        } elseif ($this->temperature >= 1500) {
            return 'bg-warning'; // Yüksek
        } elseif ($this->temperature >= 1200) {
            return 'bg-success'; // Normal
        } else {
            return 'bg-info'; // Düşük
        }
    }

    /**
     * Belirli bir ocağın son sıcaklık kaydı
     */
    public static function getLastTemperatureForFurnace(int $furnaceId): ?self
    {
        return self::where('furnace_id', $furnaceId)
                   ->latest('recorded_at')
                   ->first();
    }

    /**
     * Belirli bir ocağın sıcaklık geçmişi
     */
    public static function getTemperatureHistoryForFurnace(int $furnaceId, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('furnace_id', $furnaceId)
                   ->latest('recorded_at')
                   ->limit($limit)
                   ->get();
    }
}
