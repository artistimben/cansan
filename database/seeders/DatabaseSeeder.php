<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Cansan Çelik Üretim Fabrikası Kalite Kontrol Sistemi
 * Ana seeder sınıfı
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Veritabanını seed et
     */
    public function run(): void
    {
        $this->call([
            FurnaceSetSeeder::class,
            FurnaceSeeder::class,
            QualityStandardSeeder::class,
            // Test verileri için (sadece development ortamında)
            // CastingSeeder::class,
            // SampleSeeder::class,
        ]);
    }
}
