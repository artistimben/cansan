<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Furnace;
use App\Models\FurnaceSet;

/**
 * Ocak Seeder
 * Fabrikadaki 6 ocağı oluşturur (her sette 2 ocak, sadece 1 tanesi aktif)
 */
class FurnaceSeeder extends Seeder
{
    /**
     * Ocakları oluştur
     */
    public function run(): void
    {
        $sets = FurnaceSet::all();

        foreach ($sets as $set) {
            // Her sette 2 ocak oluştur
            $furnaces = [
                [
                    'furnace_set_id' => $set->id,
                    'name' => 'Ocak ' . (($set->set_number - 1) * 2 + 1),
                    'furnace_number' => ($set->set_number - 1) * 2 + 1,
                    'status' => 'active', // İlk ocak aktif
                    'is_operational' => true,
                    'description' => $set->name . ' - Birinci ocak (Aktif)',
                    'capacity' => 50.0, // 50 ton
                    'max_temperature' => 1800.0, // 1800°C
                    'current_temperature' => 1650.0, // Mevcut sıcaklık
                    'fuel_type' => 'natural_gas',
                    'installation_date' => now()->subYears(rand(2, 5))->format('Y-m-d'),
                    'last_maintenance_date' => now()->subDays(rand(10, 90))->format('Y-m-d'),
                    'maintenance_interval_days' => 100,
                    'status_updated_at' => now()
                ],
                [
                    'furnace_set_id' => $set->id,
                    'name' => 'Ocak ' . (($set->set_number - 1) * 2 + 2),
                    'furnace_number' => ($set->set_number - 1) * 2 + 2,
                    'status' => 'idle', // İkinci ocak beklemede
                    'is_operational' => true,
                    'description' => $set->name . ' - İkinci ocak (Standby)',
                    'capacity' => 50.0, // 50 ton
                    'max_temperature' => 1800.0, // 1800°C
                    'current_temperature' => 0.0, // Kapalı
                    'fuel_type' => 'natural_gas',
                    'installation_date' => now()->subYears(rand(2, 5))->format('Y-m-d'),
                    'last_maintenance_date' => now()->subDays(rand(10, 90))->format('Y-m-d'),
                    'maintenance_interval_days' => 100,
                    'status_updated_at' => now()
                ]
            ];

            foreach ($furnaces as $furnaceData) {
                Furnace::create($furnaceData);
            }
        }

        $this->command->info('Ocaklar başarıyla oluşturuldu.');
        $this->command->info('Her sette 1 ocak aktif, 1 ocak standby durumda.');
    }
}
