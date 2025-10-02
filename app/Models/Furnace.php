<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

/**
 * İndüksiyon Ocakları Modeli
 * 
 * Kullanım:
 * $activeFurnaces = Furnace::getActiveFurnaces(); // Aktif ocakları getirir
 * $furnace = Furnace::find(1);
 * $furnace->startNewCasting(); // Yeni döküm başlatır
 */
class Furnace extends Model
{
    use HasFactory;

    protected $fillable = [
        'furnace_number',
        'furnace_set',
        'status',
        'total_castings',
        'max_castings_before_maintenance',
        'last_maintenance_date',
        'next_maintenance_due',
        'is_charging'
    ];

    protected $casts = [
        'last_maintenance_date' => 'datetime',
        'next_maintenance_due' => 'datetime',
        'is_charging' => 'boolean'
    ];

    /**
     * Ocağın dökümlerini getirir
     */
    public function castings(): HasMany
    {
        return $this->hasMany(Casting::class);
    }

    /**
     * Aktif ocakları getirir (maksimum 3 adet)
     */
    public static function getActiveFurnaces()
    {
        return self::where('status', 'active')->orderBy('furnace_number')->get();
    }

    /**
     * Bakıma gitmesi gereken ocakları getirir
     */
    public static function getFurnacesNeedingMaintenance()
    {
        return self::where('total_castings', '>=', self::raw('max_castings_before_maintenance'))
                   ->where('status', 'active')
                   ->get();
    }

    /**
     * Yeni döküm başlatır
     */
    public function startNewCasting()
    {
        // Mevcut döküm var mı kontrol et
        $currentCasting = $this->castings()->where('status', 'in_progress')->first();
        if ($currentCasting) {
            return false; // Zaten devam eden döküm var
        }

        // Genel döküm sayısını hesapla
        $globalCastingNumber = Casting::max('global_casting_number') + 1;
        
        // Ocak bazında döküm sayısını hesapla
        $furnaceCastingNumber = $this->castings()->count() + 1;

        // Yeni döküm oluştur
        $casting = $this->castings()->create([
            'casting_number_per_furnace' => $furnaceCastingNumber,
            'global_casting_number' => $globalCastingNumber,
            'start_time' => now(),
            'production_date' => now()->toDateString(),
            'status' => 'in_progress'
        ]);

        // Charging durumunu güncelle
        $this->update(['is_charging' => true]);

        return $casting;
    }

    /**
     * Döküm tamamlandığında çağrılır
     */
    public function completeCasting($castingId)
    {
        $casting = $this->castings()->find($castingId);
        if (!$casting) {
            return false;
        }

        // Döküm tamamla
        $casting->update([
            'end_time' => now(),
            'status' => 'completed'
        ]);

        // Toplam döküm sayısını artır
        $this->increment('total_castings');

        // Bakım gerekli mi kontrol et
        if ($this->total_castings >= $this->max_castings_before_maintenance) {
            $this->goToMaintenance();
        } else {
            // Otomatik olarak yeni döküm başlat
            $this->startNewCasting();
        }

        return true;
    }

    /**
     * Ocağı bakıma gönderir
     */
    public function goToMaintenance()
    {
        $this->update([
            'status' => 'maintenance',
            'is_charging' => false,
            'last_maintenance_date' => now(),
            'total_castings' => 0 // Bakım sonrası sıfırla
        ]);

        // Aynı setteki diğer ocağı aktif yap
        $this->activateBackupFurnaceInSet();
    }

    /**
     * Aynı setteki yedek ocağı aktif yapar
     */
    private function activateBackupFurnaceInSet()
    {
        $backupFurnace = self::where('furnace_set', $this->furnace_set)
                            ->where('id', '!=', $this->id)
                            ->where('status', 'standby')
                            ->first();

        if ($backupFurnace) {
            $backupFurnace->update(['status' => 'active']);
            $backupFurnace->startNewCasting();
        }
    }

    /**
     * Bakımdan çıkarır
     */
    public function completeMaintenanceAndStandby()
    {
        $this->update([
            'status' => 'standby',
            'next_maintenance_due' => now()->addDays(30), // 30 gün sonra tekrar bakım
            'total_castings' => 0
        ]);
    }

    /**
     * Ocağın mevcut döküm durumunu getirir
     */
    public function getCurrentCasting()
    {
        return $this->castings()->where('status', 'in_progress')->first();
    }

    /**
     * Ocağın bugünkü döküm sayısını getirir
     */
    public function getTodaysCastingCount()
    {
        return $this->castings()
                   ->where('production_date', now()->toDateString())
                   ->where('status', 'completed')
                   ->count();
    }
}
