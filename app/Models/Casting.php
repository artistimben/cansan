<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

/**
 * Döküm İşlemleri Modeli
 * 
 * Kullanım:
 * $activeCastings = Casting::getActiveCastings(); // Devam eden dökümler
 * $todaysCastings = Casting::getTodaysCastings(); // Bugünkü dökümler
 * $casting->addQualityControl($data); // Kalite kontrol ekler
 */
class Casting extends Model
{
    use HasFactory;

    protected $fillable = [
        'furnace_id',
        'casting_number_per_furnace',
        'global_casting_number',
        'start_time',
        'end_time',
        'duration_minutes',
        'status',
        'production_date',
        'notes'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'production_date' => 'date'
    ];

    /**
     * Döküm hangi ocağa ait
     */
    public function furnace(): BelongsTo
    {
        return $this->belongsTo(Furnace::class);
    }

    /**
     * Döküm kalite kontrolü
     */
    public function qualityControl(): HasOne
    {
        return $this->hasOne(QualityControl::class);
    }

    /**
     * Devam eden dökümler (tüm ocaklar)
     */
    public static function getActiveCastings()
    {
        return self::with('furnace')
                   ->where('status', 'in_progress')
                   ->orderBy('start_time', 'desc')
                   ->get();
    }

    /**
     * Bugünkü tüm dökümler (tamamlanan + devam eden)
     */
    public static function getTodaysCastings()
    {
        return self::with(['furnace', 'qualityControl'])
                   ->where('production_date', now()->toDateString())
                   ->orderBy('global_casting_number', 'desc')
                   ->get();
    }

    /**
     * Genel döküm listesi (son dökümler en üstte)
     */
    public static function getGlobalCastingList($limit = 50)
    {
        return self::with(['furnace', 'qualityControl'])
                   ->orderBy('global_casting_number', 'desc')
                   ->limit($limit)
                   ->get();
    }

    /**
     * Belirli ocağın döküm geçmişi
     */
    public static function getFurnaceCastingHistory($furnaceId, $limit = 20)
    {
        return self::with('qualityControl')
                   ->where('furnace_id', $furnaceId)
                   ->orderBy('casting_number_per_furnace', 'desc')
                   ->limit($limit)
                   ->get();
    }

    /**
     * Döküm süresini hesaplar (dakika)
     */
    public function getActualDurationAttribute()
    {
        if (!$this->end_time || !$this->start_time) {
            return null;
        }

        return $this->start_time->diffInMinutes($this->end_time);
    }

    /**
     * Döküm durumunu günceller
     */
    public function complete()
    {
        $this->update([
            'end_time' => now(),
            'status' => 'completed'
        ]);

        // Ocağın toplam döküm sayısını artır ve yeni döküm başlat
        $this->furnace->completeCasting($this->id);
    }

    /**
     * Kalite kontrol değerleri ekler
     */
    public function addQualityControl(array $data)
    {
        return $this->qualityControl()->create(array_merge($data, [
            'test_time' => now()
        ]));
    }

    /**
     * Döküm kalan süresini hesaplar
     */
    public function getRemainingTimeAttribute()
    {
        if ($this->status !== 'in_progress') {
            return 0;
        }

        $elapsedMinutes = $this->start_time->diffInMinutes(now());
        $remainingMinutes = max(0, $this->duration_minutes - $elapsedMinutes);

        return $remainingMinutes;
    }

    /**
     * Döküm tamamlanma yüzdesini hesaplar
     */
    public function getProgressPercentageAttribute()
    {
        if ($this->status !== 'in_progress') {
            return $this->status === 'completed' ? 100 : 0;
        }

        $elapsedMinutes = $this->start_time->diffInMinutes(now());
        $percentage = min(100, ($elapsedMinutes / $this->duration_minutes) * 100);

        return round($percentage, 1);
    }

    /**
     * Tahmini bitiş zamanını hesaplar
     */
    public function getEstimatedEndTimeAttribute()
    {
        if ($this->end_time) {
            return $this->end_time;
        }

        return $this->start_time->addMinutes($this->duration_minutes);
    }

    /**
     * Döküm geç mi bitiyor kontrolü
     */
    public function isDelayed()
    {
        if ($this->status !== 'in_progress') {
            return false;
        }

        return now()->greaterThan($this->estimated_end_time);
    }

    /**
     * Günlük rapor için döküm istatistikleri
     */
    public static function getDailyCastingStats($date = null)
    {
        $date = $date ?? now()->toDateString();

        $stats = [
            'total_castings' => 0,
            'completed_castings' => 0,
            'in_progress_castings' => 0,
            'furnace_breakdown' => [],
            'average_duration' => 0,
            'efficiency' => 0
        ];

        $castings = self::where('production_date', $date)->get();
        $stats['total_castings'] = $castings->count();
        $stats['completed_castings'] = $castings->where('status', 'completed')->count();
        $stats['in_progress_castings'] = $castings->where('status', 'in_progress')->count();

        // Ocak bazında döküm sayıları
        $furnaceStats = $castings->groupBy('furnace_id');
        foreach ($furnaceStats as $furnaceId => $furnaceCastings) {
            $furnace = Furnace::find($furnaceId);
            $stats['furnace_breakdown'][] = [
                'furnace_number' => $furnace->furnace_number,
                'casting_count' => $furnaceCastings->count(),
                'completed_count' => $furnaceCastings->where('status', 'completed')->count()
            ];
        }

        // Ortalama döküm süresi
        $completedCastings = $castings->where('status', 'completed');
        if ($completedCastings->count() > 0) {
            $totalDuration = $completedCastings->sum(function ($casting) {
                return $casting->actual_duration ?? $casting->duration_minutes;
            });
            $stats['average_duration'] = round($totalDuration / $completedCastings->count(), 1);
        }

        return $stats;
    }
}
