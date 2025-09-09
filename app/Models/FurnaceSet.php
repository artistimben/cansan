<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Ocak Seti Modeli
 * Her set 2 ocak içerir ve sadece 1 tanesi aktif çalışır
 */
class FurnaceSet extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'set_number',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'set_number' => 'integer'
    ];

    /**
     * Bu sete ait ocakları getir
     */
    public function furnaces(): HasMany
    {
        return $this->hasMany(Furnace::class);
    }

    /**
     * Bu setteki aktif ocağı getir
     */
    public function activeFurnace()
    {
        return $this->furnaces()->where('status', 'active')->first();
    }

    /**
     * Bu setteki tüm dökümleri getir
     */
    public function castings()
    {
        return $this->hasManyThrough(Casting::class, Furnace::class);
    }

    /**
     * Bu setteki günlük döküm sayısını getir
     */
    public function getDailyCastingCount($date = null)
    {
        $date = $date ?? now()->format('Y-m-d');
        
        return $this->castings()
            ->whereDate('casting_date', $date)
            ->count();
    }

    /**
     * Bu setteki haftalık döküm sayısını getir
     */
    public function getWeeklyCastingCount($startDate = null)
    {
        $startDate = $startDate ?? now()->startOfWeek();
        $endDate = $startDate->copy()->endOfWeek();
        
        return $this->castings()
            ->whereBetween('casting_date', [$startDate, $endDate])
            ->count();
    }

    /**
     * Bu setteki aylık döküm sayısını getir
     */
    public function getMonthlyCastingCount($month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;
        
        return $this->castings()
            ->whereMonth('casting_date', $month)
            ->whereYear('casting_date', $year)
            ->count();
    }
}
